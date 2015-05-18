<?php

/*
  Copyright (C) 2014 plenigo

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
 * This class handles the entire content and allows to apply the plenigo paywall ruleset. It also uses the theme
 * templating system to provide a customizable look and feel for the plenigo paywall curtain (what you see if you
 * haven't paid for the article).
 * </b>
 *
 * @category WordPressPlugin
 * @package  plenigoPlugin
 * @author   Sebastian Dieguez <s.dieguez@plenigo.com>
 * @link     https://plenigo.com
 */
class PlenigoContentManager {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options = null;

    /**
     * Holds values for the SDK requests, so they are made just once per request
     */
    private $reqCache = array();

    /**
     * Holds the String[] to be rendered for debug checklist.
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

    /**
     * Priority in which plenigo will handle content. It has to be less than 10 
     * (for shortcode processing) but more than 1.
     */
    const PLENIGO_CONTENT_PRIO = 5;
    //Plenigo settings group
    const PLENIGO_SETTINGS_GROUP = 'plenigo';
    const PLENIGO_SETTINGS_NAME = 'plenigo_settings';
    const MORE_SPLITTER = '<span id="more-';
    const PLENIGO_SEPARATOR = '<!-- {{PLENIGO_SEPARATOR}} -->';
    //Plenigo settings
    const OPT_METERED_EXEMPT = 'plenigo_metered_exempt_tag';
    // Render types
    const RENDER_FEED = 0;
    const RENDER_SINGLE = 1;
    const RENDER_SEARCH = 2;
    const RENDER_OTHER = 3;
    //Replacement tags
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
    //NoScript
    const REPLACE_NS_TITLE = "<!--[NOSCRIPT_TITLE]-->";
    const REPLACE_NS_MESSAGE = "<!--[NOSCRIPT_MESSAGE]-->";
    // Teaser smart detection
    const TEASER_SHORTCODES_CONTAINER = "aesop_content,pl_checkout,pl_checkout_button,pl_renew";
    const TEASER_SHORTCODES_SINGLE = "aesop_quote";
    const TEASER_HTML_CONTAINER = "p,div,table";
    const TEASER_HTML_SINGLE = "";
    const CURTAIN_MODE_LB = 1;
    const CURTAIN_MODE_LC = 2;
    const CURTAIN_MODE_LCB = 3;
    const CURTAIN_MODE_C = 4;

    private $templateMap = array(
        self::RENDER_FEED => array(TRUE => null, FALSE => 'plenigo-curtain-feed.html'),
        self::RENDER_SINGLE => array(TRUE => null, FALSE => 'plenigo-curtain-single.html'),
        self::RENDER_SEARCH => array(TRUE => null, FALSE => 'plenigo-curtain-search.html'),
        self::RENDER_OTHER => array(TRUE => null, FALSE => 'plenigo-curtain-feed.html')
    );

    /**
     * Default constructor, called from the main php file
     */
    public function __construct() {
        add_filter('the_content', array($this, 'plenigo_handle_main_content'), self::PLENIGO_CONTENT_PRIO);
        add_filter('the_content_feed ', array($this, 'plenigo_handle_feed_content'), self::PLENIGO_CONTENT_PRIO);
        $this->options = get_option(self::PLENIGO_SETTINGS_NAME, array());

        add_action('wp_footer', array($this, 'plenigo_js_snippet'));
        add_action('wp_enqueue_scripts', array($this, 'add_scripts'));
        add_action('wp_head', array($this, 'add_metatags'), 0);
    }

    /**
     * Check if the curtain has to be rendered and injects a meta tag for search engine robots
     * 
     */
    public function add_metatags() {
        global $post;
        $paywalled = $this->paywalled_check();
        $rType = $this->get_render_type(is_feed());
        if ($paywalled === TRUE && $rType === self::RENDER_SINGLE) {
            echo PHP_EOL . '<meta name="robots" content="NOARCHIVE" />' . PHP_EOL . PHP_EOL;
        }
    }

    /**
     * Add Javascript/CSS imports
     */
    public function add_scripts() {
        wp_register_style('plenigo-curtain-css', plugins_url('plenigo_css/miniStopper.css', dirname(__FILE__)));
        wp_enqueue_style('plenigo-curtain-css');
    }

