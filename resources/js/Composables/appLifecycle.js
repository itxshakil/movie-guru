/**
 * The service worker calls skipWaiting() on every install, so a new version takes
 * control of open windows straight away while they still run the old build's JS.
 * Reload each window once when that happens. In a page that started without a worker
 * (a first visit), the first controllerchange is clients.claim() handing it one, with
 * nothing stale to replace, so that one is skipped; a later update still reloads.
 *
 * @param {ServiceWorkerContainer|undefined} container navigator.serviceWorker
 * @param {() => void} reload
 */
export function reloadWhenUpdated(container, reload) {
    if (!container) {
        return;
    }
    // A reload is due on the change that gives this page its second worker.
    let workers = container.controller ? 1 : 0;
    container.addEventListener('controllerchange', () => {
        workers++;
        if (workers === 2) {
            reload();
        }
    });
}

/**
 * With launch_handler "focus-existing", opening a shortcut or a shared link focuses
 * the running window instead of loading a new one. The page routes there itself, so
 * the app keeps its state instead of reloading. The launch that opened this window
 * already loaded its URL, so it is skipped. (Notification clicks are routed by the
 * service worker.)
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
