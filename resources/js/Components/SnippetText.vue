<template>
  <p title="Click to view" @click="toggleText">{{ snippet}}</p>
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
