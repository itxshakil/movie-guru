<template>
    <button
        v-if="variant === 'icon'"
        :aria-label="isInWatchlist ? `Remove ${movie.title} from watchlist` : `Add ${movie.title} to watchlist`"
        :aria-pressed="isInWatchlist"
        :class="isInWatchlist ? 'bg-primary-600 text-white' : 'bg-black/55 text-white hover:bg-black/70'"
        :title="isInWatchlist ? 'Remove from watchlist' : 'Add to watchlist'"
        class="relative z-10 grid h-9 w-9 place-items-center rounded-full backdrop-blur-md transition-colors active:scale-90"
        type="button"
        @click.stop.prevent="toggleWatchlist"
    >
        <Bookmark :filled="isInWatchlist" class="h-[18px] w-[18px]"/>
    </button>
    <UiButton
        v-else
        :aria-pressed="isInWatchlist"
        :title="isInWatchlist ? 'Remove from watchlist' : 'Add to watchlist'"
        size="sm"
        variant="secondary"
        @click.stop.prevent="toggleWatchlist"
    >
        <template #icon>
            <Bookmark :filled="isInWatchlist" class="h-4 w-4"/>
        </template>
        {{ isInWatchlist ? 'Saved' : 'Watchlist' }}
    </UiButton>
</template>

<script setup>
import {computed} from 'vue';
import {useWatchlist} from '@/Composables/useWatchlist.js';
import Bookmark from '@/Components/Icons/Bookmark.vue';
import UiButton from '@/Components/Ui/UiButton.vue';

const props = defineProps({
    movie: {
        type: Object,
        required: true,
    },
    variant: {
        type: String,
        default: 'label',
    },
});

const {isMovieInWatchlist, toggleMovie} = useWatchlist();

const isInWatchlist = computed(() => isMovieInWatchlist(props.movie.imdb_id));

const toggleWatchlist = () => {
    toggleMovie(props.movie);
};
</script>
