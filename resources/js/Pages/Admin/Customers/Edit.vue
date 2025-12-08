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
                <p class="text-gray-600 mt-1">Update customer information and details</p>
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

                <!-- Payment Information -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                      <CreditCardIcon class="w-4 h-4 text-white" />
                    </div>
                    <span>Payment Information</span>
                  </h3>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label for="total_payment_amount" class="block text-sm font-medium text-gray-700 mb-2">Total Amount *</label>
                      <input
                        id="total_payment_amount"
                        v-model="form.total_payment_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                        :class="{'border-red-300': form.errors.total_payment_amount}"
                      />
                      <p v-if="form.errors.total_payment_amount" class="mt-1 text-sm text-red-600">{{ form.errors.total_payment_amount }}</p>
                    </div>

                    <div>
                      <label for="paid_amount" class="block text-sm font-medium text-gray-700 mb-2">Paid Amount</label>
                      <input
                        id="paid_amount"
                        v-model="form.paid_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        :class="{'border-red-300': form.errors.paid_amount}"
                        @input="calculateRemaining"
                      />
                      <p v-if="form.errors.paid_amount" class="mt-1 text-sm text-red-600">{{ form.errors.paid_amount }}</p>
                    </div>

                    <div>
                      <label for="remaining_amount" class="block text-sm font-medium text-gray-700 mb-2">Remaining Amount</label>
                      <input
                        id="remaining_amount"
                        v-model="form.remaining_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                        :class="{'border-red-300': form.errors.remaining_amount}"
                        readonly
                      />
                      <p v-if="form.errors.remaining_amount" class="mt-1 text-sm text-red-600">{{ form.errors.remaining_amount }}</p>
                    </div>
                  </div>
                </div>

                <!-- Commission Information -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <div class="w-8 h-8 bg-gradient-to-r from-teal-500 to-teal-600 rounded-lg flex items-center justify-center mr-3">
                      <UserIcon class="w-4 h-4 text-white" />
                    </div>
                    <span>Commission Information</span>
                  </h3>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label for="commission_user_id" class="block text-sm font-medium text-gray-700 mb-2">Commission User</label>
                      <select
                        id="commission_user_id"
                        v-model="form.commission_user_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        :class="{'border-red-300': form.errors.commission_user_id}"
                        @change="onCommissionUserChange"
                      >
                        <option value="">Select Commission User</option>
                        <option v-for="user in commissionUsers" :key="user.id" :value="user.id">
                          {{ user.name }} ({{ user.commission_rate }}%)
                        </option>
                      </select>
                      <p v-if="form.errors.commission_user_id" class="mt-1 text-sm text-red-600">{{ form.errors.commission_user_id }}</p>
                    </div>

                    <div>
                      <label for="commission_rate" class="block text-sm font-medium text-gray-700 mb-2">Commission Rate (%)</label>
                      <input
                        id="commission_rate"
                        v-model="form.commission_rate"
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        :class="{'border-red-300': form.errors.commission_rate}"
                        @input="calculateCommission"
                      />
                      <p v-if="form.errors.commission_rate" class="mt-1 text-sm text-red-600">{{ form.errors.commission_rate }}</p>
                    </div>

                    <div>
                      <label for="commission_amount" class="block text-sm font-medium text-gray-700 mb-2">Total Commission</label>
                      <input
                        id="commission_amount"
                        v-model="form.commission_amount"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500"
                        :class="{'border-red-300': form.errors.commission_amount}"
                        readonly
                      />
                      <p v-if="form.errors.commission_amount" class="mt-1 text-sm text-red-600">{{ form.errors.commission_amount }}</p>
                    </div>

                    <div>
                      <label for="paid_commission" class="block text-sm font-medium text-gray-700 mb-2">Paid Commission</label>
                      <input
                        id="paid_commission"
                        v-model="form.paid_commission"
                        type="number"
                        step="0.01"
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                        :class="{'border-red-300': form.errors.paid_commission}"
                      />
                      <p v-if="form.errors.paid_commission" class="mt-1 text-sm text-red-600">{{ form.errors.paid_commission }}</p>
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
                    <span v-if="form.processing">Saving...</span>
                    <span v-else>Save Changes</span>
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
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
  CreditCardIcon,
  UserIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  customer: Object,
  cities: Array,
  subcities: Array,
  commissionUsers: Array,
  tables: Array,
  permissions: Object,
  flash: Object,
  errors: Object
})

// State
const mobileSidebarOpen = ref(false)

// Flash message state
const flashMessage = ref(props.flash?.message || '')
const flashMessageType = ref(props.flash?.type || 'success')

// Form
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
  total_payment_amount: props.customer?.total_payment_amount || 0,
  paid_amount: props.customer?.paid_amount || 0,
  remaining_amount: props.customer?.remaining_amount || 0,
  commission_user_id: props.customer?.commission_user_id || '',
  commission_rate: props.customer?.commission_rate || 0,
  commission_amount: props.customer?.commission_amount || 0,
  paid_commission: props.customer?.paid_commission || 0,
  processing: false,
  errors: props.errors || {}
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
  form.subcity_id = ''
}

// Calculate remaining amount
const calculateRemaining = () => {
  const total = parseFloat(form.total_payment_amount) || 0
  const paid = parseFloat(form.paid_amount) || 0
  form.remaining_amount = Math.max(0, total - paid)
}

// Calculate commission amount
const calculateCommission = () => {
  const total = parseFloat(form.total_payment_amount) || 0
  const rate = parseFloat(form.commission_rate) || 0
  form.commission_amount = (total * rate) / 100
}

// When commission user changes, set their commission rate
const onCommissionUserChange = () => {
  if (form.commission_user_id) {
    const user = props.commissionUsers.find(u => u.id == form.commission_user_id)
    if (user) {
      form.commission_rate = user.commission_rate || 0
      calculateCommission()
    }
  } else {
    form.commission_rate = 0
    form.commission_amount = 0
    form.paid_commission = 0
  }
}

// Watch for changes to calculate automatically
watch(() => form.total_payment_amount, () => {
  calculateRemaining()
  calculateCommission()
})

watch(() => form.paid_amount, () => {
  calculateRemaining()
})

watch(() => form.commission_rate, () => {
  calculateCommission()
})

// Initialize filtered subcities
watch(() => form.city_id, (cityId) => {
  if (cityId) {
    loadSubcities()
  }
}, { immediate: true })

const submit = () => {
  form.processing = true
  form.errors = {}
  
  // Ensure calculations are up to date
  calculateRemaining()
  calculateCommission()
  
  router.put(`/admin/customers/${props.customer.id}`, form, {
    preserveScroll: true,
    onSuccess: () => {
      form.processing = false
      flashMessage.value = 'Customer updated successfully!'
      flashMessageType.value = 'success'
    },
    onError: (errors) => {
      form.processing = false
      form.errors = errors
      flashMessage.value = 'Please fix the errors below.'
      flashMessageType.value = 'error'
    }
  })
}

// Initialize form with customer data
import { onMounted } from 'vue'
onMounted(() => {
  if (props.flash?.message) {
    flashMessage.value = props.flash.message
    flashMessageType.value = props.flash.type || 'success'
  }
  
  // Load subcities for current city
  if (form.city_id) {
    loadSubcities()
  }
})
</script>