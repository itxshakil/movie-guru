<script setup>
import {computed, onBeforeUnmount, onMounted, ref, useId} from 'vue';
import {useLibrary} from '@/Composables/useLibrary.js';

/**
 * "Did you like it?" for a title just marked watched whose card has left the page, such as on
 * the To watch tab. Takes focus when it appears, hides itself after a few seconds unless the
 * person is using it, and closes on Escape.
 */
const props = defineProps({
    movie: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['close']);

const AUTO_HIDE_MS = 5000;

const {reactionFor, setReaction} = useLibrary();

const headingId = `reaction-prompt-${useId()}`;
const dialog = ref(null);
const reaction = computed(() => reactionFor(props.movie.imdb_id));
let hideTimer = null;
let isHovered = false;
let isUsingControls = false;

const close = () => {
    clearTimeout(hideTimer);
    emit('close', {hadFocus: Boolean(dialog.value?.contains(document.activeElement))});
};

// Focus is moved to the dialog itself on purpose, so only real use holds the prompt open: hovering it,
// focusing one of its buttons, or pressing keys inside it.
const updateTimer = () => {
    clearTimeout(hideTimer);
    if (!isHovered && !isUsingControls) {
        hideTimer = setTimeout(close, AUTO_HIDE_MS);
    }
};

const setHovered = (hovered) => {
    isHovered = hovered;
    updateTimer();
};

const onKeydown = (event) => {
    if (event.key === 'Escape') {
        event.stopPropagation();
        close();
        return;
    }
    isUsingControls = true;
    updateTimer();
};

const onFocusin = (event) => {
    if (event.target !== dialog.value) {
        isUsingControls = true;
        updateTimer();
    }
};

const onFocusout = (event) => {
    if (!dialog.value?.contains(event.relatedTarget)) {
        isUsingControls = false;
        updateTimer();
    }
};

const chooseReaction = async (choice) => {
    await setReaction(props.movie.imdb_id, reaction.value === choice ? null : choice);
    close();
};

onMounted(() => {
    dialog.value?.focus({preventScroll: true});
    updateTimer();
});

onBeforeUnmount(() => clearTimeout(hideTimer));
</script>

<template>
    <div
        ref="dialog"
        :aria-labelledby="headingId"
        class="reaction-prompt fixed inset-x-4 bottom-24 z-60 mx-auto flex max-w-sm items-center gap-2 rounded-2xl border border-gray-200 bg-white/95 py-2 pl-4 pr-2 text-gray-900 shadow-lg outline-none backdrop-blur-md focus-visible:ring-2 focus-visible:ring-primary-500 lg:bottom-6 dark:border-gray-700 dark:bg-gray-800/95 dark:text-white"
        role="dialog"
        tabindex="-1"
        @focusin="onFocusin"
        @focusout="onFocusout"
        @keydown="onKeydown"
        @pointerenter="setHovered(true)"
        @pointerleave="setHovered(false)"
    >
        <p :id="headingId" class="min-w-0 flex-1 line-clamp-2 text-sm font-medium">
            Did you like <span class="font-semibold">{{ movie.title }}</span>?
        </p>
        <button
            :aria-label="`Like ${movie.title}`"
            :aria-pressed="reaction === 'liked'"
            :class="reaction === 'liked' ? 'bg-emerald-100 dark:bg-emerald-900/50' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
            class="shrink-0 rounded-lg px-2.5 py-1.5 text-lg leading-none transition-colors"
            type="button"
            @click="chooseReaction('liked')"
        >👍</button>
        <button
            :aria-label="`Dislike ${movie.title}`"
            :aria-pressed="reaction === 'disliked'"
            :class="reaction === 'disliked' ? 'bg-rose-100 dark:bg-rose-900/50' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
            class="shrink-0 rounded-lg px-2.5 py-1.5 text-lg leading-none transition-colors"
            type="button"
            @click="chooseReaction('disliked')"
        >👎</button>
        <button
            aria-label="Dismiss"
            class="shrink-0 rounded-lg p-1.5 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200"
            type="button"
            @click="close"
        >
            <svg aria-hidden="true" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M6 18L18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            </svg>
        </button>
    </div>
</template>

<style scoped>
.reaction-prompt {
    transition: opacity 0.2s ease-out, translate 0.2s ease-out;
}

@starting-style {
    .reaction-prompt {
        opacity: 0;
        translate: 0 0.5rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    .reaction-prompt {
        transition: none;
    }
}
</style>