    /**
     * This is only for testing purposes, the snipet here allows to change the
     * baseURL of the JS and PHP SDKs at the same time
     */
    public function plenigo_js_snippet() {
        global $post;
        PlenigoSDKManager::get()->getPlenigoSDK();
        $isPaywalled = $this->plenigo_paywalled_content();

        if ($isPaywalled == TRUE && !isset($this->reqCache["listProdId"]) && !isset($this->reqCache["lastCatId"])) {
            plenigo_log_message("PRODUCT OR CATEGORY NOT FOUND!!!", E_USER_WARNING);
        }
        //Handling other pages than single post view
        $rType = $this->get_render_type(FALSE);
        //Checking if product has been bought
        $userBought = (PlenigoSDKManager::get()->plenigo_bought($this->reqCache["listProdId"]) === TRUE);
        //Checking if the user has free views
        $hasFreeViews = (PlenigoSDKManager::get()->plenigo_has_free_views() === TRUE);
        //Checking if the metered view is exempt by tag
        $tagExempt = $this->is_metered_exempt();
        // Check if the user can edit this post
        $canEdit = current_user_can('edit_post', $post->ID);

        $disableText = '';
        if ($canEdit == TRUE || $tagExempt == TRUE || $isPaywalled === FALSE || $userBought === TRUE || $hasFreeViews === FALSE || $rType !== self::RENDER_SINGLE) {
            $disableText = ' data-disable-metered="true" ';
        }
        $meteredURLText = '';
        if (isset($this->options['metered_url']) && filter_var($this->options['metered_url'], FILTER_VALIDATE_URL) !== FALSE) {
            $meteredURLText = ' data-metered-description-url="' . esc_url(trim($this->options['metered_url'])) . '" ';
        }

        $strNoScript = $this->getNoScriptTag();
        echo'<script type="application/javascript" '
        . 'src="' . PLENIGO_JSSDK_URL . '/static_resources/javascript/'
        . $this->options["company_id"] . '/plenigo_sdk.min.js" '
        . $disableText . $meteredURLText . '></script>' . $strNoScript;

        $this->printGoogleAnalytics();

        //Output the checklist
        $this->printDebugChecklist();
    }

    /**
     * This method redirects to the content handler for main , widget and search renderers
     *
     * @param  string $content the contents as it will be shown
     * @return string the content filtered if needed by the plenigo paywall
     */
    public function plenigo_handle_main_content($content) {
        return $this->plenigo_filter_content($content);
    }

    /**
     * This method redirects to the content handler and set the feed flag to determine that we are in a feed renderer
     * This method is particular to the Feed functionality (Atom, RSS, etc)
     *
     * @param  string $content the contents as it will be shown
     * @return string the content filtered if needed by the plenigo paywall
     */
    public function plenigo_handle_feed_content($content) {
        return $this->plenigo_filter_content($content, TRUE);
    }

