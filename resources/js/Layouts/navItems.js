import Home from '@/Components/Icons/Home.vue';
import Sparkles from '@/Components/Icons/Sparkles.vue';
import Users from '@/Components/Icons/Users.vue';
import Bookmark from '@/Components/Icons/Bookmark.vue';

export const NAV_ITEMS = [
    {label: 'Discover', short: 'Home', route: 'home', match: (url) => url === '/' || url.startsWith('/?'), icon: Home},
    {label: 'Mood', short: 'Mood', route: 'mood.index', match: (url) => url.startsWith('/mood'), icon: Sparkles},
    {label: 'Movie Match', short: 'Match', route: 'movie-match.create', match: (url) => url.startsWith('/movie-match'), icon: Users},
    {label: 'Watchlist', short: 'Watchlist', route: 'watchlist', match: (url) => url.startsWith('/watchlist'), icon: Bookmark, showsCount: true},
];
