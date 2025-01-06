<script setup>
import {Head, useForm} from '@inertiajs/vue3';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import OurFeatures from '@/Components/OurFeatures.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import LoadingSpinnerIcon from '@/Components/Icons/LoadingSpinner.vue';

const props = defineProps({
  trendingSearchQueries: Array
});

const form = useForm({
  s: '',
});

const doSearch = () => {
  form.get(route('search'));
};

// Randomize trending search queries
const shuffledTrendingQueries = [...props.trendingSearchQueries].sort(() => Math.random() - 0.5);

defineOptions({ layout: BaseLayout });

const pageTitle = 'Discover. Explore. Enjoy.';
const pageDescription = 'Explore an extensive database of movies with detailed information, reviews, and ratings. Find your next favorite film effortlessly with our user-friendly search feature. Your gateway to a universe of entertainment awaits!';
const pageUrl = window.location.href;
const ogImage = "https://movieguru.shakiltech.com/icons/ios/64.png";
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

  <div class="relative isolate px-6 pt-14 lg:px-8 dark:bg-gray-900 dark:text-white">
    <div class="mx-auto max-w-7xl py-32 sm:py-48 lg:py-56">
      <div class="text-center">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">
          🎬 Discover. Explore. Enjoy.🎥
        </h1>
        <h2 class="mt-6 sm:text-lg leading-8 text-gray-600 dark:text-gray-400">
          Explore a vast collection of movies and find your next favorite.
        </h2>
        <div class="mt-10 flex items-center justify-center gap-x-6">
          <form class="flex gap-2 flex-col sm:flex-row items-center w-full max-w-lg relative"
                @submit.prevent="doSearch">
            <div class="w-full">
              <InputLabel class="sr-only" for="search-input">Search movies and series</InputLabel>
              <input id="search-input" v-model="form.s" enterkeyhint="search"
                     aria-label="Search Movie or Series"
                     class="sm:text-xl w-full rounded-full py-3 px-5 sm:py-5 sm:px-10 bg-gray-300 text-gray-900 focus:outline-none sm:pr-36 border-gray-300 dark:border-gray-700 dark:bg-gray-800 disabled:bg-slate-400 dark:disabled:bg-gray-600 dark:read-only:bg-gray-600 dark:text-gray-300 dark:focus:border-primary-600 dark:focus:ring-primary-600 shadow-sm bg-gray-400/10 placeholder-gray-500 border-transparent transition duration-75 focus:bg-white focus:placeholder-gray-400 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 dark:focus:text-gray-700 dark:placeholder-gray-400 focus:read-only:text-gray-200"
                     name="s"
                     placeholder="Search movies and series"
                     required autofocus autocomplete="off"
                     type="search"/>
            </div>
            <button :class="form.processing ? 'opacity-70 cursor-wait' : ''" :disabled="form.processing"
                    class="flex items-center justify-center sm:text-xl tracking-wider font-semibold w-full sm:w-32 sm:absolute right-1 bg-primary-500 hover:bg-primary-600 text-white py-3 px-5 sm:py-4 sm:px-8 rounded-full"
                    type="submit">
              <LoadingSpinnerIcon v-show="form.processing"
                                  class="w-6 h-6 mr-1 -ml-2 rtl:ml-1 rtl:-mr-2"/>
              <span>Search</span>
            </button>
          </form>
        </div>
          <div class="mt-4">
            <h3 class="text-lg font-medium text-gray-600 dark:text-gray-400 sr-only">Trending Searches:</h3>
            <ul class="flex flex-wrap justify-center mt-2 gap-2">
              <li v-for="(query, index) in shuffledTrendingQueries.slice(0, 5)" :key="index">
                <button @click="form.s = query; doSearch()"
                        class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-full hover:bg-primary-500 hover:text-white dark:hover:bg-primary-600 transition">
                  {{ query }}
                </button>
              </li>
            </ul>
        </div>
      </div>
    </div>
  </div>
  <OurFeatures/>
  <NewsletterForm/>
</template>
