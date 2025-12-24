<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar with hamburger menu -->
    <Sidebar :tables="tables" />
    
    <div class="flex-1 min-w-0 flex flex-col overflow-hidden w-full">
      <!-- Header with hamburger menu for mobile -->
      <div class="sticky top-0 z-40 bg-white shadow-sm border-b border-gray-200">
        <!-- Mobile Header with Hamburger -->
        <div class="lg:hidden">
          <div class="flex items-center justify-between px-4 py-3">
            <!-- Hamburger Menu Button -->
            <button
              @click="toggleSidebar"
              class="p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
              aria-label="Toggle sidebar"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
              </svg>
            </button>
            
            <!-- Center: Title -->
            <div class="flex-1 text-center min-w-0">
              <h1 class="text-lg font-semibold text-gray-900 flex items-center justify-center gap-2 truncate">
                <span class="truncate">Edit Proposal</span>
                <div class="w-5 h-5 bg-gradient-to-r from-blue-400/30 to-purple-400/30 rounded flex items-center justify-center flex-shrink-0">
                  <DocumentTextIcon class="w-3 h-3 text-blue-600/70" />
                </div>
              </h1>
            </div>
            
            <!-- Right spacer for balance -->
            <div class="w-10"></div>
          </div>
        </div>

        <!-- Desktop Header -->
        <div class="hidden lg:block">
          <div class="px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
              <div class="flex items-center space-x-4">
                <button 
                  type="button"
                  @click="goBackToCustomer"
                  class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                >
                  <ArrowLeftIcon class="w-4 h-4" />
                  <span>Back to Customer</span>
                </button>
                <div>
                  <h1 class="text-2xl font-bold text-gray-900">Edit Proposal</h1>
                  <p class="text-gray-600 mt-1">Update proposal details</p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600">
                  Customer: {{ proposal.potential_customer?.potential_customer_name || 'Unknown' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 overflow-auto">
        <div class="p-3 md:p-4 lg:p-6">
          <div class="max-w-4xl mx-auto">
            <!-- Form Container -->
            <div class="bg-white rounded-xl md:rounded-lg shadow-sm border border-gray-200">
              <div class="px-4 md:px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-gray-50">
                <h3 class="text-base md:text-lg font-semibold text-gray-900">Edit Proposal Details</h3>
                <p class="text-gray-600 text-xs md:text-sm mt-1">
                  For: {{ proposal.potential_customer?.potential_customer_name || 'Unknown Customer' }}
                </p>
              </div>

              <form @submit.prevent="submitForm" class="p-4 md:p-6 space-y-4 md:space-y-6">
                <!-- Title -->
                <div>
                  <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">
                    Proposal Title *
                  </label>
                  <input
                    type="text"
                    v-model="form.title"
                    required
                    placeholder="Enter proposal title"
                    class="w-full px-3 py-2 md:px-4 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                  />
                </div>

                <!-- Description -->
                <div>
                  <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">
                    Description *
                  </label>
                  <textarea
                    v-model="form.description"
                    required
                    rows="4"
                    placeholder="Enter proposal description"
                    class="w-full px-3 py-2 md:px-4 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none text-sm"
                  ></textarea>
                </div>

                <!-- Price -->
                <div>
                  <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">
                    Price ($) *
                  </label>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    v-model="form.price"
                    required
                    placeholder="0.00"
                    class="w-full px-3 py-2 md:px-4 md:py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                  />
                </div>

                <!-- Current File -->
                <div v-if="proposal.file">
                  <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">
                    Current File
                  </label>
                  <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <DocumentIcon class="w-5 h-5 text-blue-600" />
                    <span class="text-sm text-gray-700 flex-1 truncate">{{ getFileName(proposal.file) }}</span>
                    <button
                      type="button"
                      @click="downloadFile"
                      class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-medium transition-colors flex items-center space-x-1 whitespace-nowrap"
                    >
                      <ArrowDownTrayIcon class="w-3 h-3" />
                      <span>Download</span>
                    </button>
                  </div>
                </div>

                <!-- File Upload -->
                <div>
                  <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">
                    {{ proposal.file ? 'Replace File' : 'Upload File' }} (Optional)
                  </label>
                  <input
                    type="file"
                    @change="handleFileUpload"
                    accept=".pdf,.doc,.docx"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700"
                  />
                  <p class="text-xs text-gray-500 mt-1">Accepted formats: PDF, DOC, DOCX (Max: 2MB)</p>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-wrap justify-end gap-2 pt-4 md:pt-6 border-t border-gray-200">
                  <button
                    type="button"
                    @click="goBackToCustomer"
                    class="px-3 py-1.5 md:px-4 md:py-2 text-gray-600 hover:text-gray-800 font-medium text-xs md:text-sm transition-colors"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-3 py-1.5 md:px-6 md:py-2 rounded-lg font-semibold transition-all duration-200 text-xs md:text-sm shadow hover:shadow-lg disabled:cursor-not-allowed whitespace-nowrap"
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
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  DocumentTextIcon,
  DocumentIcon,
  ArrowLeftIcon,
  ArrowDownTrayIcon
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
  permissions: {
    type: Object,
    default: () => ({})
  }
})

// State
const form = reactive({
  title: props.proposal.title,
  description: props.proposal.description,
  price: props.proposal.price,
  file: null,
  processing: false
})

const isSidebarOpen = ref(false)

// Helper methods
const getFileName = (filePath) => {
  return filePath ? filePath.split('/').pop() : ''
}

const handleFileUpload = (event) => {
  form.file = event.target.files[0]
}

const downloadFile = () => {
  window.open(`/admin/proposals/${props.proposal.id}/download`, '_blank')
}

// Sidebar toggle
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
  window.dispatchEvent(new CustomEvent('toggle-sidebar', { detail: { isOpen: isSidebarOpen.value } }))
}

// Navigation
const goBackToCustomer = () => {
  if (props.proposal?.potential_customer?.id) {
    router.get(`/admin/potential-customers/${props.proposal.potential_customer.id}`)
  } else {
    router.get('/admin/potential-customers')
  }
}

// Form submission
const submitForm = () => {
  const formData = new FormData()
  formData.append('title', form.title)
  formData.append('description', form.description)
  formData.append('price', form.price)
  formData.append('_method', 'PUT')
  if (form.file) {
    formData.append('file', form.file)
  }

  form.processing = true

  router.post(`/admin/proposals/${props.proposal.id}`, formData, {
    preserveScroll: true,
    onSuccess: () => {
      // Go back to customer after successful update
      if (props.proposal?.potential_customer?.id) {
        router.get(`/admin/potential-customers/${props.proposal.potential_customer.id}`)
      }
    },
    onError: (errors) => {
      console.error('Update error:', errors)
    },
    onFinish: () => {
      form.processing = false
    }
  })
}
</script>

<style scoped>
/* Responsive styles */
@media (max-width: 767px) {
  .mobile-header {
    padding-left: 4px;
    padding-right: 4px;
  }
  
  .mobile-title {
    font-size: 0.875rem;
  }
}

/* Form input focus styles */
input:focus, textarea:focus {
  outline: none;
  ring-width: 2px;
}
</style>