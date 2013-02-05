=== Sell Media ===

Contributors: endortrails, zanematthew
Donate link: http://graphpaperpress.com/plugins/sell-media/
Tags: commerce, digital downloads, download, downloads, e-commerce, paypal, photography, sell digital, sell download, selling, sell photos, sell videos, sell media, stock photos
Requires at least: 3.4
Tested up to: 3.5-beta
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sell, license and protect images, videos, audio and pdf's on your site.

== Description ==

[Sell Media](http://graphpaperpress.com/plugins/sell-media/) is a WordPress plugin that allows anyone to sell, license and protect images, videos, audio and pdf's on their self-hosted WordPress site.

[Learn more](http://graphpaperpress.com/plugins/sell-media/) or track the progress of [Sell Media on Github](https://github.com/graphpaperpress/sell-media).

The Sell Media plugin allows you to:

*   Sell any kind of media file that you upload into WordPress, including images, videos, audio files and pdf's.
*   License your media for specific usages, including commercial, editorial, or personal usages.
*   Create you own stock photo or video site.
*   Protect file uploads.
*   Accept payments via Paypal.
*   Earn passive income by selling and licensing your media.

Sell Media extensions will soon include:

* Sell photo reprints via Adorama Pix or self-fulfillment
* Newsletter integration with SendGrid, Mailchimp, AWeber, Campaign Monitor
* Process credit card transactions using Stripe integration
* Coupon Code system
* Watermark images
* Backup media uploads to Amazon S3

== Installation ==

1. Activate the plugin.
2. Visit Sell Media -> Settings and configure the options.
3. Insert the **required Sell Media shortcodes** onto your preferred Pages (see FAQ section).
4. Visit Sell Media -> Licenses and add or configure your default licenses for new uploads.
5. Visit Sell Media -> Add New and upload an image, video, audio file or pdf for sale.

= IMPORTANT: Final Step For PayPay Setup =

You must setup your Paypal IPN URL for Sell Media to work.

1. Login to your PayPal account.
2. Mouse over the Profile menu option and then click on the Selling Tools menu option.
3. Scroll down to "Getting paid and managing my risk" and click the Update link beside "Instant payment notifications".
4. Paste your Paypal IPN URL onto that page in Paypal. Your Paypal IPN URL is located on the Sell Media Settings page.

== Frequently Asked Questions ==

= What are shortcodes and how do I use them? =

Shortcodes are small snippets of code that when added to a Post, Page or Widget add functionality to your site. You must add the following shortcodes to your preferred Pages to use Sell Media:

* **Checkout Shortcode** - (REQUIRED) Create a page called "Checkout" and add this shortcode to it: [sell_media_checkout]
* **Thanks Shortcode** - (REQUIRED) Create a page called "Thanks" and add this shortcode to it: [sell_media_thanks]
* **Buy Button Shortcode** - (OPTIONAL) Used for displaying specific items for sale: [sell_media_buy_button]
* **Search Form Shortcode** - (OPTIONAL) Used to display a search form exclusively for searching items for sale within Sell Media: [sell_media_searchform]

= How do I show my checkout cart? =

1. Create a page called "Checkout" and add this shortcode to the page: [sell_media_checkout]
2. Visit Sell Media -> Settings and select the Checkout page you created above to the "Checkout Page" option.

= How do I show an item available for sale? =

You have two options:

1. After adding a new item for sale on the Sell Media -> Add New page, copy and paste the shortcode at the bottom of the screen into a Post, Page or Text Widget. This shortcode will embed the image and an "Add to Cart" button below the image. The shortcode looks something like this: [sell_media id="257" text="Purchase" style="button" color="blue" size="medium"]
2. Each item you add for sale also has a dedicated URL. Click the View Item button after saving your first Sell Media item. You could then add the link to that specific item to one of your Menus on Appearance -> Menus -> Custom Menu Item.

= How do I bulk upload images for sale? =

1. Click Sell Media -> Add Bulk
2. Click "Upload or Select Images"
2. Simply drag and drop your files into the box that appears, or click Select Files to choose a file from your computer to upload. Please keep in mind that the drag and drop uploader only works in browsers with HTML5 upload support such as the latest versions of Chrome, Firefox, or Safari. Other browsers will still show the Select Files button or the basic browser uploader form.
4. This item will be added as a new entry in Sell Media. By default, the newly created Sell Media item will inherit the sizes, prices and licenses that you chose on Sell Media -> Settings. You can modify the price and available licenses on the Sell Media tab by editing each individual item.

= How do I display a gallery of images for sale? =

Sell Media includes a new "Collections" taxonomy, which you can see on the right side of the screen when adding a new item to Sell Media. Assign each item to a specific Collection and the items will be displayed on that specific collection's archive page. You can then link to the collection like this: http://example.com/collection/my-collection-name/. A list of collecitons also shows up on the Appearance -> Menus page so you can add them to any menu.

= How do I password protect an item? =

The Password Protection option is located in the Publish box when editing a Sell Media item. Click the Visibility - Public - Edit link, select Password Protected, type in a password and click Save.

= How do I password protect a collection? =

*This feature will only be available in version 1.0*

Click Sell Media -> Collections -> Add New and fill in the password protect field.

= How do I hide a collection from being listed on archive pages? =

Click Sell Media -> Collections -> Add New and check the "Hide" option.

= How do I increase the maximum upload size in WordPress? =

Depending on the web hosting company you choose and the package you select, each of you will see maximum file upload limit on your Media Uploader page in WordPress. For some it is as low as 2MB which is clearly not enough for large images or videos. You can increase this by doing one of the follwing:

1. Theme Functions File - There are cases where we have seen that just by adding the following code in the theme function’s file, you can increase the upload size:

    `@ini_set( 'upload_max_size', '64M' );
    @ini_set( 'post_max_size', '64M');
    @ini_set( 'max_execution_time', '300' );`

2. Create or Edit an existing PHP.INI file - In most cases if you are on a shared host, you will not see a php.ini file in your directory. If you do not see one, then create a file called php.ini and upload it in the root folder. In that file add the following code:

    `upload_max_filesize = 64M
    post_max_size = 64M
    max_execution_time = 300`

3. htaccess Method - Some people have tried using the htaccess method where by modifying the .htaccess file in the root directory, you can increase the maximum upload size in WordPress. Open or create the .htaccess file in the root folder and add the following code:

    `php_value upload_max_filesize 64M
    php_value post_max_size 64M
    php_value max_execution_time 300
    php_value max_input_time 300`

Again, it is important that we emphasize that if you are on a shared hosting package, these techniques may not work. In that case, you would have to contact your web hosting provider to increase the limit for you.

= Transactions are not posting. Why? =

Please visit the Add Media -> Settings -> Payments page and double check all of your settings. Also, if you are using Paypal, you need to make sure you have [added your IPN Listener URL to Paypal](https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_admin_IPNSetup).

= What are license types? =

The Sell Media plugin helps you to create and assign different licenses and prices for each image, video or audio file based on the anticipated usage of the media. For example: If a large company wants to purchase one of your images for a billboard, you should charge one price for commercial usage, charge another for editorial, and so on.

= Does the plugin work for a WordPress Network =

It only works on the primary blog. While it will work on other blogs, file uploads will not be protected. Why? Because WordPress stores uploads in a "virtual" directory of blogs.dir, server side file protection using .htaccess doesn't work on virtual directories.

== Screenshots ==

1. The Shopping Cart
2. Single Item Template
3. Add New Item
4. Payments History
5. Available Extensions (coming soon)

== Developers ==

= Actions =
Example for adding a message above the cart:
`function sell_media_above_cart_function() {

    print '<p>This message will show up above the cart on the cart popup. You could include a copyright message or links to your terms of service.</p>';

}
add_action( 'sell_media_above_cart', 'sell_media_above_cart_function' );`

Example for adding a message below the cart:
`function sell_media_below_cart_function() {

    print '<p>This message will show up below the cart on the cart popup. You could include a copyright message or links to your terms of service.</p>';

}
add_action( 'sell_media_below_cart', 'sell_media_below_cart_function' );`

Action hooks available:
* sell_media_above_cart - Above the cart
* sell_media_below_cart - Below the cart
* sell_media_cart_below_licenses - Between license and price on cart
* sell_media_menu_hook - settings.php - Use for adding new submenu pages
* sell_media_register_settings_hook - settings.php - Use for registering new settings/options
* sell_media_settings_above_general_section_hook - settings.php - Above tables on settings page
* sell_media_settings_below_general_section_hook - settings.php - Below tables on settings page

== Upgrade Notice ==

 * Double check your Sell Media Settings after upgrading

== Changelog ==
= 1.2 =
* Added full Featured Image support
* Bug fixes

= 1.1 =
* Instruction changes
* Fixing settings to work better with extensions

= 1.0.9 =
* Added bulk uploader
* Added size variants
* Code enhancments
* Bug fixes

= 1.0.8 =
* Bug fixes
* Code improvements

= 1.0.7 =
* Bug fixes
* Added option for customer notification
* 3.5 media cart fix

= 1.0.6 =
* License descriptions now show on the checkout page and option boxes when hovered
* Fixed bug when Attachments are no longer marked for sale.
* Fixed issue when Item is emptied from trash bin
* Fixed bug where editor appeared on other Add New pages.
* Adding hook for single theme.

= 1.0.5 =
* Fixed transients
* Fixed issue where button was not showing on cart

= 1.0.4 =
* Added Loading gif on purchase dialog
* Users can now manually upload a file to the sell_media folder
  then add the path to the file on the "Add Item" page.
* Better handling of template redirection
* Item description now has a Text Editor
* Added shortcode sell_media_all_items
* Added shortcode sell_media_download_list
* Zip file support
* Added bulk "Sell This" option
* Bug fixes

= 1.0.3 =
* Added Google Charts on Reports page
* Added Paypal IPN instructions on Settings
* Max-width fix for Firefox on Sell Media archives
* Post Type Taxonomy archives conflict fix
* Download file fix

= 1.0.2 =
* Plugin Settings save bugfix

= 1.0.1 =
* Added action hooks
* Default to Live Mode, not Test Mode
* License calculation on Edit License works
* Removed email testing code
* Additional support for image, video, document mime types
* Checkout page removing item bugfix
* Tabs for Settings
* Use submit_button()

= 1.0 =
* Public release

= 0.1-beta2 =
* Permalinks flushed on plugin activation

= 0.1-beta =
* First public beta
