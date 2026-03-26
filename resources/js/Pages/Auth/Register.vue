<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout subtitle="Join Movie Guru and start discovering great films." title="Create account">
        <Head title="Register" />

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <InputLabel class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5" for="name"
                            value="Name"/>
                <TextInput
                    id="name"
                    type="text"
                    class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError :message="form.errors.name" class="mt-1.5"/>
            </div>

            <div>
                <InputLabel class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5" for="email"
                            value="Email"/>
                <TextInput
                    id="email"
                    type="email"
                    class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError :message="form.errors.email" class="mt-1.5"/>
            </div>

            <div>
                <InputLabel class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5" for="password"
                            value="Password"/>
                <TextInput
                    id="password"
                    type="password"
                    class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password" class="mt-1.5"/>
            </div>

            <div>
                <InputLabel class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5" for="password_confirmation"
                            value="Confirm Password"/>
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password_confirmation" class="mt-1.5"/>
            </div>

            <PrimaryButton
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
                class="w-full justify-center py-3 rounded-xl text-sm font-semibold"
            >
                Create account
            </PrimaryButton>

            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                Already have an account?
                <Link :href="route('login')"
                      class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Sign in
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
