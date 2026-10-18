<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import UiButton from '@/Components/Ui/UiButton.vue';
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

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <InputLabel for="password"
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

            <UiButton block :loading="form.processing" size="lg" type="submit">
                Sign in
            </UiButton>

            <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                Don't have an account?
                <Link :href="route('register')"
                      class="text-primary-600 dark:text-primary-400 font-semibold hover:underline">Create one
                </Link>
            </p>
        </form>
    </GuestLayout>
</template>
