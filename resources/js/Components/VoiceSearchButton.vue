<script setup>
import {onBeforeUnmount, ref} from 'vue';
import UiButton from '@/Components/Ui/UiButton.vue';

const emit = defineEmits(['result']);

const SpeechRecognition = typeof window !== 'undefined'
    ? (window.SpeechRecognition ?? window.webkitSpeechRecognition)
    : undefined;
const isSupported = Boolean(SpeechRecognition);

const isListening = ref(false);
let recognition = null;

const stopListening = () => {
    recognition?.stop();
};

const startListening = () => {
    recognition = new SpeechRecognition();
    recognition.lang = document.documentElement.lang || navigator.language || 'en-US';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;

    recognition.onresult = (event) => {
        const transcript = event.results[0]?.[0]?.transcript?.trim();
        if (transcript) {
            emit('result', transcript);
        }
    };
    recognition.onerror = (event) => {
        // "no-speech" and "aborted" are normal outcomes, not failures.
        if (!['no-speech', 'aborted'].includes(event.error)) {
            console.error('Voice search failed', event.error);
        }
    };
    recognition.onend = () => {
        isListening.value = false;
        recognition = null;
    };

    recognition.start();
    isListening.value = true;
};

const toggleListening = () => {
    if (isListening.value) {
        stopListening();
    } else {
        startListening();
    }
};

onBeforeUnmount(() => recognition?.abort());
</script>

<template>
    <UiButton
        v-if="isSupported"
        :aria-label="isListening ? 'Stop voice search' : 'Search by voice'"
        :aria-pressed="isListening"
        :class="{'voice-search--listening': isListening}"
        :title="isListening ? 'Listening... tap to stop' : 'Search by voice'"
        class="relative"
        size="icon"
        variant="ghost"
        @click="toggleListening"
    >
        <span v-if="isListening" aria-hidden="true" class="absolute inset-0 rounded-full bg-red-500/20 motion-safe:animate-ping"/>
        <svg aria-hidden="true" class="relative h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect height="12" rx="3" width="6" x="9" y="3"/>
            <path d="M5 11a7 7 0 0 0 14 0M12 18v3" stroke-linecap="round"/>
        </svg>
    </UiButton>
</template>

<style scoped>
/* Unlayered, so it beats the ghost variant's text colour utilities without !important. */
.voice-search--listening {
    color: var(--color-red-500);
}

@media (prefers-color-scheme: dark) {
    .voice-search--listening {
        color: var(--color-red-400);
    }
}
</style>
