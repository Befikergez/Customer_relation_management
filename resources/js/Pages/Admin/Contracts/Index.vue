<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-teal-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="p-6">
        <div class="flex justify-between items-center mb-8">
          <div class="space-y-2">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
                <DocumentTextIcon class="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 class="text-3xl font-bold text-slate-800">Contracts</h1>
                <p class="text-slate-600 text-lg">Manage customer contracts</p>
              </div>
            </div>
          </div>
          <button 
            v-if="permissions.create"
            @click="visitCreate"
            class="group bg-gradient-to-r from-blue-500 to-teal-500 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center space-x-3 transform hover:-translate-y-0.5 hover:from-blue-600 hover:to-teal-600"
          >
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
              <PlusIcon class="w-4 h-4 text-white" />
            </div>
            <span>New Contract</span>
          </button>
        </div>

        <!-- Results Count -->
        <div v-if="contracts.data && contracts.data.length > 0" class="mb-4">
          <p class="text-sm text-slate-600">
            Showing {{ contracts.meta?.from || 0 }} to {{ contracts.meta?.to || 0 }} of {{ contracts.meta?.total || 0 }} contracts
          </p>
        </div>

        <div v-if="contracts.data && contracts.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="contract in contracts.data"
            :key="contract.id"
            class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300 group hover:border-blue-200"
          >
            <div class="h-1.5 bg-slate-100 relative overflow-hidden">
              <div 
                class="h-full transition-all duration-1000 ease-out"
                :class="{
                  'w-full bg-gradient-to-r from-teal-500 to-blue-500': contract.status === 'accepted',
                  'w-1/2 bg-gradient-to-r from-blue-500 to-teal-500': contract.status === 'first_contract_sent', 
                  'w-1/4 bg-gradient-to-r from-slate-500 to-slate-400': contract.status === 'draft',
                  'w-full bg-gradient-to-r from-rose-500 to-red-500': contract.status === 'rejected'
                }"
              ></div>
            </div>

            <div class="p-6">
              <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center shadow-md">
                  <DocumentTextIcon class="w-6 h-6 text-white" />
                </div>
                <div class="flex flex-col items-end">
                  <span :class="`px-3 py-1 rounded-full text-xs font-semibold ${
                    contract.status === 'accepted' 
                      ? 'bg-teal-100 text-teal-800 border border-teal-200' 
                      : contract.status === 'first_contract_sent'
                      ? 'bg-blue-100 text-blue-800 border border-blue-200'
                      : contract.status === 'draft'
                      ? 'bg-slate-100 text-slate-800 border border-slate-200'
                      : 'bg-rose-100 text-rose-800 border border-rose-200'
                  }`">
                    {{ formatStatus(contract.status) }}
                  </span>
                  <span class="text-lg font-bold text-slate-800 mt-1">${{ contract.total_value }}</span>
                </div>
              </div>

              <h3 class="text-lg font-bold text-slate-800 mb-3 line-clamp-2 group-hover:text-blue-700 transition-colors">
                {{ contract.contract_title }}
              </h3>
              <p class="text-slate-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                {{ contract.contract_description }}
              </p>

              <div class="space-y-3 mb-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center border border-slate-300">
                    <UserIcon class="w-4 h-4 text-slate-600" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-slate-500 font-medium">Customer</p>
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ contract.customer?.name || 'Unknown' }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center border border-slate-300">
                    <CalendarIcon class="w-4 h-4 text-slate-600" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-slate-500 font-medium">Duration</p>
                    <p class="text-sm font-semibold text-slate-800 truncate">
                      {{ formatDate(contract.start_date) }} - {{ formatDate(contract.end_date) }}
                    </p>
                  </div>
                </div>
                <div v-if="contract.proposal" class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center border border-slate-300">
                    <DocumentTextIcon class="w-4 h-4 text-slate-600" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-slate-500 font-medium">From Proposal</p>
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ contract.proposal?.title || 'N/A' }}</p>
                  </div>
                </div>
              </div>

              <div v-if="contract.file" class="flex items-center space-x-3 mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <DocumentIcon class="w-5 h-5 text-blue-600" />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-blue-800 truncate">{{ getFileName(contract.file) }}</p>
                </div>
                <button
                  @click="downloadFile(contract.id)"
                  class="px-3 py-1 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition-colors font-medium"
                  title="Download File"
                >
                  Download
                </button>
              </div>

              <div class="flex flex-col gap-2 pt-4 border-t border-slate-200">
                <div class="flex gap-2">
                  <button 
                    @click.prevent="viewContract(contract.id)"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs font-medium transition-all duration-200 flex items-center space-x-1"
                  >
                    <EyeIcon class="w-3 h-3" />
                    <span>View</span>
                  </button>
                  
                  <button 
                    v-if="permissions.edit && contract.status !== 'accepted' && contract.status !== 'rejected'"
                    @click="visitEdit(contract.id)"
                    class="flex-1 flex items-center justify-center space-x-2 px-3 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all duration-200 text-sm font-semibold"
                  >
                    <PencilIcon class="w-4 h-4" />
                    <span>Edit</span>
                  </button>
                </div>

                <div class="flex gap-2" v-if="contract.status === 'first_contract_sent'">
                  <button 
                    v-if="permissions.approve"
                    @click="approveContract(contract.id)"
                    class="flex-1 flex items-center justify-center space-x-2 px-3 py-2.5 bg-teal-500 text-white rounded-lg hover:bg-teal-600 transition-all duration-200 text-sm font-semibold"
                  >
                    <CheckIcon class="w-4 h-4" />
                    <span>Approve</span>
                  </button>
                  
                  <button 
                    v-if="permissions.reject"
                    @click="rejectContract(contract.id)"
                    class="flex-1 flex items-center justify-center space-x-2 px-3 py-2.5 bg-rose-500 text-white rounded-lg hover:bg-rose-600 transition-all duration-200 text-sm font-semibold"
                  >
                    <XMarkIcon class="w-4 h-4" />
                    <span>Reject</span>
                  </button>
                </div>

                <button 
                  v-if="permissions.delete && contract.status !== 'accepted'"
                  @click="deleteContract(contract.id)"
                  class="w-full flex items-center justify-center space-x-2 px-3 py-2.5 bg-white border border-rose-300 text-rose-700 rounded-lg hover:bg-rose-50 hover:border-rose-400 transition-all duration-200 text-sm font-semibold"
                >
                  <TrashIcon class="w-4 h-4" />
                  <span>Delete Contract</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-16">
          <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <DocumentTextIcon class="w-10 h-10 text-white" />
          </div>
          <h3 class="text-xl font-bold text-slate-800 mb-3">No contracts yet</h3>
          <p class="text-slate-600 mb-8 max-w-md mx-auto">Start by creating your first contract for customers.</p>
          <button 
            v-if="permissions.create"
            @click="visitCreate"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Create First Contract
          </button>
        </div>

        <!-- Enhanced Pagination -->
        <div v-if="contracts.links && contracts.links.length > 3" class="mt-8 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
          <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-slate-600">
              Page {{ contracts.meta?.current_page || 1 }} of {{ contracts.meta?.last_page || 1 }} • 
              Showing {{ contracts.meta?.from || 0 }} to {{ contracts.meta?.to || 0 }} of {{ contracts.meta?.total || 0 }} contracts
            </div>
            <div class="flex flex-wrap gap-2 justify-center">
              <!-- First Page -->
              <button 
                v-if="contracts.meta?.current_page > 1"
                @click="visitPage(contracts.links[0].url)"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-slate-400 transition-all duration-200 flex items-center space-x-2"
              >
                <ChevronDoubleLeftIcon class="w-4 h-4" />
                <span>First</span>
              </button>

              <!-- Previous Page -->
              <button 
                v-if="contracts.links[0].url"
                @click="visitPage(contracts.links[0].url)"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-slate-400 transition-all duration-200 flex items-center space-x-2"
              >
                <ChevronLeftIcon class="w-4 h-4" />
                <span>Previous</span>
              </button>

              <!-- Page Numbers -->
              <button 
                v-for="link in contracts.links.slice(1, -1)"
                :key="link.label"
                :disabled="!link.url"
                @click="visitPage(link.url)"
                class="px-4 py-2 text-sm font-medium rounded-lg border transition-all duration-200 min-w-[3rem]"
                :class="{
                  'bg-gradient-to-r from-blue-500 to-teal-500 text-white border-transparent shadow-md': link.active,
                  'text-slate-700 border-slate-300 hover:bg-slate-50 hover:border-blue-300': !link.active && link.url,
                  'text-slate-400 border-slate-200 cursor-not-allowed': !link.url
                }"
                v-html="link.label"
              ></button>

              <!-- Next Page -->
              <button 
                v-if="contracts.links[contracts.links.length - 1].url"
                @click="visitPage(contracts.links[contracts.links.length - 1].url)"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-slate-400 transition-all duration-200 flex items-center space-x-2"
              >
                <span>Next</span>
                <ChevronRightIcon class="w-4 h-4" />
              </button>

              <!-- Last Page -->
              <button 
                v-if="contracts.meta?.current_page < contracts.meta?.last_page"
                @click="visitPage(contracts.links[contracts.links.length - 1].url)"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-slate-400 transition-all duration-200 flex items-center space-x-2"
              >
                <span>Last</span>
                <ChevronDoubleRightIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Contract Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md border border-slate-200 shadow-xl">
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
            <XMarkIcon class="w-5 h-5 text-red-600" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-800">Reject Contract</h3>
            <p class="text-slate-600 text-sm">Please provide a reason for rejection</p>
          </div>
        </div>
        
        <textarea 
          v-model="rejectReason"
          placeholder="Enter reason for rejecting this contract..."
          class="w-full h-32 p-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm resize-none"
        ></textarea>
        
        <div class="flex justify-end space-x-3 mt-6">
          <button 
            @click="closeRejectModal" 
            class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium text-sm transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="confirmReject"
            :disabled="!rejectReason.trim()"
            class="bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
          >
            Confirm Reject
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  DocumentTextIcon,
  EyeIcon,
  CheckIcon,
  XMarkIcon,
  UserIcon,
  CalendarIcon,
  DocumentIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ChevronDoubleLeftIcon,
  ChevronDoubleRightIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  contracts: {
    type: Object,
    default: () => ({ data: [] })
  },
  permissions: {
    type: Object,
    default: () => ({})
  },
  tables: {
    type: Array,
    default: () => []
  }
})

const loading = ref(false)
const showRejectModal = ref(false)
const rejectReason = ref('')
const selectedContractId = ref(null)

const getFileName = (filePath) => {
  return filePath ? filePath.split('/').pop() : ''
}

const formatStatus = (status) => {
  const statusMap = {
    draft: 'Draft',
    first_contract_sent: 'First Contract Sent',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return statusMap[status] || status
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const visitCreate = () => {
  router.get('/admin/contracts/create')
}

const viewContract = (contractId) => {
  router.get(`/admin/contracts/${contractId}`)
}

const visitEdit = (id) => {
  router.get(`/admin/contracts/${id}/edit`)
}

const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

const downloadFile = (id) => {
  window.location.href = `/admin/contracts/${id}/download`
}

const deleteContract = (id) => {
  if (confirm('Are you sure you want to delete this contract?')) {
    router.delete(`/admin/contracts/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['contracts'] })
      }
    })
  }
}

const approveContract = (id) => {
  if (confirm('Are you sure you want to approve this contract? This will mark it as accepted.')) {
    router.post(`/admin/contracts/${id}/accept`, {}, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['contracts'] })
      },
      onError: () => {
        alert('Failed to approve contract. Please try again.')
      }
    })
  }
}

const rejectContract = (id) => {
  selectedContractId.value = id
  showRejectModal.value = true
}

const closeRejectModal = () => {
  showRejectModal.value = false
  rejectReason.value = ''
  selectedContractId.value = null
}

const confirmReject = () => {
  if (!rejectReason.value.trim()) {
    alert('Please enter a reason for rejection')
    return
  }

  if (!selectedContractId.value) return

  router.post(`/admin/contracts/${selectedContractId.value}/reject`, {
    reason: rejectReason.value
  }, {
    preserveScroll: true,
    onSuccess: () => {
      closeRejectModal()
      router.reload({ only: ['contracts'] })
    },
    onError: () => {
      alert('Failed to reject contract. Please try again.')
    }
  })
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>