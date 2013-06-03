Sell Media
==========

Description
-----------

[Sell Media](http://graphpaperpress.com/plugins/sell-media/) is a WordPress plugin that allows anyone to sell, license and protect images, videos, audio and pdf's on their self-hosted WordPress site.

[Learn more](http://graphpaperpress.com/plugins/sell-media/) or track the progress of [Sell Media on Github](https://github.com/graphpaperpress/sell-media).

The Sell Media plugin allows you to:

*   Sell any kind of media file that you upload into WordPress, including images, videos, audio files and pdf's.
*   License your media for specific usages, including commercial, editorial, or personal usages.
*   Create you own stock photo or video site.
*   Protect file uploads.
*   Accept payments via Paypal.
*   Earn passive income by selling and licensing your media.

Take Sell Media to the next level with these powerful extensions:

* [Sell photo reprints](http://graphpaperpress.com/?download=reprints-self-fulfillment)
* [Watermark your images](http://graphpaperpress.com/?download=watermark)
* [Newsletter integration with Mailchimp](http://graphpaperpress.com/?download=mailchimp)
* [Advanced Search](http://graphpaperpress.com/?download=advanced-search)
* [Sales Commissions](http://graphpaperpress.com/?download=commissions)
* [And many more](http://graphpaperpress.com/downloads/category/extensions/)

Installation
------------

1. Activate the plugin.
2. Visit Sell Media -> Settings and configure the options.
3. Insert the **required Sell Media shortcodes** onto your preferred Pages (see FAQ section).
4. Visit Sell Media -> Licenses and add or configure your default licenses for new uploads.
5. Visit Sell Media -> Add New and upload an image, video, audio file or pdf for sale.


PayPal
------
Login to your PayPal account, mouse over the Profile menu option and then click on the Selling Tools menu option. When page loads, scroll down to the "Getting paid and managing my risk" and click the Update link beside "Instant payment notifications". That is where you put in the listener URL provided in the Sell Media setup.


Frequently Asked Questions
--------------------------

I have 5k+ photos I would like to sell, can Sell Media handle this?
---

* Sell Media is a plugin for WordPress and WordPress can easily handle hundreds or thousands of files. There maybe limits based on your web hosting provider and you will need to manually manage each Item.

My file is 500MB+ in size but users cannot download the file after purchasing?
---

Check with your hosting provide on your download limits. Sell Media does not provide any type of file splitting service.

What are shortcodes and how do I use them?
---

Shortcodes are small snippets of code that when added to a Post, Page or Widget add functionality to your site. You must add the following shortcodes to your preferred Pages to use Sell Media:

* **Checkout shortcode** - (REQUIRED) Create a page called "Checkout" and visit the settings page (general tab) and set "Checkout Page" to the page that contains this shortcode: `[sell_media_checkout]`
* **Thanks shortcode** - (REQUIRED) Create a page called "Thanks" and visit the settings page (general tab) and set "Thanks Page" to the page that contains this shortcode: `[sell_media_thanks]`
* **Buy button shortcode** - (OPTIONAL) Used for displaying specific items for sale: `[sell_media_item]`
* **Search form shortcode** - (OPTIONAL) Used to display a search form exclusively for searching items for sale within Sell Media: `[sell_media_searchform]`
* **All items shortcode** - (OPTIONAL) Displays all (or a certain collection) items in a grid view: `[sell_media_all_items collection="type-your-collection-name-here"]`
* **Download list shortcode** - (OPTIONAL) List logged in users downloads: `[sell_media_download_list]`

How do I show my checkout cart?
---

1. Create a page called "Checkout" and add this shortcode to the page: `[sell_media_checkout]`
2. Visit Sell Media -> Settings and select the Checkout page you created above to the "Checkout Page" option.

How do I show an item available for sale?
---

You have two options:

1. After adding a new item for sale on the Sell Media -> Add New page, copy and paste the shortcode at the bottom of the screen into a Post, Page or Text Widget. This shortcode will embed the image and an "Add to Cart" button below the image. The shortcode looks something like this: `[sell_media id="257" text="Purchase" style="button" color="blue" size="medium"]`
2. Each item you add for sale also has a dedicated URL. Click the View Item button after saving your first Sell Media item. You could then add the link to that specific item to one of your Menus on Appearance -> Menus -> Custom Menu Item.

How do I bulk upload images for sale?
---

1. Click Sell Media -> Add Bulk
2. Click "Upload or Select Images"
2. Simply drag and drop your files into the box that appears, or click Select Files to choose a file from your computer to upload. Please keep in mind that the drag and drop uploader only works in browsers with HTML5 upload support such as the latest versions of Chrome, Firefox, or Safari. Other browsers will still show the Select Files button or the basic browser uploader form.
4. This item will be added as a new entry in Sell Media. By default, the newly created Sell Media item will inherit the sizes, prices and licenses that you chose on Sell Media -> Settings. You can modify the price and available licenses on the Sell Media tab by editing each individual item.

How do I display a gallery of images for sale?
---

Sell Media includes a new "Collections" taxonomy, which you can see on the right side of the screen when adding a new item to Sell Media. Assign each item to a specific Collection and the items will be displayed on that specific collection's archive page. You can then link to the collection like this: http://example.com/collection/my-collection-name/. A list of collecitons also shows up on the Appearance -> Menus page so you can add them to any menu.

How do I password protect an item?
---

The Password Protection option is located in the Publish box when editing a Sell Media item. Click the Visibility - Public - Edit link, select Password Protected, type in a password and click Save.

How do I hide a collection?
---

Click Sell Media -> Collections -> Click "Edit" next to the Collection you want to hide, check the checkbox "Hide" click save.

How do I hide a collection from being listed on archive pages?
---

Click Sell Media -> Collections -> Add New and check the "Hide" option.

How do I increase the maximum upload size in WordPress?
---

Depending on the web hosting company you choose and the package you select, each of you will see maximum file upload limit on your Media Uploader page in WordPress. For some it is as low as 2MB which is clearly not enough for large images or videos. You can increase this by doing one of the follwing:

1. Theme Functions File - There are cases where we have seen that just by adding the following code in the theme function’s file, you can increase the upload size:

	`@ini_set( 'upload_max_size', '64M' );`

	`@ini_set( 'post_max_size', '64M');`

	`@ini_set( 'max_execution_time', '300' );`



2. Create or Edit an existing PHP.INI file - In most cases if you are on a shared host, you will not see a php.ini file in your directory. If you do not see one, then create a file called php.ini and upload it in the root folder. In that file add the following code:

    `upload_max_filesize = 6`

	`post_max_size = 6`

    `max_execution_time = 30`


3. htaccess Method - Some people have tried using the htaccess method where by modifying the .htaccess file in the root directory, you can increase the maximum upload size in WordPress. Open or create the .htaccess file in the root folder and add the following code:

	`php_value upload_max_filesize 64M`

    `php_value post_max_size 64M`

    `php_value max_execution_time 300`

    `php_value max_input_time 300`


Again, it is important that we emphasize that if you are on a shared hosting package, these techniques may not work. In that case, you would have to contact your web hosting provider to increase the limit for you.

Transactions are not posting. Why?
---


Please visit the Add Media -> Settings -> Payments page and double check all of your settings. Also, if you are using Paypal, you need to make sure you have [added your IPN Listener URL to Paypal](https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_admin_IPNSetup).

What are license types?
---


The Sell Media plugin helps you to create and assign different licenses and prices for each image, video or audio file based on the anticipated usage of the media. For example: If a large company wants to purchase one of your images for a billboard, you should charge one price for commercial usage, charge another for editorial, and so on.


Screenshots
-----------

1. The Shopping Cart
2. Single Item Template
3. Add New Item
4. Payments History
5. Available Extensions (coming soon)

Developers
----------

Actions
---

Example for adding a message above the cart:

    function sell_media_above_cart_function() {

        print '<p>This message will show up above the cart on the cart popup. You could include a copyright message or links to your terms of service.</p>';

    }
    add_action( 'sell_media_above_cart', 'sell_media_above_cart_function' );

Example for adding a message below the cart:

    function sell_media_below_cart_function() {

        print '<p>This message will show up below the cart on the cart popup. You could include a copyright message or links to your terms of service.</p>';

    }
    add_action( 'sell_media_below_cart', 'sell_media_below_cart_function' );

Action hooks available:

* sell_media_above_cart - Above the cart
* sell_media_below_cart - Below the cart
* sell_media_cart_below_licenses - Between license and price on cart
* sell_media_menu_hook - settings.php - Use for adding new submenu pages
* sell_media_register_settings_hook - settings.php - Use for registering new settings/options
* sell_media_settings_above_general_section_hook - settings.php - Above tables on settings page
* sell_media_settings_below_general_section_hook - settings.php - Below tables on settings page

Upgrade Notice
--------------
 * None

Changelog
---------

1.4.9
---
* Collections are now password protected
* Collections now have featured images/icons
* Fixing issue were shipping value was not properly formatted for PayPal
* Fixing issue where markup was not showing/displaying properly for items with one license
* Sub-collections now supported

1.4.8
---
* Minor bug fix for password-less collections

1.4.7
---
* Collections are now password protected
* New setting "dashboard page" allows admins to create a custom page for recurring customers
* Better child theme support
* Various bug fixes

1.4.6
---
* Editors can now use the bulk upload
* Optimized download history shortcode
* Various bug fixes


1.4.5
---
* Fixing issues in download history shortcode

1.4.4
---
* Correcting param in filter

1.4.3
---
* Image description is imported as item description during bulk upload process
* Adding language support
* Creator taxonomy is now publicly viewable
* Added various filters in bulk uploader for developers
* Widget updates
* Added hook `sell_media_bulk_uploader_additional_fields_meta` to allow developers to add additional meta fields in bulk upload
* Number formatting fix, 0.1 would show in some cases, now it includes trailing zero, i.e., 0.10
* Bug fix in add to cart button
* Bug fix in `[sell_media_item]` shortcode
* Bug fix when prices of 0.1 were being rounded to 0.00 on some pages

1.4.2
---
* Users can now change the post type slug
* Added shortcode `[sell_media_additional_list_items]` so users can add additional information on the single item page under the size list.
* `sell_media_bulk_uploader_additional_fields` allows developers to add additional form fields for bulk upload/edit
* Fixing issue where original file is sometimes downloaded vs. the one purchased
* Various PHP notices fixes

1.4.1
---

* Improved support for importing EXIF/IPTC data such as; city, keywords, etc. during the creation of new items
* Allowed prices to be lower than 0.99
* Improved handling when an item has no size or license

1.4
---

* Better support for portrait images
* Fixed issue where download sizes would not always be displayed

1.3
---

* Additional error checking added for PayPal
* Translation issue fixed
* Email formatting and issues fixed

1.2.9
---

* Optimized bulk uploader! Images sizes are no created on download (after purchase) and not created during the upload process.
* You can now bulk add images to a Collection from the bulk uploader
* Various bug fixes

1.2.8
---

* Fixing bug where users received multiple or no download email

1.2.7
---

* Fixing bug where items with 0 price can be added to the cart

1.2.6
---

* Numerous hooks and filters addd
* Numerous bug fixes

1.2.5
---

* Performance improvements
* Added MS Docs support
* Updated readme.txt
* Bug: Price can now be saved in decimal
* Better styling support

1.2.4
---

* Settings: Default price moved to its own section
* Hook: Updated 'sell_media_after_successful_payment' now accepts an additional parameter
* Hook: Updated 'sell_media_settings_init_hook' to work on all tabs
* Hook: Moved 'sell_media_size_settings_hook' to the aprobiate location
* Hook: Added new hook 'sell_media_below_registration_form'
* Hook: Added 'sell_media_addtional_cusotmer_meta'
* Hook: Added sell_media_before_session_add
* Feature: Options in dialog now default to nothing
* Feature: Added current state select
* Removed validation scripts inplace of native browser validation
* Various bug fixes and styling enhancments

1.2.3
---

* Fixed issue where currency was not showing for some users
* Fixed issue where default search would result in 404 for some results
* Markup percentage is no longer shown on the front-end, only the adjust price is

1.2.2
---

* Fixed intermittent issue where download files would be 0kb
* Fixed issue where downloaded zip files would unzip as zip
* Fixed issue in settings regarding default price

1.2.1
---

* Minor bug fix in size variants

1.2
---

* Added full Featured Image support
* Bug fixes

1.1
---

* Instruction changes
* Fixing settings to work better with extensions

1.0.9
---

* Added bulk uploader
* Added size variants
* Code enhancments
* Bug fixes

1.0.8
---

* Bug fixes
* Code improvements

1.0.7
---

* Bug fixes
* Added option for customer notification
* 3.5 media cart fix

1.0.6
---

* License descriptions now show on the checkout page and option boxes when hovered
* Fixed bug when Attachments are no longer marked for sale.
* Fixed issue when Item is emptied from trash bin
* Fixed bug where editor appeared on other Add New pages.
* Adding hook for single theme.

1.0.5
---

* Fixed transients
* Fixed issue where button was not showing on cart

1.0.4
---

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

1.0.3
---

* Added Google Charts on Reports page
* Added Paypal IPN instructions on Settings
* Max-width fix for Firefox on Sell Media archives
* Post Type Taxonomy archives conflict fix
* Download file fix

1.0.2
---

* Plugin Settings save bugfix

1.0
---

* Public release

0.1-beta2
---

* Permalinks flushed on plugin activation

0.1-beta
---

* First public beta