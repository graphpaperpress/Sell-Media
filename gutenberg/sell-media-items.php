<?php

//  Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Dynamic Blocks.
require plugin_dir_path( __FILE__ ) . 'enqueue-scripts.php';
require plugin_dir_path( __FILE__ ) . 'class-sell-media-gutenberg-block.php';
require plugin_dir_path( __FILE__ ) . 'blocks/sell-media-all-items/sell-media-all-items.php'; 
require plugin_dir_path( __FILE__ ) . 'blocks/sell-media-filters/sell-media-filters.php';
require plugin_dir_path( __FILE__ ) . 'blocks/sell-media-items-slider/sell-media-items-slider.php';
require plugin_dir_path( __FILE__ ) . 'blocks/sell-media-list-all-collections/sell-media-list-all-collections.php';
require plugin_dir_path( __FILE__ ) . 'blocks/sell-media-search-form/sell-media-search-form.php';