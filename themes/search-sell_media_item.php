<?php

/**
 * The template for displaying Search pages in Sell Media.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Sell Media
 * @since 0.1
 */

status_header('200 OK');
get_header();

/**
 * Only run this if we have a keyword OR collection
 */
if ( ! empty( $_GET['keyword'] ) || ! empty( $_GET['collection'] ) ){

	/**
	 * Esc our get param, trim white space and only add it
	 * if we have a value
	 */
	$clean_get = array();
	foreach( $_GET as $k => $v ){
		if ( ! empty( $v ) )
			$clean_get[ $k ] = trim( esc_attr( $v ) );
	}


	/**
	 * Set-up our basic params
	 */
	$args = array(
	    'post_type' => array('sell_media_item'),
	    'post_status' => 'publish',
	    'posts_per_page' => -1
	    );



	/**
	 * If we have both taxonomies we add the relation
	 */
	if ( ! empty( $clean_get['keyword'] ) && ! empty( $clean_get['collection'] ) ){
		$args['tax_query']['relation'] = 'OR';
	}

	$search_terms = array(
		's' => get_search_query(),
		'post_type' => $_GET['post_type']
		);
	/**
	 * If we have a keyword we add that.
	 */
	if ( ! empty( $clean_get['keyword'] ) ){
	    $args['tax_query'][] = array(
	    	'taxonomy' => 'keywords',
	    	'field'    => 'id',
	        'terms'    => $clean_get['keyword']
	    );

	    $search_terms['keyword'] = (array)get_term_by('id', $_GET['keyword'], 'keywords');
	}


	/**
	 * If we have a collection we add that.
	 */
	if ( ! empty( $clean_get['collection'] ) ){
	    $args['tax_query'][] = array(
		    'taxonomy' => 'collection',
		    'field'    => 'id',
		    'terms'    => $clean_get['collection']
	    );
	    $search_terms['collection'] = (array)get_term_by('id', $_GET['collection'], 'collection');
	}

	add_filter( 'posts_where', 'sell_media_search_where' );
	$my_query = new WP_Query( $args );
}?>
	<div id="sell-media-archive" class="sell-media">
		<div id="content" role="main">
			<?php echo do_shortcode( '[sell_media_searchform]' ); ?>
			<?php if ( $my_query->posts ) : $i=0; ?>
				<header class="entry-header">
					<h1 class="entry-title">
						<?php if ( ! empty( $search_terms['s'] ) ) : ?><?php printf( __( 'Search Results for: %s', 'sell_media' ), '<span>'.$search_terms['s'].'</span>' ); ?><?php endif; ?>
					</h1>
				</header>
				<div class="sell-media-grid-container">
					<?php foreach( $my_query->posts as $post ) : setup_postdata( $post ); $i++; ?>
						<?php $end = ( $i %3 == 0 ) ? ' end' : null; ?>
						<div class="sell-media-grid<?php echo $end; ?>">
							<a href="<?php the_permalink(); ?>"><?php sell_media_item_icon( get_post_meta( $post->ID, '_sell_media_attachment_id', true ) ); ?></a>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<?php sell_media_item_buy_button( $post->ID, 'text', 'Purchase' ); ?>
						</div>
					<?php endforeach; remove_filter( 'posts_where', 'sell_media_search_where' ); ?>
					<?php sell_media_pagination_filter(); ?>
				</div>
			<?php else : ?>
				<p><?php _e( 'Nothing Found', 'sell_media' ); ?></p>
                <?php echo do_shortcode( '[sell_media_searchform]' ); ?>
            <?php endif; $i = 0; ?>

		</div><!-- #content -->
	</div><!-- #sell_media-single .sell_media -->
<?php get_footer(); ?>