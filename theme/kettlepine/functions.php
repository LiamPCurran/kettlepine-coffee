<?php
/**
 * Kettlepine child theme: small, safe performance and SEO tweaks only.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'kettlepine', get_stylesheet_uri(), array(), '1.0.0' );
} );

// No emoji polyfill: modern browsers render emoji natively.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );

// Trim head clutter.
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );

// Load block styles only for blocks actually on the page.
add_filter( 'should_load_separate_core_block_assets', '__return_true' );

// Preload the hero image on the front page (it's the LCP element).
add_action( 'wp_head', function () {
	if ( is_front_page() ) {
		printf(
			'<link rel="preload" as="image" href="%1$s" imagesrcset="%2$s 600w, %1$s 1200w" imagesizes="(max-width: 781px) 100vw, 600px" fetchpriority="high">' . "\n",
			esc_url( get_theme_file_uri( 'assets/hero.webp' ) ),
			esc_url( get_theme_file_uri( 'assets/hero-600.webp' ) )
		);
	}
}, 1 );

// Meta descriptions without an SEO plugin (a small store doesn't need one).
add_action( 'wp_head', function () {
	$desc = '';
	if ( is_front_page() ) {
		$desc = 'Small-batch specialty coffee roasted in Ottawa and shipped fresh across Canada. Blends, single origins, decaf and brewing gear. Free shipping over $50.';
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$desc = 'Shop fresh-roasted coffee from Kettlepine: breakfast and espresso blends, single-origin coffees, water-process decaf and pour-over gear.';
	} elseif ( is_tax( 'product_cat' ) ) {
		$desc = wp_strip_all_tags( term_description() );
	} elseif ( is_singular() ) {
		$post = get_queried_object();
		$desc = has_excerpt( $post ) ? get_the_excerpt( $post ) : wp_trim_words( wp_strip_all_tags( $post->post_content ), 28, '…' );
	}
	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( wp_strip_all_tags( $desc ) ) );
	}
}, 2 );

// WooCommerce auto-inserts a mini-cart into the header via block hooks; the header already has a "Cart" link,
// and the mini-cart drawer causes an aria-hidden-focus accessibility issue.
add_filter( 'hooked_block_types', function ( $hooked, $position, $anchor ) {
	return array_values( array_diff( $hooked, array( 'woocommerce/mini-cart' ) ) );
}, 20, 3 );
