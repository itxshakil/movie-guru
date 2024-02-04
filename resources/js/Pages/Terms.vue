<script setup>
import {Head, Link} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';

defineOptions({ layout: BaseLayout })

const pageTitle = 'Terms and Conditions';
const pageDescription = 'Navigate the world of Movie Guru with confidence, guided by our transparent and user-friendly Terms and Conditions. Learn about your rights, responsibilities, and the rules that govern your interaction with our platform. Enjoy the cinematic journey, knowing you\'re supported by a foundation of clear and fair terms.';
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>
        <meta :content="pageDescription" head-key="description" name="description"/>
        <meta :content="pageTitle" head-key="subject" name="subject"/>
        <meta :content="pageTitle" head-key="og:title" name="og:title"/>
        <meta :content="pageDescription" head-key="og:description" name="og:description"/>
    </Head>
        <div class="pt-24 pb-8 bg-gray-100 dark:bg-gray-900 dark:text-white max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold mb-4">Terms and Conditions</h1>
                <h3 class="text-xl font-bold mt-8 mb-4">Introduction</h3>
                <p class="mb-4">Welcome to Movie Guru, your go-to platform for exploring movies and series. Before you
                    embark on your cinematic journey, take a moment to familiarize yourself with our Terms and
                    Conditions (the "Agreement"). By utilizing our service, you signify your agreement to abide by these
                    terms.</p>
                <h3 class="text-xl font-bold mt-8 mb-4">Service Overview:</h3>
                <p class="mb-4">Movie Guru offers an open-source, free-of-charge service allowing you to search for
                    movies and series and access comprehensive details about them. Our commitment is to provide a
                    valuable resource for cinephiles worldwide. Please note that the service is provided "as is," and we
                    do not guarantee the accuracy or reliability of the information provided.</p>
                <h3 class="text-xl font-bold mt-8 mb-4">User Obligations:</h3>
                <p class="mb-4">By using Movie Guru, you agree to:</p>
                <ul class="list-disc pl-6 mb-4">
                    <li>Use the service for lawful and authorized purposes only.</li>
                    <li>Avoid distributing spam, malware, or any harmful content through our platform.</li>
                    <li>Respect third-party rights, including copyrights, trademarks, and privacy.</li>
                    <li>Avoid using the service in a manner that could harm our reputation or business.</li>
                    <li>Refrain from attempting to access the service using automated tools or techniques.</li>
                </ul>
                <h3 class="text-xl font-bold mt-8 mb-4">Link Ownership:</h3>
                <p class="mb-4">You maintain ownership of any links created through our service. However, we reserve the
                    right to remove links that violate our terms or are deemed inappropriate or harmful.</p>
                <h3 class="text-xl font-bold mt-8 mb-4">Intellectual Property:</h3>
                <p class="mb-4">All contents of Movie Guru, including logos, graphics, and software, are the property of
                    our company and are protected by copyright and other intellectual property laws. Unauthorized
                    commercial use is strictly prohibited.</p>
                <h3 class="text-xl font-bold mt-8 mb-4">Disclaimer of Warranties:</h3>
                <p class="mb-4">Movie Guru is provided "as is," and we make no warranties, express or implied, regarding
                    its performance or reliability. We are not liable for any damages resulting from your use of our
                    service.</p>
                <h3 class="text-xl font-bold mt-8 mb-4">Changes to Terms and Conditions:</h3>
                <p class="mb-4">We reserve the right to modify these Terms and Conditions. Users will be notified of any
                    changes, and it is your responsibility to review and accept the updated terms.</p>
                <h3 class="text-xl font-bold mt-8 mb-4">Governing Law:</h3>
                <p class="mb-4">This Agreement is governed by the laws of India. Any disputes will be resolved through
                    binding arbitration in accordance with the Arbitration and Conciliation Act, 1996.</p>
                <h3 class="text-xl font-bold mt-8 mb-4">Contact</h3>
                <p class="mb-4">For questions or concerns about these Terms and Conditions, please reach out to us at
                    <Link class="underline" href="/contact">Contact Us</Link>
                    .
                </p>
            </div>
        </div>
</template>

