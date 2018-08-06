<?php

namespace plenigo_plugin;

/**
 * PlenigoSDKManager
 *
 * <b>
 * This class handles the initialization, main methods and request caches for the Wordpress plugin. Initialization is done by calling the getPlenigoSDK() method once it's configured properly.
 * </b>
 *
 * @category SDK
 * @package  plenigo_plugin
 * @link     https://plenigo.com
 */
class PlenigoSDKManager
{

    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    const PLENIGO_META_NAME = 'plenigo_uid';

    /**
     * Holds the Plugin configuration options
     */
    private $options = null;

    /**
     * Holds the PlenigoManager
     * @var \plenigo\PlenigoManager
     */
    private $plenigoSDK = null;

    /**
     * Holds values for the SDK requests, so they are made just once per request
     */
//    private $reqCache = array();

    /**
     * Singleton instance.
     */
    private static $instance = null;

    /**
     * Default constructor, called from the main php file
     */
    private function __construct() {
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME, array());
        $this->start_session();
    }

    /**
     * Returns the singleton instance of the SDK Manager to use
     *
     * @return PlenigoSDKManager The SDK Manager
     */
    public static function get() {
        if (self::$instance === null) {
            self::$instance = new PlenigoSDKManager();
        }

        return self::$instance;
    }

    /**
     * Obtain a value or object from the request cache
     *
     * @param string $cacheKey The Key to find
     *
     * @return mixed The value obtained or null
     */
    function getCacheValue($cacheKey = null) {
        if (!is_null($cacheKey) && array_key_exists($cacheKey, $this->reqCache)) {
            return $this->reqCache[$cacheKey];
        } else {
            return null;
        }
    }

    /**
     * Sets a cached value for this request
     *
     * @param string $cacheKey The Cache Key
     * @param mixed $cacheValue The Value object
     */
    function setCacheValue($cacheKey = null, $cacheValue = null) {
        if (!is_null($cacheKey) && is_string($cacheKey) && !is_null($cacheValue)) {
            $this->reqCache[$cacheKey] = $cacheValue;
        }
    }

    /**
     * Obtain a value or object from the session
     *
     * @param string $valueKey The Key to find
     *
     * @return mixed The value obtained or null
     */
    function getSessionValue($valueKey = null) {
        $this->start_session();
        if (!is_null($valueKey) && isset($_SESSION[$valueKey])) {
            return $_SESSION[$valueKey];
        } else {
            return null;
        }
    }

    /**
     * Sets a cached value for this session
     *
     * @param string $key The session variable Key
     * @param mixed $value The Value object
     */
    function setSessionValue($key = null, $value = null) {
        $this->start_session();
        if (!is_null($key) && is_string($key) && !is_null($value)) {
            $_SESSION[$key] = $value;
        }
        if (is_null($value)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Creates or configures the plenigo SDK to be used in the class for calling the plenigo Services
     *
     * @return \plenigo\PlenigoManager the new or reused instance of the PlenigoManager
     */
    public function getPlenigoSDK() {
        if (is_null($this->plenigoSDK)) {
            $testValue = false;
            if (!isset($this->options['test_mode']) || ($this->options['test_mode'] == 1)) {
                $testValue = true;
            }
            plenigo_log_message('Configuring SDK for company id: ' . $this->options["company_id"], E_USER_NOTICE);
            $this->plenigoSDK = \plenigo\PlenigoManager::configure(
                $this->options["company_secret"], $this->options["company_id"], $testValue
                , PLENIGO_SVC_URL, PLENIGO_OAUTH_SVC_URL
            );
        }
        $this->plenigoSDK->setDebug((PLENIGO_DEBUG === true));
        return $this->plenigoSDK;
    }

    /**
     * Generates or obtains a CRSF token to be used in Oauth and SSO requests
     *
     * @return string the CSRF token generated or otherwise cached in user session
     */
    public function get_csrf_token() {
        $this->start_session();

        if (isset($_SESSION['plenigo_csrf'])) {
            $csrfValue = $_SESSION["plenigo_csrf"];
        } else {
            $csrfValue = \plenigo\services\TokenService::createCsrfToken();
            $_SESSION["plenigo_csrf"] = $csrfValue;
        }
        return $csrfValue;
    }

    /**
     * Calls the PHP SDK and queries the server for products already bought. Sanitizes the response as a boolean
     *
     * @param  string $products the product Id string or an array of product ids
     *
     * @return bool true if the user has bought the product
     */
    public function plenigo_bought($products = null) {
        if (is_null($products)) {
            plenigo_log_message("Plenigo bought check: false => products null", E_USER_NOTICE);
            return false;
        }

        if (is_string($products)) {
            $products = array($products);
        }

        if (!is_array($products) || count($products) < 1) {
            plenigo_log_message("Plenigo bought check: false => products array is weird " . print_r($products, true), E_USER_NOTICE);
            return false;
        }

        if (!isset($this->reqCache['bought'])) {
            plenigo_log_message("Bought check array initialized", E_USER_NOTICE);
            $this->reqCache['bought'] = array();
        }

        $result = false;
        $sdk = $this->getPlenigoSDK();
        if (is_null($sdk) || !($sdk instanceof \plenigo\PlenigoManager)) {
            plenigo_log_message("Plenigo bought check: false => SDK failed to start", E_USER_WARNING);
            return false;
        }
        foreach ($products as $currProdID) {
            // cached
            if (isset($this->reqCache['bought'][$currProdID]) && $this->reqCache['bought'][$currProdID] === true) {
                plenigo_log_message("Plenigo bought cached result true for " . $currProdID, E_USER_NOTICE);
                $result = true;
            }
            try {
                $res = \plenigo\services\UserService::hasUserBought($currProdID);
                //caching
                $this->reqCache['bought'][$currProdID] = $res;
                plenigo_log_message("Plenigo bought result for " . $currProdID . ' = ' . var_export($res, true), E_USER_NOTICE);

                $result = ($res === true) ? true : $result;
            } catch (\Exception $exc) {
                plenigo_log_message($exc->getMessage() . '<br>' . $exc->getTraceAsString(), E_USER_WARNING);
            }
            if ($result === true) {
                break;
            }
        }

        plenigo_log_message("Plenigo bought result " . var_export($result, true), E_USER_NOTICE);
        return $result;
    }

    /**
     * Checks if the user has free views in a metered environment
     *
     * @return bool true if the user has metered views left
     */
    public function plenigo_has_free_views() {
        // cached
        if (isset($this->reqCache['freeViews'])) {
            plenigo_log_message("frreViews cached: " . ($this->reqCache['freeViews'] ? 'true' : 'false'));
            return $this->reqCache['freeViews'];
        }

        $result = false;

        if (!isset($this->options['check_metered']) || $this->options['check_metered'] == 1) {
            try {
                $res = \plenigo\services\MeterService::hasFreeViews();
                $result = ($res === true);
            } catch (\Exception $exc) {
                error_log($exc->getMessage() . '<br>' . $exc->getTraceAsString(), 0);
            }
        }

        //caching
        $this->reqCache['freeViews'] = $result;

        plenigo_log_message("freeViews returned: " . ($result ? 'true' : 'false'));
        return $result;
    }

    /**
     * This method checks for the paywall enabled flag from the plenigo administration.
     *
     * @return bool TRUE if the paywall is enabled from the server side, false if not
     */
    public function isPayWallEnabled() {
        // cached
        if (isset($this->reqCache['payWallEnabled'])) {
            return $this->reqCache['payWallEnabled'];
        }

        $result = false;
        $sdk = $this->getPlenigoSDK();
        if (!is_null($sdk) && ($sdk instanceof \plenigo\PlenigoManager)) {
            try {
                $res = \plenigo\services\UserService::isPaywallEnabled();
                $result = ($res === true);
            } catch (\Exception $exc) {
                error_log($exc->getMessage() . '<br>' . $exc->getTraceAsString(), 0);
            }
            //caching
            $this->reqCache['payWallEnabled'] = $result;
        }

        return $result;
    }

    /**
     * Checks if a session is already started (with PHP compatibility) and starts it if there isnt one
     */
    private function start_session() {
        $res = false;
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                $res = session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                $res = session_id() === '' ? FALSE : TRUE;
            }
        }
        if (($res === FALSE && !headers_sent()) || (!isset($_SESSION) && !headers_sent())) {
            session_start();
        }
    }

    /**
     * Generates the login snippet with the current wordpress configuration.
     *
     * @return mixed
     */
    public function getLoginSnippet() {
        $options = get_option(self::PLENIGO_SETTINGS_NAME, array());
        $redirectUrl = $options['redirect_url'];
        if (empty($redirectUrl)) {
            $redirectUrl = null;
        }
        $config = new \plenigo\models\LoginConfig($redirectUrl, \plenigo\models\AccessScope::PROFILE);
        $builder = new \plenigo\builders\LoginSnippetBuilder($config);
        $token = \plenigo_plugin\PlenigoSDKManager::get()->get_csrf_token();
        return $builder->withCSRFToken($token)->build();
    }

    /**
     * Generates the login snippet with the current wordpress configuration.
     *
     * @return mixed
     */
    public function getProfileUrl() {
        $options = get_option(self::PLENIGO_SETTINGS_NAME, array());
        $profileUrl = $options['profile_url'];
        if (empty($profileUrl)) {
            return "";
        }
        return $profileUrl;
    }

    /**
     * Get the group one product list.
     * 
     * @return array product list as array
     */
    public function getPlenigoGroupOne() {
        $csvList = array();
        $options = get_option(self::PLENIGO_SETTINGS_NAME, array());
        $plenigoGroupOne = (isset($options['plenigo_product_group_one_db']) ? $this->options['plenigo_product_group_one_db'] : '');
        if (!empty($plenigoGroupOne)) {
            $csvList = explode(',', $plenigoGroupOne);
            $csvList = array_map('trim', $csvList);
        }
        return $csvList;
    }
}
