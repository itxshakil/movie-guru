<script setup>
defineProps({
    title: {type: String, required: true},
    subtitle: {type: String, default: ''},
    as: {type: String, default: 'h2'},
    align: {type: String, default: 'left'},
});
</script>

<template>
    <div :class="align === 'center' ? 'items-center text-center' : 'items-end'"
         class="mb-6 flex flex-wrap justify-between gap-x-4 gap-y-3">
        <div :class="align === 'center' ? 'mx-auto' : ''" class="min-w-0">
            <component :is="as" class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ title }}</component>
            <p v-if="subtitle" class="mt-1 text-sm text-gray-500 sm:text-base dark:text-gray-400">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.action" class="flex shrink-0 items-center gap-2">
            <slot name="action"/>
        </div>
    </div>
</template>
