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

const touchStartY = ref(0);
const touchCurrentY = ref(0);

const handleTouchStart = (e) => {
    // Detect if we are touching a scrollable element and its scroll position
    const scrollableElement = e.target.closest('.overflow-y-auto, .overflow-y-scroll');
    if (scrollableElement && scrollableElement.scrollTop > 0) {
        touchStartY.value = 0;
        return;
    }

    // Clear any previous transition to allow drag to be responsive
    if (window.innerWidth < 640 && dialog.value) {
        dialog.value.style.transition = '';
    }

    touchStartY.value = e.touches[0].clientY;
};

const handleTouchMove = (e) => {
    if (window.innerWidth >= 640 || !dialog.value || touchStartY.value === 0) return;
    touchCurrentY.value = e.touches[0].clientY;
    const deltaY = touchCurrentY.value - touchStartY.value;
    if (deltaY > 0) {
        // Prevent default scrolling when pulling down
        if (e.cancelable) e.preventDefault();

        // Use passive false to actually prevent scroll, but requestAnimationFrame for the transform
        requestAnimationFrame(() => {
            if (dialog.value) {
                // Apply a slight resistance to the pull
                const resistance = 0.8;
                dialog.value.style.transform = `translateY(${deltaY * resistance}px)`;

                // Opacity reduction for background
                const opacity = Math.max(0, 1 - (deltaY / 400));
                dialog.value.style.setProperty('--tw-bg-opacity', opacity.toString());
            }
        });
    }
};

const handleTouchEnd = () => {
    if (window.innerWidth >= 640 || !dialog.value || touchStartY.value === 0) return;
    const deltaY = touchCurrentY.value - touchStartY.value;
    if (deltaY > 150) {
        close();
    } else {
        dialog.value.style.transition = 'transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1)';
        dialog.value.style.transform = '';
        dialog.value.style.setProperty('--tw-bg-opacity', '1');
        setTimeout(() => {
            if (dialog.value) {
                dialog.value.style.transition = '';
                dialog.value.style.transform = '';
            }
        }, 500);
    }
    touchStartY.value = 0;
    touchCurrentY.value = 0;
};

const handleClickOutside = (e) => {
    if (e.target === dialog.value) {
        close();
    }
};

onMounted(() => {
    if (!dialog.value) return;

    // Initial state for mobile slide-up
    if (window.innerWidth < 640) {
        dialog.value.style.transform = 'translateY(100%)';
    }

    try {
        if (!dialog.value.open) {
            dialog.value.showModal();
        }
    } catch (e) {
        // Fallback for browsers that don't support showModal()
        if (dialog.value) {
            dialog.value.setAttribute('open', '');
        }
    }

    // Trigger slide-up animation for mobile
    if (window.innerWidth < 640) {
        requestAnimationFrame(() => {
            if (dialog.value) {
                dialog.value.style.transition = 'transform 0.5s cubic-bezier(0.32, 0.72, 0, 1)';
                dialog.value.style.transform = 'translateY(0)';
                // Ensure opacity is 1
                dialog.value.style.setProperty('--tw-bg-opacity', '1');
            }
        });
    }

    // Vibrate on open if supported
    if (window.innerWidth < 640 && 'vibrate' in navigator) {
        navigator.vibrate(15);
    }
    // Prevent scrolling of the background
    if (window.innerWidth < 640) {
        document.body.style.overflow = 'hidden';
        dialog.value.addEventListener('touchstart', handleTouchStart, {passive: true});
        dialog.value.addEventListener('touchmove', handleTouchMove, {passive: false});
        dialog.value.addEventListener('touchend', handleTouchEnd, {passive: true});
    }
    dialog.value.addEventListener('cancel', (e) => {
        e.preventDefault();
        close();
    });
    dialog.value.addEventListener('click', handleClickOutside);
});

