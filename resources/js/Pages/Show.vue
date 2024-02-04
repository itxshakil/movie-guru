<script setup>
import {Head} from '@inertiajs/vue3';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import DetailCard from '@/Components/DetailCard.vue';

const props = defineProps({
    detail: Object
})

defineOptions({ layout: BaseLayout })

const pageTitle = 'Details of ' + props.detail.Title;
const pageDescription = props.detail.Plot;

</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>
        <meta :content="pageDescription" head-key="description" name="description"/>
        <meta :content="pageTitle" head-key="subject" name="subject"/>
        <meta :content="pageTitle" head-key="og:title" name="og:title"/>
        <meta :content="pageDescription" head-key="og:description" name="og:description"/>
    </Head>

    <DetailCard :detail="detail" class="relative isolate px-6 pt-14 lg:px-8 dark:bg-gray-900 dark:text-white" />
    <NewsletterForm/>
</template>

