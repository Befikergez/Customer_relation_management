<template>
  <!-- Modal Backdrop -->
  <div v-if="isOpen" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
      <!-- Background overlay -->
      <div class="fixed inset-0 transition-opacity" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-500 opacity-75" @click="closeModal"></div>
      </div>

      <!-- Modal -->
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6 pb-4 border-b px-6 pt-6">
          <div>
            <h3 class="text-xl font-bold text-gray-900">
              {{ isEditMode ? 'Edit Contract' : 'Create Contract' }}
            </h3>
            <p class="text-gray-600 text-sm mt-1">
              {{ isEditMode ? 'Update contract details' : 'Create a new contract for ' + customer?.name }}
            </p>
          </div>
          <button
            @click="closeModal"
            class="p-2 hover:bg-gray-100 rounded-full transition-colors"
          >
            <XMarkIcon class="w-5 h-5 text-gray-500" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="space-y-6 max-h-[70vh] overflow-y-auto px-6 pb-6">
          <!-- Contract Details -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Customer ID (hidden) -->
            <input type="hidden" v-model="form.customer_id" />
            
            <!-- Contract Title -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Contract Title *
              </label>
              <input
                type="text"
                v-model="form.contract_title"
                required
                placeholder="Enter contract title"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': form.errors.contract_title }"
              />
              <p v-if="form.errors.contract_title" class="mt-1 text-sm text-red-600">
                {{ form.errors.contract_title }}
              </p>
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Contract Description *
              </label>
              <textarea
                v-model="form.contract_description"
                rows="3"
                required
                placeholder="Describe the contract terms and conditions..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                :class="{ 'border-red-300': form.errors.contract_description }"
              ></textarea>
              <p v-if="form.errors.contract_description" class="mt-1 text-sm text-red-600">
                {{ form.errors.contract_description }}
              </p>
            </div>

            <!-- Total Value -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Total Value *
              </label>
              <input
                type="number"
                step="0.01"
                min="0"
                v-model="form.total_value"
                required
                placeholder="0.00"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': form.errors.total_value }"
              />
              <p v-if="form.errors.total_value" class="mt-1 text-sm text-red-600">
                {{ form.errors.total_value }}
              </p>
            </div>

           

            <!-- Start Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Start Date *
              </label>
              <input
                type="date"
                v-model="form.start_date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': form.errors.start_date }"
              />
              <p v-if="form.errors.start_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.start_date }}
              </p>
            </div>

            <!-- End Date -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                End Date *
              </label>
              <input
                type="date"
                v-model="form.end_date"
                required
                :min="form.start_date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :class="{ 'border-red-300': form.errors.end_date }"
              />
              <p v-if="form.errors.end_date" class="mt-1 text-sm text-red-600">
                {{ form.errors.end_date }}
              </p>
            </div>

            <!-- Payment Terms -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Payment Terms *
              </label>
              <textarea
                v-model="form.payment_terms"
                rows="3"
                required
                placeholder="Describe payment schedule, methods, and terms..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                :class="{ 'border-red-300': form.errors.payment_terms }"
              ></textarea>
              <p v-if="form.errors.payment_terms" class="mt-1 text-sm text-red-600">
                {{ form.errors.payment_terms }}
              </p>
            </div>

            <!-- File Upload -->
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Contract File (Optional)
              </label>
              <div class="flex items-center space-x-4">
                <input
                  type="file"
                  ref="fileInput"
                  @change="handleFileChange"
                  accept=".pdf,.doc,.docx"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />
                <div v-if="form.file && typeof form.file === 'string'" class="text-sm text-gray-600">
                  {{ form.file.split('/').pop() }}
                </div>
                <div v-else-if="form.file" class="text-sm text-gray-600">
                  {{ form.file.name }}
                </div>
              </div>
              <p v-if="form.errors.file" class="mt-1 text-sm text-red-600">
                {{ form.errors.file }}
              </p>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium text-sm transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.processing"
              class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 disabled:from-teal-400 disabled:to-teal-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
            >
              <span v-if="form.processing">
                {{ isEditMode ? 'Updating...' : 'Creating...' }}
              </span>
              <span v-else>
                {{ isEditMode ? 'Update Contract' : 'Create Contract' }}
              </span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  isOpen: Boolean,
  customer: Object,
  contract: Object
})

