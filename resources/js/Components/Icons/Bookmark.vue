<script setup>
defineProps({
    filled: Boolean,
});
</script>

<template>
    <svg aria-hidden="true" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24">
        <path d="M6 4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v17l-6-4-6 4V4z" :fill="filled ? 'currentColor' : 'none'"/>
    </svg>
</template>
