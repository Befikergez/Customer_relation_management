<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-blue-50">
        <!-- Header -->
        <div class="bg-white border-b border-teal-200 px-4 sm:px-6 py-4 sm:py-6">
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-0">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-teal-600 to-blue-600 bg-clip-text text-transparent">Potential Customer Details</h1>
              <p class="text-slate-600 mt-1 sm:mt-2 text-sm sm:text-base">Review customer information and manage proposals</p>
            </div>
            <button 
              @click="goBack"
              class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg w-fit"
            >
              <ArrowLeftIcon class="w-4 h-4" />
              <span class="text-sm">Back to Customers</span>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4 sm:p-6">
          <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-lg border border-teal-200 overflow-hidden">
              <!-- Header Banner -->
              <div class="bg-gradient-to-r from-teal-600 to-blue-600 text-white p-4 sm:p-6 md:p-8">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 sm:gap-0">
                  <div class="flex items-center space-x-3 sm:space-x-4">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-white/20 rounded-xl sm:rounded-2xl flex items-center justify-center">
                      <UserCircleIcon class="w-6 h-6 sm:w-8 sm:h-8 text-white" />
                    </div>
                    <div>
                      <h2 class="text-xl sm:text-2xl md:text-3xl font-bold">{{ potentialCustomer.potential_customer_name }}</h2>
                      <p class="text-teal-100 mt-1 sm:mt-2 text-sm sm:text-lg">Potential Customer Lead</p>
                    </div>
                  </div>
                  <span :class="getStatusBadgeClass(potentialCustomer.status)" class="px-3 py-2 sm:px-6 sm:py-3 rounded-full text-sm sm:text-lg font-semibold border border-white/30 w-fit">
                    {{ formatStatus(potentialCustomer.status) }}
                  </span>
                </div>
              </div>

              <!-- Content -->
              <div class="p-4 sm:p-6 md:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                  <!-- Contact Information -->
                  <div class="space-y-4 sm:space-y-6">
                    <h3 class="text-lg sm:text-xl font-semibold text-slate-800 border-b border-slate-200 pb-2 sm:pb-3">Contact Information</h3>
                    
                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Email Address</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ potentialCustomer.email || 'Not provided' }}</p>
                    </div>

                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Phone Number</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ potentialCustomer.phone || 'Not provided' }}</p>
                    </div>

                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Location</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ potentialCustomer.location || 'Not provided' }}</p>
                    </div>
                  </div>

                  <!-- Additional Information -->
                  <div class="space-y-4 sm:space-y-6">
                    <h3 class="text-lg sm:text-xl font-semibold text-slate-800 border-b border-slate-200 pb-2 sm:pb-3">Additional Information</h3>
                    
                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Created By</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ potentialCustomer.created_by?.name || 'System' }}</p>
                    </div>

                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Created At</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ formatDate(potentialCustomer.created_at) }}</p>
                    </div>

                    <div>
                      <p class="text-xs sm:text-sm text-slate-600 font-medium mb-1 sm:mb-2">Last Updated</p>
                      <p class="text-base sm:text-lg font-semibold text-slate-900">{{ formatDate(potentialCustomer.updated_at) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Remarks -->
                <div class="mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-slate-200">
                  <h3 class="text-lg sm:text-xl font-semibold text-slate-800 mb-3 sm:mb-4">Remarks</h3>
                  <div class="bg-blue-50 rounded-lg p-4 sm:p-6 border-l-4 border-blue-500">
                    <p class="text-blue-900 text-base sm:text-lg leading-relaxed">{{ potentialCustomer.remarks || 'No remarks provided.' }}</p>
                  </div>
                </div>

                <!-- Proposals Section -->
                <div class="mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-slate-200">
                  <h3 class="text-lg sm:text-xl font-semibold text-slate-800 mb-4 sm:mb-6">Proposals</h3>
                  
                  <div v-if="potentialCustomer.proposals && potentialCustomer.proposals.length > 0" class="space-y-3 sm:space-y-4">
                    <div v-for="proposal in potentialCustomer.proposals" :key="proposal.id" 
                         class="bg-teal-50 rounded-lg p-4 sm:p-6 border border-teal-200">
                      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 sm:gap-0">
                        <div>
                          <h4 class="font-semibold text-slate-900 text-base sm:text-lg">{{ proposal.title }}</h4>
                          <p class="text-slate-600 mt-1 sm:mt-2 text-sm sm:text-base">{{ proposal.description }}</p>
                          <div class="flex flex-col sm:flex-row sm:items-center space-y-2 sm:space-y-0 sm:space-x-4 mt-2 sm:mt-3">
                            <span class="text-base sm:text-lg font-bold text-rose-600">${{ proposal.price }}</span>
                            <span :class="getProposalStatusBadgeClass(proposal.status)" class="px-2 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs sm:text-sm font-semibold border w-fit">
                              {{ formatProposalStatus(proposal.status) }}
                            </span>
                          </div>
                        </div>
                        <a 
                          :href="`/admin/proposals/${proposal.id}`"
                          class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 text-sm w-fit"
                        >
                          <EyeIcon class="w-4 h-4" />
                          <span>View Details</span>
                        </a>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-6 sm:py-8 text-slate-500">
                    <DocumentTextIcon class="w-12 h-12 sm:w-16 sm:h-16 mx-auto mb-3 sm:mb-4 text-slate-400" />
                    <p class="text-base sm:text-lg">No proposals created for this customer yet.</p>
                    <button 
                      v-if="permissions.create && potentialCustomer.status === 'draft'"
                      @click="createProposal"
                      class="mt-3 sm:mt-4 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 mx-auto text-sm"
                    >
                      <DocumentTextIcon class="w-4 h-4" />
                      <span>Create Proposal</span>
                    </button>
                  </div>
                </div>

                <!-- Action Buttons - Smaller -->
                <div v-if="potentialCustomer.status === 'draft'" class="flex flex-wrap gap-2 mt-6 sm:mt-8 pt-6 sm:pt-8 border-t border-slate-200">
                  <!-- Edit Button -->
                  <button 
                    v-if="permissions.update"
                    @click="editCustomer"
                    class="bg-amber-600 hover:bg-amber-700 text-white px-3 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg text-sm"
                  >
                    <PencilIcon class="w-4 h-4" />
                    <span>Edit Customer</span>
                  </button>

                  <button 
                    v-if="permissions.create"
                    @click="createProposal"
                    class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg text-sm"
                  >
                    <DocumentTextIcon class="w-4 h-4" />
                    <span>Create Proposal</span>
                  </button>
                  <button 
                    v-if="permissions.approve"
                    @click="approveCustomer"
                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg text-sm"
                  >
                    <CheckIcon class="w-4 h-4" />
                    <span>Approve Customer</span>
                  </button>
                  <button 
                    v-if="permissions.reject"
                    @click="showRejectModal = true"
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg text-sm"
                  >
                    <XMarkIcon class="w-4 h-4" />
                    <span>Reject Customer</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-4 sm:p-6 w-full max-w-md border border-slate-200 shadow-xl">
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-lg sm:rounded-xl flex items-center justify-center">
            <XMarkIcon class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" />
          </div>
          <div>
            <h3 class="text-base sm:text-lg font-bold text-slate-800">Reject Customer</h3>
            <p class="text-slate-600 text-xs sm:text-sm">Please provide a reason for rejection</p>
          </div>
        </div>
        
        <textarea 
          v-model="rejectForm.reason"
          placeholder="Enter reason for rejecting this customer..."
          class="w-full h-24 sm:h-32 p-3 sm:p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm resize-none"
          required
        ></textarea>
        
        <div class="flex justify-end space-x-2 sm:space-x-3 mt-4 sm:mt-6">
          <button 
            @click="showRejectModal = false" 
            class="px-3 py-2 sm:px-4 sm:py-2.5 text-slate-600 hover:text-slate-800 font-medium text-xs sm:text-sm transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="rejectCustomer"
            :disabled="!rejectForm.reason.trim()"
            class="bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white px-4 py-2 sm:px-6 sm:py-2.5 rounded-lg font-semibold transition-all duration-200 text-xs sm:text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
          >
            Reject Customer
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  CheckIcon,
  XMarkIcon,
  DocumentTextIcon,
  EyeIcon,
  UserCircleIcon,
  PencilIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  potentialCustomer: Object,
  permissions: Object,
  tables: Array
})

