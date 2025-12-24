<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar - With hamburger menu -->
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
                <span class="truncate">Proposal Details</span>
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
                  <h1 class="text-2xl font-bold text-gray-900">Proposal Details</h1>
                  <p class="text-gray-600 mt-1">Review proposal information and manage status</p>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <span :class="getProposalStatusBadgeClass(proposal)" class="px-4 py-2 rounded-full text-sm font-semibold">
                  {{ formatProposalStatus(proposal) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 overflow-auto">
        <div class="p-3 md:p-4 lg:p-6">
          <div class="max-w-7xl mx-auto">
            <!-- Loading State -->
            <div v-if="!proposal" class="flex items-center justify-center p-8">
              <div class="text-center">
                <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-gray-600">Loading proposal details...</p>
              </div>
            </div>

            <!-- Proposal Content -->
            <div v-else class="space-y-6">
              <!-- Proposal Overview Card -->
              <div class="bg-white rounded-xl md:rounded-lg shadow-sm border border-gray-200 p-4 md:p-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
                  <div class="flex items-start space-x-4">
                    <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl md:rounded-2xl flex items-center justify-center">
                      <DocumentTextIcon class="w-6 h-6 md:w-8 md:h-8 text-white" />
                    </div>
                    <div class="min-w-0">
                      <h2 class="text-lg md:text-xl lg:text-2xl font-bold text-gray-900 truncate">{{ proposal.title || 'Untitled Proposal' }}</h2>
                      <p class="text-gray-600 text-xs md:text-sm mt-1 truncate">
                        For: {{ proposal.potential_customer?.potential_customer_name || 'Unknown Customer' }}
                      </p>
                    </div>
                  </div>
                  <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <span :class="getProposalStatusBadgeClass(proposal)" class="px-4 py-2 rounded-full text-sm font-semibold w-fit">
                      {{ formatProposalStatus(proposal) }}
                    </span>
                    <button 
                      @click="goBackToCustomer"
                      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 text-sm whitespace-nowrap"
                    >
                      <ArrowLeftIcon class="w-4 h-4" />
                      <span>Back to Customer</span>
                    </button>
                  </div>
                </div>

                <!-- Proposal Details Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
                  <!-- Proposal Information -->
                  <div class="space-y-4 md:space-y-6">
                    <h3 class="text-base md:text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Proposal Information</h3>
                    
                    <div>
                      <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Title</label>
                      <p class="text-gray-900 font-medium text-sm md:text-base">{{ proposal.title || 'Not provided' }}</p>
                    </div>

                    <div>
                      <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Description</label>
                      <p class="text-gray-700 bg-gray-50 rounded-lg p-3 md:p-4 text-sm md:text-base">
                        {{ proposal.description || 'No description provided' }}
                      </p>
                    </div>

                    <div>
                      <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Price</label>
                      <p class="text-gray-900 font-bold text-base md:text-lg">${{ proposal.price || '0.00' }}</p>
                    </div>

                    <div v-if="proposal.file">
                      <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Attached File</label>
                      <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                        <DocumentIcon class="w-5 h-5 text-blue-600" />
                        <span class="text-sm text-gray-700 flex-1 truncate">{{ getFileName(proposal.file) }}</span>
                        <button
                          @click="downloadProposalFile(proposal.id)"
                          class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-xs font-medium transition-colors flex items-center space-x-1 whitespace-nowrap"
                        >
                          <ArrowDownTrayIcon class="w-3 h-3" />
                          <span>Download</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Customer Information -->
                  <div class="space-y-4 md:space-y-6">
                    <h3 class="text-base md:text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">Customer Information</h3>
                    
                    <div>
                      <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Customer Name</label>
                      <p class="text-gray-900 font-medium text-sm md:text-base truncate">{{ proposal.potential_customer?.potential_customer_name || 'Not provided' }}</p>
                    </div>

                    <div>
                      <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Email Address</label>
                      <p class="text-gray-900 font-medium text-sm md:text-base truncate">{{ proposal.potential_customer?.email || 'Not provided' }}</p>
                    </div>

                    <div>
                      <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Phone Number</label>
                      <p class="text-gray-900 font-medium text-sm md:text-base truncate">{{ proposal.potential_customer?.phone || 'Not provided' }}</p>
                    </div>

                    <div>
                      <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Location</label>
                      <p class="text-gray-900 font-medium text-sm md:text-base">{{ proposal.potential_customer?.text_location || proposal.potential_customer?.map_location || 'Not provided' }}</p>
                    </div>

                    <!-- Customer Status -->
                    <div>
                      <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Customer Status</label>
                      <span :class="getCustomerStatusBadgeClass(proposal.potential_customer?.status)" class="px-3 py-1.5 rounded-full text-xs font-semibold">
                        {{ formatCustomerStatus(proposal.potential_customer?.status) }}
                      </span>
                    </div>

                    <!-- View Customer Button -->
                    <div class="pt-4 border-t border-gray-200">
                      <button 
                        @click="goToCustomerDetails"
                        class="w-full bg-teal-600 hover:bg-teal-700 text-white px-4 py-2.5 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                      >
                        <UserCircleIcon class="w-4 h-4" />
                        <span>View Customer Details</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Timeline & Metadata -->
                <div class="mt-6 md:mt-8 pt-6 md:pt-8 border-t border-gray-200">
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                    <div>
                      <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4">Created Information</h3>
                      <div class="space-y-3">
                        <div>
                          <p class="text-xs md:text-sm text-gray-600 font-medium mb-1">Created By</p>
                          <p class="text-sm font-semibold text-gray-900">{{ proposal.created_by?.name || 'System' }}</p>
                        </div>
                        <div>
                          <p class="text-xs md:text-sm text-gray-600 font-medium mb-1">Created At</p>
                          <p class="text-sm font-semibold text-gray-900">{{ formatDate(proposal.created_at) }}</p>
                        </div>
                        <div>
                          <p class="text-xs md:text-sm text-gray-600 font-medium mb-1">Last Updated</p>
                          <p class="text-sm font-semibold text-gray-900">{{ formatDate(proposal.updated_at) }}</p>
                        </div>
                      </div>
                    </div>

                    <!-- Status Timeline -->
                    <div class="md:col-span-2">
                      <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3 md:mb-4">Status Timeline</h3>
                      <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                          <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 md:mt-2 flex-shrink-0"></div>
                          <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">Proposal Created</p>
                            <p class="text-xs text-gray-500">{{ formatDate(proposal.created_at) }}</p>
                          </div>
                        </div>

                        <div v-if="proposal.customer_approved_at" class="flex items-start space-x-3">
                          <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5 md:mt-2 flex-shrink-0"></div>
                          <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">Approved by Customer</p>
                            <p class="text-xs text-gray-500">{{ formatDate(proposal.customer_approved_at) }}</p>
                          </div>
                        </div>

                        <div v-if="proposal.customer_rejected_at" class="flex items-start space-x-3">
                          <div class="w-2 h-2 bg-red-500 rounded-full mt-1.5 md:mt-2 flex-shrink-0"></div>
                          <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">Rejected by Customer</p>
                            <p class="text-xs text-gray-500">{{ formatDate(proposal.customer_rejected_at) }}</p>
                            <p v-if="proposal.customer_review" class="text-xs text-gray-500 mt-1 italic">"{{ proposal.customer_review }}"</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap gap-2 mt-6 md:mt-8 pt-6 md:pt-8 border-t border-gray-200">
                  <!-- Edit Button -->
                  <button 
                    v-if="permissions.edit && !proposal.is_rejected && proposal.status === 'unsigned'"
                    @click="openEditModal"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                  >
                    <PencilIcon class="w-4 h-4" />
                    <span>Edit Proposal</span>
                  </button>

                  <!-- Approve Button -->
                  <button 
                    v-if="permissions.approve && proposal.status === 'unsigned' && !proposal.is_rejected"
                    @click="approveProposal"
                    :disabled="loading"
                    class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 disabled:cursor-not-allowed"
                  >
                    <CheckIcon class="w-4 h-4" />
                    <span v-if="loading">Approving...</span>
                    <span v-else>Approve Proposal</span>
                  </button>

                  <!-- Reject Button -->
                  <button 
                    v-if="permissions.reject && proposal.status === 'unsigned' && !proposal.is_rejected"
                    @click="showRejectModal = true"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                  >
                    <XMarkIcon class="w-4 h-4" />
                    <span>Reject Proposal</span>
                  </button>

                  <!-- Delete Button -->
                  <button 
                    v-if="permissions.delete"
                    @click="deleteProposal"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                  >
                    <TrashIcon class="w-4 h-4" />
                    <span>Delete Proposal</span>
                  </button>

                  <!-- Download Button -->
                  <button 
                    v-if="proposal.file"
                    @click="downloadProposalFile(proposal.id)"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                  >
                    <ArrowDownTrayIcon class="w-4 h-4" />
                    <span>Download File</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Proposal Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-3 md:p-4 z-50">
      <div class="bg-white rounded-xl md:rounded-lg p-4 md:p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-200 shadow-xl">
        <div class="flex items-center justify-between mb-4 md:mb-6">
          <div class="flex items-center space-x-2 md:space-x-3">
            <div class="w-8 h-8 md:w-10 md:h-10 bg-gray-100 rounded-lg md:rounded-xl flex items-center justify-center flex-shrink-0">
              <PencilIcon class="w-4 h-4 md:w-5 md:h-5 text-gray-600" />
            </div>
            <div class="min-w-0">
              <h3 class="text-base md:text-lg font-bold text-gray-900 truncate">Edit Proposal</h3>
              <p class="text-gray-600 text-xs md:text-sm truncate">Update proposal information</p>
            </div>
          </div>
          <button 
            @click="closeEditModal" 
            class="text-gray-400 hover:text-gray-600 transition-colors flex-shrink-0"
          >
            <XMarkIcon class="w-5 h-5 md:w-6 md:h-6" />
          </button>
        </div>
        
        <form @submit.prevent="submitEdit" class="space-y-3 md:space-y-4">
          <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Title *</label>
            <input
              type="text"
              v-model="editForm.title"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
              placeholder="Enter proposal title"
            />
          </div>

          <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Description *</label>
            <textarea
              v-model="editForm.description"
              required
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none text-sm"
              placeholder="Enter proposal description"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Price ($) *</label>
            <input
              type="number"
              step="0.01"
              min="0"
              v-model="editForm.price"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
              placeholder="0.00"
            />
          </div>

          <div v-if="proposal.file">
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">Current File</label>
            <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
              <DocumentIcon class="w-5 h-5 text-blue-600" />
              <span class="text-sm text-gray-700 flex-1 truncate">{{ getFileName(proposal.file) }}</span>
              <button
                type="button"
                @click="downloadProposalFile(proposal.id)"
                class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors whitespace-nowrap"
              >
                Download
              </button>
            </div>
          </div>

          <div>
            <label class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">{{ proposal.file ? 'Replace File' : 'Upload File' }} (Optional)</label>
            <input
              type="file"
              @change="handleFileUpload"
              accept=".pdf,.doc,.docx"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700"
            />
            <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX (Max: 2MB)</p>
          </div>

          <div class="flex flex-wrap justify-end gap-2 pt-3 md:pt-4 border-t border-gray-200">
            <button 
              type="button"
              @click="closeEditModal"
              class="px-3 py-1.5 md:px-4 md:py-2 text-gray-600 hover:text-gray-800 font-medium text-xs md:text-sm transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="editForm.processing"
              class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-3 py-1.5 md:px-6 md:py-2 rounded-lg font-semibold transition-all duration-200 text-xs md:text-sm shadow hover:shadow-lg disabled:cursor-not-allowed whitespace-nowrap"
            >
              <span v-if="editForm.processing">Updating...</span>
              <span v-else>Update Proposal</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Reject Proposal Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-3 md:p-4 z-50">
      <div class="bg-white rounded-xl md:rounded-lg p-4 md:p-6 w-full max-w-md border border-gray-200 shadow-xl">
        <div class="flex items-center space-x-2 md:space-x-3 mb-3 md:mb-4">
          <div class="w-8 h-8 md:w-10 md:h-10 bg-red-100 rounded-lg md:rounded-xl flex items-center justify-center flex-shrink-0">
            <XMarkIcon class="w-4 h-4 md:w-5 md:h-5 text-red-600" />
          </div>
          <div class="min-w-0">
            <h3 class="text-base md:text-lg font-bold text-gray-900">Reject Proposal</h3>
            <p class="text-gray-600 text-xs md:text-sm">Please provide a reason for rejection</p>
          </div>
        </div>
        
        <textarea 
          v-model="rejectForm.reason"
          placeholder="Enter reason for rejecting this proposal..."
          class="w-full h-24 md:h-32 p-3 md:p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm resize-none"
          required
        ></textarea>
        
        <div class="flex flex-wrap justify-end gap-2 pt-3 md:pt-4 mt-3 md:mt-6">
          <button 
            @click="showRejectModal = false" 
            class="px-3 py-1.5 md:px-4 md:py-2.5 text-gray-600 hover:text-gray-800 font-medium text-xs md:text-sm transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="rejectProposal"
            :disabled="!rejectForm.reason.trim()"
            class="bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white px-3 py-1.5 md:px-6 md:py-2.5 rounded-lg font-semibold transition-all duration-200 text-xs md:text-sm shadow hover:shadow-lg disabled:cursor-not-allowed whitespace-nowrap"
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
  DocumentIcon,
  ArrowDownTrayIcon
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

// State
const showRejectModal = ref(false)
const showEditModal = ref(false)
const loading = ref(false)
const isSidebarOpen = ref(false)

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
  if (proposal.is_rejected === true || proposal.status === 'reject' || proposal.status === 'rejected') {
    return 'bg-red-100 text-red-800'
  }
  
  if (proposal.status === 'signed') {
    return 'bg-green-100 text-green-800'
  }
  
  if (proposal.status === 'unsigned') {
    return 'bg-yellow-100 text-yellow-800'
  }
  
  return 'bg-gray-100 text-gray-800'
}

function getCustomerStatusBadgeClass(status) {
  const map = {
    draft: 'bg-blue-100 text-blue-800',
    proposal_sent: 'bg-orange-100 text-orange-800',
    accepted: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

function formatProposalStatus(proposal) {
  if (proposal.is_rejected === true || proposal.status === 'reject' || proposal.status === 'rejected') {
    return 'Rejected'
  }
  
  if (proposal.status === 'signed') {
    return 'Signed'
  }
  
  if (proposal.status === 'unsigned') {
    return 'Unsigned'
  }
  
  return proposal.status || 'Unknown'
}

function formatCustomerStatus(status) {
  const map = {
    draft: 'Draft',
    proposal_sent: 'Proposal Sent',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return map[status] || status
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function getFileName(filePath) {
  return filePath ? filePath.split('/').pop() : ''
}

// Sidebar toggle
const toggleSidebar = () => {
  isSidebarOpen.value = !isSidebarOpen.value
  // Dispatch event for sidebar to listen to
  window.dispatchEvent(new CustomEvent('toggle-sidebar', { detail: { isOpen: isSidebarOpen.value } }))
}

// Navigation methods
const goBackToCustomer = () => {
  if (props.proposal?.potential_customer?.id) {
    router.visit(`/admin/potential-customers/${props.proposal.potential_customer.id}`)
  } else {
    router.visit('/admin/potential-customers')
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

const rejectProposal = () => {
  if (!props.proposal || !props.proposal.id) return
  
  if (!rejectForm.reason.trim()) {
    alert('Please provide a reason for rejection')
    return
  }
  
  if (confirm('Are you sure you want to reject this proposal?')) {
    router.post(`/admin/proposals/${props.proposal.id}/reject`, { 
      reason: rejectForm.reason 
    }, {
      preserveScroll: true,
      onSuccess: () => {
        showRejectModal.value = false
        rejectForm.reason = ''
        router.reload({ only: ['proposal'] })
      },
      onError: (errors) => {
        console.error('Reject error details:', errors)
        alert('Failed to reject proposal. Please check console for details.')
      }
    })
  }
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
  window.open(`/admin/proposals/${proposalId}/download`, '_blank')
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

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
</style>