<script setup>
import {Head, useForm} from '@inertiajs/vue3';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import OurFeatures from '@/Components/OurFeatures.vue';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import LoadingSpinnerIcon from '@/Components/Icons/LoadingSpinner.vue';

const form = useForm({
    s: '',
});

const doSearch = () => {
    form.get(route('search'));
};

defineOptions({ layout: BaseLayout })

const pageTitle = 'Discover. Explore. Enjoy.';
const pageDescription = 'Explore an extensive database of movies with detailed information, reviews, and ratings. Find your next favorite film effortlessly with our user-friendly search feature. Your gateway to a universe of entertainment awaits!';
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>
        <meta :content="pageDescription" head-key="description" name="description"/>
        <meta :content="pageTitle" head-key="subject" name="subject"/>
        <meta :content="pageTitle" head-key="og:title" name="og:title"/>
        <meta :content="pageDescription" head-key="og:description" name="og:description"/>
    </Head>

        <div class="relative isolate px-6 pt-14 lg:px-8 dark:bg-gray-900 dark:text-white">
            <div aria-hidden="true"
                 class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80">
                <div
                    class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#ff80b5] to-[#9089fc] opacity-30 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"
                    style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)"></div>
            </div>
            <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
                <div class="hidden sm:mb-8 sm:flex sm:justify-center">
                    <div
                        class="relative rounded-full px-3 py-1 text-sm leading-6 text-gray-600 dark:text-gray-400 ring-1 ring-gray-900/10 hover:ring-gray-900/20 dark:ring-gray-100/10 dark:hover:ring-gray-100/20">
                        We got a brand new design <a class="font-semibold text-primary-600"
                                                     href="https://movie-guru.shakiltech.com"><span
                        aria-hidden="true" class="absolute inset-0"></span>Legacy Design <span
                        aria-hidden="true">&rarr;</span></a>
                    </div>
                </div>
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

                </div>
            </div>
        </div>
        <OurFeatures/>
        <NewsletterForm/>
</template>

