<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout subtitle="Enter your email and we'll send you a reset link." title="Reset password">
        <Head title="Forgot Password" />

        <div v-if="status"
             class="mb-6 text-sm text-green-600 font-medium bg-green-50 dark:bg-green-900/20 px-4 py-3 rounded-xl">
            {{ status }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5" for="email"
                            value="Email"/>
                <TextInput
                    id="email"
                    type="email"
                    class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-1.5"/>
            </div>

            <PrimaryButton
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
                class="w-full justify-center py-3 rounded-xl text-sm font-semibold"
            >
                Send reset link
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
