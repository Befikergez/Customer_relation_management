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
            class="text-gray-500 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <Bars3Icon class="h-6 w-6" />
          </button>
          <div class="flex-1 text-center">
            <h1 class="text-lg font-semibold text-gray-900">Edit Payment</h1>
          </div>
          <div class="w-6"></div>
        </div>
      </div>

      <!-- Desktop Header -->
      <div class="hidden lg:block bg-white shadow-sm border-b border-gray-200">
        <div class="px-6 py-4">
          <div class="flex items-center space-x-4">
            <button 
              @click="goBack"
              class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
            >
              <ArrowLeftIcon class="w-4 h-4" />
              <span>Back to Payments</span>
            </button>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Edit Payment</h1>
              <p class="text-gray-600 mt-1">
                For: {{ customerName }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 p-4 lg:p-6">
        <div class="max-w-4xl mx-auto">
          <!-- Flash Messages -->
          <div v-if="flashMessage && flashMessage.message" class="mb-6">
            <div :class="flashMessageClass" class="rounded-lg p-4 shadow-sm">
              <div class="flex items-center space-x-3">
                <ExclamationCircleIcon v-if="flashMessage.type === 'error'" class="w-5 h-5 text-red-500" />
                <CheckCircleIcon v-else-if="flashMessage.type === 'success'" class="w-5 h-5 text-green-500" />
                <ExclamationTriangleIcon v-else class="w-5 h-5 text-yellow-500" />
                <div>
                  <p class="font-medium">{{ flashMessage.message }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Error Messages -->
          <div v-if="errorList.length > 0" class="mb-6">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 shadow-sm">
              <div class="flex items-center space-x-3">
                <ExclamationCircleIcon class="w-5 h-5 text-red-500" />
                <div>
                  <p class="font-medium mb-2">Please fix the following errors:</p>
                  <ul class="list-disc list-inside text-sm space-y-1">
                    <li v-for="error in errorList" :key="error">{{ error }}</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Edit Payment Form -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-8">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <CurrencyDollarIcon class="w-5 h-5 text-yellow-600" />
                  </div>
                  <div>
                    <h3 class="text-lg font-semibold text-gray-900">Edit Payment</h3>
                    <p class="text-gray-600 text-sm">Update payment information</p>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <span v-if="payment?.status_badge_class" :class="payment.status_badge_class" class="px-3 py-1 rounded-full text-sm font-semibold">
                    {{ payment.formatted_status || 'Active' }}
                  </span>
                  <span class="text-xs text-gray-500">Created: {{ formatDate(payment?.created_at) }}</span>
                </div>
              </div>
              
              <!-- Customer Info -->
              <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-blue-600 font-medium">Customer Name</p>
                    <p class="text-gray-900 font-semibold">
                      {{ customerName }}
                    </p>
                  </div>
                  <div>
                    <p class="text-sm text-blue-600 font-medium">Payment Created By</p>
                    <p class="text-gray-900">{{ payment?.created_by?.name || 'System' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-blue-600 font-medium">Customer Type</p>
                    <p class="text-gray-900 capitalize">{{ customerType }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-blue-600 font-medium">Payment ID</p>
                    <p class="text-gray-900">#{{ payment?.id || 'N/A' }}</p>
                  </div>
                </div>
              </div>
            </div>
            
            <form @submit.prevent="submitForm" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Amount -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Amount ($) <span class="text-red-500">*</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input
                      type="number"
                      step="0.01"
                      min="0"
                      v-model="form.amount"
                      required
                      class="pl-7 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                      placeholder="0.00"
                      :class="errors?.amount ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                    />
                  </div>
                  <p v-if="errors?.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
                </div>

                <!-- Payment Method -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Method <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.method"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                    :class="errors?.method ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                  >
                    <option value="">Select Payment Method</option>
                    <option value="Cash">💰 Cash</option>
                    <option value="Bank Transfer">🏦 Bank Transfer</option>
                    <option value="Credit Card">💳 Credit Card</option>
                    <option value="Check">📝 Check</option>
                    <option value="Digital Wallet">📱 Digital Wallet</option>
                    <option value="Other">💸 Other</option>
                  </select>
                  <p v-if="errors?.method" class="mt-1 text-sm text-red-600">{{ errors.method }}</p>
                </div>

                <!-- Payment Schedule -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Schedule <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.schedule"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                    :class="errors?.schedule ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                  >
                    <option value="">Select Payment Schedule</option>
                    <option value="One-time">One-time Payment</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Quarterly">Quarterly</option>
                    <option value="Semi-annual">Semi-annual</option>
                    <option value="Annual">Annual</option>
                    <option value="Custom">Custom Schedule</option>
                  </select>
                  <p v-if="errors?.schedule" class="mt-1 text-sm text-red-600">{{ errors.schedule }}</p>
                </div>

                <!-- Payment Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Date <span class="text-red-500">*</span>
                  </label>
                  <input
                    type="date"
                    v-model="form.payment_date"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                    :class="errors?.payment_date ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                  />
                  <p v-if="errors?.payment_date" class="mt-1 text-sm text-red-600">{{ errors.payment_date }}</p>
                </div>

                <!-- Reference Number -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                  <input
                    type="text"
                    v-model="form.reference_number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                    placeholder="e.g., INV-001, TRANS-001"
                    :class="errors?.reference_number ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                  />
                  <p v-if="errors?.reference_number" class="mt-1 text-sm text-red-600">{{ errors.reference_number }}</p>
                </div>
              </div>

              <!-- Remarks -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <textarea
                  v-model="form.remarks"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 resize-none"
                  placeholder="Enter any additional notes about this payment..."
                  :class="errors?.remarks ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                ></textarea>
                <p v-if="errors?.remarks" class="mt-1 text-sm text-red-600">{{ errors.remarks }}</p>
              </div>

              <!-- Commission Information (if available) -->
              <div v-if="payment?.commission_earned > 0" class="p-4 bg-green-50 rounded-lg border border-green-200">
                <div class="flex items-center space-x-2 mb-2">
                  <CurrencyDollarIcon class="w-5 h-5 text-green-600" />
                  <h4 class="text-sm font-semibold text-green-800">Commission Information</h4>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div>
                    <p class="text-xs text-green-600 font-medium">Commission Earned</p>
                    <p class="text-green-900 font-semibold">${{ (payment.commission_earned || 0).toFixed(2) }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-green-600 font-medium">Commission Paid</p>
                    <p class="text-green-900 font-semibold">${{ (payment.commission_paid || 0).toFixed(2) }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-green-600 font-medium">Commission Status</p>
                    <span :class="payment.commission_paid_status ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" 
                          class="px-2 py-1 rounded-full text-xs font-semibold">
                      {{ payment.commission_paid_status ? 'Paid' : 'Pending' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                <button 
                  type="button"
                  @click="goBack"
                  class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium text-sm transition-colors"
                >
                  Cancel
                </button>
                <button 
                  v-if="permissions?.delete"
                  type="button"
                  @click="deletePayment"
                  class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium text-sm transition-colors flex items-center space-x-2"
                >
                  <TrashIcon class="w-4 h-4" />
                  <span>Delete Payment</span>
                </button>
                <button 
                  type="submit"
                  :disabled="form.processing"
                  class="bg-yellow-600 hover:bg-yellow-700 disabled:bg-yellow-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed flex items-center space-x-2"
                >
                  <PencilIcon class="w-4 h-4" />
                  <span v-if="form.processing">Updating Payment...</span>
                  <span v-else>Update Payment</span>
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
import { reactive, ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  XMarkIcon,
  CurrencyDollarIcon,
  PencilIcon,
  TrashIcon,
  Bars3Icon,
  ExclamationCircleIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  payment: {
    type: Object,
    default: () => ({})
  },
  customer: {
    type: Object,
    default: () => ({})
  },
  customerType: {
    type: String,
    default: 'potential'
  },
  permissions: {
    type: Object,
    default: () => ({})
  },
  tables: {
    type: Array,
    default: () => []
  },
  errors: {
    type: Object,
    default: () => ({})
  }
})

// Get page props for flash messages
const page = usePage()

// State
const mobileSidebarOpen = ref(false)
const form = reactive({
  amount: '',
  method: '',
  schedule: '',
  payment_date: '',
  reference_number: '',
  remarks: '',
  customer_type: props.customerType,
  processing: false
})

// Computed
const flashMessage = computed(() => {
  return page.props?.flash || {}
})

const flashMessageClass = computed(() => {
  const type = flashMessage.value?.type || 'success'
  return {
    'error': 'bg-red-50 border border-red-200 text-red-800',
    'warning': 'bg-yellow-50 border border-yellow-200 text-yellow-800',
    'info': 'bg-blue-50 border border-blue-200 text-blue-800',
    'success': 'bg-green-50 border border-green-200 text-green-800'
  }[type] || 'bg-green-50 border border-green-200 text-green-800'
})

const customerName = computed(() => {
  if (props.customerType === 'potential') {
    return props.customer?.potential_customer_name || 'N/A'
  } else {
    return props.customer?.name || 'N/A'
  }
})

const errorList = computed(() => {
  const list = []
  if (props.errors) {
    Object.entries(props.errors).forEach(([field, messages]) => {
      if (Array.isArray(messages)) {
        messages.forEach(msg => list.push(`${field}: ${msg}`))
      } else {
        list.push(`${field}: ${messages}`)
      }
    })
  }
  return list
})

// Helper functions
function formatDate(dateString) {
  if (!dateString) return 'N/A'
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch (e) {
    return 'Invalid Date'
  }
}

function formatDateForInput(dateString) {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    return date.toISOString().split('T')[0]
  } catch (e) {
    return ''
  }
}

// Actions
const goBack = () => {
  if (props.customerType === 'potential') {
    router.get(`/admin/potential-customers/${props.customer?.id}/payments`)
  } else {
    router.get(`/admin/customers/${props.customer?.id}/payments`)
  }
}

const submitForm = () => {
  form.processing = true
  
  // Include customer_type in the form data
  const formData = {
    ...form,
    customer_type: props.customerType
  }
  
  if (props.customerType === 'potential') {
    router.put(`/admin/potential-customers/${props.customer?.id}/payments/${props.payment?.id}`, formData, {
      preserveScroll: true,
      onError: (errors) => {
        form.processing = false
      },
      onSuccess: () => {
        form.processing = false
      }
    })
  } else {
    router.put(`/admin/customers/${props.customer?.id}/payments/${props.payment?.id}`, formData, {
      preserveScroll: true,
      onError: (errors) => {
        form.processing = false
      },
      onSuccess: () => {
        form.processing = false
      }
    })
  }
}

const deletePayment = () => {
  if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
    if (props.customerType === 'potential') {
      router.delete(`/admin/potential-customers/${props.customer?.id}/payments/${props.payment?.id}`, {
        preserveScroll: true,
        onSuccess: () => goBack()
      })
    } else {
      router.delete(`/admin/customers/${props.customer?.id}/payments/${props.payment?.id}`, {
        preserveScroll: true,
        onSuccess: () => goBack()
      })
    }
  }
}

// Mounted
onMounted(() => {
  // Initialize form with payment data
  if (props.payment) {
    form.amount = props.payment.amount || ''
    form.method = props.payment.method || ''
    form.schedule = props.payment.schedule || ''
    form.payment_date = props.payment.payment_date ? formatDateForInput(props.payment.payment_date) : ''
    form.reference_number = props.payment.reference_number || ''
    form.remarks = props.payment.remarks || ''
    form.customer_type = props.customerType
  }
})
</script>