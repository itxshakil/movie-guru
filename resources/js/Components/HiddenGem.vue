<script setup>
import {ref, onMounted} from 'vue';
import SearchCard from '@/Components/SearchCard.vue';

const gem = ref(null);
const loading = ref(true);

onMounted(async () => {
    try {
        const res = await fetch(route('hidden-gem.show'));
        gem.value = await res.json();
    } catch {
        gem.value = null;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <section class="py-10 px-4">
        <div class="mx-auto max-w-7xl">
            <div class="mb-5 flex items-center gap-2">
                <span class="text-xl">💎</span>
                <h2 class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">Hidden Gem of the Day</h2>
            </div>

            <!-- Skeleton -->
            <div v-if="loading"
                 class="max-w-xs animate-pulse rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">
                <div class="h-64 bg-gray-200 dark:bg-gray-700"/>
                <div class="p-4 space-y-3">
                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"/>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/3"/>
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"/>
                </div>
            </div>

            <!-- Gem card -->
            <div v-else-if="gem" class="md:max-w-sm ">
                <SearchCard :movie="gem"/>
            </div>
        </div>
    </section>
</template>
