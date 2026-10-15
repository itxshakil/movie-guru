import {ref} from 'vue';

/**
 * Plays a YouTube trailer in a Document Picture-in-Picture window so it keeps playing while
 * the user browses. YouTube iframes cannot use video Picture-in-Picture or Media Session
 * directly, so the embed itself moves into the floating window.
 *
 * State lives at module level on purpose: the window must outlive the TrailerPlayer that
 * opened it, because Inertia unmounts the page component on every navigation.
 */

const pictureInPictureVideoId = ref(null);
let pictureInPictureWindow = null;

export const isDocumentPictureInPictureSupported = typeof window !== 'undefined'
    && 'documentPictureInPicture' in window;

export function buildTrailerEmbedUrl(videoId, {startSeconds = 0, origin = window.location.origin} = {}) {
    const params = new URLSearchParams({
        autoplay: '1',
        rel: '0',
        modestbranding: '1',
        enablejsapi: '1',
        origin,
    });
    if (startSeconds > 0) {
        params.set('start', String(Math.floor(startSeconds)));
    }

    return `https://www.youtube-nocookie.com/embed/${encodeURIComponent(videoId)}?${params}`;
}

function closeTrailerPictureInPicture() {
    pictureInPictureWindow?.close();
}

/**
 * Must be called from a user gesture (e.g. a click).
 */
async function openTrailerPictureInPicture({videoId, title, startSeconds}) {
    closeTrailerPictureInPicture();

    const pipWindow = await window.documentPictureInPicture.requestWindow({width: 480, height: 270});
    pictureInPictureWindow = pipWindow;
    pictureInPictureVideoId.value = videoId;

    pipWindow.document.title = `${title} trailer`;
    const style = pipWindow.document.createElement('style');
    style.textContent = 'html, body { margin: 0; height: 100%; background: #000; overflow: hidden; } '
        + 'iframe { display: block; width: 100%; height: 100%; border: 0; }';
    pipWindow.document.head.append(style);

    const iframe = pipWindow.document.createElement('iframe');
    iframe.src = buildTrailerEmbedUrl(videoId, {startSeconds});
    iframe.title = `${title} trailer`;
    iframe.allow = 'autoplay; encrypted-media; picture-in-picture; fullscreen';
    iframe.referrerPolicy = 'strict-origin-when-cross-origin';
    pipWindow.document.body.append(iframe);

    pipWindow.addEventListener('pagehide', () => {
        if (pictureInPictureWindow === pipWindow) {
            pictureInPictureWindow = null;
            pictureInPictureVideoId.value = null;
        }
    }, {once: true});
}

export function useTrailerPictureInPicture() {
    return {
        pictureInPictureVideoId,
        openTrailerPictureInPicture,
        closeTrailerPictureInPicture,
    };
}
