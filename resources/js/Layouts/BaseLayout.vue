<script setup>
import AppFooter from "@/Layouts/AppFooter.vue";
import ToastNotifications from "@/Components/ToastNotifications.vue";
import InstallBanner from "@/Components/InstallBanner.vue";
import AppHeader from "@/Layouts/AppHeader.vue";
import OfflineSearchPrompt from "@/Components/OfflineSearchPrompt.vue";
import NetworkStatus from "@/Components/NetworkStatus.vue";
</script>

<template>
    <AppHeader />
    <slot />
    <AppFooter />
    <ToastNotifications />
    <InstallBanner />
    <InstallBanner />
    <OfflineSearchPrompt />
    <NetworkStatus />

    <div
        class="fixed bottom-0 right-0 z-50 flex flex-col snackbar-container mb-2"
    ></div>
    <slot name="modals" />
</template>
