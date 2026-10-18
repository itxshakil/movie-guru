<script setup>
import {Head, Link} from '@inertiajs/vue3';
import BaseLayout from '@/Layouts/BaseLayout.vue';

defineOptions({layout: BaseLayout});

const pageTitle = 'Privacy Policy';
const pageDescription = 'Your privacy matters at Movie Guru. Explore our Privacy Policy to understand how we safeguard your personal information. Trust Movie Guru to prioritize your data security while you enjoy a seamless movie discovery experience. Your privacy, our commitment.';
</script>

<template>
    <Head>
        <title>{{ pageTitle }}</title>
        <meta :content="pageDescription" head-key="description" name="description"/>
        <meta :content="pageTitle" head-key="subject" name="subject"/>
        <meta :content="pageTitle" head-key="og:title" name="og:title"/>
        <meta :content="pageDescription" head-key="og:description" name="og:description"/>
    </Head>
    <div class="mx-auto max-w-3xl px-4 pb-12 pt-28 sm:px-6">
        <div class="text-base leading-relaxed text-gray-700 dark:text-gray-300">
            <h1 class="mb-4 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Privacy Policy</h1>
            <p class="mb-4">At Movie Guru, we are committed to protecting the privacy of our users. This Privacy Policy
                outlines how we handle the information collected when you use our movie detail telling service. By using
                Movie Guru, you agree to the terms described in this policy.</p>

            <h2 class="mt-10 mb-4 text-xl font-semibold text-gray-900 dark:text-white">1. Information We Collect</h2>
            <h3 class="mt-6 mb-2 text-lg font-semibold text-gray-900 dark:text-white">1.1 Links and Click Data:</h3>
            <p class="mb-4">When you use Movie Guru to get information of movie, series and related, we collect and
                store the search query and the corresponding response. We also gather data related to link clicks, such
                as the number of clicks and basic analytics to help us providing you performant website and better user
                experience.</p>

            <h3 class="mt-6 mb-2 text-lg font-semibold text-gray-900 dark:text-white">1.2 Non-Personal Information:</h3>
            <p class="mb-4">We may collect non-personal information about your usage of the Movie Guru service. This
                includes information such as your IP address, browser type, device information, and operating system.
                This information is used for analytics and to improve our service.</p>

            <h3 class="mt-6 mb-2 text-lg font-semibold text-gray-900 dark:text-white">2.1 Service Improvement:</h3>
            <p class="mb-4">We analyze non-personal information to improve the functionality, performance, and security
                of our service. This helps us enhance your overall experience while using Movie Guru.</p>

            <h3 class="mt-6 mb-2 text-lg font-semibold text-gray-900 dark:text-white">2.2 Non-Personal Information:</h3>
            <p class="mb-4">We may collect non-personal information about your usage of the Movie Guru service. This
                includes information such as your IP address, browser type, device information, and operating system.
                This information is used for analytics and to improve our service.</p>

            <h2 class="mt-10 mb-4 text-xl font-semibold text-gray-900 dark:text-white">3. Information Sharing</h2>
            <h3 class="mt-6 mb-2 text-lg font-semibold text-gray-900 dark:text-white">3.1 Third-Party Service Providers:</h3>
            <p class="mb-4">We may engage trusted third-party service providers to assist us in providing and improving
                our service. These providers may have access to the information we collect, but they are strictly bound
                by confidentiality and are not permitted to use the data for any other purpose.</p>

            <h3 class="mt-6 mb-2 text-lg font-semibold text-gray-900 dark:text-white">3.2 Legal Compliance and Protection:</h3>
            <p class="mb-4">We may disclose information if required to do so by law or in response to valid requests by
                public authorities. We may also disclose information to protect our rights, property, or safety, or the
                rights, property, or safety of others.</p>

            <h2 class="mt-10 mb-4 text-xl font-semibold text-gray-900 dark:text-white">4. Data Security</h2>
            <p class="mb-4">We take appropriate measures to safeguard the information collected through Movie Guru.
                However, please note that no method of data transmission or storage can guarantee absolute security. We
                strive to protect your information, but we cannot guarantee its complete security.</p>

            <h2 class="mt-10 mb-4 text-xl font-semibold text-gray-900 dark:text-white">5. Third-Party Links</h2>
            <p class="mb-4">Movie Guru may contain links to third-party websites or services. We are not responsible for
                the privacy practices or the content of those websites. We encourage you to review the privacy policies
                of any third-party websites you visit.</p>

            <h2 class="mt-10 mb-4 text-xl font-semibold text-gray-900 dark:text-white">6. Changes to this Privacy Policy</h2>
            <p class="mb-4">We reserve the right to modify or update this Privacy Policy at any time. Any changes will
                be effective upon posting the revised policy on our website. We encourage you to review this Privacy
                Policy periodically for any updates.</p>

            <h2 class="mt-10 mb-4 text-xl font-semibold text-gray-900 dark:text-white">7. Contact</h2>
            <p class="mb-4">If you have any questions or concerns about these Terms and Conditions, please contact us at
                <Link class="underline hover:text-primary-600 dark:hover:text-primary-400" href="/contact">Contact Us</Link>
                .
            </p>

            <p class="text-xl font-bold mt-8 mb-4"><strong>Last updated: 31-01-2024</strong></p>
            <p class="mb-4">By using Movie Guru, you acknowledge that you have read and understood this Privacy Policy
                and agree to the collection and use of information as described herein.</p>
        </div>
    </div>
</template>
