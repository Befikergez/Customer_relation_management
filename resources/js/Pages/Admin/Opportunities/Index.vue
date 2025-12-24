<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-teal-50 flex">
    <!-- Sidebar - This will handle its own hamburger button like in search.vue -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content -->
    <div class="flex-1 min-h-screen w-full overflow-x-hidden">
      <!-- Header -->
      <div class="bg-white/105 backdrop-blur-lg border-b border-blue-200/60 px-4 md:px-6 py-4 md:py-6 sticky top-0 z-20 shadow-lg">
        <div class="flex items-center">
          <!-- Mobile spacing for sidebar hamburger button -->
          <div v-if="!isDesktop" class="w-12 flex-shrink-0"></div>
          
          <div class="flex-1 flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div class="flex items-center space-x-3 md:space-x-4">
              <!-- Hidden on tablet/mobile (hamburger button replaces this) -->
              <div class="hidden lg:flex w-10 h-10 md:w-12 md:h-12 bg-gradient-to-r from-blue-500 to-teal-500 rounded-lg md:rounded-xl items-center justify-center shadow-lg flex-shrink-0">
                <BriefcaseIcon class="w-5 h-5 md:w-6 md:h-6 text-white" />
              </div>
              <div class="min-w-0">
                <!-- Tablet/Mobile Title -->
                <div class="lg:hidden">
                  <h1 class="text-lg md:text-xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent flex items-center space-x-1 md:space-x-2 truncate">
                    <span class="truncate">Opportunities</span>
                    <SparklesIcon class="w-4 h-4 md:w-5 md:h-5 text-amber-500 flex-shrink-0" />
                  </h1>
                  <p class="text-slate-600 text-xs md:text-sm mt-0.5 truncate">Manage opportunities</p>
                </div>
                
                <!-- Desktop Title -->
                <div class="hidden lg:block">
                  <h1 class="text-xl md:text-2xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent flex items-center space-x-2 truncate">
                    <span>Opportunities</span>
                    <SparklesIcon class="w-5 h-5 text-amber-500" />
                  </h1>
                  <p class="text-slate-600 text-sm md:text-base mt-1 truncate">Manage potential business opportunities</p>
                </div>
              </div>
            </div>
            <button 
              v-if="permissions.create"
              @click="visitCreate"
              class="bg-gradient-to-r from-blue-500 to-teal-500 hover:from-blue-600 hover:to-teal-600 text-white px-3 py-2 md:px-4 md:py-2.5 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105 text-xs md:text-sm w-full md:w-auto min-w-max"
            >
              <PlusIcon class="w-3.5 h-3.5 md:w-4 md:h-4 flex-shrink-0" />
              <span class="truncate">Create Opportunity</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="p-3 md:p-4 lg:p-5">
        <!-- Check if data is loading or empty -->
        <div v-if="!opportunities || !opportunities.data" class="text-center py-8 md:py-10">
          <div class="inline-block animate-spin rounded-full h-10 w-10 md:h-12 md:w-12 border-4 border-blue-500 border-t-transparent"></div>
          <p class="mt-3 md:mt-4 text-slate-600 text-sm">Loading opportunities...</p>
        </div>
        
        <!-- Empty State -->
        <div v-else-if="opportunities.data.length === 0" class="bg-white rounded-xl md:rounded-2xl border border-blue-100/50 p-4 md:p-6 lg:p-8 text-center shadow-lg max-w-full overflow-hidden">
          <div class="w-12 h-12 md:w-14 md:h-14 bg-gradient-to-br from-blue-100 to-teal-100 rounded-xl flex items-center justify-center mx-auto mb-3 md:mb-4 shadow-inner">
            <BriefcaseIcon class="w-6 h-6 md:w-7 md:h-7 text-blue-500" />
          </div>
          <h3 class="text-base md:text-lg font-semibold text-slate-800 mb-1.5 md:mb-2">No opportunities yet</h3>
          <p class="text-slate-600 mb-3 md:mb-4 text-xs md:text-sm max-w-full px-2 mx-auto">Get started by creating your first business opportunity.</p>
          <button 
            v-if="permissions.create"
            @click="visitCreate"
            class="bg-gradient-to-r from-blue-500 to-teal-500 hover:from-blue-600 hover:to-teal-600 text-white px-3 py-2 md:px-4 md:py-2.5 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center space-x-2 mx-auto shadow-lg hover:shadow-xl transform hover:scale-105 text-xs md:text-sm min-w-max"
          >
            <PlusIcon class="w-3.5 h-3.5 md:w-4 md:h-4" />
            <span>Create Your First Opportunity</span>
          </button>
        </div>

        <!-- Table (when data exists) -->
        <div v-else class="space-y-3 md:space-y-4">
          <!-- Stats Summary (Mobile) -->
          <div class="md:hidden bg-white rounded-xl border border-blue-100/50 p-3 shadow-lg max-w-full overflow-hidden">
            <div class="flex items-center justify-between gap-2">
              <div class="text-xs font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full whitespace-nowrap">
                {{ opportunities.data.length }} records
              </div>
              <div class="text-xs text-slate-700 whitespace-nowrap">
                Page {{ opportunities.meta?.current_page || 1 }} of {{ opportunities.meta?.last_page || 1 }}
              </div>
            </div>
          </div>

          <!-- Table Container -->
          <div class="bg-white rounded-xl md:rounded-2xl border border-blue-100/50 overflow-hidden shadow-lg max-w-full">
            <div class="px-3 md:px-4 py-3 md:py-4 border-b border-blue-100/50 bg-gradient-to-r from-blue-50 to-teal-50/30 max-w-full overflow-hidden">
              <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-2 md:gap-0 max-w-full">
                <div class="min-w-0">
                  <h3 class="text-base md:text-lg font-bold text-slate-800 truncate">Opportunity List</h3>
                  <!-- Mobile pagination info -->
                  <div v-if="opportunities.links && opportunities.links.length > 3" class="md:hidden text-xs text-slate-600 mt-0.5">
                    Swipe to see more columns →
                  </div>
                </div>
                <div class="hidden md:block text-sm font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full whitespace-nowrap">
                  {{ opportunities.data.length }} records
                </div>
              </div>
            </div>
            
            <!-- Mobile Card View -->
            <div class="md:hidden">
              <div v-for="opportunity in opportunities.data" :key="opportunity.id" class="border-b border-blue-100/30 p-3 hover:bg-blue-50/50 transition-all duration-200 max-w-full overflow-hidden">
                <!-- Card Header -->
                <div class="flex items-start justify-between mb-2 gap-2">
                  <div class="flex items-center space-x-2 min-w-0">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-teal-400 rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                      <UserCircleIcon class="w-4 h-4 text-white" />
                    </div>
                    <div class="min-w-0">
                      <h4 class="font-bold text-slate-800 text-sm truncate">{{ opportunity.potential_customer_name }}</h4>
                      <div class="text-xs text-slate-500 mt-0.5 truncate">{{ opportunity.remarks || 'No remarks' }}</div>
                    </div>
                  </div>
                  <div class="flex flex-col items-end space-y-0.5 flex-shrink-0">
                    <div class="text-xs text-slate-600 font-medium whitespace-nowrap">Created</div>
                    <div class="text-xs text-slate-500 whitespace-nowrap">{{ formatDate(opportunity.created_at) }}</div>
                  </div>
                </div>

                <!-- Contact Info -->
                <div class="space-y-1.5 mb-2">
                  <div class="flex items-center space-x-2">
                    <EnvelopeIcon class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" />
                    <span class="text-sm text-slate-700 truncate">{{ opportunity.email || 'No email' }}</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <PhoneIcon class="w-3.5 h-3.5 text-teal-500 flex-shrink-0" />
                    <span class="text-sm text-slate-700 truncate">{{ opportunity.phone || 'No phone' }}</span>
                  </div>
                </div>

                <!-- Location Info -->
                <div class="space-y-1 mb-2">
                  <div class="flex items-center space-x-2">
                    <BuildingLibraryIcon class="w-3.5 h-3.5 text-purple-500 flex-shrink-0" />
                    <span class="text-sm font-medium text-slate-800 truncate">{{ opportunity.city?.name || 'No city' }}</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <BuildingOfficeIcon class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" />
                    <span class="text-sm text-slate-600 truncate">{{ opportunity.subcity?.name || 'No subcity' }}</span>
                  </div>
                  <div v-if="opportunity.text_location" class="flex items-start space-x-1.5 text-xs text-slate-500 italic">
                    <MapPinIcon class="w-3 h-3 text-amber-500 mt-0.5 flex-shrink-0" />
                    <span class="truncate">{{ opportunity.text_location }}</span>
                  </div>
                </div>

                <!-- Map Link -->
                <div class="mb-3">
                  <a 
                    v-if="opportunity.map_location && opportunity.map_location.trim() !== ''" 
                    :href="opportunity.map_location" 
                    target="_blank" 
                    class="inline-flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 shadow-md hover:shadow-lg w-full justify-center truncate"
                  >
                    <MapPinIcon class="w-3.5 h-3.5" />
                    <span>View Map</span>
                  </a>
                  <span v-else class="text-slate-400 text-xs italic block text-center truncate">No map link</span>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                  <div class="text-xs text-slate-600 font-medium truncate max-w-[120px]">
                    Created by: {{ opportunity.created_by?.name || 'System' }}
                  </div>
                  <div class="flex items-center space-x-1.5 flex-shrink-0">
                    <button 
                      @click="visitShow(opportunity.id)"
                      class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded-lg text-xs font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[28px] h-7"
                      title="View"
                    >
                      <EyeIcon class="w-3 h-3" />
                    </button>
                    <button 
                      v-if="permissions.edit"
                      @click="visitEdit(opportunity.id)"
                      class="bg-teal-500 hover:bg-teal-600 text-white p-1 rounded-lg text-xs font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[28px] h-7"
                      title="Edit"
                    >
                      <PencilIcon class="w-3 h-3" />
                    </button>
                    <button 
                      v-if="permissions.delete"
                      @click="deleteOpportunity(opportunity.id)"
                      class="bg-rose-500 hover:bg-rose-600 text-white p-1 rounded-lg text-xs font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[28px] h-7"
                      title="Delete"
                    >
                      <TrashIcon class="w-3 h-3" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Desktop & Tablet Table View -->
            <div class="hidden md:block w-full overflow-x-auto">
              <div class="min-w-full inline-block align-middle">
                <table class="min-w-full divide-y divide-blue-100/30">
                  <thead class="bg-gradient-to-r from-blue-50 to-teal-50/50">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50 whitespace-nowrap">Customer Details</th>
                      <th class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50 whitespace-nowrap">Contact Information</th>
                      <th class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50 whitespace-nowrap">Location Details</th>
                      <th class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50 whitespace-nowrap">Map Location</th>
                      <th class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50 whitespace-nowrap">Created By</th>
                      <th class="px-4 py-3 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-blue-100/50 whitespace-nowrap">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-blue-100/30">
                    <tr 
                      v-for="opportunity in opportunities.data" 
                      :key="opportunity.id" 
                      class="hover:bg-blue-50/50 transition-all duration-200 group bg-white"
                    >
                      <td class="px-4 py-3">
                        <div class="flex items-center">
                          <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-teal-400 rounded-lg flex items-center justify-center mr-3 shadow-sm group-hover:shadow-md transition-shadow flex-shrink-0">
                            <UserCircleIcon class="w-4 h-4 text-white" />
                          </div>
                          <div class="min-w-0">
                            <div class="font-bold text-slate-800 text-sm truncate">{{ opportunity.potential_customer_name }}</div>
                            <div class="text-xs text-slate-500 mt-0.5 truncate">{{ opportunity.remarks || 'No remarks' }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <div class="space-y-1">
                          <div class="text-sm font-medium text-slate-800 flex items-center space-x-2">
                            <EnvelopeIcon class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" />
                            <span class="truncate">{{ opportunity.email || 'No email' }}</span>
                          </div>
                          <div class="text-sm text-slate-500 flex items-center space-x-2">
                            <PhoneIcon class="w-3.5 h-3.5 text-teal-500 flex-shrink-0" />
                            <span class="truncate">{{ opportunity.phone || 'No phone' }}</span>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <div class="space-y-1">
                          <div class="flex items-center space-x-2">
                            <BuildingLibraryIcon class="w-3.5 h-3.5 text-purple-500 flex-shrink-0" />
                            <span class="text-sm font-medium text-slate-800 truncate">{{ opportunity.city?.name || 'No city' }}</span>
                          </div>
                          <div class="flex items-center space-x-2">
                            <BuildingOfficeIcon class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" />
                            <span class="text-sm text-slate-600 truncate">{{ opportunity.subcity?.name || 'No subcity' }}</span>
                          </div>
                          <div v-if="opportunity.text_location" class="text-xs text-slate-500 italic flex items-start space-x-2 mt-1">
                            <MapPinIcon class="w-3 h-3 text-amber-500 mt-0.5 flex-shrink-0" />
                            <span class="truncate">{{ opportunity.text_location }}</span>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex justify-center">
                          <a 
                            v-if="opportunity.map_location && opportunity.map_location.trim() !== ''" 
                            :href="opportunity.map_location" 
                            target="_blank" 
                            class="inline-flex items-center space-x-2 bg-blue-500 hover:bg-blue-600 text-white px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 whitespace-nowrap"
                          >
                            <MapPinIcon class="w-3.5 h-3.5" />
                            <span>View Map</span>
                          </a>
                          <span v-else class="text-slate-400 text-xs italic whitespace-nowrap">No map link</span>
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <div class="text-sm font-medium text-slate-800 truncate">{{ opportunity.created_by?.name || 'System' }}</div>
                        <div class="text-xs text-slate-500 truncate">{{ formatDate(opportunity.created_at) }}</div>
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex items-center space-x-1.5">
                          <!-- View Button -->
                          <button 
                            @click="visitShow(opportunity.id)"
                            class="bg-blue-500 hover:bg-blue-600 text-white p-1.5 rounded-lg text-xs font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg group relative transform hover:scale-105 min-w-[32px] h-8"
                            title="View Opportunity"
                          >
                            <EyeIcon class="w-3.5 h-3.5" />
                            <span class="absolute -top-7 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                              View Details
                            </span>
                          </button>

                          <!-- Edit Button -->
                          <button 
                            v-if="permissions.edit"
                            @click="visitEdit(opportunity.id)"
                            class="bg-teal-500 hover:bg-teal-600 text-white p-1.5 rounded-lg text-xs font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg group relative transform hover:scale-105 min-w-[32px] h-8"
                            title="Edit Opportunity"
                          >
                            <PencilIcon class="w-3.5 h-3.5" />
                            <span class="absolute -top-7 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                              Edit Opportunity
                            </span>
                          </button>
                          
                          <!-- Delete Button -->
                          <button 
                            v-if="permissions.delete"
                            @click="deleteOpportunity(opportunity.id)"
                            class="bg-rose-500 hover:bg-rose-600 text-white p-1.5 rounded-lg text-xs font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg group relative transform hover:scale-105 min-w-[32px] h-8"
                            title="Delete Opportunity"
                          >
                            <TrashIcon class="w-3.5 h-3.5" />
                            <span class="absolute -top-7 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white text-xs rounded py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
                              Delete Opportunity
                            </span>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Pagination - Desktop & Tablet -->
            <div v-if="opportunities.links && opportunities.links.length > 3" class="hidden md:flex px-4 py-3 bg-white border-t border-blue-100/50 justify-between items-center gap-2">
              <div class="text-xs text-slate-700 whitespace-nowrap">
                Page {{ opportunities.meta?.current_page || 1 }} of {{ opportunities.meta?.last_page || 1 }}
              </div>
              <div class="flex flex-wrap gap-1.5 justify-end">
                <button 
                  v-for="link in opportunities.links"
                  :key="link.label"
                  :disabled="!link.url"
                  @click="visitPage(link.url)"
                  class="px-2 py-1 text-xs border rounded-lg transition-all duration-200 min-w-[32px] text-center"
                  :class="{
                    'bg-gradient-to-r from-blue-500 to-teal-500 text-white border-transparent shadow-md': link.active,
                    'text-slate-600 border-blue-200 hover:bg-blue-50 hover:border-blue-300 hover:shadow-sm': !link.active && link.url,
                    'text-slate-400 border-blue-100 cursor-not-allowed': !link.url
                  }"
                  v-html="link.label"
                ></button>
              </div>
            </div>

            <!-- Pagination - Mobile -->
            <div v-if="opportunities.links && opportunities.links.length > 3" class="md:hidden px-3 py-2 bg-white border-t border-blue-100/50">
              <div class="flex flex-wrap gap-1.5 justify-center">
                <button 
                  v-for="link in opportunities.links"
                  :key="link.label"
                  :disabled="!link.url"
                  @click="visitPage(link.url)"
                  class="px-1.5 py-1 text-xs border rounded-lg transition-all duration-200 min-w-[32px] text-center"
                  :class="{
                    'bg-gradient-to-r from-blue-500 to-teal-500 text-white border-transparent shadow-md': link.active,
                    'text-slate-600 border-blue-200 hover:bg-blue-50 hover:border-blue-300 hover:shadow-sm': !link.active && link.url,
                    'text-slate-400 border-blue-100 cursor-not-allowed': !link.url
                  }"
                  v-html="link.label === 'Next &raquo;' ? 'Next' : link.label === '&laquo; Previous' ? 'Prev' : link.label"
                ></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
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

// Reactive state
const windowWidth = ref(0)

// Check screen size
const checkScreenSize = () => {
  windowWidth.value = window.innerWidth
}

// Computed properties for device detection
const isDesktop = computed(() => windowWidth.value >= 1024) // 1024px and above

// Setup event listeners
onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize)
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
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
::-webkit-scrollbar-thumb {
  background: rgba(59, 130, 246, 0.5);
  border-radius: 6px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(59, 130, 246, 0.7);
}

/* Smooth transitions */
.transition-all {
  transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* Prevent horizontal scrolling */
.overflow-x-hidden {
  overflow-x: hidden;
}

/* Responsive table adjustments */
@media (max-width: 1023px) {
  .min-w-full {
    min-width: 100%;
    width: 100%;
  }
}

/* Truncate long text */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Ensure content fits */
.max-w-full {
  max-width: 100%;
}

/* Prevent overflow */
.min-w-0 {
  min-width: 0;
}
</style>