# Kettlepine Coffee Roasters: WooCommerce store template

**Concept project by Liam Curran. Kettlepine is a fictional brand. Products, prices and reviews are illustrative, and no real orders are processed.**

**Live site:** https://liampcurran.github.io/kettlepine-coffee/

A small-batch coffee store built on WordPress and WooCommerce. The live site is a **static snapshot** of that build (WordPress's own HTML, CSS and images), hosted on GitHub Pages. Because GitHub Pages can't run PHP, the snapshot swaps WooCommerce's server cart for a small browser-only demo cart (`static/kp-cart.js`). You can add to cart, change quantities and go through a demo checkout; nothing is sent or charged.

- **Theme:** a lightweight child theme of Twenty Twenty-Five (`theme/kettlepine`). The homepage follows patterns from leading specialty-coffee stores: an announcement bar, a full-bleed photo hero, value props, best sellers with roast level and tasting notes on every card, photo category tiles, a brand-story split, a sampler promo, reviews and a newsletter signup.
  - Self-hosted Fraunces + DM Sans fonts and responsive WebP photos, with a preloaded hero image.
  - A reassurance panel under Add to cart.
  - jQuery and WooCommerce scripts deferred, and jQuery Migrate removed.
  - Meta descriptions without an SEO plugin, and trimmed `<head>` bloat.
- **Store setup plugin** (`plugin/kettlepine-setup`): creates 8 products with full descriptions, attributes and images, 5 categories, pages, navigation, Canada shipping ($8 flat, free over $50) and a demo payment method.
- **Static export** (`export_static.py`): crawls the running store, copies every page and asset, and swaps the server-only parts for the demo cart.
- **Product data** (`data/products.json`): all product copy.

## Rebuild

```
python make_images.py      # product illustrations
python make_photos.py      # stock photos (from ../_stock) -> theme/kettlepine/assets/img/
python package.py LiamPCurran      # zips theme + plugin into dist/ (for installing on any WordPress site)

# run the real WordPress + WooCommerce store locally (port 9400):
npx @wp-playground/cli server --port=9400 --blueprint=./blueprint.local.json --mount-dir ./theme/kettlepine /wordpress/wp-content/themes/kettlepine --mount-dir ./plugin/kettlepine-setup /wordpress/wp-content/plugins/kettlepine-setup

python export_static.py    # -> _static/kettlepine-coffee/, then copy its contents into docs/
```

## Credits

- Photos: [Unsplash](https://unsplash.com/license) (free to use). Photo IDs: 6HR8vpjYUHo, Lk4UzBF6vss, 4KqHNQ3sJ6g, S8daAB_nJSg, MK60jE_6zXU, TYIzeCiZ_60, Y3AqmbmtLQI, F0XGFD9Z1Uk.
- Fonts: Fraunces and DM Sans (SIL Open Font License).
