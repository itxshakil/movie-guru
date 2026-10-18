<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import UiButton from '@/Components/Ui/UiButton.vue';
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
                <InputLabel class="mb-1.5" for="email"
                            value="Email"/>
                <TextInput
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-1.5"/>
            </div>

            <UiButton block :loading="form.processing" size="lg" type="submit">
                Send reset link
            </UiButton>
        </form>
    </GuestLayout>
</template>
