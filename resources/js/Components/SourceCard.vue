<script lang="ts" setup>
import {WATCH_TILE_LINK, WATCH_TILE_NEUTRAL, WATCH_TILE_SHAPE} from '@/Components/watchTileClasses.js';

defineProps({
    source: {
        type: Object,
        required: true,
    },
    sendAnalytics: {
        type: Function,
        required: true,
    }
});

const FORMAT_CLASSES = {
    '4K': 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100',
    HD: 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100',
    SD: 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
};

const TYPE_CLASSES = {
    rent: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100',
    sub: 'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100',
    free: 'bg-green-200 text-green-900 dark:bg-green-900 dark:text-green-200',
};

const TYPE_LABELS = {sub: 'Subscription', rent: 'Rent', free: 'Free'};

const BADGE_CLASS = 'rounded-md px-1.5 py-0.5 text-[11px] font-semibold';
</script>

<template>
    <li :class="[WATCH_TILE_SHAPE, WATCH_TILE_NEUTRAL]">
        <a
            :href="source.availability.webUrl"
            :class="WATCH_TILE_LINK"
            rel="noopener noreferrer"
            target="_blank"
            @click="sendAnalytics(source.meta?.name, source.availability.webUrl)"
        >
            <img
                :alt="source.meta?.name + ' Logo'"
                :src="source.meta?.logo100px"
                class="h-10 w-10 shrink-0 rounded-lg"
            />

            <span class="min-w-0">
                <span class="block truncate font-medium text-gray-900 dark:text-white">{{ source.meta?.name }}</span>
                <span class="mt-0.5 flex flex-wrap items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                    <span
                        v-for="format in source.availability.formats"
                        :key="format"
                        :class="[BADGE_CLASS, FORMAT_CLASSES[format]]"
                    >{{ format }}</span>
                    <span v-if="source.availability.type"
                          :class="[BADGE_CLASS, TYPE_CLASSES[source.availability.type]]"
                    >{{ TYPE_LABELS[source.availability.type] ?? source.availability.type }}</span>
                    <span v-if="source.availability.price">· {{ source.availability.price }}</span>
                    <span v-if="source.availability.seasons">
                        {{ source.availability.seasons }} seasons / {{ source.availability.episodes }} episodes
                    </span>
                </span>
            </span>
        </a>
    </li>
</template>
