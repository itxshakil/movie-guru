<script setup>
import Snackbar from "@/Components/SnackBar.vue";
import {onMounted, ref, watch} from "vue";

const isIos = () => {
    if (typeof window === 'undefined') return false;
    const userAgent = window.navigator.userAgent.toLowerCase();
    return /iphone|ipad|ipod/.test(userAgent);
};

const isStandalone = () => {
    if (typeof window === 'undefined') return false;
    return (
        window.navigator.standalone ||
        window.matchMedia("(display-mode: standalone)").matches
    );
};

const isAppInstalled = ref(isStandalone());
const promptForInstall = ref(false);
const beforeinstallprompt = ref(null);

const installAppPromptTitle = isIos() ? "Add to Home Screen" : "Install App";
const installAppPromptDescription = isIos()
    ? 'Install this application on your home screen for quick, easy and offline access. Tap the "share" icon at the bottom of your browser, then tap "Add to Home Screen"'
    : "Install this app on your home screen for a better experience. It's free and only takes a few seconds.";
const installAppPromptPrimaryAction = isIos() ? "Add Now" : "Install Now";
const installAppPromptSecondaryAction = "Not Now";

onMounted(() => {
    window.addEventListener("beforeinstallprompt", (e) => {
        e.preventDefault();
        console.log("beforeinstallprompt event fired");
        beforeinstallprompt.value = e;
    });

    // Check if we should prompt
    if (!isAppInstalled.value && lastPromptDaysAgo()) {
        if (isIos()) {
            // IOS doesn't have beforeinstallprompt, so we prompt if not standalone
            setTimeout(() => {
                promptForInstall.value = true;
                localStorage.setItem("lastPromptDate", new Date().toISOString());
            }, 10000);
        }
    }
});

const installApp = () => {
    promptForInstall.value = false;
    if (beforeinstallprompt.value) {
        beforeinstallprompt.value.prompt();
        beforeinstallprompt.value.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === "accepted") {
                isAppInstalled.value = true;
            }
        });
    } else if (isIos()) {
        // Just hide the banner, user has to do it manually
        promptForInstall.value = false;
    }
};

const lastPromptDaysAgo = (days = 7) => {
    const localStorageKeyValue = localStorage.getItem("lastPromptDate");
    if (localStorageKeyValue) {
        const lastPromptDate = new Date(localStorageKeyValue);
        const currentDate = new Date();
        const timeDiff = currentDate.getTime() - lastPromptDate.getTime();
        const daysDifference = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));

        return daysDifference >= days;
    }

    return true;
};

watch(beforeinstallprompt, (value) => {
    if (value && !isAppInstalled.value && lastPromptDaysAgo()) {
        setTimeout(() => {
            promptForInstall.value = true;
            localStorage.setItem("lastPromptDate", new Date().toISOString());
        }, 5000);
    }
});
</script>

<template>
    <Snackbar
        v-if="promptForInstall"
        :title="installAppPromptTitle"
        :description="installAppPromptDescription"
        :primaryAction="installAppPromptPrimaryAction"
        :secondaryAction="installAppPromptSecondaryAction"
        @confirm="installApp"
        @close="promptForInstall = false"
        :delay="20000"
    />
</template>
