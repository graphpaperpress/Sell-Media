=== Sell Media ===

Contributors: endortrails, racase
Donate link: https://graphpaperpress.com/plugins/sell-media/
Tags: photography, photos, sell media, sell photos, sell videos, sell downloads, download, downloads, e-commerce, paypal, stock photos, photo gallery, photo cart
Requires at least: 4.0
Tested up to: 4.8
Stable tag: 2.3.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Sell photos, prints, videos and pdf's online through WordPress in seconds. Everything you need to sell your photography online.

== Description ==

[Sell Media](http://graphpaperpress.com/plugins/sell-media/) is finely tailored e-commerce solution for selling photos, prints, and videos through your self-hosted WordPress site. Photographers love it for it's flexibly gallery layouts and powerful [extensions](https://graphpaperpress.com/extensions/sell-media/).

Using Sell Media, you can:

*   Sell photos, galleries, videos, pdf's and other digital files.
*   Create you own stock photo or video site.
*   Charge licensing fees for commercial, editorial, or personal usages.
*   Protect file uploads.
*   Accept payments via PayPal. Additional payment gateways are also available.

= Resources =

* [Beginners's PDF Guide](https://graphpaperpress-downloads.s3.amazonaws.com/free/Sell-Photos-Online.pdf)
* [Documentation](http://graphpaperpress.com/docs/sell-media/)
* [Official Plugin Page](http://graphpaperpress.com/plugins/sell-media/)
* [Github Code Repository](https://github.com/graphpaperpress/sell-media)

= Theme Integration =

These [WordPress themes](http://graphpaperpress.com/wordpress-themes/sell-media/) were designed to enhance the functionality of Sell Media. Sell Media also works with any properly coded WordPress theme, however, some minor styling tweaks might be required.

Take Sell Media to the next level with these powerful extensions:

* [Sell photo prints](http://graphpaperpress.com/plugins/sell-media-reprints)
* [Sell Subscription Plans](https://graphpaperpress.com/plugins/sell-media-subscription/)
* [Cloud Backups](http://graphpaperpress.com/plugins/sell-media-s3)
* [Watermark your images](http://graphpaperpress.com/plugins/sell-media-watermark)
* [Newsletter integration with Mailchimp](http://graphpaperpress.com/plugins/sell-media-mailchimp)
* [Sales Commissions](http://graphpaperpress.com/plugins/sell-media-commissions)
* [And many more](https://graphpaperpress.com/extensions/sell-media/)

= Instant Setup =

If you're having difficulties getting Sell Media set up, check out [VisualSociety.com](https://visualsociety.com). Visual Society is our new fully-hosted e-commerce platform powered by Sell Media and WordPress. This new platform also includes automated print fulfillment worldwide!

== Installation ==

= Server Requirements =

1. PHP 5.6 or higher
2. CURL PHP extension
3. GD PHP extension
4. Original file uploads are protected automatically on Apache servers using .htaccess. If you are using an NGINX server, you'll need to add this to your sites configuration file:
`location ~ /wp-content/uploads/sell_media {
    rewrite / permanent;
}`

1. Activate the plugin.
2. Visit Sell Media -> Settings and configure the options.
3. Insert the **required Sell Media shortcodes** onto your preferred Pages (see FAQ section).
4. Visit Sell Media -> Add New and upload an image, video, audio file or pdf for sale.

= Configuration =

[Download our Free PDF Guide](https://graphpaperpress-downloads.s3.amazonaws.com/free/Sell-Photos-Online.pdf) for detailed instructions and screenshots or visit the official plugin [Documentation](http://graphpaperpress.com/docs/sell-media/) to learn how to setup and customize Sell Media.

== Frequently Asked Questions ==

= Can you help me set up this plugin? =

If you're having trouble getting things set up, we offer a hosted version of Sell Media through our [VisualSociety.com](https://visualsociety.com) platform. There are many additional features found at VisualSociety.com.

= Can I sell photo prints in addition to downloads? =

Yes, there are two ways to do this: 1) [VisualSociety.com](https://visualsociety.com) includes print fulfillment by the world's best photo printers. 2) The [Reprints extension](https://graphpaperpress.com/plugins/sell-media-reprints/) allows you to sell prints, but you are responsible for fulfilling the order. Both you and the customer will receive an email when an order is placed.

= I have 5k+ photos I would like to sell, can Sell Media handle this? =

Sell Media is a plugin for WordPress and WordPress can easily handle hundreds or thousands of files. That said, the number of images that can be bulk uploaded at once is largely related to server performance. If you are using a cheap, shared web host, then you will need to contact them and ask them to change [PHP settings] (http://php.net/manual/en/function.set-time-limit.php).

= What are shortcodes and how do I use them? =

Shortcodes are small snippets of code that when added to a Post, Page or Widget add functionality to your site. You must add the following shortcodes to your preferred Pages to use Sell Media:

* **Checkout Shortcode** - (REQUIRED) Create a page called "Checkout" and add this shortcode to it: `[sell_media_checkout]`
* **Thanks Shortcode** - (REQUIRED) Create a page called "Thanks" and add this shortcode to it: `[sell_media_thanks]`
* **Buy Button Shortcode** - (OPTIONAL) Used for displaying specific items for sale: `[sell_media_item id="1893" text="Purchase" style="button" size="medium"]` Options include: text="purchase | buy" style="button | text" size="thumbnail | medium | large" align="left | center | right"
* **Search Form Shortcode** - (OPTIONAL) Used to display a search form exclusively for searching items for sale within Sell Media: `[sell_media_searchform]`
* **All items shortcode** - (OPTIONAL) Displays all (or a certain collection) items in a grid view: `[sell_media_all_items collection="type-your-collection-slug-here" show="type-number-of-items-per-page"]`
* **Download list shortcode** - (OPTIONAL) List logged in users downloads: `[sell_media_download_list]`
* **Lightbox shortcode** - (OPTIONAL) Displays a page containing all items that visitors have added to their lightbox: `[sell_media_lightbox]`
* **Login shortcode** – (OPTIONAL) Used to show a custom login form for your customers: `[sell_media_login_form]`
* **Filters shortcode** – (OPTIONAL) Used to show a page with Latest, Most Popular, Collections, and Keywords as filters: `[sell_media_filters filters="all"]` You can also pass 1, 2, 3, 4 into the filters shortcode parameter to only show specific tabs Latest (1), Most Popular (2), Collections (3),  or Keywords (4). For example, if you only wanted to show the Latest and Keywords tabs, you would use this shortcode: `[sell_media_filters filters="1,4"]`

= How do I show my checkout cart? =

1. Create a page called "Checkout" and add this shortcode to the page: `[sell_media_checkout]`
2. Visit Sell Media -> Settings and select the Checkout page you created above to the "Checkout Page" option.

= How do I show an item available for sale? =

Visit the Sell Media -> Add Product page and configure the page options. Click Save. Then click the View Product link.

= How do I show a search form for Sell Media items? =

Create a Page called "Search Media" and add this shortcode to it: `[sell_media_searchform]`. A search form also shows up above archive pages for products.

= Can I sell image galleries? =

Yes and you have two options:

1. Simply upload more than one image on the Sell Media -> Add New page. The price listed below is for each image in the gallery. In the near future, we will be releasing an extension for selling the entire gallery of images for one price.
2. Assign each item to a specific Collection and the items will be displayed on that specific collection's archive page. You can then link to the collection like this: http://example.com/collection/my-collection-name/. A list of collecitons also shows up on the Appearance -> Menus page so you can add them to any menu.

= How do I password protect an item? =

The Password Protection option is located in the Publish box when editing a Sell Media item. Click the Visibility - Public - Edit link, select Password Protected, type in a password and click Save.

= How do I password protect a collection? =

Click Sell Media -> Collections -> Click "Edit" next to the Collection you want to hide, fill in the "Password" click update.

= How do I hide a collection from being listed on archive pages? =

Click Sell Media -> Collections -> Add New and check the "Hide" option.

= Payments aren't showing up in Sell Media. Why? =

Please visit the Add Media -> Settings -> Payments page and double check all of your settings.

If you're still having issues, [check these tips in the PayPal section](https://graphpaperpress.com/docs/sell-media/#paypal).

= My file is 500MB+ in size but users cannot download the file after purchasing? =

Check with your hosting provide on your download limits. Sell Media does not provide any type of file splitting service.

We do offer an Amazon S3 extension which offloads to storage of all uploads, which might be useful: [View the extensions](http://graphpaperpress.com/downloads/category/extensions/).

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

= What are license types? =

The Sell Media plugin helps you to create and assign different licenses and prices for each image, video or audio file based on the anticipated usage of the media. For example: If a large company wants to purchase one of your images for a billboard, you should charge one price for commercial usage, charge another for editorial, and so on.

= Does the plugin work for a WordPress Network =

Yes.

= My customer is receiving their confirmation email X many times? =

Please disable your plugins and see if you still have the issue. Some plugins (ones that alter access via IP) do not allow the IPN to function properly.

== Screenshots ==

1. Quick View
2. Single Item Template
3. Archive
4. Collection Archive
5. Checkout
6. Add New Item
7. [Filters - See Usage](https://graphpaperpress.com/blog/sell-media-filters/)
8. [Optional Premium Extensions](https://graphpaperpress.com/extensions/sell-media/)
9. [Optional Premium Themes](https://graphpaperpress.com/wordpress-themes/sell-media/)

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

* sell_media_above_archive_content - Above archive content
* sell_media_below_archive_content - Below archive content
* sell_media_above_archive_header_content - Above archive header content
* sell_media_below_archive_header_content - Below archive header content
* sell_media_above_archive_header_content
* sell_media_above_cart - Above the cart
* sell_media_below_cart - Below the cart
* sell_media_cart_below_licenses - Between license and price on cart
* sell_media_menu_hook - settings.php - Use for adding new submenu pages
* sell_media_register_settings_hook - settings.php - Use for registering new settings/options
* sell_media_settings_above_general_section_hook - settings.php - Above tables on settings page
* sell_media_settings_below_general_section_hook - settings.php - Below tables on settings page

== Upgrade Notice ==


== Changelog ==

= 2.3.5 =
* Fix: Hide password protected items from archives
* Fix: Shortcode update
* Fix: Purchase download links
* Fix: Breadcrumbs filter
* Fix: Price group saving
* Fix: Creator metabox fix
* Fix: Thumbnail logic for ZIP/PDF


= 2.3.4 =
* Update: Japnese translations.
* New: Auto login customer after purchase.
* New: Added filter 'sell_media_breadcrumbs' in breadcrumbs.
* Fix: Removed Update / Publish button in Payment.
* Fix: Wrong icon in all collections shortcode.
* Fix: Hide password protected items.
* Fix: Non-purchased downloads not protected? #807.
* Fix: Multiple Commission section in advance tab.
* Fix: Creator never saved #812.


= 2.3.3 =
* Update: French translations
* Fix: Reinit Macy.js in Filters ajax
* Fix: Check if e-commerce is enabled to show price metabox

= 2.3.2 =
* New: Macy.js Masonry gallery layout
* New: Horizontal Masonry gallery layout
* New: UI range slider
* New: Filter admin notices
* New: Currency hooks
* Fix: S3 Download location fix
* Fix: Constrain image sizes on thanks page
* Fix: Orientation
* Fix: Collection icon fix
* Fix: Quick View style improvements

= 2.3.1 =
* New: Delete Pricelists
* Added deprecated classes

= 2.3.0 =
* New: UI for Add New page
* New: WP Cookie-based sessions. Replaces use of $_SESSION.
* New: Pricelist UI improvements

= 2.2.12 =
* Fix: Search multiple word keywords
* Fix: Discount codes
* Fix: Download url checks

= 2.2.11 =
* Fix: Double featured images in some themes
* Fix: SEO plugin conflict
* Fix: Add sell_media_content_loop function in deprecated list
* New: Add products column filter

= 2.2.10 =
* Fix: JetPack $post->ID conflict fix.
* Fix: Count post views
* Fix: Display captions
* Fix: Display video embeds

= 2.2.9 =
* Fix: Keyword migration cron workaround.
* Fix: Widget layout fixes.
* Fix: Don't show current item in Similar or New Widgets.

= 2.2.8 =
* Fix: PHP 5.4 issue
* Fix: Password protected entries and media

= 2.2.7 =
* New: Keywords for attachments.
* New: Search filters.
* New: Bulk/ Quick edit on backend item listing.
* New: 'sell_media_taxonomies' shortcode to displays recent entry from each custom taxonomy.
* New: 'sell_media_thanks' filter in 'sell_media_thanks' shortcode.
* New: Title and Quick View optional display (new settings).
* New: Layouts, Query, Search, Upgrades classes.
* Fix: Keep data on plugin deactivation.
* Fix: Signup issue on registration.
* Fix: Fix session errors.
* Fix: Other minor bug fixings.
* Fix: Varios coding standards.
* Fix: Remove legacy code for taxonomy metadata.

= 2.2.6 =
* Fix: Error if not selling prints fixed

= 2.2.5 =
* Fix: Send customer login email

= 2.2.4 =
* Fix: Cookie notice on thanks page

= 2.2.3 =
* Fix: Show license descriptions
* Fix: Remove deprecated param
* Fix: Button sizing CSS fix
* Fix: Clear cookie after purchase

= 2.2.2 =
* Feature: Responsive videos
* Feature: New hooks for images and downloads
* Fix: Remove extra character in markup
* Fix: PayPal args use WP charset
* Fix: Unserialize serialized payment data

= 2.2.1 =
* Fix: JS conflict fix for non SM pages.

= 2.2 =
* Feature: New Video & Audio class. Improves selling options for both.
* Feature: Regenerate missing files
* Tweak: Disable lightbox if no page set
* Tweak: Show file dimensions of original, protected file
* Tweak: Delete and move protected files when user deletes sell_media_item
* Fix: Clear cart after payment
* Fix: Update all translation files
* Fix: Masonry layout issue
* Fix: Filter tax layout issue

= 2.1.8 =
* Feature: Tax display setting (inclusive/exclusive)
* Tweak: Show exif for original file, not large
* Tweak: Show message for no keyword search
* Fix: Keyword import bugfix

= 2.1.7 =
* Tweak: Set time limit
* Tweak: Added filter to change original file path
* Tweak: Added scroll on TOS popup
* Tweak: Check user cap to show license message
* Tweak: Fixed hover issue on listing
* Fix: Undefined variable fix
* Fix: Fixing missing args issue
* Fix: Fixed password procted post quick view issue

= 2.1.6 =
* Tweak: Integration with Limited Edition Prints.
* Tweak: Integration with Manual Purchases extension.
* Tweak: Integration with Download Lightbox extension.
* Tweak: Regenerate Thumbnails integration.
* Tweak: German language files update.
* Bugfix: Rare free purchases and reprints conflict.
* Bugfix: Collection archives showing attachments.

= 2.1.5 =
* New Feature: Filter Shortcode. Usage: `[sell_media_filters filters="all"]`. See instructions for additional parameters.
* New Feature: Add "Search Everywhere" checkbox as optional search parameter.
* Tweak: Default search to keywords only. This helps site owners optimize search results.
* Tweak: Retain searched options on page load.
* Tweak: Add archive page template override back into theme. Usage: Copy sell-media/themes/archive.php into your active theme folder and rename it to archive-sell-media.php
* Tweak: Always use square images in widgets.
* Tweak: Collection shortcode overlay design fix.
* Tweak: Free download button text filter.
* Tweak: Require WordPress 4.4 on activation.

= 2.1.4 =
* Tweak: Price group UI fix.
* Fix: Prices over $1,000 on checkout.
* Fix: If cart only contains downloadables, no shipping.
* Tweak: Number input case statement.
* Tweak: Only load css on sell_media_item admin pages.

= 2.1.3 =
* New Feature: Thumbnail Gallery Layouts
* New Feature: Thumbnail Cropping
* Tweak: Hover effects on images
* Tweak: Next/Prev improvement on Quick View
* Tweak: View Gallery text

= 2.1.2 =
* New Feature: Automatic updates for extensions.
* Tweak: Multisite custom taxonomy support.
* Tweak: Don't search term descriptions.
* Tweak: Show "View Gallery" text.
* Tweak: Use core WP term meta.
* Tweak: Migrate custom taxonomy meta tables into core term meta tables.
* Fix: Notices on thanks page, updater.

= 2.1.1 =
* Tweak: Multisite support
* Tweak: Hook added for Free Downloads extension
* Fix: Checkout blank if missing $attachment_id
* Fix: Missing price options on dropdown

= 2.1 =
* New Feature: Quick View.
* New Feature: $0 prices creates a free download.
* New Feature: PHP Sessions saved to DB.
* New Feature: Auto page creation w/ shortcodes during activation.
* New Feature: Set default options during plugin activation.
* New Feature: Delete options during plugin deactivation.
* New Feature: Various hooks and filters added on archive.php and for text strings.
* Tweak: Responsive improvements.
* Tweak: Theme compatibility css.
* Tweak: wp_title fix on single entries.
* Tweak: Improved gallery navigation.
* Tweak: CSS vendor prefixes.
* Fix: Attachments now propertly searched. There is a known issue, however, with attachments previously uploaded to Posts or Pages. Users can change the post parent on the attachment page, should it be needed.
* Fix: delete_transient for caching fix


= 2.0.7 =
* Hide attachments not not for sale from Sell Media Search page.
* Change CPT priority

= 2.0.6 =
* Hotfix for users with PHP versions older than 5.3.

= 2.0.5 =
* New Feature: Search widget.
* New Feature: Keywords saved with attachments, improves search results.
* Tweak: Widget construct for 4.3 compatibility.
* Tweak: Make settings page access filterable.
* Tweak: Lower priority on widgets_init so it doesn't get priority over sidebar.
* Tweak: Improve gallery caption display.
* Tweak: Add missing strings for translation.
* Bugfix: Add missing watermark class to galleries.
* Bugfix: Missing thumbnails on checkout if not in a gallery or if item doesn't have featured image.


= 2.0.4 =
* Tweak: New filters for Access Control extension
* Tweak: Minor UI changes in admin
* Tweak: Show image caption and title on gallery images
* Tweak: Added logout url to dashboard, with filter
* Tweak: New translation files
* Tweak: Grunt tasks for translations, coding standards
* Bugfix: sell_media_item shortcode shouldn't show buy button for galleries
* Bugfix: Deleting collection thumbnail fix

= 2.0.3 =
* New Feature: Check for newest version of Sell Media extensions.
* Tweak: Add search by ID in admin.
* Tweak: Don't send empty sizes and licenses to gateway.
* Tweak: Improved html email receipts.
* Bugfix: Missing sell_media_image class on gallery images.
* Bugfix: Move non-image files to /uploads/sell_media/.
* Bugfix: Filepath checks for non-image uploads (zips, etc).


= 2.0.2 =
* Tweak: Do not cache checkout, thanks and lightbox pages. Prevents PayPal IPN misses.
* Tweak: Increase max_execution_time for bulk uploads
* Tweak: Properly sanitize add_query_arg with esc_url
* Tweak: Define select box width in dialog to prevent overflow on long titles
* Tweak: Notice for is_gallery check on 404 and Search pages.
* Tweak: Update PayPal IPN class and cert files.

= 2.0.1 =
* New Feature: Galleries. Upload multiple files.
* New Feature: Gallery navigation.
* New Feature: Importing options (Lightroom, etc)
* New Feature: Sales stats for each product.
* New Feature: View counts for each product.
* New Feature: Lightbox notification text.
* New Feature: Automatic classes for Checkout and Lightbox menu items.
* New Feature: Upgrade notices for extensions.
* New Feature: Dashicon integration.
* Tweak: Updated download methods for accepting $attachment_id.
* Tweak: Lightbox changes to accomodate $attachment_id.
* Tweak: Lightbox now uses serialized multidimensional array.
* Tweak: Flush permalinks if slug changed on settings.
* Tweak
* Bugfix: Missing sell_media_image class.
* Bugix: Notice fix on system info and payments pages.

= 2.0 =
* New Feature: Breadcrumb navigation options.
* New Feature: Layout options. Choose one or two column layouts on single entries.
* New Feature: Search optimization. Now searches titles, content and keywords and includes exact phrase match for search ("New York", vs "New" and "York").
* New Feature: Lots of new action hooks and filters.
* Tweak: Theme compatibility fixes, no longer requires template files.
* Tweak: Improved localization.
* Tweak: Rewrite of the lightbox feature.
* Tweak: Improve the logic of checkout button activation.
* Bug: Adding multiple downloads by accidental multi-clicking fix.
* Bug: Adding prints to cart without selecting price group fix.

= 1.9.13 =
* Collections count bug fix
* Translation bug fix

= 1.9.12 =
* Removed default UI of price group from backend
* Starting price for free download containing collection set to $0.00
* iOS Chrome and Safari crash fix
* Extensions menus not displaying bug fix

= 1.9.11 =
* Master language file added
* List all collections shortcode layout bug fix
* Extensions direct link & upgrades
* PayPal POODLE SSL fix
* Translation updates. See languages/readme.txt for details.

= 1.9.10 =
* Single license mark-up issue fix
* BUY button in shortcode for free download fix
* Search pagination fix

= 1.9.9 =
* Larger images sizes available for purchase bug fix
* All items shortcode paging bug fix
* Password protected collections visible in search issue fix
* Downloads not working on some Apache servers. Replace wp_die() with exit()
* Fixed "reprints-price-group" typo in Sell_Media->products->has_price_group. Corrects default price shown in cart.

= 1.9.8 =
* Bugfix on collection archive template

= 1.9.7 =
* Bugfix fix not ! check on sell media archive template

= 1.9.6 =
* Buy and Save buttons added back to archives
* Password protection to childs issue fix
* Custom thumbnail size parameter added
* Items order added in all items shortcode

= 1.9.5 =
* Feature: Infinite nesting of Collections
* Feature: Breadcrumbs for Collections archives
* Bug: Show lowest price, not default price

= 1.9.4 =
* Feature: Lightbox integration
* Feature: apply_filters to Bulk and Package post_status
* Tweak: archive styling improvements for lightbox

= 1.9.3 =
* Tweak: Add Forgot Password link to login shortcode
* Tweak: South African rand currency support added
* Tweak: Limit access to Payments submenu to admins only
* Tweak: Charge shipping only if prints in cart
* Tweak: Remove image link from the PayPal shopping cart
* Tweak: sell_media_item shortcode styling

= 1.9.2 =
* Tweak: Updates to Discount Codes
* Tweak: apply_filters on delivery text
* Tweak: Update translation files

= 1.9.1 =
* Feature: apply_filters to sell_media_item post type registration
* Tweak: Live quantity checks on checkout to toggle cart visibility
* Tweak: Uppercase post slug on archive template
* Tweak: Widget CSS improvements

= 1.9.0 =
* Tweak: Filter checkout texts
* Bug: Correct prices for items without Price Groups, but with license markup

= 1.8.9 =
* Tweak: Streamline disabled button checks on cart
* Tweak: Search results help text

= 1.8.8 =
* Feature: EXIF Widget added
* Tweak: Set default price on all new uploads
* Tweak: PayPal taxes when qty increases
* Tweak: Cart button conditional fixes to allow for items without price groups or licenses
* Bug: Show correct currency on cart

= 1.8.7 =
* Feature: Advanced Search integrated into core
* Tweak: Remove filtering core WP search, just rely on Advanced Search now
* Bug: number_format() warning when no default price set in admin

= 1.8.6 =
* Tweak: Use core table for Payments page
* Tweak: New sell_media_item_icon function
* Tweak: Submenu ordering
* Tweak: Remove admin attachment filers
* Tweak: Simplify bulk add
* Bug: Fix for password protected collections


= 1.8.5 =
* Feature: Packages feature added
* Tweak: Make Sell_Media a singleton
* Tweak: Add support for additional mime types
* Tweak: Remove redundant calls to self::upload_dir, use one function now
* Tweak: Hide file dimensions on cart if not image
* Tweak: New file download method
* Bug: Bulk uploads fix
* Bug: PayPal tax
* Bug: Deprecated functions missing for image caption

= 1.8.4 =
* Tweak: Template redirects for taxonomies
* Bug: Terms dialog

= 1.8.3 =
* Bug: PayPal live mode or test mode

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