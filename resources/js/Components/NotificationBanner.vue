<script setup>
import Snackbar from "@/Components/Snackbar.vue";
import {onMounted, ref} from "vue";

const promptForNotification = ref(false);
const hasNotificationPermission = ref(Notification.permission === "granted");

// Notification prompt content
const notificationPromptTitle = "Enable Notifications";
const notificationPromptDescription =
    "Stay updated with the latest movie trends, reviews, and recommendations. Enable notifications to never miss out!";
const notificationPromptPrimaryAction = "Enable Now";
const notificationPromptSecondaryAction = "Maybe Later";

onMounted(() => {
  // Check if we should prompt for notifications
  if (
      Notification.permission === "default" &&
      !hasNotificationPermission.value
  ) {
    setTimeout(() => {
      promptForNotification.value = true;
    }, 5000); // Delay prompt to avoid overwhelming the user
  }
});

const requestNotificationPermission = () => {
  Notification.requestPermission().then((permission) => {
    if (permission === "granted") {
      hasNotificationPermission.value = true;
    }
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
