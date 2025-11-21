<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-teal-50">
        <!-- Header -->
        <div class="bg-white border-b border-slate-200 px-6 py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">Opportunities</h1>
              <p class="text-slate-600 mt-2">Manage potential business opportunities</p>
            </div>
            <button 
              v-if="permissions.create"
              @click="visitCreate"
              class="bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-700 hover:to-teal-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
            >
              <PlusIcon class="w-5 h-5" />
              <span>Create Opportunity</span>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <!-- Empty State -->
          <div v-if="!opportunities.data || opportunities.data.length === 0" class="bg-white rounded-lg border border-slate-200 p-12 text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <BriefcaseIcon class="w-10 h-10 text-blue-600" />
            </div>
            <h3 class="text-xl font-semibold text-slate-900 mb-3">No opportunities yet</h3>
            <p class="text-slate-600 mb-8 max-w-md mx-auto">Get started by creating your first business opportunity.</p>
            <button 
              v-if="permissions.create"
              @click="visitCreate"
              class="bg-gradient-to-r from-blue-600 to-teal-600 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 mx-auto"
            >
              <PlusIcon class="w-5 h-5" />
              <span>Create Your First Opportunity</span>
            </button>
          </div>

          <!-- Table -->
          <div v-else class="bg-white rounded-lg border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
              <h3 class="text-lg font-semibold text-slate-800">Opportunity List</h3>
              <p class="text-sm text-slate-600 mt-1">
                Showing {{ opportunities.meta?.from || 0 }} to {{ opportunities.meta?.to || 0 }} of {{ opportunities.meta?.total || 0 }} results
              </p>
            </div>
            
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-slate-50">
                  <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Customer Details</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Contact Information</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Location</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                  <tr 
                    v-for="opportunity in opportunities.data" 
                    :key="opportunity.id" 
                    class="hover:bg-blue-50 transition-all duration-200"
                  >
                    <td class="px-6 py-4">
                      <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center mr-4">
                          <UserCircleIcon class="w-5 h-5 text-white" />
                        </div>
                        <div>
                          <div class="font-semibold text-slate-900">{{ opportunity.potential_customer_name }}</div>
                          <div class="text-sm text-slate-500 mt-1">{{ opportunity.remarks || 'No remarks' }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-slate-900">{{ opportunity.email || 'No email' }}</div>
                      <div class="text-sm text-slate-500">{{ opportunity.phone || 'No phone' }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <a v-if="opportunity.location" :href="opportunity.location" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm flex items-center space-x-2 transition-colors">
                        <MapPinIcon class="w-4 h-4" />
                        <span>View Location</span>
                      </a>
                      <span v-else class="text-slate-400 text-sm">No location</span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-slate-900">{{ opportunity.created_by?.name || 'System' }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center space-x-2">
                        <button 
                          v-if="permissions.approve"
                          @click="approveOpportunity(opportunity.id)"
                          class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                        >
                          <CheckIcon class="w-4 h-4" />
                          <span>Approve</span>
                        </button>
                        
                        <button 
                          v-if="permissions.reject"
                          @click="openRejectModal(opportunity)"
                          class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                        >
                          <XMarkIcon class="w-4 h-4" />
                          <span>Reject</span>
                        </button>

                        <button 
                          v-if="permissions.edit"
                          @click="visitEdit(opportunity.id)"
                          class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                        >
                          <PencilIcon class="w-4 h-4" />
                          <span>Edit</span>
                        </button>
                        
                        <button 
                          v-if="permissions.delete"
                          @click="deleteOpportunity(opportunity.id)"
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
            <div v-if="opportunities.links && opportunities.links.length > 3" class="px-6 py-4 bg-white border-t border-slate-200 flex justify-between items-center">
              <div class="text-sm text-slate-700">
                Page {{ opportunities.meta?.current_page || 1 }} of {{ opportunities.meta?.last_page || 1 }}
              </div>
              <div class="flex space-x-2">
                <button 
                  v-for="link in opportunities.links"
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

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md border border-slate-200 shadow-xl">
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center">
            <XMarkIcon class="w-5 h-5 text-rose-600" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-800">Reject Opportunity</h3>
            <p class="text-slate-600 text-sm">Please provide a reason for rejection</p>
          </div>
        </div>
        
        <textarea 
          v-model="rejectReason"
          placeholder="Enter reason for rejecting this opportunity..."
          class="w-full h-32 p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm resize-none"
        ></textarea>
        
        <div class="flex justify-end space-x-3 mt-6">
          <button 
            @click="closeRejectModal" 
            class="px-4 py-2.5 text-slate-600 hover:text-slate-800 font-medium text-sm transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="confirmReject"
            :disabled="!rejectReason.trim()"
            class="bg-rose-600 hover:bg-rose-700 disabled:bg-rose-400 text-white px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg hover:shadow-xl disabled:cursor-not-allowed"
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
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  PlusIcon,
  MapPinIcon,
  CheckIcon,
  XMarkIcon,
  PencilIcon,
  TrashIcon,
  BriefcaseIcon,
  UserCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  opportunities: {
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

const showRejectModal = ref(false)
const rejectReason = ref('')
const selectedOpportunity = ref(null)

// Navigation methods
const visitCreate = () => {
  router.get('/admin/opportunities/create')
}

const visitEdit = (id) => {
  router.get(`/admin/opportunities/${id}/edit`)
}

const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

const openRejectModal = (opportunity) => {
  selectedOpportunity.value = opportunity
  showRejectModal.value = true
}

const closeRejectModal = () => {
  showRejectModal.value = false
  rejectReason.value = ''
  selectedOpportunity.value = null
}

const confirmReject = () => {
  if (!rejectReason.value.trim()) {
    alert('Please enter a reason for rejection')
    return
  }

  router.post(`/admin/opportunities/${selectedOpportunity.value.id}/reject`, {
    reason: rejectReason.value
  }, {
    onSuccess: () => {
      closeRejectModal()
    }
  })
}

const approveOpportunity = (id) => {
  if (confirm('Are you sure you want to approve this opportunity? It will be moved to Potential Customers.')) {
    router.post(`/admin/opportunities/${id}/approve`, {}, {
      onSuccess: () => {
        // Show success message or refresh data
      }
    })
  }
}

const deleteOpportunity = (id) => {
  if (confirm('Are you sure you want to delete this opportunity?')) {
    router.delete(`/admin/opportunities/${id}`, {
      onSuccess: () => {
        // Show success message or refresh data
      }
    })
  }
}
</script>