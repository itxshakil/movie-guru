<script setup>
import { computed, onMounted } from 'vue';
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
        default: () => 3000
    },
    dimissable: {
        type: Boolean,
        default: () => false
    }
});

onMounted(() => {
    setTimeout(() => emit('remove'), props.duration);
});

let iconClasses = computed(() => {
    return {
        'text-red-600 dark:text-red-400 ':
            props.level === 'danger',
        'text-green-600  dark:text-green-400 ':
            props.level === 'success',
        'text-yellow-600  dark:text-yellow-400 ':
            props.level === 'warning',
        'text-primary-600  dark:text-primary-400 ': ![
            'danger',
            'success',
            'warning',
        ].includes(props.level),
    };
});

const emit = defineEmits(['remove']);
</script>

<template>
    <div
        :class="iconClasses"
        class="flex items-start px-3 py-2 space-x-2 backdrop-blur-xl backdrop-saturate-150 rtl:space-x-reverse text-xs shadow ring-1 rounded-xl"
    >
        <TimesCircle
            v-if="level === 'danger'"
            class="shrink-0 w-6 h-6"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        />
        <TickCircle
            v-else-if="level === 'success'"
            class="shrink-0 w-6 h-6"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        />
        <WarningTriangle
            v-else-if="level === 'warning'"
            class="shrink-0 w-6 h-6"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
        />
        <InfoCircle
            v-else
            class="shrink-0 w-6 h-6"
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
                        class="w-4 h-4 text-gray-900"
                        fill="currentColor"
                        stroke="2"
                        viewBox="0 0 20 20"
                    />
                </button>
            </div>
        </div>
    </div>
</template>