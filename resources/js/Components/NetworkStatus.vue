<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";

const show = ref(false);
const onlineStatus = ref(navigator.onLine);
const message = ref("You are now online");

const updateOnlineStatus = () => {
    onlineStatus.value = navigator.onLine;
    if (onlineStatus.value) {
        show.value = true;
        message.value = "You are now online";

        setTimeout(() => {
            show.value = false;
        }, 1000);
    } else {
        show.value = true;
        message.value = "You are now offline";
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

const classes = computed(() => {
    return {
        "bg-green-600": onlineStatus.value,
        "bg-red-600": !onlineStatus.value,
    };
});
</script>

<template>
    <transition
        enter-active-class="duration-500 ease-out"
        enter-from-class="opacity-0 transform translate-y-2"
        enter-to-class="opacity-100 transform translate-y-0"
        leave-active-class="duration-500 ease-in"
        leave-from-class="opacity-100 transform translate-y-0"
        leave-to-class="opacity-0 transform translate-y-2"
    >
        <div
            v-if="show"
            class="w-full text-gray-100 shadow-md fixed bottom-0 right-0 z-10"
            :class="classes"
            role="alert"
        >
            <div class="flex justify-center">
                <span class="text-sm tracking-wider">
                    {{ message }}
                </span>
            </div>
        </div>
    </transition>
</template>
