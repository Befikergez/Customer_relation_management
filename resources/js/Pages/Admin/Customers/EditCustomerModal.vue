<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click.self="closeModal">
    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden border border-gray-200 shadow-xl">
      <!-- Modal Header -->
      <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 z-10">
        <div class="flex justify-between items-center">
          <div>
            <h2 class="text-xl font-bold text-gray-900">Edit Customer</h2>
            <p class="text-gray-600 text-sm mt-1">Update customer basic information</p>
          </div>
          <button 
            @click="closeModal"
            class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>
        
        <!-- Status Badge in Header -->
        <div class="flex items-center mt-3">
          <span :class="getStatusBadgeClass(customer.status)" class="px-3 py-1 rounded-lg text-sm font-semibold">
            {{ formatStatus(customer.status) }}
          </span>
          <span class="text-gray-500 text-sm ml-3">ID: #{{ customer.id }}</span>
        </div>
      </div>

      <!-- Modal Content - Scrollable -->
      <div class="overflow-y-auto max-h-[calc(90vh-140px)] p-6">
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
                This modal is for editing customer basic information only. 
                Payment and commission details can be edited directly from the customer details page.
              </p>
            </div>
          </div>
        </div>

        <!-- Edit Form -->
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

                <div>
                  <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                  <input
                    id="location"
                    v-model="form.location"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    :class="{'border-red-300': form.errors.location}"
                  />
                  <p v-if="form.errors.location" class="mt-1 text-sm text-red-600">{{ form.errors.location }}</p>
                </div>

                <div>
                  <label for="specific_location" class="block text-sm font-medium text-gray-700 mb-2">Specific Location</label>
                  <input
                    id="specific_location"
                    v-model="form.specific_location"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    :class="{'border-red-300': form.errors.specific_location}"
                  />
                  <p v-if="form.errors.specific_location" class="mt-1 text-sm text-red-600">{{ form.errors.specific_location }}</p>
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
          </div>
        </form>
      </div>

      <!-- Modal Footer -->
      <div class="sticky bottom-0 bg-white border-t border-gray-200 px-6 py-4">
        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="closeModal"
            class="px-6 py-2 text-gray-600 hover:text-gray-800 font-medium text-sm transition-colors hover:bg-gray-100 rounded-lg"
          >
            Cancel
          </button>
          <button
            type="button"
            @click="submit"
            :disabled="loading"
            class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 disabled:from-teal-400 disabled:to-teal-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
          >
            <span v-if="loading">
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

      <!-- Loading Overlay -->
      <div v-if="loading" class="absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200">
          <div class="flex items-center space-x-3">
            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700 font-medium">Processing...</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  XMarkIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  UserCircleIcon,
  MapPinIcon,
  DocumentTextIcon,
  InformationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  },
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
  }
})

const emit = defineEmits(['close', 'success'])

// State
const loading = ref(false)

// Flash message state
const flashMessage = ref('')
const flashMessageType = ref('success')

// Form - Only basic customer information
const form = reactive({
  name: props.customer?.name || '',
  email: props.customer?.email || '',
  phone: props.customer?.phone || '',
  status: props.customer?.status || 'draft',
  city_id: props.customer?.city_id || '',
  subcity_id: props.customer?.subcity_id || '',
  location: props.customer?.location || '',
  specific_location: props.customer?.specific_location || '',
  remarks: props.customer?.remarks || '',
  is_basic_update: true,
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

const showFlashMessage = (message, type = 'success') => {
  flashMessage.value = message
  flashMessageType.value = type
  setTimeout(() => clearFlashMessage(), 5000)
}

// Modal methods
const closeModal = () => {
  emit('close')
}

// Form methods
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

// Main submit function
const submit = async () => {
  // Clear previous errors and flash messages
  form.errors = {}
  clearFlashMessage()
  loading.value = true
  
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    if (!csrfToken) {
      throw new Error('CSRF token not found')
    }

    const response = await fetch(`/admin/customers/${props.customer.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        name: form.name,
        email: form.email,
        phone: form.phone,
        status: form.status,
        city_id: form.city_id || null,
        subcity_id: form.subcity_id || null,
        location: form.location,
        specific_location: form.specific_location,
        remarks: form.remarks,
        is_basic_update: true
      })
    })

    const data = await response.json()

    if (response.ok) {
      // Success
      showFlashMessage('Customer updated successfully!', 'success')
      
      // Emit success event to parent
      emit('success', 'Customer updated successfully!')
      
      // Close modal after a short delay
      setTimeout(() => {
        closeModal()
      }, 1500)
    } else {
      // Handle validation errors
      if (data.errors) {
        form.errors = data.errors
        showFlashMessage('Please fix the errors below.', 'error')
      } else if (data.message) {
        showFlashMessage(data.message, 'error')
      } else {
        showFlashMessage('Failed to update customer.', 'error')
      }
    }
  } catch (error) {
    console.error('Update error:', error)
    showFlashMessage('Failed to update customer. Please try again.', 'error')
  } finally {
    loading.value = false
  }
}

// Initialize filtered subcities
watch(() => form.city_id, (cityId) => {
  if (cityId) {
    loadSubcities()
  }
}, { immediate: true })

// Initialize form data when customer changes
watch(() => props.customer, (newCustomer) => {
  if (newCustomer) {
    form.name = newCustomer.name || ''
    form.email = newCustomer.email || ''
    form.phone = newCustomer.phone || ''
    form.status = newCustomer.status || 'draft'
    form.city_id = newCustomer.city_id || ''
    form.subcity_id = newCustomer.subcity_id || ''
    form.location = newCustomer.location || ''
    form.specific_location = newCustomer.specific_location || ''
    form.remarks = newCustomer.remarks || ''
  }
}, { immediate: true })

// Load subcities on mounted
onMounted(() => {
  if (form.city_id) {
    loadSubcities()
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

.max-h-\[calc\(90vh-140px\)\] {
  max-height: calc(90vh - 140px);
}
</style>