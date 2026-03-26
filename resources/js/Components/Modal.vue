<script setup>
import {computed, ref, watch} from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);
const dialogRef = ref(null);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

watch(
    () => props.show,
    (value) => {
        if (!dialogRef.value) {
            return;
        }

        if (value) {
            dialogRef.value.showModal();
        } else {
            dialogRef.value.close();
        }
    }
);

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <teleport to="body">
        <dialog
            ref="dialogRef"
            :class="maxWidthClass"
            class="w-full mx-auto px-4 py-6 sm:px-0 bg-transparent max-h-screen overflow-y-auto"
            @cancel.prevent="close"
            @click.self="close"
        >
            <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl sm:w-full sm:mx-auto">
                <slot v-if="show"/>
            </div>
        </dialog>
    </teleport>
</template>
