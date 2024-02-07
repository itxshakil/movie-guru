<script setup>
import Snackbar from "@/Components/Snackbar.vue";
import { ref, onMounted, onUnmounted } from "vue";

const promptNotify = ref(false);
const onlineStatus = ref(navigator.onLine);
const promptTitle = ref("Network Status");
const primaryAction = ref(null);
const secondaryAction = ref("Okay");
const promptDescriptionOnline = ref("You are now online.");
const promptDescriptionOffline = ref("You are currently offline. Enable notifications to be notified when the network is available.");

const updateOnlineStatus = () => {
    onlineStatus.value = navigator.onLine;
    if (onlineStatus.value) {
        promptNotify.value = true;
        primaryAction.value = "Notify Me";
    } else {
        promptNotify.value = true;
        primaryAction.value = null;
    }
};

onMounted(() => {
    window.addEventListener("online", updateOnlineStatus);
    window.addEventListener("offline", updateOnlineStatus);
});

onUnmounted(() => {
    window.removeEventListener("online", updateOnlineStatus);
    window.removeEventListener("offline", updateOnlineStatus);
});

const cancelPrompt = () => {
    promptNotify.value = false;
};
 
</script>

<template>
    <Snackbar
        v-if="promptNotify"
        :title="promptTitle"
        :description="onlineStatus.value ? promptDescriptionOnline : promptDescriptionOffline"
        :primaryAction="primaryAction"
        :secondaryAction="secondaryAction"
        @close="cancelPrompt"
        :delay="100"
    />
</template>
