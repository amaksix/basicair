"""Back up product photos, then fit each one on a sharp square canvas."""
import shutil
from pathlib import Path

from PIL import Image, ImageFilter, ImageStat

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / "images" / "shop" / "products"
BACKUP = ROOT / "_image-originals" / "products"
CANVAS = 1400
PADDING = 0.08


def flatten(im):
    im = im.convert("RGBA")
    white = Image.new("RGB", im.size, (255, 255, 255))
    white.paste(im, mask=im.split()[-1])
    return white


def luminance(rgb):
    return 0.2126 * rgb[0] + 0.7152 * rgb[1] + 0.0722 * rgb[2]


def corner_color(im):
    w, h = im.size
    px = im.load()
    samples = []
    inset = max(1, min(w, h) // 40)
    points = [
        (inset, inset),
        (w - 1 - inset, inset),
        (inset, h - 1 - inset),
        (w - 1 - inset, h - 1 - inset),
        (w // 2, inset),
        (w // 2, h - 1 - inset),
    ]
    for x, y in points:
        samples.append(px[x, y])
    channels = list(zip(*samples))
    mid = len(samples) // 2
    return tuple(sorted(channel)[mid] for channel in channels)


def close(color, bg, tol):
    return all(abs(int(color[i]) - int(bg[i])) <= tol for i in range(3))


def trim_light_margin(im, bg):
    if luminance(bg) < 225:
        return im
    w, h = im.size
    px = im.load()
    tol = 18

    def row_empty(y):
        bad = 0
        limit = max(2, int(w * 0.015))
        for x in range(w):
            if not close(px[x, y], bg, tol):
                bad += 1
                if bad > limit:
                    return False
        return True

    def col_empty(x):
        bad = 0
        limit = max(2, int(h * 0.015))
        for y in range(h):
            if not close(px[x, y], bg, tol):
                bad += 1
                if bad > limit:
                    return False
        return True

    top = 0
    while top < h - 1 and row_empty(top):
        top += 1
    bottom = h - 1
    while bottom > top and row_empty(bottom):
        bottom -= 1
    left = 0
    while left < w - 1 and col_empty(left):
        left += 1
    right = w - 1
    while right > left and col_empty(right):
        right -= 1

    if (right - left) < w * 0.2 or (bottom - top) < h * 0.2:
        return im

    pad = 2
    box = (
        max(0, left - pad),
        max(0, top - pad),
        min(w, right + 1 + pad),
        min(h, bottom + 1 + pad),
    )
    return im.crop(box)


def fit(im, trim=True):
    bg = corner_color(im)
    trimmed = trim_light_margin(im, bg) if trim else im
    inner = int(CANVAS * (1 - PADDING * 2))
    scale = inner / max(trimmed.size)
    new_size = (
        max(1, round(trimmed.width * scale)),
        max(1, round(trimmed.height * scale)),
    )
    if scale > 2:
        mid = (
            max(1, trimmed.width * 2),
            max(1, trimmed.height * 2),
        )
        resized = trimmed.resize(mid, Image.Resampling.LANCZOS)
        resized = resized.filter(ImageFilter.UnsharpMask(radius=1.1, percent=70, threshold=2))
        resized = resized.resize(new_size, Image.Resampling.LANCZOS)
    else:
        resized = trimmed.resize(new_size, Image.Resampling.LANCZOS)

    if scale > 1.2:
        resized = resized.filter(ImageFilter.UnsharpMask(radius=1.3, percent=90, threshold=3))

    # Keep a light lift so pale products stay readable on the white canvas.
    stat = ImageStat.Stat(resized)
    if sum(stat.mean) / 3 > 235:
        resized = resized.filter(ImageFilter.UnsharpMask(radius=1.0, percent=40, threshold=1))

    canvas = Image.new("RGB", (CANVAS, CANVAS), (255, 255, 255))
    canvas.paste(
        resized,
        ((CANVAS - new_size[0]) // 2, (CANVAS - new_size[1]) // 2),
    )
    return canvas, trimmed.size, scale


def main():
    BACKUP.mkdir(parents=True, exist_ok=True)
    files = sorted(SRC.glob("*.webp"))
    if not files:
        raise SystemExit(f"No images in {SRC}")

    for path in files:
        backup = BACKUP / path.name
        if not backup.exists():
            shutil.copy2(path, backup)
        source = Image.open(backup)
        original = source.size
        flat = flatten(source)
        # White fabric blends into the background, so skip aggressive trimming.
        fitted, trimmed, scale = fit(flat, trim=path.name != "tehniskie-audumi.webp")
        fitted.save(path, format="WEBP", quality=92, method=6)
        print(
            f"{path.name:42} {original[0]}x{original[1]} -> "
            f"trim {trimmed[0]}x{trimmed[1]} x{scale:.2f} -> {CANVAS}x{CANVAS}"
        )


if __name__ == "__main__":
    main()
