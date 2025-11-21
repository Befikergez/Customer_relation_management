<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-rose-50 via-white to-blue-50">
        <!-- Header -->
        <div class="bg-white border-b border-rose-200 px-6 py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-rose-600 to-blue-600 bg-clip-text text-transparent">Customer Details</h1>
              <p class="text-slate-600 mt-2">View and manage customer information</p>
            </div>
            <div class="flex space-x-3">
              <button 
                @click="goBack"
                class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
              >
                <ArrowLeftIcon class="w-5 h-5" />
                <span>Back to Customers</span>
              </button>
              <a 
                v-if="permissions.edit"
                :href="`/admin/customers/${customer.id}/edit`"
                class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
              >
                <PencilIcon class="w-5 h-5" />
                <span>Edit Customer</span>
              </a>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-6xl mx-auto">
            <div class="bg-white rounded-lg border border-rose-200 overflow-hidden">
              <!-- Header Banner -->
              <div class="bg-gradient-to-r from-rose-600 to-blue-600 text-white p-8">
                <div class="flex justify-between items-start">
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                      <UserCircleIcon class="w-8 h-8 text-white" />
                    </div>
                    <div>
                      <h2 class="text-3xl font-bold">{{ customer.name }}</h2>
                      <p class="text-rose-100 mt-2 text-lg">Approved Customer Account</p>
                    </div>
                  </div>
                  <span class="px-6 py-3 bg-white/20 rounded-full text-lg font-semibold border border-white/30">
                    Active Customer
                  </span>
                </div>
              </div>

              <!-- Content -->
              <div class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  <!-- Contact Information -->
                  <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-slate-800 border-b border-slate-200 pb-3">Contact Information</h3>
                    
                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Email Address</p>
                      <p class="text-lg font-semibold text-slate-900">{{ customer.email || 'Not provided' }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Phone Number</p>
                      <p class="text-lg font-semibold text-slate-900">{{ customer.phone || 'Not provided' }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Location</p>
                      <a v-if="customer.location" :href="customer.location" target="_blank" class="text-lg font-semibold text-blue-600 hover:text-blue-800 transition-colors flex items-center">
                        View on Map 
                        <ArrowTopRightOnSquareIcon class="w-4 h-4 ml-1" />
                      </a>
                      <p v-else class="text-lg font-semibold text-slate-500">Not provided</p>
                    </div>
                  </div>

                  <!-- Additional Information -->
                  <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-slate-800 border-b border-slate-200 pb-3">Additional Information</h3>
                    
                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Created At</p>
                      <p class="text-lg font-semibold text-slate-900">{{ formatDate(customer.created_at) }}</p>
                    </div>

                    <div>
                      <p class="text-sm text-slate-600 font-medium mb-2">Last Updated</p>
                      <p class="text-lg font-semibold text-slate-900">{{ formatDate(customer.updated_at) }}</p>
                    </div>

                    <div v-if="customer.proposal">
                      <p class="text-sm text-slate-600 font-medium mb-2">Created From Proposal</p>
                      <a 
                        :href="`/admin/proposals/${customer.proposal.id}`"
                        class="text-lg font-semibold text-blue-600 hover:text-blue-800 transition-colors flex items-center"
                      >
                        View Proposal
                        <ArrowTopRightOnSquareIcon class="w-4 h-4 ml-1" />
                      </a>
                    </div>
                  </div>
                </div>

                <!-- Remarks -->
                <div class="mt-8 pt-8 border-t border-slate-200">
                  <h3 class="text-xl font-semibold text-slate-800 mb-4">Remarks</h3>
                  <div class="bg-blue-50 rounded-lg p-6 border-l-4 border-blue-500">
                    <p class="text-blue-900 text-lg leading-relaxed">{{ customer.remarks || 'No remarks provided.' }}</p>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-4 mt-8 pt-8 border-t border-slate-200">
                  <button 
                    v-if="permissions.delete"
                    @click="deleteCustomer"
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                  >
                    <TrashIcon class="w-5 h-5" />
                    <span>Delete Customer</span>
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
  UserCircleIcon,
  ArrowTopRightOnSquareIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  customer: Object,
  permissions: Object,
  tables: Array
})

function formatDate(dateString) {
  if (!dateString) return 'Unknown date'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const goBack = () => {
  router.get('/admin/customers')
}

const deleteCustomer = () => {
  if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
    router.delete(`/admin/customers/${props.customer.id}`)
  }
}
</script>