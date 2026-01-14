/**
 * Google Analytics Tracking Plugin for Vue 3
 */
export default {
    install: (app, options) => {
        const trackEvent = (eventName, eventParams = {}) => {
            if (typeof window !== 'undefined' && window.gtag) {
                window.gtag('event', eventName, {
                    ...eventParams,
                    non_interaction: eventParams.non_interaction ?? false
                });
            } else if (import.meta.env.DEV) {
                console.log(`[GA Event] ${eventName}`, eventParams);
            }
        };

        app.config.globalProperties.$gtag = {
            event: trackEvent,
            // Track view detail specifically
            trackViewDetail: (imdbId, sectionName, title = null) => {
                trackEvent('view_detail', {
                    event_category: 'Engagement',
                    event_label: sectionName,
                    item_id: imdbId,
                    item_name: title,
                    value: imdbId
                });
            },
            // Track search
            trackSearch: (searchTerm, category = 'all') => {
                trackEvent('search', {
                    search_term: searchTerm,
                    movie_type: category
                });
            },
            // Track external link clicks
            trackExternalClick: (platform, url) => {
                trackEvent('click_external', {
                    event_category: 'Outbound',
                    event_label: platform,
                    url: url
                });
            }
        };

        // Provide it for Composition API
        app.provide('gtag', app.config.globalProperties.$gtag);
    }
};
