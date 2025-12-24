<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-teal-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1 flex flex-col">
      <!-- Fixed Header - Z-index lowered for mobile -->
      <div class="bg-white/95 backdrop-blur-lg border-b border-blue-200/60 px-6 py-6 fixed top-0 left-0 right-0 lg:left-64 flex-shrink-0 shadow-lg transition-all duration-300"
           :class="isMobile ? 'z-20' : 'z-30'">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-teal-500 rounded-xl flex items-center justify-center shadow-lg">
              <BriefcaseIcon class="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent flex items-center space-x-2">
                <span>Opportunity Details</span>
                <EyeIcon class="w-6 h-6 text-amber-500" />
              </h1>
              <p class="text-slate-600 text-sm mt-1">Complete information about this business opportunity</p>
            </div>
          </div>
          <div class="md:text-right">
            <button 
              @click="goBack"
              class="bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105 w-full md:w-auto justify-center"
            >
              <ArrowLeftIcon class="w-5 h-5" />
              <span>Back to Opportunities</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Scrollable Content with increased top padding -->
      <div class="flex-1 overflow-auto pt-40 md:pt-32 lg:pt-28">
        <div class="p-6">
          <div class="max-w-6xl mx-auto">
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
              <!-- Left Column - Customer & Contact Info -->
              <div class="lg:col-span-2 space-y-8">
                <!-- Customer Information Card -->
                <div class="bg-white rounded-2xl border border-blue-100/50 p-6 shadow-lg">
                  <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-teal-500 rounded-lg flex items-center justify-center shadow-md">
                      <UserCircleIcon class="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <h2 class="text-xl font-bold text-slate-800">Customer Information</h2>
                      <p class="text-slate-600 text-sm">Primary contact details</p>
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                      <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Customer Name</label>
                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                          <UserCircleIcon class="w-5 h-5 text-blue-500" />
                          <span class="text-lg font-bold text-slate-800">{{ opportunity.potential_customer_name }}</span>
                        </div>
                      </div>
                      <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Email Address</label>
                        <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-100">
                          <EnvelopeIcon class="w-5 h-5 text-blue-500" />
                          <span class="text-slate-700">{{ opportunity.email || 'No email provided' }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="space-y-4">
                      <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Phone Number</label>
                        <div class="flex items-center space-x-3 p-3 bg-teal-50 rounded-lg border border-teal-100">
                          <PhoneIcon class="w-5 h-5 text-teal-500" />
                          <span class="text-slate-700">{{ opportunity.phone || 'No phone provided' }}</span>
                        </div>
                      </div>
                      <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Created By</label>
                        <div class="flex items-center space-x-3 p-3 bg-slate-50 rounded-lg border border-slate-100">
                          <UserIcon class="w-5 h-5 text-slate-500" />
                          <span class="text-slate-700">{{ opportunity.created_by?.name || 'System' }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Location Information Card -->
                <div class="bg-white rounded-2xl border border-blue-100/50 p-6 shadow-lg">
                  <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center shadow-md">
                      <MapPinIcon class="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <h2 class="text-xl font-bold text-slate-800">Location Information</h2>
                      <p class="text-slate-600 text-sm">Geographical details</p>
                    </div>
                  </div>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                      <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-2">City</label>
                        <div class="flex items-center space-x-3 p-3 bg-purple-50 rounded-lg border border-purple-100">
                          <BuildingLibraryIcon class="w-5 h-5 text-purple-500" />
                          <span class="text-slate-700 font-medium">{{ opportunity.city?.name || 'No city specified' }}</span>
                        </div>
                      </div>
                      <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Subcity</label>
                        <div class="flex items-center space-x-3 p-3 bg-indigo-50 rounded-lg border border-indigo-100">
                          <BuildingOfficeIcon class="w-5 h-5 text-indigo-500" />
                          <span class="text-slate-700 font-medium">{{ opportunity.subcity?.name || 'No subcity specified' }}</span>
                        </div>
                      </div>
                    </div>
                    <div class="space-y-4">
                      <div v-if="opportunity.text_location">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Location Description</label>
                        <div class="flex items-start space-x-3 p-3 bg-amber-50 rounded-lg border border-amber-100">
                          <MapPinIcon class="w-5 h-5 text-amber-500 mt-0.5" />
                          <span class="text-slate-700 italic">{{ opportunity.text_location }}</span>
                        </div>
                      </div>
                      <div v-if="opportunity.map_location">
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Map Location</label>
                        <a :href="opportunity.map_location" target="_blank" class="flex items-center space-x-3 p-3 bg-rose-50 rounded-lg border border-rose-100 hover:bg-rose-100 transition-all duration-200 group shadow-sm hover:shadow-md">
                          <MapPinIcon class="w-5 h-5 text-rose-500 group-hover:text-rose-600" />
                          <span class="text-slate-700 font-medium group-hover:text-slate-800">View on Google Maps</span>
                          <ArrowTopRightOnSquareIcon class="w-4 h-4 text-slate-400 group-hover:text-slate-600 ml-auto" />
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column - Additional Info & Actions -->
              <div class="space-y-8">
                <!-- Additional Information Card -->
                <div class="bg-white rounded-2xl border border-blue-100/50 p-6 shadow-lg">
                  <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-500 rounded-lg flex items-center justify-center shadow-md">
                      <DocumentTextIcon class="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <h2 class="text-xl font-bold text-slate-800">Additional Information</h2>
                      <p class="text-slate-600 text-sm">Notes and metadata</p>
                    </div>
                  </div>
                  <div class="space-y-4">
                    <div>
                      <label class="block text-sm font-semibold text-slate-600 mb-2">Remarks</label>
                      <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 min-h-20">
                        <p class="text-slate-700">{{ opportunity.remarks || 'No remarks provided' }}</p>
                      </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Created</label>
                        <div class="p-2 bg-blue-50 rounded border border-blue-100">
                          <p class="text-sm text-slate-700">{{ formatDate(opportunity.created_at) }}</p>
                        </div>
                      </div>
                      <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-2">Updated</label>
                        <div class="p-2 bg-teal-50 rounded border border-teal-100">
                          <p class="text-sm text-slate-700">{{ formatDate(opportunity.updated_at) }}</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons Card - ALWAYS VISIBLE -->
                <div class="bg-white rounded-2xl border border-blue-100/50 p-6 shadow-lg">
                  <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-r from-amber-500 to-orange-500 rounded-lg flex items-center justify-center shadow-md">
                      <Cog6ToothIcon class="w-5 h-5 text-white" />
                    </div>
                    <div>
                      <h2 class="text-xl font-bold text-slate-800">Actions</h2>
                      <p class="text-slate-600 text-sm">Manage this opportunity</p>
                    </div>
                  </div>
                  <div class="space-y-3">
                    <!-- Approve Button - ALWAYS VISIBLE -->
                    <button 
                      @click="approveOpportunity"
                      class="w-full bg-gradient-to-r from-emerald-500 to-green-500 hover:from-emerald-600 hover:to-green-600 text-white px-4 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105"
                    >
                      <CheckIcon class="w-5 h-5" />
                      <span>Approve Opportunity</span>
                    </button>
                    
                    <!-- Reject Button - ALWAYS VISIBLE -->
                    <button 
                      @click="openRejectModal"
                      class="w-full bg-gradient-to-r from-rose-500 to-pink-500 hover:from-rose-600 hover:to-pink-600 text-white px-4 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105"
                    >
                      <XMarkIcon class="w-5 h-5" />
                      <span>Reject Opportunity</span>
                    </button>

                    <!-- Edit Button - ALWAYS VISIBLE -->
                    <button 
                      @click="visitEdit"
                      class="w-full bg-gradient-to-r from-blue-500 to-teal-500 hover:from-blue-600 hover:to-teal-600 text-white px-4 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105"
                    >
                      <PencilIcon class="w-5 h-5" />
                      <span>Edit Opportunity</span>
                    </button>
                    
                    <!-- Delete Button - ALWAYS VISIBLE -->
                    <button 
                      @click="deleteOpportunity"
                      class="w-full bg-gradient-to-r from-red-500 to-rose-500 hover:from-red-600 hover:to-rose-600 text-white px-4 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center space-x-2 shadow-lg hover:shadow-xl transform hover:scale-105"
                    >
                      <TrashIcon class="w-5 h-5" />
                      <span>Delete Opportunity</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md border border-blue-100/50 shadow-2xl">
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center">
            <XMarkIcon class="w-5 h-5 text-rose-500" />
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-800">Reject Opportunity</h3>
            <p class="text-slate-600 text-sm">Please provide a reason for rejection</p>
          </div>
        </div>
        
        <textarea 
          v-model="rejectReason"
          placeholder="Enter reason for rejecting this opportunity..."
          class="w-full h-32 p-4 border border-blue-200 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm resize-none transition-all duration-200"
        ></textarea>
        
        <div class="flex justify-end space-x-3 mt-6">
          <button 
            @click="closeRejectModal" 
            class="px-4 py-2.5 text-slate-600 hover:text-slate-800 font-medium text-sm transition-colors hover:bg-slate-100 rounded-lg"
          >
            Cancel
          </button>
          <button 
            @click="confirmReject"
            :disabled="!rejectReason.trim()"
            class="bg-rose-500 hover:bg-rose-600 disabled:bg-rose-300 text-white px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm shadow-lg hover:shadow-xl disabled:cursor-not-allowed transform hover:scale-105 disabled:transform-none"
          >
            Confirm Reject
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { 
  ArrowLeftIcon,
  CheckIcon,
  XMarkIcon,
  PencilIcon,
  TrashIcon,
  BriefcaseIcon,
  UserCircleIcon,
  MapPinIcon,
  EyeIcon,
  DocumentTextIcon,
  Cog6ToothIcon,
  EnvelopeIcon,
  PhoneIcon,
  UserIcon,
  BuildingLibraryIcon,
  BuildingOfficeIcon,
  ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  opportunity: {
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
  },
  cities: {
    type: Array,
    default: () => []
  },
  subcities: {
    type: Array,
    default: () => []
  }
})

const showRejectModal = ref(false)
const rejectReason = ref('')
const isMobile = ref(false)

// Check screen size
const checkScreenSize = () => {
  isMobile.value = window.innerWidth < 768
}

onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize)
})

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const goBack = () => {
  router.get('/admin/opportunities')
}

