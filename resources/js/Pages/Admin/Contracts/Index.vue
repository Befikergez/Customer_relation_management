<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-teal-50">
        <!-- Header -->
        <div class="bg-white border-b border-blue-200 px-6 py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">Contracts</h1>
              <p class="text-slate-600 mt-2">Manage customer contracts and agreements</p>
            </div>
            <a 
              v-if="permissions.create"
              href="/admin/contracts/create"
              class="bg-gradient-to-r from-teal-600 to-blue-600 hover:from-teal-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
            >
              <PlusIcon class="w-5 h-5" />
              <span>Create Contract</span>
            </a>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <!-- Stats Overview -->
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl border border-blue-200 p-6 text-center shadow-lg hover:shadow-xl transition-all duration-300">
              <div class="text-3xl font-bold text-slate-900 mb-3">{{ totalContracts }}</div>
              <div class="text-sm font-semibold text-slate-700 uppercase tracking-wide">Total Contracts</div>
              <div class="w-16 h-1 bg-gradient-to-r from-blue-400 to-teal-400 rounded-full mx-auto mt-4"></div>
            </div>
            
            <div class="bg-white rounded-2xl border border-teal-200 p-6 text-center shadow-lg hover:shadow-xl transition-all duration-300">
              <div class="text-3xl font-bold text-teal-600 mb-3">{{ unsignedCount }}</div>
              <div class="text-sm font-semibold text-teal-600 uppercase tracking-wide">Unsigned</div>
              <div class="w-16 h-1 bg-teal-500 rounded-full mx-auto mt-4"></div>
            </div>
            
            <div class="bg-white rounded-2xl border border-green-200 p-6 text-center shadow-lg hover:shadow-xl transition-all duration-300">
              <div class="text-3xl font-bold text-green-600 mb-3">{{ signedCount }}</div>
              <div class="text-sm font-semibold text-green-600 uppercase tracking-wide">Signed</div>
              <div class="w-16 h-1 bg-green-500 rounded-full mx-auto mt-4"></div>
            </div>
            
            <div class="bg-white rounded-2xl border border-rose-200 p-6 text-center shadow-lg hover:shadow-xl transition-all duration-300">
              <div class="text-3xl font-bold text-rose-600 mb-3">{{ rejectedCount }}</div>
              <div class="text-sm font-semibold text-rose-600 uppercase tracking-wide">Rejected</div>
              <div class="w-16 h-1 bg-rose-500 rounded-full mx-auto mt-4"></div>
            </div>
          </div>

          <!-- Filter Buttons -->
          <div class="flex flex-wrap gap-3 mb-6">
            <button 
              v-for="filter in statusFilters"
              :key="filter.key"
              @click="setStatusFilter(filter.key)"
              class="px-4 py-2.5 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-sm"
              :class="getFilterButtonClass(filter.key)"
            >
              <span class="flex items-center space-x-2">
                <span>{{ filter.label }}</span>
                <span class="text-xs bg-white/30 px-1.5 py-0.5 rounded-full">
                  {{ getFilterCount(filter.key) }}
                </span>
              </span>
            </button>
          </div>

          <!-- Empty State -->
          <div v-if="!contracts.data || contracts.data.length === 0" class="bg-white rounded-2xl border-2 border-dashed border-blue-200 p-16 text-center">
            <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-teal-100 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
              <DocumentTextIcon class="w-12 h-12 text-blue-500" />
            </div>
            <h3 class="text-2xl font-semibold text-slate-900 mb-3">No contracts found</h3>
            <p class="text-slate-600 text-lg mb-8 max-w-md mx-auto">Create your first contract to get started with customer agreements.</p>
            <a 
              v-if="permissions.create"
              href="/admin/contracts/create"
              class="bg-gradient-to-r from-teal-600 to-blue-600 text-white px-8 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl inline-flex items-center space-x-2"
            >
              <PlusIcon class="w-5 h-5" />
              <span>Create Your First Contract</span>
            </a>
          </div>

          <!-- Table -->
          <div v-else class="bg-white rounded-lg border border-blue-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-blue-200 bg-blue-50">
              <h3 class="text-lg font-semibold text-slate-800">Contract List</h3>
              <p class="text-sm text-slate-600 mt-1">
                Showing {{ contracts.meta?.from || 0 }} to {{ contracts.meta?.to || 0 }} of {{ contracts.meta?.total || 0 }} results
              </p>
            </div>
            
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-blue-50">
                  <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Contract Details</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Customer & Proposal</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Value & Dates</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-blue-100">
                  <tr 
                    v-for="contract in filteredContracts" 
                    :key="contract.id" 
                    class="hover:bg-blue-50 transition-all duration-200"
                  >
                    <td class="px-6 py-4">
                      <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center mr-4">
                          <DocumentTextIcon class="w-5 h-5 text-white" />
                        </div>
                        <div>
                          <div class="font-semibold text-slate-900">{{ contract.contract_title }}</div>
                          <div class="text-sm text-slate-500 mt-1">{{ formatDate(contract.created_at) }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-slate-900">{{ contract.customer?.name || 'No customer' }}</div>
                      <div class="text-sm text-slate-500">Proposal: {{ contract.proposal?.title || 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-slate-900">${{ contract.total_value }}</div>
                      <div class="text-sm text-slate-500">{{ formatDate(contract.start_date) }} to {{ formatDate(contract.end_date) }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <span :class="getStatusBadgeClass(contract.status)" class="px-3 py-1.5 rounded-full text-xs font-semibold border">
                        {{ formatStatus(contract.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center space-x-2">
                        <!-- View Button -->
                        <a 
                          :href="`/admin/contracts/${contract.id}`"
                          class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                        >
                          <EyeIcon class="w-4 h-4" />
                          <span>View</span>
                        </a>

                        <!-- Edit Button -->
                        <a 
                          v-if="permissions.edit"
                          :href="`/admin/contracts/${contract.id}/edit`"
                          class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                        >
                          <PencilIcon class="w-4 h-4" />
                          <span>Edit</span>
                        </a>

                        <!-- Approve Button -->
                        <button 
                          v-if="permissions.approve && contract.status === 'unsigned'"
                          @click="approveContract(contract.id)"
                          class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                        >
                          <CheckIcon class="w-4 h-4" />
                          <span>Approve</span>
                        </button>

                        <!-- Reject Button -->
                        <button 
                          v-if="permissions.reject && contract.status === 'unsigned'"
                          @click="rejectContract(contract.id)"
                          class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                        >
                          <XMarkIcon class="w-4 h-4" />
                          <span>Reject</span>
                        </button>

                        <!-- Delete Button -->
                        <button 
                          v-if="permissions.delete"
                          @click="deleteContract(contract.id)"
                          class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                        >
                          <TrashIcon class="w-4 h-4" />
                          <span>Delete</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="contracts.links && contracts.links.length > 3" class="px-6 py-4 bg-white border-t border-blue-200 flex justify-between items-center">
              <div class="text-sm text-slate-700">
                Page {{ contracts.meta?.current_page || 1 }} of {{ contracts.meta?.last_page || 1 }}
              </div>
              <div class="flex space-x-2">
                <button 
                  v-for="link in contracts.links"
                  :key="link.label"
                  :disabled="!link.url"
                  @click="visitPage(link.url)"
                  class="px-3 py-1.5 text-sm border rounded-lg transition-all duration-200"
                  :class="{
                    'bg-gradient-to-r from-blue-600 to-teal-600 text-white border-transparent shadow-lg': link.active,
                    'text-slate-600 border-slate-300 hover:bg-slate-50 hover:border-slate-400': !link.active && link.url,
                    'text-slate-400 border-slate-200 cursor-not-allowed': !link.url
                  }"
                  v-html="link.label"
                ></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  EyeIcon,
  PencilIcon,
  TrashIcon,
  CheckIcon,
  XMarkIcon,
  DocumentTextIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  contracts: Object,
  permissions: Object,
  tables: Array
})

const statusFilter = ref('all')

// Status filters
const statusFilters = [
  { key: 'all', label: 'All' },
  { key: 'unsigned', label: 'Unsigned' },
  { key: 'signed', label: 'Signed' },
  { key: 'rejected', label: 'Rejected' }
]

// Computed properties
const totalContracts = computed(() => {
  return props.contracts.data?.length || 0
})

const unsignedCount = computed(() => {
  return props.contracts.data?.filter(c => c.status === 'unsigned').length || 0
})

const signedCount = computed(() => {
  return props.contracts.data?.filter(c => c.status === 'signed').length || 0
})

const rejectedCount = computed(() => {
  return props.contracts.data?.filter(c => c.status === 'rejected').length || 0
})

const filteredContracts = computed(() => {
  if (!props.contracts.data) return []
  
  if (statusFilter.value === 'all') {
    return props.contracts.data
  }
  
  return props.contracts.data.filter(contract => contract.status === statusFilter.value)
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

function getFilterButtonClass(filterKey) {
  const baseClasses = 'px-4 py-2.5 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-sm'
  
  if (filterKey === statusFilter.value) {
    return `${baseClasses} bg-gradient-to-r from-blue-600 to-teal-600 text-white shadow-lg scale-105`
  }
  
  return `${baseClasses} text-slate-600 bg-white border border-slate-300 hover:bg-slate-50 hover:border-slate-400`
}

function getFilterCount(filterKey) {
  if (filterKey === 'all') return totalContracts.value
  if (filterKey === 'unsigned') return unsignedCount.value
  if (filterKey === 'signed') return signedCount.value
  if (filterKey === 'rejected') return rejectedCount.value
  return 0
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

// Navigation methods
const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

const setStatusFilter = (filter) => {
  statusFilter.value = filter
}

const approveContract = (id) => {
  if (confirm('Are you sure you want to approve this contract? It will be marked as signed.')) {
    router.post(`/admin/contracts/${id}/approve`, {}, {
      onSuccess: () => {
        // Success handled by controller
      }
    })
  }
}

const rejectContract = (id) => {
  if (confirm('Are you sure you want to reject this contract?')) {
    router.post(`/admin/contracts/${id}/reject`, {}, {
      onSuccess: () => {
        // Success handled by controller
      }
    })
  }
}

const deleteContract = (id) => {
  if (confirm('Are you sure you want to delete this contract? This action cannot be undone.')) {
    router.delete(`/admin/contracts/${id}`, {
      onSuccess: () => {
        // Success handled by controller
      }
    })
  }
}
</script>