<?php

namespace plenigo_plugin;

use plenigo\services\TokenService;
use plenigo\services\UserService;

/**
 * PlenigoLoginManager
 * 
 * <b>
 * This class handles the processing of the OAuth URL redirection. Register a User if needed and then logs the User in.
 * For doing all this, it hooks a method to the 'init' filter, so treat with care as it can break the entire Wordpress site.
 * If that happened, delete the plenigo-x.x.x plugin directory, settings will be maintained.
 * </b>
 *
 * @category SDK
 * @package  plenigo_plugin
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class PlenigoLoginManager {

    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    const PLENIGO_META_NAME = 'plenigo_uid';
    const PLENIGO_URL_EXCEPTIONS = 'wp-login,wp-admin';

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options = null;

    /**
     * Default constructor, called from the main php file
     */
    public function __construct() {
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME);
        //If this pageload isn't supposed to be handing a login, just stop here.
        if (filter_input(INPUT_GET, 'code') !== null && filter_input(INPUT_GET, 'code') !== false) {
            add_action("init", array($this, 'plenigo_process_login'));
            add_filter('auth_cookie_expiration', array($this, 'filter_cookie_expiration'), 12, 3);
        } else {
            //Just saving return URL
            add_action('wp_footer', array($this, 'store_url'));
        }

        if (filter_input(INPUT_GET, 'error') !== null && filter_input(INPUT_GET, 'error') !== false &&
            filter_input(INPUT_GET, 'error_description') !== null && filter_input(INPUT_GET, 'error_description') !== false) {
            add_filter('login_message', array($this, 'login_error'));
        }

        // Logout user if there is no plenigo user cookie
        PlenigoSDKManager::get()->getPlenigoSDK();
        $loggedIn = UserService::isLoggedIn();
        if ($loggedIn === false) {
            add_action('wp_footer', array($this, 'trigger_logout'));
        }
        if (filter_input(INPUT_GET, 'loggedout') !== null && filter_input(INPUT_GET, 'loggedout') !== false) {
            add_action('login_footer', array($this, 'ensure_logout'));
        }
    }

    /**
     * Provokes the logout of the user. It is assumed that the logged out users will be the default role
     */
    public function trigger_logout() {
        $user_id = \get_user_meta(\get_current_user_id(), self::PLENIGO_META_NAME, true);
        if (\is_user_logged_in() && $user_id !== '' && $this->is_regular_user()) {
            \wp_logout();
            $returnURL = (isset($_SESSION['plenigo_throwback_url'])) ? $_SESSION['plenigo_throwback_url'] : null;
            if (is_null($returnURL)) {
                $returnURL = \home_url('/');
            }
            echo '<script type="application/javascript">';
            echo "plenigo.logout();location.href='" . $returnURL . "';";
            echo '</script>';
        }
    }

    /**
     * Ensures that plenigo cookie are is cleared after you triggered logout by other means (the WP Bar for example)
     */
    public function ensure_logout() {
        echo'<script type="application/javascript" '
        . 'src="' . PLENIGO_JSSDK_URL . '/static_resources/javascript/'
        . $this->options["company_id"] . '/plenigo_sdk.min.js" data-disable-metered="true"></script>';
        echo '<script type="application/javascript">';
        echo 'plenigo.logout();';
        echo '</script>';
    }

    /**
     * This method checks if the page has to take care of the code received from the Oauth redirection. 
     * If it does then it attempts to register the user, update the fields with plenigo data and the log the user in.
     * 
     * @return void only returns abruptly if no code is found on the request
     */
    public function plenigo_process_login() {
        $code = filter_input(INPUT_GET, 'code');

        // Double check
        if ($code === null || $code === false) {
            return;
        }

        // getting the CSRF Token
        $csrfToken = PlenigoSDKManager::get()->get_csrf_token();
        // this url must be registered in plenigo
        $redirectUrl = $this->options['redirect_url'];

        plenigo_log_message("ATEMPTING LOGIN - REDIRECT TO:" . $redirectUrl);
        // Now we pass the generated CSRF Token as third parameter
        $tokenData = TokenService::getAccessToken($code, $redirectUrl, $csrfToken);

        /**
          The TokenData object contains the following fields:
          accessToken	This token has an expiration date and can be used to get user information
          expiresIn	The time in seconds where the access token will expire
          refreshToken	This token is used to get more access token
          state           This is the csrf token in case you specified one for a more secure request
          tokenType	The type of token
          With this information you can access user data, a simple example of getting the user data is below:
         */
        //obtain the TokenData object with the tokenService or get it from the session if you have already done that
        $userData = UserService::getUserData($tokenData->getAccessToken());

        $currentUser = $this->perform_register($userData);
        do_action('plenigo_prelogin');
        $this->perform_login($currentUser);
        do_action('plenigo_postlogin');
    }

    /**
     * This method looks for the user with the plenigo details. It stores a 'meta' key  with the plenigo UID so,
     * if found, the user is updated, if not found it will look by email address, if found the user is updated,
     * if not found, it will register a new Wordpress user with the plenigo details. The username will have 'PL_'
     * prepended for visibility reasons.
     * 
     * @param plenigo\models\UserData $userData the user data returned by the API call
     * @return int the new or existant Wordpress User ID
     */
    private function perform_register($userData) {
        $plenigoArgs = array('meta_key' => self::PLENIGO_META_NAME, 'meta_value' => $userData->getId());
        // User with meta found
        $plenigoUsers = get_users($plenigoArgs);
        foreach ($plenigoUsers as $user) {
            // override data if needed
            $this->update_with_plenigo_user($user->ID, $userData);
            plenigo_log_message("User found with meta key: " . print_r($user, true));
            return $user->ID;
        }

        // No user found so we look by email
        $emailArgs = array(
            'search' => $userData->getEmail(),
            'search_columns' => array('user_login', 'user_email', 'email'),
            'number' => 1,
            'orderby' => 'user_registered',
            'order' => 'DESC'
        );
        $user_query = new \WP_User_Query($emailArgs);
        if (!empty($user_query->results)) {
            foreach ($user_query->results as $user) {
                // override data if needed
                $this->update_with_plenigo_user($user->ID, $userData);
                plenigo_log_message("User found by email: " . print_r($userData->getEmail(), true));
                // fill with metadata
                update_user_meta($user->ID, self::PLENIGO_META_NAME, $userData->getId());
                return $user->ID;
            }
        } else {
            plenigo_log_message("No User found with email:" . print_r($userData->getEmail(), true));
        }

        // We are still here so we register the user
        $user_data = array();
        $user_data['user_login'] = $this->generateUserName($userData);
        $user_data['user_pass'] = wp_generate_password();
        $user_data['user_nicename'] = sanitize_title($user_data['user_login']);
        $user_data['first_name'] = $userData->getFirstName();
        $user_data['last_name'] = $userData->getLastName();
        $user_data['nickname'] = $userData->getUsername();
        $user_data['display_name'] = $user_data['nickname'];
        $user_data['user_email'] = $userData->getEmail();

        //Insert a new user to our database and make sure it worked
        plenigo_log_message("Inserting user: " . print_r($user_data, true));
        $user_login_id = wp_insert_user($user_data);
        if (is_wp_error($user_login_id)) {
            $errMsg = "Error: wp_insert_user failed!<br /><br />";
            $errMsg .= "Message: " . (method_exists($user_login_id, 'get_error_message') ? $user_login_id->get_error_message() : "Undefined") . "<br />";
            die($errMsg);
        }
        //Success! Notify the site admin.
        wp_new_user_notification($user_login_id);

        //Tag the user with our meta so we can recognize them next time, without resorting to email hashes
        update_user_meta($user_login_id, self::PLENIGO_META_NAME, $userData->getId());

        return $user_login_id;
    }

    /**
     * Performs the actual login of a given Wordpress User ID. If defined it will redirect to the needed URL, or else
     * it will redirect to this blog's home page. This currently doesnt support Wordpress remember me functionality as 
     * it is more complex than just providing a cookie.
     * 
     * @param int $currUserID the Wordpress User ID 
     */
    private function perform_login($currUserID) {
        //Log them in
        $rememberme = true;
        wp_set_auth_cookie($currUserID, $rememberme);
        $homeURL = home_url('/');
        $sessionURL = (isset($_SESSION['plenigo_throwback_url'])) ? $_SESSION['plenigo_throwback_url'] : null;

        // Sanitize Login URL
        if (!isset($this->options['login_url']) || empty($this->options['login_url']) || is_null($this->options['login_url'])) {
            if (is_null($sessionURL)) {
                $this->options['login_url'] = esc_url($homeURL);
            } else {
                $this->options['login_url'] = esc_url($sessionURL);
            }
        }
        plenigo_log_message("Redirecting to:" . $this->options['login_url'] . "  <<<END>>>");
        header("Location: " . $this->options['login_url']);
        exit;
    }

    /**
     * This filter the user being logged in and set it's cookie expiration date one year from now
     * 
     * @param int $length The current length of the cookie expiration (in seconds)
     * @param int $user_id The current user ID being logged in
     * @param bool $remember The remember me flag
     * @return int Number of secconds to allow the cookie to stay
     */
    public function filter_cookie_expiration($length, $user_id, $remember) {
        $length = YEAR_IN_SECONDS; //Standard Wordpress constant since Wordpress 3.5
        return $length;
    }

    /**
     * Provides a custom message to the login screen if the server returned errors on login redirect
     * 
     * @param string $message An optional message that may came from other plugin or Wordpress
     * @return string The final string HTML message
     */
    public function login_error($message) {
        $errorCode = filter_input(INPUT_GET, "error");
        $errorMessage = filter_input(INPUT_GET, "error_description");
        $finalMsg = sprintf("<div id='login_error'><strong>%s:</strong> %s</div>", $errorCode, $errorMessage);
        if (empty($message)) {
            return $finalMsg . "";
        } else {
            return $finalMsg . $message;
        }
    }

    /**
     * Generates a user name given the UserData object. This takes into account several cases:
     * 1 - Username is set on plenigo, then use that
     * 2 - First and Last Name are set, then use "firstNameLastName" algorythm
     * 3 - Nothing is set so it takes the first part of the email address and use it as username
     * 
     * @param plenigo\models\UserData $userData the data that comes from the API call
     * @return string the resolved username to create
     */
    private function generateUserName($userData) {
        plenigo_log_message("USER RETURNED: \n" . print_r($userData, true));

        if (is_null($userData)) {
            return null;
        }

        $userName = trim($userData->getUsername());
        $firstName = trim($userData->getFirstName());
        $lastName = trim($userData->getLastName());

        if (!is_null($userName) && strlen($userName) > 1) {
            plenigo_log_message("FROM USERNAME [" . print_r($userName, true) . "]");
            $name = strtolower($userName);
        } else if (!is_null($lastName) && strlen($lastName) > 1) {
            $firstName = !is_null($firstName) ? $firstName : __('Mr./Mis', self::PLENIGO_SETTINGS_GROUP);
            plenigo_log_message("FROM FIRST, LAST: [" . print_r($firstName, true) . ", " . print_r($lastName, true) . "]");
            $name = lcfirst(ucwords($firstName)) . ucwords($lastName);
        } else {
            $arrName = explode('@', $userData->getEmail());
            plenigo_log_message("FROM EMAIL:\n" . print_r($arrName, true));
            $name = strtolower($arrName[0]);
        }
        //Close multiple words gaps
        $name = str_replace(' ', '', $name);
        //WP sanitize
        $name = sanitize_user(trim($name), true);

        //Add plenigo prefix
        $name = "PL_" . $name;

        //Make sure the name is unique: if we've already got a user with this name, append a number to it.
        $counter = 1;
        if (username_exists($name)) {
            do {
                $username = $name;
                $counter++;
                $username = $username . sprintf("%03d", $counter);
            } while (username_exists($username));
        } else {
            $username = $name;
        }

        return $username;
    }

    /**
     * Check if the current user has the default user role.
     * 
     * @return bool
     */
    private function is_regular_user() {
        $defaultRole = \get_option('default_role');

        return $this->check_user_role($defaultRole);
    }

    /**
     * Checks if a particular user has a role. 
     * Returns true if a match was found.
     *
     * @param string $role Role name.
     * @param int $user_id (Optional) The ID of a user. Defaults to the current user.
     * @return bool
     */
    private function check_user_role($role, $user_id = null) {
        plenigo_log_message("Checking current user for Auto-logout!", E_USER_NOTICE);
        if (!is_null($user_id)) {
            $user = \get_userdata($user_id);
        } else {
            $user = \get_userdata(\get_current_user_id());
        }
        if (empty($user)) {
            return false;
        }
        return in_array($role, (array) $user->roles);
    }

    /**
     * Updates and modifies the user profiles with the plenigo data if needed and site is allowing it
     * 
     * @param int $id the User ID to modify
     * @param plenigo\models\UserData $userData the data that comes from the API call
     */
    public function update_with_plenigo_user($id, $userData) {
        if (isset($this->options['override_profiles']) && $this->options['override_profiles'] == 1) {
            $user_upd = array();
            $user_upd['ID'] = $id;
            $user_upd['user_email'] = $userData->getEmail();
            $user_upd['first_name'] = $userData->getFirstName();
            $user_upd['last_name'] = $userData->getLastName();
            $user_upd['nickname'] = $userData->getUsername();
            $user_upd['display_name'] = $user_upd['nickname'];
            wp_update_user($user_upd);
        }
    }

    /**
     * This method stores the last URL inside the site regardless the HTTP referrer
     */
    public function store_url() {
        plenigo_log_message("PREVIOUS THROWBACK: " . var_export($_SESSION['plenigo_throwback_url'], true), E_USER_NOTICE);


        $current_url = PlenigoURLManager::get()->getSanitizedURL();
        $arrTokens = explode(',', self::PLENIGO_URL_EXCEPTIONS);
        $updNeeded = true;
        foreach ($arrTokens as $token) {
            if (stristr($current_url, $token)) {
                $updNeeded = false;
                break;
            }
        }
        if ($updNeeded !== FALSE) {
            $_SESSION['plenigo_throwback_url'] = $current_url;
        }

        plenigo_log_message("THROWBACK: " . var_export($_SESSION['plenigo_throwback_url'], true), E_USER_NOTICE);
    }

}
