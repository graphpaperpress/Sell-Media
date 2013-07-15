<?php
/**
 * The template for displaying Search pages in Sell Media.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Sell Media
 * @since 0.1
 */

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

			<?php if ( $my_query->posts ) : $i=0; ?>
				<div class="sell-media-grid-container">
					<header class="entry-header">
						<h1 class="entry-title">
							<?php if ( $search_terms['s'] ) : ?><?php printf( __( 'Search Results for: %s', 'sell_media' ), '<span>'.$search_terms['s'].'</span>' ); ?><?php endif; ?>
							<?php if ( $search_terms['keyword'] ) : ?><?php printf( __( 'Keyword: %s', 'sell_media' ), '<span>'.$search_terms['keyword']['name'].'</span>' ); ?><?php endif; ?>
							<?php if ( $search_terms['collection'] ) : ?><?php printf( __( 'Collection: %s', 'sell_media' ), '<span>'.$search_terms['collection']['name'].'</span>' ); ?><?php endif; ?>
						</h1>
					</header>
					<?php foreach( $my_query->posts as $post ) : setup_postdata( $post ); ?>
						<?php
							if ( $i %3 == 0)
								$end = ' end';
							else
							$end = null;
						?>
						<div class="sell-media-grid<?php echo $end; ?>">
							<a href="<?php the_permalink(); ?>"><?php sell_media_item_icon( get_post_meta( $post->ID, '_sell_media_attachment_id', true ) ); ?></a>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<?php sell_media_item_buy_button( $post->ID, 'text', 'Purchase' ); ?>
						</div>
					<?php endforeach; remove_filter( 'posts_where', 'sell_media_search_where' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>

			<header class="entry-header">
				<h1 class="entry-title"><?php printf( __( 'Search Results for: %s', 'sell_media' ), '<span>' . $_GET['keyword'] . '</span>' ); ?></h1>
			</header>

			<div class="sell-media-grid-container">
				<?php $i = 0; ?>
				<?php while ( have_posts() ) : the_post(); $i++; ?>
					<?php
						if ( $i %3 == 0)
							$end = ' end';
						else
							$end = null;
					?>
					<div class="sell-media-grid<?php echo $end; ?>">
						<a href="<?php the_permalink(); ?>"><?php sell_media_item_icon( get_post_meta( $post->ID, '_sell_media_attachment_id', true ) ); ?></a>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<?php sell_media_item_buy_button( $post->ID, 'text', 'Purchase' ); ?>
					</div>

				<?php endwhile; wp_reset_postdata(); ?>

				<?php sell_media_pagination_filter(); ?>
			</div><!-- .sell-media-grid-container -->
			<?php else : ?>
				<p><?php _e( 'Nothing Found', 'sell_media' ); ?></p>
				<?php echo do_shortcode( '[sell_media_searchform]' ); ?>
			<?php endif; $i = 0; ?>
		</div><!-- #content -->
	</div><!-- #sell_media-single .sell_media -->
<?php get_footer(); ?>