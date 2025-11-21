<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-teal-50 flex">
    <!-- Sidebar -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content -->
    <div class="flex-1">
      <!-- Header -->
      <div class="bg-white/90 backdrop-blur-md border-b border-blue-100 px-8 py-6 shadow-sm">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">
              Edit Product
            </h1>
            <p class="text-slate-600 mt-2 flex items-center">
              <span class="w-2 h-2 bg-rose-500 rounded-full mr-2 animate-pulse"></span>
              Update product information and categorization
            </p>
          </div>
          <Link
            href="/admin/products"
            class="group bg-gradient-to-r from-blue-500 to-teal-500 hover:from-blue-600 hover:to-teal-600 text-white px-6 py-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl flex items-center transform hover:-translate-y-0.5"
          >
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
            </div>
            Back to Products
          </Link>
        </div>
      </div>

      <!-- Content -->
      <div class="p-8">
        <ProductForm
          :product="product"
          :categories="categories"
          :industries="industries"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import ProductForm from './Form.vue'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const product = page.props.product
const categories = computed(() => page.props.categories || [])
const industries = computed(() => page.props.industries || [])
const tables = computed(() => page.props.tables || [])
</script>