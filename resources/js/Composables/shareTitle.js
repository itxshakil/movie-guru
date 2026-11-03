/**
 * Shares a title through the native share sheet, or copies its link where there is none.
 * The outcome goes through the toast, which is a polite live region, so screen readers hear it too.
 *
 * @param {{title: string, url: string}} title
 * @param {{nav?: Navigator, notify?: (message: string, level: string) => void}} [deps] seams for tests
 * @returns {Promise<'shared'|'copied'|'cancelled'|'failed'>}
 */
export async function shareTitle({title, url}, {nav = navigator, notify = toast} = {}) {
    try {
        if (nav.share) {
            await nav.share({title, text: `Check out ${title} on Movie Guru!`, url});
            return 'shared';
        }
        await nav.clipboard.writeText(url);
        notify('Link copied', 'success');
        return 'copied';
    } catch (error) {
        if (error?.name === 'AbortError') {
            return 'cancelled';
        }
        notify('Could not share or copy the link', 'danger');
        return 'failed';
    }
}

function toast(message, level) {
    new BroadcastChannel('toast-notifications').postMessage({message, level});
}
