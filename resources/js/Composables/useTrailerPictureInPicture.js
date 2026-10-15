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

export function buildTrailerEmbedUrl(videoId) {
    const params = new URLSearchParams({
        autoplay: '1',
        rel: '0',
        modestbranding: '1',
        // The YouTube iframe API reports the playback position, so PiP can resume from it.
        enablejsapi: '1',
        origin: window.location.origin,
    });

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

    // The window's document is about:blank, and YouTube rejects referrer-less embeds (Error 153),
    // so it loads a same-origin page that embeds the player with this site as referrer.
    const iframe = pipWindow.document.createElement('iframe');
    const startAt = Math.floor(startSeconds ?? 0);
    iframe.src = route('trailer-embed.show', startAt > 0 ? {videoId, start: startAt} : {videoId});
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