const emit = defineEmits(['close', 'success'])

// Check if we're in edit mode
const isEditMode = computed(() => !!props.contract)

// File input ref
const fileInput = ref(null)

// Form
const form = reactive({
  customer_id: props.customer?.id || '',
  contract_title: props.contract?.contract_title || '',
  contract_description: props.contract?.contract_description || '',
  total_value: props.contract?.total_value || '',
  start_date: props.contract?.start_date ? props.contract.start_date.split(' ')[0] : '',
  end_date: props.contract?.end_date ? props.contract.end_date.split(' ')[0] : '',
  payment_terms: props.contract?.payment_terms || '',
  proposal_id: props.contract?.proposal_id || '',
  file: props.contract?.file || null,
  processing: false,
  errors: {}
})

// Close modal
const closeModal = () => {
  emit('close')
  resetForm()
}

// Reset form
const resetForm = () => {
  form.customer_id = props.customer?.id || ''
  form.contract_title = ''
  form.contract_description = ''
  form.total_value = ''
  form.start_date = ''
  form.end_date = ''
  form.payment_terms = ''
  form.proposal_id = ''
  form.file = null
  form.processing = false
  form.errors = {}
  
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

// Handle file change
const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    form.file = file
  }
}

// Submit form
const submitForm = async () => {
  form.processing = true
  form.errors = {}
  
  // Prepare form data for file upload
  const formData = new FormData()
  formData.append('customer_id', form.customer_id)
  formData.append('contract_title', form.contract_title)
  formData.append('contract_description', form.contract_description)
  formData.append('total_value', form.total_value)
  formData.append('start_date', form.start_date)
  formData.append('end_date', form.end_date)
  formData.append('payment_terms', form.payment_terms)
  formData.append('proposal_id', form.proposal_id || '')
  formData.append('redirect_to_customer', 'true')
  
  if (form.file && typeof form.file !== 'string') {
    formData.append('file', form.file)
  }
  
  try {
    if (isEditMode.value) {
      // Use router.patch for updating contracts
      await router.patch(`/admin/contracts/${props.contract.id}`, formData, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          emit('success', 'Contract updated successfully!')
          closeModal()
        },
        onError: (errors) => {
          form.processing = false
          form.errors = errors || {}
          console.error('Update error:', errors)
        }
      })
    } else {
      // Create new contract
      await router.post('/admin/contracts', formData, {
        preserveScroll: true,
        preserveState: false,
        onSuccess: () => {
          emit('success', 'Contract created successfully!')
          closeModal()
        },
        onError: (errors) => {
          form.processing = false
          form.errors = errors || {}
          console.error('Create error:', errors)
        }
      })
    }
    
  } catch (error) {
    form.processing = false
    console.error('Form submission error:', error)
    form.errors = { general: 'An error occurred. Please try again.' }
  }
}

// Watch for modal open/close to reset form
watch(() => props.isOpen, (isOpen) => {
  if (!isOpen) {
    resetForm()
  }
})

// Watch for contract prop changes
watch(() => props.contract, (newContract) => {
  if (newContract) {
    form.customer_id = newContract.customer_id || props.customer?.id || ''
    form.contract_title = newContract.contract_title || ''
    form.contract_description = newContract.contract_description || ''
    form.total_value = newContract.total_value || ''
    form.start_date = newContract.start_date ? newContract.start_date.split(' ')[0] : ''
    form.end_date = newContract.end_date ? newContract.end_date.split(' ')[0] : ''
    form.payment_terms = newContract.payment_terms || ''
    form.proposal_id = newContract.proposal_id || ''
    form.file = newContract.file || null
  }
})

// Watch for customer prop changes
watch(() => props.customer, (newCustomer) => {
  if (newCustomer && !isEditMode.value) {
    form.customer_id = newCustomer.id || ''
  }
})
</script>

<style scoped>
/* Add any custom styles if needed */
</style>