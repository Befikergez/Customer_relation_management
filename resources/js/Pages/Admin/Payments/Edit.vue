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
                <span class="truncate">Edit Payment</span>
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
                  <span class="truncate">Edit Payment</span>
                  <div class="w-6 h-6 md:w-7 md:h-7 bg-gradient-to-r from-blue-400/30 to-purple-400/30 rounded-lg flex items-center justify-center flex-shrink-0">
                    <CurrencyDollarIcon class="w-4 h-4 text-blue-600/70" />
                  </div>
                </h1>
                <p class="text-gray-600 mt-1 truncate text-sm md:text-base">
                  For: {{ customerName }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 overflow-auto">
        <div class="p-3 md:p-4 lg:p-6">
          <div class="max-w-4xl mx-auto">
            <!-- Flash Messages -->
            <div v-if="flashMessage && flashMessage.message" class="mb-4 md:mb-6">
              <div :class="flashMessageClass" class="rounded-xl md:rounded-lg p-4 shadow-sm">
                <div class="flex items-center space-x-3">
                  <ExclamationCircleIcon v-if="flashMessage.type === 'error'" class="w-5 h-5 text-red-500 flex-shrink-0" />
                  <CheckCircleIcon v-else-if="flashMessage.type === 'success'" class="w-5 h-5 text-green-500 flex-shrink-0" />
                  <ExclamationTriangleIcon v-else class="w-5 h-5 text-yellow-500 flex-shrink-0" />
                  <div class="min-w-0">
                    <p class="font-medium text-sm md:text-base truncate">{{ flashMessage.message }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Error Messages -->
            <div v-if="errorList.length > 0" class="mb-4 md:mb-6">
              <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl md:rounded-lg p-4 shadow-sm">
                <div class="flex items-center space-x-3">
                  <ExclamationCircleIcon class="w-5 h-5 text-red-500 flex-shrink-0" />
                  <div class="min-w-0">
                    <p class="font-medium mb-2 text-sm md:text-base">Please fix the following errors:</p>
                    <ul class="list-disc list-inside text-xs md:text-sm space-y-1">
                      <li v-for="error in errorList" :key="error" class="truncate">{{ error }}</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>

            <!-- Edit Payment Form -->
            <div class="bg-white rounded-xl md:rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
              <div class="mb-6 md:mb-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 md:gap-0 mb-3 md:mb-4">
                  <div class="flex items-center space-x-2 md:space-x-3">
                    <div class="w-8 h-8 md:w-10 md:h-10 bg-yellow-100 rounded-lg md:rounded-xl flex items-center justify-center flex-shrink-0">
                      <CurrencyDollarIcon class="w-4 h-4 md:w-5 md:h-5 text-yellow-600" />
                    </div>
                    <div class="min-w-0">
                      <h3 class="text-base md:text-lg font-semibold text-gray-900 truncate">Edit Payment</h3>
                      <p class="text-gray-600 text-xs md:text-sm truncate">Update payment information</p>
                    </div>
                  </div>
                  <div class="flex flex-col sm:flex-row sm:items-center gap-1 md:gap-2">
                    <span v-if="payment?.status_badge_class" :class="payment.status_badge_class" class="px-2 py-1 md:px-3 md:py-1 rounded-full text-xs md:text-sm font-semibold whitespace-nowrap">
                      {{ payment.formatted_status || 'Active' }}
                    </span>
                    <span class="text-xs text-gray-500 truncate">Created: {{ formatDate(payment?.created_at) }}</span>
                  </div>
                </div>
                
                <!-- Customer Info -->
                <div class="p-3 md:p-4 bg-blue-50 rounded-lg md:rounded-lg border border-blue-200">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                    <div>
                      <p class="text-xs md:text-sm text-blue-600 font-medium">Customer Name</p>
                      <p class="text-gray-900 font-semibold text-sm md:text-base truncate">
                        {{ customerName }}
                      </p>
                    </div>
                    <div>
                      <p class="text-xs md:text-sm text-blue-600 font-medium">Payment Created By</p>
                      <p class="text-gray-900 text-sm md:text-base truncate">{{ payment?.created_by?.name || 'System' }}</p>
                    </div>
                    <div>
                      <p class="text-xs md:text-sm text-blue-600 font-medium">Customer Type</p>
                      <p class="text-gray-900 text-sm md:text-base capitalize">{{ customerType }}</p>
                    </div>
                    <div>
                      <p class="text-xs md:text-sm text-blue-600 font-medium">Payment ID</p>
                      <p class="text-gray-900 text-sm md:text-base">#{{ payment?.id || 'N/A' }}</p>
                    </div>
                    <div v-if="customer?.id">
                      <p class="text-xs md:text-sm text-blue-600 font-medium">Customer ID</p>
                      <p class="text-gray-900 text-sm md:text-base">#{{ customer.id }}</p>
                    </div>
                    <div>
                      <p class="text-xs md:text-sm text-blue-600 font-medium">Has Potential Customer ID?</p>
                      <p class="text-gray-900 text-sm md:text-base">{{ payment?.potential_customer_id ? 'Yes' : 'No' }}</p>
                    </div>
                  </div>
                </div>
              </div>
              
              <form @submit.prevent="submitForm" class="space-y-4 md:space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                  <!-- Amount -->
                  <div>
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">
                      Amount ($) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 text-sm">$</span>
                      </div>
                      <input
                        type="number"
                        step="0.01"
                        min="0"
                        v-model="form.amount"
                        required
                        class="pl-7 w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm md:text-base"
                        placeholder="0.00"
                        :class="errors?.amount ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                      />
                    </div>
                    <p v-if="errors?.amount" class="mt-1 text-xs md:text-sm text-red-600 truncate">{{ errors.amount }}</p>
                  </div>

                  <!-- Payment Method -->
                  <div>
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">
                      Payment Method <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.method"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm md:text-base"
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
                    <p v-if="errors?.method" class="mt-1 text-xs md:text-sm text-red-600 truncate">{{ errors.method }}</p>
                  </div>

                  <!-- Payment Schedule -->
                  <div>
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">
                      Payment Schedule <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.schedule"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm md:text-base"
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
                    <p v-if="errors?.schedule" class="mt-1 text-xs md:text-sm text-red-600 truncate">{{ errors.schedule }}</p>
                  </div>

                  <!-- Payment Date -->
                  <div>
                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">
                      Payment Date <span class="text-red-500">*</span>
                    </label>
                    <input
                      type="date"
                      v-model="form.payment_date"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm md:text-base"
                      :class="errors?.payment_date ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                    />
                    <p v-if="errors?.payment_date" class="mt-1 text-xs md:text-sm text-red-600 truncate">{{ errors.payment_date }}</p>
                  </div>
                </div>

                <!-- Remarks -->
                <div>
                  <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Remarks</label>
                  <textarea
                    v-model="form.remarks"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 resize-none text-sm md:text-base"
                    placeholder="Enter any additional notes about this payment..."
                    :class="errors?.remarks ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : ''"
                  ></textarea>
                  <p v-if="errors?.remarks" class="mt-1 text-xs md:text-sm text-red-600 truncate">{{ errors.remarks }}</p>
                </div>

                <!-- Commission Information (if available) -->
                <div v-if="payment?.commission_earned > 0" class="p-3 md:p-4 bg-green-50 rounded-lg border border-green-200">
                  <div class="flex items-center space-x-2 mb-2">
                    <CurrencyDollarIcon class="w-4 h-4 md:w-5 md:h-5 text-green-600" />
                    <h4 class="text-xs md:text-sm font-semibold text-green-800">Commission Information</h4>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                    <div>
                      <p class="text-xs text-green-600 font-medium">Commission Earned</p>
                      <p class="text-green-900 font-semibold text-sm md:text-base">${{ (payment.commission_earned || 0).toFixed(2) }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-green-600 font-medium">Commission Paid</p>
                      <p class="text-green-900 font-semibold text-sm md:text-base">${{ (payment.commission_paid || 0).toFixed(2) }}</p>
                    </div>
                    <div>
                      <p class="text-xs text-green-600 font-medium">Commission Status</p>
                      <span :class="payment.commission_paid_status ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" 
                            class="px-2 py-1 rounded-full text-xs font-semibold whitespace-nowrap">
                        {{ payment.commission_paid_status ? 'Paid' : 'Pending' }}
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-wrap justify-end gap-2 pt-4 md:pt-6 border-t border-gray-200">
                  <button 
                    type="button"
                    @click="goBackToPotentialCustomer"
                    class="px-3 py-1.5 md:px-4 md:py-2 text-gray-600 hover:text-gray-800 font-medium text-xs md:text-sm transition-colors whitespace-nowrap"
                  >
                    Cancel
                  </button>
                  <button 
                    v-if="permissions?.delete"
                    type="button"
                    @click="deletePayment"
                    class="px-3 py-1.5 md:px-4 md:py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium text-xs md:text-sm transition-colors flex items-center space-x-2 whitespace-nowrap"
                  >
                    <TrashIcon class="w-3 h-3 md:w-4 md:h-4" />
                    <span>Delete Payment</span>
                  </button>
                  <button 
                    type="submit"
                    :disabled="form.processing"
                    class="bg-yellow-600 hover:bg-yellow-700 disabled:bg-yellow-400 text-white px-3 py-1.5 md:px-6 md:py-2 rounded-lg font-semibold transition-all duration-200 text-xs md:text-sm shadow hover:shadow-lg disabled:cursor-not-allowed flex items-center space-x-2 whitespace-nowrap"
                  >
                    <PencilIcon class="w-3 h-3 md:w-4 md:h-4" />
                    <span v-if="form.processing">Updating...</span>
                    <span v-else>Update Payment</span>
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
import { reactive, ref, computed, onMounted, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  XMarkIcon,
  CurrencyDollarIcon,
  PencilIcon,
  TrashIcon,
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
  },
  customerId: {
    type: [String, Number],
    default: null
  }
})

// Get page props for flash messages
const page = usePage()

// State
const isSidebarOpen = ref(false)
const form = reactive({
  amount: '',
  method: '',
  schedule: '',
  payment_date: '',
  remarks: '',
  customer_type: props.customerType,
  processing: false
})

// Debug logging
console.log('Edit.vue props received:', {
  customer: props.customer,
  customerId: props.customerId,
  customerType: props.customerType,
  payment: props.payment
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
  
  console.log('Edit.vue mounted')
  console.log('Initial props:', {
    customer: props.customer,
    customerId: props.customerId,
    customerType: props.customerType,
    payment: props.payment
  })
  
  // Initialize form with payment data
  if (props.payment) {
    form.amount = props.payment.amount || ''
    form.method = props.payment.method || ''
    form.schedule = props.payment.schedule || ''
    form.payment_date = props.payment.payment_date ? formatDateForInput(props.payment.payment_date) : ''
    form.remarks = props.payment.remarks || ''
    form.customer_type = props.customerType
  }
  
  return () => {
    window.removeEventListener('sidebar:state-changed', handleSidebarStateChange)
  }
})

// Watch for props changes
watch(() => props.customer, (newCustomer) => {
  console.log('Customer prop changed:', newCustomer)
}, { deep: true })

watch(() => props.customerId, (newCustomerId) => {
  console.log('customerId prop changed:', newCustomerId)
})

// Actions
const goBackToPotentialCustomer = () => {
  console.log('goBackToPotentialCustomer called')
  console.log('Current customer:', props.customer)
  console.log('Current customerId:', props.customerId)
  console.log('Current customerType:', props.customerType)
  
  // Try multiple approaches to get the correct customer ID
  let targetCustomerId = null
  
  // First, try to get the ID from the customer object
  if (props.customer?.id) {
    targetCustomerId = props.customer.id
    console.log('Using customer.id:', targetCustomerId)
  }
  // If not, try the customerId prop
  else if (props.customerId) {
    targetCustomerId = props.customerId
    console.log('Using customerId prop:', targetCustomerId)
  }
  // If still not, try to get it from the payment
  else if (props.payment?.potential_customer_id) {
    targetCustomerId = props.payment.potential_customer_id
    console.log('Using payment.potential_customer_id:', targetCustomerId)
  }
  else if (props.payment?.customer_id) {
    targetCustomerId = props.payment.customer_id
    console.log('Using payment.customer_id:', targetCustomerId)
  }
  
  if (targetCustomerId) {
    console.log('Navigating to potential customer:', targetCustomerId)
    
    // Check if this is a potential customer or regular customer
    // For potential customers, we use /admin/potential-customers/{id}
    // For regular customers, we use /admin/customers/{id}
    
    if (props.customerType === 'potential') {
      router.get(`/admin/potential-customers/${targetCustomerId}`)
    } else {
      // If it's a customer type but we want to go to potential customer,
      // we need to find if this customer has a potential_customer_id
      if (props.customer?.potential_customer_id) {
        router.get(`/admin/potential-customers/${props.customer.potential_customer_id}`)
      } else {
        router.get(`/admin/customers/${targetCustomerId}`)
      }
    }
  } else {
    console.error('No customer ID found to navigate back to')
    // Fallback to potential customers list
    router.get('/admin/potential-customers')
  }
}

const submitForm = () => {
  form.processing = true
  
  // Include customer_type in the form data
  const formData = {
    ...form,
    customer_type: props.customerType
  }
  
  console.log('Submitting form with customerId:', props.customerId)
  
  // Always use the potential customer route
  if (props.customerId) {
    router.put(`/admin/potential-customers/${props.customerId}/payments/${props.payment?.id}`, formData, {
      preserveScroll: false,
      preserveState: false,
      onError: (errors) => {
        console.error('Form submission error:', errors)
        form.processing = false
      },
      onSuccess: () => {
        console.log('Form submitted successfully')
        form.processing = false
        // The controller will redirect back to potential customer
      }
    })
  } else {
    console.error('No customerId available for form submission')
    form.processing = false
  }
}

const deletePayment = () => {
  if (confirm('Are you sure you want to delete this payment? This action cannot be undone.')) {
    console.log('Deleting payment with customerId:', props.customerId)
    
    // Always delete using potential customer route
    if (props.customerId) {
      router.delete(`/admin/potential-customers/${props.customerId}/payments/${props.payment?.id}`, {
        preserveScroll: false,
        preserveState: false,
        onSuccess: () => {
          console.log('Payment deleted successfully')
          // The controller will redirect back to potential customer
        },
        onError: (error) => {
          console.error('Delete payment error:', error)
        }
      })
    } else {
      console.error('No customerId available for delete')
    }
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
</style>