/**
 * Renders a shareable poster card (poster, title, ratings, "via Movie Guru") on a canvas.
 * Images travel much better than links on WhatsApp status and Instagram stories.
 */

const CARD_WIDTH = 1080;
const CARD_HEIGHT = 1350;
const FONT_FAMILY = 'Figtree, system-ui, -apple-system, "Segoe UI", sans-serif';
const BACKGROUND_COLOR = '#030712';
const ACCENT_COLOR = '#3e63ff';

function loadImage(src) {
    return new Promise((resolve, reject) => {
        const image = new Image();
        // Poster CDNs send Access-Control-Allow-Origin: *, so the canvas stays exportable.
        image.crossOrigin = 'anonymous';
        image.onload = () => resolve(image);
        image.onerror = () => reject(new Error(`Could not load ${src}`));
        image.src = src;
    });
}

/**
 * OMDb posters are 300px wide; Amazon's image CDN serves a sharper copy for a larger size hint.
 */
function highResolutionPoster(src) {
    try {
        const url = new URL(src);
        if (url.hostname.endsWith('media-amazon.com')) {
            url.pathname = url.pathname.replace(/\._V1_[^/]*\.(jpg|png)$/i, '._V1_SX1000.$1');
            return url.toString();
        }
    } catch {
        // Not a parseable URL: use it as is.
    }
    return src;
}

function wrapText(context, text, maxWidth, maxLines) {
    const words = text.split(/\s+/);
    const lines = [];
    let current = '';

    for (const word of words) {
        const candidate = current ? `${current} ${word}` : word;
        if (context.measureText(candidate).width <= maxWidth || current === '') {
            current = candidate;
        } else {
            lines.push(current);
            current = word;
        }
    }
    lines.push(current);

    if (lines.length > maxLines) {
        const kept = lines.slice(0, maxLines);
        let last = kept[maxLines - 1];
        while (context.measureText(`${last}…`).width > maxWidth && last.length > 1) {
            last = last.slice(0, -1);
        }
        kept[maxLines - 1] = `${last.trimEnd()}…`;
        return kept;
    }

    return lines;
}

function roundedRectPath(context, x, y, width, height, radius) {
    context.beginPath();
    context.roundRect(x, y, width, height, radius);
}

function drawCover(context, image, x, y, width, height) {
    const scale = Math.max(width / image.width, height / image.height);
    const drawWidth = image.width * scale;
    const drawHeight = image.height * scale;
    context.drawImage(image, x + (width - drawWidth) / 2, y + (height - drawHeight) / 2, drawWidth, drawHeight);
}

function ratingBadges(detail) {
    const labels = {
        'Internet Movie Database': 'IMDb',
        'Rotten Tomatoes': 'Rotten Tomatoes',
        Metacritic: 'Metacritic',
    };

    return (detail.Ratings ?? [])
        .filter((rating) => labels[rating.Source] && rating.Value && rating.Value !== 'N/A')
        .map((rating) => ({label: labels[rating.Source], value: rating.Value}));
}

function drawBadges(context, badges, centerX, y) {
    const paddingX = 28;
    const gap = 20;
    const height = 110;
    const widths = badges.map(({label, value}) => {
        context.font = `500 26px ${FONT_FAMILY}`;
        const labelWidth = context.measureText(label).width;
        context.font = `700 34px ${FONT_FAMILY}`;
        return Math.max(labelWidth, context.measureText(value).width) + paddingX * 2;
    });
    const totalWidth = widths.reduce((sum, width) => sum + width, 0) + gap * (badges.length - 1);
    let x = centerX - totalWidth / 2;

    context.textAlign = 'center';
    badges.forEach(({label, value}, index) => {
        const width = widths[index];
        roundedRectPath(context, x, y, width, height, 22);
        context.fillStyle = 'rgba(255, 255, 255, 0.08)';
        context.fill();

        context.fillStyle = '#ffffff';
        context.font = `700 34px ${FONT_FAMILY}`;
        context.fillText(value, x + width / 2, y + 50);
        context.fillStyle = 'rgba(255, 255, 255, 0.6)';
        context.font = `500 26px ${FONT_FAMILY}`;
        context.fillText(label, x + width / 2, y + 88);

        x += width + gap;
    });
}

