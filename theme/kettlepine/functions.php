<?php
/**
 * Kettlepine child theme.
 *
 * Two modes, for the WooCommerce speed case study:
 * - optimized (default): the performance and SEO tweaks below.
 * - "before" (option kettlepine_mode = before): how many stores ship. No script deferral, jQuery Migrate kept,
 *   emoji/head clutter kept, render-blocking Google Fonts, full-size PNG/JPEG photos without srcset or
 *   dimensions, no preloads, no meta descriptions, no schema, a weaker heading structure.
 */
defined( 'ABSPATH' ) || exit;

function kettlepine_before() {
	return 'before' === get_option( 'kettlepine_mode' );
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'kettlepine', get_stylesheet_uri(), array(), '2.1.0' );
	if ( kettlepine_before() ) {
		// Typical theme setup: fonts from Google's CDN as a render-blocking stylesheet.
		wp_enqueue_style( 'kettlepine-google-fonts', 'https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Fraunces:wght@400;500;600;700&display=swap', array(), null );
	}
} );

// "Before" mode: drop the self-hosted @font-face rules from theme.json (fonts come from Google instead).
add_filter( 'wp_theme_json_data_theme', function ( $theme_json ) {
	if ( ! kettlepine_before() ) {
		return $theme_json;
	}
	$data = $theme_json->get_data();
	foreach ( $data['settings']['typography']['fontFamilies']['theme'] ?? array() as $i => $family ) {
		unset( $data['settings']['typography']['fontFamilies']['theme'][ $i ]['fontFace'] );
	}
	return new WP_Theme_JSON_Data( $data, 'theme' );
} );

if ( ! kettlepine_before() ) {
	// Front-end JS performance: drop jQuery Migrate and load jQuery + WooCommerce scripts with `defer`
	// (WordPress only applies the strategy where every dependent script can be deferred too).
	add_action( 'wp_default_scripts', function ( $scripts ) {
		if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
			$scripts->registered['jquery']->deps = array( 'jquery-core' );
		}
	} );
	add_action( 'wp_enqueue_scripts', function () {
		foreach ( array( 'jquery-core', 'jquery', 'js-cookie', 'wc-js-cookie', 'woocommerce', 'wc-add-to-cart', 'wc-single-product', 'jquery-blockui', 'wc-jquery-blockui',
			'zoom', 'wc-zoom', 'flexslider', 'wc-flexslider', 'photoswipe', 'wc-photoswipe', 'photoswipe-ui-default', 'wc-photoswipe-ui-default' ) as $h ) {
			wp_script_add_data( $h, 'strategy', 'defer' );
		}
	}, 999 );

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

	// Preload the heading font, and the hero photo on the front page (it's the LCP element).
	add_action( 'wp_head', function () {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( get_theme_file_uri( 'assets/fonts/fraunces.woff2' ) ) );
		if ( is_front_page() ) {
			printf(
				'<link rel="preload" as="image" href="%1$s" imagesrcset="%1$s 800w, %2$s 1600w" imagesizes="100vw" fetchpriority="high">' . "\n",
				esc_url( get_theme_file_uri( 'assets/img/hero-800.webp' ) ),
				esc_url( get_theme_file_uri( 'assets/img/hero-1600.webp' ) )
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

	// Store schema on the home page (WooCommerce already outputs Product schema on product pages).
	add_action( 'wp_head', function () {
		if ( ! is_front_page() ) {
			return;
		}
		$home = home_url( '/' );
		$ld   = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array( '@type' => 'OnlineStore', '@id' => $home . '#store', 'name' => get_bloginfo( 'name' ), 'url' => $home,
					'description' => 'Small-batch specialty coffee roasted in Ottawa and shipped across Canada. (Concept store: fictional brand.)',
					'logo' => get_theme_file_uri( 'assets/favicon.svg' ), 'email' => 'hello@example.com',
					'address' => array( '@type' => 'PostalAddress', 'addressLocality' => 'Ottawa', 'addressRegion' => 'ON', 'addressCountry' => 'CA' ) ),
				array( '@type' => 'WebSite', '@id' => $home . '#website', 'name' => get_bloginfo( 'name' ), 'url' => $home, 'publisher' => array( '@id' => $home . '#store' ) ),
			),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
	}, 3 );
}

// Product cards: show roast level and tasting notes under the title (from the product's attributes).
add_action( 'woocommerce_after_shop_loop_item_title', function () {
	global $product;
	if ( ! $product ) {
		return;
	}
	$roast = $product->get_attribute( 'Roast' );
	$notes = $product->get_attribute( 'Tasting notes' );
	if ( $roast && 'Mixed' !== $roast ) {
		printf( '<span class="kp-roast">%s roast</span>', esc_html( $roast ) );
	}
	if ( $notes ) {
		printf( '<p class="kp-card-meta">%s</p>', esc_html( str_replace( ', ', ' · ', $notes ) ) );
	}
}, 5 );

// Single product: a short reassurance panel under the add-to-cart button.
add_action( 'woocommerce_after_add_to_cart_form', function () {
	echo '<ul class="kp-trust"><li>Roasted to order, shipped within 2 business days</li><li>Free shipping in Canada on orders over $50</li><li>30-day satisfaction promise</li></ul>';
} );

// WooCommerce auto-inserts a mini-cart into the header via block hooks; the header already has a "Cart" link,
// and the mini-cart drawer causes an aria-hidden-focus accessibility issue.
add_filter( 'hooked_block_types', function ( $hooked, $position, $anchor ) {
	return array_values( array_diff( $hooked, array( 'woocommerce/mini-cart' ) ) );
}, 20, 3 );
