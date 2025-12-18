<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-blue-50">
        <!-- Header -->
        <div class="bg-white border-b border-teal-200 px-4 sm:px-6 py-4 sm:py-6">
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-0">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-teal-600 to-blue-600 bg-clip-text text-transparent">Edit Potential Customer</h1>
              <p class="text-slate-600 mt-1 sm:mt-2 text-sm sm:text-base">Update customer information and details</p>
            </div>
            <button 
              @click="goBack"
              class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg w-fit"
            >
              <ArrowLeftIcon class="w-4 h-4" />
              <span class="text-sm">Back to Customers</span>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4 sm:p-6">
          <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg border border-teal-200 overflow-hidden">
              <!-- Success/Error Messages -->
              <div v-if="successMessage" class="bg-green-50 border-l-4 border-green-500 p-4 m-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <CheckCircleIcon class="h-5 w-5 text-green-400" />
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-green-700">{{ successMessage }}</p>
                  </div>
                </div>
              </div>
              
              <div v-if="errorMessage" class="bg-red-50 border-l-4 border-red-500 p-4 m-4">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <XCircleIcon class="h-5 w-5 text-red-400" />
                  </div>
                  <div class="ml-3">
                    <p class="text-sm text-red-700">{{ errorMessage }}</p>
                  </div>
                </div>
              </div>

              <!-- Form -->
              <form @submit.prevent="submitForm" class="p-6 sm:p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Customer Name -->
                  <div class="md:col-span-2">
                    <label for="potential_customer_name" class="block text-sm font-medium text-slate-700 mb-2">
                      Customer Name *
                    </label>
                    <input
                      type="text"
                      id="potential_customer_name"
                      v-model="form.potential_customer_name"
                      class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200"
                      required
                    />
                    <div v-if="form.errors.potential_customer_name" class="text-red-600 text-sm mt-1">
                      {{ form.errors.potential_customer_name }}
                    </div>
                  </div>

                  <!-- Email -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                      Email Address
                    </label>
                    <input
                      type="email"
                      id="email"
                      v-model="form.email"
                      class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200"
                    />
                    <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">
                      {{ form.errors.email }}
                    </div>
                  </div>

                  <!-- Phone -->
                  <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 mb-2">
                      Phone Number
                    </label>
                    <input
                      type="tel"
                      id="phone"
                      v-model="form.phone"
                      class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200"
                    />
                    <div v-if="form.errors.phone" class="text-red-600 text-sm mt-1">
                      {{ form.errors.phone }}
                    </div>
                  </div>

                  <!-- Location -->
                  <div class="md:col-span-2">
                    <label for="location" class="block text-sm font-medium text-slate-700 mb-2">
                      Location
                    </label>
                    <input
                      type="text"
                      id="location"
                      v-model="form.location"
                      class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200"
                    />
                    <div v-if="form.errors.location" class="text-red-600 text-sm mt-1">
                      {{ form.errors.location }}
                    </div>
                  </div>

                  <!-- City -->
                  <div>
                    <label for="city_id" class="block text-sm font-medium text-slate-700 mb-2">
                      City
                    </label>
                    <select
                      id="city_id"
                      v-model="form.city_id"
                      @change="onCityChange"
                      class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200"
                    >
                      <option value="">Select City</option>
                      <option v-for="city in cities" :key="city.id" :value="city.id">
                        {{ city.name }}
                      </option>
                    </select>
                    <div v-if="form.errors.city_id" class="text-red-600 text-sm mt-1">
                      {{ form.errors.city_id }}
                    </div>
                  </div>

                  <!-- Subcity -->
                  <div>
                    <label for="subcity_id" class="block text-sm font-medium text-slate-700 mb-2">
                      Subcity
                    </label>
                    <select
                      id="subcity_id"
                      v-model="form.subcity_id"
                      :disabled="!form.city_id"
                      class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 disabled:bg-slate-100 disabled:cursor-not-allowed"
                    >
                      <option value="">Select Subcity</option>
                      <option v-for="subcity in availableSubcities" :key="subcity.id" :value="subcity.id">
                        {{ subcity.name }}
                      </option>
                    </select>
                    <div v-if="form.errors.subcity_id" class="text-red-600 text-sm mt-1">
                      {{ form.errors.subcity_id }}
                    </div>
                  </div>

                  <!-- Remarks -->
                  <div class="md:col-span-2">
                    <label for="remarks" class="block text-sm font-medium text-slate-700 mb-2">
                      Remarks
                    </label>
                    <textarea
                      id="remarks"
                      v-model="form.remarks"
                      rows="4"
                      class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 resize-none"
                      placeholder="Add any additional remarks or notes about this customer..."
                    ></textarea>
                    <div v-if="form.errors.remarks" class="text-red-600 text-sm mt-1">
                      {{ form.errors.remarks }}
                    </div>
                  </div>

                  <!-- Status -->
                  <div class="md:col-span-2">
                    <label for="status" class="block text-sm font-medium text-slate-700 mb-2">
                      Status
                    </label>
                    <select
                      id="status"
                      v-model="form.status"
                      class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200"
                    >
                      <option value="draft">Draft</option>
                      <option value="proposal_sent">Proposal Sent</option>
                      <option value="accepted">Accepted</option>
                      <option value="rejected">Rejected</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-600 text-sm mt-1">
                      {{ form.errors.status }}
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-3 mt-8 pt-6 border-t border-slate-200">
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-teal-600 hover:bg-teal-700 disabled:bg-teal-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg"
                  >
                    <CheckIcon class="w-4 h-4" />
                    <span>{{ form.processing ? 'Updating...' : 'Update Customer' }}</span>
                  </button>
                  
                  <button
                    type="button"
                    @click="goBack"
                    class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg"
                  >
                    <XMarkIcon class="w-4 h-4" />
                    <span>Cancel</span>
                  </button>

                  <button
                    type="button"
                    @click="resetForm"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg"
                  >
                    <ArrowPathIcon class="w-4 h-4" />
                    <span>Reset</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, onMounted, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  CheckIcon,
  XMarkIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  potentialCustomer: Object,
  tables: Array,
  errors: Object,
  cities: Array,
  subcities: Array
})

