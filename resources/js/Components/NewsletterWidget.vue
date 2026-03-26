<script setup>
import {useForm} from '@inertiajs/vue3';
import {inject} from 'vue';

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
        onSuccess: () => {
            form.reset();
            const channel = new BroadcastChannel('toast-notifications');
            channel.postMessage({message: 'Thanks for subscribing! 🎬', level: 'success'});
        },
    });
};
</script>

<template>
    <div class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900 p-6">
        <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1">
                Newsletter</p>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-snug"
                style="font-feature-settings: 'kern' 1, 'liga' 1;">
                Never miss a great film.
            </h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Weekly picks, hidden gems, and cinematic news —
                straight to your inbox.</p>
        </div>

        <div v-if="form.recentlySuccessful" class="text-sm text-green-600 dark:text-green-400 font-medium py-2">
            ✓ You're subscribed! Check your inbox.
        </div>

        <form v-else class="space-y-2.5" @submit.prevent="subscribe">
            <input
                v-model="form.first_name"
                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                placeholder="First name (optional)"
                type="text"
            />
            <input
                v-model="form.email"
                class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                placeholder="Your email address"
                required
                type="email"
            />
            <button
                :disabled="form.processing"
                class="w-full py-2.5 bg-primary-600 hover:bg-primary-500 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition-colors"
                type="submit"
            >
                {{ form.processing ? 'Subscribing…' : 'Subscribe for free' }}
            </button>
            <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
        </form>
    </div>
</template>
