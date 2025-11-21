<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-teal-50 via-white to-blue-50">
        <!-- Header -->
        <div class="bg-white border-b border-teal-200 px-4 sm:px-6 py-4 sm:py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-teal-600 to-blue-600 bg-clip-text text-transparent">Potential Customers</h1>
              <p class="text-slate-600 mt-1 sm:mt-2 text-sm sm:text-base">Manage and review potential customer leads</p>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4 sm:p-6">
          <!-- Status Filter Buttons - Small Compact Layout -->
          <div class="flex flex-wrap gap-2 mb-6 sm:mb-8">
            <button 
              v-for="filter in statusFilters"
              :key="filter.key"
              @click="setStatusFilter(filter.key)"
              class="px-3 py-2 text-xs font-medium rounded-lg transition-all duration-200 flex items-center space-x-2 border"
              :class="getFilterButtonClass(filter.key)"
            >
              <span class="font-bold">{{ getFilterCount(filter.key) }}</span>
              <span>{{ filter.label }}</span>
            </button>
          </div>

          <!-- Empty State -->
          <div v-if="!hasCustomers" class="bg-white rounded-lg border border-teal-200 p-6 sm:p-8 md:p-12 text-center">
            <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-teal-100 to-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4 sm:mb-6">
              <UserGroupIcon class="w-8 h-8 sm:w-10 sm:h-10 text-teal-600" />
            </div>
            <h3 class="text-lg sm:text-xl font-semibold text-slate-900 mb-2 sm:mb-3">No potential customers found</h3>
            <p class="text-slate-600 mb-6 sm:mb-8 max-w-md mx-auto text-sm sm:text-base">Potential customer leads will appear here after approval from opportunities.</p>
          </div>

          <!-- Table -->
          <div v-else class="bg-white rounded-lg border border-teal-200 overflow-hidden">
            <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-teal-200 bg-teal-50">
              <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-0">
                <div>
                  <h3 class="text-base sm:text-lg font-semibold text-slate-800">Customer Leads</h3>
                  <p class="text-xs sm:text-sm text-slate-600 mt-1">
                    Showing {{ potentialCustomers.meta?.from || 0 }} to {{ potentialCustomers.meta?.to || 0 }} of {{ potentialCustomers.meta?.total || 0 }} results
                  </p>
                </div>
              </div>
            </div>
            
            <div class="overflow-x-auto">
              <table class="w-full min-w-[800px]">
                <thead class="bg-teal-50">
                  <tr>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-slate-700 uppercase tracking-wider">Customer Details</th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-slate-700 uppercase tracking-wider">Contact</th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-slate-700 uppercase tracking-wider">Proposals</th>
                    <th class="px-4 sm:px-6 py-3 text-left text-xs sm:text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-teal-100">
                  <tr 
                    v-for="customer in filteredCustomers" 
                    :key="customer?.id" 
                    class="hover:bg-teal-50 transition-all duration-200"
                  >
                    <td class="px-4 sm:px-6 py-3 sm:py-4">
                      <div class="flex items-center">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-teal-500 to-blue-500 rounded-lg sm:rounded-xl flex items-center justify-center mr-3 sm:mr-4">
                          <UserCircleIcon class="w-4 h-4 sm:w-5 sm:h-5 text-white" />
                        </div>
                        <div>
                          <div class="font-semibold text-slate-900 text-sm sm:text-base">{{ customer?.potential_customer_name || 'Unknown Customer' }}</div>
                          <div class="text-xs sm:text-sm text-slate-500 mt-1">{{ customer?.location || 'No location' }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-4 sm:px-6 py-3 sm:py-4">
                      <div class="text-sm font-medium text-slate-900">{{ customer?.email || 'No email' }}</div>
                      <div class="text-xs sm:text-sm text-slate-500">{{ customer?.phone || 'No phone' }}</div>
                    </td>
                    <td class="px-4 sm:px-6 py-3 sm:py-4">
                      <span :class="getStatusBadgeClass(customer?.status)" class="px-2 py-1 sm:px-3 sm:py-1.5 rounded-full text-xs font-semibold border">
                        {{ formatStatus(customer?.status) }}
                      </span>
                    </td>
                    <td class="px-4 sm:px-6 py-3 sm:py-4">
                      <div class="text-sm text-slate-900">
                        {{ customer?.proposals?.length || 0 }} proposal(s)
                      </div>
                      <div v-if="customer?.proposals && customer.proposals.length > 0" class="text-xs text-slate-500">
                        Latest: {{ formatDate(customer.proposals[0]?.created_at) }}
                      </div>
                    </td>
                    <td class="px-4 sm:px-6 py-3 sm:py-4">
                      <div class="flex flex-wrap gap-1 sm:gap-2">
                        <!-- View Button -->
                        <a 
                          v-if="customer?.id"
                          :href="`/admin/potential-customers/${customer.id}`"
                          class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1.5 rounded text-xs font-medium transition-all duration-200 flex items-center space-x-1 shadow hover:shadow-lg"
                        >
                          <EyeIcon class="w-3 h-3" />
                          <span>View</span>
                        </a>

                        <!-- Edit Button - Always Visible -->
                        <button 
                          v-if="customer?.id"
                          @click="editCustomer(customer.id)"
                          class="bg-amber-600 hover:bg-amber-700 text-white px-2 py-1.5 rounded text-xs font-medium transition-all duration-200 flex items-center space-x-1 shadow hover:shadow-lg"
                        >
                          <PencilIcon class="w-3 h-3" />
                          <span>Edit</span>
                        </button>

                        <!-- Create Proposal Button (Only for draft status) -->
                        <button 
                          v-if="customer?.status === 'draft' && customer?.id"
                          @click="createProposal(customer)"
                          class="bg-rose-600 hover:bg-rose-700 text-white px-2 py-1.5 rounded text-xs font-medium transition-all duration-200 flex items-center space-x-1 shadow hover:shadow-lg"
                        >
                          <DocumentTextIcon class="w-3 h-3" />
                          <span>Proposal</span>
                        </button>

                        <!-- Approve Button (Only for draft and proposal_sent status) -->
                        <button 
                          v-if="(customer?.status === 'draft' || customer?.status === 'proposal_sent') && customer?.id"
                          @click="approveCustomer(customer.id)"
                          class="bg-green-600 hover:bg-green-700 text-white px-2 py-1.5 rounded text-xs font-medium transition-all duration-200 flex items-center space-x-1 shadow hover:shadow-lg"
                        >
                          <CheckIcon class="w-3 h-3" />
                          <span>Approve</span>
                        </button>

                        <!-- Reject Button (Only for draft and proposal_sent status) -->
                        <button 
                          v-if="(customer?.status === 'draft' || customer?.status === 'proposal_sent') && customer?.id"
                          @click="showRejectModal(customer)"
                          class="bg-red-600 hover:bg-red-700 text-white px-2 py-1.5 rounded text-xs font-medium transition-all duration-200 flex items-center space-x-1 shadow hover:shadow-lg"
                        >
                          <XMarkIcon class="w-3 h-3" />
                          <span>Reject</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div v-if="potentialCustomers.links && potentialCustomers.links.length > 3" class="px-4 sm:px-6 py-3 sm:py-4 bg-white border-t border-teal-200 flex flex-col sm:flex-row justify-between items-center gap-3 sm:gap-0">
              <div class="text-xs sm:text-sm text-slate-700">
                Page {{ potentialCustomers.meta?.current_page || 1 }} of {{ potentialCustomers.meta?.last_page || 1 }}
              </div>
              <div class="flex flex-wrap gap-1 sm:gap-2">
                <button 
                  v-for="link in potentialCustomers.links"
                  :key="link.label"
                  :disabled="!link.url"
                  @click="visitPage(link.url)"
                  class="px-2 py-1 sm:px-3 sm:py-1.5 text-xs sm:text-sm border rounded transition-all duration-200 min-w-[2rem]"
                  :class="{
                    'bg-gradient-to-r from-teal-600 to-blue-600 text-white border-transparent shadow': link.active,
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

    <!-- Reject Modal -->
    <div v-if="showRejectModalVisible" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
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
          v-model="rejectReason"
          placeholder="Enter reason for rejecting this customer..."
          class="w-full h-24 sm:h-32 p-3 sm:p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm resize-none"
        ></textarea>
        
        <div class="flex justify-end space-x-2 sm:space-x-3 mt-4 sm:mt-6">
          <button 
            @click="closeRejectModal" 
            class="px-3 py-2 sm:px-4 sm:py-2.5 text-slate-600 hover:text-slate-800 font-medium text-xs sm:text-sm transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="confirmReject"
            :disabled="!rejectReason.trim()"
            class="bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white px-4 py-2 sm:px-6 sm:py-2.5 rounded-lg font-semibold transition-all duration-200 text-xs sm:text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
          >
            Confirm Reject
          </button>
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
  CheckIcon,
  XMarkIcon,
  DocumentTextIcon,
  UserGroupIcon,
  UserCircleIcon,
  PencilIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  potentialCustomers: Object,
  permissions: Object,
  tables: Array
})

