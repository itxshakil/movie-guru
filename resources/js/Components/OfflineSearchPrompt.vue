<script setup>
import Snackbar from "@/Components/SnackBar.vue";
import {onBeforeUnmount, onMounted, ref} from "vue";

const promptNotify = ref(false);
const installAppPromptDescription = ref(
    "Offline? No problem, Movie Guru will remember your search and send you a notification when results are ready."
);
const primaryAction = ref("Notify Me");
const secondaryAction = ref("Not Now");
const promptTitle = ref("Network Unavailable");

// The service worker queues the search in IndexedDB itself; this prompt only asks
// for notification permission so the worker can tell the user when it is ready.
const handleServiceWorkerMessage = (event) => {
    if (event.data?.type === "OFFLINE_SEARCH_DETECTED" && event.data.status === "offline") {
        promptNotify.value = true;
    }
};

onMounted(() => {
    navigator.serviceWorker?.addEventListener("message", handleServiceWorkerMessage);
});

onBeforeUnmount(() => {
    navigator.serviceWorker?.removeEventListener("message", handleServiceWorkerMessage);
});

const cancelPrompt = () => {
    promptNotify.value = false;
};

const handlePostNoticationConfirm = () => {
    primaryAction.value = null;
    secondaryAction.value = "Okay";
};

const handleNotificationConfirm = () => {
    if (!("Notification" in window)) {
        installAppPromptDescription.value =
            "This browser does not support notifications. Your search will still load once you're back online.";
        handlePostNoticationConfirm();
        return;
    }

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
