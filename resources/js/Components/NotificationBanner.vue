<script setup>
import Snackbar from "@/Components/SnackBar.vue";
import {onMounted, ref} from "vue";

const isNotificationSupported = typeof Notification !== "undefined";
const promptForNotification = ref(false);
const hasNotificationPermission = ref(isNotificationSupported && Notification.permission === "granted");

// Notification prompt content
const notificationPromptTitle = "Enable Notifications";
const notificationPromptDescription =
    "Stay updated with the latest movie trends, reviews, and recommendations. Enable notifications to never miss out!";
const notificationPromptPrimaryAction = "Enable Now";
const notificationPromptSecondaryAction = "Maybe Later";

onMounted(() => {
  // Check if we should prompt for notifications
  if (
      isNotificationSupported &&
      Notification.permission === "default" &&
      !hasNotificationPermission.value
  ) {
    setTimeout(() => {
      promptForNotification.value = true;
    }, 15000); // Delay prompt more to not compete with install banner
  }
});

const requestNotificationPermission = () => {
  Notification.requestPermission().then((permission) => {
    if (permission === "granted") {
      hasNotificationPermission.value = true;
    }
    promptForNotification.value = false;
  }).catch((error) => {
    console.error("Error requesting notification permission", error);
    promptForNotification.value = false;
  });
};
</script>

<template>
  <Snackbar
      v-if="promptForNotification"
      :delay="20000"
      :description="notificationPromptDescription"
      :primaryAction="notificationPromptPrimaryAction"
      :secondaryAction="notificationPromptSecondaryAction"
      :title="notificationPromptTitle"
      @close="promptForNotification = false"
      @confirm="requestNotificationPermission"
  />
</template>
