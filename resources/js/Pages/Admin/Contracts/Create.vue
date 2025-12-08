<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-teal-50 flex">
    <Sidebar :tables="tables" />
    
    <!-- Main content area -->
    <div class="flex-1">
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <div class="space-y-2">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
                <DocumentTextIcon class="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 class="text-3xl font-bold text-slate-800">Create Contract</h1>
                <p class="text-slate-600 text-lg">Create a new contract for customers</p>
              </div>
            </div>
          </div>
          <button 
            @click="visitIndex"
            class="group bg-white/80 backdrop-blur-sm border border-blue-200 text-blue-700 hover:bg-blue-50 px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center space-x-3 transform hover:-translate-y-0.5"
          >
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
              <ArrowLeftIcon class="w-4 h-4 text-blue-600" />
            </div>
            <span>Back to Contracts</span>
          </button>
        </div>

        <!-- Form -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-teal-50">
            <h3 class="text-lg font-semibold text-slate-800">Contract Details</h3>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-6">
            <!-- Customer -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Customer *
              </label>
              <select
                v-model="form.customer_id"
                required
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
              >
                <option value="">Select a customer</option>
                <option 
                  v-for="customer in customers" 
                  :key="customer.id" 
                  :value="customer.id"
                >
                  {{ customer.name }} ({{ customer.email }})
                </option>
              </select>
              <div v-if="form.errors.customer_id" class="text-red-500 text-sm mt-1">{{ form.errors.customer_id }}</div>
            </div>

            <!-- Proposal (Optional) -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Based on Proposal (Optional)
              </label>
              <select
                v-model="form.proposal_id"
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
              >
                <option value="">Select a proposal (optional)</option>
                <option 
                  v-for="proposal in proposals" 
                  :key="proposal.id" 
                  :value="proposal.id"
                >
                  {{ proposal.title }} - ${{ proposal.price }} ({{ proposal.potential_customer?.potential_customer_name }})
                </option>
              </select>
              <div v-if="form.errors.proposal_id" class="text-red-500 text-sm mt-1">{{ form.errors.proposal_id }}</div>
            </div>

            <!-- Contract Title -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Contract Title *
              </label>
              <input
                type="text"
                v-model="form.contract_title"
                required
                placeholder="Enter contract title"
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
              />
              <div v-if="form.errors.contract_title" class="text-red-500 text-sm mt-1">{{ form.errors.contract_title }}</div>
            </div>

            <!-- Contract Description -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Description *
              </label>
              <textarea
                v-model="form.contract_description"
                required
                rows="4"
                placeholder="Enter contract description"
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
              ></textarea>
              <div v-if="form.errors.contract_description" class="text-red-500 text-sm mt-1">{{ form.errors.contract_description }}</div>
            </div>

            <!-- Total Value -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Total Value ($) *
              </label>
              <input
                type="number"
                step="0.01"
                min="0"
                v-model="form.total_value"
                required
                placeholder="0.00"
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
              />
              <div v-if="form.errors.total_value" class="text-red-500 text-sm mt-1">{{ form.errors.total_value }}</div>
            </div>

            <!-- Start and End Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  Start Date *
                </label>
                <input
                  type="date"
                  v-model="form.start_date"
                  required
                  class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                />
                <div v-if="form.errors.start_date" class="text-red-500 text-sm mt-1">{{ form.errors.start_date }}</div>
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">
                  End Date *
                </label>
                <input
                  type="date"
                  v-model="form.end_date"
                  required
                  class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
                />
                <div v-if="form.errors.end_date" class="text-red-500 text-sm mt-1">{{ form.errors.end_date }}</div>
              </div>
            </div>

            <!-- Payment Terms -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Payment Terms *
              </label>
              <textarea
                v-model="form.payment_terms"
                required
                rows="3"
                placeholder="Enter payment terms and conditions"
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
              ></textarea>
              <div v-if="form.errors.payment_terms" class="text-red-500 text-sm mt-1">{{ form.errors.payment_terms }}</div>
            </div>

            <!-- File Upload -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Contract File (Optional)
              </label>
              <input
                type="file"
                @change="handleFileUpload"
                accept=".pdf,.doc,.docx"
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              />
              <p class="text-sm text-slate-500 mt-2">Accepted formats: PDF, DOC, DOCX (Max: 2MB)</p>
              <div v-if="form.errors.file" class="text-red-500 text-sm mt-1">{{ form.errors.file }}</div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-blue-100">
              <button
                type="button"
                @click="visitIndex"
                class="px-6 py-3 border border-slate-300 text-slate-700 hover:bg-slate-50 rounded-xl font-semibold transition-all duration-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl font-semibold hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:shadow-none"
              >
                <span v-if="form.processing">Creating...</span>
                <span v-else>Create Contract</span>
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
  DocumentTextIcon,
  ArrowLeftIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  tables: {
    type: Array,
    default: () => []
  },
  customers: {
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
  selectedCustomerId: {
    type: String,
    default: ''
  }
})

const form = useForm({
  customer_id: props.selectedCustomerId || '',
  proposal_id: '',
  contract_title: '',
  contract_description: '',
  total_value: '',
  start_date: '',
  end_date: '',
  payment_terms: '',
  file: null,
})

const handleFileUpload = (event) => {
  form.file = event.target.files[0]
}

const submitForm = () => {
  form.post('/admin/contracts', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    }
  })
}

const visitIndex = () => {
  router.get('/admin/contracts')
}
</script>