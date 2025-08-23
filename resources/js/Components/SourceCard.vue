<script lang="ts" setup>
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
</script>

<template>
    <li class="flex items-center gap-2 p-2 border rounded-lg shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800">
        <a
            :href="source.availability.webUrl"
            class="flex items-center gap-3 flex-2"
            rel="noopener noreferrer"
            target="_blank"
            @click="sendAnalytics(source.meta?.name, source.availability.webUrl)"
        >
            <img
                :alt="source.meta?.name + ' Logo'"
                :src="source.meta?.logo100px"
                class="h-10 w-10 rounded-md shadow"
            />

            <div>
                <p class="font-medium text-gray-900 dark:text-white flex gap-2 items-end flex-wrap">
                    {{ source.meta?.name }}

                    <template v-if="source.availability.seasons">
                        <span
                            v-if="source.availability.format"
                            :class="{
                            'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': source.availability.format === '4K',
                            'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100': source.availability.format === 'HD',
                            'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200': source.availability.format === 'SD'
                        }"
                            class="px-2 py-0.5 rounded text-xs font-medium"
                        >
                        {{ source.availability.format }}
                    </span>

                        <span v-if="source.availability.type"
                              :class="{
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100': source.availability.type === 'rent',
                            'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100': source.availability.type === 'sub',
                            'bg-green-200 text-green-900 dark:bg-green-900 dark:text-green-200': source.availability.type === 'free'
                         }"
                              class="px-2 py-0.5 rounded text-xs font-medium"
                        >
                        {{
                                source.availability.type === 'sub' ? 'Subscription' :
                                    source.availability.type === 'rent' ? 'Rent' :
                                        source.availability.type === 'free' ? 'Free' : source.availability.type
                            }}
                    </span>

                        <span v-if="source.availability.price">
                          • {{ source.availability.price }}
                    </span>
                    </template>
                </p>

                <p class="text-sm text-gray-600 dark:text-gray-400 flex flex-wrap gap-1 items-center">
                    <template v-if="!source.availability.seasons">
                        <span
                            v-if="source.availability.format"
                            :class="{
                            'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': source.availability.format === '4K',
                            'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100': source.availability.format === 'HD',
                            'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-200': source.availability.format === 'SD'
                        }"
                            class="px-2 py-0.5 rounded text-xs font-medium"
                        >
                      {{ source.availability.format }}
                    </span>

                        <span v-if="source.availability.type"
                              :class="{
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100': source.availability.type === 'rent',
                            'bg-purple-100 text-purple-800 dark:bg-purple-800 dark:text-purple-100': source.availability.type === 'sub',
                            'bg-green-200 text-green-900 dark:bg-green-900 dark:text-green-200': source.availability.type === 'free'
                         }"
                              class="px-2 py-0.5 rounded text-xs font-medium"
                        >
                        {{
                                source.availability.type === 'sub' ? 'Subscription' :
                                    source.availability.type === 'rent' ? 'Rent' :
                                        source.availability.type === 'free' ? 'Free' : source.availability.type
                            }}
                    </span>

                        <span v-if="source.availability.price">
                          • {{ source.availability.price }}
                    </span>
                    </template>

                    <span v-if="source.availability.seasons">
                          {{ source.availability.seasons }} seasons /
                          {{ source.availability.episodes }} episodes
                    </span>

                    <span class="ml-2 text-xs bg-gray-200 dark:bg-gray-700 px-2 mt-1 py-0.5 rounded">
                      {{ source.availability.region }}
                    </span>
                </p>
            </div>
        </a>
    </li>
</template>
