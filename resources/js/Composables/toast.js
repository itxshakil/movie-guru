/** Shows a toast. ToastNotifications renders it in a polite live region, so screen readers hear it too. */
export function showToast(message, level = 'danger') {
    new BroadcastChannel('toast-notifications').postMessage({message, level});
}
