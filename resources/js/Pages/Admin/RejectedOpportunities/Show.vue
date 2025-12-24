<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-rose-50 flex">
    <!-- Sidebar -->
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
              <div class="hidden lg:flex w-10 h-10 md:w-12 md:h-12 bg-gradient-to-r from-rose-500 to-red-500 rounded-lg md:rounded-xl items-center justify-center shadow-lg flex-shrink-0">
                <svg class="w-5 h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </div>
              <div class="min-w-0">
                <!-- Tablet/Mobile Title -->
                <div class="lg:hidden">
                  <h1 class="text-lg md:text-xl font-bold bg-gradient-to-r from-rose-600 to-red-600 bg-clip-text text-transparent flex items-center space-x-1 md:space-x-2 truncate">
                    <span class="truncate">Rejected Details</span>
                    <ExclamationTriangleIcon class="w-4 h-4 md:w-5 md:h-5 text-amber-500 flex-shrink-0" />
                  </h1>
                  <p class="text-slate-600 text-xs md:text-sm mt-0.5 truncate">Review rejection information</p>
                </div>
                
                <!-- Desktop Title -->
                <div class="hidden lg:block">
                  <h1 class="text-xl md:text-2xl font-bold bg-gradient-to-r from-rose-600 to-red-600 bg-clip-text text-transparent flex items-center space-x-2 truncate">
                    <span>Rejected Opportunity Details</span>
                    <ExclamationTriangleIcon class="w-5 h-5 text-amber-500" />
                  </h1>
                  <p class="text-slate-600 text-sm md:text-base mt-1 truncate">Review rejection information</p>
                </div>
              </div>
            </div>
            <button 
              @click="goBack"
              class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-2 md:px-4 md:py-2.5 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105 text-xs md:text-sm w-full md:w-auto min-w-max"
            >
              <ArrowLeftIcon class="w-3.5 h-3.5 md:w-4 md:h-4 flex-shrink-0" />
              <span class="truncate">Back to List</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="p-3 md:p-4 lg:p-5">
        <div class="max-w-full lg:max-w-6xl mx-auto">
          <!-- Loading State -->
          <div v-if="!rejected" class="bg-white rounded-xl md:rounded-2xl shadow-lg border border-rose-100 p-8 md:p-12 text-center">
            <div class="animate-spin w-12 h-12 md:w-16 md:h-16 border-4 border-rose-600 border-t-transparent rounded-full mx-auto mb-3 md:mb-4"></div>
            <p class="text-slate-600 text-sm md:text-base">Loading opportunity details...</p>
          </div>

          <!-- Main Content -->
          <div v-else class="bg-white rounded-xl md:rounded-2xl shadow-lg border border-rose-100 overflow-hidden">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-rose-600 to-red-600 text-white p-4 md:p-6 lg:p-8">
              <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                <div class="flex items-center space-x-3 md:space-x-4">
                  <div class="w-12 h-12 md:w-16 md:h-16 bg-white/20 rounded-xl md:rounded-2xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 md:w-8 md:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </div>
                  <div class="min-w-0">
                    <h2 class="text-lg md:text-2xl lg:text-3xl font-bold truncate">{{ rejected.potential_customer_name || 'Unknown Customer' }}</h2>
                    <p class="text-rose-100 mt-1 md:mt-2 text-sm md:text-lg truncate">Rejected Opportunity Record</p>
                  </div>
                </div>
                <span class="px-3 py-1.5 md:px-4 md:py-2 lg:px-6 lg:py-3 bg-white/20 rounded-full text-xs md:text-sm lg:text-base font-semibold border border-white/30 whitespace-nowrap">
                  {{ formatSource(rejected.rejected_from) }}
                </span>
              </div>
            </div>

            <!-- Content Grid -->
            <div class="p-4 md:p-6 lg:p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8">
                <!-- Contact Information -->
                <div class="space-y-4 md:space-y-6">
                  <h3 class="text-lg md:text-xl font-semibold text-slate-800 border-b border-slate-200 pb-2 md:pb-3 flex items-center">
                    <UserCircleIcon class="w-5 h-5 md:w-6 md:h-6 text-blue-600 mr-2" />
                    Contact Information
                  </h3>
                  
                  <div class="space-y-3 md:space-y-4">
                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2 flex items-center">
                        <EnvelopeIcon class="w-4 h-4 mr-2 flex-shrink-0" />
                        Email Address
                      </p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">{{ formatField(rejected.email, 'Email') }}</p>
                    </div>

                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2 flex items-center">
                        <PhoneIcon class="w-4 h-4 mr-2 flex-shrink-0" />
                        Phone Number
                      </p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">{{ formatField(rejected.phone, 'Phone') }}</p>
                    </div>

                    <!-- Text Location -->
                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2 flex items-center">
                        <DocumentTextIcon class="w-4 h-4 mr-2 flex-shrink-0" />
                        Text Location Description
                      </p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">
                        {{ formatField(rejected.text_location, 'Text Location') }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Location Information -->
                <div class="space-y-4 md:space-y-6">
                  <h3 class="text-lg md:text-xl font-semibold text-slate-800 border-b border-slate-200 pb-2 md:pb-3 flex items-center">
                    <MapPinIcon class="w-5 h-5 md:w-6 md:h-6 text-green-600 mr-2" />
                    Location Details
                  </h3>
                  
                  <div class="space-y-3 md:space-y-4">
                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2">City</p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">{{ formatField(rejected.city?.name, 'City') }}</p>
                    </div>

                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2">Subcity</p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">{{ formatField(rejected.subcity?.name, 'Subcity') }}</p>
                    </div>

                    <!-- Map Location -->
                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2">Map Location</p>
                      <div v-if="rejected.map_location && isValidUrl(rejected.map_location)">
                        <a :href="rejected.map_location" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="text-base md:text-lg font-semibold text-blue-600 hover:text-blue-800 transition-colors flex items-center truncate">
                          <GlobeAltIcon class="w-4 h-4 md:w-5 md:h-5 mr-2 flex-shrink-0" />
                          <span class="truncate">View on Map</span>
                          <ArrowTopRightOnSquareIcon class="w-3 h-3 md:w-4 md:h-4 ml-1 flex-shrink-0" />
                        </a>
                        <p class="text-xs text-slate-500 mt-1 truncate">
                          {{ rejected.map_location }}
                        </p>
                      </div>
                      <div v-else-if="rejected.map_location">
                        <p class="text-base md:text-lg font-semibold text-slate-900 truncate">
                          {{ formatField(rejected.map_location, 'Map Location') }}
                        </p>
                      </div>
                      <p v-else class="text-base md:text-lg font-semibold text-slate-500 truncate">Not provided</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Second Row Grid -->
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8 mt-4 md:mt-6 lg:mt-8">
                <!-- Rejection Information -->
                <div class="space-y-4 md:space-y-6">
                  <h3 class="text-lg md:text-xl font-semibold text-slate-800 border-b border-slate-200 pb-2 md:pb-3 flex items-center">
                    <ExclamationTriangleIcon class="w-5 h-5 md:w-6 md:h-6 text-rose-600 mr-2" />
                    Rejection Information
                  </h3>
                  
                  <div class="space-y-3 md:space-y-4">
                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2">Rejected By</p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">{{ formatField(rejected.created_by?.name, 'System') }}</p>
                    </div>

                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2">Rejected At</p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">{{ formatDate(rejected.created_at) }}</p>
                    </div>

                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2">Original Opportunity ID</p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">{{ formatField(rejected.opportunity_id, 'N/A') }}</p>
                    </div>

                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2">Source</p>
                      <span class="px-2 py-1 md:px-3 md:py-1.5 rounded-full text-xs md:text-sm font-semibold border whitespace-nowrap" :class="getSourceBadgeClass(rejected.rejected_from)">
                        {{ formatSource(rejected.rejected_from) }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Additional Information -->
                <div class="space-y-4 md:space-y-6">
                  <h3 class="text-lg md:text-xl font-semibold text-slate-800 border-b border-slate-200 pb-2 md:pb-3 flex items-center">
                    <InformationCircleIcon class="w-5 h-5 md:w-6 md:h-6 text-slate-600 mr-2" />
                    Additional Information
                  </h3>
                  
                  <div class="space-y-3 md:space-y-4">
                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2">Record ID</p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">{{ rejected.id }}</p>
                    </div>
                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2">Last Updated</p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">{{ formatDate(rejected.updated_at) }}</p>
                    </div>
                    <div>
                      <p class="text-xs md:text-sm text-slate-600 font-medium mb-1 md:mb-2">Original ID</p>
                      <p class="text-base md:text-lg font-semibold text-slate-900 truncate">{{ formatField(rejected.original_id, 'N/A') }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Rejection Reason -->
              <div class="mt-4 md:mt-6 lg:mt-8 pt-4 md:pt-6 lg:pt-8 border-t border-slate-200">
                <h3 class="text-lg md:text-xl font-semibold text-slate-800 mb-2 md:mb-4 flex items-center">
                  <DocumentTextIcon class="w-5 h-5 md:w-6 md:h-6 text-rose-600 mr-2" />
                  Rejection Reason
                </h3>
                <div class="bg-rose-50 rounded-lg md:rounded-xl p-3 md:p-4 lg:p-6 border-l-4 border-rose-500">
                  <p class="text-rose-900 text-base md:text-lg leading-relaxed">{{ formatField(rejected.reason, 'No reason provided') }}</p>
                </div>
              </div>

              <!-- Remarks -->
              <div v-if="rejected.remarks" class="mt-3 md:mt-4 lg:mt-6 pt-3 md:pt-4 lg:pt-6 border-t border-slate-200">
                <h3 class="text-lg md:text-xl font-semibold text-slate-800 mb-2 md:mb-4 flex items-center">
                  <ChatBubbleLeftRightIcon class="w-5 h-5 md:w-6 md:h-6 text-blue-600 mr-2" />
                  Additional Remarks
                </h3>
                <div class="bg-blue-50 rounded-lg md:rounded-xl p-3 md:p-4 lg:p-6 border-l-4 border-blue-500">
                  <p class="text-blue-900 text-base md:text-lg leading-relaxed">{{ rejected.remarks }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons Footer -->
          <div class="mt-4 md:mt-6 flex justify-end">
            <button 
              v-if="permissions.delete"
              @click="deleteOpportunity"
              class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 md:px-4 md:py-2.5 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105 text-xs md:text-sm w-full md:w-auto min-w-max"
            >
              <TrashIcon class="w-3.5 h-3.5 md:w-4 md:h-4 flex-shrink-0" />
              <span class="truncate">Delete Permanently</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  TrashIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon,
  UserCircleIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon,
  ChatBubbleLeftRightIcon,
  InformationCircleIcon,
  ArrowTopRightOnSquareIcon,
  GlobeAltIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  rejected: {
    type: Object,
    default: null
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

// Responsive detection
const windowWidth = ref(window.innerWidth)
const isDesktop = computed(() => windowWidth.value >= 1024)

// Setup event listeners
const checkScreenSize = () => {
  windowWidth.value = window.innerWidth
}

onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize)
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

const formatDate = (dateString) => {
  if (!dateString) return 'Unknown date'
  try {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch (e) {
    return 'Invalid date'
  }
}

const formatField = (value, defaultValue = 'Not provided') => {
  if (value === null || value === undefined || value === '') {
    return defaultValue
  }
  return value
}

const isValidUrl = (string) => {
  if (!string) return false
  try {
    const url = new URL(string)
    return url.protocol === 'http:' || url.protocol === 'https:'
  } catch (_) {
    return false
  }
}

// Action methods
const goBack = () => {
  router.get('/admin/rejected-opportunities')
}

const deleteOpportunity = () => {
  if (confirm('Are you sure you want to permanently delete this rejected opportunity? This action cannot be undone.')) {
    router.delete(`/admin/rejected-opportunities/${props.rejected.id}`, {
      onSuccess: () => {
        router.get('/admin/rejected-opportunities')
      },
      onError: (errors) => {
        alert('Failed to delete opportunity. Please try again.')
      }
    })
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
  background: rgba(244, 63, 94, 0.5);
  border-radius: 6px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(244, 63, 94, 0.7);
}

/* Smooth transitions */
.transition-all {
  transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* Prevent horizontal scrolling */
.overflow-x-hidden {
  overflow-x: hidden;
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