<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <GuestLayout subtitle="This is a secure area. Please confirm your password before continuing."
                 title="Confirm your password">
        <Head title="Confirm Password" />

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5" for="password"
                            value="Password"/>
                <TextInput
                    id="password"
                    type="password"
                    class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError :message="form.errors.password" class="mt-1.5"/>
            </div>

            <PrimaryButton
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
                class="w-full justify-center py-3 rounded-xl text-sm font-semibold"
            >
                Confirm
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
