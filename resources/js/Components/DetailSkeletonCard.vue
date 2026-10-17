<template>
    <div class="grid w-full animate-pulse grid-cols-1 items-start gap-x-6 gap-y-6 p-4 sm:grid-cols-12 sm:p-6 lg:gap-x-8 lg:p-8"
         role="status"
    >
        <div class="flex aspect-2/3 items-center justify-center rounded-2xl bg-gray-200 sm:col-span-4 dark:bg-gray-800">
            <svg aria-hidden="true" class="h-12 w-12 text-gray-300 dark:text-gray-700"
                 fill="currentColor"
                 viewBox="0 0 20 18" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M18 0H2a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2Zm-5.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm4.376 10.481A1 1 0 0 1 16 15H4a1 1 0 0 1-.895-1.447l3.5-7A1 1 0 0 1 7.468 6a.965.965 0 0 1 .9.5l2.775 4.757 1.546-1.887a1 1 0 0 1 1.618.1l2.541 4a1 1 0 0 1 .028 1.011Z"/>
            </svg>
        </div>
        <div class="min-w-0 sm:col-span-8">
            <h1 v-if="title" class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white" v-text="title"></h1>
            <div v-else class="h-9 w-2/3 max-w-sm rounded-lg bg-gray-200 dark:bg-gray-800"></div>
            <div class="mt-3 h-4 w-56 rounded-full bg-gray-200 dark:bg-gray-800"></div>

            <div class="mt-4 flex flex-wrap gap-2">
                <div v-for="(width, index) in ['w-24', 'w-24', 'w-20']" :key="index"
                     :class="width" class="h-8 rounded-xl bg-gray-200 dark:bg-gray-800"></div>
            </div>

            <div class="mt-4 h-5 w-48 rounded-full bg-gray-200 dark:bg-gray-800"></div>

            <div class="mt-6 h-4 w-28 rounded-full bg-gray-200 dark:bg-gray-800"></div>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="index in 3" :key="index"
                     class="flex items-center gap-3 rounded-xl border border-gray-200 p-3 dark:border-gray-800">
                    <div class="h-10 w-10 shrink-0 rounded-lg bg-gray-200 dark:bg-gray-800"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-3 w-3/4 rounded-full bg-gray-200 dark:bg-gray-800"></div>
                        <div class="h-3 w-1/2 rounded-full bg-gray-200 dark:bg-gray-800"></div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-2">
                <div class="h-4 w-full rounded-full bg-gray-200 dark:bg-gray-800"></div>
                <div class="h-4 w-5/6 rounded-full bg-gray-200 dark:bg-gray-800"></div>
            </div>
        </div>
        <span class="sr-only">Loading...</span>
    </div>
</template>
<script setup>
defineProps({
    title: {
        type: String,
        required: false,
    }
});
</script>
