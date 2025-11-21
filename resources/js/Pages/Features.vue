<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Features - OmishtuJoy CRM" />

  <div class="min-h-screen flex flex-col font-sans bg-blue-50">
    <!-- Navbar -->
    <header class="fixed top-0 left-0 w-full flex justify-between items-center px-12 py-2 bg-white shadow-md z-50">
      <img src="/omislogo.jpg" alt="OmishtuJoy Logo" class="h-28 w-28 md:h-30 md:w-30 object-contain" />
      <nav class="space-x-6 text-gray-700 font-medium">
        <Link href="/" class="hover:text-blue-600 transition">Home</Link>
        <Link href="/features" class="hover:text-blue-600 transition">Features</Link>
        <Link href="/contacts" class="hover:text-blue-600 transition">Contact</Link>
      </nav>
    </header>

    <div class="h-[112px] md:h-[128px]"></div>

    <!-- Features -->
    <section class="py-20 px-6 md:px-12 max-w-6xl mx-auto grid md:grid-cols-3 gap-12">
      <div class="feature-card text-center">
        <h2 class="text-2xl font-semibold text-blue-700 mb-2">Customer Management</h2>
        <p class="text-gray-600">
          Keep track of all clients, their history, and interactions. Build stronger relationships with detailed records.
        </p>
      </div>

      <div class="feature-card text-center">
        <h2 class="text-2xl font-semibold text-blue-700 mb-2">Opportunities & Proposals</h2>
        <p class="text-gray-600">
          Efficiently create, approve, and monitor proposals. Convert opportunities into loyal customers with ease.
        </p>
      </div>

      <div class="feature-card text-center">
        <h2 class="text-2xl font-semibold text-blue-700 mb-2">Sales Efficiency</h2>
        <p class="text-gray-600">
          Streamline your sales workflow, monitor contracts, and optimize team performance to achieve your business goals.
        </p>
      </div>
    </section>

    <footer class="py-8 bg-white text-center text-gray-600 text-sm border-t border-blue-100 mt-auto">
      © {{ new Date().getFullYear() }} OmishtuJoy Tech Solutions. All rights reserved.
    </footer>
  </div>
</template>

<style scoped>
.feature-card {
  @apply p-6 rounded-2xl shadow-lg bg-white border border-gray-100 transition hover:-translate-y-2 hover:shadow-xl;
}
</style>
