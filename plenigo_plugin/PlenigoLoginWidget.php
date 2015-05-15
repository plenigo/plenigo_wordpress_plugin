<?php

namespace plenigo_plugin;

/**
 * PlenigoLoginWidget
 * 
 * <b>
 * This class describes the Widget that allows the plenigo login. It renders the actual widget plus the settings of the widget.
 * This class can change in the future to allow more customizations options of the widget.
 * </b>
 *
 * @category SDK
 * @package  plenigo_plugin
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class PlenigoLoginWidget extends \WP_Widget
{

    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    const REPLACE_LOGIN_FIRST_NAME = "<!--[LOGIN_FIRST_NAME]-->";
    const REPLACE_LOGIN_LAST_NAME = "<!--[LOGIN_LAST_NAME]-->";
    const REPLACE_LOGIN_PRETTY_NAME = "<!--[LOGIN_PRETTY_NAME]-->";
    const REPLACE_PROFILE_URL = "<!--[PROFILE_URL]-->";
    const REPLACE_LOGOUT_ONCLICK = "<!--[LOGOUT_ONCLICK]-->";
    const REPLACE_IF_WP_LOGIN = "<!--[IF_WP_LOGIN]-->";
    const REPLACE_LOGIN_FORM_URL = "<!--[LOGIN_FORM_URL]-->";
    const REPLACE_LOGIN_FORGOT_URL = "<!--[LOGIN_FORGOT_URL]-->";
    const REPLACE_LOGIN_REGISTER_LINKS = "<!--[LOGIN_REGISTER_LINKS]-->";
    const REPLACE_LOGIN_REDIRECT_URL = "<!--[LOGIN_REDIRECT_URL]-->";
    const REPLACE_LOGIN_PLENIGO_ONCLICK = "<!--[LOGIN_PLENIGO_ONCLICK]-->";
    const REPLACE_LOGIN_PLENIGO_TEXT = "<!--[LOGIN_PLENIGO_TEXT]-->";
    //Labels
    const REPLACE_LABEL_USERNAME = "<!--[LABEL_USERNAME]-->";
    const REPLACE_LABEL_PASSWORD = "<!--[LABEL_PASSWORD]-->";
    const REPLACE_LABEL_FORGOT = "<!--[LABEL_FORGOT]-->";
    const REPLACE_LABEL_WP_LOGIN = "<!--[LABEL_WP_LOGIN]-->";
    const REPLACE_LABEL_PL_LOGIN = "<!--[LABEL_PL_LOGIN]-->";
    const REPLACE_LABEL_WELCOME = "<!--[LABEL_WELCOME]-->";
    const REPLACE_LABEL_PROFILE = "<!--[LABEL_PROFILE]-->";
    const REPLACE_LABEL_LOGOUT = "<!--[LABEL_LOGOUT]-->";

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options = null;

    /**
     * Register widget with WordPress.
     */
    function __construct()
    {
        parent::__construct(
            'plenigo_login_widget', // Base ID
            __('Plenigo login', self::PLENIGO_SETTINGS_GROUP), // Name
            array('description' => __('This is the Plegino Login widget. Here you will find the plenigo login button '
                . 'or the user profile data related to the plenigo user.', self::PLENIGO_SETTINGS_GROUP),) // Args
        );
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME, array());
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $loginFormFile = $this->locate_plenigo_template("plenigo-login-form.html");
        $loginStatusFile = $this->locate_plenigo_template("plenigo-login-status.html");
        $strLoginCode = "";

        //Store saved data in option cache
        $this->options['widget'] = $instance;

        //If logged in, show "Welcome, User!"
        if (is_user_logged_in()) {
            $strLoginCode = file_get_contents($loginStatusFile);
            if ($strLoginCode !== false) {
                $strLoginCode = $this->replace_login_status($strLoginCode);
            }
        }
        //Otherwise, show the login form (with plenigo connect button)
        else {
            $strLoginCode = file_get_contents($loginFormFile);
            if ($strLoginCode !== false) {
                $strLoginCode = $this->replace_login_form($strLoginCode);
            }
        }
        echo '<div id="plenigo_login_container">';
        echo $strLoginCode;
        echo '</div>';

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $inst Previously saved values from database.
     */
    public function form($inst)
    {
        $title = !empty($inst['title']) ? $inst['title'] : __('New title', self::PLENIGO_SETTINGS_GROUP);
        $username = !empty($inst['username']) ? $inst['username'] : __('Username:', self::PLENIGO_SETTINGS_GROUP);
        $password = !empty($inst['password']) ? $inst['password'] : __('Password:', self::PLENIGO_SETTINGS_GROUP);
        $forgot = !empty($inst['forgot']) ? $inst['forgot'] : __('Forgot password', self::PLENIGO_SETTINGS_GROUP);
        $wpLogin = !empty($inst['wp_login']) ? $inst['wp_login'] : __('WordPress Login', self::PLENIGO_SETTINGS_GROUP);
        $plLogin = !empty($inst['pl_login']) ? $inst['pl_login'] : __('Plenigo Login', self::PLENIGO_SETTINGS_GROUP);
        $welcome = !empty($inst['welcome']) ? $inst['welcome'] : __('Welcome, ', self::PLENIGO_SETTINGS_GROUP);
        $editProfile = !empty($inst['profile']) ? $inst['profile'] : __('Edit Profile', self::PLENIGO_SETTINGS_GROUP);
        $logout = !empty($inst['logout']) ? $inst['logout'] : __('Logout', self::PLENIGO_SETTINGS_GROUP);

        //Title
        echo '<p><label for="' . $this->get_field_id('title') . '">' . __('Title:', self::PLENIGO_SETTINGS_GROUP) . '</label> ';
        echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="'
        . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '"></p>';

        // Login
        echo '<p><b>LOGIN FORM LABELS</b><br/>';
        //Fields
        echo '<label for="' . $this->get_field_id('username') . '">' . __('Username:', self::PLENIGO_SETTINGS_GROUP) . '</label> ';
        echo '<input class="widefat" id="' . $this->get_field_id('username') . '" name="'
        . $this->get_field_name('username') . '" type="text" value="' . esc_attr($username) . '"><br/>';

        echo '<label for="' . $this->get_field_id('password') . '">' . __('Password:', self::PLENIGO_SETTINGS_GROUP) . '</label> ';
        echo '<input class="widefat" id="' . $this->get_field_id('password') . '" name="'
        . $this->get_field_name('password') . '" type="text" value="' . esc_attr($password) . '"><br/>';

        echo '<label for="' . $this->get_field_id('forgot') . '">' . __('Forgot password', self::PLENIGO_SETTINGS_GROUP) . '</label> ';
        echo '<input class="widefat" id="' . $this->get_field_id('forgot') . '" name="'
        . $this->get_field_name('forgot') . '" type="text" value="' . esc_attr($forgot) . '"><br/>';

        echo '<label for="' . $this->get_field_id('wp_login') . '">' . __('WordPress Login',
            self::PLENIGO_SETTINGS_GROUP) . '</label> ';
        echo '<input class="widefat" id="' . $this->get_field_id('wp_login') . '" name="'
        . $this->get_field_name('wp_login') . '" type="text" value="' . esc_attr($wpLogin) . '"><br/>';

        echo '<label for="' . $this->get_field_id('pl_login') . '">' . __('Plenigo Login', self::PLENIGO_SETTINGS_GROUP) . '</label> ';
        echo '<input class="widefat" id="' . $this->get_field_id('pl_login') . '" name="'
        . $this->get_field_name('pl_login') . '" type="text" value="' . esc_attr($plLogin) . '"><br/>';

        echo '</p>';

        // Status
        echo '<p><b>LOGIN STATUS LABELS</b><br/>';
        //Fields
        echo '<label for="' . $this->get_field_id('welcome') . '">' . __('Welcome, <name>', self::PLENIGO_SETTINGS_GROUP) . '</label> ';
        echo '<input class="widefat" id="' . $this->get_field_id('welcome') . '" name="'
        . $this->get_field_name('welcome') . '" type="text" value="' . esc_attr($welcome) . '"><br/>';

        echo '<label for="' . $this->get_field_id('profile') . '">' . __('Edit Profile', self::PLENIGO_SETTINGS_GROUP) . '</label> ';
        echo '<input class="widefat" id="' . $this->get_field_id('profile') . '" name="'
        . $this->get_field_name('profile') . '" type="text" value="' . esc_attr($editProfile) . '"><br/>';

        echo '<label for="' . $this->get_field_id('logout') . '">' . __('Logout', self::PLENIGO_SETTINGS_GROUP) . '</label> ';
        echo '<input class="widefat" id="' . $this->get_field_id('logout') . '" name="'
        . $this->get_field_name('logout') . '" type="text" value="' . esc_attr($logout) . '"><br/>';

        echo '</p>';
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new Values just sent to be saved.
     * @param array $old Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new, $old)
    {
        $arrNew = array();
        $arrNew['title'] = (isset($new['title']) && !empty($new['title']) ) ? trim(strip_tags($new['title'])) : '';
        $arrNew['username'] = (isset($new['username']) && !empty($new['username']) ) ? trim(strip_tags($new['username'])) : '';
        $arrNew['password'] = (isset($new['password']) && !empty($new['password']) ) ? trim(strip_tags($new['password'])) : '';
        $arrNew['forgot'] = (isset($new['forgot']) && !empty($new['forgot']) ) ? trim(strip_tags($new['forgot'])) : '';
        $arrNew['wp_login'] = (isset($new['wp_login']) && !empty($new['wp_login']) ) ? trim(strip_tags($new['wp_login'])) : '';
        $arrNew['pl_login'] = (isset($new['pl_login']) && !empty($new['pl_login']) ) ? trim(strip_tags($new['pl_login'])) : '';
        $arrNew['welcome'] = (isset($new['welcome']) && !empty($new['welcome']) ) ? trim(strip_tags($new['welcome'])) : '';
        $arrNew['profile'] = (isset($new['profile']) && !empty($new['profile']) ) ? trim(strip_tags($new['profile'])) : '';
        $arrNew['logout'] = (isset($new['logout']) && !empty($new['logout']) ) ? trim(strip_tags($new['logout'])) : '';

        return $arrNew;
    }

    /**
     * This method will create a login snippet and send it back for button on click
     * 
     * @return string the plenigo login snippet
     */
    private function get_plenigo_snippet()
    {
        $redirectUrl = $this->options['redirect_url'];
        $config = new \plenigo\models\LoginConfig($redirectUrl, \plenigo\models\AccessScope::PROFILE);
        $builder = new \plenigo\builders\LoginSnippetBuilder($config);
        $token = PlenigoSDKManager::get()->get_csrf_token();
        $snippet = $builder->withCSRFToken($token)->build();

        // now we can use this snippet in a link or button
        return $snippet;
    }

    /**
     * This method locates the file in theme directories if overriden, or gets it from the template directory
     *
     * @param  string $fileName name of the file that's needed and will be located
     * @return string The located filename with full path in order to read the file, NULL if there was a problem
     */
    private function locate_plenigo_template($fileName)
    {
        if (!is_null($fileName)) {
            $themed_template = locate_template($fileName);
            if (!is_null($themed_template) && is_string($themed_template) && $themed_template !== '') {
                plenigo_log_message("TEMPLATE FROM THEME");

                return $themed_template;
            } else {
                plenigo_log_message("TEMPLATE FROM PLUGIN");

                return dirname(__FILE__) . '/../plenigo_template/' . $fileName;
            }
        }

        return null;
    }

    /**
     * Replace tags for the login status (greeting after logged in).
     * 
     * @param string $html the raw HTML from the template
     * @return string the HTML with actual information, ready to echo
     */
    public function replace_login_status($html)
    {
        plenigo_log_message("Current user for showing status!", E_USER_NOTICE);

        $userdata = \get_userdata(\get_current_user_id());
        $loginFirstName = $userdata->user_firstname;
        $loginLastName = $userdata->user_lastname;
        $loginPrettyName = $userdata->display_name;
        $profileURL = get_option('siteurl') . '/wp-admin/profile.php';
        $logoutOnClick = "plenigo.logout();location.href='" . wp_logout_url($_SERVER['REQUEST_URI']) . "';";

        $html = str_ireplace(self::REPLACE_LOGIN_FIRST_NAME, $loginFirstName, $html);
        $html = str_ireplace(self::REPLACE_LOGIN_LAST_NAME, $loginLastName, $html);
        $html = str_ireplace(self::REPLACE_LOGIN_PRETTY_NAME, $loginPrettyName, $html);
        $html = str_ireplace(self::REPLACE_PROFILE_URL, $profileURL, $html);
        $html = str_ireplace(self::REPLACE_LOGOUT_ONCLICK, $logoutOnClick, $html);

        //Labels
        $html = str_ireplace(self::REPLACE_LABEL_WELCOME, $this->options['widget']['welcome'], $html);
        $html = str_ireplace(self::REPLACE_LABEL_PROFILE, $this->options['widget']['profile'], $html);
        $html = str_ireplace(self::REPLACE_LABEL_LOGOUT, $this->options['widget']['logout'], $html);

        return $html;
    }

    /**
     * Replace tags for the login form (and plenigo login button).
     * 
     * @param string $html the raw HTML from the template
     * @return string the HTML with actual information, ready to echo
     */
    public function replace_login_form($html)
    {
        $loginFormURL = wp_login_url();
        $loginForgotURL = wp_lostpassword_url();
        $registerLinks = wp_register('', '', false);
        $redirectURL = htmlspecialchars($_SERVER['REQUEST_URI']);
        $btnOnClick = $this->get_plenigo_snippet();

        //First of all if we have to strip out the form...
        if (!isset($this->options['use_wp_login']) || $this->options['use_wp_login'] == 1) {
            $html = str_ireplace(self::REPLACE_LOGIN_FORM_URL, $loginFormURL, $html);
            $html = str_ireplace(self::REPLACE_LOGIN_FORGOT_URL, $loginForgotURL, $html);
            $html = str_ireplace(self::REPLACE_LOGIN_REGISTER_LINKS, $registerLinks, $html);
            $html = str_ireplace(self::REPLACE_LOGIN_REDIRECT_URL, $redirectURL, $html);
            //Remove conditional tags
            $html = str_ireplace(self::REPLACE_IF_WP_LOGIN, '', $html);
        } else {
            //Strip everything between both tags
            $arrHtml = explode(self::REPLACE_IF_WP_LOGIN, $html, 3);
            if (!is_null($arrHtml) && $arrHtml !== false && count($arrHtml) == 3) {
                $html = $arrHtml[0] . $arrHtml[2];
            }
        }
        $html = str_ireplace(self::REPLACE_LOGIN_PLENIGO_ONCLICK, $btnOnClick, $html);

        //Labels
        $html = str_ireplace(self::REPLACE_LABEL_USERNAME, $this->options['widget']['username'], $html);
        $html = str_ireplace(self::REPLACE_LABEL_PASSWORD, $this->options['widget']['password'], $html);
        $html = str_ireplace(self::REPLACE_LABEL_FORGOT, $this->options['widget']['forgot'], $html);
        $html = str_ireplace(self::REPLACE_LABEL_WP_LOGIN, $this->options['widget']['wp_login'], $html);
        $html = str_ireplace(self::REPLACE_LABEL_PL_LOGIN, $this->options['widget']['pl_login'], $html);

        return $html;
    }

}
