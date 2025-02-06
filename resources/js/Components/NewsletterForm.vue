<script setup>
import {useForm} from "@inertiajs/vue3";

const form = useForm({
    email: "",
});

const subscribe = () => {
    form.post(route("subscribe"), {
        preserveScroll: true,
        preserveState: true,
      onSuccess: () => () => {
        form.reset();

        const broadcastChannel = new BroadcastChannel('toast-notifications');
        broadcastChannel.postMessage({
          message: 'Thanks for subscribing!',
          level: 'success',
        });
      },
    });
};
</script>
<template>
    <div
        class="bg-white dark:bg-gray-900 py-4"
    >
      <div class=" w-full max-w-7xl mx-auto rounded-lg p-2">
            <div
                class="bg-gray-100 dark:bg-gray-800 rounded-lg p-4 sm:p-6 lg:p-8 grid max-w-2xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-2"
            >
                <div class="max-w-xl lg:max-w-lg">
                    <h2
                        class="text-3xl font-bold tracking-tight dark:text-white sm:text-4xl"
                    >
                        Subscribe to our newsletter.
                    </h2>
                  <p class="mt-4 text-lg leading-8 dark:text-gray-300">
                        Join our Movie Guru community today and discover hidden
                        cinematic gems, reviews, and trends every week.
                    </p>
                    <form
                        @submit.prevent="subscribe"
                        class="mt-6 flex max-w-md gap-x-4"
                    >
                        <label for="email-address" class="sr-only"
                            >Email address</label
                        >
                        <input
                            v-model="form.email"
                            id="email-address"
                            name="email"
                            type="email"
                            autocomplete="email"
                            required
                            class="rounded min-w-0 flex-auto rounded-mddark:bg-white/5 px-3.5 py-2 dark:text-white shadow-xs ring-1 ring-inset ring-white focus:ring-2 focus:ring-inset focus:ring-primary-500 sm:text-sm sm:leading-6"
                            placeholder="Enter your email"
                        />
                        <button
                            type="submit"
                            :disabled="form.processing"
                            v-text="form.processing ? 'Subscribing...' : 'Subscribe'"
                            class="flex-none rounded-md bg-primary-500 px-3.5 py-2.5 text-sm font-semibold text-white shadow-xs hover:bg-primary-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500"
                        >
                        </button>
                    </form>

                  <p v-if="form.recentlySuccessful" class="mt-4 text-green-500 dark:text-green-400">
                    Thank you for subscribing!
                  </p>
                </div>
                <dl
                    class="grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2 lg:pt-2"
                >
                    <div class="flex flex-col items-start">
                        <div
                            class="rounded-md bg-white/5 p-2 ring-1 ring-white/10"
                        >
                            <svg
                                class="h-6 w-6 dark:text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z"
                                />
                            </svg>
                        </div>
                      <dt class="mt-4 font-semibold dark:text-white">
                            Weekly articles
                        </dt>
                      <dd class="mt-2 leading-7 dark:text-gray-400">
                            Discover cinematic wonders with our weekly movie
                            articles. From reviews to behind-the-scenes, get
                            your movie fix here.
                        </dd>
                    </div>
                    <div class="flex flex-col items-start">
                        <div
                            class="rounded-md bg-white/5 p-2 ring-1 ring-white/10"
                        >
                            <svg
                                class="h-6 w-6 dark:text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10.05 4.575a1.575 1.575 0 10-3.15 0v3m3.15-3v-1.5a1.575 1.575 0 013.15 0v1.5m-3.15 0l.075 5.925m3.075.75V4.575m0 0a1.575 1.575 0 013.15 0V15M6.9 7.575a1.575 1.575 0 10-3.15 0v8.175a6.75 6.75 0 006.75 6.75h2.018a5.25 5.25 0 003.712-1.538l1.732-1.732a5.25 5.25 0 001.538-3.712l.003-2.024a.668.668 0 01.198-.471 1.575 1.575 0 10-2.228-2.228 3.818 3.818 0 00-1.12 2.687M6.9 7.575V12m6.27 4.318A4.49 4.49 0 0116.35 15m.002 0h-.002"
                                />
                            </svg>
                        </div>
                      <dt class="mt-4 font-semibold dark:text-white">No spam</dt>
                      <dd class="mt-2 leading-7 dark:text-gray-400">
                            We value your time and privacy! Expect only valuable
                            content from Movie Guru's newsletter, never spam.
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</template>
