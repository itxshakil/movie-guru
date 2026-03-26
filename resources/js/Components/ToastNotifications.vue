<script setup>
import {usePage} from '@inertiajs/vue3';
import {router} from '@inertiajs/core';
import {ref} from 'vue';
import ToastNotificationItem from '@/Components/ToastNotificationItem.vue';

const notifications = ref([]);

router.on('finish', () => {
    addNotificationFromPage();
});

const addNotification = (message, level = 'info') => {
    notifications.value.push({
        key: Symbol(),
        message,
        level
    });
};

function addNotificationFromPage() {
    const page = usePage();
    if (page.props.success) {
        addNotification(page.props.success, 'success');
    }

    if (page.props.error) {
        addNotification(page.props.error, 'danger');
    }

    if (page.props.warning) {
        addNotification(page.props.warning, 'warning');
    }

    if (page.props.info) {
        addNotification(page.props.info);
    }
}

const remove = (index) => {
    notifications.value.splice(index, 1);
};

// Listen for UI-triggered toasts (e.g. NewsletterForm)
const broadcastChannel = new BroadcastChannel('toast-notifications');
broadcastChannel.onmessage = (event) => {
    if (event.data.message) {
        addNotification(event.data.message, event.data.level);
    }
};

// Listen for service worker toasts (APP_UPDATED, APP_AVAILABLE_OFFLINE, etc.)
const swChannel = new BroadcastChannel('service-worker-channel');
swChannel.onmessage = (event) => {
    const toastTypes = ['APP_AVAILABLE_OFFLINE', 'APP_UPDATED', 'OFFLINE_SYNC_FETCHED', 'NOTIFICATION_PERMISSION_DENIED'];
    if (event.data && toastTypes.includes(event.data.type) && event.data.message) {
        addNotification(event.data.message, event.data.level);
    }
};

</script>

<template>
    <div
        aria-live="polite"
        class="flex flex-col-reverse gap-4 fixed bottom-4 right-4 z-50 pointer-events-none w-full max-w-80"
        role="status"
    >
        <ToastNotificationItem
            v-for="(notification, index) in notifications"
            :key="notification.key"
            :level="notification.level"
            :message="notification.message"
            class="toast-item"
            @remove="remove(index)"
        />
    </div>
</template>

<style>
.toast-item {
    transition: opacity 0.5s ease, transform 0.5s ease;
}

@starting-style {
    .toast-item {
        opacity: 0;
        transform: translateX(100%);
    }
}
</style>
