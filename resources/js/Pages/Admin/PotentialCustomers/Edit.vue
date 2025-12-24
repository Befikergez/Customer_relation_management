<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar - With higher z-index to appear above header -->
    <Sidebar :tables="tables" />
    
    <div class="flex-1 min-w-0 flex flex-col overflow-hidden w-full">
      <!-- Fixed Header Section - Lower z-index than sidebar -->
      <div 
        id="header-section"
        class="sticky top-0 z-40 bg-white shadow-sm border-b border-gray-200 transition-transform duration-300 ease-in-out"
        :class="{
          'translate-x-64 md:translate-x-0': isSidebarOpen,
          'translate-x-0': !isSidebarOpen
        }"
      >
        <!-- Mobile/Tablet Header -->
        <div class="lg:hidden">
          <div class="flex items-center justify-between px-4 py-3">
            <!-- Mobile spacing for sidebar hamburger button -->
            <div class="w-12 flex-shrink-0"></div>
            
            <!-- Center: Title -->
            <div class="flex-1 text-center min-w-0">
              <h1 class="text-lg font-semibold text-gray-900 flex items-center justify-center gap-2 truncate">
                <span class="truncate">Edit Opportunity</span>
                <div class="w-5 h-5 bg-gradient-to-r from-blue-400/30 to-teal-400/30 rounded flex items-center justify-center flex-shrink-0">
                  <PencilSquareIcon class="w-3 h-3 text-blue-600/70" />
                </div>
              </h1>
            </div>
            
            <!-- Right spacer for balance -->
            <div class="w-12 flex-shrink-0"></div>
          </div>
        </div>

        <!-- Desktop Header (1024px and above) -->
        <div class="hidden lg:block">
          <div class="px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
              <div class="flex items-center space-x-4">
                <button 
                  @click="goBack"
                  class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                >
                  <ArrowLeftIcon class="w-4 h-4" />
                  <span>Back to Opportunities</span>
                </button>
                <div class="min-w-0">
                  <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3 truncate">
                    <span class="truncate">Edit Opportunity</span>
                    <div class="w-7 h-7 bg-gradient-to-r from-blue-400/30 to-teal-400/30 rounded-lg flex items-center justify-center flex-shrink-0">
                      <PencilSquareIcon class="w-4 h-4 text-blue-600/70" />
                    </div>
                  </h1>
                  <p class="text-gray-600 mt-1 truncate">Update existing business opportunity</p>
                </div>
              </div>
              <div class="text-sm text-gray-500 mt-2 sm:mt-0">
                Last updated: {{ formatDate(opportunity.updated_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 overflow-hidden flex flex-col">
        <!-- Mobile Back Button -->
        <div class="lg:hidden bg-white border-b border-gray-200">
          <div class="px-4 py-3">
            <button 
              @click="goBack"
              class="w-full bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
            >
              <ArrowLeftIcon class="w-4 h-4" />
              <span>Back to Opportunities</span>
            </button>
          </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto">
          <div class="p-3 md:p-4 lg:p-5">
            <!-- Flash Messages -->
            <div v-if="successMessage" class="mb-4">
              <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <CheckCircleIcon class="w-5 h-5 text-green-500" />
                    <p class="font-medium text-sm text-green-700">{{ successMessage }}</p>
                  </div>
                  <button 
                    @click="successMessage = ''" 
                    class="text-green-400 hover:text-green-600"
                  >
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>
            
            <div v-if="errorMessage" class="mb-4">
              <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <ExclamationCircleIcon class="w-5 h-5 text-red-500" />
                    <p class="font-medium text-sm text-red-700">{{ errorMessage }}</p>
                  </div>
                  <button 
                    @click="errorMessage = ''" 
                    class="text-red-400 hover:text-red-600"
                  >
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>

            <div class="max-w-4xl mx-auto">
              <div class="bg-white rounded-xl md:rounded-lg border border-gray-200 p-4 sm:p-6 lg:p-8 shadow-sm">
                <form @submit.prevent="submit">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <!-- Customer Name -->
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name *</label>
                      <input 
                        v-model="form.potential_customer_name"
                        type="text" 
                        class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200"
                        placeholder="Enter customer name"
                        required
                      >
                      <div v-if="form.errors.potential_customer_name" class="text-red-500 text-xs mt-2 flex items-center space-x-1">
                        <ExclamationCircleIcon class="w-3 h-3" />
                        <span>{{ form.errors.potential_customer_name }}</span>
                      </div>
                    </div>

                    <!-- Email -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                      <input 
                        v-model="form.email"
                        type="email" 
                        class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm transition-all duration-200"
                        placeholder="customer@example.com"
                      >
                      <div v-if="form.errors.email" class="text-red-500 text-xs mt-2 flex items-center space-x-1">
                        <ExclamationCircleIcon class="w-3 h-3" />
                        <span>{{ form.errors.email }}</span>
                      </div>
                    </div>

                    <!-- Phone -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                      <input 
                        v-model="form.phone"
                        type="tel" 
                        class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200"
                        placeholder="+1234567890"
                      >
                      <div v-if="form.errors.phone" class="text-red-500 text-xs mt-2 flex items-center space-x-1">
                        <ExclamationCircleIcon class="w-3 h-3" />
                        <span>{{ form.errors.phone }}</span>
                      </div>
                    </div>

                    <!-- City -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                      <select 
                        v-model="form.city_id"
                        class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm transition-all duration-200 appearance-none"
                      >
                        <option value="">Select City</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">
                          {{ city.name }}
                        </option>
                      </select>
                      <div v-if="form.errors.city_id" class="text-red-500 text-xs mt-2 flex items-center space-x-1">
                        <ExclamationCircleIcon class="w-3 h-3" />
                        <span>{{ form.errors.city_id }}</span>
                      </div>
                    </div>

                    <!-- Subcity -->
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">Subcity</label>
                      <select 
                        v-model="form.subcity_id"
                        class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all duration-200 appearance-none disabled:bg-gray-50 disabled:cursor-not-allowed"
                        :disabled="!form.city_id"
                      >
                        <option value="">Select Subcity</option>
                        <option v-for="subcity in availableSubcities" :key="subcity.id" :value="subcity.id">
                          {{ subcity.name }}
                        </option>
                      </select>
                      <div v-if="form.errors.subcity_id" class="text-red-500 text-xs mt-2 flex items-center space-x-1">
                        <ExclamationCircleIcon class="w-3 h-3" />
                        <span>{{ form.errors.subcity_id }}</span>
                      </div>
                    </div>

                    <!-- Text Location -->
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">Text Location Description</label>
                      <input 
                        v-model="form.text_location"
                        type="text" 
                        class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm transition-all duration-200"
                        placeholder="Enter location description"
                      >
                      <div v-if="form.errors.text_location" class="text-red-500 text-xs mt-2 flex items-center space-x-1">
                        <ExclamationCircleIcon class="w-3 h-3" />
                        <span>{{ form.errors.text_location }}</span>
                      </div>
                    </div>

                    <!-- Map Location -->
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">Map Location (URL)</label>
                      <input 
                        v-model="form.map_location"
                        type="url" 
                        class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm transition-all duration-200"
                        placeholder="https://maps.google.com/..."
                      >
                      <div v-if="form.errors.map_location" class="text-red-500 text-xs mt-2 flex items-center space-x-1">
                        <ExclamationCircleIcon class="w-3 h-3" />
                        <span>{{ form.errors.map_location }}</span>
                      </div>
                    </div>

                    <!-- Remarks -->
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                      <textarea 
                        v-model="form.remarks"
                        rows="4"
                        class="w-full px-3 sm:px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm transition-all duration-200 resize-none"
                        placeholder="Additional notes or comments about this opportunity..."
                      ></textarea>
                      <div v-if="form.errors.remarks" class="text-red-500 text-xs mt-2 flex items-center space-x-1">
                        <ExclamationCircleIcon class="w-3 h-3" />
                        <span>{{ form.errors.remarks }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Form Actions -->
                  <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 mt-6 sm:mt-8 pt-6 border-t border-gray-200">
                    <button 
                      type="button"
                      @click="goBack"
                      class="px-4 py-2.5 text-gray-600 hover:text-gray-800 font-medium text-sm transition-colors hover:bg-gray-100 rounded-lg order-2 sm:order-1 w-full sm:w-auto"
                    >
                      Cancel
                    </button>
                    <button 
                      type="submit"
                      :disabled="form.processing"
                      class="bg-gradient-to-r from-blue-500 to-teal-500 hover:from-blue-600 hover:to-teal-600 disabled:from-gray-400 disabled:to-gray-400 text-white px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 order-1 sm:order-2 w-full sm:w-auto justify-center"
                    >
                      <PencilIcon class="w-4 h-4" />
                      <span class="text-sm">{{ form.processing ? 'Updating...' : 'Update Opportunity' }}</span>
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { 
  ArrowLeftIcon, 
  PencilIcon,
  PencilSquareIcon,
  ExclamationCircleIcon,
  XMarkIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  opportunity: {
    type: Object,
    default: () => ({})
  },
  tables: {
    type: Array,
    default: () => []
  },
  permissions: {
    type: Object,
    default: () => ({})
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

// State
const isSidebarOpen = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// Form
const form = useForm({
  potential_customer_name: props.opportunity.potential_customer_name || '',
  email: props.opportunity.email || '',
  phone: props.opportunity.phone || '',
  text_location: props.opportunity.text_location || '',
  map_location: props.opportunity.map_location || '',
  remarks: props.opportunity.remarks || '',
  city_id: props.opportunity.city_id || '',
  subcity_id: props.opportunity.subcity_id || ''
})

// Computed
const availableSubcities = computed(() => {
  if (!form.city_id) return []
  return props.subcities.filter(subcity => subcity.city_id == form.city_id)
})

// Helper functions
const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

// Actions
const submit = () => {
  form.put(`/admin/opportunities/${props.opportunity.id}`, {
    preserveScroll: true,
    onSuccess: (page) => {
      successMessage.value = 'Opportunity updated successfully!'
      setTimeout(() => {
        successMessage.value = ''
      }, 3000)
      
      // Navigate back after short delay
      setTimeout(() => {
        router.get('/admin/opportunities')
      }, 1000)
    },
    onError: (errors) => {
      errorMessage.value = 'Please fix the errors in the form.'
      setTimeout(() => {
        errorMessage.value = ''
      }, 5000)
    }
  })
}

const goBack = () => {
  router.get('/admin/opportunities')
}

// Sidebar state tracking
onMounted(() => {
  const handleSidebarStateChange = (event) => {
    isSidebarOpen.value = event.detail.isOpen
  }
  
  window.addEventListener('sidebar:state-changed', handleSidebarStateChange)
  
  const checkInitialSidebarState = () => {
    const mobileMenuBtn = document.getElementById('mobile-menu-toggle')
    const sidebar = document.querySelector('aside')
    if (mobileMenuBtn && sidebar) {
      isSidebarOpen.value = sidebar.classList.contains('translate-x-0')
    }
  }
  
  setTimeout(checkInitialSidebarState, 100)
  
  return () => {
    window.removeEventListener('sidebar:state-changed', handleSidebarStateChange)
  }
})
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}
::-webkit-scrollbar-thumb {
  background: rgba(59, 130, 246, 0.3);
  border-radius: 6px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(59, 130, 246, 0.5);
}

/* Smooth transitions */
* {
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Select dropdown arrow */
select {
  background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
  background-position: right 0.5rem center;
  background-repeat: no-repeat;
  background-size: 1.5em 1.5em;
  padding-right: 2.5rem;
  -webkit-print-color-adjust: exact;
  print-color-adjust: exact;
}
</style>