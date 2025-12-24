<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Mobile Sidebar Overlay -->
    <div v-if="mobileSidebarOpen" class="fixed inset-0 flex z-40 lg:hidden">
      <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="mobileSidebarOpen = false"></div>
      <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
        <div class="absolute top-0 right-0 -mr-12 pt-2">
          <button
            @click="mobileSidebarOpen = false"
            class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
          >
            <XMarkIcon class="h-6 w-6 text-white" />
          </button>
        </div>
        <Sidebar :tables="tables" />
      </div>
    </div>

    <!-- Static sidebar for desktop -->
    <div class="hidden lg:flex lg:flex-shrink-0">
      <div class="flex flex-col w-64">
        <Sidebar :tables="tables" />
      </div>
    </div>

    <div class="flex-1 min-w-0 flex flex-col">
      <!-- Mobile top header -->
      <div class="lg:hidden">
        <div class="flex items-center justify-between bg-white shadow-sm border-b border-gray-200 px-4 py-2">
          <button
            @click="mobileSidebarOpen = true"
            class="text-gray-500 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-teal-500"
          >
            <Bars3Icon class="h-6 w-6" />
          </button>
          <div class="flex-1 text-center">
            <h1 class="text-lg font-semibold text-gray-900">Edit Customer</h1>
          </div>
          <div class="w-6"></div>
        </div>
      </div>

      <!-- Desktop Header -->
      <div class="hidden lg:block bg-white shadow-sm border-b border-gray-200">
        <div class="px-6 py-4">
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div class="flex items-center space-x-4">
              <button 
                @click="goBack"
                class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2"
              >
                <ArrowLeftIcon class="w-4 h-4" />
                <span>Back to Customer</span>
              </button>
              <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Customer</h1>
                <p class="text-gray-600 mt-1">Update customer basic information only</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <span :class="getStatusBadgeClass(customer.status)" class="px-4 py-2 rounded-lg text-sm font-semibold shadow-sm">
                {{ formatStatus(customer.status) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 p-4 lg:p-6">
        <div class="max-w-4xl mx-auto">
          <!-- Flash Messages -->
          <div v-if="flashMessage" class="mb-6">
            <div :class="flashMessageClass" class="rounded-lg p-4 shadow-sm border">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <CheckCircleIcon v-if="flashMessageType === 'success'" class="w-5 h-5 text-green-500" />
                  <ExclamationCircleIcon v-else class="w-5 h-5 text-red-500" />
                  <p class="font-medium">{{ flashMessage }}</p>
                </div>
                <button 
                  @click="clearFlashMessage" 
                  class="text-gray-400 hover:text-gray-600"
                >
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>

          <!-- Information Note -->
          <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
              <InformationCircleIcon class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" />
              <div>
                <h3 class="text-sm font-semibold text-blue-900">Note:</h3>
                <p class="text-sm text-blue-700 mt-1">
                  This page is for editing customer basic information only. 
                  Payment and commission details can be edited directly from the customer details page.
                </p>
              </div>
            </div>
          </div>

          <!-- Edit Form -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form @submit.prevent="submit">
              <div class="space-y-6">
                <!-- Basic Information Section -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3">
                      <UserCircleIcon class="w-4 h-4 text-white" />
                    </div>
                    <span>Basic Information</span>
                  </h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Customer Name *</label>
                      <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{'border-red-300': form.errors.name}"
                      />
                      <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                      <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                      <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{'border-red-300': form.errors.email}"
                      />
                      <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                      <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                      <input
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{'border-red-300': form.errors.phone}"
                      />
                      <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                    </div>

                    <div>
                      <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                      <select
                        id="status"
                        v-model="form.status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{'border-red-300': form.errors.status}"
                      >
                        <option value="draft">Draft</option>
                        <option value="contract_created">Contract Created</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                      </select>
                      <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                    </div>
                  </div>
                </div>

                <!-- Location Information -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center mr-3">
                      <MapPinIcon class="w-4 h-4 text-white" />
                    </div>
                    <span>Location Information</span>
                  </h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label for="city_id" class="block text-sm font-medium text-gray-700 mb-2">City</label>
                      <select
                        id="city_id"
                        v-model="form.city_id"
                        @change="loadSubcities"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        :class="{'border-red-300': form.errors.city_id}"
                      >
                        <option value="">Select City</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                      </select>
                      <p v-if="form.errors.city_id" class="mt-1 text-sm text-red-600">{{ form.errors.city_id }}</p>
                    </div>

                    <div>
                      <label for="subcity_id" class="block text-sm font-medium text-gray-700 mb-2">Subcity</label>
                      <select
                        id="subcity_id"
                        v-model="form.subcity_id"
                        :disabled="!form.city_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
                        :class="{'border-red-300': form.errors.subcity_id}"
                      >
                        <option value="">Select Subcity</option>
                        <option v-for="subcity in filteredSubcities" :key="subcity.id" :value="subcity.id">{{ subcity.name }}</option>
                      </select>
                      <p v-if="form.errors.subcity_id" class="mt-1 text-sm text-red-600">{{ form.errors.subcity_id }}</p>
                    </div>

                    <div class="md:col-span-2">
                      <label for="text_location" class="block text-sm font-medium text-gray-700 mb-2">Text Location Description</label>
                      <textarea
                        id="text_location"
                        v-model="form.text_location"
                        rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
                        :class="{'border-red-300': form.errors.text_location}"
                        placeholder="Enter detailed location description..."
                      ></textarea>
                      <p v-if="form.errors.text_location" class="mt-1 text-sm text-red-600">{{ form.errors.text_location }}</p>
                    </div>

                    <div class="md:col-span-2">
                      <label for="map_location" class="block text-sm font-medium text-gray-700 mb-2">Map Location URL</label>
                      <input
                        id="map_location"
                        v-model="form.map_location"
                        type="url"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        :class="{'border-red-300': form.errors.map_location}"
                        placeholder="https://maps.google.com/..."
                      />
                      <p v-if="form.errors.map_location" class="mt-1 text-sm text-red-600">{{ form.errors.map_location }}</p>
                      <p class="mt-1 text-xs text-gray-500">Enter Google Maps or other map service URL</p>
                    </div>
                  </div>
                </div>

                <!-- Remarks -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-r from-gray-600 to-gray-700 rounded-lg flex items-center justify-center mr-3">
                      <DocumentTextIcon class="w-4 h-4 text-white" />
                    </div>
                    <span>Additional Information</span>
                  </h3>
                  <div>
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                    <textarea
                      id="remarks"
                      v-model="form.remarks"
                      rows="4"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-gray-500 resize-none"
                      :class="{'border-red-300': form.errors.remarks}"
                    ></textarea>
                    <p v-if="form.errors.remarks" class="mt-1 text-sm text-red-600">{{ form.errors.remarks }}</p>
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                  <button
                    type="button"
                    @click="goBack"
                    class="px-6 py-2 text-gray-600 hover:text-gray-800 font-medium text-sm transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 disabled:from-teal-400 disabled:to-teal-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
                  >
                    <span v-if="form.processing">
                      <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Saving...
                    </span>
                    <span v-else>Save Changes</span>
                  </button>
                </div>
              </div>
            </form>
          </div>

          <!-- Loading Overlay -->
          <div v-if="loading" class="fixed inset-0 bg-gray-500 bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-lg">
              <div class="flex items-center space-x-3">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-700">Processing...</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  XMarkIcon,
  Bars3Icon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  UserCircleIcon,
  MapPinIcon,
  DocumentTextIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  customer: {
    type: Object,
    required: true
  },
  cities: {
    type: Array,
    default: () => []
  },
  subcities: {
    type: Array,
    default: () => []
  },
  tables: {
    type: Array,
    default: () => []
  },
  permissions: {
    type: Object,
    default: () => ({})
  },
  flash: {
    type: Object,
    default: () => ({})
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// State
const mobileSidebarOpen = ref(false)
const loading = ref(false)

// Flash message state
const flashMessage = ref(props.flash?.message || '')
const flashMessageType = ref(props.flash?.type || 'success')

// Form - Only basic customer information
const form = reactive({
  name: props.customer?.name || '',
  email: props.customer?.email || '',
  phone: props.customer?.phone || '',
  status: props.customer?.status || 'draft',
  city_id: props.customer?.city_id || '',
  subcity_id: props.customer?.subcity_id || '',
  text_location: props.customer?.text_location || '',
  map_location: props.customer?.map_location || '',
  remarks: props.customer?.remarks || '',
  is_basic_update: true, // IMPORTANT: This tells the backend it's a basic update
  processing: false,
  errors: {}
})

// Filtered subcities
const filteredSubcities = ref([])

// Computed
const flashMessageClass = computed(() => {
  return flashMessageType.value === 'success'
    ? 'bg-green-50 border-green-200 text-green-800'
    : 'bg-red-50 border-red-200 text-red-800'
})

// Helper functions
function getStatusBadgeClass(status) {
  const map = {
    draft: 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 shadow-sm',
    contract_created: 'bg-gradient-to-r from-indigo-100 to-indigo-200 text-indigo-800 shadow-sm',
    accepted: 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 shadow-sm',
    rejected: 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 shadow-sm'
  }
  return map[status] || 'bg-gray-100 text-gray-800 shadow-sm'
}

function formatStatus(status) {
  const map = {
    draft: 'Draft',
    contract_created: 'Contract Created',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return map[status] || status
}

// Flash methods
const clearFlashMessage = () => {
  flashMessage.value = ''
  flashMessageType.value = 'success'
}

// Actions
const goBack = () => {
  router.get(`/admin/customers/${props.customer.id}`)
}

const loadSubcities = () => {
  filteredSubcities.value = props.subcities.filter(subcity => 
    subcity.city_id == form.city_id
  )
  
  // Only reset subcity_id if the current one doesn't belong to the selected city
  if (form.subcity_id) {
    const currentSubcity = filteredSubcities.value.find(sub => sub.id == form.subcity_id)
    if (!currentSubcity) {
      form.subcity_id = ''
    }
  }
}

// Initialize filtered subcities
watch(() => form.city_id, (cityId) => {
  if (cityId) {
    loadSubcities()
  }
}, { immediate: true })

// Main submit function
const submit = () => {
  // Clear previous errors
  form.errors = {}
  flashMessage.value = ''
  loading.value = true
  form.processing = true
  
  // Use Inertia's router with proper redirect handling
  router.put(`/admin/customers/${props.customer.id}`, {
    name: form.name,
    email: form.email,
    phone: form.phone,
    status: form.status,
    city_id: form.city_id,
    subcity_id: form.subcity_id,
    text_location: form.text_location,
    map_location: form.map_location,
    remarks: form.remarks,
    is_basic_update: true
  }, {
    preserveScroll: false, // Don't preserve scroll when redirecting
    preserveState: false, // Don't preserve state when redirecting
    onSuccess: (page) => {
      // Success - redirect will happen automatically
      loading.value = false
      form.processing = false
    },
    onError: (errors) => {
      form.errors = errors || {}
      flashMessage.value = 'Please fix the errors below.'
      flashMessageType.value = 'error'
      
      loading.value = false
      form.processing = false
    },
    onFinish: () => {
      loading.value = false
      form.processing = false
    }
  })
}

// Mounted
onMounted(() => {
  if (props.flash?.message) {
    flashMessage.value = props.flash.message
    flashMessageType.value = props.flash.type || 'success'
  }
  
  // Load subcities for current city
  if (form.city_id) {
    loadSubcities()
  }
  
  // Initialize errors from props
  if (props.errors) {
    form.errors = props.errors
  }
})
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.fixed {
  position: fixed;
}

.inset-0 {
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
}

.bg-opacity-50 {
  --tw-bg-opacity: 0.5;
}

.z-50 {
  z-index: 50;
}

.disabled\:from-teal-400:disabled {
  --tw-gradient-from: #2dd4bf var(--tw-gradient-from-position);
  --tw-gradient-to: rgb(45 212 191 / 0) var(--tw-gradient-to-position);
  --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to);
}

.disabled\:to-teal-400:disabled {
  --tw-gradient-to: #2dd4bf var(--tw-gradient-to-position);
}

.disabled\:cursor-not-allowed:disabled {
  cursor: not-allowed;
}
</style>