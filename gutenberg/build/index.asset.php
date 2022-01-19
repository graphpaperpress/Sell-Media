<?php 

// check which page is loaded
global $pagenow;

if ( 'widgets.php' == $pagenow ) {
	return array('dependencies' => array('wp-blocks', 'wp-components', 'wp-edit-widgets', 'wp-element'), 'version' => '6c63fc0260e6a67764a3652eff7a928f');
} else {
	return array('dependencies' => array('wp-blocks', 'wp-components', 'wp-editor', 'wp-element'), 'version' => '6c63fc0260e6a67764a3652eff7a925f');
}