=== Plenigo ===
Contributors: Sebastian Dieguez <s.dieguez@plenigo.com>
Tags: paywall, e-commerce, Ecommerce, paid content software, subscriptions, newspaper, media, pay-per-read, pay, plugin, donate, money, transaction, bank, visa, mastercard, credit, debit, card, widget, give, pay what you want, plenigo, payment
Requires at least: 4.0.0
Requires PHP: 7.0
Tested up to: 5.7
Stable tag: 1.12.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

plenigo offers a full-featured e-commerce SaaS platform for digital products such as text, video, audio.

== Description ==
= What do I get with plenigo? =

Basically plenigo includes all you need to run your paid content offer. No matter if your offer is a Video on Demand subscription or a pay-per-view live stream, a metered model for your digital news page or magazine. With plenigo you can run any offer you like, created just with a few mouse clicks. Don’t believe it? Just try our BASIC account, with € 0 monthly fee. 

= What are the basic features? =

The plenigo team aims to include all necessary functionality you need to create the best possible platform for paid content offers. This does include a user-friendly checkout process that does not derive your users but keeps them on your page. Currently six different payment methods are included and can be integrated just by clicking a checkbox. In the penigo backend you can easily create your offer and just add e.g. „Premium“ to the tag box while managing your posts - now you have created your offer. There is much more, please have a look at „features" below. 

= Pricing =

plenigo offers four different pricing plans starting with 0 € fix fee, so it is risk free to test. You can easily upgrade once your offer is established. If you what to start with even more features, just pick one.  

= What do I need? =

Beside your Wordpress account, your plenigo merchant account and the offer you created, you need to sign up with the payment method you would like to use, e.g. PayPal. Why do you have to sign up for your own account: This way you can be 100% sure nobody but you has access to your earnings. plenigo only handles transfer, access, clients etc. but never has access to the money you earn. Currently plenigo offers credit card payments, SEPA direct debit, PayPal, Sofortueberweisung and bill payments. 

= Are there any restrictions? =

plenigo highly values author’s rights, so we have to make sure only people sell through plenigo who are allowed to. I addition, we respect all legal requirements and ask you to confirm that you are not violating any laws. 

= Data protection =

Data security is of highest value for plenigo, so we do anything in our power to keep data save. For detailed questions pls. contact our data security team at dataprotection@plenigo.com. 

= "Who owns the customer" = 

Your customer is your customer. You have full access to all information we gather for you and you can export this information any time. Once a customer signs in with plenigo, he approves plenigo can hand over the customer data to you as a publisher. This does, off course, include only data collected in context of your account.  

= These are plenigo’s basic features = 

= Access Management =

* Seamless checkout layer
* Customizable curtains 
* Customizable checkout
* OAuth2 Single Sign On 

= User Management =

* Manage all customers and transactions
* Cancel transactions 
* Manage subscriptions 

= Product Management =

* Subscription
* Promotion Period
* Single Products 
* Access Passes
* IP Restrictions
* Time Restrictions

= Payment =

* Fully implemented payment methods
* Credit card, Direct Debit, Bill Payment, PayPal, Sofortüberweisung and many more
* Absolutely safe: plugin your own account and payments will be transferred directly to you
* Recurring payments 

= Billing =

* Create your own branded bill form with easy to use wizard
* Automatic billing for subscriptions

= Analytics =

* See, how your subscriptions, transactions, customer database etc. grows

== Installation ==

