<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-teal-50 flex">
    <Sidebar :tables="tables" />
    
    <!-- Main content area -->
    <div class="flex-1">
      <div class="p-4 sm:p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6 sm:mb-8">
          <div class="space-y-2">
            <div class="flex items-center space-x-3 sm:space-x-4">
              <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl sm:rounded-2xl flex items-center justify-center shadow-lg">
                <UserGroupIcon class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
              </div>
              <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-800">Create Customer</h1>
                <p class="text-slate-600 text-sm sm:text-lg">Add a new customer to your system</p>
              </div>
            </div>
          </div>
          <button 
            @click="visitIndex"
            class="group bg-white/80 backdrop-blur-sm border border-blue-200 text-blue-700 hover:bg-blue-50 px-4 sm:px-6 py-2 sm:py-3 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center space-x-2 sm:space-x-3 transform hover:-translate-y-0.5 w-fit"
          >
            <div class="w-6 h-6 sm:w-8 sm:h-8 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
              <ArrowLeftIcon class="w-3 h-3 sm:w-4 sm:h-4 text-blue-600" />
            </div>
            <span class="text-sm sm:text-base">Back to Customers</span>
          </button>
        </div>

        <!-- Form -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
          <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-teal-50">
            <h3 class="text-base sm:text-lg font-semibold text-slate-800">Customer Details</h3>
          </div>

          <form @submit.prevent="submitForm" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <!-- Name -->
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Customer Name *
                </label>
                <input
                  type="text"
                  v-model="form.name"
                  required
                  placeholder="Enter customer name"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                />
                <div v-if="form.errors.name" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.name }}</div>
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Email Address
                </label>
                <input
                  type="email"
                  v-model="form.email"
                  placeholder="Enter email address"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                />
                <div v-if="form.errors.email" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.email }}</div>
              </div>

              <!-- Phone -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Phone Number
                </label>
                <input
                  type="tel"
                  v-model="form.phone"
                  placeholder="Enter phone number"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                />
                <div v-if="form.errors.phone" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.phone }}</div>
              </div>

              <!-- Location -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Location
                </label>
                <input
                  type="text"
                  v-model="form.location"
                  placeholder="Enter location"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                />
                <div v-if="form.errors.location" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.location }}</div>
              </div>

              <!-- City -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  City
                </label>
                <select
                  v-model="form.city_id"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                >
                  <option value="">Select City</option>
                  <option v-for="city in cities" :key="city.id" :value="city.id">
                    {{ city.name }}
                  </option>
                </select>
                <div v-if="form.errors.city_id" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.city_id }}</div>
              </div>

              <!-- Subcity -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Subcity
                </label>
                <select
                  v-model="form.subcity_id"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                >
                  <option value="">Select Subcity</option>
                  <option v-for="subcity in subcities" :key="subcity.id" :value="subcity.id">
                    {{ subcity.name }}
                  </option>
                </select>
                <div v-if="form.errors.subcity_id" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.subcity_id }}</div>
              </div>
            </div>

            <!-- Payment Information -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <!-- Total Payment Amount -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Total Payment Amount
                </label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  v-model="form.total_payment_amount"
                  placeholder="0.00"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                />
                <div v-if="form.errors.total_payment_amount" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.total_payment_amount }}</div>
              </div>

              <!-- Payment Status -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Payment Status
                </label>
                <select
                  v-model="form.payment_status"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                >
                  <option value="not_paid">Not Paid</option>
                  <option value="pending">Pending</option>
                  <option value="half_paid">Half Paid</option>
                  <option value="paid">Paid</option>
                </select>
                <div v-if="form.errors.payment_status" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.payment_status }}</div>
              </div>
            </div>

            <!-- Salesperson Information -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <!-- Salesperson -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Salesperson (Optional)
                </label>
                <select
                  v-model="form.salesperson_id"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                >
                  <option value="">Select Salesperson</option>
                  <option v-for="salesperson in salespeople" :key="salesperson.id" :value="salesperson.id">
                    {{ salesperson.name }} ({{ salesperson.commission_rate }}%)
                  </option>
                </select>
                <div v-if="form.errors.salesperson_id" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.salesperson_id }}</div>
              </div>

              <!-- Commission Rate -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Commission Rate (%) (Optional)
                </label>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  max="100"
                  v-model="form.commission_rate"
                  placeholder="0.00"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                />
                <div v-if="form.errors.commission_rate" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.commission_rate }}</div>
              </div>
            </div>

            <!-- Source Information -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
              <!-- Potential Customer -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  From Potential Customer (Optional)
                </label>
                <select
                  v-model="form.potential_customer_id"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                >
                  <option value="">Select a potential customer</option>
                  <option 
                    v-for="customer in potentialCustomers" 
                    :key="customer.id" 
                    :value="customer.id"
                  >
                    {{ customer.potential_customer_name }} ({{ customer.email }})
                  </option>
                </select>
                <div v-if="form.errors.potential_customer_id" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.potential_customer_id }}</div>
              </div>

              <!-- Proposal -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  From Proposal (Optional)
                </label>
                <select
                  v-model="form.created_from_proposal_id"
                  class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                >
                  <option value="">Select a proposal</option>
                  <option 
                    v-for="proposal in proposals" 
                    :key="proposal.id" 
                    :value="proposal.id"
                  >
                    {{ proposal.title }} ({{ proposal.potential_customer?.potential_customer_name }})
                  </option>
                </select>
                <div v-if="form.errors.created_from_proposal_id" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.created_from_proposal_id }}</div>
              </div>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Status *
              </label>
              <select
                v-model="form.status"
                required
                class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
              >
                <option value="draft">Draft</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
              </select>
              <div v-if="form.errors.status" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.status }}</div>
            </div>

            <!-- Remarks -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Remarks
              </label>
              <textarea
                v-model="form.remarks"
                rows="3"
                placeholder="Enter any additional remarks or notes about this customer..."
                class="w-full px-3 sm:px-4 py-2 sm:py-3 border border-blue-200 rounded-lg sm:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm resize-none"
              ></textarea>
              <div v-if="form.errors.remarks" class="text-red-500 text-xs sm:text-sm mt-1">{{ form.errors.remarks }}</div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-3 sm:space-x-4 pt-4 sm:pt-6 border-t border-blue-100">
              <button
                type="button"
                @click="visitIndex"
                class="px-4 sm:px-6 py-2 sm:py-3 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-lg sm:rounded-xl font-semibold transition-all duration-200 text-sm sm:text-base"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-4 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-lg sm:rounded-xl font-semibold hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none text-sm sm:text-base"
              >
                <span v-if="form.processing">Creating...</span>
                <span v-else>Create Customer</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  UserGroupIcon,
  ArrowLeftIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  tables: {
    type: Array,
    default: () => []
  },
  potentialCustomers: {
    type: Array,
    default: () => []
  },
  proposals: {
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
  },
  salespeople: {
    type: Array,
    default: () => []
  }
})

const form = useForm({
  name: '',
  email: '',
  phone: '',
  location: '',
  remarks: '',
  status: 'draft',
  potential_customer_id: '',
  created_from_proposal_id: '',
  city_id: '',
  subcity_id: '',
  total_payment_amount: '',
  payment_status: 'not_paid',
  salesperson_id: '',
  commission_rate: '',
})

const submitForm = () => {
  form.post('/admin/customers', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    }
  })
}

const visitIndex = () => {
  router.get('/admin/customers')
}
</script>