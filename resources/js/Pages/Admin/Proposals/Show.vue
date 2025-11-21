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
                <h1 class="text-3xl font-bold text-slate-800">Proposal Details</h1>
                <p class="text-slate-600 text-lg">View proposal information</p>
              </div>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <button 
              @click="visitIndex"
              class="group bg-white/80 backdrop-blur-sm border border-blue-200 text-blue-700 hover:bg-blue-50 px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center space-x-3 transform hover:-translate-y-0.5"
            >
              <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                <ArrowLeftIcon class="w-4 h-4 text-blue-600" />
              </div>
              <span>Back to Proposals</span>
            </button>
          </div>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Proposal Card -->
          <div class="lg:col-span-2">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
              <!-- Status Bar -->
              <div class="h-3 bg-slate-200 relative overflow-hidden">
                <div 
                  class="h-full bg-gradient-to-r from-green-500 to-teal-500 transition-all duration-1000 ease-out"
                  :class="proposal.status === 'signed' ? 'w-full' : 'w-0'"
                ></div>
              </div>

              <div class="p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                  <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-teal-100 rounded-xl flex items-center justify-center">
                      <DocumentTextIcon class="w-6 h-6 text-blue-600" />
                    </div>
                    <div>
                      <h2 class="text-2xl font-bold text-slate-800">{{ proposal.title }}</h2>
                      <div class="flex items-center space-x-4 mt-1">
                        <span :class="`px-3 py-1 rounded-full text-sm font-medium ${
                          proposal.status === 'signed' 
                            ? 'bg-green-100 text-green-800' 
                            : 'bg-yellow-100 text-yellow-800'
                        }`">
                          {{ proposal.status }}
                        </span>
                        <span class="text-slate-600 text-sm">
                          ${{ proposal.price }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Description -->
                <div class="mb-6">
                  <h3 class="text-lg font-semibold text-slate-800 mb-3">Description</h3>
                  <p class="text-slate-700 whitespace-pre-wrap bg-slate-50/50 p-4 rounded-xl border border-slate-200">
                    {{ proposal.description }}
                  </p>
                </div>

                <!-- File Section -->
                <div v-if="proposal.file" class="mb-6">
                  <h3 class="text-lg font-semibold text-slate-800 mb-3">Attached File</h3>
                  <div class="flex items-center space-x-4 p-4 bg-blue-50/50 rounded-xl border border-blue-200">
                    <DocumentIcon class="w-8 h-8 text-blue-600" />
                    <div class="flex-1">
                      <p class="font-medium text-slate-800">{{ getFileName(proposal.file) }}</p>
                      <p class="text-sm text-slate-600">Proposal document</p>
                    </div>
                    <button
                      @click="downloadFile"
                      class="px-4 py-2 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-lg hover:from-blue-600 hover:to-teal-600 transition-all duration-300 font-medium transform hover:-translate-y-0.5"
                    >
                      Download
                    </button>
                  </div>
                </div>

                <!-- Customer Review -->
                <div v-if="proposal.customer_review" class="mb-6">
                  <h3 class="text-lg font-semibold text-slate-800 mb-3">Customer Review</h3>
                  <div class="p-4 bg-slate-50/50 rounded-xl border border-slate-200">
                    <p class="text-slate-700 mb-2">{{ proposal.customer_review }}</p>
                    <div class="flex items-center space-x-4 text-sm text-slate-500">
                      <span v-if="proposal.customer_approved_at" class="flex items-center space-x-1">
                        <CheckIcon class="w-4 h-4 text-green-600" />
                        <span>Approved on: {{ formatDate(proposal.customer_approved_at) }}</span>
                      </span>
                      <span v-if="proposal.customer_rejected_at" class="flex items-center space-x-1">
                        <XMarkIcon class="w-4 h-4 text-rose-600" />
                        <span>Rejected on: {{ formatDate(proposal.customer_rejected_at) }}</span>
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar Cards -->
          <div class="space-y-6">
            <!-- Customer Information Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
              <div class="px-6 py-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-teal-50">
                <h3 class="text-lg font-semibold text-slate-800">Customer Information</h3>
              </div>
              <div class="p-6 space-y-4">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <BuildingOfficeIcon class="w-5 h-5 text-blue-600" />
                  </div>
                  <div>
                    <p class="font-medium text-slate-800">{{ proposal.potential_customer?.potential_customer_name || 'N/A' }}</p>
                    <p class="text-sm text-slate-600">{{ proposal.potential_customer?.email || 'N/A' }}</p>
                  </div>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-slate-200">
                  <span class="text-sm text-slate-600">Status</span>
                  <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                    {{ proposal.potential_customer?.status || 'N/A' }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Created Information Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
              <div class="px-6 py-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-teal-50">
                <h3 class="text-lg font-semibold text-slate-800">Created Information</h3>
              </div>
              <div class="p-6 space-y-4">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center">
                    <UserIcon class="w-5 h-5 text-teal-600" />
                  </div>
                  <div>
                    <p class="font-medium text-slate-800">{{ proposal.created_by?.name || 'Unknown' }}</p>
                    <p class="text-sm text-slate-600">Created By</p>
                  </div>
                </div>
                <div class="space-y-2 text-sm">
                  <div class="flex justify-between">
                    <span class="text-slate-600">Created</span>
                    <span class="text-slate-800">{{ formatDate(proposal.created_at) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-600">Updated</span>
                    <span class="text-slate-800">{{ formatDate(proposal.updated_at) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Actions Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
              <div class="px-6 py-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-teal-50">
                <h3 class="text-lg font-semibold text-slate-800">Actions</h3>
              </div>
              <div class="p-6 space-y-3">
                <button 
                  v-if="permissions.edit"
                  @click="visitEdit"
                  class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                  <PencilIcon class="w-5 h-5" />
                  <span>Edit Proposal</span>
                </button>
                
                <button 
                  v-if="permissions.approve && proposal.status === 'unsigned'"
                  @click="approveProposal"
                  class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                  <CheckIcon class="w-5 h-5" />
                  <span>Approve Proposal</span>
                </button>
                
                <button 
                  v-if="permissions.reject && proposal.status === 'unsigned'"
                  @click="rejectProposal"
                  class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-rose-500 text-white rounded-xl hover:bg-rose-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                  <XMarkIcon class="w-5 h-5" />
                  <span>Reject Proposal</span>
                </button>
                
                <button 
                  v-if="permissions.delete"
                  @click="deleteProposal"
                  class="w-full flex items-center justify-center space-x-2 px-4 py-3 bg-slate-500 text-white rounded-xl hover:bg-slate-600 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                  <TrashIcon class="w-5 h-5" />
                  <span>Delete Proposal</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  DocumentTextIcon,
  DocumentIcon,
  ArrowLeftIcon,
  PencilIcon,
  TrashIcon,
  CheckIcon,
  XMarkIcon,
  UserIcon,
  BuildingOfficeIcon
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

const getFileName = (filePath) => {
  return filePath.split('/').pop()
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const downloadFile = () => {
  router.get(`/admin/proposals/${props.proposal.id}/download`)
}

const visitIndex = () => {
  router.get('/admin/proposals')
}

const visitEdit = () => {
  router.get(`/admin/proposals/${props.proposal.id}/edit`)
}

const deleteProposal = () => {
  if (confirm('Are you sure you want to delete this proposal?')) {
    router.delete(`/admin/proposals/${props.proposal.id}`)
  }
}

const approveProposal = () => {
  if (confirm('Are you sure you want to approve this proposal? This will mark it as signed.')) {
    router.post(`/admin/proposals/${props.proposal.id}/approve`)
  }
}

const rejectProposal = () => {
  const review = prompt('Please provide a reason for rejection:')
  if (review) {
    router.post(`/admin/proposals/${props.proposal.id}/reject`, { customer_review: review })
  }
}
</script>