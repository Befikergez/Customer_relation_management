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
            <h1 class="text-lg font-semibold text-gray-900">Create Payment</h1>
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
              <h1 class="text-2xl font-bold text-gray-900">Create Payment</h1>
              <p class="text-gray-600 mt-1">For: {{ potentialCustomer.potential_customer_name }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 p-4 lg:p-6">
        <div class="max-w-4xl mx-auto">
          <!-- Flash Messages -->
          <div v-if="errors.length > 0" class="mb-6">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 shadow-sm">
              <div class="flex items-center space-x-3">
                <ExclamationCircleIcon class="w-5 h-5 text-red-500" />
                <div>
                  <p class="font-medium mb-2">Please fix the following errors:</p>
                  <ul class="list-disc list-inside text-sm space-y-1">
                    <li v-for="error in errors" :key="error">{{ error }}</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>

          <!-- Create Payment Form -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="mb-8">
              <div class="flex items-center space-x-3 mb-2">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                  <CurrencyDollarIcon class="w-5 h-5 text-green-600" />
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Create New Payment</h3>
                  <p class="text-gray-600 text-sm">Add a payment record for this customer</p>
                </div>
              </div>
              
              <!-- Customer Info -->
              <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p class="text-sm text-blue-600 font-medium">Customer Name</p>
                    <p class="text-gray-900 font-semibold">{{ potentialCustomer.potential_customer_name }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-blue-600 font-medium">Customer Status</p>
                    <span :class="statusBadgeClass" class="px-3 py-1 rounded-full text-sm font-semibold inline-block">
                      {{ formatStatus(potentialCustomer.status) }}
                    </span>
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
                      class="pl-7 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                      placeholder="0.00"
                      :class="errors.amount ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                    />
                  </div>
                  <p v-if="errors.amount" class="mt-1 text-sm text-red-600">{{ errors.amount }}</p>
                </div>

                <!-- Payment Method -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Method <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.method"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    :class="errors.method ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                  >
                    <option value="">Select Payment Method</option>
                    <option value="Cash">💰 Cash</option>
                    <option value="Bank Transfer">🏦 Bank Transfer</option>
                    <option value="Credit Card">💳 Credit Card</option>
                    <option value="Check">📝 Check</option>
                    <option value="Digital Wallet">📱 Digital Wallet</option>
                    <option value="Other">💸 Other</option>
                  </select>
                  <p v-if="errors.method" class="mt-1 text-sm text-red-600">{{ errors.method }}</p>
                </div>

                <!-- Payment Schedule -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Payment Schedule <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.schedule"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    :class="errors.schedule ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                  >
                    <option value="">Select Payment Schedule</option>
                    <option value="One-time">One-time Payment</option>
                    <option value="Monthly">Monthly</option>
                    <option value="Quarterly">Quarterly</option>
                    <option value="Semi-annual">Semi-annual</option>
                    <option value="Annual">Annual</option>
                    <option value="Custom">Custom Schedule</option>
                  </select>
                  <p v-if="errors.schedule" class="mt-1 text-sm text-red-600">{{ errors.schedule }}</p>
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
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    :class="errors.payment_date ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                  />
                  <p v-if="errors.payment_date" class="mt-1 text-sm text-red-600">{{ errors.payment_date }}</p>
                </div>

                <!-- Reference Number -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
                  <input
                    type="text"
                    v-model="form.reference_number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="e.g., INV-001, TRANS-001"
                    :class="errors.reference_number ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                  />
                  <p v-if="errors.reference_number" class="mt-1 text-sm text-red-600">{{ errors.reference_number }}</p>
                </div>
              </div>

              <!-- Remarks -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <textarea
                  v-model="form.remarks"
                  rows="4"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
                  placeholder="Enter any additional notes about this payment..."
                  :class="errors.remarks ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                ></textarea>
                <p v-if="errors.remarks" class="mt-1 text-sm text-red-600">{{ errors.remarks }}</p>
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
                  type="submit"
                  :disabled="form.processing"
                  class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed flex items-center space-x-2"
                >
                  <CurrencyDollarIcon class="w-4 h-4" />
                  <span v-if="form.processing">Creating Payment...</span>
                  <span v-else>Create Payment</span>
                </button>
              </div>
            </form>
          </div>

          <!-- Existing Payments Preview -->
          <div v-if="potentialCustomer.payments && potentialCustomer.payments.length > 0" class="mt-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Recent Payments</h4>
              <div class="space-y-3">
                <div v-for="payment in potentialCustomer.payments.slice(0, 3)" :key="payment.id" 
                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                  <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                      <span class="text-lg">{{ payment.payment_method_icon }}</span>
                    </div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">${{ payment.amount }}</p>
                      <p class="text-xs text-gray-500">{{ payment.method }} • {{ payment.formatted_date }}</p>
                    </div>
                  </div>
                  <span :class="payment.status_badge_class" class="px-2 py-1 rounded-full text-xs font-semibold">
                    {{ payment.formatted_status }}
                  </span>
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
import { reactive, ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  XMarkIcon,
  CurrencyDollarIcon,
  Bars3Icon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  potentialCustomer: {
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
  errors: {
    type: Object,
    default: () => ({})
  },
  flash: {
    type: Object,
    default: () => ({})
  }
})

// State
const mobileSidebarOpen = ref(false)
const form = reactive({
  amount: '',
  method: '',
  schedule: '',
  payment_date: new Date().toISOString().split('T')[0],
  reference_number: '',
  remarks: '',
  processing: false
})

// Computed
const errorList = computed(() => {
  const list = []
  Object.entries(props.errors).forEach(([field, messages]) => {
    if (Array.isArray(messages)) {
      messages.forEach(msg => list.push(`${field}: ${msg}`))
    } else {
      list.push(`${field}: ${messages}`)
    }
  })
  return list
})

const statusBadgeClass = computed(() => {
  const map = {
    draft: 'bg-blue-100 text-blue-800',
    proposal_sent: 'bg-orange-100 text-orange-800',
    accepted: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return map[props.potentialCustomer.status] || 'bg-gray-100 text-gray-800'
})

// Helpers
function formatStatus(status) {
  const map = {
    draft: 'Draft',
    proposal_sent: 'Proposal Sent',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return map[status] || status
}

// Actions
const goBack = () => {
  router.get(`/admin/potential-customers/${props.potentialCustomer.id}/payments`)
}

const submitForm = () => {
  form.processing = true
  router.post(`/admin/potential-customers/${props.potentialCustomer.id}/payments`, form, {
    preserveScroll: true,
    onError: (errors) => {
      form.processing = false
    },
    onSuccess: () => {
      form.processing = false
    }
  })
}

// Mounted
onMounted(() => {
  // Set default values if needed
})
</script>