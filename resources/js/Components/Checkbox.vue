<script setup>
import {computed} from 'vue';

const emit = defineEmits(['update:checked']);

const props = defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
    value: {
        default: null,
    },
});

const proxyChecked = computed({
    get() {
        return props.checked;
    },

    set(val) {
        emit('update:checked', val);
    },
});
</script>

<template>
    <input type="checkbox" :value="value" v-model="proxyChecked"
           class="rounded-sm dark:bg-primary-900 border-primary-300 dark:border-primary-700 text-primary-600 shadow-xs focus:ring-primary-500 dark:focus:ring-primary-600 dark:focus:ring-offset-primary-800"/>
</template>
