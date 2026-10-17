<script setup>
import {computed} from 'vue';
import {Link} from '@inertiajs/vue3';
import LoadingSpinner from '@/Components/Icons/LoadingSpinner.vue';
import {buttonClasses, buttonTag} from '@/Components/Ui/buttonClasses.js';

const props = defineProps({
    variant: {type: String, default: 'primary'},
    size: {type: String, default: 'md'},
    href: {type: String, default: ''},
    type: {type: String, default: 'button'},
    loading: {type: Boolean, default: false},
    disabled: {type: Boolean, default: false},
    block: {type: Boolean, default: false},
});

const classes = computed(() => buttonClasses(props));
const isLink = computed(() => buttonTag({href: props.href, disabled: props.disabled || props.loading}) === 'link');
</script>

<template>
    <Link v-if="isLink" :class="classes" :href="href">
        <slot name="icon"/>
        <slot/>
    </Link>
    <button v-else :aria-busy="loading || undefined" :class="classes" :disabled="disabled || loading" :type="type">
        <LoadingSpinner v-if="loading" aria-hidden="true" class="h-4 w-4"/>
        <slot v-else name="icon"/>
        <slot/>
    </button>
</template>
