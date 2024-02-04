<script setup>
import {computed} from 'vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';

const props = defineProps({ status: Number })

const title = computed(() => {
    return {
        503: 'Service Unavailable',
        500: 'Server Error',
        404: 'Page Not Found',
        403: 'Forbidden',
    }[props.status]
})

const description = computed(() => {
    return {
        503: 'Sorry, we are doing some maintenance. Please check back soon.',
        500: 'Whoops, something went wrong on our servers.',
        404: 'Sorry, the page you are looking for could not be found.',
        403: 'Sorry, you are forbidden from accessing this page.',
    }[props.status]
})

defineOptions({ layout: BaseLayout })

const pageTitle = title;
const pageDescription = description;
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>
        <meta :content="pageDescription" head-key="description" name="description"/>
        <meta :content="pageTitle" head-key="subject" name="subject"/>
        <meta :content="pageTitle" head-key="og:title" name="og:title"/>
        <meta :content="pageDescription" head-key="og:description" name="og:description"/>
    </Head>
    <main class="grid min-h-full place-items-center bg-white dark:bg-gray-900 text-white py-24 sm:py-32 max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
        <div class="text-center">
            <p class="text-base font-semibold text-primary-600 dark:text-gray-400">{{  status }}</p>
            <h1 class="mt-4 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">{{ title }}</h1>
            <p class="mt-6 text-base leading-7 text-gray-600 dark:text-gray-400">{{ description}}</p>
            <div class="mt-10 flex items-center justify-center gap-x-6">
                <a href="/"
                   class="rounded-md bg-primary-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                    Go back home
                </a>
                <a href="/contact" class="text-sm font-semibold text-gray-900 dark:texxt-white">
                    Contact support<span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>
    </main>
</template>
