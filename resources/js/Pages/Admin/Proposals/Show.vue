<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-blue-50">
        <!-- Header -->
        <div class="bg-white border-b border-teal-200 px-4 sm:px-6 py-4 sm:py-6">
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-0">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-teal-600 to-blue-600 bg-clip-text text-transparent">Proposal Details</h1>
              <p class="text-slate-600 mt-1 sm:mt-2 text-sm sm:text-base">Review proposal information and manage status</p>
            </div>
            <button 
              @click="goBackToCustomer"
              class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg w-fit"
            >
              <ArrowLeftIcon class="w-4 h-4" />
              <span class="text-sm">Back to Customer</span>
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="!proposal" class="flex items-center justify-center p-8">
          <div class="text-center">
            <div class="w-16 h-16 border-4 border-teal-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-slate-600">Loading proposal details...</p>
          </div>
        </div>

        <!-- Content -->
        <div v-else class="p-4 sm:p-6">
          <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-lg border border-teal-200 overflow-hidden">
              <!-- Header Banner -->
              <div class="bg-gradient-to-r from-teal-600 to-blue-600 text-white p-4 sm:p-6 md:p-8">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 sm:gap-0">
                  <div class="flex items-center space-x-3 sm:space-x-4">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white/20 rounded-xl sm:rounded-2xl flex items-center justify-center">
                      <DocumentTextIcon class="w-6 h-6 sm:w-8 sm:h-8 text-white" />
                    </div>
                    <div>
                      <h2 class="text-xl sm:text-2xl md:text-3xl font-bold">{{ proposal.title || 'Untitled Proposal' }}</h2>
                      <p class="text-teal-100 mt-1 sm:mt-2 text-sm sm:text-lg">Proposal for: {{ proposal.potential_customer?.potential_customer_name || 'Unknown Customer' }}</p>
                    </div>
                  </div>
                  <span :class="getProposalStatusBadgeClass(proposal)" class="px-3 py-2 sm:px-6 sm:py-3 rounded-full text-sm sm:text-lg font-semibold border border-white/30 w-fit">
                    {{ formatProposalStatus(proposal) }}
                  </span>
                </div>
              </div>

              <!-- Content -->
              <div class="p-4 sm:p-6 md:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                  <!-- Proposal Information -->
                  <div class="space-y-4 sm:space-y-6">
                    <h3 class="text-lg sm:text-xl font-semibold text-slate-800 border-b border-slate-200 pb-2 sm:pb-3">Proposal Information</h3>
                    
                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Title</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ proposal.title || 'Not provided' }}</p>
                    </div>

                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Description</p>
                      <p class="text-base sm:text-lg text-slate-900 leading-relaxed">{{ proposal.description || 'No description provided' }}</p>
                    </div>

                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Price</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">${{ proposal.price || '0.00' }}</p>
                    </div>

                    <div v-if="proposal.file">
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Attached File</p>
                      <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <DocumentIcon class="w-5 h-5 text-blue-600" />
                        <span class="text-sm text-slate-700 flex-1">{{ getFileName(proposal.file) }}</span>
                        <button
                          @click="downloadProposalFile(proposal.id)"
                          class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 transition-colors font-medium"
                        >
                          Download
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Customer Information -->
                  <div class="space-y-4 sm:space-y-6">
                    <h3 class="text-lg sm:text-xl font-semibold text-slate-800 border-b border-slate-200 pb-2 sm:pb-3">Customer Information</h3>
                    
                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Customer Name</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ proposal.potential_customer?.potential_customer_name || 'Not provided' }}</p>
                    </div>

                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Email Address</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ proposal.potential_customer?.email || 'Not provided' }}</p>
                    </div>

                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Phone Number</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ proposal.potential_customer?.phone || 'Not provided' }}</p>
                    </div>

                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Location</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ proposal.potential_customer?.location || 'Not provided' }}</p>
                    </div>

                    <!-- Customer Status -->
                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Customer Status</p>
                      <span :class="getCustomerStatusBadgeClass(proposal.potential_customer?.status)" class="px-2 py-1 rounded-full text-xs font-semibold">
                        {{ formatCustomerStatus(proposal.potential_customer?.status) }}
                      </span>
                    </div>

                    <!-- Customer Actions -->
                    <div v-if="proposal.potential_customer" class="pt-4 border-t border-slate-200">
                      <button 
                        @click="goToCustomerDetails"
                        class="w-full bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center space-x-2 shadow hover:shadow-lg"
                      >
                        <UserCircleIcon class="w-4 h-4" />
                        <span>View Customer Details</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Additional Information -->
                <div class="mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-slate-200">
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                    <div>
                      <h3 class="text-lg sm:text-xl font-semibold text-slate-800 mb-3 sm:mb-4">Created Information</h3>
                      <div class="space-y-3">
                        <div>
                          <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1">Created By</p>
                          <p class="text-sm font-semibold text-slate-900">{{ proposal.created_by?.name || 'System' }}</p>
                        </div>
                        <div>
                          <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1">Created At</p>
                          <p class="text-sm font-semibold text-slate-900">{{ formatDate(proposal.created_at) }}</p>
                        </div>
                        <div>
                          <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1">Last Updated</p>
                          <p class="text-sm font-semibold text-slate-900">{{ formatDate(proposal.updated_at) }}</p>
                        </div>
                      </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="md:col-span-2">
                      <h3 class="text-lg sm:text-xl font-semibold text-slate-800 mb-3 sm:mb-4">Status Timeline</h3>
                      <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                          <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <CheckIcon class="w-4 h-4 text-green-600" />
                          </div>
                          <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-900">Proposal Created</p>
                            <p class="text-xs text-slate-600">{{ formatDate(proposal.created_at) }}</p>
                          </div>
                        </div>

                        <div v-if="proposal.customer_approved_at" class="flex items-center space-x-3">
                          <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <CheckIcon class="w-4 h-4 text-green-600" />
                          </div>
                          <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-900">Approved by Customer</p>
                            <p class="text-xs text-slate-600">{{ formatDate(proposal.customer_approved_at) }}</p>
                          </div>
                        </div>

                        <div v-if="proposal.customer_rejected_at" class="flex items-center space-x-3">
                          <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <XMarkIcon class="w-4 h-4 text-red-600" />
                          </div>
                          <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-900">Rejected by Customer</p>
                            <p class="text-xs text-slate-600">{{ formatDate(proposal.customer_rejected_at) }}</p>
                            <p v-if="proposal.customer_review" class="text-xs text-slate-500 mt-1">{{ proposal.customer_review }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-2 mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-slate-200">
                  <!-- Edit Button -->
                  <button 
                    v-if="permissions.edit && !proposal.is_rejected && proposal.status === 'unsigned'"
                    @click="openEditModal"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg"
                  >
                    <PencilIcon class="w-4 h-4" />
                    <span>Edit Proposal</span>
                  </button>

                  <!-- Approve Button -->
                  <button 
                    v-if="permissions.approve && proposal.status === 'unsigned' && !proposal.is_rejected"
                    @click="approveProposal"
                    :disabled="loading"
                    class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg disabled:cursor-not-allowed"
                  >
                    <CheckIcon class="w-4 h-4" />
                    <span v-if="loading">Approving...</span>
                    <span v-else>Approve Proposal</span>
                  </button>

                  <!-- Reject Button -->
                  <button 
                    v-if="permissions.reject && proposal.status === 'unsigned' && !proposal.is_rejected"
                    @click="showRejectModal = true"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg"
                  >
                    <XMarkIcon class="w-4 h-4" />
                    <span>Reject Proposal</span>
                  </button>

                  <!-- Delete Button -->
                  <button 
                    v-if="permissions.delete"
                    @click="deleteProposal"
                    class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg"
                  >
                    <TrashIcon class="w-4 h-4" />
                    <span>Delete Proposal</span>
                  </button>

                  <!-- Back to Customer Button (Secondary) -->
                  <button 
                    @click="goBackToCustomer"
                    class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg"
                  >
                    <ArrowLeftIcon class="w-4 h-4" />
                    <span>Back to Customer</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Proposal Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-slate-200 shadow-xl">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
              <PencilIcon class="w-5 h-5 text-amber-600" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-slate-800">Edit Proposal</h3>
              <p class="text-slate-600 text-sm">Update proposal information</p>
            </div>
          </div>
          <button @click="closeEditModal" class="text-slate-400 hover:text-slate-600 transition-colors">
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>
        
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Title *</label>
            <input
              type="text"
              v-model="editForm.title"
              required
              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
              placeholder="Enter proposal title"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Description *</label>
            <textarea
              v-model="editForm.description"
              required
              rows="4"
              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent resize-none"
              placeholder="Enter proposal description"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">Price ($) *</label>
            <input
              type="number"
              step="0.01"
              min="0"
              v-model="editForm.price"
              required
              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
              placeholder="0.00"
            />
          </div>

          <div v-if="proposal.file">
            <label class="block text-sm font-medium text-slate-700 mb-2">Current File</label>
            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
              <DocumentIcon class="w-5 h-5 text-blue-600" />
              <span class="text-sm text-slate-700 flex-1">{{ getFileName(proposal.file) }}</span>
              <button
                type="button"
                @click="downloadProposalFile(proposal.id)"
                class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors"
              >
                Download
              </button>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-2">{{ proposal.file ? 'Replace File' : 'Upload File' }} (Optional)</label>
            <input
              type="file"
              @change="handleFileUpload"
              accept=".pdf,.doc,.docx"
              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-amber-50 file:text-amber-700"
            />
            <p class="text-xs text-slate-500 mt-1">PDF, DOC, DOCX (Max: 2MB)</p>
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200">
            <button 
              type="button"
              @click="closeEditModal"
              class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium text-sm transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="editForm.processing"
              class="bg-amber-600 hover:bg-amber-700 disabled:bg-amber-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
            >
              <span v-if="editForm.processing">Updating...</span>
              <span v-else>Update Proposal</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Reject Proposal Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md border border-slate-200 shadow-xl">
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
            <XMarkIcon class="w-5 h-5 text-red-600" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-800">Reject Proposal</h3>
            <p class="text-slate-600 text-sm">Please provide a reason for rejection</p>
          </div>
        </div>
        
        <textarea 
          v-model="rejectForm.reason"
          placeholder="Enter reason for rejecting this proposal..."
          class="w-full h-32 p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm resize-none"
          required
        ></textarea>
        
        <div class="flex justify-end space-x-3 mt-6">
          <button 
            @click="showRejectModal = false" 
            class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium text-sm transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="rejectProposal"
            :disabled="!rejectForm.reason.trim()"
            class="bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
          >
            Reject Proposal
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  CheckIcon,
  XMarkIcon,
  DocumentTextIcon,
  EyeIcon,
  UserCircleIcon,
  PencilIcon,
  PlusIcon,
  TrashIcon,
  DocumentIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proposal: {
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
  }
})

