<script setup>
import AppFooter from '@/Layouts/AppFooter.vue';
import ToastNotifications from '@/Components/ToastNotifications.vue';
import InstallBanner from '@/Components/InstallBanner.vue';
import AppHeader from '@/Layouts/AppHeader.vue';
import OfflineSearchPrompt from '@/Components/OfflineSearchPrompt.vue';
import NetworkStatus from '@/Components/NetworkStatus.vue';

</script>

<template>
    <AppHeader/>
    <slot />
    <AppFooter/>
    <ToastNotifications />
    <InstallBanner />
    <OfflineSearchPrompt />
    <NetworkStatus />
    <slot name="modals" />
</template>
