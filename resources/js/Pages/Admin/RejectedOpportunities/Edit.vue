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
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-rose-600 bg-clip-text text-transparent">Edit Rejected Opportunity</h1>
                <p class="text-slate-600 mt-2">Update rejection information and manage restoration</p>
              </div>
              <div class="flex space-x-3">
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
            <div v-if="loading" class="bg-white rounded-2xl shadow-lg border border-blue-100 p-12 text-center">
              <div class="animate-spin w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full mx-auto mb-4"></div>
              <p class="text-slate-600">Loading opportunity details...</p>
            </div>

            <!-- Edit Form -->
            <div v-else class="bg-white rounded-2xl shadow-xl border border-blue-100/50 overflow-hidden">
              <!-- Header Banner -->
              <div class="bg-gradient-to-r from-blue-600 to-rose-600 text-white p-8">
                <div class="flex justify-between items-start">
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                      <PencilSquareIcon class="w-8 h-8 text-white" />
                    </div>
                    <div>
                      <h2 class="text-3xl font-bold">{{ form.potential_customer_name || 'Unknown Customer' }}</h2>
                      <p class="text-blue-100 mt-2 text-lg">Edit Rejected Opportunity Record</p>
                    </div>
                  </div>
                  <span class="px-6 py-3 bg-white/20 rounded-full text-lg font-semibold border border-white/30">
                    {{ formatSource(rejected.rejected_from) }}
                  </span>
                </div>
              </div>

              <!-- Form Content -->
              <form @submit.prevent="updateOpportunity" class="p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  <!-- Contact Information -->
                  <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-slate-800 border-b border-slate-200 pb-3 flex items-center">
                      <UserCircleIcon class="w-6 h-6 text-blue-600 mr-2" />
                      Contact Information
                    </h3>
                    
                    <div class="space-y-4">
                      <!-- Customer Name -->
                      <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center">
                          <UserCircleIcon class="w-4 h-4 mr-2 text-blue-500" />
                          Customer Name
                        </label>
                        <input
                          v-model="form.potential_customer_name"
                          type="text"
                          class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                          placeholder="Enter customer name"
                        />
                        <p v-if="form.errors.potential_customer_name" class="text-red-600 text-xs mt-1">
                          {{ form.errors.potential_customer_name }}
                        </p>
                      </div>

                      <!-- Email -->
                      <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center">
                          <EnvelopeIcon class="w-4 h-4 mr-2 text-rose-500" />
                          Email Address
                        </label>
                        <input
                          v-model="form.email"
                          type="email"
                          class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                          placeholder="Enter email address"
                        />
                        <p v-if="form.errors.email" class="text-red-600 text-xs mt-1">
                          {{ form.errors.email }}
                        </p>
                      </div>

                      <!-- Phone -->
                      <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center">
                          <PhoneIcon class="w-4 h-4 mr-2 text-green-500" />
                          Phone Number
                        </label>
                        <input
                          v-model="form.phone"
                          type="tel"
                          class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                          placeholder="Enter phone number"
                        />
                        <p v-if="form.errors.phone" class="text-red-600 text-xs mt-1">
                          {{ form.errors.phone }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Location Information -->
                  <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-slate-800 border-b border-slate-200 pb-3 flex items-center">
                      <MapPinIcon class="w-6 h-6 text-green-600 mr-2" />
                      Location Details
                    </h3>
                    
                    <div class="space-y-4">
                      <!-- City -->
                      <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center">
                          <MapPinIcon class="w-4 h-4 mr-2 text-blue-500" />
                          City
                        </label>
                        <select
                          v-model="form.city_id"
                          class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                        >
                          <option value="">Select City</option>
                          <option v-for="city in cities" :key="city.id" :value="city.id">
                            {{ city.name }}
                          </option>
                        </select>
                        <p v-if="form.errors.city_id" class="text-red-600 text-xs mt-1">
                          {{ form.errors.city_id }}
                        </p>
                      </div>

                      <!-- Subcity -->
                      <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center">
                          <BuildingOfficeIcon class="w-4 h-4 mr-2 text-rose-500" />
                          Subcity
                        </label>
                        <select
                          v-model="form.subcity_id"
                          class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                          :disabled="!form.city_id"
                        >
                          <option value="">Select Subcity</option>
                          <option v-for="subcity in filteredSubcities" :key="subcity.id" :value="subcity.id">
                            {{ subcity.name }}
                          </option>
                        </select>
                        <p v-if="form.errors.subcity_id" class="text-red-600 text-xs mt-1">
                          {{ form.errors.subcity_id }}
                        </p>
                      </div>

                      <!-- Location -->
                      <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center">
                          <MapPinIcon class="w-4 h-4 mr-2 text-purple-500" />
                          Location Address
                        </label>
                        <input
                          v-model="form.location"
                          type="text"
                          class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm"
                          placeholder="Enter full address"
                        />
                        <p v-if="form.errors.location" class="text-red-600 text-xs mt-1">
                          {{ form.errors.location }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Second Row Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
                  <!-- Rejection Information -->
                  <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-slate-800 border-b border-slate-200 pb-3 flex items-center">
                      <ExclamationTriangleIcon class="w-6 h-6 text-rose-600 mr-2" />
                      Rejection Information
                    </h3>
                    
                    <div class="space-y-4">
                      <!-- Rejection Reason -->
                      <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center">
                          <DocumentTextIcon class="w-4 h-4 mr-2 text-rose-500" />
                          Rejection Reason
                        </label>
                        <textarea
                          v-model="form.reason"
                          rows="4"
                          class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm resize-vertical"
                          placeholder="Enter rejection reason"
                        ></textarea>
                        <p v-if="form.errors.reason" class="text-red-600 text-xs mt-1">
                          {{ form.errors.reason }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- Additional Information -->
                  <div class="space-y-6">
                    <h3 class="text-xl font-semibold text-slate-800 border-b border-slate-200 pb-3 flex items-center">
                      <InformationCircleIcon class="w-6 h-6 text-slate-600 mr-2" />
                      Additional Information
                    </h3>
                    
                    <div class="space-y-4">
                      <!-- Remarks -->
                      <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2 flex items-center">
                          <ChatBubbleLeftRightIcon class="w-4 h-4 mr-2 text-blue-500" />
                          Additional Remarks
                        </label>
                        <textarea
                          v-model="form.remarks"
                          rows="4"
                          class="w-full px-4 py-3 bg-white border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500 transition-all duration-300 text-sm shadow-sm resize-vertical"
                          placeholder="Enter any additional remarks"
                        ></textarea>
                        <p v-if="form.errors.remarks" class="text-red-600 text-xs mt-1">
                          {{ form.errors.remarks }}
                        </p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-8 pt-8 border-t border-slate-200 flex justify-end space-x-4">
                  <button
                    type="button"
                    @click="goBack"
                    class="px-8 py-3 bg-slate-600 hover:bg-slate-700 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center space-x-2"
                  >
                    <ArrowLeftIcon class="w-5 h-5" />
                    <span>Cancel</span>
                  </button>
                  
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-8 py-3 bg-gradient-to-r from-blue-600 to-rose-600 hover:from-blue-700 hover:to-rose-700 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <CheckIcon class="w-5 h-5" />
                    <span>{{ form.processing ? 'Updating...' : 'Update Opportunity' }}</span>
                  </button>
                </div>
              </form>
            </div>

            <!-- Additional Action Buttons -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Restore Button -->
              <button 
                v-if="permissions.create"
                @click="restoreOpportunity"
                class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-4 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
              >
                <ArrowPathIcon class="w-5 h-5" />
                <span>Restore to {{ formatSource(rejected.rejected_from) }}</span>
              </button>
              
              <!-- Delete Button -->
              <button 
                v-if="permissions.delete"
                @click="deleteOpportunity"
                class="w-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white px-6 py-4 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center justify-center space-x-2"
              >
                <TrashIcon class="w-5 h-5" />
                <span>Delete Permanently</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router, useForm } from '@inertiajs/vue3'
import { ref, computed, onMounted } from 'vue'
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
  PencilSquareIcon,
  CheckIcon,
  BuildingOfficeIcon
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

const loading = ref(false)

// Initialize form with rejected opportunity data
const form = useForm({
  potential_customer_name: props.rejected?.potential_customer_name || '',
  email: props.rejected?.email || '',
  phone: props.rejected?.phone || '',
  city_id: props.rejected?.city_id || '',
  subcity_id: props.rejected?.subcity_id || '',
  location: props.rejected?.location || '',
  reason: props.rejected?.reason || '',
  remarks: props.rejected?.remarks || '',
})

// Computed properties
const filteredSubcities = computed(() => {
  if (!form.city_id) return []
  return props.subcities.filter(subcity => subcity.city_id == form.city_id)
})

// Helper methods
const formatSource = (source) => {
  const sources = {
    opportunity: 'Opportunity',
    potential_customer: 'Potential Customer',
    customer: 'Customer'
  }
  return sources[source] || source
}

// Action methods
const goBack = () => {
  router.get('/admin/rejected-opportunities')
}

const updateOpportunity = () => {
  form.put(`/admin/rejected-opportunities/${props.rejected.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Success handled by controller
    },
    onError: (errors) => {
      console.error('Failed to update opportunity:', errors)
    }
  })
}

const restoreOpportunity = () => {
  if (confirm(`Are you sure you want to restore this opportunity? It will be moved back to ${formatSource(props.rejected.rejected_from)}.`)) {
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

// Watch for city changes to update subcities
onMounted(() => {
  if (props.rejected) {
    loading.value = false
  }
})
</script>

<style scoped>
input:focus, select:focus, textarea:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>