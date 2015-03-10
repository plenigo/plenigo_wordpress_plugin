<?php

namespace plenigo_plugin;

/**
 * PlenigoSDKManager
 * 
 * <b>
 * 
 * </b>
 *
 * @category SDK
 * @package  plenigo_plugin
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
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
     * Holds values for the SDK requests, so they are mdae just once per request
     */
    private $reqCache = array();

    /**
     * Singleton instance.
     */
    private static $instance = null;

    /**
     * Default constructor , called from the main php file
     */
    private function __construct()
    {
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME);
    }

    /**
     * Returns the singleton instance of the SDK Manager to use
     * 
     * @return PlenigoSDKManager The SDK Manager
     */
    public static function get()
    {
        if (self::$instance === null) {
            self::$instance = new PlenigoSDKManager();
        }

        return self::$instance;
    }

    /**
     * Creates or configures the Plenigo SDK to be used in the class for calling the Plenigo Services
     * 
     * @return \plenigo\PlenigoManager the new or reused instance of the PlenigoManager
     */
    public function getPlenigoSDK()
    {
        if (is_null($this->plenigoSDK)) {
            $testValue = false;
            if (!isset($this->options['test_mode']) || ($this->options['test_mode'] == 1 )) {
                $testValue = true;
            }
            $this->plenigoSDK = \plenigo\PlenigoManager::configure(
                    $this->options["company_secret"], $this->options["company_id"], $testValue
                    , PLENIGO_JSSDK_URL
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
    public function get_csrf_token()
    {
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
     * @param  string  $products the product Id string or an Array of Product Ids
     * @return boolean true if the user has bought the product
     */
    public function plenigo_bought($products = null)
    {
        if (is_null($products)) {
            return false;
        }

        if (is_string($products)) {
            $products = array($products);
        }

        if (!is_array($products) || count($products) < 1) {
            return false;
        }

        if (!isset($this->reqCache['bought'])) {
            $this->reqCache['bought'] = array();
        }

        $result = false;
        $sdk = $this->getPlenigoSDK();
        if (is_null($sdk) || !($sdk instanceof \plenigo\PlenigoManager)) {
            return false;
        }
        foreach ($products as $currProdID) {
            // cached
            if (isset($this->reqCache['bought'][$currProdID]) && $this->reqCache['bought'][$currProdID] === true) {
                $result = true;
            }
            try {
                $res = \plenigo\services\UserService::hasUserBought($currProdID);
                //caching
                $this->reqCache['bought'][$currProdID] = $res;

                $result = ($res === true) ? true : $result;
            } catch (\Exception $exc) {
                plenigo_log_message($exc->getMessage() . '<br>' . $exc->getTraceAsString(), 0, E_USER_WARNING);
            }
            if ($result === true) {
                break;
            }
        }

        return $result;
    }
    
    /**
     * Checks if the user has free views in a metered environment
     *
     * @return boolean true if the user has metered views left
     */
    public function plenigo_has_free_views()
    {
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
     * This method checks for the Paywall Enabled flag from the plenigo administration.
     *
     * @return boolean TRUE if the Paywall is enabled from the server side, false if not
     */
    public function isPayWallEnabled()
    {
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
    private function start_session()
    {
        $res = false;
        if (php_sapi_name() !== 'cli') {
            if (version_compare(phpversion(), '5.4.0', '>=')) {
                $res = session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
            } else {
                $res = session_id() === '' ? FALSE : TRUE;
            }
        }
        if ($res === FALSE) {
            session_start();
        }
    }

}