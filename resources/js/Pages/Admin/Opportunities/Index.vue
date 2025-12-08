<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-teal-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1 flex flex-col">
      <!-- Fixed Header with increased height -->
      <div class="bg-white/105 backdrop-blur-lg border-b border-blue-200/60 px-6 py-8 fixed top-0 left-0 right-0 lg:left-64 z-40 h-28 flex items-center transition-all duration-300 sidebar-transition shadow-lg">
        <div class="flex justify-between items-center w-full">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
              <BriefcaseIcon class="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent flex items-center space-x-3">
                <span>Opportunities</span>
                <SparklesIcon class="w-7 h-7 text-amber-500" />
              </h1>
              <p class="text-slate-600 text-base mt-2">Manage potential business opportunities</p>
            </div>
          </div>
          <button 
            v-if="permissions.create"
            @click="visitCreate"
            class="bg-gradient-to-r from-blue-500 to-teal-500 hover:from-blue-600 hover:to-teal-600 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105 text-base"
          >
            <PlusIcon class="w-5 h-5" />
            <span>Create Opportunity</span>
          </button>
        </div>
      </div>

      <!-- Scrollable Content with slightly reduced top padding -->
      <div class="flex-1 overflow-auto pt-28 transition-all duration-300 sidebar-transition">
        <div class="p-6">
          <!-- Empty State -->
          <div v-if="!opportunities.data || opportunities.data.length === 0" class="bg-white rounded-2xl border border-blue-100/50 p-12 text-center shadow-lg mt-2">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-inner">
              <BriefcaseIcon class="w-10 h-10 text-blue-500" />
            </div>
            <h3 class="text-xl font-semibold text-slate-800 mb-3">No opportunities yet</h3>
            <p class="text-slate-600 mb-8 max-w-md mx-auto">Get started by creating your first business opportunity.</p>
            <button 
              v-if="permissions.create"
              @click="visitCreate"
              class="bg-gradient-to-r from-blue-500 to-teal-500 hover:from-blue-600 hover:to-teal-600 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 mx-auto shadow-lg hover:shadow-xl transform hover:scale-105"
            >
              <PlusIcon class="w-5 h-5" />
              <span>Create Your First Opportunity</span>
            </button>
          </div>

          <!-- Table with slightly reduced spacing from top -->
          <div v-else class="space-y-6 mt-4">
            <!-- Table Container -->
            <div class="bg-white rounded-2xl border border-blue-100/50 overflow-hidden shadow-lg">
              <div class="px-6 py-5 border-b border-blue-100/50 bg-gradient-to-r from-blue-50 to-teal-50/30">
                <div class="flex justify-between items-center">
                  <div>
                    <h3 class="text-xl font-bold text-slate-800">Opportunity List</h3>
                    <!-- Removed the "Showing X to Y of Z results" line -->
                  </div>
                  <div class="text-sm font-medium text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
                    {{ opportunities.data.length }} records
                  </div>
                </div>
              </div>
              
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gradient-to-r from-blue-50 to-teal-50/50">
                    <tr>
                      <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50">Customer Details</th>
                      <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50">Contact Information</th>
                      <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50">Location Details</th>
                      <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50">Map Location</th>
                      <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50">Created By</th>
                      <th class="px-6 py-4 text-left text-sm font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-blue-100/30">
                    <tr 
                      v-for="opportunity in opportunities.data" 
                      :key="opportunity.id" 
                      class="hover:bg-blue-50/50 transition-all duration-200 group bg-white"
                    >
                      <td class="px-6 py-4">
                        <div class="flex items-center">
                          <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-teal-400 rounded-lg flex items-center justify-center mr-4 shadow-sm group-hover:shadow-md transition-shadow">
                            <UserCircleIcon class="w-5 h-5 text-white" />
                          </div>
                          <div>
                            <div class="font-bold text-slate-800 text-sm">{{ opportunity.potential_customer_name }}</div>
                            <div class="text-xs text-slate-500 mt-1">{{ opportunity.remarks || 'No remarks' }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="space-y-1">
                          <div class="text-sm font-medium text-slate-800 flex items-center space-x-2">
                            <EnvelopeIcon class="w-4 h-4 text-blue-500" />
                            <span>{{ opportunity.email || 'No email' }}</span>
                          </div>
                          <div class="text-sm text-slate-500 flex items-center space-x-2">
                            <PhoneIcon class="w-4 h-4 text-teal-500" />
                            <span>{{ opportunity.phone || 'No phone' }}</span>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="space-y-1">
                          <div class="flex items-center space-x-2">
                            <BuildingLibraryIcon class="w-4 h-4 text-purple-500" />
                            <span class="text-sm font-medium text-slate-800">{{ opportunity.city?.name || 'No city' }}</span>
                          </div>
                          <div class="flex items-center space-x-2">
                            <BuildingOfficeIcon class="w-4 h-4 text-indigo-500" />
                            <span class="text-sm text-slate-600">{{ opportunity.subcity?.name || 'No subcity' }}</span>
                          </div>
                          <div v-if="opportunity.text_location" class="text-xs text-slate-500 italic flex items-start space-x-2 mt-1">
                            <MapPinIcon class="w-3 h-3 text-amber-500 mt-0.5 flex-shrink-0" />
                            <span>{{ opportunity.text_location }}</span>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="flex justify-center">
                          <a 
                            v-if="opportunity.map_location && opportunity.map_location.trim() !== ''" 
                            :href="opportunity.map_location" 
                            target="_blank" 
                            class="inline-flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg text-xs font-semibold transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105"
                          >
                            <MapPinIcon class="w-4 h-4" />
                            <span>View Map</span>
                          </a>
                          <span v-else class="text-slate-400 text-xs italic">No map link</span>
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-800">{{ opportunity.created_by?.name || 'System' }}</div>
                        <div class="text-xs text-slate-500">{{ formatDate(opportunity.created_at) }}</div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                          <!-- View Button -->
                          <button 
                            @click="visitShow(opportunity.id)"
                            class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg group relative transform hover:scale-105"
                            title="View Opportunity"
                          >
                            <EyeIcon class="w-4 h-4" />
                            <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                              View Details
                            </span>
                          </button>

                          <!-- Edit Button -->
                          <button 
                            v-if="permissions.edit"
                            @click="visitEdit(opportunity.id)"
                            class="bg-teal-500 hover:bg-teal-600 text-white p-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg group relative transform hover:scale-105"
                            title="Edit Opportunity"
                          >
                            <PencilIcon class="w-4 h-4" />
                            <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                              Edit Opportunity
                            </span>
                          </button>
                          
                          <!-- Delete Button -->
                          <button 
                            v-if="permissions.delete"
                            @click="deleteOpportunity(opportunity.id)"
                            class="bg-rose-500 hover:bg-rose-600 text-white p-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg group relative transform hover:scale-105"
                            title="Delete Opportunity"
                          >
                            <TrashIcon class="w-4 h-4" />
                            <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                              Delete Opportunity
                            </span>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <div v-if="opportunities.links && opportunities.links.length > 3" class="px-6 py-4 bg-white border-t border-blue-100/50 flex justify-between items-center">
                <div class="text-sm text-slate-700">
                  Page {{ opportunities.meta?.current_page || 1 }} of {{ opportunities.meta?.last_page || 1 }}
                </div>
                <div class="flex space-x-2">
                  <button 
                    v-for="link in opportunities.links"
                    :key="link.label"
                    :disabled="!link.url"
                    @click="visitPage(link.url)"
                    class="px-3 py-1.5 text-sm border rounded-lg transition-all duration-200"
                    :class="{
                      'bg-gradient-to-r from-blue-500 to-teal-500 text-white border-transparent shadow-md': link.active,
                      'text-slate-600 border-blue-200 hover:bg-blue-50 hover:border-blue-300 hover:shadow-sm': !link.active && link.url,
                      'text-slate-400 border-blue-100 cursor-not-allowed': !link.url
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
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  PlusIcon,
  MapPinIcon,
  PencilIcon,
  TrashIcon,
  BriefcaseIcon,
  UserCircleIcon,
  SparklesIcon,
  EyeIcon,
  EnvelopeIcon,
  PhoneIcon,
  BuildingLibraryIcon,
  BuildingOfficeIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  opportunities: {
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
  cities: {
    type: Array,
    default: () => []
  },
  subcities: {
    type: Array,
    default: () => []
  }
})

// Navigation methods
const visitCreate = () => {
  router.get('/admin/opportunities/create')
}

const visitShow = (id) => {
  router.get(`/admin/opportunities/${id}`)
}

const visitEdit = (id) => {
  router.get(`/admin/opportunities/${id}/edit`)
}

const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const deleteOpportunity = (id) => {
  if (confirm('Are you sure you want to delete this opportunity?')) {
    router.delete(`/admin/opportunities/${id}`)
  }
}
</script>

<style scoped>
.sidebar-transition {
  transition: all 0.3s ease-in-out;
}

/* When sidebar is hidden, adjust the left margin */
:deep(.sidebar-hidden) ~ .flex-1 .fixed {
  left: 0 !important;
}

:deep(.sidebar-hidden) ~ .flex-1 .pt-32 {
  margin-left: 0;
}
</style>