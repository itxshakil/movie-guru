<script setup>
import {computed} from 'vue';
import {chipClasses} from '@/Components/Ui/buttonClasses.js';

const props = defineProps({
    as: {type: String, default: 'button'},
    active: {type: Boolean, default: false},
    size: {type: String, default: 'md'},
});

const classes = computed(() => chipClasses({active: props.active, size: props.size, interactive: props.as !== 'span'}));
</script>

<template>
    <component :is="as" :class="classes" :type="as === 'button' ? 'button' : undefined">
        <slot/>
    </component>
</template>
