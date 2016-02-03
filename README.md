# Sell Media #

[Sell Media](http://graphpaperpress.com/plugins/sell-media/) is a WordPress plugin that allows you to sell, license and protect images, videos, audio and pdf's on your self-hosted WordPress site.

![The Shopping Cart](http://s.wordpress.org/extend/plugins/sell-media/screenshot-1.png)

* [Documentation](http://graphpaperpress.com/docs/sell-media/)
* [Roadmap](https://github.com/graphpaperpress/sell-media/ROADMAP.md)
* [Contributing](https://github.com/graphpaperpress/sell-media/CONTRIBUTING.md)
* [Official Plugin Page](http://graphpaperpress.com/plugins/sell-media/)

### Main Features ###

*   Sell photos, galleries, videos, pdf's and other digital files.
*   Create you own stock photo or video site.
*   Charge licensing fees for commercial, editorial, or personal usages.
*   Protect file uploads.
*   Accept payments via PayPal. Additional payment gateways are also available.

### Themes ###

These [WordPress themes](http://graphpaperpress.com/wordpress-themes/sell-media/) were designed to enhance the functionality of Sell Media. Sell Media also works with any properly coded WordPress theme, however, some tweaks might be required.

### Extensions ###

* [Sell photo prints](http://graphpaperpress.com/plugins/sell-media-reprints)
* [Cloud Backups](http://graphpaperpress.com/plugins/sell-media-s3)
* [Watermark your images](http://graphpaperpress.com/plugins/sell-media-watermark)
* [Newsletter integration with Mailchimp](http://graphpaperpress.com/plugins/sell-media-mailchimp)
* [Sales Commissions](http://graphpaperpress.com/plugins/sell-media-commissions)
* [And many more](https://graphpaperpress.com/extensions/sell-media/)

## Installation ##

### Server Requirements ###

1. PHP 5.4 or higher
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

## Community ##