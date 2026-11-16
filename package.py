"""Package the theme + setup plugin as zips and write the public Playground blueprint.

Usage: python package.py GITHUB_USERNAME
Output: dist/kettlepine.zip, dist/kettlepine-setup.zip, blueprint.json
Live demo link: https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/<user>/kettlepine-coffee/main/blueprint.json
"""
import json, shutil, sys, zipfile
from pathlib import Path

ROOT = Path(__file__).parent
user = sys.argv[1] if len(sys.argv) > 1 else "USERNAME"
RAW = f"https://raw.githubusercontent.com/{user}/kettlepine-coffee/main"
DIST = ROOT / "dist"
DIST.mkdir(exist_ok=True)
shutil.copy(ROOT / "data" / "products.json", ROOT / "plugin" / "kettlepine-setup" / "products.json")


def zipdir(src: Path, dest: Path):
    with zipfile.ZipFile(dest, "w", zipfile.ZIP_DEFLATED) as z:
        for f in sorted(src.rglob("*")):
            if f.is_file():
                z.write(f, Path(src.name) / f.relative_to(src))


zipdir(ROOT / "theme" / "kettlepine", DIST / "kettlepine.zip")
zipdir(ROOT / "plugin" / "kettlepine-setup", DIST / "kettlepine-setup.zip")

bp = {
    "$schema": "https://playground.wordpress.net/blueprint-schema.json",
    "meta": {"title": "Kettlepine Coffee Roasters (concept store)", "author": user,
             "description": "WooCommerce concept store by Liam Curran. Fictional brand; no real orders are processed."},
    "landingPage": "/",
    "login": False,
    "preferredVersions": {"php": "8.3", "wp": "latest"},
    "features": {"networking": True},
    "steps": [
        {"step": "installTheme", "themeData": {"resource": "wordpress.org/themes", "slug": "twentytwentyfive"}, "options": {"activate": False}},
        {"step": "installPlugin", "pluginData": {"resource": "wordpress.org/plugins", "slug": "woocommerce"}, "options": {"activate": True}},
        {"step": "installTheme", "themeData": {"resource": "url", "url": f"{RAW}/dist/kettlepine.zip"}, "options": {"activate": True}},
        {"step": "installPlugin", "pluginData": {"resource": "url", "url": f"{RAW}/dist/kettlepine-setup.zip"}, "options": {"activate": True}},
        {"step": "runPHP", "code": "<?php require_once '/wordpress/wp-load.php'; echo kettlepine_setup();"},
    ],
}
(ROOT / "blueprint.json").write_text(json.dumps(bp, indent=2), encoding="utf-8")
print("Live demo URL:\nhttps://playground.wordpress.net/?blueprint-url=" + RAW + "/blueprint.json")
