# Kettlepine Coffee Roasters: WooCommerce concept store

**Concept project by Liam Curran. Kettlepine is a fictional brand. No real orders are processed, and checkout uses a demo-only payment method.**

**Live demo (runs in your browser via WordPress Playground; allow about 20 seconds to start):**
https://liampcurran.github.io/kettlepine-coffee/

A small-batch coffee store built on WordPress and WooCommerce:

- **Theme:** a lightweight child theme of Twenty Twenty-Five (`theme/kettlepine`). The homepage follows patterns from leading specialty-coffee stores: an announcement bar, a full-bleed photo hero, value props, best sellers with roast level and tasting notes on every card, photo category tiles, a brand-story split, a sampler promo, reviews and a newsletter signup. It also has self-hosted Fraunces + DM Sans fonts, responsive WebP photos, a preloaded hero, a reassurance panel under Add to cart, meta descriptions without an SEO plugin, and trimmed `<head>` bloat.
- **Store setup plugin** (`plugin/kettlepine-setup`): creates 8 products with full descriptions, attributes and images, 5 categories, pages, navigation, Canada shipping ($8 flat, free over $50) and a demo payment method, plus a "concept store" notice.
- **Blueprint** (`blueprint.json`): installs WooCommerce, the theme and the setup plugin, then builds the whole store automatically.
- **Product data** (`data/products.json`): all product copy, which is also used as a writing sample.

## Rebuild

```
python make_images.py      # product illustrations
python make_photos.py      # stock photos (from ../_stock) -> theme/kettlepine/assets/img/
python package.py LiamPCurran      # zips theme + plugin into dist/, writes blueprint.json
```

To test locally: `npx @wp-playground/cli server --blueprint=./blueprint.local.json --mount-dir ./theme/kettlepine /wordpress/wp-content/themes/kettlepine --mount-dir ./plugin/kettlepine-setup /wordpress/wp-content/plugins/kettlepine-setup`

## Credits

- Photos: [Unsplash](https://unsplash.com/license) (free to use). Photo IDs: 6HR8vpjYUHo, Lk4UzBF6vss, 4KqHNQ3sJ6g, S8daAB_nJSg, MK60jE_6zXU, TYIzeCiZ_60, Y3AqmbmtLQI, F0XGFD9Z1Uk.
- Fonts: Fraunces and DM Sans (SIL Open Font License).
