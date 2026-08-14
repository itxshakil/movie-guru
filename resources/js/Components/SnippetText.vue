<template>
    <p :class="{'is-expanded': showingFullText}"
       :title="showingFullText ? 'Click to collapse' : 'Click to view full plot'"
       class="snippet-text cursor-pointer transition-colors duration-200 hover:text-primary-600 dark:hover:text-primary-400"
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

<style scoped>
/* Animates the collapsed-to-full-plot height change instead of an abrupt
   jump. Collapsed height is a generous fixed value (well above what the
   ~100-character truncated preview needs) so real plot text never clips;
   expanding to block-size: auto is always safe since it's the element's
   natural height. Unsupported browsers just jump instantly (no fallback
   needed beyond the plain `auto` value below). */
.snippet-text {
    overflow: hidden;
    block-size: 4lh;
    transition: block-size 0.3s ease;
}

.snippet-text.is-expanded {
    block-size: auto;
    block-size: calc-size(auto, size);
}

@media (prefers-reduced-motion: reduce) {
    .snippet-text {
        transition: none;
    }
}
</style>
