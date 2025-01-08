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

const broadcastChannel = new BroadcastChannel('toast-notifications');
broadcastChannel.onmessage = (event) => {
  addNotification(event.data.message, event.data.level);
};

</script>

<template>
    <TransitionGroup tag="div" aria-live="polite"
        class="flex flex-col fixed bottom-4 right-4 z-50 pointer-events-none w-full max-w-sm space-y-4" role="status"
        enter-from-class="translate-x-full opacity-0" enter-active-class="duration-500" leave-active-class="duration-500"
        leave-to-class="translate-x-full opacity-0">
        <ToastNotificationItem v-for="(notification, index) in notifications" :key="notification.key"
            :message="notification.message" :level="notification.level" @remove="remove(index)" />
    </TransitionGroup>
</template>
