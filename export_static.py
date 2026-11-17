"""Export the locally running Kettlepine WordPress/WooCommerce store as a static site for GitHub Pages.

1. Start the store locally (see README: Playground CLI on port 9400).
2. python export_static.py
   -> _static/kettlepine-coffee/  (test: python -m http.server -d _static, open /kettlepine-coffee/)
   -> copied to docs/ (served by GitHub Pages at https://liampcurran.github.io/kettlepine-coffee/)

The pages are WordPress's own HTML. Server-only features are swapped out:
- add to cart / cart / checkout -> a small client-side demo cart (assets/kp-cart.js, localStorage, no payment)
- account, reviews form, sorting dropdown, ajax/tracking scripts -> removed
"""
import html, json, re, shutil, sys
from pathlib import Path
from urllib.parse import urljoin, urlparse

import requests
from bs4 import BeautifulSoup

# python export_static.py                -> optimized store (port 9400) -> docs/
# python export_static.py before         -> "before" store (port 9401) -> docs/before/
MODE = sys.argv[1] if len(sys.argv) > 1 else "after"
SRC = "http://127.0.0.1:9401" if MODE == "before" else "http://127.0.0.1:9400"
PREFIX = "/kettlepine-coffee/before" if MODE == "before" else "/kettlepine-coffee"
LIVE = "https://liampcurran.github.io" + PREFIX
CANON = "https://liampcurran.github.io/kettlepine-coffee"  # the before copy canonicalises to the optimized pages
ROOT = Path(__file__).parent
OUT = ROOT / "_static" / PREFIX.strip("/")
S = requests.Session()

DROP_SCRIPTS = ("order-attribution", "sourcebuster", "cart-fragments", "add-to-cart.min.js", "add-to-cart.js",
                "wc-blocks-checkout", "mini-cart", "customer-account", "catalog-sorting", "wp-emoji")


def local(u):
    """Absolute source URL -> output file path."""
    p = urlparse(u).path
    if p.endswith("/"):
        p += "index.html"
    return OUT / p.lstrip("/")


def rewrite(text):
    text = text.replace(SRC + "/", PREFIX + "/").replace(SRC, PREFIX)
    text = text.replace(SRC.replace("/", "\\/") + "\\/", PREFIX.replace("/", "\\/") + "\\/")
    return text


assets, pages_done = set(), []


def want_page(u):
    p = urlparse(u)
    if p.netloc and p.netloc != urlparse(SRC).netloc:
        return False
    if p.query or any(x in p.path for x in ("/wp-", "/feed", "/my-account", "/comments", "/author/", "/xmlrpc")):
        return False
    return not re.search(r"\.\w{2,4}$", p.path)


def collect_assets(soup, page_url):
    for tag, attr in (("link", "href"), ("script", "src"), ("img", "src"), ("source", "src")):
        for el in soup.find_all(tag):
            v = el.get(attr)
            if v and tag == "link" and not set(el.get("rel", [])) & {"stylesheet", "preload", "icon", "apple-touch-icon", "modulepreload"}:
                continue
            if v:
                assets.add(urljoin(page_url, v))
    for el in soup.find_all(["img", "source", "link"]):
        for attr in ("srcset", "imagesrcset"):
            for part in (el.get(attr) or "").split(","):
                if part.strip():
                    assets.add(urljoin(page_url, part.strip().split()[0]))
    for el in soup.find_all("script", type="importmap"):
        for v in json.loads(el.string or "{}").get("imports", {}).values():
            assets.add(urljoin(page_url, v))
    for el in soup.find_all(style=True):
        for m in re.findall(r"url\(['\"]?([^'\")]+)", el["style"]):
            assets.add(urljoin(page_url, m))
    for el in soup.find_all("style"):
        for m in re.findall(r"url\(['\"]?([^'\")]+)", el.string or ""):
            if not m.startswith("data:"):
                assets.add(urljoin(page_url, m))


def clean(soup, path):
    for el in soup.find_all("script", src=True):
        if any(d in el["src"] for d in DROP_SCRIPTS):
            el.decompose()
    for el in soup.find_all("script"):
        if el.get("id", "").startswith(("wc-order-attribution", "sourcebuster", "wc-cart-fragments", "wc-add-to-cart")):
            el.decompose()
    for el in soup.find_all("link", rel=lambda r: r and set(r) & {"EditURI", "https://api.w.org/", "alternate", "shortlink", "dns-prefetch", "wlwmanifest"}):
        el.decompose()
    for sel in (".wp-block-woocommerce-customer-account", ".woocommerce-ordering", ".wc-block-catalog-sorting",
                "#review_form_wrapper", ".wp-block-woocommerce-catalog-sorting"):
        for el in soup.select(sel):
            el.decompose()
    for el in soup.select("#reviews .woocommerce-noreviews"):
        el.string = "Reviews are switched off in this static portfolio demo."
    # Canonical / og:url must be absolute live URLs
    for el in soup.find_all("link", rel="canonical"):
        el["href"] = CANON + path
    if path in ("/cart/", "/checkout/"):
        main = soup.find("main")
        if main:
            main.clear()
            main.append(BeautifulSoup(f'<div class="kp-wrap kp-cartpage"><h1>{"Your cart" if path == "/cart/" else "Checkout"}</h1>'
                                      f'<div id="kp-{path.strip("/")}" data-kp-page="{path.strip("/")}"><p>Loading…</p></div></div>', "html.parser"))
    head = soup.find("head")
    if head and not head.find("link", rel="icon"):
        head.append(BeautifulSoup(f'<link rel="icon" href="{SRC}/wp-content/themes/kettlepine/assets/favicon.svg" type="image/svg+xml">', "html.parser"))
    body = soup.find("body")
    body.append(BeautifulSoup(f'<script src="{SRC}/kp-cart.js" defer></script>', "html.parser"))
    # Footer note: say what this is
    for el in soup.select(".kp-concept"):
        el.append(" This is a static snapshot of the WordPress + WooCommerce build; the cart and checkout are demo-only."
                  + (" This is the deliberately unoptimized \"before\" version used in the speed case study." if MODE == "before" else ""))


