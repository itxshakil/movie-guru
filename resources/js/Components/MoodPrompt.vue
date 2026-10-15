<script setup>
import {onBeforeUnmount, onMounted, ref} from 'vue';
import {router} from '@inertiajs/vue3';

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
        isAvailable.value = (await LanguageModel.availability()) !== 'unavailable';
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
 * Creates the base session once, as soon as the user shows intent (focusing the field), so the
 * model is warm by the time they submit. Each match runs in a clone of it.
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

onBeforeUnmount(() => baseSession?.destroy());
</script>

<template>
    <form v-if="isAvailable" class="mx-auto mb-12 max-w-2xl" @submit.prevent="matchMood">
        <label class="mb-2 block text-sm font-semibold text-gray-700 dark:text-gray-300" for="mood-description">
            Or describe it in your own words
        </label>
        <div class="flex flex-col gap-2 sm:flex-row">
            <input
                id="mood-description"
                v-model="description"
                :disabled="isMatching"
                autocomplete="off"
                class="flex-1 rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 transition-all focus:border-transparent focus:ring-2 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                enterkeyhint="go"
                maxlength="200"
                placeholder="Something cosy after a long day"
                type="text"
                @focus="prepareSession()?.catch((error) => console.error('Preparing on-device AI failed', error))"
            />
            <button
                :disabled="isMatching || !description.trim()"
                class="rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-primary-500 disabled:opacity-50"
                type="submit"
            >
                {{ isMatching ? 'Matching...' : 'Find my mood' }}
            </button>
        </div>
        <p aria-live="polite" class="mt-2 min-h-5 text-xs text-gray-500 dark:text-gray-400">
            {{ status || 'Runs privately on your device.' }}
        </p>
    </form>
</template>