function drawSignature(context, centerX, baselineY) {
    const prefix = 'via ';
    const brand = 'Movie Guru';

    context.font = `500 30px ${FONT_FAMILY}`;
    const prefixWidth = context.measureText(prefix).width;
    context.font = `800 34px ${FONT_FAMILY}`;
    const brandWidth = context.measureText(brand).width;
    const startX = centerX - (prefixWidth + brandWidth) / 2;

    context.textAlign = 'left';
    context.fillStyle = 'rgba(255, 255, 255, 0.55)';
    context.font = `500 30px ${FONT_FAMILY}`;
    context.fillText(prefix, startX, baselineY);
    context.fillStyle = '#ffffff';
    context.font = `800 34px ${FONT_FAMILY}`;
    context.fillText(brand, startX + prefixWidth, baselineY);
    context.fillStyle = ACCENT_COLOR;
    context.fillRect(startX + prefixWidth, baselineY + 14, brandWidth, 5);
}

/**
 * @param {object} detail OMDb-style detail (Title, Year, Genre, Poster, Ratings)
 * @returns {Promise<File>}
 */
export async function createPosterCard(detail) {
    await document.fonts?.load(`700 64px ${FONT_FAMILY}`).catch(() => null);

    const canvas = document.createElement('canvas');
    canvas.width = CARD_WIDTH;
    canvas.height = CARD_HEIGHT;
    const context = canvas.getContext('2d');

    const hasPoster = detail.Poster && detail.Poster !== 'N/A';
    const poster = hasPoster ? await loadImage(highResolutionPoster(detail.Poster))
        .catch(() => loadImage(detail.Poster))
        .catch(() => null) : null;

    context.fillStyle = BACKGROUND_COLOR;
    context.fillRect(0, 0, CARD_WIDTH, CARD_HEIGHT);

    if (poster) {
        context.save();
        context.filter = 'blur(40px) brightness(0.45)';
        drawCover(context, poster, -80, -80, CARD_WIDTH + 160, CARD_HEIGHT + 160);
        context.restore();
    }

    const fade = context.createLinearGradient(0, 0, 0, CARD_HEIGHT);
    fade.addColorStop(0, 'rgba(3, 7, 18, 0.2)');
    fade.addColorStop(0.55, 'rgba(3, 7, 18, 0.75)');
    fade.addColorStop(1, BACKGROUND_COLOR);
    context.fillStyle = fade;
    context.fillRect(0, 0, CARD_WIDTH, CARD_HEIGHT);

    const posterWidth = 440;
    const posterHeight = 660;
    const posterX = (CARD_WIDTH - posterWidth) / 2;
    const posterY = 90;

    context.save();
    context.shadowColor = 'rgba(0, 0, 0, 0.6)';
    context.shadowBlur = 60;
    context.shadowOffsetY = 24;
    roundedRectPath(context, posterX, posterY, posterWidth, posterHeight, 28);
    context.fillStyle = '#111827';
    context.fill();
    context.restore();

    context.save();
    roundedRectPath(context, posterX, posterY, posterWidth, posterHeight, 28);
    context.clip();
    if (poster) {
        drawCover(context, poster, posterX, posterY, posterWidth, posterHeight);
    } else {
        context.fillStyle = 'rgba(255, 255, 255, 0.35)';
        context.font = `600 40px ${FONT_FAMILY}`;
        context.textAlign = 'center';
        context.fillText('🎬', CARD_WIDTH / 2, posterY + posterHeight / 2);
    }
    context.restore();

    context.textAlign = 'center';
    context.fillStyle = '#ffffff';
    context.font = `800 64px ${FONT_FAMILY}`;
    const titleLines = wrapText(context, detail.Title ?? 'Untitled', CARD_WIDTH - 160, 2);
    let y = posterY + posterHeight + 110;
    titleLines.forEach((line) => {
        context.fillText(line, CARD_WIDTH / 2, y);
        y += 74;
    });

    const meta = [detail.Year, detail.Genre && detail.Genre !== 'N/A' ? detail.Genre.split(',').slice(0, 2).map((genre) => genre.trim()).join(', ') : null]
        .filter(Boolean)
        .join('  ·  ');
    if (meta) {
        context.fillStyle = 'rgba(255, 255, 255, 0.7)';
        context.font = `500 32px ${FONT_FAMILY}`;
        context.fillText(meta, CARD_WIDTH / 2, y - 14);
    }

    const badges = ratingBadges(detail);
    if (badges.length) {
        drawBadges(context, badges, CARD_WIDTH / 2, y + 20);
    }

    drawSignature(context, CARD_WIDTH / 2, CARD_HEIGHT - 70);

    const blob = await new Promise((resolve, reject) => {
        canvas.toBlob((result) => (result ? resolve(result) : reject(new Error('Rendering the poster card failed'))), 'image/png');
    });

    const fileName = `${String(detail.Title ?? 'movie').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')}-movie-guru.png`;
    return new File([blob], fileName, {type: 'image/png', lastModified: Date.now()});
}
