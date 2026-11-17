"""Crop and compress the Unsplash stock photos used by the Kettlepine theme.

Originals: ../_stock/u_<UnsplashID>.jpg (Unsplash License: free to use, no attribution required).
Output: theme/kettlepine/assets/img/<name>-<width>.webp
"""
from pathlib import Path
from PIL import Image

ROOT = Path(__file__).parent
SRC = ROOT.parent / "_stock"
OUT = ROOT / "theme" / "kettlepine" / "assets" / "img"
OUT.mkdir(parents=True, exist_ok=True)

PHOTOS = {  # name: (unsplash id, crop box, widths)
    "hero":       ("6HR8vpjYUHo", (0, 0, 1600, 1000), (800, 1600)),      # beans pouring out of the roaster
    "story":      ("Lk4UzBF6vss", (0, 300, 1600, 2300), (600, 1000)),    # beans cooling in the roaster (4:5)
    "blends":     ("4KqHNQ3sJ6g", (0, 400, 1600, 2000), (500,)),
    "single-origin": ("S8daAB_nJSg", (0, 400, 1600, 2000), (500,)),
    "decaf":      ("MK60jE_6zXU", (0, 900, 1600, 2500), (500,)),
    "brewing-gear": ("TYIzeCiZ_60", (250, 0, 1317, 1067), (500,)),
    "gifts":      ("Y3AqmbmtLQI", (0, 400, 1600, 2000), (500,)),
    "pourover":   ("F0XGFD9Z1Uk", (0, 500, 1600, 2100), (600, 1000)),
}
for name, (pid, box, widths) in PHOTOS.items():
    im = Image.open(SRC / f"u_{pid}.jpg").convert("RGB").crop(box)
    for w in widths:
        im.resize((w, round(im.height * w / im.width)), Image.LANCZOS).save(OUT / f"{name}-{w}.webp", "WEBP", quality=70, method=6)
    print(name, im.size)
