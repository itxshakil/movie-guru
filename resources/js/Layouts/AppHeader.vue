<template>
  <header class="absolute inset-x-0 top-0 z-50 max-w-7xl mx-auto px-2 sm:px-6 lg:px-8">
    <nav class="flex items-center justify-between p-3 lg:px-8" aria-label="Global">
      <div class="flex lg:flex-1">
        <Link href="/" class="-m-1.5 p-1.5">
          <ApplicationLogo/>
        </Link>
      </div>
    </nav>
  </header>
</template>
<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import {Link} from '@inertiajs/vue3';

</script>
