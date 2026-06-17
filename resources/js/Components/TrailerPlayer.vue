<script setup>
import { ref } from 'vue';

const props = defineProps({
    videoId: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        required: false,
        default: 'Trailer',
    },
});

const loaded = ref(false);

const thumbnail = `https://i.ytimg.com/vi/${props.videoId}/hqdefault.jpg`;
const embedUrl =
    `https://www.youtube-nocookie.com/embed/${props.videoId}` +
    '?autoplay=1&rel=0&modestbranding=1';

const play = () => {
    loaded.value = true;

    if (typeof window !== 'undefined' && window.gtag) {
        window.gtag('event', 'play_trailer', { video_id: props.videoId });
    }
};
</script>

<template>
    <div class="relative w-full overflow-hidden rounded-2xl bg-black shadow-lg aspect-video">
        <button
            v-if="!loaded"
            type="button"
            class="group absolute inset-0 h-full w-full cursor-pointer"
            :aria-label="`Play trailer for ${title}`"
            @click="play"
        >
            <img
                :src="thumbnail"
                :alt="`${title} trailer thumbnail`"
                loading="lazy"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105 group-hover:opacity-90"
            />
            <span class="absolute inset-0 bg-black/20"></span>
            <span
                class="absolute left-1/2 top-1/2 flex h-16 w-16 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-red-600 shadow-xl transition group-hover:scale-110"
            >
                <svg class="ml-1 h-7 w-7 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M8 5v14l11-7z" />
                </svg>
            </span>
            <span
                class="absolute bottom-3 left-4 right-4 truncate text-left text-sm font-semibold text-white drop-shadow"
            >
                ▶ Watch {{ title }} Trailer
            </span>
        </button>

        <iframe
            v-else
            :src="embedUrl"
            :title="`${title} trailer`"
            class="absolute inset-0 h-full w-full"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
            referrerpolicy="strict-origin-when-cross-origin"
            allowfullscreen
        ></iframe>
    </div>
</template>
