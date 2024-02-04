<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import LoadingSpinnerIcon from '@/Components/Icons/LoadingSpinner.vue';

defineProps({
    isLoading: {
        type: Boolean,
        default: false
    },
    value: {
        type: String,
        default: null
    }
});
</script>
<template>
    <PrimaryButton :disabled="isLoading" :class="isLoading ? 'opacity-70 cursor-wait' : ''">
        <LoadingSpinnerIcon v-show="isLoading" class="w-6 h-6 mr-1 -ml-2 rtl:ml-1 rtl:-mr-2" />
        <span v-if="value" class="font-bold leading-wide" v-text="value" />
        <slot v-else />
    </PrimaryButton>
</template>