const close = () => {
    if (!dialog.value || dialog.value.classList.contains('modal-out')) return;

    // Vibrate on close if supported
    if ('vibrate' in navigator) {
        navigator.vibrate(5);
    }

    dialog.value.classList.add('modal-out');
    if (window.innerWidth < 640) {
        dialog.value.style.transform = 'translateY(100%)';
        dialog.value.style.transition = 'transform 0.4s cubic-bezier(0.32, 0.72, 0, 1)';
        dialog.value.style.setProperty('--tw-bg-opacity', '0');
    }
    // Check if other dialogs are open before restoring scroll
    const openDialogs = document.querySelectorAll('dialog[open]');
    if (openDialogs.length <= 1) {
        document.body.style.overflow = '';
    }
    const timeout = window.innerWidth < 640 ? 400 : 250;
    setTimeout(() => {
        if (dialog.value) {
            try {
                dialog.value.close();
            } catch (e) {
                // Ignore error if dialog is already closed or close() not supported
                if (dialog.value && dialog.value.hasAttribute('open')) {
                    dialog.value.removeAttribute('open');
                }
            }
            dialog.value.removeEventListener('touchstart', handleTouchStart);
            dialog.value.removeEventListener('touchmove', handleTouchMove);
            dialog.value.removeEventListener('touchend', handleTouchEnd);
            dialog.value.removeEventListener('click', handleClickOutside);
            dialog.value.classList.remove('modal-out');
        }
        emit('close');
    }, timeout);
};

onUnmounted(() => {
    // Restore scroll if this was the last modal
    try {
        const openDialogs = document.querySelectorAll('dialog[open]');
        const isDialogOpen = dialog.value && (dialog.value.open || dialog.value.hasAttribute('open'));
        const remainingModals = openDialogs.length - (isDialogOpen ? 1 : 0);
        if (remainingModals <= 0) {
            document.body.style.overflow = '';
        }
    } catch (e) {
        document.body.style.overflow = '';
    }
    if (dialog.value) {
        dialog.value.removeEventListener('touchstart', handleTouchStart);
        dialog.value.removeEventListener('touchmove', handleTouchMove);
        dialog.value.removeEventListener('touchend', handleTouchEnd);
        dialog.value.removeEventListener('click', handleClickOutside);
        try {
            if (dialog.value.open || dialog.value.hasAttribute('open')) {
                dialog.value.close();
            }
        } catch (e) {
            // Ignore error if dialog is already closed or close() not supported
            if (dialog.value && dialog.value.hasAttribute('open')) {
                dialog.value.removeAttribute('open');
            }
        }
    }
});

defineExpose({close});
</script>

<template>
    <dialog ref="dialog" :class="maxWidth" scroll-region
            class="m-auto inset-0 w-full overflow-x-hidden bg-transparent p-2 md:p-4 modal-dialog backdrop:bg-black/40 dark:backdrop:bg-black/60"
        modal-menu="mega">
        <div
            class="relative rounded-lg bg-white shadow-sm dark:bg-gray-900 modal-content-wrapper overflow-y-auto max-h-[85vh] sm:max-h-none">
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
        }
        to {
            transform: translateY(0);
        }
    }
    @keyframes mobile-modal-out {
        from {
            transform: translateY(0);
        }
        to {
            transform: translateY(100%);
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
        touch-action: pan-y;
        max-height: 95vh !important;
        background-color: rgb(255 255 255 / var(--tw-bg-opacity, 1)) !important;
    }

    .dark .modal-dialog {
        background-color: rgba(17, 24, 39, var(--tw-bg-opacity, 1)) !important;
    }

    .modal-dialog::after {
        content: '';
        position: absolute;
        top: 0.75rem;
        left: 50%;
        transform: translateX(-50%);
        width: 3rem;
        height: 0.35rem;
        background-color: #d1d5db;
        border-radius: 9999px;
        z-index: 20;
    }

    .dark .modal-dialog::after {
        background-color: #4b5563;
    }

    .modal-dialog > div {
        border-radius: inherit !important;
        padding-top: 1.5rem;
        background-color: transparent !important;
        box-shadow: none !important;
    }
}
</style>
