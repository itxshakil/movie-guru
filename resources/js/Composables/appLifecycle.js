/**
 * The service worker calls skipWaiting() on every install, so a new version takes
 * control of open windows straight away while they still run the old build's JS.
 * Reload each window once when that happens. A first visit also fires
 * controllerchange (clients.claim() hands the page its first worker), but there is
 * nothing stale to replace, so that one is skipped. A later update in the same tab
 * still reloads.
 *
 * @param {ServiceWorkerContainer|undefined} container navigator.serviceWorker
 * @param {() => void} reload
 */
export function reloadWhenUpdated(container, reload) {
    if (!container) {
        return;
    }
    let controlled = Boolean(container.controller);
    let reloading = false;
    container.addEventListener('controllerchange', () => {
        if (!controlled) {
            controlled = true;
            return;
        }
        if (!reloading) {
            reloading = true;
            reload();
        }
    });
}

/**
 * With launch_handler "focus-existing", opening a shortcut, a shared link or a
 * notification focuses the running window instead of loading a new one. The page
 * routes there itself, so the app keeps its state instead of reloading. The launch
 * that opened this window already loaded its URL, so it is skipped.
 *
 * @param {object|undefined} launchQueue window.launchQueue
 * @param {(url: string) => void} visit
 * @param {() => string} currentUrl
 */
export function routeLaunches(launchQueue, visit, currentUrl) {
    if (!launchQueue) {
        return;
    }
    launchQueue.setConsumer((params) => {
        if (params?.targetURL && params.targetURL !== currentUrl()) {
            visit(params.targetURL);
        }
    });
}
