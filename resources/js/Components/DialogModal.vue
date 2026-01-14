<script setup>
import {onMounted, onUnmounted, ref} from 'vue';
import TimesIcon from '@/Components/Icons/Times.vue';

defineProps({
    maxWidth: {
        type: String,
        default: 'max-w-md',
    },
});

const dialog = ref(null);
const emit = defineEmits(['close']);

onMounted(() => {
    dialog.value?.showModal();
    // Prevent scrolling of the background
    document.body.style.overflow = 'hidden';
    dialog.value?.addEventListener('cancel', (e) => {
        e.preventDefault();
        close();
    });
});

const close = () => {
    if (!dialog.value || dialog.value.classList.contains('modal-out')) return;
    dialog.value.classList.add('modal-out');
    // Check if other dialogs are open before restoring scroll
    const openDialogs = document.querySelectorAll('dialog[open]');
    if (openDialogs.length <= 1) {
        document.body.style.overflow = '';
    }
    const timeout = window.innerWidth < 640 ? 300 : 250;
    setTimeout(() => {
        if (dialog.value) {
            dialog.value.close();
        }
        emit('close');
    }, timeout);
};

onUnmounted(() => {
    document.body.style.overflow = '';
    if (dialog.value?.open) {
        dialog.value?.close();
    }
})

defineExpose({close});
</script>

<template>
    <dialog ref="dialog" :class="maxWidth" scroll-region
            class="m-auto inset-0 w-full overflow-x-hidden bg-transparent p-2 md:p-4 modal-dialog transform transition-all duration-300"
            @click.self="close"
        modal-menu="mega">
      <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
            <button
                tabindex="-1"
                class="absolute top-2 right-2 ml-auto inline-flex items-center rounded-lg bg-transparent bg-gray-200 p-1 text-sm text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white dark:bg-gray-600 dark:text-gray-100 z-10"
                type="button" @click="close">
                <TimesIcon class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" />
                <span class="sr-only">Close modal</span>
            </button>
        </div>
        <slot />
    </dialog>
</template>
<style>
dialog {
    outline: none;
    max-height: 90vh;
}
dialog::backdrop {
    backdrop-filter: blur(8px) brightness(0.4) contrast(1.1);
    transition: backdrop-filter 0.5s ease;
}

.modal-dialog {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

dialog[open] {
    animation: modal-in 0.3s ease-out;
}

@keyframes modal-in {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-out {
    animation: modal-out 0.25s ease-in forwards !important;
}

@keyframes modal-out {
    from {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    to {
        opacity: 0;
        transform: translateY(10px) scale(0.98);
    }
}

@media (max-width: 640px) {
    dialog[open] {
        animation: mobile-modal-in 0.4s cubic-bezier(0.32, 0.72, 0, 1);
    }

    .modal-out {
        animation: mobile-modal-out 0.3s cubic-bezier(0.32, 0.72, 0, 1) forwards !important;
    }

    @keyframes mobile-modal-in {
        from {
            transform: translateY(100%);
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        to {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
    }
    @keyframes mobile-modal-out {
        from {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
        to {
            transform: translateY(100%);
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }
    }
    .modal-dialog {
        margin-bottom: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        position: fixed;
        bottom: 0;
        top: auto;
        padding: 0 !important;
        padding-bottom: env(safe-area-inset-bottom) !important;
        border-top-left-radius: 1.5rem !important;
        border-top-right-radius: 1.5rem !important;
        border-bottom-left-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
        box-shadow: 0 -10px 25px -5px rgb(0 0 0 / 0.1), 0 -8px 10px -6px rgb(0 0 0 / 0.1) !important;
        background-color: white !important;
    }

    .dark .modal-dialog {
        background-color: rgb(55 65 81) !important;
    }

    .modal-dialog::after {
        content: '';
        position: absolute;
        top: 0.75rem;
        left: 50%;
        transform: translateX(-50%);
        width: 2.5rem;
        height: 0.35rem;
        background-color: #e5e7eb;
        border-radius: 9999px;
        z-index: 20;
    }

    .dark .modal-dialog::after {
        background-color: #4b5563;
    }

    .modal-dialog > div {
        border-radius: inherit !important;
        padding-top: 1.5rem;
    }
}
</style>
