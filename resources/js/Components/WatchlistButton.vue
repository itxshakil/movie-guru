<template>
    <button
        :class="isInWatchlist ? 'bg-primary-500/80 hover:bg-primary-600/80' : 'bg-white/20 hover:bg-white/40'"
        :title="isInWatchlist ? 'Remove from Watchlist' : 'Add to Watchlist'"
        class="flex items-center gap-1 backdrop-blur-md text-white text-xs font-medium px-2.5 py-0.5 rounded-sm transition-all active:scale-90"
        @click.stop="toggleWatchlist"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
                v-if="isInWatchlist"
                d="M5 13l4 4L19 7"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
            />
            <path
                v-else
                d="M12 4v16m8-8H4"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
            />
        </svg>
        {{ isInWatchlist ? 'Saved' : 'Watchlist' }}
    </button>
</template>

<script setup>
import {computed} from 'vue';
import {useWatchlist} from '@/Composables/useWatchlist.js';

const props = defineProps({
    movie: {
        type: Object,
        required: true,
    },
});

const {isMovieInWatchlist, toggleMovie} = useWatchlist();

const isInWatchlist = computed(() => isMovieInWatchlist(props.movie.imdb_id));

const toggleWatchlist = () => {
    toggleMovie(props.movie);
};
</script>
