<script setup>
import {computed, onMounted} from 'vue';
import WarningTriangle from '@/Components/Icons/WarningTriangle.vue';
import TimesCircle from '@/Components/Icons/TimesCircle.vue';
import TickCircle from '@/Components/Icons/TickCircle.vue';
import InfoCircle from '@/Components/Icons/InfoCircle.vue';
import Times from '@/Components/Icons/Times.vue';

const props = defineProps({
    message: {
        type: String,
        default: () => 'No message for you'
    },
    level: {
        type: String,
        default: () => 'info'
    },
    duration: {
        type: Number,
      default: () => 10_000
    },
    dimissable: {
        type: Boolean,
        default: () => false
    }
});

onMounted(() => {
    setTimeout(() => emit('remove'), props.duration);
});

const iconClasses = computed(() => ({
    danger: 'text-red-600 dark:text-red-400',
    success: 'text-green-600 dark:text-green-400',
    warning: 'text-yellow-600 dark:text-yellow-400',
}[props.level] ?? 'text-primary-600 dark:text-primary-400'));

const emit = defineEmits(['remove']);
</script>

<template>
    <div
        class="animate-shake flex items-start gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-white/95 text-xs text-gray-900 shadow-lg backdrop-blur-md backdrop-saturate-150 dark:border-gray-700 dark:bg-gray-800/95 dark:text-white"
    >
        <TimesCircle
            v-if="level === 'danger'"
            :class="iconClasses"
            class="shrink-0 w-6 h-6 mt-1"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        />
        <TickCircle
            v-else-if="level === 'success'"
            :class="iconClasses"
            class="shrink-0 w-6 h-6 mt-1"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        />
        <WarningTriangle
            v-else-if="level === 'warning'"
            :class="iconClasses"
            class="shrink-0 w-6 h-6 mt-1"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        />
        <InfoCircle
            v-else
            :class="iconClasses"
            class="shrink-0 w-6 h-6 mt-1"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        />
        <div class="flex-1 space-y-1">
            <div class="flex items-center justify-between font-medium">
                <p
                    class="text-sm leading-6"
                    aria-live="polite"
                    v-text="message"
                />
                <button
                    v-if="dimissable"
                    @click="emit('remove')"
                >
                    <Times
                        class="w-4 h-4 text-gray-900 dark:text-gray-200"
                        fill="currentColor"
                        stroke="2"
                        viewBox="0 0 20 20"
                    />
                </button>
            </div>
        </div>
    </div>
</template>

<style>
.animate-shake {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%,
  100% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-5px);
  }
  50% {
    transform: translateX(5px);
  }
  75% {
    transform: translateX(-5px);
  }
}
</style>