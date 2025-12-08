<template>
  <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-blue-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-lg border-b border-teal-100/50 px-6 py-6 shadow-sm">
          <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
              <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-teal-600 to-blue-600 bg-clip-text text-transparent">Subcities</h1>
                <p class="text-slate-600 mt-2">Manage subcities and their parent cities</p>
              </div>
              <button 
                @click="$inertia.visit('/admin/subcities/create')"
                class="bg-gradient-to-r from-teal-600 to-blue-600 hover:from-teal-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
              >
                <PlusIcon class="w-5 h-5" />
                <span>Add New Subcity</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-7xl mx-auto">
            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl mb-6 shadow-sm">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                  <CheckCircleIcon class="w-4 h-4 text-green-600" />
                </div>
                {{ $page.props.flash.success }}
              </div>
            </div>
            <div v-if="$page.props.flash && $page.props.flash.error" class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-6 shadow-sm">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                  <XCircleIcon class="w-4 h-4 text-red-600" />
                </div>
                {{ $page.props.flash.error }}
              </div>
            </div>

            <!-- Subcities Table -->
            <div class="bg-white rounded-2xl shadow-lg border border-teal-100 overflow-hidden">
              <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-teal-50 to-blue-50">
                <h3 class="text-lg font-semibold text-slate-800">Subcity List</h3>
                <p class="text-slate-600 text-sm mt-1">
                  Showing {{ subcities.meta?.from || 0 }} to {{ subcities.meta?.to || 0 }} of {{ subcities.meta?.total || 0 }} subcities
                </p>
              </div>
              
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-slate-50">
                    <tr>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Subcity Name</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Parent City</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Created At</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    <tr 
                      v-for="subcity in subcities.data" 
                      :key="subcity.id" 
                      class="hover:bg-teal-50/50 transition-all duration-200"
                    >
                      <td class="px-6 py-4">
                        <div class="flex items-center">
                          <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-blue-500 rounded-xl flex items-center justify-center mr-4">
                            <HomeModernIcon class="w-5 h-5 text-white" />
                          </div>
                          <div>
                            <div class="font-semibold text-slate-900">{{ subcity.name }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="flex items-center">
                          <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <BuildingOfficeIcon class="w-4 h-4 text-blue-600" />
                          </div>
                          <span class="font-medium text-slate-700">{{ subcity.city?.name || 'No City' }}</span>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-sm text-slate-600">
                        {{ formatDate(subcity.created_at) }}
                      </td>
                      <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                          <button 
                            @click="$inertia.visit(`/admin/subcities/${subcity.id}/edit`)"
                            class="bg-teal-600 hover:bg-teal-700 text-white p-2 rounded-lg font-medium transition-all duration-200 flex items-center justify-center"
                            title="Edit Subcity"
                          >
                            <PencilIcon class="w-4 h-4" />
                          </button>
                          <button 
                            @click="deleteSubcity(subcity.id)"
                            class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-lg font-medium transition-all duration-200 flex items-center justify-center"
                            title="Delete Subcity"
                          >
                            <TrashIcon class="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Empty State -->
              <div v-if="!subcities.data || subcities.data.length === 0" class="text-center py-12">
                <HomeModernIcon class="w-16 h-16 text-slate-400 mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-slate-800 mb-2">No subcities found</h3>
                <p class="text-slate-600 mb-6">Get started by creating your first subcity.</p>
                <button 
                  @click="$inertia.visit('/admin/subcities/create')"
                  class="bg-gradient-to-r from-teal-600 to-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-teal-700 hover:to-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl"
                >
                  Add Your First Subcity
                </button>
              </div>

              <!-- Pagination -->
              <div v-if="subcities.links && subcities.links.length > 3" class="px-6 py-4 bg-white border-t border-slate-100 flex justify-between items-center">
                <div class="text-sm text-slate-700">
                  Page {{ subcities.meta?.current_page || 1 }} of {{ subcities.meta?.last_page || 1 }}
                </div>
                <div class="flex space-x-2">
                  <button 
                    v-for="link in subcities.links"
                    :key="link.label"
                    :disabled="!link.url"
                    @click="visitPage(link.url)"
                    class="px-3 py-1.5 text-sm border rounded-lg transition-all duration-200"
                    :class="{
                      'bg-gradient-to-r from-teal-600 to-blue-600 text-white border-transparent shadow-lg': link.active,
                      'text-slate-600 border-slate-300 hover:bg-slate-50 hover:border-teal-300': !link.active && link.url,
                      'text-slate-400 border-slate-200 cursor-not-allowed': !link.url
                    }"
                    v-html="link.label"
                  ></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  HomeModernIcon,
  BuildingOfficeIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  subcities: {
    type: Object,
    default: () => ({ data: [] })
  },
  tables: {
    type: Array,
    default: () => []
  },
  flash: {
    type: Object,
    default: () => ({})
  }
})

// Helper methods
const formatDate = (dateString) => {
  if (!dateString) return 'Unknown date'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Navigation methods
const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

// Delete subcity method
const deleteSubcity = (id) => {
  if (confirm('Are you sure you want to delete this subcity?')) {
    router.delete(`/admin/subcities/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
        // Success handled by controller
      },
      onError: () => {
        alert('Failed to delete subcity. Please try again.')
      }
    })
  }
}
</script>