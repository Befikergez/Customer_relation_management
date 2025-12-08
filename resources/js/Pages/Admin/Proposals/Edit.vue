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
                <h1 class="text-3xl font-bold text-slate-800">Edit Proposal</h1>
                <p class="text-slate-600 text-lg">Update proposal details</p>
              </div>
            </div>
          </div>
          <button 
            @click="goBackToCustomer"
            class="group bg-white/80 backdrop-blur-sm border border-blue-200 text-blue-700 hover:bg-blue-50 px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center space-x-3 transform hover:-translate-y-0.5"
          >
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
              <ArrowLeftIcon class="w-4 h-4 text-blue-600" />
            </div>
            <span>Back to customer</span>
          </button>
        </div>

        <!-- Form -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-teal-50">
            <h3 class="text-lg font-semibold text-slate-800">Edit Proposal Details</h3>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-6">
            <!-- Potential Customer -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Potential Customer *
              </label>
              <select
                v-model="form.potential_customer_id"
                required
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
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
            </div>

            <!-- Title -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Proposal Title *
              </label>
              <input
                type="text"
                v-model="form.title"
                required
                placeholder="Enter proposal title"
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
              />
            </div>

            <!-- Description -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Description *
              </label>
              <textarea
                v-model="form.description"
                required
                rows="4"
                placeholder="Enter proposal description"
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
              ></textarea>
            </div>

            <!-- Price -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Price ($) *
              </label>
              <input
                type="number"
                step="0.01"
                min="0"
                v-model="form.price"
                required
                placeholder="0.00"
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm"
              />
            </div>

            <!-- Current File -->
            <div v-if="proposal.file">
              <label class="block text-sm font-medium text-slate-700 mb-2">
                Current File
              </label>
              <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-xl">
                <DocumentIcon class="w-5 h-5 text-blue-600" />
                <span class="text-sm text-slate-700 flex-1">{{ getFileName(proposal.file) }}</span>
                <button
                  type="button"
                  @click="downloadFile"
                  class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors"
                >
                  Download
                </button>
              </div>
            </div>

            <!-- File Upload -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">
                {{ proposal.file ? 'Replace File' : 'Upload File' }} (Optional)
              </label>
              <input
                type="file"
                @change="handleFileUpload"
                accept=".pdf,.doc,.docx"
                class="w-full px-4 py-3 border border-blue-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/50 backdrop-blur-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              />
              <p class="text-sm text-slate-500 mt-2">Accepted formats: PDF, DOC, DOCX (Max: 2MB)</p>
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
                <span v-if="form.processing">Updating...</span>
                <span v-else>Update Proposal</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  DocumentTextIcon,
  DocumentIcon,
  ArrowLeftIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proposal: {
    type: Object,
    default: () => ({})
  },
  tables: {
    type: Array,
    default: () => []
  },
  potentialCustomers: {
    type: Array,
    default: () => []
  },
  permissions: {
    type: Object,
    default: () => ({})
  }
})

const form = reactive({
  potential_customer_id: props.proposal.potential_customer_id,
  title: props.proposal.title,
  description: props.proposal.description,
  price: props.proposal.price,
  file: null,
  processing: false
})

const getFileName = (filePath) => {
  return filePath.split('/').pop()
}

const handleFileUpload = (event) => {
  form.file = event.target.files[0]
}

const downloadFile = () => {
  router.get(`/admin/proposals/${props.proposal.id}/download`)
}

const submitForm = () => {
  const formData = new FormData()
  formData.append('potential_customer_id', form.potential_customer_id)
  formData.append('title', form.title)
  formData.append('description', form.description)
  formData.append('price', form.price)
  formData.append('_method', 'PUT')
  if (form.file) {
    formData.append('file', form.file)
  }

  form.processing = true

  router.post(`/admin/proposals/${props.proposal.id}`, formData, {
    onFinish: () => {
      form.processing = false
    }
  })
}

const visitIndex = () => {
  router.get('/admin/proposals')
}
</script>