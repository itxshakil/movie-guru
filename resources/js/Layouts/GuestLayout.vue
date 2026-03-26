<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: 'Welcome back',
    },
    subtitle: {
        type: String,
        default: null,
    },
});

const posters = [
    'https://image.tmdb.org/t/p/w500/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg',
    'https://image.tmdb.org/t/p/w500/3bhkrj58Vtu7enYsLLeHjnDPEYB.jpg',
    'https://image.tmdb.org/t/p/w500/rCzpDGLbOoPwLjy3OAm5NUPOTrC.jpg',
    'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg',
    'https://image.tmdb.org/t/p/w500/pB8BM7pdSp6B6Ih7QZ4DrQ3PmJK.jpg',
    'https://image.tmdb.org/t/p/w500/arw2vcBveWOVZr6pxd9XTd1TdQa.jpg',
];
</script>

<template>
    <div class="min-h-screen flex">
        <!-- Left cinematic panel -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative overflow-hidden bg-gray-950 flex-col">
            <!-- Poster mosaic grid -->
            <div class="absolute inset-0 grid grid-cols-3 gap-1 opacity-60">
                <div
                    v-for="(poster, i) in posters"
                    :key="i"
                    class="overflow-hidden"
                >
                    <img
                        :alt="`Movie poster ${i + 1}`"
                        :src="poster"
                        class="h-full w-full object-cover object-center"
                        loading="lazy"
                    />
                </div>
            </div>

            <!-- Dark gradient overlay -->
            <div class="absolute inset-0 bg-gradient-to-br from-gray-950/80 via-gray-950/60 to-primary-950/70"/>

            <!-- Brand content -->
            <div class="relative z-10 flex flex-col justify-between h-full p-12">
                <Link class="flex items-center gap-3" href="/">
                    <img alt="Movie Guru" class="h-10 w-10 rounded-xl shadow-lg" src="/icons/ios/64.png"/>
                    <span class="text-white font-bold text-xl tracking-tight">Movie Guru</span>
                </Link>

                <div class="max-w-md">
                    <blockquote class="text-white/90 text-2xl font-semibold leading-snug mb-4"
                                style="font-feature-settings: 'kern' 1, 'liga' 1;">
                        "Discover your next favourite film — curated, personalised, and always fresh."
                    </blockquote>
                    <p class="text-white/50 text-sm font-medium">Trusted by thousands of movie lovers worldwide.</p>
                </div>
            </div>
        </div>

        <!-- Right form panel -->
        <div class="flex-1 flex flex-col justify-center items-center px-6 py-12 bg-white dark:bg-gray-950 min-h-screen">
            <!-- Mobile logo -->
            <div class="lg:hidden mb-8">
                <Link class="flex items-center gap-2" href="/">
                    <img alt="Movie Guru" class="h-9 w-9 rounded-xl" src="/icons/ios/64.png"/>
                    <span class="text-gray-900 dark:text-white font-bold text-lg tracking-tight">Movie Guru</span>
                </Link>
            </div>

            <div class="w-full max-w-sm">
                <!-- Heading -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight"
                        style="font-feature-settings: 'kern' 1, 'liga' 1;">
                        {{ title }}
                    </h1>
                    <p v-if="subtitle" class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ subtitle }}
                    </p>
                </div>

                <slot/>
            </div>
        </div>
    </div>
</template>
