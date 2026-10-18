<script setup>
import {onBeforeUnmount, onMounted, ref} from 'vue';
import {router} from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import UiButton from '@/Components/Ui/UiButton.vue';

/**
 * Free-text mood matching with Chrome's built-in Prompt API (on-device Gemini Nano):
 * "something cosy after a long day" becomes one of the existing moods. Only rendered where
 * the model can run; everywhere else the regular mood picker is the whole experience.
 */

const props = defineProps({
    moods: {
        type: Array,
        required: true,
    },
});

const CACHE_PREFIX = 'mood-prompt:';

const isAvailable = ref(false);
// 'available' (ready), 'downloadable' or 'downloading' (multi-GB download, only on explicit submit).
const modelAvailability = ref('unavailable');
const description = ref('');
const status = ref('');
const isMatching = ref(false);
let baseSession = null;
let baseSessionPromise = null;

onMounted(async () => {
    if (!('LanguageModel' in self)) {
        return;
    }

    try {
        modelAvailability.value = await LanguageModel.availability();
        isAvailable.value = modelAvailability.value !== 'unavailable';
    } catch (error) {
        console.error('Checking on-device AI availability failed', error);
    }
});

const systemPrompt = () => [
    'You map how someone feels, or what they feel like watching, to exactly one movie mood.',
    'Available moods:',
    ...props.moods.map((mood) => `- ${mood.slug}: ${mood.label}. ${mood.description}`),
    'Answer with the slug of the single best mood.',
].join('\n');

/**
 * Creates the base session once. Each match runs in a clone of it.
 */
const prepareSession = () => {
    if (!isAvailable.value || baseSessionPromise) {
        return baseSessionPromise;
    }

    baseSessionPromise = LanguageModel.create({
        initialPrompts: [{role: 'system', content: systemPrompt()}],
        monitor(monitor) {
            monitor.addEventListener('downloadprogress', (event) => {
                status.value = `Downloading the on-device model... ${Math.round(event.loaded * 100)}%`;
            });
        },
    }).then((session) => {
        baseSession = session;
        return session;
    }).catch((error) => {
        baseSessionPromise = null;
        throw error;
    });

    return baseSessionPromise;
};

const readCachedMood = (key) => {
    try {
        return sessionStorage.getItem(CACHE_PREFIX + key);
    } catch {
        return null;
    }
};

const writeCachedMood = (key, slug) => {
    try {
        sessionStorage.setItem(CACHE_PREFIX + key, slug);
    } catch {
        // Storage unavailable (private mode): matching still works, just without the cache.
    }
};

const matchMood = async () => {
    const text = description.value.trim();
    if (!text || isMatching.value) {
        return;
    }

    const cacheKey = text.toLowerCase().replace(/\s+/g, ' ');
    const cachedSlug = readCachedMood(cacheKey);
    if (cachedSlug && props.moods.some((mood) => mood.slug === cachedSlug)) {
        router.visit(route('mood.show', cachedSlug));
        return;
    }

    isMatching.value = true;
    status.value = 'Finding your mood on this device...';
    let session = null;

    try {
        session = await (await prepareSession()).clone();
        const result = await session.prompt(text, {
            responseConstraint: {
                type: 'object',
                properties: {
                    mood: {type: 'string', enum: props.moods.map((mood) => mood.slug)},
                },
                required: ['mood'],
            },
        });

        const {mood: slug} = JSON.parse(result);
        if (!props.moods.some((mood) => mood.slug === slug)) {
            throw new Error(`Unknown mood "${slug}"`);
        }

        writeCachedMood(cacheKey, slug);
        status.value = '';
        router.visit(route('mood.show', slug));
    } catch (error) {
        console.error('Matching the mood failed', error);
        status.value = "Couldn't read that mood. Try different words or pick one above.";
    } finally {
        session?.destroy();
        isMatching.value = false;
    }
};

/**
 * Warms the model up as soon as the user shows intent, but only when it is already on the device:
 * focusing a field must never start the multi-gigabyte model download.
 */
const warmUpSession = () => {
    if (modelAvailability.value !== 'available') {
        return;
    }

    prepareSession()?.catch((error) => console.error('Preparing on-device AI failed', error));
};

onBeforeUnmount(() => baseSession?.destroy());
</script>

<template>
    <form v-if="isAvailable" class="mx-auto mb-12 max-w-2xl" @submit.prevent="matchMood">
        <InputLabel class="mb-2" for="mood-description" value="Or describe it in your own words"/>
        <div class="flex flex-col gap-2 sm:flex-row">
            <TextInput
                id="mood-description"
                v-model="description"
                :disabled="isMatching"
                autocomplete="off"
                class="flex-1"
                enterkeyhint="go"
                maxlength="200"
                placeholder="Something cosy after a long day"
                type="text"
                @focus="warmUpSession"
            />
            <UiButton :disabled="isMatching || !description.trim()" type="submit">
                {{ isMatching ? 'Matching...' : 'Find my mood' }}
            </UiButton>
        </div>
        <p aria-live="polite" class="mt-2 min-h-5 text-xs text-gray-500 dark:text-gray-400">
            {{ status || 'Runs privately on your device.' }}
        </p>
    </form>
</template>
