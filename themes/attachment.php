<?php
/**
 * The template for displaying Sell Media Taxonomy Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package Sell Media
 * @since 0.1
 */
get_header(); ?>

<div id="primary" class="content-area">
        <main id="main" class="site-main" role="main">

        <?php while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
                </header><!-- .entry-header -->

                <div class="entry-content">
                    <?php
                        the_content( sprintf(
                            /* translators: %s: Name of current post. */
                            wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', '_s' ), array( 'span' => array( 'class' => array() ) ) ),
                            the_title( '<span class="screen-reader-text">"', '"</span>', false )
                        ) );
                        wp_link_pages( array(
                            'before' => '<div class="page-links">' . esc_html__( 'Pages:', '_s' ),
                            'after'  => '</div>',
                        ) );
                    ?>
                </div><!-- .entry-content -->

                <footer class="entry-footer">
                    
                </footer><!-- .entry-footer -->
            </article><!-- #post-## -->
        <?php the_post_navigation(); ?>
        <?php endwhile; ?>

        </main><!-- #main -->
    </div><!-- #primary -->

<?php get_footer(); ?>