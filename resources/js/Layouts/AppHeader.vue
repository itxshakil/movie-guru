<template>
    <header
        class="sticky top-0 z-50 w-full bg-white/80 dark:bg-gray-950/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800 transition-all duration-300">
        <div class="h-[env(safe-area-inset-top)] w-full bg-inherit"></div>
        <nav aria-label="Global" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <div class="flex lg:flex-1 items-center gap-4">
                <Link class="-m-1.5 p-1.5 flex items-center gap-2 group" href="/">
                    <ApplicationLogo class="transition-transform duration-300 group-hover:scale-105"/>
                    <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white hidden sm:block">Movie Guru</span>
                </Link>
            </div>

            <div class="flex items-center gap-4 lg:flex-1 lg:justify-end"></div>
    </nav>
  </header>
</template>
<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {Link} from '@inertiajs/vue3';
</script>