For installation instructions visit [help pages](https://developer.plenigo.com/plugins/wordpress/ "Wordpress plugin").

= Pre Requirements =

* PHP version must be 5.4 or higher
* PHP cURL
* PHP mcrypt

= General Section =

**Test Mode**
Use the test mode for testing the integration and production mode for real payments.

**Company ID**
The company id provided to you by the plenigo backend after registration of a company account.

**Company Private Key**
The company private key provided to you by the plenigo backend after registration of a company account.

**Notify non-JavaScript user**
If you are using metered views or using the plenigo user login users with deactivated JavaScript can get negative site impressions. In case of metered views deactivated JavaScript means there is no paywall at all for metered view article so the user can access them without limit. We recommend to enabled this setting.

**non-JavaScript overlay title**
Title that is shown to the user in the overlay that is shown to users with deactivated JavaScript.

**non-JavaScript overlay message**
Message that is shown to the user in the overlay that is shown to users with deactivated JavaScript.

**Google Analytics ID**
If you insert your Google Analytics ID here you will get events about the delivered curtains, etc.

= OAuth2 Login Section =

**Use OAuth2 Login**
This is the base decision about your usermanagement. If you decide to use plenigo as OAuth2 Login provider all users are managed by the plenigo plugin and shadow users are created in WordPress. This way all users already registered at plenigo can login without new registration. Also the users can use the same account for payments and for writing comments, etc.
After enabling this you also need to insert the plenigo widget into your page. 

**Enable Wordpress Login**
If you select plenigo as your authentication provider you can decide if users should still see the regular WordPress login link or only see the login button via plenigo. (The later is recommended.)

**Override WP Profile data**
Decide which system is the leading system. If you allow users to modify their data also within WordPress the user data can vary and you won't get the latests data from the plenigo system transfered into your WordPress system.

**OAuth redirect URL**
This url should be set to identify the page that should handle the OAuth2 redirect result. In theory every page can do this, but after processing the user will be redirected again. So it is recommended to select a page here that can be rendered very fast, e.g. the wp-login page.

**URL After Login**
If you want the user to get redirected to a special URL after login you can define it here. If you don't insert an URL here the user will stay on the same page after login.

= Premium Content Section =

The plenigo plugin controls which article is paid content by the use of WordPress tags. So before you add mappings in this section you have to

1. Create WordPress tags that identify your payments.
2. Create a product within the plenigo product section. (This step is optional if you only want to sell single articles.)

**Premium Content Products**
The tag field offers auto completion for all existing WordPress tags. The product ids can be obtained by the plenigo backend product management. You can add multiple product ids to a single tag by separating them with a ','.
In case of the screenshot the tag payment is associated with a monthly subscription represented by its product id.

**Premium Content Categories**
The tag field offers auto completion for all existing WordPress tags. The category ids can be obtained by the plenigo backend product management. You can add multiple category ids to a single tag by separating them with a ','.
In case of the screenshot the tag buyButton is associated with a plenigo category that has a price of 2.99€. So every article that this tag is associated with will be buyable as a single product for the price of 2.99€

**Prevent Payment Tag**
If you add a tag here and add this tag to an article that is already marked with a tag that marks the article as paid content this decision will be reverted and the article will be free available again.

= Metered Views Section =

**Metered Views switch**
Metered views can be managed within the plenigo backend. If you disable metered views in the plenigo backend you should also disable them here to prevent some useless redirects that could happen to a user.

**Metered explain URL**
Metered views are often new to users. So you can provide an URL here where you explain the principle to them. This URL will automatically added to the metered view flag.

**Metered exemption tag**
With the tag defined here you can exclude articles from metered views so that the content is only available to paying customers. 

= Curtain Customization Section =

The curtain is "the heart" of your paywall. It will be shown to the user to motivate him paying. In this section you can modify the curtain. If you are an advanced user you can also overwrite the curtain template to get a completely customized curtain.

**Curtain Title**
The title of your curtain shown to the user.
 
**Curtain Message**
The message of your curtain shown to the user.

**Curtain Title (for memebers)**
This curtain title is shown to users that are already logged in but don't have paid yet.

**Curtain Message (for memebers)**
This curtain message is shown to users that are already logged in but don't have paid yet.

**Curtain Button Scheme**
This button scheme is used within the curtain if a product tag is added to an article.

**Curtain Button Scheme (Category tag)**
This button scheme is used within the curtain if a category tag is added to an article. If a product and a category tag is added to an article this scheme will be used.

**BUY Button Text**
Text that is shown on the buy button.

**LOGIN Button Text**
Text that is shown on the login button. This button can be used to provide existing users that already bought the content an easy way to log in.

**CUSTOM Button Text**
The custom button is a button that doesn't have logic implemented within. It only contains a link. This way you can redirect the user e.g. to a subscription selection page.
In this field you set the title for this button.

**CUSTOMER Button URL**
The URL the custom button should link to.

**CUSTOM Button Text (Category tag)**
In this field you set the title for the button within the category scheme.

**CUSTOM Button URL (Category tag)**
The URL the custom button in the category scheme should link to.

**Buy Button Text (based on tag)**
You can define different button texts for the buy button defined by the tags provided. If two tags with different button texts are provided for the same article the first tag is choosen.

= WooCommerce Section =

**Use WooCommerce Payment Gateway**
Enable/Disable the WooCommerce functionality of the plenigo plugin

**Order Title Format**
Order title format to use.

**Product Type**
Type of the product going to be sold. This is important for the tax rate that will be selected.

== Frequently Asked Questions ==

= Help =

To get help visit [help pages](https://developer.plenigo.com/plugins/wordpress/ "Wordpress plugin").

= Available Shortcodes =

**Checkout Button**
Creates a checkout button with optional title and optional css class.
If the product is bought, the button won't e shown and the contents
will be shown instead.
Note: You can use shortcodes and all formatting inside the button shortcode

[pl_checkout prod_id="{{PRODUCT ID}}" title="{{BUTTON TITLE}}"
class="{{CSS CLASS}}"]{{DISPLAY TEXT AFTER PURCHASE}}[/pl_checkout]

**Checkout Button (persistent)**
Same as pl_checkout but it will show the checkout button even if the
product is bought.

[pl_checkout_button prod_id="{{PRODUCT ID}}" title="{{BUTTON TITLE}}"
class="{{CSS CLASS}}"][/pl_checkout_button]

**Failed Payments button**
Same as pl_checkout_button but it will trigger the failed payments list
instead of checking out a particular product.

[pl_failed title="{{BUTTON TITLE}}" class="{{CSS CLASS}}"][/pl_failed]

**Subscription renewal button**
Same as pl_checkout but it will set the "Subscription Renew" flag for
this purchase.

[pl_renew prod_id="{{PRODUCT ID}}" title="{{BUTTON TITLE}}"
class="{{CSS CLASS}}"]{{DISPLAY TEXT AFTER PURCHASE}}[/pl_renew]

**Show content or Hide content based on purchases**
PRO TIP: What if you want to do a Welcome page that knows what the customer 
purchased and/or refer to other product that may be interested in?
No problem! Just create a page and use this neat shortcodes (superpowers if you
ask me) to show content if the product has been bought! We do the rocket science.

TO SHOW CONTENT: 

[pl_content_show prod_id="<Some plenigo Product ID>"]This content is shown if the 
Product has been bought. Thank your customer and encourage to 
buy other products[/pl_content_show]

TO HIDE CONTENT:

[pl_content_hide prod_id="<Some plenigo Product ID>"]We don't show and invitation 
to buy the product if it has been already purchased. Don't annoy your customers
[/pl_content_hide]

Ok, combine this with purchase buttons and the sky is the limit!

**Plenigo User Profile replacement**
To render a complete user profile with the current data from plenigo, just
create a nuew Page (i.e.: /PlenigoProfile ) and put the Shortcode below to 
render a nice (and customizable) user profile.

[pl_user_profile]Sorry the user is not logged in to plenigo[/pl_user_profile]

After that copy the URL of the newly cerated page and paste it in the Profile URL
setting at the Plenigo Login section.

**Separator vs More tag**
This hidden HTML tag will let plenigo know where the plugin should trim
the contents of the post and show the payment curtain. This allows to
manually slice the post without interferring with the "More..."
separator, native Wordpress tag.

<!-- {{PLENIGO_SEPARATOR}} -->

== Screenshots ==
1. Extensive configuration options

/assets/screenshot-1.png

2. Freely configurable curtain

/assets/screenshot-2.png

== Changelog ==
= 1.12.0 - WP updated plenigo URLs

= 1.11.0 - WP updated plenigo URLs

= 1.10.0 - WP version compatibility

= 1.8.0 - Added Analytics Support
- new plenigo-SDK
- new field for feed handling

= 1.7.9 - Added Analytics Support

= 1.7.8 - Fixed Displaying Categories with quotes

= 1.7.7 - Fixed Translations

= 1.7.6 - Updated dependencies =
- New plenigo PHP SDK fixing potential bugs with some PHP versions
- Now you can use categories, to control the paywall

= 1.7.5 - Updated dependencies =
- New plenigo PHP SDK fixing potential bugs with some PHP versions

= 1.7.4 - Updated dependencies =
- New plenigo PHP SDK fixing potential bugs with some PHP versions

= 1.7.3 - Updated dependencies =
- Upgraded plenigo PHP SDK version

= 1.7.2 - Improved stability =
- Upgraded plenigo PHP SDK version

= 1.7.1 - Improved stability =
- Added stability improvements

= 1.7.0 - Added new snippet types =
- Updated SDK
- Added additional snippet types to create more customized profile pages

= 1.6.5 - New parameters on checkout button =
- Updated SDK
- Added Source/Target URL and Affiliate ID to the checkout shortcode (and TinyMCE button) to allow send those values to the checkout process

= 1.6.4 - Fix incorrect snippet button =
- Fixed problem with snippets button on tinyMCE not generating the correct shortcode

= 1.6.3 - Fix incorrect snippet calls =
- Fixed problem with snippets not showing correctly

= 1.6.2 - Embed plenigo on your site =
- New Snippet shortcode/Editor button to put parts of plenigo workflow screens on your site (idealy pages) like Order status, User profile information, etc.

= 1.6.1 - App ID Administration =
- Got a mobile app for your site? Checkout our Mobile SDKs for iOS and Android! Want your user to create Mobile App IDs for your product and redeem them in you app? No problem just create a post/page and put the shortcode *[pl_mobile_admin]* in it. Voila!

= 1.6.0 - Time for some updates =
- Updated the SDK
- Attempt to start the session earlier to primer some missleading Themes that create output before the plugin is loaded. Bad Art Monkey, no cookie for you.

= 1.5.8 - Bug fixes =
- Fixed problem with incorrectly configured plugins or themes

= 1.5.7 - plenigo in a language near you =
- Updated english, spanish and german translations.

= 1.5.6 - Bug fixes =
- Added multiple product id support for **pl_content_show** and **pl_content_hide**

= 1.5.5 - Bug fixes =
- Fixed strange behavior with add_settings_error

= 1.5.4 - Bug fixes =
- Fixed short codes

= 1.5.3 - Bug fixes =
- Fixed internationalization

= 1.5.2 - Improvements (and new year's resolutions) =
- Fixed formatting issues, check the login widget!

= 1.5.1 - Fixed version number =
- Fixed version number

= 1.5.0 - Some presents for this holidays! = 
- Changed all of our JavaScript snippets to "text/javascript" for compatibility with old IE versions.
- New shortcode! **pl_content_show** and **pl_content_hide** to customize content display for bought products
- New shortcode! **pl_user_profile** to show a (themeable) user profile report and a link to the plenigo account settings
- Added a setting to change the Profile URL in the login widget to show a particular page instead the WP profile

= 1.4.9 - Awesome Greetings for your awesome new users! = 
- Added a Redirect URL for new users (registering using plenigo or already registered with plenigo).

= 1.4.8 - Paving the road of the future and covering some road bumps too! = 
- Update SDK with support for new functionality
- Fixed a problem with subscription renewal buttons not beeing shown on certain scenarios

= 1.4.7 - To provide better support for you! = 
- Update SDK to the new version 1.5.0 which uses the more secure API v2 from plenigo.

= 1.4.6 - To provide better support for you! = 
- Improved Debugging checklist
- Better documentation
- More reliable check of product and category matching

= 1.4.5 - Swifty and in shape = 
- Updated SDK: Connection and response timeoiut set to 10 secs
- More accurate documentarion

= 1.4.4 - Where is my missing image? =
- Fixed: There was a bug with the separator parsing routine that actually deleted image tags just after the separator.

= 1.4.3 - Get help! =
- Added help pages.

= 1.4.2 - It's MY order you got there! =
- If customers started an order (cart) without logging in, the order wasnt associated to the customer and then lost as annonymous, not anymore.

= 1.4.1 - Beware of advanced features! =
- Some customers wanted more information on the operation of the plugin without breaking the look & feel of their site. You got it, be warned!
- Plenty of improvements
- OAuth working with the WooCommerce stuff
- Now you can redeem past orders! if you purchased them of course, just go to your customer profile
- Many bug fixes client and server side

= 1.4.0 - New Product types! =
- Updated SDK
- WooCommerce now have new product types

= 1.3.0 - Sorry Sir, your credit card was rejected.... =
- Improved: Updated PHP SDK for supporting the failed payment flag
- Feature: New button to provide the users with a way to check their failed payments, so they can take care of those problems easily from your site!
- Improved: TinyMCE buttons now have tooltips, aren't the icons enough explanation? Aren't they?

= 1.2.3 - Peek A Boo, I see you . . . and you =
- Avoid sending the default PageView to Google Analytics to prevent duplicated measurements. Demographic stuff should be measured as well

= 1.2.2 - Lover for category of products =
- Improved: Updated PHP SDK
- Fixed: Editors were seeing the metered view ticker
- Fixed: Now smoothly adding settings defaults for new settings, so our friends updating the plugin get some sort of values before actually going into the setting screen and saving their own.
- Feature: Now new preferences for category-tagged products, subscription and Buy button (the latter with configurable text based on which category)

= 1.2.1 - Don't want to forget =
- Improved: Now if you login using plenigo, the WordPress Session will not expire
- Improved: Updated PHP SDK

= 1.2.0 - A new hope =
- Feature: Now you can set a post tag to be a "metered views prevention tag"
- Feature: Now we are WordPress 4.2.0 compatible
- Improved: Updated PHP SDK
- Improved: Moved metered settings to the new "metered views" section
- Fixed: A problem with returning URLs after loging in from the curtain
- Fixed: A problem where not being loggedout from plenigo when loggedout from Wordpress via the login screen
- Fixed: A problem with checking "bought" condition in some scenarios

= 1.1.32 - WooCommerce Payment Gateway =
- Feature: Now you can use plenigo as a WooCommerce Payment Gateway! No further setup required! Altough you can customize the order title to be shown in the customers bills. Start selling your products right away!

= 1.1.31 - Fixed deployment script =
- Fixed: Problems with deployment script

= 1.1.30 - Fixed release problems =
- Fixed: Version 1.1.29 was not released properly

= 1.1.29 - Fixes and Improvements = 
- Improved: a better CDN with less tracking cookies, nice!
- Feature: Now you can customize a URL to explain about metered views
- Fixed: Some warnings that were resulting fatal to som of our customers
- Fixed: Some error catching realted issues

= 1.1.28 - Validation problems = 
- Fixed: Validation errors in settings

= 1.1.27 - Hot fix release =
- Fixed: Some interface breaking stuff, data is ok. Sorry about that!

= 1.1.26 - Google bot magic =
- Feature: Whenever the paywall is shown, tell Google bot not to store the page in its archive, nice one.

= 1.1.25 - no-Javascript overlay =
- Feature: Plenigo works best with Javascript enable. Now we provide you a nice overlay you can customize (or event theme it yourself) to show your users that the best experience is with Javascript enabled. 

= 1.1.24 - Teaser customization =
- Feature: Now you can customize how much content you want to show before the curtain is displayed. This takes precedence over the More Tag and doesn't have its side effects.

= 1.1.23 - New cool freebie tag! =
- Feature: Now you can create and configure a tag to prevent the payment curtain to appear. This allows you to give freebies from your site even if they have the payment tags installed. Great for giveaways and special discounts.

= 1.1.22 - Aesop and other plugin support =
- Feature: Now supporting Aesop tags to be shown before showing the curtain
- Fixed: a possible bug involving the footer tag in the HTML template
- Note: now the content processing has more priority, please check your templates to avoid problems in the HTML

= 1.1.21 - Fixes for URL redirect =
- Fixed a problem with URL parameters being stored as a return URL

= 1.1.20 - Bug fixes and improvements =
- Fixed a problem where in some cases a user would see a post even if metered views limit is reached

= 1.1.19 - Allow no redirection =
- Feature: Leave empty the login redirect URL to return to the same page after login
- Fixed a problem where in some cases you could edit a post but still get paywalled

= 1.1.18 =
- Fixed version

= 1.1.17 =
- Improved readme

= 1.1.16 - New curtain =
- Feature: New, responsive curtain design
- Feature: New button for Subscription Renew
- Feature: New option to allow plenigo to Override profile data with plenigo data, or to allow Wordpress users to change it
- Fixed a problem with paragraphs inside the curtain text

= 1.1.15 - Disable Metered =
- Feature: Disable metered checks from the Settings screen
- Feature: Google Analytics for the curtain events
- Fix: Updated PHP SDK
- Optimization fixes

= 1.1.14 - Category support =
- First Public Wordpress version
