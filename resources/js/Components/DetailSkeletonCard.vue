<template>
    <div class="space-y-8 animate-pulse md:space-y-0 md:space-x-8 rtl:space-x-reverse md:items-center grid w-full grid-cols-1 items-start gap-x-6 gap-y-8 sm:grid-cols-12 lg:gap-x-8"
         role="status"
    >
        <div class="relative overflow-hidden rounded-lg sm:col-span-4 lg:col-span-5">
            <div
                class="flex items-center justify-center h-48 bg-gray-300 rounded-sm dark:bg-gray-700 aspect-h-3 aspect-w-2 mx-w-64 w-full">
                <svg aria-hidden="true" class="w-full h-full text-gray-200 dark:text-gray-600"
                     fill="currentColor"
                     viewBox="0 0 20 18" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M18 0H2a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2Zm-5.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3Zm4.376 10.481A1 1 0 0 1 16 15H4a1 1 0 0 1-.895-1.447l3.5-7A1 1 0 0 1 7.468 6a.965.965 0 0 1 .9.5l2.775 4.757 1.546-1.887a1 1 0 0 1 1.618.1l2.541 4a1 1 0 0 1 .028 1.011Z"/>
                </svg>
            </div>
            <div class="flex flex-wrap gap-1 absolute top-0 p-1 justify-end w-full">
                <span class="w-16 mb-2 bg-gray-400 rounded-full dark:bg-gray-600 p-1 sm:py-2 h-1 flex items-center gap-1"></span>
                <span class="w-16 mb-2 bg-gray-400 rounded-full dark:bg-gray-600 p-1 sm:py-2 h-1 flex items-center gap-1"></span>
            </div>
        </div>
        <div class="sm:col-span-8 lg:col-span-7">
          <h1 v-if="title" class="text-2xl font-bold text-gray-900 dark:text-white sm:pr-12" itemprop="name"
              v-text="title"></h1>
          <h2 v-else class="sm:pr-12 mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-56 p-2 sm:py-4"></h2>
            <span class="inline-flex mr-2 w-12 mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-16 p-1 sm:py-2 h-1"></span>
            <span class="inline-flex mr-2 w-16 mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-16 p-1 sm:py-2 h-1"></span>
            <span class="inline-flex mr-2 w-8 mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-16 p-1 sm:py-2 h-1"></span>
            <span class="inline-flex mr-2 w-12 mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-16 p-1 sm:py-2 h-1"></span>

            <section aria-labelledby="information-heading" class="mt-2">
                <div class="flex items-center">
                    <svg v-for="i in 5"
                         :key="i" :class="i <= 4 ? 'text-gray-300 dark:text-gray-200' : 'text-gray-500 dark:text-gray-400'"
                         aria-hidden="true" class="h-5 w-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path clip-rule="evenodd"
                              d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401z"
                              fill-rule="evenodd"/>
                    </svg>
                    <a class="ml-3 w-24 mb-2 bg-gray-200 rounded-full dark:bg-gray-700 p-1 sm:py-2" href="#"></a>
                </div>
                <p class="mt-2 w-full h-2 mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-[480px] p-1 sm:py-2 odd:bg-gray-200 dark:odd:bg-gray-800"></p>
                <p class="mt-2 w-full h-2 mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-[480px] p-1 sm:py-2 odd:bg-gray-200 dark:odd:bg-gray-800"></p>

                <div>
                    <div class="py-4 pb-2">
                        <h3 class="mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-48 p-2 sm:py-4"></h3>
                    </div>
                    <div>
                        <div v-for="_i in 5"
                             class="mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-[480px] p-2 sm:py-4 sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-200 dark:odd:bg-gray-800">
                            <dt class="w-6"></dt>
                            <dd class="mt-1 sm:col-span-3 sm:mt-0 w-16"></dd>
                        </div>
                        <div
                            class="p-2 sm:py-4 sm:grid sm:grid-cols-4 sm:gap-4 odd:bg-gray-200 dark:odd:bg-gray-800">
                            <dt class="mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-24 p-2 sm:py-4 odd:bg-gray-200 dark:odd:bg-gray-800 h-2"></dt>
                            <dd class="mt-2 sm:col-span-3 sm:mt-0">
                                <ul class="" role="list">
                                    <li v-for="index in 3" :key="index"
                                        class="mb-2 bg-gray-200 rounded-full dark:bg-gray-700 max-w-[480px] p-2 sm:py-4 odd:bg-gray-200 dark:odd:bg-gray-800 flex items-center justify-between py-4 pl-4 pr-5">
                                        <span class="w-12"></span>
                                        <span class="w-2"></span>
                                    </li>
                                </ul>
                            </dd>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <span class="sr-only">Loading...</span>
    </div>
</template>
<script setup>
const props = defineProps({
  title: {
    type: String,
    required: false,
  }
});
</script>