def crawl():
    queue = ["/", "/shop/", "/cart/", "/checkout/"]
    seen = set(queue)
    while queue:
        path = queue.pop(0)
        r = S.get(SRC + path, timeout=120)
        if r.status_code != 200 or "text/html" not in r.headers.get("content-type", ""):
            print("skip", path, r.status_code)
            continue
        soup = BeautifulSoup(r.text, "html.parser")
        for a in soup.find_all("a", href=True):
            u = urljoin(SRC + path, a["href"]).split("#")[0]
            if u.startswith(SRC) and want_page(u):
                p = urlparse(u).path or "/"
                if p not in seen:
                    seen.add(p)
                    queue.append(p)
        clean(soup, path)
        collect_assets(soup, SRC + path)
        dest = local(SRC + path)
        dest.parent.mkdir(parents=True, exist_ok=True)
        dest.write_text(rewrite(str(soup)), encoding="utf-8")
        pages_done.append(path)
        print("page", path)


def fetch_assets():
    done = set()
    todo = [a for a in assets if a.startswith(SRC)]
    while todo:
        u = todo.pop().split("#")[0]
        base = u.split("?")[0]
        if base in done or base.endswith("/kp-cart.js"):
            continue
        done.add(base)
        r = S.get(u, timeout=120)
        if r.status_code != 200:
            print("asset", r.status_code, u)
            continue
        dest = local(base)
        dest.parent.mkdir(parents=True, exist_ok=True)
        if base.endswith(".css"):
            css = r.text
            for m in re.findall(r"url\(['\"]?([^'\")]+)", css):
                if not m.startswith("data:"):
                    todo.append(urljoin(u, m))
            dest.write_text(rewrite(css), encoding="utf-8")
        elif base.endswith((".js", ".mjs")):
            js = r.text
            # ES module imports (Interactivity API etc.)
            for m in re.findall(r"""(?:import|from)\s*\(?\s*["'](\.{1,2}/[^"']+)["']""", js):
                todo.append(urljoin(u, m))
            dest.write_text(rewrite(js), encoding="utf-8")
        else:
            dest.write_bytes(r.content)
    print(len(done), "assets")


def products_json():
    r = S.get(SRC + "/wp-json/wc/store/v1/products?per_page=50", timeout=120).json()
    out = {}
    for p in r:
        img = p["images"][0] if p["images"] else {}
        out[str(p["id"])] = {"name": html.unescape(p["name"]), "price": int(p["prices"]["price"]) / 10 ** p["prices"]["currency_minor_unit"],
                             "url": p["permalink"], "img": img.get("thumbnail", "")}
        if img:
            assets.add(img.get("thumbnail"))
    (OUT / "assets").mkdir(parents=True, exist_ok=True)
    (OUT / "assets" / "kp-products.json").write_text(rewrite(json.dumps(out, ensure_ascii=False)), encoding="utf-8")


OUT.mkdir(parents=True, exist_ok=True)
for old in OUT.iterdir():  # clear contents (the folder itself may be open in a local test server)
    shutil.rmtree(old) if old.is_dir() else old.unlink()
crawl()
products_json()
fetch_assets()
# 404 page
r = S.get(SRC + "/this-page-does-not-exist/", timeout=120)
soup = BeautifulSoup(r.text, "html.parser")
clean(soup, "/404/")
collect_assets(soup, SRC + "/")
(OUT / "404.html").write_text(rewrite(str(soup)), encoding="utf-8")
fetch_assets()
# client-side cart + SEO files
shutil.copy(ROOT / "static" / "kp-cart.js", OUT / "kp-cart.js")
# WooCommerce's product-button script asks the Store API for the cart on load: give it an empty cart.
api = OUT / "wp-json" / "wc" / "store" / "v1"
api.mkdir(parents=True, exist_ok=True)
(api / "cart").write_text(json.dumps({"items": [], "items_count": 0, "items_weight": 0, "coupons": [], "errors": [], "needs_payment": False, "needs_shipping": False,
    "totals": {"total_items": "0", "total_price": "0", "currency_code": "CAD", "currency_symbol": "$", "currency_minor_unit": 2}}), encoding="utf-8")
(OUT / ".nojekyll").write_text("", encoding="utf-8")
if MODE == "before":
    (OUT / "sitemap.xml").unlink(missing_ok=True)
else:
  (OUT / "sitemap.xml").write_text('<?xml version="1.0" encoding="UTF-8"?>\n<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
    + "".join(f"<url><loc>{LIVE}{p}</loc></url>" for p in pages_done if p not in ("/cart/", "/checkout/")) + "</urlset>\n", encoding="utf-8")
  (OUT / "robots.txt").write_text(f"User-agent: *\nAllow: /\n\nSitemap: {LIVE}/sitemap.xml\n", encoding="utf-8")
print(len(pages_done), "pages ->", OUT)

# Publish into docs/ (GitHub Pages). The optimized export keeps docs/before/ intact.
DOCS = ROOT / "docs" / ("before" if MODE == "before" else "")
DOCS.mkdir(parents=True, exist_ok=True)
for old in DOCS.iterdir():
    if MODE != "before" and old.name == "before":
        continue
    shutil.rmtree(old) if old.is_dir() else old.unlink()
for f in OUT.iterdir():
    shutil.copytree(f, DOCS / f.name) if f.is_dir() else shutil.copy2(f, DOCS / f.name)
print("published to", DOCS)
