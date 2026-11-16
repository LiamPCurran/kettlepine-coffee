<?php
/**
 * Plugin Name: Kettlepine Demo Setup
 * Description: One-time setup for the Kettlepine Coffee Roasters concept store (fictional brand): products, pages, menu, shipping and store settings. Portfolio demo by Liam Curran.
 * Version: 1.0.0
 * Requires Plugins: woocommerce
 * License: GPL-2.0-or-later
 */
defined( 'ABSPATH' ) || exit;

function kettlepine_p( $text ) {
	return "<!-- wp:paragraph -->\n<p>" . $text . "</p>\n<!-- /wp:paragraph -->\n";
}
function kettlepine_h( $text, $level = 2 ) {
	return "<!-- wp:heading {\"level\":$level} -->\n<h$level class=\"wp-block-heading\">$text</h$level>\n<!-- /wp:heading -->\n";
}

function kettlepine_attach_image( $file, $title ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$bits = wp_upload_bits( basename( $file ), null, file_get_contents( $file ) );
	if ( ! empty( $bits['error'] ) ) {
		return 0;
	}
	$id = wp_insert_attachment( array(
		'post_mime_type' => 'image/webp',
		'post_title'     => $title,
		'post_status'    => 'inherit',
	), $bits['file'] );
	if ( $id && ! is_wp_error( $id ) ) {
		$meta = wp_generate_attachment_metadata( $id, $bits['file'] );
		if ( $meta ) {
			wp_update_attachment_metadata( $id, $meta );
		}
		update_post_meta( $id, '_wp_attachment_image_alt', $title );
	}
	return (int) $id;
}

