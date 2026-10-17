/** Below this viewport width a plain click on a movie card opens the quick-view sheet. */
export const QUICK_VIEW_MAX_WIDTH = 1024;

/** A primary-button click or press without modifier keys, i.e. one the browser would follow in the same tab. */
export const isPlainClick = (event) => event.button === 0
    && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey;

/** Whether a click on a card's detail link should open the quick view instead of following the link. */
export const shouldOpenQuickView = (event, viewportWidth) => viewportWidth < QUICK_VIEW_MAX_WIDTH && isPlainClick(event);
