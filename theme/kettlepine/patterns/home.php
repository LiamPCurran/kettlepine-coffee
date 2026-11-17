<?php
/**
 * Title: Kettlepine home page
 * Slug: kettlepine/home
 * Inserter: no
 */
$kp_img  = function ( $name, $w ) { return esc_url( get_theme_file_uri( "assets/img/{$name}-{$w}.webp" ) ); };
$kp_shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$kp_page = function ( $slug ) { $p = get_page_by_path( $slug ); return esc_url( $p ? get_permalink( $p ) : home_url( "/{$slug}/" ) ); };
$kp_cat  = function ( $name ) {
	$t = get_term_by( 'name', $name, 'product_cat' );
	return esc_url( $t && ! is_wp_error( $t ) ? get_term_link( $t ) : home_url( '/shop/' ) );
};
$kp_sampler = get_page_by_path( 'roasters-sampler', OBJECT, 'product' );
$kp_sampler = $kp_sampler ? esc_url( get_permalink( $kp_sampler ) ) : esc_url( $kp_shop );
$kp_ico = array(
	'flame' => '<path d="M12 2c3 4.5 6 7 6 12a6 6 0 0 1-12 0c0-3 1.5-4.5 3-6 0 2 .8 3.5 2 4-.6-4 0-7 1-10z"/>',
	'truck' => '<path d="M1 3h15v13H1zM16 8h4l3 3v5h-7z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
	'gift'  => '<path d="M20 12v10H4V12M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>',
	'leaf'  => '<path d="M11 20A7 7 0 0 1 4 13c0-6 6-10 16-10 0 10-4 16-10 16"/><path d="M4 21c4-6 8-9 12-11"/>',
);
$kp_svg = function ( $k ) use ( $kp_ico ) { return '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $kp_ico[ $k ] . '</svg>'; };
$kp_star = str_repeat( '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/></svg>', 5 );
ob_start(); // "before" mode rewrites the markup below (see the end of this file)
?>
<!-- wp:html -->
<section class="kp-hero alignfull">
<img class="kp-hero-bg" src="<?php echo $kp_img( 'hero', 800 ); ?>" srcset="<?php echo $kp_img( 'hero', 800 ); ?> 800w, <?php echo $kp_img( 'hero', 1600 ); ?> 1600w" sizes="100vw" width="1600" height="1000" fetchpriority="high" decoding="async" alt="Freshly roasted coffee beans pouring out of a drum roaster into the cooling tray">
<div class="kp-wrap kp-hero-inner">
<p class="kp-eyebrow">Small-batch roastery · Ottawa, Ontario</p>
<h1>Coffee roasted this week, not this season</h1>
<p class="kp-lede">We roast in small batches, ship within two business days and print the roast date on every bag. Taste what fresh actually means.</p>
<div class="kp-ctas"><a class="kp-btn" href="<?php echo esc_url( $kp_shop ); ?>">Shop coffee</a><a class="kp-btn kp-btn-ghost" href="<?php echo $kp_sampler; ?>">Try the sampler</a></div>
</div>
</section>
<!-- /wp:html -->

<!-- wp:html -->
<section class="kp-props alignfull"><div class="kp-wrap">
<div><?php echo $kp_svg( 'flame' ); ?><strong>Roasted to order</strong><span>Roast date printed on every bag</span></div>
<div><?php echo $kp_svg( 'truck' ); ?><strong>Ships in 2 business days</strong><span>Anywhere in Canada</span></div>
<div><?php echo $kp_svg( 'gift' ); ?><strong>Free shipping over $50</strong><span>$8 flat rate under that</span></div>
<div><?php echo $kp_svg( 'leaf' ); ?><strong>Traceable, seasonal lots</strong><span>Origin and producer on every bag</span></div>
</div></section>
<!-- /wp:html -->

<!-- wp:group {"align":"wide","className":"kp-section","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide kp-section">
<!-- wp:html -->
<div class="kp-head"><div><p class="kp-eyebrow">Customer favourites</p><h2>Our best sellers</h2></div><a class="kp-link" href="<?php echo esc_url( $kp_shop ); ?>">Shop all coffee →</a></div>
<!-- /wp:html -->
<!-- wp:html -->
<?php echo do_shortcode( '[products limit="4" columns="4" visibility="featured" orderby="menu_order" order="ASC"]' ); ?>
<!-- /wp:html -->
</div>
<!-- /wp:group -->

<!-- wp:html -->
<section class="kp-section kp-cats alignwide">
<div class="kp-head"><div><p class="kp-eyebrow">Find your coffee</p><h2>Shop by category</h2></div></div>
<div class="kp-cat-grid">
<?php
foreach ( array(
	array( 'Blends', 'blends', 'Balanced, chocolatey, easy to love' ),
	array( 'Single Origin', 'single-origin', 'Bright, fruity, one place in every cup' ),
	array( 'Decaf', 'decaf', 'Water-process, roasted for sweetness' ),
	array( 'Brewing Gear', 'brewing-gear', 'Everything for a better pour-over' ),
	array( 'Gifts', 'gifts', 'Samplers for the coffee lover' ),
) as $c ) {
	printf( '<a class="kp-cat" href="%s"><img src="%s" width="500" height="500" loading="lazy" decoding="async" alt=""><span><strong>%s</strong><small>%s</small></span></a>', $kp_cat( $c[0] ), $kp_img( $c[1], 500 ), esc_html( $c[0] ), esc_html( $c[2] ) );
}
?>
</div></section>
<!-- /wp:html -->

<!-- wp:html -->
<section class="kp-story alignfull"><div class="kp-wrap kp-split">
<div class="kp-story-img"><img src="<?php echo $kp_img( 'story', 600 ); ?>" srcset="<?php echo $kp_img( 'story', 600 ); ?> 600w, <?php echo $kp_img( 'story', 800 ); ?> 800w" sizes="(max-width: 900px) 100vw, 520px" width="800" height="1000" loading="lazy" decoding="async" alt="Fresh coffee beans turning in the roaster's cooling tray">
<div class="kp-stamp"><strong>Roasted</strong><span>Tuesday</span><small>Shipped Wednesday</small></div></div>
<div>
<p class="kp-eyebrow">Our story</p>
<h2>It started on a camp stove up the Ottawa Valley</h2>
<p>Our founder roasted coffee in a hand-cranked drum over a propane burner on long weekends, and friends kept asking for bags to take home. We still roast the way we started: small batches, one coffee at a time, tasting every roast before it ships.</p>
<ul class="kp-ticks"><li>Every coffee gets its own roast profile</li><li>Plain-English tasting notes and a brew guide on every bag</li><li>A 30-day satisfaction promise: love it or we'll make it right</li></ul>
<a class="kp-btn" href="<?php echo $kp_page( 'about' ); ?>">Read our story</a>
</div></div></section>
<!-- /wp:html -->

<!-- wp:html -->
<section class="kp-section alignwide"><div class="kp-promo">
<div class="kp-promo-copy"><p class="kp-eyebrow light">Not sure where to start?</p>
<h2>The Roaster's Sampler</h2>
<p>Three 120 g bags, one from each style: a chocolatey blend, a bright single origin and our latest seasonal pick. The easiest way to find your favourite.</p>
<div class="kp-ctas"><a class="kp-btn kp-btn-honey" href="<?php echo $kp_sampler; ?>">Shop the sampler · $34</a></div></div>
<img src="<?php echo $kp_img( 'pourover', 600 ); ?>" srcset="<?php echo $kp_img( 'pourover', 600 ); ?> 600w, <?php echo $kp_img( 'pourover', 1000 ); ?> 1000w" sizes="(max-width: 900px) 100vw, 520px" width="1000" height="1000" loading="lazy" decoding="async" alt="Hot water being poured over coffee in a pour-over dripper">
</div></section>
<!-- /wp:html -->

<!-- wp:html -->
<section class="kp-section kp-reviews alignwide">
<div class="kp-head center"><div><p class="kp-eyebrow">Reviews</p><h2>What our regulars say</h2>
<p class="kp-rating"><span class="kp-stars" role="img" aria-label="Rated 4.9 out of 5"><?php echo $kp_star; ?></span> 4.9 average from 640+ reviews</p></div></div>
<div class="kp-review-grid">
<figure><span class="kp-stars" role="img" aria-label="5 out of 5 stars"><?php echo $kp_star; ?></span><blockquote>“The Guji actually tastes like blueberries. I didn't believe the bag until I brewed it.”</blockquote><figcaption><strong>Hannah R.</strong> · Ethiopia Guji Natural</figcaption></figure>
<figure><span class="kp-stars" role="img" aria-label="5 out of 5 stars"><?php echo $kp_star; ?></span><blockquote>“Roast date on the bag, at my door two days later. My grocery-store coffee can't compete.”</blockquote><figcaption><strong>Marcus T.</strong> · First Light Breakfast Blend</figcaption></figure>
<figure><span class="kp-stars" role="img" aria-label="5 out of 5 stars"><?php echo $kp_star; ?></span><blockquote>“Finally a decaf that doesn't taste like cardboard. Night Owl is in my cupboard permanently.”</blockquote><figcaption><strong>Jess L.</strong> · Night Owl Decaf</figcaption></figure>
</div></section>
<!-- /wp:html -->

<!-- wp:html -->
<section class="kp-news alignfull"><div class="kp-wrap kp-news-inner">
<div><h2>Get 10% off your first bag</h2><p>New roasts, brew tips and the occasional subscriber-only coffee. One email a month, no spam.</p></div>
<form class="kp-news-form" onsubmit="event.preventDefault();this.querySelector('.kp-news-msg').hidden=false;this.reset();">
<label class="screen-reader-text" for="kp-email">Email address</label>
<input id="kp-email" type="email" placeholder="you@example.com" autocomplete="email" required>
<button type="submit">Subscribe</button>
<p class="kp-news-msg" role="status" hidden>Thanks! This is a concept store, so nothing was sent.</p>
</form></div></section>
<!-- /wp:html -->
<?php
$kp_html = ob_get_clean();
if ( function_exists( 'kettlepine_before' ) && kettlepine_before() ) {
	// Typical unoptimized build: full-size PNG/JPEG photos, no srcset, no dimensions, no lazy loading,
	// and the main headline as a styled <div> instead of an <h1>.
	$kp_html = preg_replace_callback(
		'#<img[^>]*src="([^"]*/assets/img/)([a-z-]+)-\d+\.webp"[^>]*alt="([^"]*)"[^>]*>#',
		function ( $m ) {
			$ext = in_array( $m[2], array( 'hero', 'story', 'pourover' ), true ) ? 'png' : 'jpg';
			$cls = preg_match( '#class="([^"]+)"#', $m[0], $c ) ? ' class="' . $c[1] . '"' : '';
			return '<img' . $cls . ' src="' . str_replace( '/assets/img/', '/assets/img-before/', $m[1] ) . $m[2] . '.' . $ext . '" alt="' . $m[3] . '">';
		},
		$kp_html
	);
	$kp_html = preg_replace( '#<h1>(.*?)</h1>#s', '<div class="kp-h1">$1</div>', $kp_html );
}
echo $kp_html;
