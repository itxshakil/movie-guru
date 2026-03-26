<script setup>
import {computed} from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout subtitle="Check your inbox and click the link we sent you to get started." title="Verify your email">
        <Head title="Email Verification" />

        <div v-if="verificationLinkSent"
             class="mb-6 text-sm text-green-600 font-medium bg-green-50 dark:bg-green-900/20 px-4 py-3 rounded-xl">
            A new verification link has been sent to your email address.
        </div>

        <div
            class="mb-6 p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
            Thanks for signing up! Please verify your email address by clicking the link we emailed you. Didn't receive
            it? We'll send another.
        </div>

        <form class="space-y-4" @submit.prevent="submit">
            <PrimaryButton
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
                class="w-full justify-center py-3 rounded-xl text-sm font-semibold"
            >
                Resend verification email
            </PrimaryButton>

            <Link
                :href="route('logout')"
                as="button"
                class="w-full text-center block text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                method="post"
            >
                Log out
            </Link>
        </form>
    </GuestLayout>
</template>
