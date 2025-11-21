<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-rose-50">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-lg border-b border-blue-100/50 px-6 py-6 shadow-sm">
          <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
              <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-rose-600 bg-clip-text text-transparent">Rejected Opportunities</h1>
                <p class="text-slate-600 mt-2">
                  View all rejected items from opportunities, potential customers, and customers
                </p>
              </div>
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
                  <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                  </svg>
                </div>
                {{ $page.props.flash.success }}
              </div>
            </div>
            <div v-if="$page.props.flash && $page.props.flash.error" class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-6 shadow-sm">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                  <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </div>
                {{ $page.props.flash.error }}
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="!rejectedOpportunities.data || rejectedOpportunities.data.length === 0" class="bg-white rounded-2xl shadow-lg border border-blue-100 p-12 text-center">
              <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-rose-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <ArchiveBoxIcon class="w-10 h-10 text-blue-600" />
              </div>
              <h3 class="text-xl font-semibold text-slate-900 mb-3">No rejected opportunities yet</h3>
              <p class="text-slate-600 mb-8 max-w-md mx-auto">Rejected items from opportunities, potential customers, and customers will appear here.</p>
            </div>

            <!-- Table -->
            <div v-else class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
              <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-rose-50">
                <h3 class="text-lg font-semibold text-slate-800">Rejected Items</h3>
                <p class="text-sm text-slate-600 mt-1">
                  Showing {{ rejectedOpportunities.meta?.from || 0 }} to {{ rejectedOpportunities.meta?.to || 0 }} of {{ rejectedOpportunities.meta?.total || 0 }} results
                </p>
              </div>
              
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-slate-50">
                    <tr>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Customer Details</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Contact Info</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Source</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Rejection Details</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    <tr 
                      v-for="item in rejectedOpportunities.data" 
                      :key="item.id" 
                      class="hover:bg-blue-50/50 transition-all duration-200"
                    >
                      <td class="px-6 py-4">
                        <div class="flex items-center">
                          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-rose-500 rounded-xl flex items-center justify-center mr-4">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                          </div>
                          <div>
                            <div class="font-semibold text-slate-900">{{ item.potential_customer_name || 'Unknown Customer' }}</div>
                            <div class="text-sm text-slate-500 mt-1">{{ item.remarks || 'No remarks' }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-900">{{ item.email || 'No email' }}</div>
                        <div class="text-sm text-slate-500">{{ item.phone || 'No phone' }}</div>
                      </td>
                      <td class="px-6 py-4">
                        <span class="px-3 py-1.5 rounded-full text-xs font-semibold border" :class="getSourceBadgeClass(item.rejected_from)">
                          {{ formatSource(item.rejected_from) }}
                        </span>
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-sm text-slate-700 max-w-xs">
                          <div class="font-medium mb-1">{{ item.reason || 'No reason provided' }}</div>
                          <div class="text-xs text-slate-500">By {{ item.created_by?.name || 'System' }}</div>
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                          <!-- View Button -->
                          <button 
                            @click="viewItem(item.id)"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                          >
                            <EyeIcon class="w-4 h-4" />
                            <span>View</span>
                          </button>

                          <!-- Revert Button -->
                          <button 
                            v-if="permissions.create"
                            @click="revertItem(item.id, item.rejected_from)"
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                          >
                            <ArrowPathIcon class="w-4 h-4" />
                            <span>Revert</span>
                          </button>
                          
                          <!-- Delete Button -->
                          <button 
                            v-if="permissions.delete"
                            @click="deleteItem(item.id)"
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                          >
                            <TrashIcon class="w-4 h-4" />
                            <span>Delete</span>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div v-if="rejectedOpportunities.links && rejectedOpportunities.links.length > 3" class="px-6 py-4 bg-white border-t border-slate-100 flex justify-between items-center">
                <div class="text-sm text-slate-700">
                  Page {{ rejectedOpportunities.meta?.current_page || 1 }} of {{ rejectedOpportunities.meta?.last_page || 1 }}
                </div>
                <div class="flex space-x-2">
                  <button 
                    v-for="link in rejectedOpportunities.links"
                    :key="link.label"
                    :disabled="!link.url"
                    @click="visitPage(link.url)"
                    class="px-3 py-1.5 text-sm border rounded-lg transition-all duration-200"
                    :class="{
                      'bg-gradient-to-r from-blue-600 to-rose-600 text-white border-transparent shadow-lg': link.active,
                      'text-slate-600 border-slate-300 hover:bg-slate-50 hover:border-slate-400': !link.active && link.url,
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
  EyeIcon,
  TrashIcon,
  ArchiveBoxIcon,
  ArrowPathIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  rejectedOpportunities: {
    type: Object,
    default: () => ({ data: [] })
  },
  permissions: {
    type: Object,
    default: () => ({})
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
const getSourceBadgeClass = (source) => {
  const classes = {
    opportunity: 'bg-purple-100 text-purple-800 border-purple-200',
    potential_customer: 'bg-blue-100 text-blue-800 border-blue-200',
    customer: 'bg-green-100 text-green-800 border-green-200'
  }
  return classes[source] || 'bg-slate-100 text-slate-800 border-slate-200'
}

const formatSource = (source) => {
  const sources = {
    opportunity: 'Opportunity',
    potential_customer: 'Potential Customer',
    customer: 'Customer'
  }
  return sources[source] || source
}

// Navigation methods
const viewItem = (id) => {
  router.get(`/admin/rejected-opportunities/${id}`)
}

const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

const revertItem = (id, source) => {
  const sourceName = formatSource(source)
  if (confirm(`Are you sure you want to revert this item? It will be moved back to ${sourceName}.`)) {
    router.post(`/admin/rejected-opportunities/${id}/revert`, {}, {
      onSuccess: () => {
        // Success handled by controller
      },
      onError: (errors) => {
        alert('Failed to revert item. Please try again.')
      }
    })
  }
}

const deleteItem = (id) => {
  if (confirm('Are you sure you want to permanently delete this rejected opportunity?')) {
    router.delete(`/admin/rejected-opportunities/${id}`, {
      onSuccess: () => {
        // Success handled by controller
      },
      onError: (errors) => {
        alert('Failed to delete item. Please try again.')
      }
    })
  }
}
</script>