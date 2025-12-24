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
                <span class="truncate">Payments</span>
                <div class="w-5 h-5 bg-gradient-to-r from-blue-400/30 to-purple-400/30 rounded flex items-center justify-center flex-shrink-0">
                  <CurrencyDollarIcon class="w-3 h-3 text-blue-600/70" />
                </div>
              </h1>
            </div>
            
            <!-- Right spacer for balance -->
            <div class="w-12 flex-shrink-0"></div>
          </div>
        </div>

        <!-- Desktop Header (1024px and above) -->
        <div class="hidden lg:block">
          <div class="px-4 lg:px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 md:gap-4">
              <div class="flex items-center space-x-3 md:space-x-4">
                <button 
                  @click="goBackToPotentialCustomer"
                  class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-2 md:px-4 md:py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 text-sm md:text-base whitespace-nowrap"
                >
                  <ArrowLeftIcon class="w-4 h-4" />
                  <span>Back to Customer</span>
                </button>
                <div class="min-w-0">
                  <h1 class="text-xl md:text-2xl font-bold text-gray-900 flex items-center gap-2 md:gap-3 truncate">
                    <span class="truncate">Payments</span>
                    <div class="w-6 h-6 md:w-7 md:h-7 bg-gradient-to-r from-blue-400/30 to-purple-400/30 rounded-lg flex items-center justify-center flex-shrink-0">
                      <CurrencyDollarIcon class="w-4 h-4 text-blue-600/70" />
                    </div>
                  </h1>
                  <p class="text-gray-600 mt-1 truncate text-sm md:text-base">Manage payments for {{ potentialCustomerName }}</p>
                </div>
              </div>
              <button 
                v-if="permissions.create"
                @click="goToCreate"
                class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 md:px-4 md:py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 text-sm md:text-base whitespace-nowrap mt-2 sm:mt-0"
              >
                <PlusIcon class="w-4 h-4" />
                <span>Create Payment</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 overflow-auto">
        <div class="p-3 md:p-4 lg:p-6">
          <div class="max-w-7xl mx-auto">
            <!-- Flash Messages -->
            <div v-if="flashMessage" class="mb-4 md:mb-6">
              <div :class="flashMessageClass" class="rounded-xl md:rounded-lg p-4 shadow-sm border">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <CheckCircleIcon v-if="flashMessageType === 'success'" class="w-5 h-5 text-green-500 flex-shrink-0" />
                    <ExclamationCircleIcon v-else class="w-5 h-5 text-red-500 flex-shrink-0" />
                    <p class="font-medium text-sm md:text-base">{{ flashMessage }}</p>
                  </div>
                  <button @click="clearFlashMessage" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 lg:gap-6 mb-4 md:mb-6">
              <div class="bg-white rounded-xl md:rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                <div class="flex items-center justify-between">
                  <div class="min-w-0">
                    <p class="text-xs md:text-sm text-gray-600 font-medium mb-1">Total Payments</p>
                    <p class="text-lg md:text-xl lg:text-2xl font-bold text-gray-900">{{ payments.length }}</p>
                  </div>
                  <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-lg md:rounded-xl flex items-center justify-center flex-shrink-0">
                    <CurrencyDollarIcon class="w-5 h-5 md:w-6 md:h-6 text-blue-600" />
                  </div>
                </div>
              </div>
              
              <div class="bg-white rounded-xl md:rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                <div class="flex items-center justify-between">
                  <div class="min-w-0">
                    <p class="text-xs md:text-sm text-gray-600 font-medium mb-1">Total Amount</p>
                    <p class="text-lg md:text-xl lg:text-2xl font-bold text-green-600">${{ totalAmount.toFixed(2) }}</p>
                  </div>
                  <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-lg md:rounded-xl flex items-center justify-center flex-shrink-0">
                    <BanknotesIcon class="w-5 h-5 md:w-6 md:h-6 text-green-600" />
                  </div>
                </div>
              </div>
              
              <div class="bg-white rounded-xl md:rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                <div class="flex items-center justify-between">
                  <div class="min-w-0">
                    <p class="text-xs md:text-sm text-gray-600 font-medium mb-1">Customer Status</p>
                    <p :class="statusBadgeClass" class="px-2 py-1 md:px-3 md:py-1 rounded-full text-xs md:text-sm font-semibold inline-block whitespace-nowrap">
                      {{ formatStatus(customerStatus) }}
                    </p>
                  </div>
                  <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 rounded-lg md:rounded-xl flex items-center justify-center flex-shrink-0">
                    <UserCircleIcon class="w-5 h-5 md:w-6 md:h-6 text-purple-600" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Sync Notice for Accepted Customers -->
            <div v-if="potentialCustomer?.status === 'accepted' && hasAssociatedCustomer" class="mb-4 md:mb-6 p-3 md:p-4 bg-blue-50 border border-blue-200 rounded-xl md:rounded-lg">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex items-center space-x-2 md:space-x-3">
                  <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <div class="min-w-0">
                    <p class="text-xs md:text-sm font-medium text-blue-900 truncate">
                      This customer has been converted to a regular customer.
                      <a :href="`/admin/customers/${associatedCustomerId}`" class="text-blue-600 hover:text-blue-800 font-semibold ml-1 whitespace-nowrap">
                        View Regular Customer →
                      </a>
                    </p>
                  </div>
                </div>
                <button
                  @click="syncPayments"
                  class="px-3 py-2 md:px-4 md:py-2 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-lg hover:shadow-lg transition-all text-xs md:text-sm font-medium whitespace-nowrap"
                >
                  Sync Payments to Customer
                </button>
              </div>
            </div>

            <!-- Payments Table -->
            <div class="bg-white rounded-xl md:rounded-lg shadow-sm border border-gray-200">
              <div class="px-3 md:px-4 lg:px-6 py-3 md:py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                  <div class="min-w-0">
                    <h3 class="text-base md:text-lg font-semibold text-gray-900 truncate">Payment History</h3>
                    <p class="text-gray-600 text-xs md:text-sm mt-0.5 truncate">Total: {{ payments.length }} payment(s)</p>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-center gap-2 md:gap-3">
                    <div class="relative min-w-0">
                      <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search payments..."
                        class="pl-9 pr-3 py-1.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-full text-sm"
                      />
                      <MagnifyingGlassIcon class="absolute left-3 top-2 md:top-2.5 w-4 h-4 md:w-5 md:h-5 text-gray-400" />
                    </div>
                    <button 
                      v-if="permissions.create"
                      @click="goToCreate"
                      class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 md:px-4 md:py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 text-xs md:text-sm whitespace-nowrap"
                    >
                      <PlusIcon class="w-3 h-3 md:w-4 md:h-4" />
                      <span>Create Payment</span>
                    </button>
                  </div>
                </div>
              </div>
              
              <!-- Mobile Card View -->
              <div v-if="filteredPayments.length > 0" class="md:hidden divide-y divide-gray-200">
                <div v-for="payment in filteredPayments" :key="payment.id" class="p-3 hover:bg-gray-50 transition-colors">
                  <div class="flex justify-between items-start mb-2">
                    <div>
                      <div class="font-bold text-gray-900 text-sm">${{ formatNumber(payment.amount) }}</div>
                      <div class="flex items-center space-x-2 mt-1">
                        <span class="text-lg">{{ getPaymentMethodIcon(payment.method) }}</span>
                        <span class="text-gray-700 text-xs">{{ payment.method || 'Not specified' }}</span>
                      </div>
                    </div>
                    <span :class="getPaymentStatusClass(payment)" class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
                      {{ payment.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-2 mb-3 text-xs text-gray-600">
                    <div>
                      <span class="font-medium">Schedule:</span>
                      <p class="truncate">{{ payment.schedule || 'Not specified' }}</p>
                    </div>
                    <div>
                      <span class="font-medium">Date:</span>
                      <p class="truncate">{{ formatDate(payment.payment_date) }}</p>
                    </div>
                  </div>
                  
                  <div v-if="payment.customer_id" class="text-xs text-green-600 mb-2">
                    ✅ Synced to Regular Customer
                  </div>
                  
                  <div class="flex gap-2">
                    <button 
                      v-if="permissions.edit"
                      @click="editPayment(payment.id)"
                      class="bg-yellow-600 hover:bg-yellow-700 text-white px-2 py-1 rounded text-xs font-medium transition-colors flex items-center space-x-1 flex-1 justify-center"
                    >
                      <PencilIcon class="w-3 h-3" />
                      <span>Edit</span>
                    </button>
                    <button 
                      v-if="permissions.delete"
                      @click="deletePayment(payment.id)"
                      class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs font-medium transition-colors flex items-center space-x-1 flex-1 justify-center"
                    >
                      <TrashIcon class="w-3 h-3" />
                      <span>Delete</span>
                    </button>
                  </div>
                </div>
              </div>
              
              <!-- Desktop Table View -->
              <div v-else-if="filteredPayments.length > 0" class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[800px]">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Amount</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Method</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Schedule</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Payment Date</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Status</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider whitespace-nowrap">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="payment in filteredPayments" :key="payment.id" class="hover:bg-gray-50 transition-colors">
                      <td class="px-4 py-3 whitespace-nowrap">
                        <div class="font-bold text-gray-900">${{ formatNumber(payment.amount) }}</div>
                        <div v-if="payment.customer_id" class="text-xs text-green-600 mt-1">
                          ✅ Synced to Regular Customer
                        </div>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center space-x-2">
                          <span class="text-lg">{{ getPaymentMethodIcon(payment.method) }}</span>
                          <span class="text-gray-700 text-sm">{{ payment.method || 'Not specified' }}</span>
                        </div>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <span class="text-gray-700 text-sm">{{ payment.schedule || 'Not specified' }}</span>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex flex-col">
                          <span class="text-gray-900 font-medium text-sm">{{ formatDate(payment.payment_date) }}</span>
                        </div>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <span :class="getPaymentStatusClass(payment)" class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
                          {{ payment.is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex gap-2">
                          <button 
                            v-if="permissions.edit"
                            @click="editPayment(payment.id)"
                            class="bg-yellow-600 hover:bg-yellow-700 text-white px-2 py-1 rounded text-xs font-medium transition-colors flex items-center space-x-1"
                          >
                            <PencilIcon class="w-3 h-3" />
                            <span>Edit</span>
                          </button>
                          <button 
                            v-if="permissions.delete"
                            @click="deletePayment(payment.id)"
                            class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs font-medium transition-colors flex items-center space-x-1"
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
              <div v-else class="text-center py-8 md:py-12">
                <CurrencyDollarIcon class="w-12 h-12 md:w-16 md:h-16 text-gray-400 mx-auto mb-3 md:mb-4" />
                <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-2">No payments found</h3>
                <p class="text-gray-600 mb-3 md:mb-4 max-w-md mx-auto text-xs md:text-sm">
                  {{ searchQuery ? 'No payments match your search.' : 'No payments have been added for this customer.' }}
                </p>
                <button 
                  v-if="permissions.create"
                  @click="goToCreate"
                  class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 md:px-4 md:py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 text-xs md:text-sm mx-auto whitespace-nowrap"
                >
                  <PlusIcon class="w-3 h-3 md:w-4 md:h-4" />
                  <span>{{ payments.length === 0 ? 'Create First Payment' : 'Add New Payment' }}</span>
                </button>
              </div>

              <!-- Payment Stats -->
              <div v-if="payments.length > 0" class="px-3 md:px-4 lg:px-6 py-3 md:py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 md:gap-4">
                  <div class="text-xs md:text-sm text-gray-600 truncate">
                    Showing {{ filteredPayments.length }} of {{ payments.length }} payments
                    <span v-if="syncedPaymentCount > 0" class="ml-1 md:ml-2 text-green-600 font-medium whitespace-nowrap">
                      ({{ syncedPaymentCount }} synced)
                    </span>
                  </div>
                  <div class="text-xs md:text-sm text-gray-600 truncate">
                    <span class="font-medium">Methods:</span>
                    <span v-for="(method, index) in paymentMethods" :key="method" class="ml-1 md:ml-2">
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
const isSidebarOpen = ref(false)
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

// Listen for sidebar state changes
onMounted(() => {
  // Listen for sidebar state changes
  const handleSidebarStateChange = (event) => {
    isSidebarOpen.value = event.detail.isOpen
  }
  
  window.addEventListener('sidebar:state-changed', handleSidebarStateChange)
  
  // Check initial sidebar state by looking for mobile menu button
  const checkInitialSidebarState = () => {
    const mobileMenuBtn = document.getElementById('mobile-menu-toggle')
    const sidebar = document.querySelector('aside')
    if (mobileMenuBtn && sidebar) {
      // Check if sidebar is visible (translate-x-0 class)
      isSidebarOpen.value = sidebar.classList.contains('translate-x-0')
    }
  }
  
  // Wait a bit for DOM to be ready
  setTimeout(checkInitialSidebarState, 100)
  
  console.log('Index.vue mounted for potential customer')
  console.log('Potential Customer:', props.potentialCustomer)
  console.log('Potential Customer ID:', potentialCustomerId.value)
  
  if (props.flash?.message) {
    flashMessage.value = props.flash.message
    flashMessageType.value = props.flash.type || 'success'
  }
  
  return () => {
    window.removeEventListener('sidebar:state-changed', handleSidebarStateChange)
  }
})

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
</script>

<style>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
}

/* Smooth transitions */
* {
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform, background-image;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Gradient text for active status */
.gradient-text {
  background: linear-gradient(to right, #3b82f6, #8b5cf6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Prevent overflow */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.min-w-0 {
  min-width: 0;
}

.max-w-full {
  max-width: 100%;
}

/* Responsive table styles */
@media (max-width: 768px) {
  .table-container {
    overflow-x: visible;
  }
  
  .table-container table {
    width: 100%;
  }
}

/* Header transition */
#header-section {
  transition: transform 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

/* Ensure proper z-index layering */
aside {
  z-index: 50 !important;
}

#header-section {
  z-index: 40 !important;
}

/* Prevent horizontal overflow */
html, body {
  overflow-x: hidden;
}

/* Ensure tables don't cause horizontal scroll on large screens */
@media (min-width: 1024px) {
  .overflow-x-auto {
    overflow-x: visible !important;
  }
  
  table {
    width: 100% !important;
  }
}
</style>