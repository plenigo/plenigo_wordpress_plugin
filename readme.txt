=== Plenigo ===
Contributors: Sebastian Dieguez <s.dieguez@plenigo.com>
Tags: paywall, e-commerce, Ecommerce, paid content software, subscriptions, newspaper, media, pay-per-read, pay, plugin, donate, money, transaction, bank, visa, mastercard, credit, debit, card, widget, give, pay what you want, plenigo, payment
Requires at least: 4.0.0
Tested up to: 4.2.2
Stable tag: 1.2.2
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

To use the plenigo plugin you must [register a company account at plenigo](https://www.plenigo.com/company/registration "plenigo company registration").

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
This button scheme is used within the curtain if a category tag is added to an article. If a product an d a category tag is added to an article this scheme will be used.

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
Type of the product going to be sold. This is importent for the tax rate that will be selected.

== Frequently Asked Questions ==

= Available Shortcodes =

Creates a checkout button with optional title and optional css class.
If the product is bought, the button won't e shown and the contents
will be shown instead.
Note: You can use shotcodes and all formating inside the button shortcode

[pl_checkout prod_id="{{PRODUCT ID}}" title="{{BUTTON TITLE}}"
class="{{CSS CLASS}}"]{{DISPLAY TEXT AFTER PURCHASE}}[/pl_checkout]

Same as pl_checkout but it will show the checkout button even if the
product is bought.

[pl_checkout_button prod_id="{{PRODUCT ID}}" title="{{BUTTON TITLE}}"
class="{{CSS CLASS}}"][/pl_checkout_button]

Same as pl_checkout but it will set the "Subcription Renew" flag for
this purchase.

[pl_renew prod_id="{{PRODUCT ID}}" title="{{BUTTON TITLE}}"
class="{{CSS CLASS}}"]{{DISPLAY TEXT AFTER PURCHASE}}[/pl_renew]

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
