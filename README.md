# Kettlepine Coffee Roasters: WooCommerce concept store

**Concept project by Liam Curran. Kettlepine is a fictional brand. No real orders are processed, and checkout uses a demo-only payment method.**

**Live demo (runs in your browser via WordPress Playground; allow about 20 seconds to start):**
https://liampcurran.github.io/kettlepine-coffee/

A small-batch coffee store built on WordPress and WooCommerce:

- **Theme:** a lightweight child theme of Twenty Twenty-Five (`theme/kettlepine`), with a custom palette, system fonts, a custom front page, header and footer, a preloaded hero image, meta descriptions without an SEO plugin, and trimmed `<head>` bloat.
- **Store setup plugin** (`plugin/kettlepine-setup`): creates 8 products with full descriptions, attributes and images, 5 categories, pages, navigation, Canada shipping ($8 flat, free over $50) and a demo payment method, plus a "concept store" notice.
- **Blueprint** (`blueprint.json`): installs WooCommerce, the theme and the setup plugin, then builds the whole store automatically.
- **Product data** (`data/products.json`): all product copy, which is also used as a writing sample.

## Rebuild

```
python make_images.py
python package.py LiamPCurran      # zips theme + plugin into dist/, writes blueprint.json
```

To test locally: `npx @wp-playground/cli server --blueprint=./blueprint.local.json --mount-dir ./theme/kettlepine /wordpress/wp-content/themes/kettlepine --mount-dir ./plugin/kettlepine-setup /wordpress/wp-content/plugins/kettlepine-setup`
