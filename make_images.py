"""Illustrated product images for the Kettlepine concept store (no real photos/brands)."""
import json
from pathlib import Path
from PIL import Image, ImageDraw, ImageFont

ROOT = Path(__file__).parent
OUT = ROOT / "plugin" / "kettlepine-setup" / "images"
OUT.mkdir(parents=True, exist_ok=True)
S = 2  # supersample


def font(size, bold=False):
    for n in (["georgiab.ttf", "segoeuib.ttf"] if bold else ["segoeui.ttf", "arial.ttf"]):
        try:
            return ImageFont.truetype(n, size)
        except OSError:
            pass
    return ImageFont.load_default()


COLORS = {
    "first-light-breakfast-blend": ("#e8b04b", "#3b2a1a"),
    "ridgeback-espresso": ("#5b2a1e", "#f3e6d3"),
    "ethiopia-guji-natural": ("#6d5aa8", "#f6f1ea"),
    "colombia-huila": ("#b5442f", "#f6f1ea"),
    "guatemala-huehuetenango": ("#d9772b", "#2d2018"),
    "night-owl-decaf": ("#1f3a4d", "#f3e6d3"),
    "roasters-sampler": ("#2f5d4a", "#f6f1ea"),
}


def pine(d, cx, top, h, col):
    w = h * 0.7
    for i in range(3):
        y0 = top + i * h * 0.25
        d.polygon([(cx, y0), (cx - w / 2 * (0.6 + i * 0.2), y0 + h * 0.45), (cx + w / 2 * (0.6 + i * 0.2), y0 + h * 0.45)], fill=col)
    d.rectangle([cx - h * 0.04, top + h * 0.9, cx + h * 0.04, top + h * 1.05], fill=col)


def wrap_text(d, text, f, maxw):
    words, lines, cur = text.split(), [], ""
    for w in words:
        t = (cur + " " + w).strip()
        if d.textlength(t, font=f) <= maxw:
            cur = t
        else:
            lines.append(cur); cur = w
    lines.append(cur)
    return lines


def bag(p):
    W = 800 * S
    im = Image.new("RGB", (W, W), "#f4efe7")
    d = ImageDraw.Draw(im)
    bg, fg = COLORS[p["slug"]]
    # shadow
    d.ellipse([200 * S, 690 * S, 600 * S, 730 * S], fill="#e2d9cc")
    # bag body + crimped top
    d.rounded_rectangle([215 * S, 150 * S, 585 * S, 710 * S], radius=18 * S, fill=bg)
    d.rectangle([215 * S, 110 * S, 585 * S, 170 * S], fill=bg)
    for x in range(215, 585, 18):
        d.line([(x * S, 112 * S), (x * S, 165 * S)], fill=fg, width=1 * S)
    # label
    d.rounded_rectangle([255 * S, 240 * S, 545 * S, 640 * S], radius=12 * S, fill="#fbf8f3")
    pine(d, 400 * S, 270 * S, 90 * S, "#2f5d4a")
    d.text((400 * S, 395 * S), "KETTLEPINE", font=font(22 * S, True), fill="#2f5d4a", anchor="mm")
    f = font(30 * S, True)
    y = 450 * S
    for line in wrap_text(d, p["name"], f, 260 * S):
        d.text((400 * S, y), line, font=f, fill="#2b1d12", anchor="mm"); y += 38 * S
    small = font(18 * S)
    if p["notes"]:
        for line in wrap_text(d, p["notes"], small, 250 * S):
            d.text((400 * S, y + 8 * S), line, font=small, fill="#6b5a4a", anchor="mm"); y += 24 * S
    d.text((400 * S, 610 * S), f'{p["roast"]} roast · {p["weight"]}' if p["roast"] != "Mixed" else p["weight"], font=small, fill="#6b5a4a", anchor="mm")
    return im.resize((800, 800), Image.LANCZOS)


def dripper():
    W = 800 * S
    im = Image.new("RGB", (W, W), "#f4efe7")
    d = ImageDraw.Draw(im)
    d.ellipse([220 * S, 640 * S, 580 * S, 690 * S], fill="#e2d9cc")
    # mug
    d.rounded_rectangle([280 * S, 470 * S, 520 * S, 660 * S], radius=26 * S, fill="#2f5d4a")
    d.arc([490 * S, 510 * S, 590 * S, 610 * S], 270, 90, fill="#2f5d4a", width=22 * S)
    # dripper cone
    d.polygon([(220 * S, 230 * S), (580 * S, 230 * S), (450 * S, 450 * S), (350 * S, 450 * S)], fill="#fbf8f3", outline="#cdbfae")
    d.rectangle([330 * S, 450 * S, 470 * S, 478 * S], fill="#fbf8f3", outline="#cdbfae")
    for i in range(5):
        x = 270 + i * 65
        d.line([(x * S, 250 * S), ((360 + i * 20) * S, 430 * S)], fill="#e6dccf", width=4 * S)
    d.ellipse([220 * S, 210 * S, 580 * S, 250 * S], fill="#efe7dc", outline="#cdbfae")
    # steam
    for x in (360, 400, 440):
        d.arc([x * S - 20 * S, 120 * S, x * S + 20 * S, 190 * S], 100, 260, fill="#c9bba8", width=5 * S)
    return im.resize((800, 800), Image.LANCZOS)


def hero():
    W, H = 1200 * S, 900 * S
    im = Image.new("RGB", (W, H), "#2f5d4a")
    d = ImageDraw.Draw(im)
    d.ellipse([150 * S, 120 * S, 1050 * S, 1020 * S], fill="#3a6d57")
    for i, (x, h) in enumerate([(220, 300), (330, 380), (960, 340), (1060, 260)]):
        pine(d, x * S, (760 - h) * S, h * S, "#24493a")
    # three bags
    for x, col in [(380, "#e8b04b"), (600, "#5b2a1e"), (820, "#6d5aa8")]:
        d.rounded_rectangle([(x - 100) * S, 380 * S, (x + 100) * S, 760 * S], radius=14 * S, fill=col)
        d.rounded_rectangle([(x - 72) * S, 450 * S, (x + 72) * S, 660 * S], radius=10 * S, fill="#fbf8f3")
        pine(d, x * S, 475 * S, 60 * S, "#2f5d4a")
        d.text((x * S, 590 * S), "KETTLEPINE", font=font(16 * S, True), fill="#2f5d4a", anchor="mm")
    d.rectangle([0, 760 * S, W, H], fill="#24493a")
    return im.resize((1200, 900), Image.LANCZOS)


products = json.loads((ROOT / "data" / "products.json").read_text(encoding="utf-8"))
for p in products:
    img = dripper() if p["slug"] == "ceramic-pour-over-dripper" else bag(p)
    img.save(OUT / f'{p["slug"]}.webp', quality=82, method=6)
theme_assets = ROOT / "theme" / "kettlepine" / "assets"
theme_assets.mkdir(parents=True, exist_ok=True)
# The old illustrated hero (hero()) was replaced in v2 by a photo; see make_photos.py.
for f in sorted(OUT.iterdir()) + sorted(theme_assets.iterdir()):
    print(f.name, f.stat().st_size)