const showRejectModalVisible = ref(false)
const rejectReason = ref('')
const selectedCustomer = ref(null)
const statusFilter = ref('all')

// Status filters
const statusFilters = [
  { key: 'all', label: 'All Leads' },
  { key: 'draft', label: 'Draft' },
  { key: 'proposal_sent', label: 'Proposal Sent' },
  { key: 'accepted', label: 'Accepted' },
  { key: 'rejected', label: 'Rejected' }
]

// Computed properties with safe null checks
const hasCustomers = computed(() => {
  return props.potentialCustomers?.data?.length > 0
})

const totalCustomers = computed(() => {
  return props.potentialCustomers?.data?.length || 0
})

const draftCount = computed(() => {
  return props.potentialCustomers?.data?.filter(c => c?.status === 'draft').length || 0
})

const proposalSentCount = computed(() => {
  return props.potentialCustomers?.data?.filter(c => c?.status === 'proposal_sent').length || 0
})

const acceptedCount = computed(() => {
  return props.potentialCustomers?.data?.filter(c => c?.status === 'accepted').length || 0
})

const rejectedCount = computed(() => {
  return props.potentialCustomers?.data?.filter(c => c?.status === 'rejected').length || 0
})

const filteredCustomers = computed(() => {
  if (!props.potentialCustomers?.data) return []
  
  if (statusFilter.value === 'all') {
    return props.potentialCustomers.data.filter(customer => customer != null)
  }
  
  return props.potentialCustomers.data.filter(customer => customer?.status === statusFilter.value)
})

