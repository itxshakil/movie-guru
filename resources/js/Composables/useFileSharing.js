/**
 * Shares a generated file (an image or a calendar event) with the Web Share API when the
 * browser can share files, and downloads it otherwise.
 *
 * @returns {Promise<'shared'|'downloaded'|'cancelled'>}
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
 * @returns {Promise<'shared'|'copied'|'cancelled'>}
 */
export async function shareOrCopyLink({url, title, text}) {
    if (navigator.share) {
        try {
            await navigator.share({url, title, text});
            return 'shared';
        } catch (error) {
            if (error.name === 'AbortError') {
                return 'cancelled';
            }
            console.error('Sharing the link failed, copying it instead', error);
        }
    }

    await navigator.clipboard.writeText(url);
    return 'copied';
}