const page = usePage()

// Computed property for available subcities
const availableSubcities = computed(() => {
  if (!form.city_id) return []
  return props.subcities.filter(subcity => subcity.city_id == form.city_id)
})

// Initialize form with customer data
const form = reactive({
  potential_customer_name: props.potentialCustomer?.potential_customer_name || '',
  email: props.potentialCustomer?.email || '',
  phone: props.potentialCustomer?.phone || '',
  location: props.potentialCustomer?.location || '',
  remarks: props.potentialCustomer?.remarks || '',
  status: props.potentialCustomer?.status || 'draft',
  city_id: props.potentialCustomer?.city_id || '',
  subcity_id: props.potentialCustomer?.subcity_id || '',
  processing: false,
  errors: {}
})

// Success and error messages
const successMessage = computed(() => page.props.flash.message)
const errorMessage = computed(() => page.props.flash.error || page.props.flash.errors?.message)

// Handle city change
const onCityChange = () => {
  // Reset subcity when city changes
  form.subcity_id = ''
}

// Submit form
const submitForm = () => {
  form.processing = true
  form.errors = {}
  
  router.put(`/admin/potential-customers/${props.potentialCustomer.id}`, form, {
    onSuccess: () => {
      // Success handled by controller
      form.processing = false
    },
    onError: (errors) => {
      form.errors = errors
      form.processing = false
    },
    preserveScroll: true
  })
}

// Reset form to original values
const resetForm = () => {
  form.potential_customer_name = props.potentialCustomer.potential_customer_name || ''
  form.email = props.potentialCustomer.email || ''
  form.phone = props.potentialCustomer.phone || ''
  form.location = props.potentialCustomer.location || ''
  form.remarks = props.potentialCustomer.remarks || ''
  form.status = props.potentialCustomer.status || 'draft'
  form.city_id = props.potentialCustomer.city_id || ''
  form.subcity_id = props.potentialCustomer.subcity_id || ''
  form.errors = {}
}

// Navigation
const goBack = () => {
  router.get('/admin/potential-customers')
}

// Clear flash messages after 5 seconds
onMounted(() => {
  if (successMessage.value || errorMessage.value) {
    setTimeout(() => {
      page.props.flash.message = null
      page.props.flash.error = null
    }, 5000)
  }
})

// Watch for flash messages
watch(() => page.props.flash, (newFlash) => {
  if (newFlash.message || newFlash.error) {
    setTimeout(() => {
      page.props.flash.message = null
      page.props.flash.error = null
    }, 5000)
  }
}, { deep: true })
</script>