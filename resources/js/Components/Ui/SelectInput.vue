<script setup>
const model = defineModel({type: [String, Number, null], default: ''});
</script>

<template>
    <select
        v-model="model"
        class="block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 pr-9 text-sm text-gray-900 shadow-xs transition-colors focus:border-primary-500 focus:ring-2 focus:ring-primary-500/30 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
    >
        <slot/>
    </select>
</template>
