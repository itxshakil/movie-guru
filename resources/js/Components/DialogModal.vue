<script setup>
import {onMounted, ref} from 'vue';
import TimesIcon from '@/Components/Icons/Times.vue';

defineProps({
    maxWidth: {
        type: String,
        default: 'max-w-md',
    },
});

const dialog = ref(null);
onMounted(() => {
    dialog.value?.showModal();
});
</script>

<template>
    <dialog ref="dialog" :class="maxWidth" scroll-region
            class="m-auto inset-0 w-full overflow-x-hidden bg-transparent p-2 md:p-4 modal-dialog sm:p-0 transform transition-500 transition-opacity"
        modal-menu="mega">
      <div class="relative rounded-lg bg-white shadow-sm dark:bg-gray-700">
            <button
                tabindex="-1"
                class="absolute top-2 right-2 ml-auto inline-flex items-center rounded-lg bg-transparent bg-gray-200 p-1 text-sm text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white dark:bg-gray-600 dark:text-gray-100 z-10"
                type="button" @click="dialog.close()">
                <TimesIcon class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" />
                <span class="sr-only">Close modal</span>
            </button>
        </div>
        <slot />
    </dialog>
</template>
<style>
dialog::backdrop {
    backdrop-filter: blur(5px) brightness(0.5) contrast(1.5);
    transition: backdrop-filter 5s ease;
}

</style>
