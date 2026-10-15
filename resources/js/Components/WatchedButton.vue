<script setup>
import {computed, onBeforeUnmount, ref, useId} from 'vue';
import {useLibrary} from '@/Composables/useLibrary.js';

// Two root nodes (the button and its popover): parent classes go on the button.
defineOptions({inheritAttrs: false});

const props = defineProps({
    movie: {
        type: Object,
        required: true,
    },
});

const REACTION_PROMPT_MS = 5000;

const {isWatched, reactionFor, markWatched, setReaction, unmarkWatched} = useLibrary();

const popoverId = `watched-${useId()}`;
const anchorName = `--watched-${useId()}`;
const button = ref(null);
const popover = ref(null);
let hidePromptTimer = null;

const watched = computed(() => isWatched(props.movie.imdb_id));
const reaction = computed(() => reactionFor(props.movie.imdb_id));
const reactionEmoji = computed(() => ({liked: '👍', disliked: '👎'})[reaction.value] ?? '');

const openReactionPrompt = () => {
    try {
        popover.value?.showPopover({source: button.value});
    } catch {
        popover.value?.showPopover();
    }

    clearTimeout(hidePromptTimer);
    hidePromptTimer = setTimeout(() => {
        if (popover.value && !popover.value.matches(':hover, :focus-within')) {
            popover.value.hidePopover();
        }
    }, REACTION_PROMPT_MS);
};

const onButtonClick = async () => {
    if (watched.value) {
        popover.value?.togglePopover();
        return;
    }

    await markWatched(props.movie);
    if (watched.value) {
        openReactionPrompt();
    }
};

const chooseReaction = async (choice) => {
    await setReaction(props.movie.imdb_id, reaction.value === choice ? null : choice);
    popover.value?.hidePopover();
};

const markNotWatched = async () => {
    popover.value?.hidePopover();
    await unmarkWatched(props.movie.imdb_id);
};

onBeforeUnmount(() => clearTimeout(hidePromptTimer));
</script>

<template>
    <button
        ref="button"
        v-bind="$attrs"
        :aria-label="watched ? `${movie.title}: watched${reaction ? `, ${reaction}` : ''}. Change` : `Mark ${movie.title} as watched`"
        :aria-pressed="watched"
        :class="watched ? 'bg-emerald-600/85 hover:bg-emerald-600' : 'bg-white/20 hover:bg-white/40'"
        :style="{anchorName}"
        class="watched-btn squircle flex items-center gap-1 backdrop-blur-md text-white text-xs font-medium px-2.5 py-0.5 rounded-sm transition-all active:scale-90"
        type="button"
        @click.stop="onButtonClick"
    >
        <span aria-hidden="true" class="relative flex">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path v-if="watched" d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"/>
                <template v-else>
                    <path d="M2.5 12s3.5-7 9.5-7 9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z" stroke-linejoin="round"/>
                    <circle cx="12" cy="12" r="3"/>
                </template>
            </svg>
            <span v-if="reactionEmoji" class="watched-btn__badge absolute -top-1.5 -right-2 text-[10px] leading-none">{{ reactionEmoji }}</span>
        </span>
        <span class="text-box-tight watched-btn__label">{{ watched ? `Watched ${reactionEmoji}`.trim() : 'Watched?' }}</span>
    </button>

    <div
        :id="popoverId"
        ref="popover"
        :style="{positionAnchor: anchorName}"
        class="watched-popover m-0 rounded-xl border border-gray-200 bg-white p-2 text-gray-900 shadow-lg dark:border-gray-700 dark:bg-gray-800 dark:text-white"
        popover="auto"
        role="dialog"
        :aria-label="`Did you like ${movie.title}?`"
        @click.stop
        @pointerdown.stop
        @pointerup.stop
    >
        <p class="px-1 pb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400">Did you like it?</p>
        <div class="flex gap-1">
            <button
                :aria-label="`Like ${movie.title}`"
                :aria-pressed="reaction === 'liked'"
                :class="reaction === 'liked' ? 'bg-emerald-100 dark:bg-emerald-900/50' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                class="rounded-lg px-3 py-1.5 text-lg transition-colors"
                type="button"
                @click="chooseReaction('liked')"
            >👍</button>
            <button
                :aria-label="`Dislike ${movie.title}`"
                :aria-pressed="reaction === 'disliked'"
                :class="reaction === 'disliked' ? 'bg-rose-100 dark:bg-rose-900/50' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
                class="rounded-lg px-3 py-1.5 text-lg transition-colors"
                type="button"
                @click="chooseReaction('disliked')"
            >👎</button>
        </div>
        <button
            class="mt-1.5 w-full rounded-lg px-2 py-1.5 text-left text-xs font-medium text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
            type="button"
            @click="markNotWatched"
        >Not watched</button>
    </div>
</template>

<style scoped>
.watched-btn__badge {
    display: none;
}

/* Compact grids hide the label, so the reaction moves onto the icon. */
@container style(--card-density: compact) {
    .watched-btn__label {
        display: none;
    }

    .watched-btn__badge {
        display: block;
    }
}

.watched-popover {
    opacity: 0;
    transform: scale(0.95) translateY(-4px);
    transition: opacity 0.15s ease-out, transform 0.15s ease-out, display 0.15s allow-discrete, overlay 0.15s allow-discrete;
}

.watched-popover:popover-open {
    opacity: 1;
    transform: none;
}

@starting-style {
    .watched-popover:popover-open {
        opacity: 0;
        transform: scale(0.95) translateY(-4px);
    }
}

/* Tethered under the button where anchor positioning exists; centred by the UA otherwise. */
@supports (position-area: block-end) {
    .watched-popover {
        inset: auto;
        margin-block-start: 0.375rem;
        position-area: block-end span-inline-start;
        position-try-fallbacks: flip-block, flip-inline;
    }
}

@media (prefers-reduced-motion: reduce) {
    .watched-popover {
        transition: none;
    }
}
</style>