function kettlepine_setup() {
	if ( get_option( 'kettlepine_setup_done' ) ) {
		return 'already done';
	}

	// ---- Site + store settings ----
	update_option( 'blogname', 'Kettlepine Coffee Roasters' );
	update_option( 'blogdescription', 'Small-batch coffee, roasted in Ottawa' );
	update_option( 'permalink_structure', '/%postname%/' );
	update_option( 'woocommerce_currency', 'CAD' );
	update_option( 'woocommerce_default_country', 'CA:ON' );
	update_option( 'woocommerce_store_city', 'Ottawa' );
	update_option( 'woocommerce_weight_unit', 'g' );
	update_option( 'woocommerce_coming_soon', 'no' );
	update_option( 'woocommerce_store_pages_only', 'no' );
	update_option( 'woocommerce_allow_tracking', 'no' );
	update_option( 'woocommerce_show_marketplace_suggestions', 'no' );
	update_option( 'woocommerce_task_list_hidden', 'yes' );
	update_option( 'woocommerce_onboarding_profile', array( 'skipped' => true ) );
	update_option( 'woocommerce_demo_store', 'yes' );
	update_option( 'woocommerce_demo_store_notice', 'Concept store by Liam Curran – fictional brand. No real orders are processed.' );
	update_option( 'woocommerce_cod_settings', array(
		'enabled'            => 'yes',
		'title'              => 'Demo order (no payment taken)',
		'description'        => 'This is a concept store. Placing an order here does not charge you or ship anything.',
		'instructions'       => 'Thanks for trying the demo checkout. No real order was placed.',
		'enable_for_methods' => array(),
		'enable_for_virtual' => 'yes',
	) );

	// Remove default content.
	foreach ( array( 'hello-world', 'sample-page' ) as $slug ) {
		$p = get_page_by_path( $slug, OBJECT, array( 'post', 'page' ) );
		if ( $p ) {
			wp_delete_post( $p->ID, true );
		}
	}

	// ---- Shipping: Canada flat rate, free over $50 ----
	if ( class_exists( 'WC_Shipping_Zone' ) ) {
		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( 'Canada' );
		$zone->add_location( 'CA', 'country' );
		$zone->save();
		$flat_id = $zone->add_shipping_method( 'flat_rate' );
		update_option( 'woocommerce_flat_rate_' . $flat_id . '_settings', array( 'title' => 'Standard shipping (2–5 business days)', 'tax_status' => 'taxable', 'cost' => '8' ) );
		$free_id = $zone->add_shipping_method( 'free_shipping' );
		update_option( 'woocommerce_free_shipping_' . $free_id . '_settings', array( 'title' => 'Free shipping', 'requires' => 'min_amount', 'min_amount' => '50' ) );
	}

	// ---- Categories ----
	$cat_desc = array(
		'Blends'        => 'Coffees built for balance and consistency, from an easy-drinking breakfast blend to a chocolatey espresso.',
		'Single Origin' => 'Coffees from one region, roasted to show off what makes each place taste different.',
		'Decaf'         => 'Water-process decaf, roasted for sweetness. All the flavour, none of the 2am ceiling-staring.',
		'Gifts'         => 'Samplers and gift boxes for the coffee person in your life.',
		'Brewing Gear'  => 'Simple, durable gear for brewing better coffee at home.',
	);
	$cats = array();
	$order = 0;
	foreach ( $cat_desc as $name => $desc ) {
		$t = term_exists( $name, 'product_cat' );
		if ( ! $t ) {
			$t = wp_insert_term( $name, 'product_cat', array( 'description' => $desc ) );
		}
		$cats[ $name ] = (int) ( is_array( $t ) ? $t['term_id'] : $t );
		update_term_meta( $cats[ $name ], 'order', $order++ );
	}
	$uncat = get_term_by( 'slug', 'uncategorized', 'product_cat' );

	// ---- Products ----
	$data = json_decode( file_get_contents( __DIR__ . '/products.json' ), true );
	$menu_order = 0;
	foreach ( $data as $d ) {
		$p = new WC_Product_Simple();
		$p->set_name( $d['name'] );
		$p->set_slug( $d['slug'] );
		$p->set_sku( $d['sku'] );
		$p->set_regular_price( $d['price'] );
		$p->set_short_description( $d['short'] );
		$p->set_description( $d['long'] );
		$p->set_category_ids( array( $cats[ $d['category'] ] ) );
		$p->set_featured( ! empty( $d['featured'] ) );
		$p->set_menu_order( $menu_order++ );
		$p->set_manage_stock( false );
		$p->set_stock_status( 'instock' );
		$img = __DIR__ . '/images/' . $d['slug'] . '.webp';
		if ( file_exists( $img ) ) {
			$img_id = kettlepine_attach_image( $img, $d['name'] . ( $d['weight'] ? ', ' . $d['weight'] . ' bag' : '' ) );
			$p->set_image_id( $img_id );
			// First product image in each category becomes the category thumbnail.
			if ( $img_id && ! get_term_meta( $cats[ $d['category'] ], 'thumbnail_id', true ) ) {
				update_term_meta( $cats[ $d['category'] ], 'thumbnail_id', $img_id );
			}
		}
		$attrs = array();
		foreach ( array( 'Roast' => $d['roast'], 'Tasting notes' => $d['notes'], 'Size' => $d['weight'] ) as $label => $val ) {
			if ( $val ) {
				$a = new WC_Product_Attribute();
				$a->set_name( $label );
				$a->set_options( array( $val ) );
				$a->set_visible( true );
				$attrs[] = $a;
			}
		}
		$p->set_attributes( $attrs );
		$p->save();
	}
	if ( $uncat && 0 === (int) $uncat->count ) {
		// Keep it but out of sight; WooCommerce requires a default category.
		update_option( 'default_product_cat', $cats['Blends'] );
		wp_delete_term( $uncat->term_id, 'product_cat' );
	}

	// ---- Pages ----
	$pages = array(
		'about'              => array( 'Our Story',
			kettlepine_p( 'Kettlepine started on a camp stove. Our founder roasted coffee in a hand-cranked drum over a propane burner on long weekends up the Ottawa Valley, and friends kept asking for bags to take home.' )
			. kettlepine_p( 'We still roast the way we started: small batches, one coffee at a time, tasting every roast before it ships. The difference now is a proper roaster and a website, so you don\'t have to know us to get a bag.' )
			. kettlepine_h( 'What we believe' )
			. kettlepine_p( '<strong>Fresh is non-negotiable.</strong> We print the roast date on every bag and ship within two business days of roasting.' )
			. kettlepine_p( '<strong>Good coffee shouldn\'t need a dictionary.</strong> Every coffee comes with plain-English tasting notes and a simple brew guide.' )
			. kettlepine_p( '<strong>Start where you are.</strong> Drip machine, French press or a $2,000 espresso setup: we\'ll help you find a coffee that tastes great in it.' ),
			'How a camp-stove hobby became a small-batch Ottawa coffee roaster. Fresh coffee, plain-English tasting notes and help finding a coffee you love.' ),
		'shipping-returns'   => array( 'Shipping & Returns',
			kettlepine_h( 'Shipping' )
			. kettlepine_p( 'We roast to order and ship within two business days. Standard shipping anywhere in Canada is $8 and takes 2 to 5 business days. Orders over $50 ship free.' )
			. kettlepine_h( 'Not happy with your coffee?' )
			. kettlepine_p( 'Tell us within 30 days and we\'ll send a different coffee or refund you. You don\'t need to send back an open bag of beans.' )
			. kettlepine_h( 'Brewing gear' )
			. kettlepine_p( 'Unused gear can be returned within 30 days for a full refund. Arrived broken? Send us a photo and we\'ll replace it.' ),
			'Kettlepine ships fresh-roasted coffee across Canada within two business days. $8 flat-rate shipping, free over $50, and a 30-day satisfaction promise.' ),
		'contact'            => array( 'Contact',
			kettlepine_p( 'Questions about an order, a coffee or a wholesale account? Email <a href="mailto:hello@example.com">hello@example.com</a> and a real person will reply within one business day.' )
			. kettlepine_p( 'Prefer the phone? Call (613) 555-0187, Monday to Friday, 9am to 4pm Eastern.' ),
			'Contact Kettlepine Coffee Roasters about orders, coffee recommendations or wholesale. We reply within one business day.' ),
	);
	$page_ids = array();
	foreach ( $pages as $slug => $pg ) {
		$page_ids[ $slug ] = wp_insert_post( array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $pg[0],
			'post_content' => $pg[1],
			'post_excerpt' => $pg[2],
		) );
		add_post_type_support( 'page', 'excerpt' );
	}
	// WooCommerce system pages stay out of the footer page list.
	$exclude = array_filter( array( wc_get_page_id( 'cart' ), wc_get_page_id( 'checkout' ), wc_get_page_id( 'myaccount' ), wc_get_page_id( 'shop' ) ) );

	// ---- Header navigation (block themes use the most recent wp_navigation post) ----
	$links = array(
		array( 'Shop', wc_get_page_id( 'shop' ) ),
		array( 'Our Story', $page_ids['about'] ),
		array( 'Shipping', $page_ids['shipping-returns'] ),
		array( 'Contact', $page_ids['contact'] ),
		array( 'Cart', wc_get_page_id( 'cart' ) ),
	);
	$nav = '';
	foreach ( $links as $l ) {
		$nav .= sprintf( '<!-- wp:navigation-link {"label":"%s","type":"page","id":%d,"url":"%s","kind":"post-type"} /-->', esc_attr( $l[0] ), $l[1], esc_url( get_permalink( $l[1] ) ) );
	}
	wp_insert_post( array( 'post_type' => 'wp_navigation', 'post_status' => 'publish', 'post_title' => 'Main', 'post_content' => $nav ) );

	update_option( 'kettlepine_footer_exclude', $exclude );
	flush_rewrite_rules( false );
	update_option( 'kettlepine_setup_done', 1 );
	return 'done';
}

// Hide WooCommerce system pages (Cart, Checkout, My account, Shop) from the footer page list.
add_filter( 'get_pages', function ( $pages ) {
	if ( is_admin() ) {
		return $pages;
	}
	$exclude = (array) get_option( 'kettlepine_footer_exclude', array() );
	return array_values( array_filter( $pages, function ( $p ) use ( $exclude ) {
		return ! in_array( (int) $p->ID, array_map( 'intval', $exclude ), true );
	} ) );
} );
