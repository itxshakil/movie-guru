<script setup>
defineProps({
    value: String,
    required: {
        type: Boolean,
        default: false
    }
});
</script>

<template>
    <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
        <span v-if="required" class="text-red-500 dark:text-red-700" aria-hidden="true">*</span>
        <span v-if="required" class="sr-only">Required</span>
    </label>
</template>
