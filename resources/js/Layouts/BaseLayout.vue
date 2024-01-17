<script setup>
import AppFooter from '@/Layouts/AppFooter.vue';
import ToastNotifications from '@/Components/ToastNotifications.vue';
import AppHeader from '@/Layouts/AppHeader.vue';

</script>

<template>
    <AppHeader/>
    <slot />
    <AppFooter/>
    <ToastNotifications />

    <slot name="modals" />
</template>

