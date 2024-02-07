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
            class="w-full max-w-xs p-4 text-gray-500 bg-white rounded-lg shadow-md dark:bg-gray-800 dark:text-gray-400 fixed bottom-0 right-0 m-2"
            role="alert"
        >
            <div class="flex">
                <div
                    class="hidden inline-flex items-center justify-center flex-shrink-0 w-8 h-8 text-primary-500 bg-primary-100 rounded-lg dark:text-primary-300 dark:bg-primary-900"
                >
                    <svg
                        class="w-4 h-4"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 18 20"
                    >
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M16 1v5h-5M2 19v-5h5m10-4a8 8 0 0 1-14.947 3.97M1 10a8 8 0 0 1 14.947-3.97"
                        />
                    </svg>
                    <span class="sr-only">Refresh icon</span>
                </div>
                <div class="ms-3 text-sm font-normal">
                    <span
                        class="mb-1 text-sm font-semibold text-gray-900 dark:text-white"
                        >{{ title }}</span
                    >
                    <div class="mb-2 text-sm font-normal">
                        {{ description }}
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div v-if="primaryAction">
                            <button
                                @click="onConfirm"
                                class="inline-flex justify-center w-full px-2 py-1.5 text-xs font-medium text-center text-white bg-primary-600 rounded-lg hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 dark:bg-primary-500 dark:hover:bg-primary-600 dark:focus:ring-primary-800"
                            >
                                {{ primaryAction }}
                            </button>
                        </div>
                        <div>
                            <button
                                @click="onCancel"
                                class="inline-flex justify-center w-full px-2 py-1.5 text-xs font-medium text-center text-gray-900 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 dark:bg-gray-600 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-700 dark:focus:ring-gray-700"
                            >
                                {{ secondaryAction }}
                            </button>
                        </div>
                    </div>
                </div>
                <button
                    @click="onCancel"
                    type="button"
                    class="ms-auto -mx-1.5 -my-1.5 bg-white items-center justify-center flex-shrink-0 text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700"
                    data-dismiss-target="#toast-interactive"
                    aria-label="Close"
                >
                    <span class="sr-only">Close</span>
                    <svg
                        class="w-3 h-3"
                        aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 14 14"
                    >
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { ref, onMounted, nextTick } from "vue";

const props = defineProps({
    title: String,
    description: String,
    delay: {
        type: Number,
        default: 1000,
    },
    primaryAction: {
        type: String,
        default: "Okay",
    },
    secondaryAction: {
        type: String,
        default: "Not Now",
    },
    closeOnConfirm: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(["close", "confirm"]);

const show = ref(false);

const onCancel = () => {
    nextTick(() => {
        show.value = false;
    });
    emit("close");
};

const onConfirm = () => {
    nextTick(() => {
        if (props.closeOnConfirm) {
            show.value = false;
        }
    });
    emit("confirm");
}

onMounted(() => {
    setTimeout(() => {
        show.value = true;
    }, props.delay);
});
</script>
