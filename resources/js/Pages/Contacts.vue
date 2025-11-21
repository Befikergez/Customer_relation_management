<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
  <Head title="Contact Us - OmishtuJoy CRM" />

  <div class="min-h-screen flex flex-col font-sans bg-blue-50">
    <header class="fixed top-0 left-0 w-full flex justify-between items-center px-12 py-2 bg-white shadow-md z-50">
      <img src="/omislogo.jpg" alt="OmishtuJoy Logo" class="h-28 w-28 md:h-30 md:w-30 object-contain" />
      <nav class="space-x-6 text-gray-700 font-medium">
        <Link href="/" class="hover:text-blue-600 transition">Home</Link>
        <Link href="/features" class="hover:text-blue-600 transition">Features</Link>
        <Link href="/contacts" class="hover:text-blue-600 transition">Contact</Link>
      </nav>
    </header>

    <section class="relative mt-[80px] py-20 px-6 md:px-12 max-w-4xl mx-auto bg-white rounded-3xl shadow-lg">
      <h1 class="text-4xl md:text-5xl font-bold text-blue-800 mb-12 text-center">Contact Us</h1>

      <div class="space-y-8 text-blue-900 text-lg md:text-xl">
        <div>
          <h2 class="text-2xl font-semibold mb-2">Our Location</h2>
          <p>Addis Ababa, Ethiopia</p>
          <p>Yeka Sub-City, Megenegna, Metebaber Building, 7th floor, Office 711A</p>
        </div>

        <div>
          <h2 class="text-2xl font-semibold mb-2">Call Us On</h2>
          <p>+251 916 396011</p>
          <p>+251 932 265791</p>
          <p>+251 939 140883</p>
        </div>

        <div>
          <h2 class="text-2xl font-semibold mb-2">Email Us</h2>
          <p>info@omishtujoy.com</p>
          <p>omishtujoy@gmail.com</p>
        </div>
      </div>
    </section>

    <footer class="py-8 bg-white text-center text-gray-600 text-sm border-t border-blue-100 mt-auto">
      © {{ new Date().getFullYear() }} OmishtuJoy Tech Solutions. All rights reserved.
    </footer>
  </div>
</template>
