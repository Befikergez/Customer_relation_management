<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-rose-50">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-lg border-b border-blue-100/50 px-6 py-6 shadow-sm">
          <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
              <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-rose-600 bg-clip-text text-transparent">Rejected Opportunity Details</h1>
                <p class="text-slate-600 mt-2">Review rejection information and manage restoration</p>
              </div>
              <div class="flex space-x-3">
                <button 
                  v-if="permissions.create"
                  @click="restoreOpportunity"
                  class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                >
                  <ArrowPathIcon class="w-5 h-5" />
                  <span>Restore</span>
                </button>
                <button 
                  @click="goBack"
                  class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                >
                  <ArrowLeftIcon class="w-5 h-5" />
                  <span>Back to List</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-6xl mx-auto">
            <!-- Loading State -->
            <div v-if="!rejected" class="bg-white rounded-2xl shadow-lg border border-blue-100 p-12 text-center">
              <div class="animate-spin w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full mx-auto mb-4"></div>
              <p class="text-slate-600">Loading opportunity details...</p>
            </div>

            <!-- Main Content -->
            <div v-else class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
              <!-- Header Banner -->
              <div class="bg-gradient-to-r from-rose-600 to-red-600 text-white p-8">
                <div class="flex justify-between items-start">
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                      <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </div>
                    <div>
                      <h2 class="text-3xl font-bold">{{ rejected.potential_customer_name || 'Unknown Customer' }}</h2>
                      <p class="text-rose-100 mt-2 text-lg">Rejected Opportunity Record</p>
                    </div>
                  </div>
                  <span class="px-6 py-3 bg-white/20 rounded-full text-lg font-semibold border border-white/30">
                    {{ formatSource(rejected.rejected_from) }}
                  </span>
                </div>
              </div>

              <!-- Content Grid -->
              <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  <!-- Contact Information -->
                  <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-slate-800 border-b border-slate-200 pb-3 flex items-center">
                      <UserCircleIcon class="w-6 h-6 text-blue-600 mr-2" />
                      Contact Information
                    </h3>
                    
                    <div class="space-y-4">
                      <div>
                        <p class="text-sm text-slate-600 font-medium mb-2 flex items-center">
                          <EnvelopeIcon class="w-4 h-4 mr-2" />
                          Email Address
                        </p>
                        <p class="text-lg font-semibold text-slate-900">{{ rejected.email || 'Not provided' }}</p>
                      </div>

                      <div>
                        <p class="text-sm text-slate-600 font-medium mb-2 flex items-center">
                          <PhoneIcon class="w-4 h-4 mr-2" />
                          Phone Number
                        </p>
                        <p class="text-lg font-semibold text-slate-900">{{ rejected.phone || 'Not provided' }}</p>
                      </div>

                      <div>
                        <p class="text-sm text-slate-600 font-medium mb-2 flex items-center">
                          <MapPinIcon class="w-4 h-4 mr-2" />
                          Location
                        </p>
                        <a v-if="rejected.location" :href="rejected.location" target="_blank" class="text-lg font-semibold text-blue-600 hover:text-blue-800 transition-colors flex items-center">
                          View on Map 
                          <ArrowTopRightOnSquareIcon class="w-4 h-4 ml-1" />
                        </a>
                        <p v-else class="text-lg font-semibold text-slate-500">Not provided</p>
                      </div>
                    </div>
                  </div>

                  <!-- Rejection Information -->
                  <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-slate-800 border-b border-slate-200 pb-3 flex items-center">
                      <ExclamationTriangleIcon class="w-6 h-6 text-rose-600 mr-2" />
                      Rejection Information
                    </h3>
                    
                    <div class="space-y-4">
                      <div>
                        <p class="text-sm text-slate-600 font-medium mb-2">Rejected By</p>
                        <p class="text-lg font-semibold text-slate-900">{{ rejected.created_by?.name || 'System' }}</p>
                      </div>

                      <div>
                        <p class="text-sm text-slate-600 font-medium mb-2">Rejected At</p>
                        <p class="text-lg font-semibold text-slate-900">{{ formatDate(rejected.created_at) }}</p>
                      </div>

                      <div>
                        <p class="text-sm text-slate-600 font-medium mb-2">Original Opportunity ID</p>
                        <p class="text-lg font-semibold text-slate-900">{{ rejected.opportunity_id || 'N/A' }}</p>
                      </div>

                      <div>
                        <p class="text-sm text-slate-600 font-medium mb-2">Source</p>
                        <span class="px-3 py-1.5 rounded-full text-sm font-semibold border" :class="getSourceBadgeClass(rejected.rejected_from)">
                          {{ formatSource(rejected.rejected_from) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Rejection Reason -->
                <div class="mt-8 pt-8 border-t border-slate-200">
                  <h3 class="text-xl font-semibold text-slate-800 mb-4 flex items-center">
                    <DocumentTextIcon class="w-6 h-6 text-rose-600 mr-2" />
                    Rejection Reason
                  </h3>
                  <div class="bg-rose-50 rounded-xl p-6 border-l-4 border-rose-500">
                    <p class="text-rose-900 text-lg leading-relaxed">{{ rejected.reason || 'No reason provided' }}</p>
                  </div>
                </div>

                <!-- Remarks -->
                <div v-if="rejected.remarks" class="mt-6 pt-6 border-t border-slate-200">
                  <h3 class="text-xl font-semibold text-slate-800 mb-4 flex items-center">
                    <ChatBubbleLeftRightIcon class="w-6 h-6 text-blue-600 mr-2" />
                    Additional Remarks
                  </h3>
                  <div class="bg-blue-50 rounded-xl p-6 border-l-4 border-blue-500">
                    <p class="text-blue-900 text-lg leading-relaxed">{{ rejected.remarks }}</p>
                  </div>
                </div>

                <!-- Additional Information -->
                <div class="mt-6 pt-6 border-t border-slate-200">
                  <h3 class="text-xl font-semibold text-slate-800 mb-4 flex items-center">
                    <InformationCircleIcon class="w-6 h-6 text-slate-600 mr-2" />
                    Additional Information
                  </h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Record ID</p>
                      <p class="text-lg font-semibold text-slate-900">{{ rejected.id }}</p>
                    </div>
                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Last Updated</p>
                      <p class="text-lg font-semibold text-slate-900">{{ formatDate(rejected.updated_at) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Buttons Footer -->
            <div class="mt-6 flex justify-end space-x-4">
              <button 
                v-if="permissions.delete"
                @click="deleteOpportunity"
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
              >
                <TrashIcon class="w-5 h-5" />
                <span>Delete Permanently</span>
              </button>
              
              <button 
                v-if="permissions.create"
                @click="restoreOpportunity"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
              >
                <ArrowPathIcon class="w-5 h-5" />
                <span>Restore Opportunity</span>
              </button>
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
  ArrowPathIcon,
  TrashIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon,
  UserCircleIcon,
  ExclamationTriangleIcon,
  DocumentTextIcon,
  ChatBubbleLeftRightIcon,
  InformationCircleIcon,
  ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  rejected: {
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
  }
})

// Helper methods
const getSourceBadgeClass = (source) => {
  const classes = {
    opportunity: 'bg-purple-100 text-purple-800 border-purple-200',
    potential_customer: 'bg-blue-100 text-blue-800 border-blue-200',
    customer: 'bg-green-100 text-green-800 border-green-200'
  }
  return classes[source] || 'bg-slate-100 text-slate-800 border-slate-200'
}

const formatSource = (source) => {
  const sources = {
    opportunity: 'Opportunity',
    potential_customer: 'Potential Customer',
    customer: 'Customer'
  }
  return sources[source] || source
}

const formatDate = (dateString) => {
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
  router.get('/admin/rejected-opportunities')
}

const restoreOpportunity = () => {
  if (confirm('Are you sure you want to restore this opportunity? It will be moved back to its original location.')) {
    router.post(`/admin/rejected-opportunities/${props.rejected.id}/revert`, {}, {
      onSuccess: () => {
        // Success handled by controller
      },
      onError: (errors) => {
        alert('Failed to restore opportunity. Please try again.')
      }
    })
  }
}

const deleteOpportunity = () => {
  if (confirm('Are you sure you want to permanently delete this rejected opportunity? This action cannot be undone.')) {
    router.delete(`/admin/rejected-opportunities/${props.rejected.id}`, {
      onSuccess: () => {
        router.get('/admin/rejected-opportunities')
      },
      onError: (errors) => {
        alert('Failed to delete opportunity. Please try again.')
      }
    })
  }
}
</script>