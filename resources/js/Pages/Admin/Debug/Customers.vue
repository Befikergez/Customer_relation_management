<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Customer Debug Page</h1>
      
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Debug Information</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="font-medium text-blue-900">Pagination Info</h3>
            <p class="text-sm text-blue-700 mt-2">
              Total Customers: {{ debug_info.total_customers }}<br>
              Current Page: {{ debug_info.current_page }}<br>
              Per Page: {{ debug_info.per_page }}<br>
              Data Count: {{ debug_info.data_count }}<br>
              Has Data: {{ debug_info.has_data ? 'Yes' : 'No' }}
            </p>
          </div>
          
          <div v-if="debug_info.first_customer" class="bg-green-50 p-4 rounded-lg">
            <h3 class="font-medium text-green-900">First Customer</h3>
            <p class="text-sm text-green-700 mt-2">
              ID: {{ debug_info.first_customer.id }}<br>
              Name: {{ debug_info.first_customer.name }}<br>
              Email: {{ debug_info.first_customer.email }}<br>
              Status: {{ debug_info.first_customer.status }}<br>
              Created: {{ formatDate(debug_info.first_customer.created_at) }}
            </p>
          </div>
          
          <div v-if="debug_info.error" class="bg-red-50 p-4 rounded-lg col-span-2">
            <h3 class="font-medium text-red-900">Error</h3>
            <p class="text-sm text-red-700 mt-2">
              {{ debug_info.error }}
            </p>
          </div>
        </div>
      </div>
      
      <!-- Customers Table -->
      <div v-if="customers.data && customers.data.length > 0" class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Customers Data ({{ customers.data.length }} of {{ customers.total }})</h3>
        </div>
        
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="customer in customers.data" :key="customer.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ customer.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ customer.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ customer.email || 'No email' }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(customer.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                  {{ customer.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(customer.created_at) }}</td>
            </tr>
          </tbody>
        </table>
        
        <!-- Pagination -->
        <div v-if="customers.links && customers.links.length > 3" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ customers.meta?.from || 0 }} to {{ customers.meta?.to || 0 }} of {{ customers.meta?.total || 0 }}
            </div>
            <div class="flex space-x-2">
              <button 
                v-for="link in customers.links.slice(1, -1)"
                :key="link.label"
                @click="visitPage(link.url)"
                :disabled="!link.url"
                :class="link.active 
                  ? 'bg-blue-600 text-white' 
                  : 'bg-white text-gray-700 hover:bg-gray-50'"
                class="px-3 py-1 rounded-md text-sm font-medium"
                v-html="link.label"
              ></button>
            </div>
          </div>
        </div>
      </div>
      
      <div v-else class="bg-white rounded-lg shadow p-6 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Customers Found</h3>
        <p class="text-gray-600 mb-4">
          Database returned {{ debug_info.total_customers }} customers but Vue received 0.
        </p>
        <div class="space-y-4">
          <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-yellow-700">
                  Possible issues:<br>
                  1. Vue component not receiving data properly<br>
                  2. Data structure mismatch<br>
                  3. JavaScript error preventing rendering<br>
                </p>
              </div>
            </div>
          </div>
          
          <div class="flex space-x-3 justify-center">
            <a href="/admin/debug/check-customers" target="_blank" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
              </svg>
              Check API Response
            </a>
            <a href="/admin/customers" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700">
              Back to Customers
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  customers: Object,
  debug_info: Object
})

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const getStatusClass = (status) => {
  const classes = {
    'draft': 'bg-yellow-100 text-yellow-800',
    'accepted': 'bg-green-100 text-green-800',
    'rejected': 'bg-red-100 text-red-800',
    'contract_sent': 'bg-blue-100 text-blue-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

// Log props for debugging
onMounted(() => {
  console.log('DEBUG PAGE PROPS:', props)
  console.log('Customers data:', props.customers?.data)
  console.log('Debug info:', props.debug_info)
})
</script>