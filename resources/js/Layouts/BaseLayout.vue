<script setup>
import AppFooter from '@/Layouts/AppFooter.vue';
import ToastNotifications from '@/Components/ToastNotifications.vue';
import InstallBanner from '@/Components/InstallBanner.vue';
import AppHeader from '@/Layouts/AppHeader.vue';

</script>

<template>
    <AppHeader/>
    <slot />
    <AppFooter/>
    <ToastNotifications />
    <InstallBanner />

    <slot name="modals" />
</template>
