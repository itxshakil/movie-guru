<script setup>
import {computed} from 'vue';
import {chipClasses, chipPressed} from '@/Components/Ui/buttonClasses.js';

const props = defineProps({
    as: {type: String, default: 'button'},
    // Left undefined when not bound, so an action chip is not announced as a toggle.
    active: {type: Boolean, default: undefined},
    size: {type: String, default: 'md'},
});

const classes = computed(() => chipClasses({active: props.active, size: props.size, interactive: props.as !== 'span'}));
const pressed = computed(() => chipPressed({as: props.as, active: props.active}));
</script>

<template>
    <component :is="as" :aria-pressed="pressed" :class="classes" :type="as === 'button' ? 'button' : undefined">
        <slot/>
    </component>
</template>
