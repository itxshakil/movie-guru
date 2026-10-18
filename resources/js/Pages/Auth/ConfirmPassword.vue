<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import UiButton from '@/Components/Ui/UiButton.vue';
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
                <InputLabel class="mb-1.5" for="password"
                            value="Password"/>
                <TextInput
                    id="password"
                    type="password"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError :message="form.errors.password" class="mt-1.5"/>
            </div>

            <UiButton block :loading="form.processing" size="lg" type="submit">
                Confirm
            </UiButton>
        </form>
    </GuestLayout>
</template>
