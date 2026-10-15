<script setup>
import {computed, onBeforeUnmount, onMounted, ref} from 'vue';
import {
    buildTrailerEmbedUrl,
    isDocumentPictureInPictureSupported,
    useTrailerPictureInPicture,
} from '@/Composables/useTrailerPictureInPicture.js';

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

const YOUTUBE_ORIGIN = 'https://www.youtube-nocookie.com';

const loaded = ref(false);
const iframe = ref(null);
const currentTime = ref(0);

const {pictureInPictureVideoId, openTrailerPictureInPicture, closeTrailerPictureInPicture} = useTrailerPictureInPicture();
const isPlayingInPictureInPicture = computed(() => pictureInPictureVideoId.value === props.videoId);

const thumbnail = `https://i.ytimg.com/vi/${props.videoId}/hqdefault.jpg`;
const embedUrl = buildTrailerEmbedUrl(props.videoId);

const play = () => {
    loaded.value = true;

    if (typeof window !== 'undefined' && window.gtag) {
        window.gtag('event', 'play_trailer', { video_id: props.videoId });
    }
};

/**
 * Subscribes to the embed's playback updates (the YouTube iframe postMessage protocol) so the
 * Picture-in-Picture window can resume where the inline player was.
 */
const listenToPlayer = () => {
    iframe.value?.contentWindow?.postMessage(JSON.stringify({event: 'listening', id: props.videoId, channel: 'widget'}), YOUTUBE_ORIGIN);
};

const handlePlayerMessage = (event) => {
    if (event.origin !== YOUTUBE_ORIGIN || event.source !== iframe.value?.contentWindow || typeof event.data !== 'string') {
        return;
    }

    try {
        const message = JSON.parse(event.data);
        if (message.event === 'infoDelivery' && typeof message.info?.currentTime === 'number') {
            currentTime.value = message.info.currentTime;
        }
    } catch (error) {
        console.error('Unreadable trailer player message', error);
    }
};

const popOut = async () => {
    try {
        await openTrailerPictureInPicture({videoId: props.videoId, title: props.title, startSeconds: currentTime.value});
        loaded.value = false;
    } catch (error) {
        console.error('Opening the trailer in picture-in-picture failed', error);
    }
};

const playInline = () => {
    closeTrailerPictureInPicture();
    play();
};

onMounted(() => window.addEventListener('message', handlePlayerMessage));
onBeforeUnmount(() => window.removeEventListener('message', handlePlayerMessage));
</script>

<template>
    <div>
        <div class="relative w-full overflow-hidden rounded-2xl bg-black shadow-lg aspect-video">
            <div
                v-if="isPlayingInPictureInPicture"
                class="absolute inset-0 flex flex-col items-center justify-center gap-3 p-4 text-center text-white"
            >
                <img
                    :src="thumbnail"
                    alt=""
                    aria-hidden="true"
                    class="absolute inset-0 h-full w-full object-cover opacity-30"
                    loading="lazy"
                />
                <p class="relative text-sm font-semibold">Playing in picture-in-picture</p>
                <button
                    type="button"
                    class="relative rounded-xl bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur-md transition hover:bg-white/25"
                    @click="playInline"
                >
                    Play here instead
                </button>
            </div>

            <button
                v-else-if="!loaded"
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
                ref="iframe"
                :src="embedUrl"
                :title="`${title} trailer`"
                class="absolute inset-0 h-full w-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen
                @load="listenToPlayer"
            ></iframe>
        </div>

        <div v-if="loaded && isDocumentPictureInPictureSupported" class="mt-2 flex justify-end">
            <button
                type="button"
                class="flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white"
                title="Keep the trailer playing while you browse"
                @click="popOut"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <rect height="14" rx="2" width="20" x="2" y="5"/>
                    <rect fill="currentColor" height="5" rx="1" stroke="none" width="8" x="12" y="12"/>
                </svg>
                Pop out while browsing
            </button>
        </div>
    </div>
</template>