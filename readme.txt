=== Sell Media ===

Contributors: endortrails, ZaneMatthew
Donate link: http://graphpaperpress.com/plugins/sell-media/
Tags: commerce, digital downloads, download, downloads, e-commerce, paypal, photography, sell digital, sell download, selling, sell photos, sell videos, sell media, stock photos
Requires at least: 3.4
Tested up to: 3.7
Stable tag: 1.8.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sell photos, prints, videos and pdf's online through WordPress in seconds. Built by creatives, for creatives.

== Description ==

[Sell Media](http://graphpaperpress.com/plugins/sell-media/) is a WordPress plugin that allows anyone to sell, license and protect images, videos, audio and pdf's on their self-hosted WordPress site.

[Learn more](http://graphpaperpress.com/plugins/sell-media/) or track the progress of [Sell Media on Github](https://github.com/graphpaperpress/sell-media).

The Sell Media plugin allows you to:

*   Sell any kind of media file that you upload into WordPress, including images, videos, audio files and pdf's.
*   License your media for specific usages, including commercial, editorial, or personal usages.
*   Create you own stock photo or video site.
*   Protect file uploads.
*   Accept payments via PayPal.
*   Earn passive income by selling and licensing your media.

Take Sell Media to the next level with these powerful extensions:

* [Sell photo reprints](http://graphpaperpress.com/?download=reprints-self-fulfillment)
* [Watermark your images](http://graphpaperpress.com/?download=watermark)
* [Newsletter integration with Mailchimp](http://graphpaperpress.com/?download=mailchimp)
* [Advanced Search](http://graphpaperpress.com/?download=advanced-search)
* [Sales Commissions](http://graphpaperpress.com/?download=commissions)
* [And many more](http://graphpaperpress.com/downloads/category/extensions/)

== Installation ==

1. Activate the plugin.
2. Visit Sell Media -> Settings and configure the options.
3. Insert the **required Sell Media shortcodes** onto your preferred Pages (see FAQ section).
4. Visit Sell Media -> Licenses and add or configure your default licenses for new uploads.
5. Visit Sell Media -> Add New and upload an image, video, audio file or pdf for sale.

= IMPORTANT: PayPal Setup =

In order for PayPal to communicate with your site, you must do three things:

1. Set your IPN in PayPal
2. Enable Auto Return in PayPal
3. Enable Payment Data Transfer in PayPal

Setting your IPN in PayPal

1. Login to your PayPal account.
2. Mouse over the Profile menu option and then click on the Selling Tools menu option.
3. Scroll down to "Getting paid and managing my risk" and click the Update link beside "Instant payment notifications".
4. Paste your PayPal IPN URL onto that page in PayPal. Your PayPal IPN URL is located on the Sell Media Settings page.

Enabling Auto Return in PayPal

1. Log in to your PayPal account at https://www.paypal.com. The My Account Overview page appears.
2. Click the Profile subtab. The Profile Summary page appears.
3. Click the My Selling Tools link in the left column.
4. Under the Selling Online section, click the Update link in the row for Website Preferences.
5. Under Auto Return for Website Payments, click the On radio button to enable Auto Return.
6. In the Return URL field, enter the URL to your Thanks Page. NOTE: PayPal checks the Return URL that you enter. If the URL is not properly formatted or cannot be validated, PayPal will not activate Auto Return.

Enabling Payment Data Transfer in PayPal

7. On the same page as you set your Auto Return url, find the Payment Data Transfer section and click the On radio button to enable Payment Data Transfer.
8. Scroll to the bottom of the page, and click the Save button.

PayPal is now ready to communicate with your website.

== Frequently Asked Questions ==

= I have 5k+ photos I would like to sell, can Sell Media handle this? =
* Sell Media is a plugin for WordPress and WordPress can easily handle hundreds or thousands of files. That said, the number of images that can be bulk uploaded at once is largely related to server performance. If you are using a cheap, shared web host, then you will need to contact them and ask them to change [PHP settings] (http://php.net/manual/en/function.set-time-limit.php).

= My file is 500MB+ in size but users cannot download the file after purchasing? =
Check with your hosting provide on your download limits. Sell Media does not provide any type of file splitting service.

= What are shortcodes and how do I use them? =

Shortcodes are small snippets of code that when added to a Post, Page or Widget add functionality to your site. You must add the following shortcodes to your preferred Pages to use Sell Media:

* **Checkout Shortcode** - (REQUIRED) Create a page called "Checkout" and add this shortcode to it: `[sell_media_checkout]`
* **Thanks Shortcode** - (REQUIRED) Create a page called "Thanks" and add this shortcode to it: `[sell_media_thanks]`
* **Buy Button Shortcode** - (OPTIONAL) Used for displaying specific items for sale: `[sell_media_item id="1893" text="Purchase" style="button" size="medium"]` Options include: text="purchase | buy" style="button | text" size="thumbnail | medium | large" align="left | center | right"
* **Search Form Shortcode** - (OPTIONAL) Used to display a search form exclusively for searching items for sale within Sell Media: `[sell_media_searchform]`
* **All items shortcode** - (OPTIONAL) Displays all (or a certain collection) items in a grid view: `[sell_media_all_items collection="type-your-collection-slug-here"]`
* **Download list shortcode** - (OPTIONAL) List logged in users downloads: `[sell_media_download_list]`


= How do I show my checkout cart? =

1. Create a page called "Checkout" and add this shortcode to the page: `[sell_media_checkout]`
2. Visit Sell Media -> Settings and select the Checkout page you created above to the "Checkout Page" option.

= How do I show an item available for sale? =

You have two options:

1. After adding a new item for sale on the Sell Media -> Add New page. Use the following shortcode with your ID for the item you would like to sale: `[sell_media id="257" text="Purchase" style="button" color="blue" size="medium"]` You can locate the ID of the item by looking at the URL when editing that item in WordPress. For example, http://test.com/wp-admin/post.php?post=1891&action=edit The ID is 1891, so the shortcode would be `[sell_media id="1891" text="Purchase" style="button" color="blue" size="medium"]`

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

Click Sell Media -> Collections -> Click "Edit" next to the Collection you want to hide, fill in the "Password" click update.

= How do I hide a collection from being listed on archive pages? =

Click Sell Media -> Collections -> Add New and check the "Hide" option.

= How do I increase the maximum upload size in WordPress? =

Depending on the web hosting company you choose and the package you select, each of you will see maximum file upload limit on your Media Uploader page in WordPress. For some it is as low as 2MB which is clearly not enough for large images or videos. You can increase this by doing one of the following:

1. Theme Functions File - There are cases where we have seen that just by adding the following code in the theme function's file, you can increase the upload size:

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

Please visit the Add Media -> Settings -> Payments page and double check all of your settings. Also, if you are using PayPal, you need to make sure you have [added your IPN Listener URL to PayPal](https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_admin_IPNSetup).

Also, PayPal computers use IP ranges 66.211.*.* and 173.0.*.* and visit the IPN URL with NO User-Agent. Some web hosting companies have their servers set up to block incoming pings when the User-Agent is not explicitly set (as is the case with PayPal's IPN). In this case, you'll want to modify your .htaccess file to override user-agent blocking with these address ranges.

= What are license types? =

The Sell Media plugin helps you to create and assign different licenses and prices for each image, video or audio file based on the anticipated usage of the media. For example: If a large company wants to purchase one of your images for a billboard, you should charge one price for commercial usage, charge another for editorial, and so on.

= Does the plugin work for a WordPress Network =

It only works on the primary blog. While it will work on other blogs, file uploads will not be protected. Why? Because WordPress stores uploads in a "virtual" directory of blogs.dir, server side file protection using .htaccess doesn't work on virtual directories.

= My customer is receiving their confirmation email X many times? =

Please disable your plugins and see if you still have the issue. Some plugins (ones that alter access via IP) do not allow the IPN to function properly.

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

* You must enable Auto Return Payment Data Transfer in PayPal for purchases to be recorded. See readme.txt for instructions.

== Changelog ==

= 1.8.2 =
* Bug: Fix price verification for PayPal
* Bug: Only add tax if enabled

= 1.8.1 =
* Tweak: Checkout page styling
* Tweak: Cart overlay top position
* Bug: Set default currency to USD
* Bug: Emails to buyers not always sending
* Bug: Show cart markup on all pages, except checkout
* Bug: Fatal error for countries list for reprints extension

= 1.8 =
* Feature: UI changes for responsive/mobile support
* Feature: Tax suport
* Feature: Added 3 shipping methods for prints
* Tweak: sellMediaCart js now powers cart
* Tweak: Removed $_SESSION cart
* Tweak: Validate prices before sending to payment gateway
* Tweak: Standardize data storage for payment meta _sell_media_payment_meta
* Tweak: Responsive grids
* Tweak: Checkout page reworked
* Tweak: Improved emails
* Tweak: New classes for all major parts of plugin
* Tweak: Derive buyer data from payment gateway
* Tweak: Publish paid purchases only
* Tweak: Translations updates
* Bug: Ajax setup
* Bug: Pending payments
* Bug: Conflict with JetPack infinite scroll

= 1.7 =
* Bug: Fixed issue with price groups not appearing in the drop down

= 1.6.9 =
* Tweak: Next/Previous on the default single item page now cycles through collections in same category
* Added additional name space for translation
* Added filter for State and Country list
* Password protected collections now expire their password after a set time limit
* Sorting by title, date, author, price in the admin made possible
* Addressed issue where shipping was added twice on some hooks
* Bug: Checkout page is now using the derived image size for the description, prior the price group height/width was used
* Bug: Sanitize slug
* Bug: Login redirect fix

= 1.6.8 =
* Tweak: Pending items are no longer displayed when the download history shortcode is used
* Tweak: Improved email reliability by embedding the license description as apposed to attaching it
* Tweak: Country and State are now required
* Tweak: Updated `sell_media.pot` file
* Bug: Fixing issue when license was toggled after the size was toggled, an incorrect license was applied

= 1.6.7 =
* Tweak: Updating shortcode instructions in `readme.txt`
* Bug: Fixing translation issues
* Bug: Fixing issue where search would still show even if it was disabled

= 1.6.6 =
* Feature: Sell Media items are now support post authors
* Bug: Fixing issue with settings and price groups on activation

= 1.6.5 =
* Bug: Various bug fixes with Price Groups
* Feature: Added new settings API
* Tweak: Add to cart button no longer changes to check out after adding 1 item
* Tweak: Added tweak from [@NETLINK](https://github.com/NETLINK) so PayPal IPN URL can be added as a param.
* Tweak: Added support for WPML

= 1.6.4 =
* Feature: Added various hooks and filters
* Feature: Using jQuery validation in place of HTML5 validation
* Tweak: Allowed for translation of "Pending" on the `sell_media_download_list` shortcode
* Tweak: Added link to purchased item from payments page
* Tweak: Added "Lost your password?" link to the check out page
* Tweak: Fixing issue where currency symbol on admin pages
* Tweak: Administrators can now buy products while logged in
* Tweak: Updated admin payments UI
* Tweak: Deriving a more reliable IPN URL for some users
* Bug: Fixing issue with 3.7 and `save_post`
* Bug: Fixing issue when quantity was updated on the checkout page the subtotal was not correctly updated
* Bug: Fixing issue where session was not starting for some users

= 1.6.3 =
* Bug: Fixing issue where sizes from price group did not show in select box
* Bug: Fixing issue where download link would show when users purchased a reprint

= 1.6.2 =

* Feature: License descriptions are now emailed as an attachment when a purchase is made
* Feature: Added option to enable/disable admin columns
* Feature: Added clear cart button
* Feature: Added additional admin column "protected" for collection page
* Bug: Fixing issue were size was not dependent on license
* Bug: Fixing notice when there is no min price to be displayed
* Bug: Fixed conflict with WordPress heartbeat API
* Tweak: Download list shortcode now list newest to oldest purchases
* Tweak: Customers now have a "remove all" option on the checkout page
* Tweak: Keywords can now be used with default permalinks
* Tweak: Password protected collections are now more secure
* Tweak: Added admin notices
* Tweak: Password protected collections now use a custom template

= 1.6.1 =

* Bug: Updated PayPal arguments for buyer download email
* Bug: Fixing issue were cart was not emptied
* Bug: New users are sent an automated WordPress email when registering

= 1.6 =

* Feature: Added option to set a given price group as the site default
* Tweak: Password protected collections are filtered from the widgets page and no longer show on the front end
* Tweak: Added filter `sell_media_purchase_text`
* Bug: Customers are no longer redirected to the WordPress dashboard after login
* Bug: Addressed issue when some users did not receive the download link
* Bug: Addressed issue where cart was not emptied, due to session not being started and browser cache

= 1.5.9 =

* Feature: Added option to hide original price
* Feature: Added option to disable the built in Sell Media search and use native WordPress search
* Tweak: Readme no longer references hiding a collection, replaced with password instructions
* Tweak: Original height and width is display, for images only, on the single item page
* Tweak: Admin notice is only displayed on the child collection if the parent has a password
* Bug: Various updates to the cart
* Bug: Checkout page now checks if email already exists
* Bug: Price group parents now correctly updates the parent name when saved
* Bug: quotes can now be used in the license description
* Bug: Similar items widget now displays correct items even when collections slug are changed
* Bug: Widgets overflowing bug fix.

= 1.5.8 =

* Bug: Updating admin payments and needed screens to support previous versions of pricing

= 1.5.7 =

* Tweak: Various improvements for cart in PHP session
* Tweak: Minor text update
* Tweak: Price groups class can now be used for any parent/child taxonomy
* Tweak: Updating shortcode in readme.txt
* Tweak: Adding template check for other developers to change the collection password page
* Bug: Child collections now inherit the password of the parent collection
* Bug: No longer enqueue `nav-menu`, it has been removed in WordPress 3.6
* Bug: Corrected text domain issue

= 1.5.6 =

* Feature: Admin shows collection icon on collection edit column
* Feature: Added detailed PayPal log per item on the single item payments page
* Bug: Better PayPal support
* Tweak: Improved search results

= 1.5.5 =

* Feature: Add shortcode for custom login '[sell_media_login_form]'
* Feature: Add setting for custom login page
* Bug: Updated admin payment apply markup consistently
* Bug: Updated paypal payments to no longer by 0 and have markup applied if added

= 1.5.4 =

* Feature: Added shortcode to display all collections 'sell_media_list_all_collections' ( available shortcode attributes: thumbs, details )
* Feature: Added price groups to the native WordPress bulk edit
* Feature: File downloads have the size and license appended to them, i.e., my-image-1024x768-commercial.jpg
* Feature: License descriptions now show on hover, next to the license on the dialog
* Feature: Added tooltips to license descriptions
* Feature: Download size is now displayed on the admin payments item page
* Tweak: Added new attributes to 'sell_media_all_items' ( attributes: collection, show )
* Tweak: Added a "Continue shopping" link on the checkout page
* Tweak: Adjusting currency format in settings
* Tweak: Payments should not be publicly queryable
* Tweak: Make checkout shortcode function name semantic
* Bug: Cart totals were inconsistent
* Bug: Featured image used on archive pages if present
* Bug: Non-image items can now have multiple licenses
* Bug: Quantity totals are immediately calculated
* Bug: Intermittent bug where items were added to the cart twice
* Bug: Validate Safari new user registrations on checkout page
* Bug: Downloads are now based on the constraints of the original image and the price group


= 1.5.3 =

* Bug: "Original File Price" was truncating trailing zero's from prices.
* Bug: Fixed issue when removing items from the cart incorrect totals were displayed
* Bug: Fixed issue in widgets
* Bug: Price groups was not respecting newly created sizes when downloading images
* Tweak: Added version number to enqueue script and styles

= 1.5.2 =

* New: Added price groups to admin item columns
* New: Added collection to admin item columns
* Tweak: Corrected instructions for `sell_media_all_items` shortcode
* Bug: Fixed notice on checkout page when no licese id was present for markups
* Bug: Price groups that did not have any images assigned were not showing in the price group drop down.

= 1.5.1 =

* New: Price groups, users are no longer limited to "small, medium, large", they can create infinite price groups and assign them to items.
* New: Added PayPal log.txt file to admin settings
* New: Added field for admins to add CC accounts for paypal purchases
* New: Added Option to change sort order on archive pages
* New: Added POT file
* New: Useful info is now stored in global js object "sell_media"
* Tweak: US State list is no longer required, this was causing issues for users outside of the US
* Tweak: Changed order of "bulk upload" buttons
* Tweak: "Default Price" is now changed to "Original Price"
* Tweak: Updated menu cart class names
* Bug: Duplicate licenses are no longer installed on re-activation
* Bug: Typo

= 1.5 =

* Fixing issue where a generic function needed to be prefixed

= 1.4.9 =

* Collections are now password protected
* Collections now have featured images/icons
* Fixing issue where shipping value was not properly formatted for PayPal
* Fixing issue where markup was not showing/displaying properly for items with one license
* Sub-collections now supported

= 1.4.8 =

* Minor bug fix for password-less collections

= 1.4.7 =

* Collections are now password protected
* New setting "dashboard page" allows admins to create a custom page for recurring customers
* Better child theme support
* Various bug fixes

= 1.4.6 =

* Editors can now use the bulk upload
* Optimized download history shortcode
* Various bug fixes

= 1.4.5 =

* Fixing issues in download history shortcode

= 1.4.4 =

* Correcting param in filter

= 1.4.3 =

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

= 1.4.2 =

* Users can now change the post type slug
* Added shortcode `sell_media_additional_list_items` so users can add additional information on the single item page under the size list.
* `sell_media_bulk_uploader_additional_fields` Allows developers to add additional form fields for bulk upload/edit
* Fixing issue where original file is sometimes downloaded vs. the one purchased
* Various PHP notices fixes

= 1.4.1 =

* Improved support for importing EXIF/IPTC data such as; city, keywords, etc. during the creation of new items
* Allowed prices to be lower than 0.99
* Improved handling when an item has no size or license
* Improved handling for prices with non-images

= 1.4 =

* Better support for portrait images
* Fixed issue where download sizes would not always be displayed

= 1.3 =

* Additional error checking added for PayPal
* Translation issue fixed
* Email formatting and issues fixed

= 1.2.9 =

* Optimized bulk uploader! Images sizes are no created on download (after purchase) and not created during the upload process.
* You can now bulk add images to a Collection from the bulk uploader
* Various bug fixes

= 1.2.8 =

* Fixing bug where users received multiple or no download email

= 1.2.7 =

* Fixing bug where items with 0 price can be added to the cart

= 1.2.6 =

* Numerous hooks and filters addd
* Numerous bug fixes

= 1.2.5 =

* Performance improvements
* Added MS Docs support
* Updated readme.txt
* Bug: Price can now be saved in decimal
* Better styling support

= 1.2.4 =

* Settings: Default price moved to its own section
* Hook: Updated 'sell_media_after_successful_payment' now accepts an additional parameter
* Hook: Updated 'sell_media_settings_init_hook' to work on all tabs
* Hook: Moved 'sell_media_size_settings_hook' to the appropriate location
* Hook: Added new hook 'sell_media_below_registration_form'
* Hook: Added 'sell_media_additional_cusotmer_meta'
* Hook: Added sell_media_before_session_add
* Feature: Options in dialog now default to nothing
* Feature: Added current state select
* Removed validation scripts inplace of native browser validation
* Various bug fixes and styling enhancements

= 1.2.3 =

* Fixed issue where currency was not showing for some users
* Fixed issue where default search would result in 404 for some results
* Markup percentage is no longer shown on the front-end, only the adjust price is

= 1.2.2 =

* Fixed intermittent issue where download files would be 0kb
* Fixed issue where downloaded zip files would unzip as zip
* Fixed issue in settings regarding default price

= 1.2.1 =

* Minor bug fix in size variants

= 1.2 =

* Added full Featured Image support
* Bug fixes

= 1.1 =

* Instruction changes
* Fixing settings to work better with extensions

= 1.0.9 =

* Added bulk uploader
* Added size variants
* Code enhancements
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
* Added PayPal IPN instructions on Settings
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
