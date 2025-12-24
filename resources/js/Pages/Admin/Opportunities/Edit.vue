<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1 flex flex-col">
      <!-- Fixed Header - Z-index lowered for mobile -->
      <div class="bg-white/90 backdrop-blur-md border-b border-slate-200/60 px-6 py-4 flex-shrink-0 transition-all duration-300"
           :class="isMobile ? 'z-20' : 'z-30'">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-teal-500 rounded-lg flex items-center justify-center shadow-md">
              <BriefcaseIcon class="w-5 h-5 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent flex items-center space-x-2">
                <span>Edit Opportunity</span>
                <PencilSquareIcon class="w-6 h-6 text-amber-500" />
              </h1>
              <p class="text-slate-600 text-sm mt-1">Update existing business opportunity</p>
            </div>
          </div>
          <div class="md:text-right">
            <button 
              @click="goBack"
              class="bg-slate-500 hover:bg-slate-600 text-white px-5 py-2.5 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-md hover:shadow-lg w-full md:w-auto justify-center"
            >
              <ArrowLeftIcon class="w-4 h-4" />
              <span>Back to Opportunities</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Scrollable Content -->
      <div class="flex-1 overflow-auto bg-gradient-to-br from-blue-50 via-white to-teal-50">
        <div class="p-6">
          <div class="max-w-4xl mx-auto">
            <div class="bg-blue-50/30 rounded-xl border border-blue-100/50 p-8 shadow-sm">
              <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- Customer Name -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Customer Name *</label>
                    <input 
                      v-model="form.potential_customer_name"
                      type="text" 
                      class="w-full p-4 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200 bg-white/50"
                      placeholder="Enter customer name"
                      required
                    >
                    <div v-if="form.errors.potential_customer_name" class="text-rose-500 text-sm mt-2 flex items-center space-x-1">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.potential_customer_name }}</span>
                    </div>
                  </div>

                  <!-- Email -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Email Address</label>
                    <input 
                      v-model="form.email"
                      type="email" 
                      class="w-full p-4 border border-blue-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm transition-all duration-200 bg-white/50"
                      placeholder="customer@example.com"
                    >
                    <div v-if="form.errors.email" class="text-rose-500 text-sm mt-2 flex items-center space-x-1">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.email }}</span>
                    </div>
                  </div>

                  <!-- Phone -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Phone Number</label>
                    <input 
                      v-model="form.phone"
                      type="tel" 
                      class="w-full p-4 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm transition-all duration-200 bg-white/50"
                      placeholder="+1234567890"
                    >
                    <div v-if="form.errors.phone" class="text-rose-500 text-sm mt-2 flex items-center space-x-1">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.phone }}</span>
                    </div>
                  </div>

                  <!-- City -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">City</label>
                    <select 
                      v-model="form.city_id"
                      class="w-full p-4 border border-blue-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm transition-all duration-200 bg-white/50"
                    >
                      <option value="">Select City</option>
                      <option v-for="city in cities" :key="city.id" :value="city.id">
                        {{ city.name }}
                      </option>
                    </select>
                    <div v-if="form.errors.city_id" class="text-rose-500 text-sm mt-2 flex items-center space-x-1">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.city_id }}</span>
                    </div>
                  </div>

                  <!-- Subcity -->
                  <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Subcity</label>
                    <select 
                      v-model="form.subcity_id"
                      class="w-full p-4 border border-blue-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition-all duration-200 bg-white/50"
                    >
                      <option value="">Select Subcity</option>
                      <option v-for="subcity in subcities" :key="subcity.id" :value="subcity.id">
                        {{ subcity.name }}
                      </option>
                    </select>
                    <div v-if="form.errors.subcity_id" class="text-rose-500 text-sm mt-2 flex items-center space-x-1">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.subcity_id }}</span>
                    </div>
                  </div>

                  <!-- Text Location -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Text Location Description</label>
                    <input 
                      v-model="form.text_location"
                      type="text" 
                      class="w-full p-4 border border-blue-200 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm transition-all duration-200 bg-white/50"
                      placeholder="Enter location description"
                    >
                    <div v-if="form.errors.text_location" class="text-rose-500 text-sm mt-2 flex items-center space-x-1">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.text_location }}</span>
                    </div>
                  </div>

                  <!-- Map Location -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Map Location (URL)</label>
                    <input 
                      v-model="form.map_location"
                      type="url" 
                      class="w-full p-4 border border-blue-200 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm transition-all duration-200 bg-white/50"
                      placeholder="https://maps.google.com/..."
                    >
                    <div v-if="form.errors.map_location" class="text-rose-500 text-sm mt-2 flex items-center space-x-1">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.map_location }}</span>
                    </div>
                  </div>

                  <!-- Remarks -->
                  <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-3">Remarks</label>
                    <textarea 
                      v-model="form.remarks"
                      rows="4"
                      class="w-full p-4 border border-blue-200 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm transition-all duration-200 bg-white/50 resize-none"
                      placeholder="Additional notes or comments about this opportunity..."
                    ></textarea>
                    <div v-if="form.errors.remarks" class="text-rose-500 text-sm mt-2 flex items-center space-x-1">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.remarks }}</span>
                    </div>
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col md:flex-row justify-end space-y-3 md:space-y-0 md:space-x-4 mt-8 pt-6 border-t border-blue-100/50">
                  <button 
                    type="button"
                    @click="goBack"
                    class="px-6 py-3 text-slate-600 hover:text-slate-800 font-semibold text-sm transition-colors order-2 md:order-1 w-full md:w-auto bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200"
                  >
                    Cancel
                  </button>
                  <button 
                    type="submit"
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-blue-500 to-teal-500 hover:from-blue-600 hover:to-teal-600 disabled:from-slate-400 disabled:to-slate-400 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-200 shadow-md hover:shadow-lg flex items-center space-x-2 order-1 md:order-2 w-full md:w-auto justify-center"
                  >
                    <PencilIcon class="w-5 h-5" />
                    <span>{{ form.processing ? 'Updating...' : 'Update Opportunity' }}</span>
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
import { ref, onMounted, onUnmounted } from 'vue'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { 
  ArrowLeftIcon, 
  PencilIcon,
  BriefcaseIcon,
  PencilSquareIcon,
  ExclamationCircleIcon
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

const form = useForm({
  potential_customer_name: props.opportunity.potential_customer_name || '',
  email: props.opportunity.email || '',
  phone: props.opportunity.phone || '',
  text_location: props.opportunity.text_location || '',
  map_location: props.opportunity.map_location || '',
  remarks: props.opportunity.remarks || '',
  city_id: props.opportunity.city_id || '',
  subcity_id: props.opportunity.subcity_id || ''
})

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

const submit = () => {
  form.put(`/admin/opportunities/${props.opportunity.id}`, {
    onSuccess: () => {
      router.get('/admin/opportunities')
    }
  })
}

const goBack = () => {
  router.get('/admin/opportunities')
}
</script>

<style scoped>
@media (max-width: 768px) {
  .fixed.py-4 {
    left: 0 !important;
  }
}
</style>