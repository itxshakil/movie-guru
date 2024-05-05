<script setup>
import {Head} from '@inertiajs/vue3';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import DetailCard from '@/Components/DetailCard.vue';

const props = defineProps({
    detail: Object
})

defineOptions({ layout: BaseLayout })

const moviePoster = (movie) => {
    return movie.Poster && movie.Poster !== 'N/A' ? movie.Poster : '/assets/images/no-poster.jpg';
};

const pageTitle = `Details of ${props.detail.Title}`;
const pageDescription = props.detail.Plot;
const ogImage = moviePoster(props.detail);

</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>
        <meta :content="pageDescription" head-key="description" name="description"/>
        <meta :content="pageTitle" head-key="subject" name="subject"/>
        <meta :content="pageTitle" head-key="og:title" name="og:title"/>
        <meta :content="pageDescription" head-key="og:description" name="og:description"/>
        <meta :content="ogImage" head-key="og:image" name="og:image"/>

        <meta :content="pageDescription" head-key="twitter:card" name="twitter:card"/>
        <!-- <meta :content="pageDescription" head-key="twitter:url" name="twitter:url"/> -->
        <meta :content="pageTitle" head-key="twitter:title" name="twitter:title"/>
        <meta :content="pageDescription" head-key="twitter:description" name="twitter:description"/>
        <meta :content="ogImage" head-key="twitter:image" name="twitter:image"/>
    </Head>

    <DetailCard :detail="detail" class="relative isolate px-6 pt-14 lg:px-8 dark:bg-gray-900 dark:text-white" />
    <NewsletterForm/>
</template>
