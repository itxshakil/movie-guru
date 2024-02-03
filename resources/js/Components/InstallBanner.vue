<script setup>
import Snackbar from "@/Components/Snackbar.vue";
import { ref, onMounted,watch } from "vue";

const isAppInstalled = ref(false);
const promptForInstall = ref(false);
const beforeinstallprompt = ref(null);

if (
    navigator.standalone ||
    window.matchMedia("(display-mode: standalone)").matches
) {
    isAppInstalled.value = true;
}

const isIos = () => {
    const userAgent = window.navigator.userAgent.toLowerCase();
    return /iphone|ipad|ipod/.test(userAgent);
};

const installAppPromptTitle = isIos() ? "Add to Home Screen" : "Install App";
const installAppPromptDescription = isIos()
    ? 'Install this application on your home screen for quick, easy and offline access when you’re on the go. Close this modal, tap the "share" icon, and then tap on "Add to home screen"'
    : "Install this app on your home screen for a better experience. It's free and only takes a few seconds.";
const installAppPromptPrimaryAction = isIos() ? "Add Now" : "Install Now";
const installAppPromptSecondaryAction = "Not Now";

onMounted(() => {
    window.addEventListener("beforeinstallprompt", (e) => {
        e.preventDefault();
        console.log("beforeinstallprompt event fired");
        beforeinstallprompt.value = e;
    });
});

const installApp = () => {
    promptForInstall.value = false
    if (beforeinstallprompt.value || navigator.standalone === false) {
        beforeinstallprompt.value.prompt();
        beforeinstallprompt.value.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === "accepted") {
                isAppInstalled.value = true;
            }
        });
    }
};

const lastPromptDaysAgo = (days = 30) => {
    const localStorageKeyValue = localStorage.getItem("lastPromptDate");
    if (localStorageKeyValue) {
        const lastPromptDate = new Date(localStorageKeyValue);
        const currentDate = new Date();
        const daysDifference = Math.ceil(
            (currentDate - lastPromptDate) / (1000 * 60 * 60 * 24)
        );

        return daysDifference >= days;
    }

    return true;
};

if (
    !isAppInstalled.value &&
    lastPromptDaysAgo() &&
    (beforeinstallprompt.value || isIos())
) {
    promptForInstall.value = true;
    localStorage.setItem("lastPromptDate", new Date());
}

watch(beforeinstallprompt, (value) => {
    if (value) {
        promptForInstall.value = true;
        localStorage.setItem("lastPromptDate", new Date());
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
