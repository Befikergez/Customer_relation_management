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
              <div class="hidden lg:flex w-10 h-10 md:w-12 md:h-12 bg-gradient-to-r from-blue-500 to-rose-500 rounded-lg md:rounded-xl items-center justify-center shadow-lg flex-shrink-0">
                <PencilSquareIcon class="w-5 h-5 md:w-6 md:h-6 text-white" />
              </div>
              <div class="min-w-0">
                <!-- Tablet/Mobile Title -->
                <div class="lg:hidden">
                  <h1 class="text-lg md:text-xl font-bold bg-gradient-to-r from-blue-600 to-rose-600 bg-clip-text text-transparent flex items-center space-x-1 md:space-x-2 truncate">
                    <span class="truncate">Edit Rejected</span>
                    <PencilSquareIcon class="w-4 h-4 md:w-5 md:h-5 text-amber-500 flex-shrink-0" />
                  </h1>
                  <p class="text-slate-600 text-xs md:text-sm mt-0.5 truncate">Update rejection information</p>
                </div>
                
                <!-- Desktop Title -->
                <div class="hidden lg:block">
                  <h1 class="text-xl md:text-2xl font-bold bg-gradient-to-r from-blue-600 to-rose-600 bg-clip-text text-transparent flex items-center space-x-2 truncate">
                    <span>Edit Rejected Opportunity</span>
                    <PencilSquareIcon class="w-5 h-5 text-amber-500" />
                  </h1>
                  <p class="text-slate-600 text-sm md:text-base mt-1 truncate">Update rejection information</p>
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
          <div v-if="loading" class="bg-white rounded-xl md:rounded-2xl shadow-lg border border-blue-100 p-8 md:p-12 text-center">
            <div class="animate-spin w-12 h-12 md:w-16 md:h-16 border-4 border-blue-600 border-t-transparent rounded-full mx-auto mb-3 md:mb-4"></div>
            <p class="text-slate-600 text-sm md:text-base">Loading opportunity details...</p>
          </div>

          <!-- Edit Form -->
          <div v-else class="bg-white rounded-xl md:rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-blue-600 to-rose-600 text-white p-4 md:p-6 lg:p-8">
              <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
                <div class="flex items-center space-x-3 md:space-x-4">
                  <div class="w-12 h-12 md:w-16 md:h-16 bg-white/20 rounded-xl md:rounded-2xl flex items-center justify-center flex-shrink-0">
                    <PencilSquareIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
                  </div>
                  <div class="min-w-0">
                    <h2 class="text-lg md:text-2xl lg:text-3xl font-bold truncate">{{ form.potential_customer_name || 'Unknown Customer' }}</h2>
                    <p class="text-blue-100 mt-1 md:mt-2 text-sm md:text-lg truncate">Edit Rejected Opportunity Record</p>
                  </div>
                </div>
                <span class="px-3 py-1.5 md:px-4 md:py-2 lg:px-6 lg:py-3 bg-white/20 rounded-full text-xs md:text-sm lg:text-base font-semibold border border-white/30 whitespace-nowrap">
                  {{ formatSource(rejected.rejected_from) }}
                </span>
              </div>
            </div>

            <!-- Form Content -->
            <form @submit.prevent="updateOpportunity" class="p-4 md:p-6 lg:p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 lg:gap-8">
                <!-- Contact Information -->
                <div class="space-y-4 md:space-y-6">
                  <h3 class="text-lg md:text-xl font-semibold text-slate-800 border-b border-slate-200 pb-2 md:pb-3 flex items-center">
                    <UserCircleIcon class="w-5 h-5 md:w-6 md:h-6 text-blue-600 mr-2" />
                    Contact Information
                  </h3>
                  
                  <div class="space-y-3 md:space-y-4">
                    <!-- Customer Name -->
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-1 md:mb-2 flex items-center">
                        <UserCircleIcon class="w-4 h-4 mr-2 text-blue-500 flex-shrink-0" />
                        Customer Name
                      </label>
                      <input
                        v-model="form.potential_customer_name"
                        type="text"
                        class="w-full px-3 py-2 md:px-4 md:py-3 bg-white border border-slate-300 rounded-lg md:rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                        placeholder="Enter customer name"
                      />
                      <p v-if="form.errors.potential_customer_name" class="text-red-600 text-xs mt-1">
                        {{ form.errors.potential_customer_name }}
                      </p>
                    </div>

                    <!-- Email -->
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-1 md:mb-2 flex items-center">
                        <EnvelopeIcon class="w-4 h-4 mr-2 text-rose-500 flex-shrink-0" />
                        Email Address
                      </label>
                      <input
                        v-model="form.email"
                        type="email"
                        class="w-full px-3 py-2 md:px-4 md:py-3 bg-white border border-slate-300 rounded-lg md:rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                        placeholder="Enter email address"
                      />
                      <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">
                        {{ form.errors.email }}
                      </p>
                    </div>

                    <!-- Phone -->
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-1 md:mb-2 flex items-center">
                        <PhoneIcon class="w-4 h-4 mr-2 text-green-500 flex-shrink-0" />
                        Phone Number
                      </label>
                      <input
                        v-model="form.phone"
                        type="tel"
                        class="w-full px-3 py-2 md:px-4 md:py-3 bg-white border border-slate-300 rounded-lg md:rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                        placeholder="Enter phone number"
                      />
                      <p v-if="form.errors.phone" class="text-red-600 text-xs mt-1">
                        {{ form.errors.phone }}
                      </p>
                    </div>

                    <!-- Text Location -->
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-1 md:mb-2 flex items-center">
                        <DocumentTextIcon class="w-4 h-4 mr-2 text-purple-500 flex-shrink-0" />
                        Text Location Description
                      </label>
                      <input
                        v-model="form.text_location"
                        type="text"
                        class="w-full px-3 py-2 md:px-4 md:py-3 bg-white border border-slate-300 rounded-lg md:rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                        placeholder="e.g., Near Central Park, Downtown area"
                      />
                      <p v-if="form.errors.text_location" class="text-red-600 text-xs mt-1">
                        {{ form.errors.text_location }}
                      </p>
                      <p class="text-xs text-slate-500 mt-1">
                        Descriptive location (landmarks, area description)
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
                    <!-- City -->
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-1 md:mb-2 flex items-center">
                        <MapPinIcon class="w-4 h-4 mr-2 text-blue-500 flex-shrink-0" />
                        City
                      </label>
                      <select
                        v-model="form.city_id"
                        class="w-full px-3 py-2 md:px-4 md:py-3 bg-white border border-slate-300 rounded-lg md:rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                      >
                        <option value="">Select City</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">
                          {{ city.name }}
                        </option>
                      </select>
                      <p v-if="form.errors.city_id" class="text-red-600 text-xs mt-1">
                        {{ form.errors.city_id }}
                      </p>
                    </div>

                    <!-- Subcity -->
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-1 md:mb-2 flex items-center">
                        <BuildingOfficeIcon class="w-4 h-4 mr-2 text-rose-500 flex-shrink-0" />
                        Subcity
                      </label>
                      <select
                        v-model="form.subcity_id"
                        class="w-full px-3 py-2 md:px-4 md:py-3 bg-white border border-slate-300 rounded-lg md:rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                        :disabled="!form.city_id"
                      >
                        <option value="">Select Subcity</option>
                        <option v-for="subcity in filteredSubcities" :key="subcity.id" :value="subcity.id">
                          {{ subcity.name }}
                        </option>
                      </select>
                      <p v-if="form.errors.subcity_id" class="text-red-600 text-xs mt-1">
                        {{ form.errors.subcity_id }}
                      </p>
                    </div>

                    <!-- Map Location -->
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-1 md:mb-2 flex items-center">
                        <GlobeAltIcon class="w-4 h-4 mr-2 text-green-500 flex-shrink-0" />
                        Map Location URL
                      </label>
                      <input
                        v-model="form.map_location"
                        type="url"
                        class="w-full px-3 py-2 md:px-4 md:py-3 bg-white border border-slate-300 rounded-lg md:rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                        placeholder="https://maps.google.com/?q=..."
                      />
                      <p v-if="form.errors.map_location" class="text-red-600 text-xs mt-1">
                        {{ form.errors.map_location }}
                      </p>
                      <p class="text-xs text-slate-500 mt-1">
                        Google Maps or other map service link
                      </p>
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
                    <!-- Rejection Reason -->
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-1 md:mb-2 flex items-center">
                        <DocumentTextIcon class="w-4 h-4 mr-2 text-rose-500 flex-shrink-0" />
                        Rejection Reason
                      </label>
                      <textarea
                        v-model="form.reason"
                        rows="3"
                        class="w-full px-3 py-2 md:px-4 md:py-3 bg-white border border-slate-300 rounded-lg md:rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm resize-vertical"
                        placeholder="Enter rejection reason"
                      ></textarea>
                      <p v-if="form.errors.reason" class="text-red-600 text-xs mt-1">
                        {{ form.errors.reason }}
                      </p>
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
                    <!-- Remarks -->
                    <div>
                      <label class="block text-sm font-semibold text-slate-700 mb-1 md:mb-2 flex items-center">
                        <ChatBubbleLeftRightIcon class="w-4 h-4 mr-2 text-blue-500 flex-shrink-0" />
                        Additional Remarks
                      </label>
                      <textarea
                        v-model="form.remarks"
                        rows="3"
                        class="w-full px-3 py-2 md:px-4 md:py-3 bg-white border border-slate-300 rounded-lg md:rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm resize-vertical"
                        placeholder="Enter any additional remarks"
                      ></textarea>
                      <p v-if="form.errors.remarks" class="text-red-600 text-xs mt-1">
                        {{ form.errors.remarks }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="mt-6 md:mt-8 pt-4 md:pt-6 lg:pt-8 border-t border-slate-200 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 md:space-x-4">
                <button
                  type="button"
                  @click="goBack"
                  class="px-4 py-2.5 md:px-6 md:py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-lg md:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2 w-full sm:w-auto"
                >
                  <ArrowLeftIcon class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0" />
                  <span>Cancel</span>
                </button>
                
                <button
                  type="submit"
                  :disabled="form.processing"
                  class="px-4 py-2.5 md:px-6 md:py-3 bg-gradient-to-r from-blue-600 to-rose-600 hover:from-blue-700 hover:to-rose-700 text-white rounded-lg md:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2 w-full sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <CheckIcon class="w-4 h-4 md:w-5 md:h-5 flex-shrink-0" />
                  <span>{{ form.processing ? 'Updating...' : 'Update Opportunity' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router, useForm } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon,
  UserCircleIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon,
  ChatBubbleLeftRightIcon,
  InformationCircleIcon,
  PencilSquareIcon,
  CheckIcon,
  BuildingOfficeIcon,
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

const loading = ref(false)

// Initialize form with rejected opportunity data
const form = useForm({
  potential_customer_name: props.rejected?.potential_customer_name || '',
  email: props.rejected?.email || '',
  phone: props.rejected?.phone || '',
  text_location: props.rejected?.text_location || '',
  map_location: props.rejected?.map_location || '',
  city_id: props.rejected?.city_id || '',
  subcity_id: props.rejected?.subcity_id || '',
  reason: props.rejected?.reason || '',
  remarks: props.rejected?.remarks || '',
})

// Computed properties
const filteredSubcities = computed(() => {
  if (!form.city_id) return []
  return props.subcities.filter(subcity => subcity.city_id == form.city_id)
})

// Helper methods
const formatSource = (source) => {
  const sources = {
    opportunity: 'Opportunity',
    potential_customer: 'Potential Customer',
    customer: 'Customer'
  }
  return sources[source] || source
}

// Action methods
const goBack = () => {
  router.get('/admin/rejected-opportunities')
}

const updateOpportunity = () => {
  form.put(`/admin/rejected-opportunities/${props.rejected.id}`, {
    onSuccess: () => {
      // Redirect to the show page after successful update
      router.get(`/admin/rejected-opportunities/${props.rejected.id}`)
    },
    onError: (errors) => {
      console.error('Failed to update opportunity:', errors)
    }
  })
}

// Watch for city changes to update subcities
onMounted(() => {
  if (props.rejected) {
    loading.value = false
    
    // Log data for debugging
    console.log('Edit form data loaded:', {
      text_location: props.rejected.text_location,
      map_location: props.rejected.map_location,
      city_id: props.rejected.city_id,
      subcity_id: props.rejected.subcity_id,
      email: props.rejected.email,
      phone: props.rejected.phone
    })
  }
})
</script>

<style scoped>
input:focus, select:focus, textarea:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

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