const visitEdit = () => {
  router.get(`/admin/opportunities/${props.opportunity.id}/edit`)
}

const approveOpportunity = () => {
  if (confirm('Are you sure you want to approve this opportunity? It will be moved to Potential Customers.')) {
    router.post(`/admin/opportunities/${props.opportunity.id}/approve`, {}, {
      onSuccess: () => {
        router.get('/admin/opportunities')
      }
    })
  }
}

const deleteOpportunity = () => {
  if (confirm('Are you sure you want to delete this opportunity?')) {
    router.delete(`/admin/opportunities/${props.opportunity.id}`, {
      onSuccess: () => {
        router.get('/admin/opportunities')
      }
    })
  }
}

const openRejectModal = () => {
  showRejectModal.value = true
}

const closeRejectModal = () => {
  showRejectModal.value = false
  rejectReason.value = ''
}

const confirmReject = () => {
  if (!rejectReason.value.trim()) {
    alert('Please enter a reason for rejection')
    return
  }

  router.post(`/admin/opportunities/${props.opportunity.id}/reject`, {
    reason: rejectReason.value
  }, {
    onSuccess: () => {
      closeRejectModal()
      router.get('/admin/opportunities')
    }
  })
}
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}
::-webkit-scrollbar-thumb {
  background: rgba(59, 130, 246, 0.5);
  border-radius: 8px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(59, 130, 246, 0.7);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .fixed.top-0 {
    left: 0 !important;
  }
  
  /* Even more spacing for mobile */
  .pt-40 {
    padding-top: 10rem;
  }
}
</style>