"""Homepage category pills: shrink City M, enlarge the fabric roll."""
from PIL import Image
from pathlib import Path

OUT = Path("images/shop/products")
CANVAS = (1760, 968)


def content_box(im, thresh=245, pad=12):
    rgb = im.convert("RGB")
    w, h = rgb.size
    px = rgb.load()
    minx, miny, maxx, maxy = w, h, 0, 0
    for y in range(h):
        for x in range(w):
            r, g, b = px[x, y]
            if r < thresh or g < thresh or b < thresh:
                minx = min(minx, x)
                miny = min(miny, y)
                maxx = max(maxx, x)
                maxy = max(maxy, y)
    minx = max(0, minx - pad)
    miny = max(0, miny - pad)
    maxx = min(w, maxx + 1 + pad)
    maxy = min(h, maxy + 1 + pad)
    return rgb.crop((minx, miny, maxx, maxy))


def place(src, dest, height_ratio, thresh=245):
    im = Image.open(src)
    subject = content_box(im, thresh=thresh)
    target_h = int(CANVAS[1] * height_ratio)
    scale = target_h / subject.height
    size = (max(1, round(subject.width * scale)), target_h)
    subject = subject.resize(size, Image.Resampling.LANCZOS)
    canvas = Image.new("RGB", CANVAS, (255, 255, 255))
    canvas.paste(subject, ((CANVAS[0] - size[0]) // 2, (CANVAS[1] - size[1]) // 2))
    canvas.save(dest, format="WEBP", quality=86, method=6)
    print(dest.name, "subject", size, "ratio", round(size[1] / CANVAS[1], 2))


place(OUT / "city-m.webp", OUT / "home-city-m.webp", 0.62)
place(OUT / "tehniskie-audumi.webp", OUT / "home-tehniskie-audumi.webp", 0.74, thresh=250)
