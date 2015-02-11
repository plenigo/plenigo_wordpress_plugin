<?php

/*
  Copyright (C) 2014 Plenigo

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace plenigo_plugin;

/**
 * PlenigoContentManager
 *
 * <b>
 * This class handles the entire content and allows to apply the Plenigo Paywall ruleset. It also uses the theme
 * templating system to provide a customizable look and feel for the Plenigo Paywall curtain (what you see if you
 * haven't paid for the article).
 * </b>
 *
 * @category WordPressPlugin
 * @package  plenigoPlugin
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class PlenigoContentManager
{

    private $plenigoSDK = null;

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options = null;

    /**
     * Holds values for the SDK requests, so they are mdae just once per request
     */
    private $reqCache = array();

    /**
     * Holds the String[] to be rendered for debug checlist.
     *
     * @var array
     */
    private $debugChecklist = array();

    /**
     * Holds events that will be pushed to Google Analytics
     *
     * @var array
     */
    private $gaEventList = array();

    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    const BOUGHT_STRING_FORMAT = "%s <p><p>Content bought with %s Thank you for your support";
    const MORE_SPLITTER = '<span id="more-';
    const JS_BASE_URL = "https://www.plenigo.com";
    const JS_BASE_URL_NOAUTH = "https://www.plenigo.com";
    const RENDER_FEED = 0;
    const RENDER_SINGLE = 1;
    const RENDER_SEARCH = 2;
    const RENDER_OTHER = 3;
    const REPLACE_PLUGIN_DIR = "<!--[PLUGIN_DIR]-->";
    const REPLACE_PRODUCT_NAME = "<!--[PRODUCT_NAME]-->";
    const REPLACE_PRODUCT_PRICE = "<!--[PRODUCT_PRICE]-->";
    const REPLACE_PRODUCT_DETAILS = "<!--[PRODUCT_DETAILS]-->";
    const REPLACE_CURTAIN_TITLE = "<!--[CURTAIN_TITLE]-->";
    const REPLACE_CURTAIN_MSG = "<!--[CURTAIN_MSG]-->";
    const REPLACE_BUTTON_TITLE = "<!--[BUTTON_TITLE]-->";
    const REPLACE_BUTTON_CLICK = "<!--[BUTTON_CLICK]-->";
    const REPLACE_BUTTON_STYLE = "<!--[BUTTON_STYLE]-->";
    const REPLACE_LOGIN_TITLE = "<!--[LOGIN_TITLE]-->";
    const REPLACE_LOGIN_CLICK = "<!--[LOGIN_CLICK]-->";
    const REPLACE_LOGIN_STYLE = "<!--[LOGIN_STYLE]-->";
    const REPLACE_CUSTOM_TITLE = "<!--[CUSTOM_TITLE]-->";
    const REPLACE_CUSTOM_CLICK = "<!--[CUSTOM_CLICK]-->";
    const REPLACE_CUSTOM_STYLE = "<!--[CUSTOM_STYLE]-->";
    //Google Analytics
    const REPLACE_GA_CODE = "<!--[PLENIGO_GA_CODE]-->";
    const REPLACE_GA_EVENTS = "<!--[PLENIGO_GA_EVENTS]-->";

    private $templateMap = array(
        self::RENDER_FEED => array(true => null, false => 'plenigo-curtain-feed.html'),
        self::RENDER_SINGLE => array(true => null, false => 'plenigo-curtain-single.html'),
        self::RENDER_SEARCH => array(true => null, false => 'plenigo-curtain-search.html'),
        self::RENDER_OTHER => array(true => null, false => 'plenigo-curtain-feed.html')
    );

    /**
     * Default constructor , called from the main php file
     */
    public function __construct()
    {
        add_filter('the_content', array($this, 'plenigo_handle_main_content'), 20);
        add_filter('the_content_feed ', array($this, 'plenigo_handle_feed_content'), 20);
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME);

        add_action('wp_footer', array($this, 'plenigo_js_snippet'));
        add_action('wp_enqueue_scripts', array($this, 'add_scripts'));
    }

    /**
     * Add Javascript/CSS imports
     */
    public function add_scripts()
    {
        wp_register_style('plenigo-curtain-css', plugins_url('plenigo_css/miniStopper.css', dirname(__FILE__)));
        wp_enqueue_style('plenigo-curtain-css');
    }

    /**
     * This is only for testing purposes, the snipet here allows to change the
     * baseURL of the JS and PHP SDKs at the same time
     */
    public function plenigo_js_snippet()
    {
        PlenigoSDKManager::get()->getPlenigoSDK();
        $isPaywalled = $this->plenigo_paywalled_content();
        /*
        echo '<script type="application/javascript">'
        . 'var plenigo = plenigo || {};'
          . 'plenigo.baseURI = "' . self::JS_BASE_URL_NOAUTH . '";</script>'; */

        if ($isPaywalled == true && !isset($this->reqCache["listProdId"]) && !isset($this->reqCache["lastCatId"])) {
            plenigo_log_message("PRODUCT OR CATEGORY NOT FOUND!!!", E_USER_WARNING);
        }
        $rType = $this->get_render_type(false);
        $userBought = (PlenigoSDKManager::get()->plenigo_bought($this->reqCache["listProdId"]) === true);
        $hasFreeViews = (PlenigoSDKManager::get()->plenigo_has_free_views() === true);

        $disableText = '';
        if ($isPaywalled === false || $userBought === true || $hasFreeViews === false || $rType !== self::RENDER_SINGLE) {
            $disableText = 'data-disable-metered="true"';
        }

        echo'<script type="application/javascript" '
        . 'src="' . self::JS_BASE_URL . '/static_resources/javascript/'
        . $this->options["company_id"] . '/plenigo_sdk.min.js" '
        . $disableText . '></script>';

        $this->printGoogleAnalytics();

        //Output the checklist
        $this->printDebugChecklist();
    }

    /**
     * *  This method redirects to the content handler for main , widget and serach renderers
     *
     * @param  string $content the contents as it will be shown
     * @return string the content filtered if needed by the Plenigo paywall
     */
    public function plenigo_handle_main_content($content)
    {
        return $this->plenigo_filter_content($content);
    }

    /**
     *  This method redirects to the content handler and set the feed flag to determine that we are in a feed renderer
     * This method is particular to the Feed functionality (Atom, RSS, etc)
     *
     * @param  string $content the contents as it will be shown
     * @return string the content filtered if needed by the Plenigo paywall
     */
    public function plenigo_handle_feed_content($content)
    {
        return $this->plenigo_filter_content($content, true);
    }

    /**
     * This method handles the content itself, showing only what the author wants to show using the MORE tag
     * and then adds the Plenigo curtain if not purchased. Otherwise shows the plain contents as usual.
     *
     * @param  string  $content the contents as it will be shown
     * @param  boolean $isFeed  TRUE if the method is being called from a FEED filter or not
     * @return string  the content filtered if needed by the Plenigo paywall
     */
    private function plenigo_filter_content($content, $isFeed = false)
    {
        $curtain_code = '';
        if ($this->plenigo_paywalled_content()) {
            plenigo_log_message("ITS PAYWALLED");
            $rType = $this->get_render_type($isFeed);
            $hasBought = $this->user_bought_content($isFeed);
            $html_curtain = null;
            if (!$hasBought && !current_user_can('edit_post',  get_the_ID())) {

                if (isset($this->templateMap[$rType][$hasBought])) {
                    $html_curtain = $this->templateMap[$rType][$hasBought];
                    $curtain_file = $this->locate_plenigo_template($html_curtain);
                }

                $curtain_code = $content;

                //IF the blog is configured to show only excerpts in the RSS, then we let the content
                //go if paywalled and MORE tag not found, otherwise we don't show anything
                $showByDefault = false;
                if (get_option('rss_use_excerpt ', 0) === 1) {
                    $showByDefault = true;
                }

                switch ($rType) {
                    case self::RENDER_FEED :
                        plenigo_log_message("ITS A FEED");
                        $curtain_code = $this->plenigo_curtain($content, $curtain_file, $showByDefault);
                        break;
                    case self::RENDER_SINGLE :
                        plenigo_log_message("ITS A SINGLE");
                        $curtain_code = $this->plenigo_curtain($content, $curtain_file, false);
                        break;
                    case self::RENDER_SEARCH :
                        plenigo_log_message("ITS A SEARCH");
                        break;
                    case self::RENDER_OTHER :
                        plenigo_log_message("ITS OTHER");
                        break;
                    default:
                        plenigo_log_message("ITS UNKNOWN");
                        break;
                }
                $this->addGAEvent("curtain|curtain-visit");
            } else {
                $this->addDebugLine("ITS BOUGHT OR THE USER CAN EDIT IT");
                $curtain_code = $content;
            }
            $content = $curtain_code;
        }

        return $content;
    }

    /**
     * This function gets the teaser from the content and concatenates the generated plenigo Curtain if any.
     * An optional parameter can be set to true if we want to return the content anyways if no MORE tag
     * is found.
     *
     * @param  string  $content      The entire content of the post
     * @param  string  $curtain_file the filename of the template we will search on theme directories
     * @param  boolean $permisive    set to TRUE to return the entire content if more tag not found
     * @return string  the content to be displayed
     */
    private function plenigo_curtain($content, $curtain_file, $permisive = false)
    {
        $teaser = $this->get_teaser_from_content($content, $permisive);
        $curtain_snippet = $this->get_curtain_code($curtain_file);

        return $teaser . $curtain_snippet;
    }

    /**
     * This method checks if the content is due to be paywalled by Plenigo
     *
     * @return boolean TRUE if the content needs to be paywalled, false otherwise
     */
    private function plenigo_paywalled_content()
    {
        $plenigoTagDB = (isset($this->options['plenigo_tag_db']) ? $this->options['plenigo_tag_db'] : '');
        $plenigoCatTagDB = (isset($this->options['plenigo_cat_tag_db']) ? $this->options['plenigo_cat_tag_db'] : '');

        // Sanitize the product cache
        if (!isset($this->reqCache["listProdId"])) {
            $this->reqCache["listProdId"] = array();
        }
        // Sanitize the category cache
        if (!isset($this->reqCache["listCatId"])) {
            $this->reqCache["listCatId"] = array();
        }

        // Do not paywall if nothing is configured
        if (trim($plenigoTagDB) === '' && trim($plenigoCatTagDB) === '') {
            plenigo_log_message("NO TAGS CONFIGURED");
            return false;
        }

        // If Paywall is disabled, we dont check anything
        if (PlenigoSDKManager::get()->isPayWallEnabled()) {

            //Checking for Category IDs
            $hasAnyCatTag = $this->hasAnyCategoryTag();
            if ($hasAnyCatTag) {
                return true;
            } else {
                //Checking for Product IDs
                $hasAnyProdTag = $this->hasAnyProductTag();
                return $hasAnyProdTag;
            }
        }

        plenigo_log_message("TAG NOT FOUND");

        return false;
    }

    /**
     * This method checks for Category IDs that need to be added to the category list 
     * so it can be checked for bought products. Returns false if there is no tag 
     * found on the article that reflects a tag in the Plenigo Settings
     * 
     * @return boolean Returns TRUE if a tag has been found and the category list is in the cache
     */
    public function hasAnyCategoryTag()
    {
        if (isset($this->reqCache["hasAnyCategoryTag"])) {
            return $this->reqCache["hasAnyCategoryTag"];
        }
        $catTagList = (isset($this->options['plenigo_cat_tag_db']) ? $this->options['plenigo_cat_tag_db'] : '');
        $res = false;
        //TAGS WITH VATEGORY IDS
        $rowSplit = explode("\n", $catTagList);
        if ($rowSplit == false || count($rowSplit) == 0) {
            $rowSplit = array($catTagList);
        }
        foreach ($rowSplit as $tagRow) {
            $strTag = explode("->", $tagRow);
            $arrToken = array();
            //Obtain the {slug}
            preg_match('/{(.*?)}/', $strTag[0], $arrToken);
            if ($strTag !== false && count($strTag) == 2 && count($arrToken) == 2 && has_tag($arrToken[1])) {
                plenigo_log_message("Category TAG! TAG=" . $strTag[0] . " CategoryID(s):" . $strTag[1]);
                $this->addDebugLine("Category match: " . $strTag[0]);
                $arrCats = array();
                //Support for multiple ids in one tag
                if (stristr($strTag[1], ',')) {
                    $arrCats = explode(',', $strTag[1]);
                } else {
                    $arrCats = array($strTag[1]);
                }
                foreach ($arrCats as $cid) {
                    if (!isset($this->reqCache["lastCatId"])) {
                        $this->reqCache["lastCatId"] = trim($cid);
                    }
                    array_push($this->reqCache["listCatId"], trim($cid));
                    $res = true;
                }
            }
        }
        if ($res === true) {
            $this->addDebugLine("Categories: " . var_export($this->reqCache["listCatId"], true));
            $this->addGAEvent("curtain|category-matched");
        }
        $this->reqCache["hasAnyCategoryTag"] = $res;
        return $res;
    }

    /**
     * This method checks for Product IDs that need to be added to the product list 
     * so it can be checked for bought products. Returns false if there is no tag 
     * found on the article that reflects a tag in the Plenigo Settings
     * 
     * @return boolean Returns TRUE if a tag has been found and the product list is in the cache
     */
    private function hasAnyProductTag()
    {
        if (isset($this->reqCache["hasAnyProductTag"])) {
            return $this->reqCache["hasAnyProductTag"];
        }
        $prodTagList = (isset($this->options['plenigo_tag_db']) ? $this->options['plenigo_tag_db'] : '');
        $res = false;
        //TAGS WITH PRODUCT IDS
        $rowSplit = explode("\n", $prodTagList);
        if ($rowSplit == false || count($rowSplit) == 0) {
            $rowSplit = array($prodTagList);
        }
        foreach ($rowSplit as $tagRow) {
            $strTag = explode("->", $tagRow);
            $arrToken = array();
            //Obtain the {slug}
            preg_match('/{(.*?)}/', $strTag[0], $arrToken);
            if ($strTag !== false && count($strTag) == 2 && count($arrToken) == 2 && has_tag($arrToken[1])) {
                plenigo_log_message("Product TAG! TAG=" . $strTag[0] . " ProductID(s):" . $strTag[1]);
                $this->addDebugLine("Product match: " . $strTag[0]);
                $arrProds = array();
                //Support for multiple ids in one tag
                if (stristr($strTag[1], ',')) {
                    $arrProds = explode(',', $strTag[1]);
                } else {
                    $arrProds = array($strTag[1]);
                }
                foreach ($arrProds as $pid) {
                    if (!isset($this->reqCache["lastProdId"])) {
                        $this->reqCache["lastProdId"] = trim($pid);
                    }
                    array_push($this->reqCache["listProdId"], trim($pid));
                    $res = true;
                }
            }
        }
        if ($res === true) {
            $this->addDebugLine("Products: " . var_export($this->reqCache["listProdId"], true));
            $this->addGAEvent("curtain|product-matched");
        }
        $this->reqCache["hasAnyProductTag"] = $res;
        return $res;
    }

    /**
     * This Method checks if the user has bought the product. This check is done by calling the SDk but
     * if only the teaser should be shown *like feed and search results) the functionality is the sames as usual
     * (having bought or not the teaser only will be shown) se we don't need to call the SDk for that
     *
     * @param  boolean $isFeed TRUE if this method is being called from a feed filter
     *                         (the_content_feed, the_excerpt_feed, etc.)
     * @return boolean TRUE if the user has bought or if its due to be paywalled
     */
    private function user_bought_content($isFeed = false)
    {
        $rType = $this->get_render_type($isFeed);
        switch ($rType) {
            case self::RENDER_FEED :
                $hasUserBought = ($this->paywalled_check() === false);
                break;
            case self::RENDER_SINGLE :
                $hasUserBought = $this->plenigo_check();
                break;
            case self::RENDER_SEARCH :
                $hasUserBought = ($this->paywalled_check() === false);
                break;
            default:
                $hasUserBought = ($this->paywalled_check() === false);
                break;
        }

        return $hasUserBought;
    }

    /**
     * Comodity method to call the paywalled check and trace some message
     *
     * @return boolean TRUE if the content needs to be paywalled, false otherwise
     */
    private function paywalled_check()
    {
        if (isset($this->reqCache["paywalledCheck"])) {
            return $this->reqCache["paywalledCheck"];
        }
        plenigo_log_message("LOOKING FOR PAYWALLED CHECK ONLY");
        $res = $this->plenigo_paywalled_content();
        $this->reqCache["paywalledCheck"] = $res;
        $this->addDebugLine("Basic paywall check: " . var_export($res, true));
        return $res;
    }

    /**
     * This methods calls the SDK and ask for the Managed product ID to check if the current logged in user
     * has bought the product. This assumes a previous login
     *
     * @return boolean TRUE if the SDK succeded to call the service and if the user has bought the product
     */
    private function plenigo_check()
    {
        global $post;
        if (isset($this->reqCache["plenigoCheck"])) {
            return $this->reqCache["plenigoCheck"];
        }
        plenigo_log_message("LOOKING FOR THE FULL PLENIGO SDK CHECK");
        $this->addDebugLine("Plenigo backend check");
        $sdk = PlenigoSDKManager::get()->getPlenigoSDK();
        $res = false;
        if (!is_null($sdk) && ($sdk instanceof \plenigo\PlenigoManager)) {


            plenigo_log_message("Checking if category is there");
            if (isset($this->reqCache["lastCatId"])) {
                $products = array($post->ID);
            } else {
                $products = $this->reqCache["listProdId"];
            }
            plenigo_log_message("Checking the prod id : " . print_r($products, true));
            $res = PlenigoSDKManager::get()->plenigo_bought($products);

            // If the user hasn't actually bought the product, check for free views
            if ($res === false) {
                plenigo_log_message("USER DIDNT BOUGHT THE PRODUCT, CHECKING FOR FREE VIEWS");
                $res = PlenigoSDKManager::get()->plenigo_has_free_views();
            } else {
                $this->addGAEvent("product|bought-visit");
            }
        }
        if ($res === true) {
            $this->addGAEvent("product|freeview-visit");
        }
        $this->reqCache["plenigoCheck"] = $res;
        $this->addDebugLine("Plenigo bought: " . var_export($res, true));
        return $res;
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
                $this->addDebugLine("Template from Theme: " + $fileName);
                return $themed_template;
            } else {
                plenigo_log_message("TEMPLATE FROM PLUGIN");
                $this->addDebugLine("Template from Plugin: " + $fileName);
                return dirname(__FILE__) . '/../plenigo_template/' . $fileName;
            }
        }

        return null;
    }

    /**
     * Calculates the renderer by calling status functions provided by Wordpress. This is mainly to detect
     * if the complete post is shown, a search result or even a embedded widget
     *
     * @param  boolean $isFeed TRUE if this method is being called from the_content_feed or the_excerpt_feed
     * @return int     One of the constants RENDER_FEED,RENDER_SINGLE,RENDER_SEARCH,RENDER_OTHER
     */
    private function get_render_type($isFeed = false)
    {
        if (isset($this->reqCache["renderType"])) {
            return $this->reqCache["renderType"];
        }
        if (is_feed() || $isFeed) {
            $this->addDebugLine("Render Type: FEED");
            $this->reqCache["renderType"] = self::RENDER_FEED;
            return self::RENDER_FEED;
        } else {
            if (is_singular() && is_main_query()) {
                $this->addDebugLine("Render Type: SINGLE POST");
                $this->reqCache["renderType"] = self::RENDER_SINGLE;
                return self::RENDER_SINGLE;
            } else {
                if (!is_singular()) {
                    $this->addDebugLine("Render Type: SEARCH RESULT");
                    $this->reqCache["renderType"] = self::RENDER_SEARCH;
                    return self::RENDER_SEARCH;
                } else {
                    $this->addDebugLine("Render Type: OTHER RENDER");
                    $this->reqCache["renderType"] = self::RENDER_OTHER;
                    return self::RENDER_OTHER;
                }
            }
        }
    }

    /**
     * This method extracts the teaser from the complete content of the post. This is achiecved by detecting the
     * MORE tag of Wordpress and returning the text prior to that. An optional flag allows to return the entire
     * content in the case that the content presented is alreeady a teaser and not the complete post
     * (happens in the RSS)
     *
     * @param  string  $content   The content to get the teaser from
     * @param  boolean $permisive set to TRUE to return the entire content if MORE tag not found
     * @return string  The teaser text from the user that has been set with the MORE tag
     */
    private function get_teaser_from_content($content, $permisive = false)
    {
        $res = '';
        $strBeforeMoreTag = array();
        $strMore = 'Read more...';

        $strBeforeMoreTag = stristr($content, self::MORE_SPLITTER, true);
        if ($strBeforeMoreTag !== false) {
            plenigo_log_message("MORE TAG FOUND");
            $this->addDebugLine("Teaser source: MORE TAG");
            $res = balanceTags($strBeforeMoreTag, true);
        } else {
            plenigo_log_message("MORE TAG NOT FOUND");
            $this->addDebugLine("Teaser source: ENTIRE POST");
            if ($permisive) {
                $this->addDebugLine("Permisive Teaser: TRUE");
                $res = balanceTags($content, true);
            }
        }

        return $res;
    }

    /**
     * Reads the template file received as parameter and then replaces the Plenigo Tags with the actual information
     * returning the contents as a string.
     *
     * @param  string $curtain_file The file path to get the curtain template
     * @return string the contents of the curtain to be appended to the teaser
     */
    private function get_curtain_code($curtain_file)
    {
        $strCoutain = 'ERROR:not found(' . $curtain_file . ')';
        if (!is_null($curtain_file)) {
            $strCoutain = file_get_contents($curtain_file);
            if ($strCoutain !== false) {
                $strCoutain = $this->replace_plenigo_tags($strCoutain);
            }
        }

        return $strCoutain;
    }

    /**
     * This methd replaces the Plenigo Tags (see bellow) with actuali information and functionality in order to create
     * a fully functional curtain with links and descriptions
     *
     * @param  string $html te curtain contents to replace the tags
     * @return string he contents of the curtain to be appended to the teaser
     */
    private function replace_plenigo_tags($html)
    {
        $sdk = PlenigoSDKManager::get()->getPlenigoSDK();

        $prodName = '*[ERROR check Product ID]';
        $prodPrice = '*[ERROR]';
        $prodDetails = '*[ERROR check Product ID]';
        $courtTitle = $this->options['curtain_title'];
        $courtMsg = $this->options['curtain_text'];
        $btnTitle = $this->options['curtain_buy'];
        $btnOnClick = "javascript:alert('This product has not been configured correctly!');";
        $btnStyle = "width:30%";
        $loginTitle = $this->options['curtain_login'];
        $loginOnClick = "javascript:alert('The login API doesn't work!');";
        $loginStyle = "width:30%";
        $custTitle = $this->options['curtain_custom_title'];
        $custOnClick = "javascript:window.location.href = '" . $this->options['curtain_custom_url'] . "';";
        $custStyle = "width:30%";

        if (!isset($this->options['use_login']) || ($this->options['use_login'] == 0 )) {
            $useOauthLogin = false;
        } else {
            $useOauthLogin = true;
        }

        //If logged in with plenigo
        $isLoggedIn = \plenigo\services\UserService::isLoggedIn();
        if ($isLoggedIn && isset($this->options['curtain_title_members']) && isset($this->options['curtain_text_members'])) {
            $courtTitle = $this->options['curtain_title_members'];
            $courtMsg = $this->options['curtain_text_members'];
        }

        $productData = null;
        if (!is_null($sdk) && ($sdk instanceof \plenigo\PlenigoManager)) {
            // creating a Plenigo-managed product
            $product = $this->get_product_checkout();
            // getting the CSRF Token
            $csrfToken = PlenigoSDKManager::get()->get_csrf_token();

            try {
                if (stristr($html, self::REPLACE_BUTTON_CLICK) !== false) {
                    // creating the checkout snippet for this product
                    $checkoutBuilder = new \plenigo\builders\CheckoutSnippetBuilder($product);

                    $coSettings = array('csrfToken' => $csrfToken);
                    if ($useOauthLogin) {
                        // this url must be registered in plenigo
                        $coSettings['oauth2RedirectUrl'] = $this->options['redirect_url'];
                        plenigo_log_message("url: " . $coSettings['oauth2RedirectUrl']);
                    }

                    // checkout snippet
                    $btnOnClick = $checkoutBuilder->build($coSettings);
                }
                if (stristr($html, self::REPLACE_PRODUCT_NAME) !== false ||
                    stristr($html, self::REPLACE_PRODUCT_PRICE) !== false ||
                    stristr($html, self::REPLACE_PRODUCT_DETAILS) !== false) {
                    // get product data
                    $productData = \plenigo\services\ProductService::getProductData($product->getId());
                }
                if (stristr($html, self::REPLACE_LOGIN_CLICK) !== false) {
                    // login snippet depending on OAuth
                    if ($useOauthLogin) {
                        $loginOnClick = $this->get_oauth_login();
                    } else {
                        $loginOnClick = $this->get_regular_login();
                    }
                }
            } catch (\Exception $exc) {
                plenigo_log_message($exc->getMessage() . ': ' . $exc->getTraceAsString(), E_USER_WARNING);
                error_log($exc->getMessage() . ': ' . $exc->getTraceAsString());
            }

            if (!is_null($productData) && ($productData instanceof \plenigo\models\ProductData)) {
                $prodName = $productData->getTitle();
                if ($productData->isPriceChosen()) {
                    $prodPrice = __('Choose payment!', self::PLENIGO_SETTINGS_GROUP);
                } else {
                    $prodPrice = $productData->getCurrency() . ' ' . sprintf("%06.2f", $productData->getPrice());
                }
                //If we should show Product details
                $prodDetails = '<table class="plenigo-product"><tr><td><b>' . $prodName . '</b></td>'
                    . '<td width="170" style="text-align: right;"><b>' . $prodPrice . '</b></td></tr></table>';
            }
        }

        $strHalf = "width:45%;";
        $strThird = "width:30%;";
        $strNone = "display:none;";
        $strEntire = "width:90%;";
        //Handling curtain modes
        if (!isset($this->options['curtain_mode']) || ($this->options['curtain_mode'] == 1 )) {
            //[BUY BUTTON] [LOGIN BUTTON]
            if ($isLoggedIn) {
                $btnStyle = $strEntire;
                $custStyle = $strNone;
                $loginStyle = $strNone;
            } else {
                $btnStyle = $strHalf;
                $custStyle = $strNone;
                $loginStyle = $strHalf;
            }
        }
        if (isset($this->options['curtain_mode']) && $this->options['curtain_mode'] == 2) {
            //[CUSTOM BUTTON] [LOGIN BUTTON]
            if ($isLoggedIn) {
                $btnStyle = $strNone;
                $custStyle = $strEntire;
                $loginStyle = $strNone;
            } else {
                $btnStyle = $strNone;
                $custStyle = $strHalf;
                $loginStyle = $strHalf;
            }
        }
        if (isset($this->options['curtain_mode']) && $this->options['curtain_mode'] == 3) {
            //[BUY BUTTON] [CUSTOM BUTTON] [LOGIN BUTTON]
            if ($isLoggedIn) {
                $btnStyle = $strHalf;
                $custStyle = $strHalf;
                $loginStyle = $strNone;
            } else {
                $btnStyle = $strThird;
                $custStyle = $strThird;
                $loginStyle = $strThird;
            }
        }
        if (isset($this->options['curtain_mode']) && $this->options['curtain_mode'] == 4) {
            //[CUSTOM BUTTON]
            $btnStyle = $strNone;
            $custStyle = $strEntire;
            $loginStyle = $strNone;
        }

        $html = str_ireplace(self::REPLACE_PLUGIN_DIR, plugins_url('', dirname(__FILE__)), $html);
        $html = str_ireplace(self::REPLACE_PRODUCT_NAME, $prodName, $html);
        $html = str_ireplace(self::REPLACE_PRODUCT_PRICE, $prodPrice, $html);
        $html = str_ireplace(self::REPLACE_PRODUCT_DETAILS, $prodDetails, $html);
        $html = str_ireplace(self::REPLACE_CURTAIN_TITLE, $courtTitle, $html);
        $html = str_ireplace(self::REPLACE_CURTAIN_MSG, $courtMsg, $html);
        $html = str_ireplace(self::REPLACE_BUTTON_TITLE, $btnTitle, $html);
        $html = str_ireplace(self::REPLACE_BUTTON_CLICK, $btnOnClick, $html);
        $html = str_ireplace(self::REPLACE_BUTTON_STYLE, $btnStyle, $html);
        $html = str_ireplace(self::REPLACE_LOGIN_TITLE, $loginTitle, $html);
        $html = str_ireplace(self::REPLACE_LOGIN_CLICK, $loginOnClick, $html);
        $html = str_ireplace(self::REPLACE_LOGIN_STYLE, $loginStyle, $html);
        $html = str_ireplace(self::REPLACE_CUSTOM_TITLE, $custTitle, $html);
        $html = str_ireplace(self::REPLACE_CUSTOM_CLICK, $custOnClick, $html);
        $html = str_ireplace(self::REPLACE_CUSTOM_STYLE, $custStyle, $html);

        return $html;
    }

    /**
     * This method creates and returns a login snippet for use with the curtain login button
     * 
     * @return string the resulting login snippet
     */
    private function get_regular_login()
    {
        $loginBuilder = new \plenigo\builders\LoginSnippetBuilder(null);
        return $loginBuilder->build();
    }

    /**
     * This method creates and returns a login snippet for use with the curtain login button 
     * but using the OAuth authentication
     * 
     * @return string the resulting login snippet
     */
    private function get_oauth_login()
    {
        $redirectUrl = $this->options['redirect_url'];
        $config = new \plenigo\models\LoginConfig($redirectUrl, \plenigo\models\AccessScope::PROFILE);
        $builder = new \plenigo\builders\LoginSnippetBuilder($config);
        return $builder->build();
    }

    /**
     * Creates a plenigo managed product with the last Product ID and/or the Cateogory ID . The Product ID comes 
     * from the TAG , but if the Category is given, it obtains it from the Current Post ID
     * 
     * @global WP_Post $post The Wordpress Post Object
     * @return \plenigo\models\ProductBase The Plenigo Product Object
     */
    public function get_product_checkout()
    {
        global $post;
        $prodID = null;
        $title = null;
        $catID = null;
        if (!isset($this->reqCache["lastCatId"])) {
            $prodID = $this->reqCache["lastProdId"];
        } else {
            $prodID = $post->ID;
            $title = $post->post_title;
            $catID = $this->reqCache["lastCatId"];
        }
        $res = new \plenigo\models\ProductBase($prodID, $title);
        if (!is_null($catID)) {
            $res->setCategoryId($catID);
        }
        return $res;
    }

    /**
     * Adds a line at the end of the debug checklist
     * 
     * @param string $row
     */
    private function addDebugLine($row = null)
    {
        $res = '';
        if (!is_null($row)) {
            if (!is_string($row) && !is_numeric($row)) {
                $res.=print_r($row, true);
            } else {
                $res.=$row;
            }
        }
        array_push($this->debugChecklist, $res);
    }

    /**
     * Outputs the debug checklist as a HTML comment for debugging purposes, then it clears the array
     */
    private function printDebugChecklist()
    {
        if (is_array($this->debugChecklist) && count($this->debugChecklist) > 0) {
            echo "<!-- *** Plenigo debug checklist ***\n";
            foreach ($this->debugChecklist as $debugRow) {
                echo "## - " . $debugRow . " \n";
            }
            echo "// *** Plenigo debug checklist *** -->";
        }
        $this->debugChecklist = array();
    }

    /**
     * Adds a line at the end of the GA event list
     * 
     * @param string $row the format is event|action
     */
    private function addGAEvent($row = null)
    {
        $res = '';
        if (!is_null($row)) {
            if (is_string($row) && stristr($row, '|') !== FALSE) {
                $res.=$row;
            }
        }
        array_push($this->gaEventList, $res);
    }

    /**
     * Outputs the Javascript representing the Google Analytics plugin and the event list for this page
     */
    public function printGoogleAnalytics()
    {
        $templateGAfile = $this->locate_plenigo_template("plenigo-ga-include.html");
        $strGAhtml = file_get_contents($templateGAfile);
        if ($strGAhtml !== FALSE) {
            $strGAfinal = $this->replace_ga_tags($strGAhtml);
            echo "\n" . $strGAfinal . "\n";
        }
    }

    /**
     * 
     * @param string $strGAhtml The HTML to be replaced
     */

    /**
     * Replaces the tags in the Google Analytics HTML with the actual values to configure GA
     * 
     * @param string $strGAhtml The HTML to be replaced
     * @return string the HTML with the tags replaced
     */
    public function replace_ga_tags($strGAhtml)
    {
        $res = '';
        if (isset($this->options['ga_code'])) {
            $strGAcode = trim($this->options['ga_code']);
            $strGAevents = '';
            $arrEventList = array_unique($this->gaEventList);
            foreach ($arrEventList as $value) {
                $arrEvent = explode("|", $value);
                $strGAevents.="\n_plenigo_ga('send', 'event', 'plenigo', '" . $arrEvent[0] . "', '" . $arrEvent[1] . "',{'nonInteraction': 1});";
            }

            $res = str_ireplace(self::REPLACE_GA_CODE, $strGAcode, $strGAhtml);
            $res = str_ireplace(self::REPLACE_GA_EVENTS, $strGAevents, $res);
        }
        return $res;
    }

}
