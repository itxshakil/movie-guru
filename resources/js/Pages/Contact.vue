<script setup>
import {Head, useForm} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';
import LoadingSpinnerButton from '@/Components/LoadingSpinnerButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import NewsletterForm from '@/Components/NewsletterForm.vue';
import {inject} from "vue";

const gtag = inject("gtag");

const form = useForm({
    name: '',
    email: '',
    message: '',
    website: ''
})

const storeContact = ()=>{
    if (gtag) {
        gtag.event('contact_form_submit', {
            event_category: 'Contact',
            event_label: 'Send Message'
        });
    }
    form.post(route('contact.store'), {
        preserveScroll:true,
        preserveState: true,
        onSuccess: ()=>{
            form.reset();
        }
    })
}

defineOptions({ layout: BaseLayout })

const pageTitle = 'Contact us';
const pageDescription = 'Whether you have questions, suggestions, or just want to share your love for movies, we\'re here to listen. Reach out to us and be part of the Movie Guru community. Your feedback fuels our passion for delivering the best in cinematic content.';
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>
        <meta :content="pageDescription" head-key="description" name="description"/>
        <meta :content="pageTitle" head-key="subject" name="subject"/>
        <meta :content="pageTitle" head-key="og:title" name="og:title"/>
        <meta :content="pageDescription" head-key="og:description" name="og:description"/>
    </Head>
    <div class="pt-24 pb-8 bg-gray-100 dark:bg-gray-900 dark:text-white">
            <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
                <div class="bg-gray-100 dark:bg-gray-800 dark:text-white p-4 rounded-lg my-4 duration-500">
                    <h2 class="text-2xl font-bold">Get in touch</h2>
                    <p class="mb-4 text-lg text-gray-600 dark:text-gray-300">Catch Us If You Can – Spoiler: You Totally Can. Let's Chat!</p>
                    <Transition
                        enter-active-class="transition ease-in-out"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out"
                        leave-to-class="opacity-0"
                    >
                        <div v-if="form.recentlySuccessful" id="form-success" role="alert" aria-live="assertive" class="col-span-2 bg-green-500 text-white p-4 rounded-md shadow-md mb-4 dark:bg-green-700 dark:text-gray-100">
                            <div class="flex gap-1 flex-wrap items-center">
                                <strong class="font-bold">Success!</strong>
                                <span id="form-success-message" class="block sm:inline">
                                    Thanks for your message. We'll be in touch.
                                </span>
                            </div>
                        </div>
                    </Transition>
                    <form class="grid grid-cols-2 gap-4" id="contact-form" @submit.prevent="storeContact" method="POST">
                        <label for="website" class="sr-only">Website (Leave blank)</label>
                        <input type="text" v-model="form.website" id="website" name="website" class="hidden" />
                        <div class="col-span-2 md:col-span-1">
                            <InputLabel for="name" required value="Name" class="font-bold text-medium mb-2" />
                            <TextInput id="name" placeholder="What should we call you?" v-model="form.name" type="text" class="w-full" required autofocus autocomplete="full-name" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <InputLabel for="email" required value="Email" class="font-bold text-medium mb-2" />
                            <TextInput id="email" v-model="form.email" placeholder="Where should we contact you?" name="email" type="email" class="w-full" required autocomplete="email" maxlength="100" />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="col-span-2">
                            <InputLabel for="message" required value="Message" class="font-bold text-medium mb-2" />
                          <textarea id="message" v-model="form.message" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-800 disabled:bg-slate-400 dark:disabled:bg-gray-600 dark:read-only:bg-gray-600 dark:text-gray-300 dark:focus:border-primary-600 dark:focus:ring-primary-600 shadow-xs bg-gray-400/10 placeholder-gray-500 border-transparent transition duration-75 rounded-lg focus:bg-white focus:placeholder-gray-400 focus:border-primary-600 focus:ring-1 focus:ring-inset focus:ring-primary-600 dark:focus:text-gray-700 dark:placeholder-gray-400 focus:read-only:text-gray-200" name="message"
                                    placeholder="Your Message"
                                    required
                                    rows="6"></textarea>
                            <InputError :message="form.errors.message" />
                        </div>
                        <div class="col-span-2">
                            <LoadingSpinnerButton :is-loading="form.processing" value="Send Message" />
                        </div>
                    </form>
                    <hr class="my-4">

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                        <div class="col-span-1 md:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Connect With Us</h3>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Follow us on social media for news, updates, and special offers.</p>
                            <div class="mt-6 flex space-x-3">
                                <a class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                   href="https://www.facebook.com/moviegurufb"
                                   rel="noopener noreferrer" target="_blank">
                                    <span class="sr-only">Join our Facebook Page</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M7 10v4h3v7h4v-7h3l1 -4h-4v-2a1 1 0 0 1 1 -1h3v-4h-3a5 5 0 0 0 -5 5v2h-3" />
                                    </svg>
                                </a>
                                <a class="text-gray-400 hover:text-green-500 transition-colors"
                                   href="https://whatsapp.com/channel/0029VaLNhA9HFxOyPamoRO17" rel="noopener noreferrer"
                                   target="_blank">
                                    <span class="sr-only">Whatsapp Channel</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M3 21l1.65 -3.8a9 9 0 1 1 3.4 2.9l-5.05 .9" />
                                        <path d="M9 10a.5 .5 0 0 0 1 0v-1a.5 .5 0 0 0 -1 0v1a5 5 0 0 0 5 5h1a.5 .5 0 0 0 0 -1h-1a.5 .5 0 0 0 0 1" />
                                    </svg>
                                </a>
                                <a class="text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
                                   href="https://www.facebook.com/groups/movieguru1"
                                   rel="noopener noreferrer" target="_blank">
                                    <span class="sr-only">Facebook Group</span>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                        <path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                        <path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" />
                                        <path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                        <path d="M17 10h2a2 2 0 0 1 2 2v1" />
                                        <path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                                        <path d="M3 13v-1a2 2 0 0 1 2 -2h2" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <div class="col-span-1 md:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Why Reach Out?</h3>
                            <ul class="mt-2 text-sm text-gray-600 dark:text-gray-400 space-y-2">
                                <li class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-primary-500" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"></path>
                                    </svg>
                                    Report missing movies or broken links
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-primary-500" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"></path>
                                    </svg>
                                    Suggest new features or improvements
                                </li>
                                <li class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-primary-500" fill="none" stroke="currentColor"
                                         viewBox="0 0 24 24">
                                        <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round"
                                              stroke-width="2"></path>
                                    </svg>
                                    Business inquiries and collaborations
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <NewsletterForm/>
</template>

