<script setup>
import Snackbar from "@/Components/Snackbar.vue";
import { ref } from "vue";

const promptNotify = ref(false);
const requestUrl = ref(null);
const installAppPromptDescription = ref(
    "Offline? No problem, Movie Guru will remember your search and send you a notification when results are ready."
);
const primaryAction = ref("Notify Me");
const secondaryAction = ref("Not Now");
const promptTitle = ref("Network Unavailable");

// Add code to handle OFFLINE_SEARCH_DETECTED message
navigator.serviceWorker.addEventListener("message", (event) => {
    if (
        event.data.type === "OFFLINE_SEARCH_DETECTED" &&
        event.data.status === "offline"
    ) {
        requestUrl.value = event.data.url;
        promptNotify.value = true;
        localStorage.setItem("offlineRequestUrl", event.data.url);
    }
});

const cancelPrompt = () => {
    promptNotify.value = false;
};

const handlePostNoticationConfirm = () => {
    primaryAction.value = null;
    secondaryAction.value = "Okay";
};

const handleNotificationConfirm = () => {
    // Check for Notification API support
    if (!("Notification" in window)) {
        installAppPromptDescription.value =
            "This browser does not support notifications, please use a modern browser.";
        handlePostNoticationConfirm();
        return;
    }

    // Check for localStorage support
    if (!window.localStorage) {
        installAppPromptDescription.value =
            "This browser does not support localStorage, please use a modern browser.";
        handlePostNoticationConfirm();
        return;
    }

    // Check current notification permission status
    if (Notification.permission === "granted") {
        installAppPromptDescription.value =
            "You will be notified when the network is available.";
        handlePostNoticationConfirm();
    } else if (Notification.permission === "denied") {
        installAppPromptDescription.value =
            "You have blocked notifications. Please unblock in your browser settings.";
        handlePostNoticationConfirm();
    } else {
        Notification.requestPermission()
            .then((permission) => {
                if (permission === "granted") {
                    // save the requestUrl in localStorage
                    localStorage.setItem("offlineRequestUrl", requestUrl.value);
                    // Update the text to inform the user that they will be notified
                    installAppPromptDescription.value =
                        "You will be notified when the network is available.";
                } else {
                    installAppPromptDescription.value =
                        "You have denied notification permissions, you will not be notified when the network is available.";
                }
                handlePostNoticationConfirm();
            })
            .catch((error) => {
                console.error(
                    "Error requesting notification permission",
                    error
                );
                installAppPromptDescription.value =
                    "Error occurred while requesting notification permissions, please try again.";
                handlePostNoticationConfirm();
            });
    }
};
</script>

<template>
    <Snackbar
        v-if="promptNotify"
        :title="promptTitle"
        :description="installAppPromptDescription"
        :primaryAction="primaryAction"
        :secondaryAction="secondaryAction"
        :closeOnConfirm="false"
        @confirm="handleNotificationConfirm"
        @close="cancelPrompt"
        :delay="100"
    />
</template>
