<script setup>
import {onMounted, ref} from 'vue';
import {FIELD_CLASSES} from '@/Components/Ui/fieldClasses.js';

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
        :class="FIELD_CLASSES"
        :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" ref="input" />
</template>
