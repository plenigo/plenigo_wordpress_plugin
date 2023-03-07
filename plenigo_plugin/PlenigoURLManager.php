<?php

namespace plenigo_plugin;

/**
 * PlenigoURLManager
 * 
 * <b>
 * This class handles everything aboiut URLs. Detecting th current one, sanitizing others, etc.
 * </b>
 *
 * @category SDK
 * @package  plenigo_plugin
 * @link     https://plenigo.com
 */
class PlenigoURLManager {

    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';

    /**
     * Additional parameters to take out from the URL, this one time;
     * 
     * @var array
     */
    private $forbiddenAdditional = array();

    /**
     * Holds the list of forbidden URL query parameters
     */
    private $forbiddenParams = array(
        "state", "code", "plppsuccess", "plppfailure", "token", "PayerID", "plsofortsuccess", "plsofortfailure", "plpfsuccess", "plpffailure", "LANGUAGE", "OrderID",
        "CN", "NCErrorCN", "CardNo", "Brand", "NCErrorCardNo", "CVC", "NCErrorCVC", "ED", "NCErrorED", "NCError", "Alias", "status", "SHASign",
        "ALIASPERSISTEDAFTERUSE","mobileCID","mobilePID","removeAID");

    /**
     * Holds the plugin configuration options
     */
    private $options = null;

    /**
     * Singleton instance.
     */
    private static $instance = null;

    /**
     * Default constructor, called from the main php file
     */
    private function __construct() {
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME, array());
    }

    /**
     * Returns the singleton instance of the URL Manager to use
     * 
     * @return PlenigoURLManager The URL Manager
     */
    public static function get($forbidenParams = array()) {
        if (self::$instance === null) {
            self::$instance = new PlenigoURLManager();
        }
        self::$instance->forbiddenAdditional = $forbidenParams;

        return self::$instance;
    }

    /*     * *
     * Obtains the current URL the user should be in. This won't include local anchors found in the page.
     */

    public function getCurrentURL() {
        return $this->full_url($_SERVER, true);
    }

    /**
     * Obtains the current URL the user should be in and then sanitixes it. This won't include local anchors found in the page.
     * @return string the current sanitized URL string
     */
    public function getSanitizedURL() {
        $res = $this->getCurrentURL();
        return $this->sanitize_url($res);
    }

    /**
     * This method adds the predicate (query string) to the origin URL and returns it
     * 
     * @param array $s the SERVER variable
     * @param bool $use_fwd_host true if we want to pay attention to the HTTP_X_FORWARDED_HOST header
     * @return string
     */
    private function full_url($s, $use_fwd_host = false) {
        return $this->url_origin($s, $use_fwd_host) . $s['REQUEST_URI'];
    }

    /**
     * This method builds the URL based on several variables and HTTP headers
     * 
     * @param array $s the SERVER variable
     * @param bool $use_fwd_host true if we want to pay attention to the HTTP_X_FORWARDED_HOST header
     * @return string
     */
    private function url_origin($s, $use_fwd_host = false) {
        $ssl = is_ssl();
        $sp = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
        $host = ($use_fwd_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }

    /**
     * Strip certain parameters from the URL for the sake of cohesive experience for the users.
     * @param string $current_url the URL to strip from the query parameters
     */
    private function sanitize_url($current_url) {
        $res = $current_url; // a safe default
        $arrParsedURL = parse_url($current_url);
        $arrParsedQuery = array();
        if (is_null($this->forbiddenAdditional)) {
            $this->forbiddenAdditional = array();
        }
        plenigo_log_message("Filtering additional:" . var_export($this->forbiddenAdditional, true), E_USER_NOTICE);
        $finalForbiddenParams = array_merge($this->forbiddenParams, $this->forbiddenAdditional);
        plenigo_log_message("Filtering these parameters:" . var_export($finalForbiddenParams, true), E_USER_NOTICE);
        if ($arrParsedURL !== FALSE && !is_null($arrParsedURL) && array_key_exists("query", $arrParsedURL)) {
            parse_str($arrParsedURL['query'], $arrParsedQuery);
            plenigo_log_message("QueryString:" . var_export($arrParsedQuery, true), E_USER_NOTICE);
            $arrFilteredQuery = array_diff_key($arrParsedQuery, array_flip($finalForbiddenParams));
            plenigo_log_message("QueryString Filtered:" . var_export($arrFilteredQuery, true), E_USER_NOTICE);
            $arrParsedURL['query'] = http_build_query($arrFilteredQuery);
            plenigo_log_message("URL array Filtered:" . var_export($arrParsedURL, true), E_USER_NOTICE);
            $res = $this->unparse_url($arrParsedURL);
            plenigo_log_message("URL Filtered:" . $res, E_USER_NOTICE);
        }
        return $res;
    }

    /**
     * Manual replacement of http_build_url() to avoid PECL library requirement
     * 
     * @param string $parsed_url The url as it comes
     * @return string a standardized URL with all its fields
     */
    private function unparse_url($parsed_url) {
        //unset blank values
        foreach ($parsed_url as $key => $value) {
            if (isset($parsed_url[$key]) && trim($parsed_url[$key]) === '') {
                unset($parsed_url[$key]);
            }
        }
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? $pass . "@" : '';
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
        return $scheme . $user . $pass . $host . $port . $path . $query . $fragment . '';
    }

}