// Helper methods
function getFilterCount(filterKey) {
  switch (filterKey) {
    case 'all': return totalCustomers.value
    case 'draft': return draftCount.value
    case 'proposal_sent': return proposalSentCount.value
    case 'accepted': return acceptedCount.value
    case 'rejected': return rejectedCount.value
    default: return 0
  }
}

function getStatusBadgeClass(status) {
  if (!status) return 'bg-slate-100 text-slate-800 border-slate-200'
  
  const classes = {
    draft: 'bg-blue-100 text-blue-800 border-blue-200',
    proposal_sent: 'bg-rose-100 text-rose-800 border-rose-200',
    accepted: 'bg-green-100 text-green-800 border-green-200',
    rejected: 'bg-red-100 text-red-800 border-red-200'
  }
  return classes[status] || 'bg-slate-100 text-slate-800 border-slate-200'
}

function getFilterButtonClass(filterKey) {
  if (filterKey === statusFilter.value) {
    return 'bg-gradient-to-r from-teal-600 to-blue-600 text-white shadow border-transparent'
  }
  
  return 'text-slate-600 bg-white border-slate-300 hover:bg-slate-50 hover:border-slate-400'
}

function formatStatus(status) {
  if (!status) return 'Unknown'
  
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

const createProposal = (customer) => {
  if (!customer?.id) return
  window.location.href = `/admin/proposals/create?potential_customer_id=${customer.id}`
}

const editCustomer = (id) => {
  if (!id) return
  window.location.href = `/admin/potential-customers/${id}/edit`
}

const approveCustomer = (id) => {
  if (!id) return
  
  if (confirm('Are you sure you want to approve this potential customer? It will be moved to customers but kept here with accepted status.')) {
    router.post(`/admin/potential-customers/${id}/approve`, {}, {
      onSuccess: () => {
        // Success handled by controller
      }
    })
  }
}

const showRejectModal = (customer) => {
  if (!customer?.id) return
  selectedCustomer.value = customer
  showRejectModalVisible.value = true
}

const closeRejectModal = () => {
  showRejectModalVisible.value = false
  rejectReason.value = ''
  selectedCustomer.value = null
}

const confirmReject = () => {
  if (!rejectReason.value.trim()) {
    alert('Please enter a reason for rejection')
    return
  }

  if (!selectedCustomer.value?.id) return

  router.post(`/admin/potential-customers/${selectedCustomer.value.id}/reject`, {
    reason: rejectReason.value
  }, {
    onSuccess: () => {
      closeRejectModal()
    }
  })
}
</script>