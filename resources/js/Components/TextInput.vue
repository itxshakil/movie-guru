<script setup>
import {onMounted, ref} from 'vue';

defineProps(['modelValue']);

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="block w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-xs placeholder:text-gray-400 transition-colors focus:border-primary-500 focus:ring-2 focus:ring-primary-500/30 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder:text-gray-500"
        :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" ref="input" />
</template>
