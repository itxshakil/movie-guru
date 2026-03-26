<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout subtitle="Sign in to your Movie Guru account." title="Welcome back">
        <Head title="Log in" />

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

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <InputLabel class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400" for="password"
                                value="Password"/>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium"
                    >
                        Forgot password?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    type="password"
                    class="block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />
                <InputError :message="form.errors.password" class="mt-1.5"/>
            </div>

            <div class="flex items-center gap-2">
                <Checkbox v-model:checked="form.remember" name="remember"/>
                <span class="text-sm text-gray-600 dark:text-gray-400">Remember me</span>
            </div>

            <PrimaryButton
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
                class="w-full justify-center py-3 rounded-xl text-sm font-semibold"
            >
                Sign in
            </PrimaryButton>

            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                Don't have an account?
                <Link :href="route('register')"
                      class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Create one
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