const showRejectModal = ref(false)
const showEditModal = ref(false)
const loading = ref(false)

const rejectForm = reactive({
  reason: ''
})

const editForm = reactive({
  title: '',
  description: '',
  price: '',
  file: null,
  processing: false
})

onMounted(() => {
  console.log('Proposal Show.vue mounted with proposal:', props.proposal)
  console.log('Proposal Show.vue permissions:', props.permissions)
})

// Helper methods
function getProposalStatusBadgeClass(proposal) {
  if (proposal.is_rejected) {
    return 'bg-red-100 text-red-800 border-red-200'
  }
  
  const classes = {
    unsigned: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    signed: 'bg-green-100 text-green-800 border-green-200'
  }
  return classes[proposal.status] || 'bg-slate-100 text-slate-800 border-slate-200'
}

function getCustomerStatusBadgeClass(status) {
  const classes = {
    draft: 'bg-blue-100 text-blue-800',
    proposal_sent: 'bg-rose-100 text-rose-800',
    accepted: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-slate-100 text-slate-800'
}

function formatProposalStatus(proposal) {
  if (proposal.is_rejected) {
    return 'Rejected'
  }
  
  const statusMap = {
    unsigned: 'Unsigned',
    signed: 'Signed'
  }
  return statusMap[proposal.status] || proposal.status
}

function formatCustomerStatus(status) {
  const statusMap = {
    draft: 'Draft',
    proposal_sent: 'Proposal Sent',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return statusMap[status] || status
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getFileName(filePath) {
  return filePath ? filePath.split('/').pop() : ''
}

// Navigation methods
const goBackToCustomer = () => {
  if (window.history.length > 1) {
    window.history.back()
  } else {
    router.get('/admin/potential-customers')
  }
}

const goToCustomerDetails = () => {
  if (props.proposal?.potential_customer?.id) {
    router.visit(`/admin/potential-customers/${props.proposal.potential_customer.id}`)
  }
}

// Action methods
const openEditModal = () => {
  if (!props.proposal || !props.proposal.id) return
  
  editForm.title = props.proposal.title || ''
  editForm.description = props.proposal.description || ''
  editForm.price = props.proposal.price || ''
  editForm.file = null
  editForm.processing = false
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
}

const handleFileUpload = (event) => {
  editForm.file = event.target.files[0]
}

const submitEdit = () => {
  if (!props.proposal || !props.proposal.id) return

  editForm.processing = true

  const formData = new FormData()
  formData.append('title', editForm.title)
  formData.append('description', editForm.description)
  formData.append('price', editForm.price)
  formData.append('_method', 'PUT')
  if (editForm.file) {
    formData.append('file', editForm.file)
  }

  router.post(`/admin/proposals/${props.proposal.id}`, formData, {
    preserveScroll: true,
    onSuccess: () => {
      closeEditModal()
      router.reload({ only: ['proposal'] })
    },
    onError: () => {
      editForm.processing = false
    }
  })
}

const approveProposal = async () => {
  if (!props.proposal || !props.proposal.id) {
    alert('Proposal data not loaded properly. Please refresh the page.')
    return
  }
  
  if (confirm('Are you sure you want to approve this proposal? This will mark it as signed and move the customer to customers table.')) {
    loading.value = true
    
    try {
      await router.post(`/admin/proposals/${props.proposal.id}/approve`, {}, {
        preserveScroll: false,
      })
    } catch (error) {
      console.error('Approve catch error:', error)
    } finally {
      loading.value = false
    }
  }
}
const rejectProposal = (proposalId, event) => {
  // Prevent any default behavior that might cause GET request
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  
  console.log('=== REJECT PROPOSAL CLICKED ===');
  console.log('Proposal ID:', proposalId);
  
  const reason = prompt('Please provide a reason for rejection:')
  if (reason) {
    console.log('Reject reason:', reason);
    console.log('Sending POST to:', `/admin/proposals/${proposalId}/reject`);
    
    router.post(`/admin/proposals/${proposalId}/reject`, { 
      reason: reason 
    }, {
      preserveScroll: true,
      onSuccess: () => {
        console.log('Reject request successful - page should reload');
        // The controller redirects back, so we need to reload to see changes
        router.reload({ only: ['potentialCustomer'] })
      },
      onError: (errors) => {
        console.error('Reject error details:', errors);
        alert('Failed to reject proposal. Please check console for details.')
      }
    })
  } else {
    console.log('No reason provided, cancelling reject');
  }
}

const rejectCustomer = () => {
  if (!props.potentialCustomer || !props.potentialCustomer.id) return
  
  console.log('=== REJECT CUSTOMER CLICKED ===');
  console.log('Customer ID:', props.potentialCustomer.id);
  console.log('Reject reason:', rejectForm.reason);
  
  router.post(`/admin/potential-customers/${props.potentialCustomer.id}/reject`, rejectForm, {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Customer reject request successful');
      showRejectModal.value = false
      rejectForm.reason = ''
      // The controller redirects back, so we need to reload to see changes
      router.reload({ only: ['potentialCustomer'] })
    },
    onError: (errors) => {
      console.error('Customer reject error:', errors);
      alert('Failed to reject customer. Please check console for details.')
    }
  })
}

const deleteProposal = () => {
  if (!props.proposal || !props.proposal.id) return
  
  if (confirm('Are you sure you want to delete this proposal?')) {
    router.delete(`/admin/proposals/${props.proposal.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        goBackToCustomer()
      }
    })
  }
}

const downloadProposalFile = (proposalId) => {
  window.location.href = `/admin/proposals/${proposalId}/download`
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>