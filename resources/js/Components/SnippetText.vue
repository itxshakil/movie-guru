<template>
    <p :title="showingFullText ? 'Click to collapse' : 'Click to view full plot'"
       class="cursor-pointer transition-colors duration-200 hover:text-primary-600 dark:hover:text-primary-400"
       @click="toggleText">
        {{ snippet }}
        <span v-if="isOverflow" class="ml-1 text-xs font-semibold text-primary-500 hover:underline">
      {{ showingFullText ? 'Show less' : 'Read more' }}
    </span>
    </p>
</template>
<script setup>
import {computed, ref} from 'vue';

const props = defineProps({
        text: String,
        limit: {
            type: Number,
            default: 100
        },
        tail: {
            type: String,
            default: '...'
        }
    })

    const detail = ref(props.text)
const showingFullText = ref(false)

    const isOverflow = computed(() => detail.value.length > props.limit)

    const snippet = computed(() => {
        return isOverflow.value && showingFullText.value === false
            ? detail.value.slice(0, props.limit) + props.tail
            : detail.value
    })

    const toggleText = () => {
        showingFullText.value = !showingFullText.value
    }
</script>
