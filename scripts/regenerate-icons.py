"""Crops the black frame from the master app icon and rewrites every icon at its current size.

Dev-only tool; needs Python 3 and Pillow (`python3 -m pip install Pillow`).
Usage: python3 scripts/regenerate-icons.py
"""
from pathlib import Path

from PIL import Image

ROOT = Path(__file__).resolve().parent.parent
MASTER = ROOT / 'public/icons/ios/1024.png'
ICON_DIRS = [ROOT / 'public/icons/ios', ROOT / 'public/icons/android', ROOT / 'public/icons/windows11']
FAVICON = ROOT / 'public/favicon.ico'
DARK_THRESHOLD = 30


def frame_bounds(image: Image.Image) -> tuple[int, int, int, int]:
    """Returns the box of the first non-black pixels, scanning in from each edge along the
    quarter lines, where the film strip does not reach the frame."""
    width, height = image.size
    probe_x, probe_y = width // 4, height // 4

    def is_dark(x: int, y: int) -> bool:
        return sum(image.getpixel((x, y))[:3]) < DARK_THRESHOLD

    left = next(x for x in range(width) if not is_dark(x, probe_y))
    right = next(x for x in range(width - 1, -1, -1) if not is_dark(x, probe_y)) + 1
    top = next(y for y in range(height) if not is_dark(probe_x, y))
    bottom = next(y for y in range(height - 1, -1, -1) if not is_dark(probe_x, y)) + 1
    return left, top, right, bottom


def main() -> None:
    master = Image.open(MASTER).convert('RGB')
    box = frame_bounds(master)
    if box == (0, 0, *master.size):
        raise SystemExit('No frame found; the master icon is already cropped.')
    tile = master.crop(box)
    print(f'Cropped frame {box} from {MASTER.name}')

    for directory in ICON_DIRS:
        for path in sorted(directory.glob('*.png')):
            with Image.open(path) as current:
                size, mode = current.size, current.mode
                background = current.convert('RGBA').getpixel((0, 0))
            side = min(size)
            resized = tile.resize((side, side), Image.LANCZOS)
            canvas = Image.new('RGBA', size, background)
            canvas.paste(resized, ((size[0] - side) // 2, (size[1] - side) // 2))
            canvas.convert('RGB' if mode == 'RGB' else 'RGBA').save(path, optimize=True)
            print(f'Wrote {path.relative_to(ROOT)} {size[0]}x{size[1]}')

    tile.save(FAVICON, sizes=[(16, 16), (32, 32), (48, 48)])
    print(f'Wrote {FAVICON.relative_to(ROOT)}')


if __name__ == '__main__':
    main()
