<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-rose-50 via-white to-blue-50">
        <!-- Header -->
        <div class="bg-white border-b border-rose-200 px-6 py-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-3xl font-bold bg-gradient-to-r from-rose-600 to-blue-600 bg-clip-text text-transparent">Edit Customer</h1>
              <p class="text-slate-600 mt-2">Update customer information</p>
            </div>
            <button 
              @click="goBack"
              class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
            >
              <ArrowLeftIcon class="w-5 h-5" />
              <span>Back to Customers</span>
            </button>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-lg border border-rose-200 p-8">
              <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Customer Name -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Customer Name *</label>
                    <input 
                      v-model="form.name"
                      type="text" 
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm transition-all duration-200"
                      placeholder="Enter customer name"
                      required
                    >
                    <div v-if="form.errors.name" class="text-red-600 text-sm mt-2">
                      {{ form.errors.name }}
                    </div>
                  </div>

                  <!-- Email -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Email Address</label>
                    <input 
                      v-model="form.email"
                      type="email" 
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200"
                      placeholder="customer@example.com"
                    >
                    <div v-if="form.errors.email" class="text-red-600 text-sm mt-2">
                      {{ form.errors.email }}
                    </div>
                  </div>

                  <!-- Phone -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Phone Number</label>
                    <input 
                      v-model="form.phone"
                      type="tel" 
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200"
                      placeholder="+1234567890"
                    >
                    <div v-if="form.errors.phone" class="text-red-600 text-sm mt-2">
                      {{ form.errors.phone }}
                    </div>
                  </div>

                  <!-- Location -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Location (Map Link)</label>
                    <input 
                      v-model="form.location"
                      type="url" 
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm transition-all duration-200"
                      placeholder="https://maps.google.com/..."
                    >
                    <div v-if="form.errors.location" class="text-red-600 text-sm mt-2">
                      {{ form.errors.location }}
                    </div>
                  </div>

                  <!-- Remarks -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Remarks</label>
                    <textarea 
                      v-model="form.remarks"
                      rows="4"
                      class="w-full p-4 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm transition-all duration-200 resize-none"
                      placeholder="Additional notes or comments about this customer..."
                    ></textarea>
                    <div v-if="form.errors.remarks" class="text-red-600 text-sm mt-2">
                      {{ form.errors.remarks }}
                    </div>
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-slate-200">
                  <button 
                    type="button"
                    @click="goBack"
                    class="px-6 py-3 text-slate-600 hover:text-slate-800 font-semibold text-sm transition-colors"
                  >
                    Cancel
                  </button>
                  <button 
                    type="submit"
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-rose-600 to-blue-600 hover:from-rose-700 hover:to-blue-700 disabled:from-slate-400 disabled:to-slate-500 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl flex items-center space-x-2"
                  >
                    <CheckIcon class="w-5 h-5" />
                    <span>{{ form.processing ? 'Updating...' : 'Update Customer' }}</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { 
  ArrowLeftIcon, 
  CheckIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  customer: Object,
  tables: {
    type: Array,
    default: () => []
  },
  permissions: {
    type: Object,
    default: () => ({})
  }
})

const form = useForm({
  name: props.customer.name,
  email: props.customer.email,
  phone: props.customer.phone,
  location: props.customer.location,
  remarks: props.customer.remarks
})

const submit = () => {
  form.put(`/admin/customers/${props.customer.id}`, {
    onSuccess: () => {
      router.get('/admin/customers')
    }
  })
}

const goBack = () => {
  router.get('/admin/customers')
}
</script>