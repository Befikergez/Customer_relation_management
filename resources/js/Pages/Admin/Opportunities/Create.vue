<template>
  <div class="min-h-screen bg-gradient-to-br from-white via-blue-50 to-teal-50 flex flex-col lg:flex-row">
    <Sidebar :tables="tables" />
    
    <div class="flex-1 flex flex-col min-h-screen relative">
      <!-- Animated Background Elements -->
      <div class="absolute top-0 right-0 w-72 h-72 bg-gradient-to-br from-blue-400 to-teal-500 rounded-full blur-3xl opacity-20 animate-pulse"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-teal-400 to-blue-500 rounded-full blur-3xl opacity-30 animate-bounce"></div>
      
      <!-- Fixed Header - Z-index lowered for mobile -->
      <div class="bg-white/90 backdrop-blur-sm border-b-2 border-blue-500/30 px-4 sm:px-6 lg:px-8 py-4 fixed top-0 left-0 right-0 lg:left-64 shadow-lg transition-all duration-300"
           :class="isMobile ? 'z-20' : 'z-40'">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3 sm:gap-4">
          <!-- Title Section -->
          <div class="flex items-center space-x-3 lg:space-x-4 flex-1 min-w-0">
            <div class="flex-shrink-0">
              <div class="w-10 h-10 lg:w-12 lg:h-12 bg-gradient-to-br from-blue-600 to-teal-600 rounded-xl lg:rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/50">
                <BriefcaseIcon class="w-5 h-5 lg:w-6 lg:h-6 text-white" />
              </div>
            </div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center space-x-2 flex-wrap">
                <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold bg-gradient-to-r from-blue-800 to-teal-700 bg-clip-text text-transparent truncate">
                  Create Opportunity
                </h1>
                <PlusCircleIcon class="w-5 h-5 lg:w-6 lg:h-6 text-emerald-500 flex-shrink-0" />
              </div>
              <p class="text-sm lg:text-base text-slate-700 mt-1 truncate">Add a new business opportunity</p>
            </div>
          </div>

          <!-- Button Section -->
          <div class="flex-shrink-0 w-full sm:w-auto">
            <button 
              @click="goBack"
              class="bg-gradient-to-r from-slate-600 to-slate-700 hover:from-slate-700 hover:to-slate-800 text-white px-4 py-2.5 lg:px-6 lg:py-3 rounded-lg lg:rounded-xl font-semibold transition-all duration-300 flex items-center justify-center space-x-2 shadow-lg shadow-slate-500/40 hover:shadow-slate-600/50 w-full sm:w-auto text-sm lg:text-base whitespace-nowrap"
            >
              <ArrowLeftIcon class="w-4 h-4 lg:w-5 lg:h-5 flex-shrink-0" />
              <span class="truncate">Back to Opportunities</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content with increased top padding -->
      <div class="flex-1 pt-40 md:pt-32 lg:pt-28 relative z-10">
        <div class="p-4 sm:p-6 lg:p-8">
          <div class="max-w-4xl mx-auto">
            <div class="bg-blue-50/30 backdrop-blur-sm rounded-xl lg:rounded-2xl border-2 border-blue-100/50 p-6 lg:p-8 shadow-xl shadow-slate-500/10">
              <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">
                  <!-- Customer Name -->
                  <div class="md:col-span-2">
                    <label class="block text-sm lg:text-base font-bold text-slate-900 mb-2 lg:mb-3">Customer Name *</label>
                    <input 
                      v-model="form.potential_customer_name"
                      type="text" 
                      class="w-full p-3 lg:p-4 border-2 border-blue-200 rounded-lg lg:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base transition-all duration-300 bg-white/50 placeholder-blue-600 font-medium shadow-sm"
                      placeholder="Enter customer name"
                      required
                    >
                    <div v-if="form.errors.potential_customer_name" class="text-rose-500 text-sm mt-2 flex items-center space-x-1 font-medium">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.potential_customer_name }}</span>
                    </div>
                  </div>

                  <!-- Email -->
                  <div>
                    <label class="block text-sm lg:text-base font-bold text-slate-900 mb-2 lg:mb-3">Email Address</label>
                    <input 
                      v-model="form.email"
                      type="email" 
                      class="w-full p-3 lg:p-4 border-2 border-blue-200 rounded-lg lg:rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm lg:text-base transition-all duration-300 bg-white/50 placeholder-blue-600 font-medium shadow-sm"
                      placeholder="customer@example.com"
                    >
                    <div v-if="form.errors.email" class="text-rose-500 text-sm mt-2 flex items-center space-x-1 font-medium">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.email }}</span>
                    </div>
                  </div>

                  <!-- Phone -->
                  <div>
                    <label class="block text-sm lg:text-base font-bold text-slate-900 mb-2 lg:mb-3">Phone Number</label>
                    <input 
                      v-model="form.phone"
                      type="tel" 
                      class="w-full p-3 lg:p-4 border-2 border-blue-200 rounded-lg lg:rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm lg:text-base transition-all duration-300 bg-white/50 placeholder-blue-600 font-medium shadow-sm"
                      placeholder="+1234567890"
                    >
                    <div v-if="form.errors.phone" class="text-rose-500 text-sm mt-2 flex items-center space-x-1 font-medium">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.phone }}</span>
                    </div>
                  </div>

                  <!-- City -->
                  <div>
                    <label class="block text-sm lg:text-base font-bold text-slate-900 mb-2 lg:mb-3">City</label>
                    <select 
                      v-model="form.city_id"
                      class="w-full p-3 lg:p-4 border-2 border-blue-200 rounded-lg lg:rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm lg:text-base transition-all duration-300 bg-white/50 font-medium shadow-sm"
                    >
                      <option value="">Select City</option>
                      <option v-for="city in cities" :key="city.id" :value="city.id">
                        {{ city.name }}
                      </option>
                    </select>
                    <div v-if="form.errors.city_id" class="text-rose-500 text-sm mt-2 flex items-center space-x-1 font-medium">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.city_id }}</span>
                    </div>
                  </div>

                  <!-- Subcity -->
                  <div>
                    <label class="block text-sm lg:text-base font-bold text-slate-900 mb-2 lg:mb-3">Subcity</label>
                    <select 
                      v-model="form.subcity_id"
                      class="w-full p-3 lg:p-4 border-2 border-blue-200 rounded-lg lg:rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm lg:text-base transition-all duration-300 bg-white/50 font-medium shadow-sm"
                    >
                      <option value="">Select Subcity</option>
                      <option v-for="subcity in subcities" :key="subcity.id" :value="subcity.id">
                        {{ subcity.name }}
                      </option>
                    </select>
                    <div v-if="form.errors.subcity_id" class="text-rose-500 text-sm mt-2 flex items-center space-x-1 font-medium">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.subcity_id }}</span>
                    </div>
                  </div>

                  <!-- Text Location -->
                  <div class="md:col-span-2">
                    <label class="block text-sm lg:text-base font-bold text-slate-900 mb-2 lg:mb-3">Text Location Description</label>
                    <input 
                      v-model="form.text_location"
                      type="text" 
                      class="w-full p-3 lg:p-4 border-2 border-blue-200 rounded-lg lg:rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent text-sm lg:text-base transition-all duration-300 bg-white/50 placeholder-blue-600 font-medium shadow-sm"
                      placeholder="Enter location description"
                    >
                    <div v-if="form.errors.text_location" class="text-rose-500 text-sm mt-2 flex items-center space-x-1 font-medium">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.text_location }}</span>
                    </div>
                  </div>

                  <!-- Map Location -->
                  <div class="md:col-span-2">
                    <label class="block text-sm lg:text-base font-bold text-slate-900 mb-2 lg:mb-3">Map Location (URL)</label>
                    <input 
                      v-model="form.map_location"
                      type="url" 
                      class="w-full p-3 lg:p-4 border-2 border-blue-200 rounded-lg lg:rounded-xl focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm lg:text-base transition-all duration-300 bg-white/50 placeholder-blue-600 font-medium shadow-sm"
                      placeholder="https://maps.google.com/..."
                      @input="debugMapLocation"
                    >
                    <div class="text-xs text-blue-600 mt-1">
                      Current value: "{{ form.map_location }}"
                    </div>
                    <div v-if="form.errors.map_location" class="text-rose-500 text-sm mt-2 flex items-center space-x-1 font-medium">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.map_location }}</span>
                    </div>
                  </div>

                  <!-- Remarks -->
                  <div class="md:col-span-2">
                    <label class="block text-sm lg:text-base font-bold text-slate-900 mb-2 lg:mb-3">Remarks</label>
                    <textarea 
                      v-model="form.remarks"
                      rows="4"
                      class="w-full p-3 lg:p-4 border-2 border-blue-200 rounded-lg lg:rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm lg:text-base transition-all duration-300 bg-white/50 placeholder-blue-600 font-medium shadow-sm resize-none"
                      placeholder="Additional notes or comments about this opportunity..."
                    ></textarea>
                    <div v-if="form.errors.remarks" class="text-rose-500 text-sm mt-2 flex items-center space-x-1 font-medium">
                      <ExclamationCircleIcon class="w-4 h-4" />
                      <span>{{ form.errors.remarks }}</span>
                    </div>
                  </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4 mt-6 lg:mt-8 pt-6 border-t-2 border-blue-100/60">
                  <button 
                    type="button"
                    @click="goBack"
                    class="px-4 py-2.5 lg:px-6 lg:py-3 text-slate-700 hover:text-slate-900 font-semibold text-sm lg:text-base transition-colors duration-300 order-2 sm:order-1 w-full sm:w-auto bg-blue-50 hover:bg-blue-100 rounded-xl border-2 border-blue-200"
                  >
                    Cancel
                  </button>
                  <button 
                    type="submit"
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-700 hover:to-teal-700 disabled:from-slate-400 disabled:to-slate-400 text-white px-4 py-2.5 lg:px-8 lg:py-3 rounded-lg font-semibold transition-all duration-300 shadow-lg shadow-blue-500/40 hover:shadow-blue-600/50 flex items-center justify-center space-x-2 text-sm lg:text-base order-1 sm:order-2 w-full sm:w-auto"
                  >
                    <PlusIcon class="w-4 h-4 lg:w-5 lg:h-5" />
                    <span>{{ form.processing ? 'Creating...' : 'Create Opportunity' }}</span>
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
  PlusIcon,
  BriefcaseIcon,
  PlusCircleIcon,
  ExclamationCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
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
  potential_customer_name: '',
  email: '',
  phone: '',
  text_location: '',
  map_location: '',
  remarks: '',
  city_id: '',
  subcity_id: ''
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

const debugMapLocation = () => {
  console.log('Map Location input:', form.map_location)
}

const submit = () => {
  console.log('Submitting form with map_location:', form.map_location)
  form.post('/admin/opportunities', {
    onSuccess: () => {
      router.get('/admin/opportunities')
    },
    onError: (errors) => {
      console.log('Form errors:', errors)
    }
  })
}

const goBack = () => {
  router.get('/admin/opportunities')
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

/* Smooth transitions */
* {
  transition-property: all;
  transition-duration: 300ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .fixed.top-0 {
    left: 0 !important;
  }
  
  /* Increased spacing for mobile */
  .pt-40 {
    padding-top: 10rem;
  }
}
</style>