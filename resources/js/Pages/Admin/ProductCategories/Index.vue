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
              Product Categories
            </h1>
            <p class="text-slate-600 mt-2 flex items-center">
              <span class="w-2 h-2 bg-rose-500 rounded-full mr-2 animate-pulse"></span>
              Manage your product categories and classifications
            </p>
          </div>
          <Link
            href="/admin/product-categories/create"
            class="group bg-gradient-to-r from-blue-500 to-teal-500 hover:from-blue-600 hover:to-teal-600 text-white px-6 py-3 rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl flex items-center transform hover:-translate-y-0.5"
          >
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
              <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </div>
            New Category
          </Link>
        </div>
      </div>

      <!-- Content -->
      <div class="p-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div class="bg-white rounded-2xl p-6 border border-blue-100 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-slate-600 text-sm font-medium">Total Categories</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ categories.length }}</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
              </div>
            </div>
          </div>
          
          <div class="bg-white rounded-2xl p-6 border border-teal-100 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-slate-600 text-sm font-medium">Active Categories</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ activeCategoriesCount }}</p>
              </div>
              <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Categories Grid -->
        <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-teal-50">
            <h3 class="text-lg font-semibold text-slate-800">Product Categories</h3>
          </div>
          
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="category in categories"
                :key="category.id"
                class="group bg-gradient-to-br from-white to-blue-50 rounded-2xl p-6 border border-blue-200 hover:border-teal-300 shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
              >
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <h3 class="text-lg font-semibold text-slate-900 group-hover:text-blue-700 transition-colors">
                      {{ category.name }}
                    </h3>
                    <p class="text-slate-600 text-sm mt-1 line-clamp-2">
                      {{ category.description || 'No description provided' }}
                    </p>
                  </div>
                  <div class="flex space-x-2 ml-4">
                    <span 
                      :class="category.is_active ? 
                        'bg-teal-100 text-teal-800 border-teal-200' : 
                        'bg-rose-100 text-rose-800 border-rose-200'"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                    >
                      {{ category.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                </div>

                <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-100">
                  <div class="flex space-x-2">
                    <Link
                      :href="`/admin/product-categories/${category.id}/edit`"
                      class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                      title="Edit Category"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                    <button
                      @click="deleteCategory(category.id)"
                      class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                      title="Delete Category"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Empty State -->
            <div v-if="categories.length === 0" class="text-center py-16">
              <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-slate-900 mb-3">No categories found</h3>
              <p class="text-slate-600 mb-8 max-w-md mx-auto">Get started by creating your first product category to organize your products.</p>
              <Link
                href="/admin/product-categories/create"
                class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Your First Category
              </Link>
            </div>
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
const categories = computed(() => page.props.categories?.data || [])
const tables = computed(() => page.props.tables || [])

const activeCategoriesCount = computed(() => {
  return categories.value.filter(cat => cat.is_active).length
})

function deleteCategory(id) {
  if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
    router.delete(`/admin/product-categories/${id}`)
  }
}
</script>