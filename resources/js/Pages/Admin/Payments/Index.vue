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
            <h1 class="text-lg font-semibold text-gray-900">Payments</h1>
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
                @click="goBackToPotentialCustomer"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
              >
                <ArrowLeftIcon class="w-4 h-4" />
                <span>Back to Potential Customer</span>
              </button>
              <div>
                <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
                <p class="text-gray-600 mt-1">Manage payments for {{ potentialCustomerName }}</p>
              </div>
            </div>
            <button 
              v-if="permissions.create"
              @click="goToCreate"
              class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
            >
              <PlusIcon class="w-4 h-4" />
              <span>Create Payment</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 p-4 lg:p-6">
        <div class="max-w-7xl mx-auto">
          <!-- Flash Messages -->
          <div v-if="flashMessage" class="mb-6">
            <div :class="flashMessageClass" class="rounded-lg p-4 shadow-sm border">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <CheckCircleIcon v-if="flashMessageType === 'success'" class="w-5 h-5 text-green-500" />
                  <ExclamationCircleIcon v-else class="w-5 h-5 text-red-500" />
                  <p class="font-medium">{{ flashMessage }}</p>
                </div>
                <button @click="clearFlashMessage" class="text-gray-400 hover:text-gray-600">
                  <XMarkIcon class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>

          <!-- Summary Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 font-medium mb-1">Total Payments</p>
                  <p class="text-2xl font-bold text-gray-900">{{ payments.length }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                  <CurrencyDollarIcon class="w-6 h-6 text-blue-600" />
                </div>
              </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 font-medium mb-1">Total Amount</p>
                  <p class="text-2xl font-bold text-green-600">${{ totalAmount.toFixed(2) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                  <BanknotesIcon class="w-6 h-6 text-green-600" />
                </div>
              </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm text-gray-600 font-medium mb-1">Customer Status</p>
                  <p :class="statusBadgeClass" class="px-3 py-1 rounded-full text-sm font-semibold inline-block">
                    {{ formatStatus(customerStatus) }}
                  </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                  <UserCircleIcon class="w-6 h-6 text-purple-600" />
                </div>
              </div>
            </div>
          </div>

          <!-- Sync Notice for Accepted Customers -->
          <div v-if="potentialCustomer?.status === 'accepted' && hasAssociatedCustomer" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                  <p class="text-sm font-medium text-blue-900">
                    This customer has been converted to a regular customer.
                    <a :href="`/admin/customers/${associatedCustomerId}`" class="text-blue-600 hover:text-blue-800 font-semibold ml-1">
                      View Regular Customer →
                    </a>
                  </p>
                </div>
              </div>
              <button
                @click="syncPayments"
                class="px-4 py-2 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-lg hover:shadow-lg transition-all text-sm font-medium"
              >
                Sync Payments to Customer
              </button>
            </div>
          </div>

          <!-- Payments Table -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Payment History</h3>
                  <p class="text-gray-600 text-sm mt-1">Total: {{ payments.length }} payment(s)</p>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="relative">
                    <input
                      v-model="searchQuery"
                      type="text"
                      placeholder="Search payments..."
                      class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full md:w-64"
                    />
                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                  </div>
                  <button 
                    v-if="permissions.create"
                    @click="goToCreate"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 text-sm"
                  >
                    <PlusIcon class="w-4 h-4" />
                    <span>Create Payment</span>
                  </button>
                </div>
              </div>
            </div>
            
            <div v-if="filteredPayments.length > 0" class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Schedule</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Payment Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Commission</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="payment in filteredPayments" :key="payment.id" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                      <div class="font-bold text-gray-900">${{ formatNumber(payment.amount) }}</div>
                      <div v-if="payment.customer_id" class="text-xs text-green-600 mt-1">
                        ✅ Synced to Regular Customer
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center space-x-2">
                        <span class="text-lg">{{ getPaymentMethodIcon(payment.method) }}</span>
                        <span class="text-gray-700">{{ payment.method || 'Not specified' }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="text-gray-700">{{ payment.schedule || 'Not specified' }}</span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex flex-col">
                        <span class="text-gray-900 font-medium">{{ formatDate(payment.payment_date) }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span :class="getPaymentStatusClass(payment)" class="px-3 py-1 rounded-full text-sm font-semibold">
                        {{ payment.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <span class="text-gray-700">{{ payment.reference_number || 'N/A' }}</span>
                    </td>
                    <td class="px-6 py-4">
                      <div v-if="payment.commission_earned > 0">
                        <span class="text-sm font-medium text-green-600">
                          ${{ formatNumber(payment.commission_earned) }}
                        </span>
                        <div v-if="payment.commission_paid > 0" class="text-xs text-gray-500">
                          Paid: ${{ formatNumber(payment.commission_paid) }}
                        </div>
                      </div>
                      <span v-else class="text-gray-500 text-sm">-</span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex gap-2">
                        <button 
                          v-if="permissions.edit"
                          @click="editPayment(payment.id)"
                          class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
                        >
                          <PencilIcon class="w-3 h-3" />
                          <span>Edit</span>
                        </button>
                        <button 
                          v-if="permissions.delete"
                          @click="deletePayment(payment.id)"
                          class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
                        >
                          <TrashIcon class="w-3 h-3" />
                          <span>Delete</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- No Payments State -->
            <div v-else class="text-center py-12">
              <CurrencyDollarIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-semibold text-gray-900 mb-2">No payments found</h3>
              <p class="text-gray-600 mb-4 max-w-md mx-auto text-sm">
                {{ searchQuery ? 'No payments match your search.' : 'No payments have been added for this customer.' }}
              </p>
              <button 
                v-if="permissions.create"
                @click="goToCreate"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 mx-auto"
              >
                <PlusIcon class="w-4 h-4" />
                <span>{{ payments.length === 0 ? 'Create First Payment' : 'Add New Payment' }}</span>
              </button>
            </div>

            <!-- Payment Stats -->
            <div v-if="payments.length > 0" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="text-sm text-gray-600">
                  Showing {{ filteredPayments.length }} of {{ payments.length }} payments
                  <span v-if="syncedPaymentCount > 0" class="ml-2 text-green-600 font-medium">
                    ({{ syncedPaymentCount }} synced to regular customer)
                  </span>
                </div>
                <div class="flex items-center space-x-4">
                  <div class="text-sm text-gray-600">
                    <span class="font-medium">Methods:</span>
                    <span v-for="(method, index) in paymentMethods" :key="method" class="ml-2">
                      {{ method }}{{ index < paymentMethods.length - 1 ? ',' : '' }}
                    </span>
                  </div>
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
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  XMarkIcon,
  PencilIcon,
  PlusIcon,
  TrashIcon,
  CurrencyDollarIcon,
  Bars3Icon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  UserCircleIcon,
  BanknotesIcon,
  MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  potentialCustomer: {
    type: Object,
    required: true,
    default: () => ({})
  },
  payments: {
    type: Array,
    default: () => []
  },
  permissions: {
    type: Object,
    default: () => ({})
  },
  tables: {
    type: Array,
    default: () => []
  },
  flash: {
    type: Object,
    default: () => ({})
  }
})

// State
const mobileSidebarOpen = ref(false)
const flashMessage = ref(props.flash?.message || '')
const flashMessageType = ref(props.flash?.type || 'success')
const searchQuery = ref('')

// Computed
const potentialCustomerName = computed(() => {
  return props.potentialCustomer?.potential_customer_name || 'N/A'
})

const customerStatus = computed(() => {
  return props.potentialCustomer?.status || 'unknown'
})

const potentialCustomerId = computed(() => {
  return props.potentialCustomer?.id
})

const totalAmount = computed(() => {
  return props.payments.reduce((sum, payment) => sum + parseFloat(payment.amount || 0), 0)
})

const syncedPaymentCount = computed(() => {
  return props.payments.filter(p => p.customer_id).length
})

const hasAssociatedCustomer = computed(() => {
  // Check if any payment is linked to a customer
  return props.payments.some(p => p.customer_id)
})

const associatedCustomerId = computed(() => {
  // Find the first payment with a customer_id
  const paymentWithCustomer = props.payments.find(p => p.customer_id)
  return paymentWithCustomer?.customer_id || null
})

const paymentMethods = computed(() => {
  const methods = new Set()
  props.payments.forEach(payment => {
    if (payment.method) methods.add(payment.method)
  })
  return Array.from(methods)
})

const filteredPayments = computed(() => {
  if (!searchQuery.value.trim()) {
    return props.payments
  }
  
  const query = searchQuery.value.toLowerCase()
  return props.payments.filter(payment => {
    return (
      payment.method?.toLowerCase().includes(query) ||
      payment.schedule?.toLowerCase().includes(query) ||
      payment.reference_number?.toLowerCase().includes(query) ||
      payment.remarks?.toLowerCase().includes(query) ||
      payment.created_by?.name?.toLowerCase().includes(query) ||
      payment.amount?.toString().includes(query)
    )
  })
})

const flashMessageClass = computed(() => {
  return flashMessageType.value === 'success'
    ? 'bg-green-50 border-green-200 text-green-800'
    : 'bg-red-50 border-red-200 text-red-800'
})

const statusBadgeClass = computed(() => {
  const map = {
    draft: 'bg-blue-100 text-blue-800',
    proposal_sent: 'bg-orange-100 text-orange-800',
    accepted: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return map[customerStatus.value] || 'bg-gray-100 text-gray-800'
})

// Helper functions
function formatNumber(num) {
  if (!num) return '0.00'
  return parseFloat(num).toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

function formatDate(date) {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function formatStatus(status) {
  const map = {
    draft: 'Draft',
    proposal_sent: 'Proposal Sent',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return map[status] || status
}

function getPaymentMethodIcon(method) {
  const icons = {
    cash: '💵',
    'bank_transfer': '🏦',
    check: '📄',
    'mobile_payment': '📱',
    'credit_card': '💳',
    'online': '🌐'
  }
  return icons[method?.toLowerCase()] || '💰'
}

function getPaymentStatusClass(payment) {
  if (payment.is_active) {
    return payment.customer_id ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
  }
  return 'bg-gray-100 text-gray-800'
}

// Flash methods
const clearFlashMessage = () => {
  flashMessage.value = ''
  flashMessageType.value = 'success'
}

// Actions
const goBackToPotentialCustomer = () => {
  if (!potentialCustomerId.value) {
    console.error('No potential customer ID found!')
    alert('Unable to navigate back: Potential Customer ID not found')
    return
  }
  
  router.get(`/admin/potential-customers/${potentialCustomerId.value}`)
}

const goToCreate = () => {
  if (!potentialCustomerId.value) {
    alert('Unable to create payment: Potential Customer ID not found')
    return
  }
  
  router.get(`/admin/potential-customers/${potentialCustomerId.value}/payments/create`)
}

const editPayment = (paymentId) => {
  if (!potentialCustomerId.value) {
    alert('Unable to edit payment: Potential Customer ID not found')
    return
  }
  
  router.get(`/admin/potential-customers/${potentialCustomerId.value}/payments/${paymentId}/edit`)
}

const deletePayment = (paymentId) => {
  if (confirm('Are you sure you want to delete this payment?')) {
    if (!potentialCustomerId.value) {
      alert('Unable to delete payment: Potential Customer ID not found')
      return
    }
    
    router.delete(`/admin/potential-customers/${potentialCustomerId.value}/payments/${paymentId}`, {
      preserveScroll: true,
      onSuccess: () => router.reload()
    })
  }
}

const syncPayments = () => {
  if (confirm('Sync all payments to the associated regular customer? This will transfer all payments to the customer record.')) {
    router.post(route('payments.sync-to-customer', potentialCustomerId.value), {}, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['payments'] })
      },
      onError: () => {
        alert('Failed to sync payments. Please try again.')
      }
    })
  }
}

// Mounted
onMounted(() => {
  console.log('Index.vue mounted for potential customer')
  console.log('Potential Customer:', props.potentialCustomer)
  console.log('Potential Customer ID:', potentialCustomerId.value)
  
  if (props.flash?.message) {
    flashMessage.value = props.flash.message
    flashMessageType.value = props.flash.type || 'success'
  }
})
</script>