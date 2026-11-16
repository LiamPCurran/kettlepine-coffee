<?php
/**
 * Title: Kettlepine hero
 * Slug: kettlepine/hero
 * Inserter: no
 */
$kp_shop  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$kp_about = get_page_by_path( 'about' );
$kp_about = $kp_about ? get_permalink( $kp_about ) : home_url( '/about/' );
?>
<!-- wp:group {"align":"full","className":"kp-hero","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"backgroundColor":"accent-5","layout":{"type":"constrained","contentSize":"1200px"}} -->
<div class="wp-block-group alignfull kp-hero has-accent-5-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
<!-- wp:columns {"verticalAlignment":"center"} -->
<div class="wp-block-columns are-vertically-aligned-center">
<!-- wp:column {"verticalAlignment":"center","width":"52%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:52%">
<!-- wp:heading {"level":1,"fontSize":"xx-large"} -->
<h1 class="wp-block-heading has-xx-large-font-size">Small-batch coffee, roasted in Ottawa and shipped the week it's roasted</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">We roast in small batches, ship within two business days and print the roast date on every bag. Taste the difference fresh makes.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons -->
<div class="wp-block-buttons">
<!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $kp_shop ); ?>">Shop coffee</a></div><!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline"} --><div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $kp_about ); ?>">Our story</a></div><!-- /wp:button -->
</div>
<!-- /wp:buttons -->
<!-- wp:html -->
<ul class="kp-badges"><li>Roast date on every bag</li><li>Free shipping over $50</li><li>Ships in 2 business days</li></ul>
<!-- /wp:html -->
</div>
<!-- /wp:column -->
<!-- wp:column {"verticalAlignment":"center","width":"48%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:48%">
<!-- wp:html -->
<img src="<?php echo esc_url( get_theme_file_uri( 'assets/hero.webp' ) ); ?>" srcset="<?php echo esc_url( get_theme_file_uri( 'assets/hero-600.webp' ) ); ?> 600w, <?php echo esc_url( get_theme_file_uri( 'assets/hero.webp' ) ); ?> 1200w" sizes="(max-width: 781px) 100vw, 600px" width="1200" height="900" fetchpriority="high" decoding="async" alt="Three Kettlepine coffee bags in front of a pine forest illustration">
<!-- /wp:html -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
</div>
<!-- /wp:group -->
