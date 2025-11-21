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
              Products Management
            </h1>
            <p class="text-slate-600 mt-2 flex items-center">
              <span class="w-2 h-2 bg-rose-500 rounded-full mr-2 animate-pulse"></span>
              Manage your product catalog and inventory
            </p>
          </div>
          <Link
            href="/admin/products/create"
            class="group bg-gradient-to-r from-blue-500 to-teal-500 hover:from-blue-600 hover:to-teal-600 text-white px-6 py-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl flex items-center transform hover:-translate-y-0.5"
          >
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </div>
            Add Product
          </Link>
        </div>
      </div>

      <!-- Content -->
      <div class="p-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div class="bg-white rounded-2xl p-6 border border-blue-100 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-slate-600 text-sm font-medium">Total Products</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ products.length }}</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-2xl p-6 border border-teal-100 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-slate-600 text-sm font-medium">Active Categories</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ categoriesCount }}</p>
              </div>
              <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl p-6 border border-rose-100 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-slate-600 text-sm font-medium">Industries</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ industriesCount }}</p>
              </div>
              <div class="w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-teal-50">
            <h3 class="text-lg font-semibold text-slate-800">Product Catalog</h3>
          </div>
          
          <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Product</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Category</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Industry</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Description</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <tr 
                v-for="product in products" 
                :key="product.id" 
                class="hover:bg-blue-50/50 transition-all duration-200 group"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                      </svg>
                    </div>
                    <div>
                      <span class="text-sm font-semibold text-slate-900">{{ product.name }}</span>
                      <p class="text-xs text-slate-500 mt-1">Created by {{ product.creator?.name }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span v-if="product.category" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800 border border-teal-200">
                    {{ product.category.name }}
                  </span>
                  <span v-else class="text-slate-400 text-sm">No category</span>
                </td>
                <td class="px-6 py-4">
                  <span v-if="product.industry" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                    {{ product.industry.name }}
                  </span>
                  <span v-else class="text-slate-400 text-sm">No industry</span>
                </td>
                <td class="px-6 py-4">
                  <p class="text-sm text-slate-600 max-w-xs truncate">{{ product.description || 'No description provided' }}</p>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex space-x-2">
                    <Link
                      :href="`/admin/products/${product.id}/edit`"
                      class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                      title="Edit Product"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                    <button
                      @click="deleteProduct(product.id)"
                      class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                      title="Delete Product"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
          
          <!-- Empty State -->
          <div v-if="products.length === 0" class="text-center py-16">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-slate-900 mb-3">No products found</h3>
            <p class="text-slate-600 mb-8 max-w-md mx-auto">Get started by creating your first product to build your catalog.</p>
            <Link
              href="/admin/products/create"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              Create Your First Product
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const products = computed(() => page.props.products?.data || [])
const tables = computed(() => page.props.tables || [])

const categoriesCount = computed(() => {
  const categories = new Set(products.value.map(p => p.category?.id).filter(Boolean))
  return categories.size
})

const industriesCount = computed(() => {
  const industries = new Set(products.value.map(p => p.industry?.id).filter(Boolean))
  return industries.size
})

function deleteProduct(id) {
  if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
    router.delete(`/admin/products/${id}`)
  }
}
</script>