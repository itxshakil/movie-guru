<script setup>
import {useForm} from '@inertiajs/vue3';
import {inject} from 'vue';
import TextInput from '@/Components/TextInput.vue';
import UiButton from '@/Components/Ui/UiButton.vue';

const gtag = inject('gtag');

const form = useForm({
    email: '',
    first_name: '',
});

const subscribe = () => {
    if (gtag) {
        gtag.event('generate_lead', {event_category: 'Newsletter', event_label: 'Widget Subscribe'});
    }
    form.post(route('subscribe'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-800 dark:bg-gray-900">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                Never miss a great film.
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Weekly picks, hidden gems, and cinematic news -
                straight to your inbox.</p>
        </div>

        <div v-if="form.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400 font-medium py-2">
            ✓ You're on the list.
        </div>

        <form v-else class="space-y-2.5" @submit.prevent="subscribe">
            <TextInput
                v-model="form.first_name"
                aria-label="First name"
                autocomplete="given-name"
                placeholder="First name (optional)"
                type="text"
            />
            <TextInput
                v-model="form.email"
                aria-label="Email address"
                autocomplete="email"
                placeholder="Your email address"
                required
                type="email"
            />
            <UiButton :loading="form.processing" block type="submit">
                {{ form.processing ? 'Subscribing...' : 'Subscribe for free' }}
            </UiButton>
            <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
        </form>
    </div>
</template>
