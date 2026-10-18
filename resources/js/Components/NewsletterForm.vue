<script setup>
import {useForm} from "@inertiajs/vue3";
import {inject, ref} from "vue";
import TextInput from "@/Components/TextInput.vue";
import UiButton from "@/Components/Ui/UiButton.vue";

const gtag = inject("gtag");

const newsletterForm = ref(null);

const form = useForm({
    email: "",
    first_name: "",
});

const subscribe = () => {
    if (gtag) {
        gtag.event('generate_lead', {
            event_category: 'Newsletter',
            event_label: 'Subscribe'
        });
    }
    form.post(route("subscribe"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
            // Clears the browser's user-validity flag so the emptied email field is not flagged red.
            newsletterForm.value?.reset();
        },
    });
};
</script>
<template>
    <section class="py-12 sm:py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 sm:p-10 dark:border-gray-800 dark:bg-gray-900">
                <div class="grid grid-cols-1 gap-x-8 gap-y-12 lg:grid-cols-2">
                    <div class="max-w-xl lg:max-w-lg">
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            Subscribe to our newsletter.
                        </h2>
                        <p class="mt-4 text-lg leading-8 text-gray-600 dark:text-gray-300">
                            Join our Movie Guru community today and discover hidden
                            cinematic gems, reviews, and trends every week.
                        </p>
                        <form
                            ref="newsletterForm"
                            class="mt-6 flex max-w-md flex-col gap-3"
                            @submit.prevent="subscribe"
                        >
                            <label class="sr-only" for="first-name">First name</label>
                            <TextInput
                                id="first-name"
                                v-model="form.first_name"
                                autocomplete="given-name"
                                name="first_name"
                                placeholder="Your first name (optional)"
                                type="text"
                            />
                            <div class="newsletter-field-group flex gap-x-4">
                                <label class="sr-only" for="email-address">Email address</label>
                                <TextInput
                                    id="email-address"
                                    v-model="form.email"
                                    autocomplete="email"
                                    class="flex-auto"
                                    name="email"
                                    placeholder="Enter your email"
                                    required
                                    type="email"
                                />
                                <UiButton :loading="form.processing" class="shrink-0" type="submit">Subscribe</UiButton>
                            </div>
                        </form>
                    </div>
                    <dl class="grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2 lg:pt-2">
                        <div class="flex flex-col items-start">
                            <div class="rounded-md bg-white p-2 ring-1 ring-gray-200 dark:bg-white/5 dark:ring-white/10">
                                <svg aria-hidden="true" class="h-6 w-6 text-gray-900 dark:text-white" fill="none"
                                     stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                            <dt class="mt-4 font-semibold text-gray-900 dark:text-white">Weekly articles</dt>
                            <dd class="mt-2 leading-7 text-gray-600 dark:text-gray-400">
                                Discover cinematic wonders with our weekly movie
                                articles. From reviews to behind-the-scenes, get
                                your movie fix here.
                            </dd>
                        </div>
                        <div class="flex flex-col items-start">
                            <div class="rounded-md bg-white p-2 ring-1 ring-gray-200 dark:bg-white/5 dark:ring-white/10">
                                <svg aria-hidden="true" class="h-6 w-6 text-gray-900 dark:text-white" fill="none"
                                     stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path
                                        d="M10.05 4.575a1.575 1.575 0 10-3.15 0v3m3.15-3v-1.5a1.575 1.575 0 013.15 0v1.5m-3.15 0l.075 5.925m3.075.75V4.575m0 0a1.575 1.575 0 013.15 0V15M6.9 7.575a1.575 1.575 0 10-3.15 0v8.175a6.75 6.75 0 006.75 6.75h2.018a5.25 5.25 0 003.712-1.538l1.732-1.732a5.25 5.25 0 001.538-3.712l.003-2.024a.668.668 0 01.198-.471 1.575 1.575 0 10-2.228-2.228 3.818 3.818 0 00-1.12 2.687M6.9 7.575V12m6.27 4.318A4.49 4.49 0 0116.35 15m.002 0h-.002"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                            <dt class="mt-4 font-semibold text-gray-900 dark:text-white">No spam</dt>
                            <dd class="mt-2 leading-7 text-gray-600 dark:text-gray-400">
                                We value your time and privacy! Expect only valuable
                                content from Movie Guru's newsletter, never spam.
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Flags the field row (not just the input) once the user has interacted
   with an invalid email, so the error is visible even though the label
   itself is visually hidden (sr-only). Purely additive - there is no
   existing JS-driven validation-class-toggling being replaced here. */
.newsletter-field-group:has(:user-invalid) input {
    box-shadow: 0 0 0 2px #f87171;
}
</style>
