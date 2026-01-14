<script setup>
import AppFooter from "@/Layouts/AppFooter.vue";
import ToastNotifications from "@/Components/ToastNotifications.vue";
import InstallBanner from "@/Components/InstallBanner.vue";
import AppHeader from "@/Layouts/AppHeader.vue";
import OfflineSearchPrompt from "@/Components/OfflineSearchPrompt.vue";
import NetworkStatus from "@/Components/NetworkStatus.vue";
import NotificationBanner from "@/Components/NotificationBanner.vue";
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-gray-950 pb-[env(safe-area-inset-bottom)]">
        <AppHeader/>
        <slot/>
        <AppFooter/>
        <ToastNotifications/>
        <InstallBanner/>
        <NotificationBanner/>
        <OfflineSearchPrompt/>
        <NetworkStatus/>

        <div
            class="fixed bottom-0 right-0 z-50 flex flex-col snackbar-container mb-2"
        ></div>
    </div>
</template>
