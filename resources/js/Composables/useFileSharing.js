/**
 * Shares a generated file (an image or a calendar event) with the Web Share API when the
 * browser can share files, and downloads it otherwise.
 *
 * Returns 'needs-activation' when the browser refused because the tap that started the work
 * has expired (slow rendering); the caller should keep the file and share it on the next tap.
 *
 * @returns {Promise<'shared'|'downloaded'|'cancelled'|'needs-activation'>}
 */
export async function shareOrDownloadFile(file, {title, text} = {}) {
    if (navigator.canShare?.({files: [file]})) {
        try {
            await navigator.share({files: [file], title, text});
            return 'shared';
        } catch (error) {
            if (error.name === 'AbortError') {
                return 'cancelled';
            }
            if (error.name === 'NotAllowedError' && !navigator.userActivation?.isActive) {
                return 'needs-activation';
            }
            console.error('Sharing the file failed, downloading it instead', error);
        }
    }

    const url = URL.createObjectURL(file);
    const link = document.createElement('a');
    link.href = url;
    link.download = file.name;
    document.body.append(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 0);

    return 'downloaded';
}

/**
 * Shares a link with the Web Share API, falling back to the clipboard.
 *
 * @param {Navigator} [nav] a seam for tests
 * @returns {Promise<'shared'|'copied'|'cancelled'>}
 */
export async function shareOrCopyLink({url, title, text}, nav = navigator) {
    if (nav.share) {
        try {
            await nav.share({url, title, text});
            return 'shared';
        } catch (error) {
            if (error.name === 'AbortError') {
                return 'cancelled';
            }
            console.error('Sharing the link failed, copying it instead', error);
        }
    }

    await nav.clipboard.writeText(url);
    return 'copied';
}
