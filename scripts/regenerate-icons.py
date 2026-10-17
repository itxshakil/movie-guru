"""Crops the black frame from the master app icon and rewrites every icon at its current size.

Dev-only tool; needs Python 3 and Pillow (`python3 -m pip install Pillow`).
Non-square files get the tile centred, with the bars filled by stretching the tile's own edge pixels so they blend in.
Safe to rerun: when no frame is found the master is already cropped and is used whole.
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


def corner_colour(tile: Image.Image, x: int, y: int) -> tuple[int, int, int]:
    """Returns the mean colour of the 4x4 patch at the given corner; the film strip never reaches the corners."""
    patch = tile.crop((x, y, x + 4, y + 4)).resize((1, 1), Image.BOX)
    return patch.getpixel((0, 0))[:3]


def gradient(start: tuple[int, int, int], end: tuple[int, int, int], size: tuple[int, int], vertical: bool) -> Image.Image:
    """Returns a smooth fill that blends from start to end along one axis."""
    seed = Image.new('RGB', (1, 2) if vertical else (2, 1))
    seed.putpixel((0, 0), start)
    seed.putpixel((0, 1) if vertical else (1, 0), end)
    return seed.resize(size, Image.BILINEAR)


def extend_edges(tile: Image.Image, size: tuple[int, int]) -> Image.Image:
    """Centres the square tile on a canvas, filling the bars with a gradient between the tile's corner colours
    so they blend with its vignette. Stretching the edge pixels instead would smear the film strip."""
    width, height = size
    side = tile.width
    left, top = (width - side) // 2, (height - side) // 2
    last = side - 4
    top_left, top_right = corner_colour(tile, 0, 0), corner_colour(tile, last, 0)
    bottom_left, bottom_right = corner_colour(tile, 0, last), corner_colour(tile, last, last)
    canvas = Image.new('RGBA', size)
    if left > 0:
        canvas.paste(gradient(top_left, bottom_left, (left, side), True), (0, top))
        canvas.paste(gradient(top_right, bottom_right, (width - left - side, side), True), (left + side, top))
    if top > 0:
        canvas.paste(gradient(top_left, top_right, (side, top), False), (left, 0))
        canvas.paste(gradient(bottom_left, bottom_right, (side, height - top - side), False), (left, top + side))
    canvas.paste(tile, (left, top))
    return canvas


def main() -> None:
    master = Image.open(MASTER).convert('RGB')
    box = frame_bounds(master)
    if box == (0, 0, *master.size):
        print(f'No frame found; {MASTER.name} is already cropped, using it whole')
    else:
        print(f'Cropped frame {box} from {MASTER.name}')
    tile = master.crop(box)

    for directory in ICON_DIRS:
        for path in sorted(directory.glob('*.png')):
            with Image.open(path) as current:
                size, mode = current.size, current.mode
            side = min(size)
            resized = tile.resize((side, side), Image.LANCZOS)
            canvas = extend_edges(resized, size)
            canvas.convert('RGB' if mode == 'RGB' else 'RGBA').save(path, optimize=True)
            print(f'Wrote {path.relative_to(ROOT)} {size[0]}x{size[1]}')

    tile.save(FAVICON, sizes=[(16, 16), (32, 32), (48, 48)])
    print(f'Wrote {FAVICON.relative_to(ROOT)}')


if __name__ == '__main__':
    main()
