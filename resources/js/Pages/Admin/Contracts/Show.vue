<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-teal-50">
        <!-- Header -->
        <div class="bg-white border-b border-blue-200 px-6 py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">Contract Details</h1>
              <p class="text-slate-600 mt-2">View contract information and manage status</p>
            </div>
            <div class="flex space-x-3">
              <button 
                @click="goBack"
                class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
              >
                <ArrowLeftIcon class="w-5 h-5" />
                <span>Back to Contracts</span>
              </button>
              <a 
                v-if="permissions.edit"
                :href="`/admin/contracts/${contract.id}/edit`"
                class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
              >
                <PencilIcon class="w-5 h-5" />
                <span>Edit Contract</span>
              </a>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-lg border border-blue-200 overflow-hidden">
              <!-- Header Banner -->
              <div class="bg-gradient-to-r from-blue-600 to-teal-600 text-white p-8">
                <div class="flex justify-between items-start">
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                      <DocumentTextIcon class="w-8 h-8 text-white" />
                    </div>
                    <div>
                      <h2 class="text-3xl font-bold">{{ contract.contract_title }}</h2>
                      <p class="text-blue-100 mt-2 text-lg">Contract Agreement</p>
                    </div>
                  </div>
                  <span :class="getStatusBadgeClass(contract.status)" class="px-6 py-3 rounded-full text-lg font-semibold border border-white/30">
                    {{ formatStatus(contract.status) }}
                  </span>
                </div>
              </div>

              <!-- Content -->
              <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  <!-- Contract Information -->
                  <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-slate-800 border-b border-slate-200 pb-3">Contract Information</h3>
                    
                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Contract Title</p>
                      <p class="text-lg font-semibold text-slate-900">{{ contract.contract_title }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Total Value</p>
                      <p class="text-lg font-semibold text-slate-900">${{ contract.total_value }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Payment Terms</p>
                      <p class="text-lg font-semibold text-slate-900">{{ contract.payment_terms }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Contract Period</p>
                      <p class="text-lg font-semibold text-slate-900">
                        {{ formatDate(contract.start_date) }} to {{ formatDate(contract.end_date) }}
                      </p>
                    </div>
                  </div>

                  <!-- Related Information -->
                  <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-slate-800 border-b border-slate-200 pb-3">Related Information</h3>
                    
                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Customer</p>
                      <p class="text-lg font-semibold text-slate-900">{{ contract.customer?.name || 'No customer assigned' }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Proposal</p>
                      <a 
                        v-if="contract.proposal"
                        :href="`/admin/proposals/${contract.proposal.id}`"
                        class="text-lg font-semibold text-blue-600 hover:text-blue-800 transition-colors flex items-center"
                      >
                        {{ contract.proposal.title }}
                        <ArrowTopRightOnSquareIcon class="w-4 h-4 ml-1" />
                      </a>
                      <p v-else class="text-lg font-semibold text-slate-500">No proposal linked</p>
                    </div>

                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Created At</p>
                      <p class="text-lg font-semibold text-slate-900">{{ formatDateTime(contract.created_at) }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Last Updated</p>
                      <p class="text-lg font-semibold text-slate-900">{{ formatDateTime(contract.updated_at) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Contract Description -->
                <div class="mt-8 pt-8 border-t border-slate-200">
                  <h3 class="text-xl font-semibold text-slate-800 mb-4">Contract Description</h3>
                  <div class="bg-blue-50 rounded-lg p-6 border-l-4 border-blue-500">
                    <p class="text-blue-900 text-lg leading-relaxed">{{ contract.contract_description }}</p>
                  </div>
                </div>

                <!-- File Section -->
                <div v-if="contract.file" class="mt-6 pt-6 border-t border-slate-200">
                  <h3 class="text-xl font-semibold text-slate-800 mb-4">Contract File</h3>
                  <div class="bg-teal-50 rounded-lg p-6 border-l-4 border-teal-500">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-3">
                        <DocumentIcon class="w-8 h-8 text-teal-600" />
                        <div>
                          <p class="font-semibold text-teal-900">Contract Document</p>
                          <p class="text-sm text-teal-700">Uploaded contract file</p>
                        </div>
                      </div>
                      <a 
                        :href="`/storage/${contract.file}`"
                        target="_blank"
                        class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2"
                      >
                        <ArrowDownTrayIcon class="w-4 h-4" />
                        <span>Download</span>
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Status Timeline -->
                <div class="mt-6 pt-6 border-t border-slate-200">
                  <h3 class="text-xl font-semibold text-slate-800 mb-4">Status Timeline</h3>
                  <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                      <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                      <div class="text-sm text-slate-700">Contract created on {{ formatDateTime(contract.created_at) }}</div>
                    </div>
                    <div v-if="contract.customer_signed_at" class="flex items-center space-x-3">
                      <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                      <div class="text-sm text-slate-700">Signed by customer on {{ formatDateTime(contract.customer_signed_at) }}</div>
                    </div>
                    <div v-if="contract.customer_rejected_at" class="flex items-center space-x-3">
                      <div class="w-3 h-3 bg-rose-500 rounded-full"></div>
                      <div class="text-sm text-slate-700">Rejected by customer on {{ formatDateTime(contract.customer_rejected_at) }}</div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4 mt-8 pt-8 border-t border-slate-200">
                  <!-- Approve Button -->
                  <button 
                    v-if="permissions.approve && contract.status === 'unsigned'"
                    @click="approveContract"
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                  >
                    <CheckIcon class="w-5 h-5" />
                    <span>Approve Contract</span>
                  </button>

                  <!-- Reject Button -->
                  <button 
                    v-if="permissions.reject && contract.status === 'unsigned'"
                    @click="rejectContract"
                    class="bg-rose-600 hover:bg-rose-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                  >
                    <XMarkIcon class="w-5 h-5" />
                    <span>Reject Contract</span>
                  </button>

                  <!-- Delete Button -->
                  <button 
                    v-if="permissions.delete"
                    @click="deleteContract"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                  >
                    <TrashIcon class="w-5 h-5" />
                    <span>Delete Contract</span>
                  </button>
                </div>
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
  ArrowLeftIcon,
  PencilIcon,
  TrashIcon,
  CheckIcon,
  XMarkIcon,
  DocumentTextIcon,
  DocumentIcon,
  ArrowTopRightOnSquareIcon,
  ArrowDownTrayIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  contract: Object,
  permissions: Object,
  tables: Array
})

// Helper methods
function getStatusBadgeClass(status) {
  const classes = {
    unsigned: 'bg-yellow-100 text-yellow-800 border-yellow-200',
    signed: 'bg-green-100 text-green-800 border-green-200',
    rejected: 'bg-rose-100 text-rose-800 border-rose-200'
  }
  return classes[status] || 'bg-slate-100 text-slate-800 border-slate-200'
}

function formatStatus(status) {
  const statusMap = {
    unsigned: 'Unsigned',
    signed: 'Signed',
    rejected: 'Rejected'
  }
  return statusMap[status] || status
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function formatDateTime(dateString) {
  if (!dateString) return 'Unknown date'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Action methods
const goBack = () => {
  router.get('/admin/contracts')
}

const approveContract = () => {
  if (confirm('Are you sure you want to approve this contract? It will be marked as signed.')) {
    router.post(`/admin/contracts/${props.contract.id}/approve`, {}, {
      onSuccess: () => {
        // Success handled by controller
      }
    })
  }
}

const rejectContract = () => {
  if (confirm('Are you sure you want to reject this contract?')) {
    router.post(`/admin/contracts/${props.contract.id}/reject`, {}, {
      onSuccess: () => {
        // Success handled by controller
      }
    })
  }
}

const deleteContract = () => {
  if (confirm('Are you sure you want to delete this contract? This action cannot be undone.')) {
    router.delete(`/admin/contracts/${props.contract.id}`)
  }
}
</script>