const showRejectModal = ref(false)
const rejectForm = reactive({
  reason: ''
})

// Helper methods
function getStatusBadgeClass(status) {
  const classes = {
    draft: 'bg-blue-100 text-blue-800 border-blue-200',
    proposal_sent: 'bg-rose-100 text-rose-800 border-rose-200',
    accepted: 'bg-green-100 text-green-800 border-green-200',
    rejected: 'bg-red-100 text-red-800 border-red-200'
  }
  return classes[status] || 'bg-slate-100 text-slate-800 border-slate-200'
}

function formatStatus(status) {
  const statusMap = {
    draft: 'Draft',
    proposal_sent: 'Proposal Sent',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return statusMap[status] || status
}

function getProposalStatusBadgeClass(status) {
  const classes = {
    unsigned: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    signed: 'bg-green-100 text-green-800 border-green-200'
  }
  return classes[status] || 'bg-slate-100 text-slate-800 border-slate-200'
}

function formatProposalStatus(status) {
  const statusMap = {
    unsigned: 'Unsigned',
    signed: 'Signed'
  }
  return statusMap[status] || status
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Action methods
const goBack = () => {
  router.get('/admin/potential-customers')
}

const editCustomer = () => {
  router.visit(`/admin/potential-customers/${props.potentialCustomer.id}/edit`)
}

const createProposal = () => {
  router.visit('/admin/proposals/create', {
    data: { potential_customer_id: props.potentialCustomer.id }
  })
}

const approveCustomer = () => {
  if (confirm('Are you sure you want to approve this potential customer?')) {
    router.post(`/admin/potential-customers/${props.potentialCustomer.id}/approve`)
  }
}

const rejectCustomer = () => {
  router.post(`/admin/potential-customers/${props.potentialCustomer.id}/reject`, rejectForm, {
    onSuccess: () => {
      showRejectModal.value = false
      rejectForm.reason = ''
    }
  })
}
</script>