    /**
     * This method handles the content itself, showing only what the author wants to show using the MORE tag
     * and then adds the plenigo curtain if not purchased. Otherwise shows the plain contents as usual.
     *
     * @param  string  $content the contents as it will be shown
     * @param  boolean $isFeed  TRUE if the method is being called from a FEED filter or not
     * @return string  the content filtered if needed by the plenigo paywall
     */
    private function plenigo_filter_content($content, $isFeed = FALSE) {
        global $post;
        if ($this->plenigo_paywalled_content()) {
            $curtain_code = '';
            plenigo_log_message("ITS PAYWALLED");
            $canEdit = current_user_can('edit_post', $post->ID);
            $hasBought = $this->user_bought_content($isFeed);
            $this->addDebugLine("Post ID:" . $post->ID);
            $this->addDebugLine("Editor visit: " . var_export($canEdit, TRUE));
            if (!$hasBought && !$canEdit) {
                $rType = $this->get_render_type($isFeed);
                $html_curtain = null;

                if (isset($this->templateMap[$rType][$hasBought])) {
                    $html_curtain = $this->templateMap[$rType][$hasBought];
                    $curtain_file = $this->locate_plenigo_template($html_curtain);
                }

                $curtain_code = $content;

                //If the blog is configured to show only excerpts in the RSS, then we let the content
                //go if paywalled and MORE tag not found, otherwise we don't show anything
                $showByDefault = FALSE;
                if (get_option('rss_use_excerpt ', 0) === 1) {
                    $showByDefault = TRUE;
                }

                switch ($rType) {
                    case self::RENDER_FEED :
                        plenigo_log_message("ITS A FEED");
                        $curtain_code = $this->plenigo_curtain($content, $curtain_file, $showByDefault);
                        break;
                    case self::RENDER_SINGLE :
                        plenigo_log_message("ITS A SINGLE");
                        $curtain_code = $this->plenigo_curtain($content, $curtain_file, FALSE);
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
                $curtain_code = $content;
            }
            $content = $curtain_code;
        }

        return $content;
    }

    /**
     * This function gets the teaser from the content and concatenates the generated plenigo curtain if any.
     * An optional parameter can be set to TRUE if we want to return the content anyways if no MORE tag
     * is found.
     *
     * @param  string  $content      The entire content of the post
     * @param  string  $curtain_file the filename of the template we will search on theme directories
     * @param  boolean $permisive    set to TRUE to return the entire content if more tag not found
     * @return string  the content to be displayed
     */
    private function plenigo_curtain($content, $curtain_file, $permisive = FALSE) {
        $teaser = $this->get_teaser_from_content($content, $permisive);
        $curtain_snippet = $this->get_curtain_code($curtain_file);
        return $teaser . $curtain_snippet;
    }

    /**
     * This method checks if the content is due to be paywalled by plenigo
     *
     * @return boolean TRUE if the content needs to be paywalled, FALSE otherwise
     */
    private function plenigo_paywalled_content() {
        $plenigoTagDB = (isset($this->options['plenigo_tag_db']) ? $this->options['plenigo_tag_db'] : '');
        $plenigoCatTagDB = (isset($this->options['plenigo_cat_tag_db']) ? $this->options['plenigo_cat_tag_db'] : '');

        // Sanitize the product cache
        if (!isset($this->reqCache["listProdId"])) {
            $this->reqCache["listProdId"] = array();
            $this->reqCache["listProdTag"] = array();
        }
        // Sanitize the category cache
        if (!isset($this->reqCache["listCatId"])) {
            $this->reqCache["listCatId"] = array();
            $this->reqCache["listCatTag"] = array();
        }

        //Prevent tag takes precedense
        $hasPreventTag = $this->hasPreventTag();
        if ($hasPreventTag) {
            $this->addGAEvent("curtain|curtain-prevented");
            return FALSE;
        }

        // Do not paywall if nothing is configured
        if (trim($plenigoTagDB) === '' && trim($plenigoCatTagDB) === '') {
            plenigo_log_message("NO TAGS CONFIGURED");
            return FALSE;
        }

        // If Paywall is disabled, we dont check anything
        if (PlenigoSDKManager::get()->isPayWallEnabled()) {

            //Checking for category IDs
            $hasAnyCatTag = $this->hasAnyCategoryTag();
            if ($hasAnyCatTag) {
                $this->addGAEvent("curtain|category-matched");
                return TRUE;
            } else {
                //Checking for Product IDs
                $hasAnyProdTag = $this->hasAnyProductTag();
                if ($hasAnyProdTag) {
                    $this->addGAEvent("curtain|product-matched");
                }
                return $hasAnyProdTag;
            }
        }

        plenigo_log_message("TAG NOT FOUND");

        return FALSE;
    }

    /**
     * This method checks for category IDs that need to be added to the category list 
     * so it can be checked for bought products. Returns FALSE if there is no tag 
     * found on the article that reflects a tag in the plenigo settings
     * 
     * @return boolean Returns TRUE if a tag has been found and the category list is in the cache
     */
    public function hasAnyCategoryTag() {
        if (isset($this->reqCache["hasAnyCategoryTag"])) {
            return $this->reqCache["hasAnyCategoryTag"];
        }
        $catTagList = (isset($this->options['plenigo_cat_tag_db']) ? $this->options['plenigo_cat_tag_db'] : '');
        $res = FALSE;
        //TAGS WITH CATEGORY IDS
        $rowSplit = explode("\n", $catTagList);
        if ($rowSplit === FALSE || count($rowSplit) == 0) {
            $rowSplit = array($catTagList);
        }
        foreach ($rowSplit as $tagRow) {
            $strTag = explode("->", $tagRow);
            $arrToken = array();
            //Obtain the {slug}
            preg_match('/{(.*?)}/', $strTag[0], $arrToken);
            if ($strTag !== FALSE && count($strTag) == 2 && count($arrToken) == 2 && has_tag($arrToken[1])) {
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
                        $this->reqCache["lastCatTag"] = trim($arrToken[1]);
                    }
                    array_push($this->reqCache["listCatId"], trim($cid));
                    array_push($this->reqCache["listCatTag"], trim($arrToken[1]));
                    $res = TRUE;
                }
            }
        }
        if ($res === TRUE) {
            $this->addDebugLine("Categories: " . var_export($this->reqCache["listCatId"], TRUE));
        }
        $this->reqCache["hasAnyCategoryTag"] = $res;
        return $res;
    }

    /**
     * Check if the prevent tags is present, and returns the flag indicating it
     * 
     * @return boolean TRUE if the prevent tag is present and thus the curtain shouldn't
     */
    public function hasPreventTag() {
        if (isset($this->reqCache["hasPreventTag"])) {
            return $this->reqCache["hasPreventTag"];
        }
        $prevTag = (isset($this->options['plenigo_prevent_tag']) ? $this->options['plenigo_prevent_tag'] : '');
        $res = FALSE;
        $arrToken = array();
        preg_match('/{(.*?)}/', $prevTag, $arrToken);
        if (count($arrToken) == 2 && has_tag($arrToken[1])) {
            plenigo_log_message("Prevent TAG! TAG=" . $prevTag);
            $this->addDebugLine("Prevent Tag: " . $prevTag);

            $res = TRUE;
        }

        if ($res === TRUE) {
            $this->addDebugLine("Curtain display prevented by Tag");
        }
        $this->reqCache["hasPreventTag"] = $res;
        return $res;
    }

    /**
     * This method checks for Product IDs that need to be added to the product list 
     * so it can be checked for bought products. Returns FALSE if there is no tag 
     * found on the article that reflects a tag in the plenigo settings
     * 
     * @return boolean Returns TRUE if a tag has been found and the product list is in the cache
     */
    private function hasAnyProductTag() {
        if (isset($this->reqCache["hasAnyProductTag"])) {
            return $this->reqCache["hasAnyProductTag"];
        }
        $prodTagList = (isset($this->options['plenigo_tag_db']) ? $this->options['plenigo_tag_db'] : '');
        $res = FALSE;
        //TAGS WITH PRODUCT IDS
        $rowSplit = explode("\n", $prodTagList);
        if ($rowSplit == FALSE || count($rowSplit) == 0) {
            $rowSplit = array($prodTagList);
        }
        foreach ($rowSplit as $tagRow) {
            $strTag = explode("->", $tagRow);
            $arrToken = array();
            //Obtain the {slug}
            preg_match('/{(.*?)}/', $strTag[0], $arrToken);
            if ($strTag !== FALSE && count($strTag) == 2 && count($arrToken) == 2 && has_tag($arrToken[1])) {
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
                        $this->reqCache["lastProdTag"] = trim($arrToken[1]);
                    }
                    array_push($this->reqCache["listProdId"], trim($pid));
                    array_push($this->reqCache["listProdTag"], trim($arrToken[1]));
                    $res = TRUE;
                }
            }
        }
        if ($res === TRUE) {
            $this->addDebugLine("Products: " . var_export($this->reqCache["listProdId"], TRUE));
        }
        $this->reqCache["hasAnyProductTag"] = $res;
        return $res;
    }

    /**
     * This Method checks if the user has bought the product. This check is done by calling the SDK but
     * if only the teaser should be shown (like feed and search results) the functionality is the sames as usual
     * (having bought or not the teaser only will be shown) se we don't need to call the SDK for that
     *
     * @param  boolean $isFeed TRUE if this method is being called from a feed filter
     *                         (the_content_feed, the_excerpt_feed, etc.)
     * @return boolean TRUE if the user has bought or if its due to be paywalled
     */
    private function user_bought_content($isFeed = FALSE) {
        $rType = $this->get_render_type($isFeed);
        switch ($rType) {
            case self::RENDER_SINGLE :
                $hasUserBought = $this->plenigo_check();
                break;
            case self::RENDER_FEED :
            case self::RENDER_SEARCH :
            default:
                $hasUserBought = ($this->paywalled_check() === FALSE);
                break;
        }

        return $hasUserBought;
    }

    /**
     * Comodity method to call the paywalled check and trace some message
     *
     * @return boolean TRUE if the content needs to be paywalled, FALSE otherwise
     */
    private function paywalled_check() {
        if (isset($this->reqCache["paywalledCheck"])) {
            return $this->reqCache["paywalledCheck"];
        }
        plenigo_log_message("LOOKING FOR PAYWALLED CHECK ONLY");
        $res = $this->plenigo_paywalled_content();
        $this->reqCache["paywalledCheck"] = $res;
        $this->addDebugLine("Basic paywall check: " . var_export($res, TRUE));
        return $res;
    }

    /**
     * This methods calls the SDK and ask for the managed product ID to check if the current logged in user
     * has bought the product. This assumes a previous login
     *
     * @return boolean TRUE if the SDK succeded to call the service and if the user has bought the product
     */
    private function plenigo_check() {
        global $post;
        if (isset($this->reqCache["plenigoCheck"])) {
            return $this->reqCache["plenigoCheck"];
        }
        plenigo_log_message("LOOKING FOR THE FULL PLENIGO SDK CHECK");
        $this->addDebugLine("Plenigo backend check");
        $sdk = PlenigoSDKManager::get()->getPlenigoSDK();
        //Checking if the metered view is exempt by tag
        $tagExempt = $this->is_metered_exempt();
        $res = FALSE;
        if (!is_null($sdk) && ($sdk instanceof \plenigo\PlenigoManager)) {
            plenigo_log_message("Checking if category is there");
            if (isset($this->reqCache["lastCatId"])) {
                $products = array($post->ID);
            } else {
                $products = $this->reqCache["listProdId"];
            }
            plenigo_log_message("Checking the prod id : " . print_r($products, TRUE));
            $res = PlenigoSDKManager::get()->plenigo_bought($products);

            // If the user hasn't actually bought the product, check for free views
            if ($res === FALSE) {
                if ($tagExempt === FALSE) {
                    plenigo_log_message("USER DIDNT BOUGHT THE PRODUCT, CHECKING FOR FREE VIEWS");
                    $res = PlenigoSDKManager::get()->plenigo_has_free_views();
                } else {
                    plenigo_log_message("FREE VIEWS PREVENTED BY EXEMPT TAG");
                    $this->addGAEvent("product|freeview-exempt");
                }
            } else {
                $this->addGAEvent("product|bought-visit");
            }
        }
        if ($res === TRUE) {
            $this->addGAEvent("product|freeview-visit");
        }
        $this->reqCache["plenigoCheck"] = $res;
        $this->addDebugLine("Plenigo bought: " . var_export($res, TRUE));
        return $res;
    }

    /**
     * This method locates the file in theme directories if overriden, or gets it from the template directory
     *
     * @param  string $fileName name of the file that's needed and will be located
     * @return string The located filename with full path in order to read the file, NULL if there was a problem
     */
    private function locate_plenigo_template($fileName) {
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
    private function get_render_type($isFeed = FALSE) {
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
     * This method extracts the teaser from the complete content of the post. This is achieved by detecting the
     * MORE tag of Wordpress and returning the text prior to that. An optional flag allows to return the entire
     * content in the case that the content presented is alreeady a teaser and not the complete post
     * (happens in the RSS)
     *
     * @param  string  $content   The content to get the teaser from
     * @param  boolean $permisive set to TRUE to return the entire content if MORE tag not found
     * @return string  The teaser text from the user that has been set with the MORE tag
     */
    private function get_teaser_from_content($content, $permisive = FALSE) {
        $res = '';
        $strBeforeMoreTag = stristr($content, self::MORE_SPLITTER, TRUE);
        $strBeforeSeparatorTag = stristr($content, self::PLENIGO_SEPARATOR, TRUE);
        if ($strBeforeSeparatorTag !== FALSE) {
            plenigo_log_message("PLENIGO SEPARATOR FOUND");
            $this->addDebugLine("Teaser source: PLENIGO SEPARATOR");
            $res = balanceTags($strBeforeSeparatorTag, TRUE);
        } else {
            if ($strBeforeMoreTag !== FALSE) {
                plenigo_log_message("MORE TAG FOUND");
                $this->addDebugLine("Teaser source: MORE TAG");
                $res = balanceTags($strBeforeMoreTag, TRUE);
            } else {
                plenigo_log_message("MORE TAG NOT FOUND");
                $this->addDebugLine("Teaser source: ENTIRE POST");
                if ($permisive) {
                    $this->addDebugLine("Permisive Teaser: TRUE");
                    $res = balanceTags($content, TRUE);
                } else {
                    $res = $this->specialTeaserSupport($content);
                }
            }
        }
        return $res;
    }

    /**
     * Reads the template file received as parameter and then replaces the plenigo tags with the actual information
     * returning the contents as a string.
     *
     * @param  string $curtain_file The file path to get the curtain template
     * @return string the contents of the curtain to be appended to the teaser
     */
    private function get_curtain_code($curtain_file) {
        $strCoutain = 'ERROR:not found(' . $curtain_file . ')';
        if (!is_null($curtain_file)) {
            $strCoutain = file_get_contents($curtain_file);
            if ($strCoutain !== FALSE) {
                $strCoutain = $this->replace_plenigo_tags($strCoutain);
            }
        }

        return $strCoutain;
    }

    /**
     * This method replaces the plenigo tags (see bellow) with actual information and functionality in order to create
     * a fully functional curtain with links and descriptions
     *
     * @param  string $html the curtain contents to replace the tags
     * @return string the contents of the curtain to be appended to the teaser
     */
    private function replace_plenigo_tags($html) {
        $sdk = PlenigoSDKManager::get()->getPlenigoSDK();

        $prodName = '*[ERROR check Product ID]';
        $prodPrice = '*[ERROR]';
        $prodDetails = '*[ERROR check Product ID]';
        $courtTitle = $this->options['curtain_title'];
        $courtMsg = $this->options['curtain_text'];
        //Get buy text of the tag DB
        $buyTitle = $this->get_buy_text(isset($this->options['curtain_buy']) ? $this->options['curtain_buy'] : '');
        $buyOnClick = "javascript:alert('This product has not been configured correctly!');";
        $loginStyle = "width:30%";
        $custStyle = "width:30%";
        $buyStyle = "width:30%";
        $loginTitle = $this->options['curtain_login'];
        $loginOnClick = "javascript:alert('The login API doesn't work!');";
        if (!isset($this->reqCache["lastCatId"])) {
            $curtainMode = (isset($this->options['curtain_mode'])) ? $this->options['curtain_mode'] : self::CURTAIN_MODE_LB;
            $custTitle = $this->options['curtain_custom_title'];
            $custOnClick = "javascript:window.location.href = '" . $this->options['curtain_custom_url'] . "';";
        } else {
            $curtainMode = (isset($this->options['curtain_cat_mode'])) ? $this->options['curtain_cat_mode'] : self::CURTAIN_MODE_LB;
            $custTitle = $this->options['curtain_cat_custom_title'];
            $custOnClick = "javascript:window.location.href = '" . $this->options['curtain_cat_custom_url'] . "';";
        }

        if (!isset($this->options['use_login']) || ($this->options['use_login'] == 0 )) {
            $useOauthLogin = FALSE;
        } else {
            $useOauthLogin = TRUE;
        }

        //If logged in with plenigo
        $isLoggedIn = \plenigo\services\UserService::isLoggedIn();
        if ($isLoggedIn && isset($this->options['curtain_title_members']) && isset($this->options['curtain_text_members'])) {
            $courtTitle = $this->options['curtain_title_members'];
            $courtMsg = $this->options['curtain_text_members'];
        }

        $productData = null;
        if (!is_null($sdk) && ($sdk instanceof \plenigo\PlenigoManager)) {
            // creating a plenigo-managed product
            $product = $this->get_product_checkout();
            // getting the CSRF Token
            $csrfToken = PlenigoSDKManager::get()->get_csrf_token();

            try {
                if (stristr($html, self::REPLACE_BUTTON_CLICK) !== FALSE) {
                    // creating the checkout snippet for this product
                    $checkoutBuilder = new \plenigo\builders\CheckoutSnippetBuilder($product);

                    $coSettings = array('csrfToken' => $csrfToken);
                    if ($useOauthLogin) {
                        // this url must be registered in plenigo
                        $coSettings['oauth2RedirectUrl'] = $this->options['redirect_url'];
                        plenigo_log_message("url: " . $coSettings['oauth2RedirectUrl']);
                    }

                    // checkout snippet
                    $buyOnClick = $checkoutBuilder->build($coSettings);
                }
                if (stristr($html, self::REPLACE_PRODUCT_NAME) !== FALSE ||
                    stristr($html, self::REPLACE_PRODUCT_PRICE) !== FALSE ||
                    stristr($html, self::REPLACE_PRODUCT_DETAILS) !== FALSE) {
                    // get product data
                    $productData = \plenigo\services\ProductService::getProductData($product->getId());
                }
                if (stristr($html, self::REPLACE_LOGIN_CLICK) !== FALSE) {
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
        if ($curtainMode == self::CURTAIN_MODE_LB) {
            //[LOGIN BUTTON] [BUY BUTTON]
            if ($isLoggedIn) {
                $buyStyle = $strEntire;
                $custStyle = $strNone;
                $loginStyle = $strNone;
            } else {
                $buyStyle = $strHalf;
                $custStyle = $strNone;
                $loginStyle = $strHalf;
            }
        }
        if ($curtainMode == self::CURTAIN_MODE_LC) {
            //[LOGIN BUTTON] [CUSTOM BUTTON]
            if ($isLoggedIn) {
                $buyStyle = $strNone;
                $custStyle = $strEntire;
                $loginStyle = $strNone;
            } else {
                $buyStyle = $strNone;
                $custStyle = $strHalf;
                $loginStyle = $strHalf;
            }
        }
        if ($curtainMode == self::CURTAIN_MODE_LCB) {
            //[LOGIN BUTTON] [CUSTOM BUTTON] [BUY BUTTON] 
            if ($isLoggedIn) {
                $buyStyle = $strHalf;
                $custStyle = $strHalf;
                $loginStyle = $strNone;
            } else {
                $buyStyle = $strThird;
                $custStyle = $strThird;
                $loginStyle = $strThird;
            }
        }
        if ($curtainMode == self::CURTAIN_MODE_C) {
            //[CUSTOM BUTTON]
            $buyStyle = $strNone;
            $custStyle = $strEntire;
            $loginStyle = $strNone;
        }
        $html = str_ireplace(self::REPLACE_PLUGIN_DIR, plugins_url('', dirname(__FILE__)), $html);
        $html = str_ireplace(self::REPLACE_PRODUCT_NAME, $prodName, $html);
        $html = str_ireplace(self::REPLACE_PRODUCT_PRICE, $prodPrice, $html);
        $html = str_ireplace(self::REPLACE_PRODUCT_DETAILS, $prodDetails, $html);
        $html = str_ireplace(self::REPLACE_CURTAIN_TITLE, $courtTitle, $html);
        $html = str_ireplace(self::REPLACE_CURTAIN_MSG, $courtMsg, $html);
        $html = str_ireplace(self::REPLACE_BUTTON_TITLE, $buyTitle, $html);
        $html = str_ireplace(self::REPLACE_BUTTON_CLICK, $buyOnClick, $html);
        $html = str_ireplace(self::REPLACE_BUTTON_STYLE, $buyStyle, $html);
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
    private function get_regular_login() {
        $loginBuilder = new \plenigo\builders\LoginSnippetBuilder(null);
        return $loginBuilder->build();
    }

    /**
     * This method creates and returns a login snippet for use with the curtain login button 
     * but using the OAuth authentication
     * 
     * @return string the resulting login snippet
     */
    private function get_oauth_login() {
        $redirectUrl = $this->options['redirect_url'];
        $config = new \plenigo\models\LoginConfig($redirectUrl, \plenigo\models\AccessScope::PROFILE);
        $builder = new \plenigo\builders\LoginSnippetBuilder($config);
        return $builder->build();
    }

    /**
     * Creates a plenigo managed product with the last Product ID and/or the Cateogory ID . The Product ID comes 
     * from the TAG , but if the category is given, it obtains it from the Current Post ID
     * 
     * @global WP_Post $post The Wordpress post object
     * @return \plenigo\models\ProductBase The plenigo product object
     */
    private function get_product_checkout() {
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
    private function addDebugLine($row = null) {
        $res = '';
        if (!is_null($row)) {
            if (!is_string($row) && !is_numeric($row)) {
                $res.=print_r($row, TRUE);
            } else {
                $res.=$row;
            }
        }
        array_push($this->debugChecklist, $res);
    }

    /**
     * Outputs the debug checklist as a HTML comment for debugging purposes, then it clears the array
     */
    private function printDebugChecklist() {
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
    private function addGAEvent($row = null) {
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
    private function printGoogleAnalytics() {
        $templateGAfile = $this->locate_plenigo_template("plenigo-ga-include.html");
        $strGAhtml = file_get_contents($templateGAfile);
        if ($strGAhtml !== FALSE) {
            $strGAfinal = $this->replace_ga_tags($strGAhtml);
            echo "\n" . $strGAfinal . "\n";
        }
    }

    /**
     * Returns the &lt;noscript&gt; tag found in the plugin or theme directory
     * 
     * @return string the HTML to render or empty if no template has been found
     */
    private function getNoScriptTag() {
        $templateNoScriptFile = $this->locate_plenigo_template("plenigo-noscript-msg.html");
        $strNoScripthtml = file_get_contents($templateNoScriptFile);
        if ($strNoScripthtml !== FALSE) {
            $strNoScriptFinal = $this->replace_noscript_tags($strNoScripthtml);
            return $strNoScriptFinal;
        }
        return "";
    }

    /**
     * Replaces the template tags for the NOSCRIPT overlay
     * 
     * @param string $htmlText The template HTML text
     * @return string The template HTML text with the replacement tags or empty if the NOSCRIPT functionality is disabled
     */
    public function replace_noscript_tags($htmlText) {
        $res = '';
        if (isset($this->options['noscript_enabled']) && $this->options['noscript_enabled'] === 1) {
            $strTitle = (isset($this->options['noscript_title'])) ? $this->options['noscript_title'] : __("You need JavaScript",
                    self::PLENIGO_SETTINGS_GROUP);
            $strMessage = (isset($this->options['noscript_message'])) ? $this->options['noscript_message'] : __("In order to provide you with the best experience, "
                    . "this site requires that you allow JavaScript to run. "
                    . "Please correct that and try again.", self::PLENIGO_SETTINGS_GROUP);
            $res = str_ireplace(self::REPLACE_NS_TITLE, trim(wp_kses_post($strTitle)), $htmlText);
            $res = str_ireplace(self::REPLACE_NS_MESSAGE, trim(wp_kses_post(wpautop($strMessage))), $res);
        }
        return $res;
    }

    /**
     * Replaces the tags in the Google Analytics HTML with the actual values to configure GA
     * 
     * @param string $strGAhtml The HTML to be replaced
     * @return string the HTML with the tags replaced
     */
    public function replace_ga_tags($strGAhtml) {
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

    /**
     * This method allow to strip a teaser tag from the content if it is starting with  one of the specified tags
     * 
     * @param string $content the actual post content
     * @return string The teaser or blank if nothing is found
     */
    private function specialTeaserSupport($content = null) {
        $res = '';
        if (!is_null($content) && is_string($content) && trim($content) !== '') {
            $trimmedContent = trim($content);
            $fstWord = strtok($trimmedContent, ' '); //get the very first work, it should be [aesop_ or any other tag
            //if its a shortcode, check for allowed shortcodes and capture the teaser
            if (substr($fstWord, 0, 1) == '[') {
                $this->addDebugLine("SHORTCODE");
                $tag = strtolower(trim(substr($fstWord, 1)));
                //First lets catch container tags
                if (in_array($tag, $this->resolveArray(self::TEASER_SHORTCODES_CONTAINER))) {
                    $needle = "[/" . $tag . "]";
                    $this->addDebugLine("TAG:" . $tag . " NEEDLE:" . $needle);
                    return $this->getTeaserText($trimmedContent, $needle);
                }
                if (in_array($tag, $this->resolveArray(self::TEASER_SHORTCODES_SINGLE))) {
                    $needle = "]";
                    $this->addDebugLine("TAG:" . $tag . " NEEDLE:" . $needle);
                    return $this->getTeaserText($trimmedContent, $needle);
                }
            }
            //if its a html tag, check for allowed html tags and capture the teaser
            if (substr($fstWord, 0, 1) == '<') {
                $this->addDebugLine("HTML");
                $tag = strtolower(trim(substr($fstWord, 1)));
                //First lets catch container tags
                if (in_array($tag, $this->resolveArray(self::TEASER_HTML_CONTAINER))) {
                    $needle = "/" . $tag . ">";
                    $this->addDebugLine("TAG:" . $tag . " NEEDLE:" . $needle);
                    return $this->getTeaserText($trimmedContent, $needle);
                }
                if (in_array($tag, $this->resolveArray(self::TEASER_HTML_SINGLE))) {
                    $needle = "/>";
                    $this->addDebugLine("TAG:" . $tag . " NEEDLE:" . $needle);
                    return $this->getTeaserText($trimmedContent, $needle);
                }
            }
        }

        return $res;
    }

    /**
     * Search for the needle and returns the begining of the content with the needle attached at the end
     * 
     * @param string $content
     * @param string $needle
     * @return string
     */
    private function getTeaserText($content, $needle) {
        $pos = stristr($content, $needle, TRUE);
        if ($pos !== FALSE) {
            $res = $pos . $needle;
            return $res;
        }
        return '';
    }

    /**
     * Sanitizes a string array to return an array, empty or with a single value as special cases
     * 
     * @param string $stringArray
     * @return array
     */
    private function resolveArray($stringArray) {
        $separator = ",";
        $arr = array();
        if (trim($stringArray) !== '') {
            if (stripos($stringArray, $separator) !== FALSE) {
                $arr = explode($separator, $stringArray);
            } else {
                $arr[0] = $stringArray;
            }
        }
        return $arr;
    }

    /**
     * Checks if the metered view is exempt by tag
     * 
     * @return boolean TRUE if the post is exempt of metered views
     */
    public function is_metered_exempt() {
        $res = FALSE;
        $optExempt = isset($this->options[self::OPT_METERED_EXEMPT]) ? $this->options[self::OPT_METERED_EXEMPT] : NULL;
        if (!is_null($optExempt) && $optExempt !== '') {
            $arrToken = array();
            preg_match('/{(.*?)}/', $optExempt, $arrToken);
            if (count($arrToken) == 2 && has_tag(trim($arrToken[1]))) {
                $res = TRUE;
            }
        }
        return $res;
    }

    /**
     * Obtains the BUY button text in the case there is a product or a category tag 
     * associated to a buy text, if nothing is found then it returns a given default value.
     * 
     * @param string $defaultValue the default value to return if no tag is found in the DB
     * @return string The final Buy button text
     */
    private function get_buy_text($defaultValue = '') {
        $res = $defaultValue;
        $tag = '';
        if (isset($this->reqCache["lastCatTag"])) {
            $tag = $this->reqCache["lastCatTag"];
            $this->addDebugLine("Tag from Category:" . $tag);
        }
        if (isset($this->reqCache["lastProdTag"])) {
            $tag = $this->reqCache["lastProdTag"];
            $this->addDebugLine("Tag from Product:" . $tag);
        }
        $buyTextTagList = (isset($this->options['curtain_buy_text_db']) ? $this->options['curtain_buy_text_db'] : '');
        //TAGS WITH PRODUCT IDS
        $rowSplit = explode("\n", $buyTextTagList);
        if ($rowSplit == FALSE || count($rowSplit) == 0) {
            $rowSplit = array($buyTextTagList);
        }
        foreach ($rowSplit as $tagRow) {
            if (stripos($tagRow, "->") === FALSE) {
                continue;
            }
            $strTag = explode("->", $tagRow);
            $arrToken = array();
            //Obtain the {slug}
            preg_match('/{(.*?)}/', $strTag[0], $arrToken);
            if ($strTag !== FALSE && count($strTag) == 2 && count($arrToken) == 2 && $arrToken[1] == $tag) {
                $res = $strTag[1];
                $this->addDebugLine("Buyt Button Text from tag: " . $tag . ' ==> ' . $res);
                break;
            }
        }
        return $res;
    }

}
