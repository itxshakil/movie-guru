<script setup>
import Snackbar from "@/Components/Snackbar.vue";
import { ref, onMounted, onUnmounted, computed } from "vue";

const promptNotify = ref(false);
const onlineStatus = ref(navigator.onLine);
const promptTitle = ref("Network Status");
const primaryAction = ref(null);
const secondaryAction = ref("Okay");

const statusMessage = computed(() => {
    return onlineStatus.value
        ? "You are now online."
        : "You are currently offline. Enable notifications to be notified when the network is available.";
});

const updateOnlineStatus = () => {
    onlineStatus.value = navigator.onLine;
    if (onlineStatus.value) {
        promptNotify.value = true;
        primaryAction.value = null;
    } else {
        promptNotify.value = true;
        primaryAction.value = "Notify Me";
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
        :description="statusMessage"
        :primaryAction="primaryAction"
        :secondaryAction="secondaryAction"
        @close="cancelPrompt"
        @confirm="cancelPrompt"
        :delay="100"
    />
</template>
