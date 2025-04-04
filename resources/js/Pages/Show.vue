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
const pageUrl = window.location.href;

const generateMetaTitle = (detail) => {
  if (!detail || !detail.Title) {
    return 'MovieGuru - Find Your Next Favorite'; // Fallback
  }
  const year = detail.Year ? `(${detail.Year})` : '';

  return `Watch ${detail.Title} ${year} Online: Cast, Plot & Info`;
};

const generateMetaDescription = (detail) => {
  if (!detail || !detail.Title || !detail.Plot) {
    return 'Explore a vast library of movies and series. Find details, ratings, and where to watch your favorites.';
  }
  const year = detail.Year ? `(${detail.Year})` : '';
  const genre = detail.Genre ? `Genre: ${detail.Genre.split(', ')[0]}. ` : '';
  const cast = detail.Actors ? `Starring: ${detail.Actors.split(', ').slice(0, 2).join(', ')}. ` : '';

  const plotSnippet = detail.Plot.substring(0, 100) + (detail.Plot.length > 100 ? '...' : '');

  return `${detail.Title} ${year}: ${genre}${cast}${plotSnippet} Find streaming options and detailed information on MovieGuru.`;
};

const pageTitle = generateMetaTitle(props.detail);
const pageDescription = generateMetaDescription(props.detail);
const ogImage = moviePoster(props.detail);

</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>
        <meta :content="pageDescription" head-key="description" name="description"/>
        <meta property="subject" :content="pageTitle" head-key="subject">

        <meta property="og:url" :content="pageUrl" head-key="og:url">
        <meta property="og:type" content="website">
        <meta property="og:title" :content="pageTitle" head-key="og:title">
        <meta property="og:description" :content="pageDescription" head-key="og:description">
        <meta property="og:image" :content="ogImage" head-key="og:image">

        <!-- Twitter Meta Tags -->
        <meta name="twitter:card" content="summary_large_image">
        <meta property="twitter:domain" content="movieguru.shakiltech.com">
        <meta property="twitter:url" :content="pageUrl" head-key="twitter:url">
        <meta name="twitter:title" :content="pageTitle" head-key="twitter:title">
        <meta name="twitter:description" :content="pageDescription" head-key="twitter:description">
        <meta name="twitter:image" :content="ogImage" head-key="twitter:image">

        <link rel="canonical" :href="pageUrl" head-key="canonical" />
    </Head>

    <DetailCard :detail="detail" class="relative isolate px-6 pt-14 lg:px-8 dark:bg-gray-900 dark:text-white" />
    <NewsletterForm/>
</template>
