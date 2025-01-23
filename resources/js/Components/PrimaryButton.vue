<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type"
            class="inline-flex items-center px-4 py-2 bg-primary-800 dark:bg-primary-700 border border-transparent rounded-md font-semibold text-xs text-white dark:text-primary-200 uppercase tracking-widest hover:bg-primary-700 dark:hover:bg-primary-800 focus:bg-primary-700 dark:focus:bg-primary-900 active:bg-primary-900 dark:active:bg-primary-300 focus:outline-hidden focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-primary-800 transition ease-in-out duration-150">
        <slot />
    </button>
</template>
