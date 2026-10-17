<template>
    <div>
        <p ref="paragraph" :class="isExpanded ? '' : clampClass" v-bind="$attrs">{{ text }}</p>
        <button v-if="isClamped || isExpanded"
                :aria-expanded="isExpanded"
                class="mt-1 text-sm font-semibold text-primary-600 hover:underline dark:text-primary-400"
                type="button"
                @click="isExpanded = !isExpanded">
            {{ isExpanded ? 'Show less' : 'Read more' }}
        </button>
    </div>
</template>
<script setup>
import {nextTick, onBeforeUnmount, onMounted, ref, watch} from 'vue';

defineOptions({inheritAttrs: false});

const props = defineProps({
    text: {
        type: String,
        required: true,
    },
    /** Line-clamp utilities for the collapsed state; responsive variants (md:line-clamp-none) show the full text there. */
    clampClass: {
        type: String,
        default: 'line-clamp-3',
    },
});

const paragraph = ref(null);
const isExpanded = ref(false);
const isClamped = ref(false);
let resizeObserver = null;

/** "Read more" only shows when the clamp actually hides text at the current width. */
const measureClamp = () => {
    if (paragraph.value && !isExpanded.value) {
        isClamped.value = paragraph.value.scrollHeight > paragraph.value.clientHeight + 1;
    }
};

onMounted(() => {
    measureClamp();
    resizeObserver = new ResizeObserver(measureClamp);
    resizeObserver.observe(paragraph.value);
});

onBeforeUnmount(() => resizeObserver?.disconnect());

watch(() => props.text, async () => {
    isExpanded.value = false;
    await nextTick();
    measureClamp();
});
</script>
