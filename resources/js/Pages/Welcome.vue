<script setup>
import { Head, Link } from '@inertiajs/vue3'

defineProps({
  canLogin: Boolean,
})
</script>

<template>
  <Head title="OmishtuJoy CRM" />

  <div class="min-h-screen flex flex-col font-sans">

    <!-- Navbar -->
    <header class="fixed top-0 left-0 w-full flex justify-between items-center px-12 py-2 bg-white shadow-md z-50">
      <!-- Logo -->
      <img src="/omislogo.jpg" alt="OmishtuJoy Logo" class="h-28 w-28 md:h-30 md:w-30 object-contain" />

      <nav class="space-x-6 text-gray-700 font-medium">
        <Link href="/" class="hover:text-blue-600 transition">Home</Link>
        <Link href="/features" class="hover:text-blue-600 transition">Features</Link>
        <Link href="/contacts" class="hover:text-blue-600 transition">Contact</Link>

        <Link
          v-if="canLogin"
          href="/login"
          class="px-5 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition"
        >
          Login
        </Link>
      </nav>
    </header>

    <!-- Hero Section -->
    <section
      class="relative flex items-center h-[450px] mt-[64px] bg-cover bg-center"
      style="background-image: url('/carausol.jpg');"
    >
      <!-- Blue overlay -->
      <div class="absolute inset-0 bg-blue-500/20"></div>

      <!-- Hero text -->
      <div class="relative max-w-3xl px-12 text-left text-blue-900">
        <p class="text-2xl md:text-4xl drop-shadow-md max-w-3xl">
          Manage customers, track opportunities, and streamline your sales process effortlessly.
        </p>

        <div class="flex space-x-4 mt-6">
          <Link
            v-if="canLogin"
            href="/login"
            class="px-6 py-3 bg-blue-700 text-white text-lg font-semibold rounded-lg shadow hover:bg-blue-800 transition"
          >
            Access Dashboard
          </Link>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-12 bg-blue-50">
      <h2 class="text-4xl font-bold text-center text-blue-800 mb-16">Key Features</h2>

      <div class="grid md:grid-cols-3 gap-12 max-w-6xl mx-auto">
        <div class="feature-card text-center">
          <h2 class="text-xl font-semibold text-blue-700 mb-2">Customer Management</h2>
          <p class="text-gray-600">
            Track all clients and maintain a complete history for better relationships.
          </p>
        </div>

        <div class="feature-card text-center">
          <h2 class="text-xl font-semibold text-blue-700 mb-2">Opportunities & Proposals</h2>
          <p class="text-gray-600">
            Create, review, and approve proposals easily to convert opportunities into clients.
          </p>
        </div>

        <div class="feature-card text-center">
          <h2 class="text-xl font-semibold text-blue-700 mb-2">Sales Efficiency</h2>
          <p class="text-gray-600">
            Monitor progress, manage contracts, and streamline your sales workflow effortlessly.
          </p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 bg-white text-center text-gray-600 text-sm border-t border-blue-100">
      © {{ new Date().getFullYear() }} OmishtuJoy Tech Solutions. All rights reserved.
    </footer>
  </div>
</template>

<style scoped>
.feature-card {
  @apply p-6 rounded-2xl shadow-lg bg-white border border-gray-100 transition hover:-translate-y-2 hover:shadow-xl;
}
</style>
