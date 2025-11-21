<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-teal-50">
        <!-- Header -->
        <div class="bg-white border-b border-blue-200 px-6 py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">Create Contract</h1>
              <p class="text-slate-600 mt-2">Create a new customer contract</p>
            </div>
            <button 
              @click="goBack"
              class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
            >
              <ArrowLeftIcon class="w-5 h-5" />
              <span>Back to Contracts</span>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg border border-blue-200 p-8">
              <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Proposal Selection -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Select Proposal *</label>
                    <select 
                      v-model="form.proposal_id"
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200"
                      required
                    >
                      <option value="">Select a proposal</option>
                      <option v-for="proposal in proposals" :key="proposal.id" :value="proposal.id">
                        {{ proposal.title }} - {{ proposal.potential_customer?.potential_customer_name }} (${{ proposal.price }})
                      </option>
                    </select>
                    <div v-if="form.errors.proposal_id" class="text-red-600 text-sm mt-2">
                      {{ form.errors.proposal_id }}
                    </div>
                  </div>

                  <!-- Contract Title -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Contract Title *</label>
                    <input 
                      v-model="form.contract_title"
                      type="text" 
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200"
                      placeholder="Enter contract title"
                      required
                    >
                    <div v-if="form.errors.contract_title" class="text-red-600 text-sm mt-2">
                      {{ form.errors.contract_title }}
                    </div>
                  </div>

                  <!-- Contract Description -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Contract Description *</label>
                    <textarea 
                      v-model="form.contract_description"
                      rows="4"
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm transition-all duration-200 resize-none"
                      placeholder="Describe the contract terms and conditions..."
                      required
                    ></textarea>
                    <div v-if="form.errors.contract_description" class="text-red-600 text-sm mt-2">
                      {{ form.errors.contract_description }}
                    </div>
                  </div>

                  <!-- Total Value -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Total Value ($) *</label>
                    <input 
                      v-model="form.total_value"
                      type="number" 
                      step="0.01"
                      min="0"
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm transition-all duration-200"
                      placeholder="0.00"
                      required
                    >
                    <div v-if="form.errors.total_value" class="text-red-600 text-sm mt-2">
                      {{ form.errors.total_value }}
                    </div>
                  </div>

                  <!-- Payment Terms -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Payment Terms *</label>
                    <input 
                      v-model="form.payment_terms"
                      type="text" 
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200"
                      placeholder="e.g., Net 30, 50% upfront"
                      required
                    >
                    <div v-if="form.errors.payment_terms" class="text-red-600 text-sm mt-2">
                      {{ form.errors.payment_terms }}
                    </div>
                  </div>

                  <!-- Start Date -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Start Date *</label>
                    <input 
                      v-model="form.start_date"
                      type="date" 
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200"
                      required
                    >
                    <div v-if="form.errors.start_date" class="text-red-600 text-sm mt-2">
                      {{ form.errors.start_date }}
                    </div>
                  </div>

                  <!-- End Date -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">End Date *</label>
                    <input 
                      v-model="form.end_date"
                      type="date" 
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm transition-all duration-200"
                      required
                    >
                    <div v-if="form.errors.end_date" class="text-red-600 text-sm mt-2">
                      {{ form.errors.end_date }}
                    </div>
                  </div>

                  <!-- File Upload -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Contract File (Optional)</label>
                    <input 
                      type="file"
                      @change="handleFileUpload"
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm transition-all duration-200"
                      accept=".pdf,.doc,.docx"
                    >
                    <div class="text-xs text-slate-500 mt-2">
                      Supported formats: PDF, DOC, DOCX (Max: 10MB)
                    </div>
                    <div v-if="form.errors.file" class="text-red-600 text-sm mt-2">
                      {{ form.errors.file }}
                    </div>
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-slate-200">
                  <button 
                    type="button"
                    @click="goBack"
                    class="px-6 py-3 text-slate-600 hover:text-slate-800 font-semibold text-sm transition-colors"
                  >
                    Cancel
                  </button>
                  <button 
                    type="submit"
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-700 hover:to-teal-700 disabled:from-slate-400 disabled:to-slate-500 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl flex items-center space-x-2"
                  >
                    <PlusIcon class="w-5 h-5" />
                    <span>{{ form.processing ? 'Creating...' : 'Create Contract' }}</span>
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
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { 
  ArrowLeftIcon, 
  PlusIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proposals: Array,
  tables: {
    type: Array,
    default: () => []
  },
  permissions: {
    type: Object,
    default: () => ({})
  }
})

const form = useForm({
  proposal_id: '',
  contract_title: '',
  contract_description: '',
  total_value: '',
  start_date: '',
  end_date: '',
  payment_terms: '',
  file: null
})

const handleFileUpload = (event) => {
  form.file = event.target.files[0]
}

const submit = () => {
  form.post('/admin/contracts', {
    onSuccess: () => {
      router.get('/admin/contracts')
    }
  })
}

const goBack = () => {
  router.get('/admin/contracts')
}
</script>