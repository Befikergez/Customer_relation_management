<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-rose-50 via-white to-blue-50">
        <!-- Header -->
        <div class="bg-white border-b border-rose-200 px-6 py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-rose-600 to-blue-600 bg-clip-text text-transparent">Customers</h1>
              <p class="text-slate-600 mt-2">Manage approved customer accounts</p>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <!-- Empty State -->
          <div v-if="!customers.data || customers.data.length === 0" class="bg-white rounded-lg border border-rose-200 p-12 text-center">
            <div class="w-20 h-20 bg-gradient-to-br from-rose-100 to-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <UserGroupIcon class="w-10 h-10 text-rose-600" />
            </div>
            <h3 class="text-xl font-semibold text-slate-900 mb-3">No customers found</h3>
            <p class="text-slate-600 mb-8 max-w-md mx-auto">Customer accounts will appear here after approval from potential customers.</p>
          </div>

          <!-- Table -->
          <div v-else class="bg-white rounded-lg border border-rose-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-rose-200 bg-rose-50">
              <h3 class="text-lg font-semibold text-slate-800">Customer List</h3>
              <p class="text-sm text-slate-600 mt-1">
                Showing {{ customers.meta?.from || 0 }} to {{ customers.meta?.to || 0 }} of {{ customers.meta?.total || 0 }} results
              </p>
            </div>
            
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-rose-50">
                  <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Customer Details</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Contact Information</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-rose-100">
                  <tr 
                    v-for="customer in customers.data" 
                    :key="customer.id" 
                    class="hover:bg-rose-50 transition-all duration-200"
                  >
                    <td class="px-6 py-4">
                      <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-rose-500 to-blue-500 rounded-xl flex items-center justify-center mr-4">
                          <UserCircleIcon class="w-5 h-5 text-white" />
                        </div>
                        <div>
                          <div class="font-semibold text-slate-900">{{ customer.name }}</div>
                          <div class="text-sm text-slate-500 mt-1">{{ customer.remarks || 'No remarks' }}</div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm font-medium text-slate-900">{{ customer.email || 'No email' }}</div>
                      <div class="text-sm text-slate-500">{{ customer.phone || 'No phone' }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="text-sm text-slate-900">{{ formatDate(customer.created_at) }}</div>
                      <div class="text-xs text-slate-500">{{ formatTime(customer.created_at) }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center space-x-2">
                        <!-- View Button -->
                        <a 
                          :href="`/admin/customers/${customer.id}`"
                          class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                        >
                          <EyeIcon class="w-4 h-4" />
                          <span>View</span>
                        </a>

                        <!-- Edit Button -->
                        <a 
                          v-if="permissions.edit"
                          :href="`/admin/customers/${customer.id}/edit`"
                          class="bg-teal-600 hover:bg-teal-700 text-white px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
                        >
                          <PencilIcon class="w-4 h-4" />
                          <span>Edit</span>
                        </a>

                        <!-- Delete Button -->
                        <button 
                          v-if="permissions.delete"
                          @click="deleteCustomer(customer.id)"
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
            <div v-if="customers.links && customers.links.length > 3" class="px-6 py-4 bg-white border-t border-rose-200 flex justify-between items-center">
              <div class="text-sm text-slate-700">
                Page {{ customers.meta?.current_page || 1 }} of {{ customers.meta?.last_page || 1 }}
              </div>
              <div class="flex space-x-2">
                <button 
                  v-for="link in customers.links"
                  :key="link.label"
                  :disabled="!link.url"
                  @click="visitPage(link.url)"
                  class="px-3 py-1.5 text-sm border rounded-lg transition-all duration-200"
                  :class="{
                    'bg-gradient-to-r from-rose-600 to-blue-600 text-white border-transparent shadow-lg': link.active,
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
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  EyeIcon,
  PencilIcon,
  TrashIcon,
  UserGroupIcon,
  UserCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  customers: Object,
  permissions: Object,
  tables: Array
})

// Helper methods
function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function formatTime(dateString) {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Navigation methods
const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

const deleteCustomer = (id) => {
  if (confirm('Are you sure you want to delete this customer? This action cannot be undone.')) {
    router.delete(`/admin/customers/${id}`, {
      onSuccess: () => {
        // Success handled by controller
      }
    })
  }
}
</script>