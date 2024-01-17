<script setup>
defineProps(['message']);
</script>

<template>
    <transition enter-active-class="transition-opacity duration-500 ease-in-out" leave-active-class="transition-opacity duration-500 ease-in-out">
        <div v-show="message"  key="message" class="opacity-100">
            <p class="text-sm text-red-600 dark:text-red-400" role="alert" aria-live="assertive">
                {{ message }}
            </p>
        </div>
    </transition>
</template>
