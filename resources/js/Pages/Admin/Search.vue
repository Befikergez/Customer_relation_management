<template>
  <div class="min-h-screen bg-gradient-to-br from-white via-blue-50 to-teal-50 flex">
    <!-- Sidebar -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content -->
    <div class="flex-1 relative">
      <!-- Animated Background Elements -->
      <div class="absolute top-0 right-0 w-72 h-72 bg-gradient-to-br from-blue-400 to-teal-500 rounded-full blur-3xl opacity-20 animate-pulse"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-teal-400 to-blue-500 rounded-full blur-3xl opacity-30 animate-bounce"></div>
      
      <div class="relative">
        <!-- Fixed Header - Lower z-index than sidebar -->
        <div class="bg-white/90 backdrop-blur-sm border-b-2 border-blue-500/30 px-6 py-6 sticky top-0 z-20 shadow-lg">
          <div class="flex justify-between items-center">
            <!-- Mobile spacing for hamburger button -->
            <div class="md:hidden w-12 flex-shrink-0"></div>
            
            <div class="flex-1 flex justify-between items-center">
              <div class="flex items-center space-x-4">
                <div class="relative">
                  <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-teal-600 rounded-2xl flex items-center justify-center shadow-lg shadow-blue-500/50">
                    <Search class="w-7 h-7 text-white" />
                  </div>
                  <div class="absolute -top-1 -right-1 w-4 h-4 bg-teal-500 rounded-full border-2 border-white animate-ping"></div>
                </div>
                <div>
                  <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-800 to-teal-700 bg-clip-text text-transparent">
                    Search
                  </h1>
                  <p class="text-slate-700 mt-1 flex items-center space-x-2">
                    <span class="font-medium">Welcome back, {{ $page.props.auth.user.name }}</span>
                    <span class="text-xs bg-gradient-to-r from-teal-600 to-blue-600 text-white px-3 py-1 rounded-full capitalize font-bold shadow-md">
                      {{ userRole }}
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6 relative z-10">
          <!-- Search Section -->
          <div class="max-w-4xl mx-auto">
            <!-- Search Input -->
            <div class="relative mb-8">
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <Search class="h-6 w-6 text-blue-600" />
              </div>
              <input
                v-model="searchQuery"
                @input="performSearch"
                type="text"
                class="w-full pl-12 pr-24 py-5 bg-white/95 backdrop-blur-sm border-3 border-blue-500 rounded-2xl text-slate-900 placeholder-blue-600 font-medium focus:ring-4 focus:ring-blue-500/40 focus:border-blue-600 transition-all duration-300 text-lg shadow-2xl shadow-blue-500/30 hover:shadow-2xl hover:shadow-blue-600/40 hover:border-blue-500"
                placeholder="Search by name, subcity, city ..."
              />
              <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
                <div v-if="loading" class="flex items-center space-x-2 text-blue-600 font-medium">
                  <div class="animate-spin rounded-full h-4 w-4 border-2 border-blue-600 border-t-transparent"></div>
                  <span class="text-sm">Searching...</span>
                </div>
              </div>
            </div>

            <!-- Search Results -->
            <div v-if="searchResults.length > 0" class="space-y-4">
              <!-- Results Header -->
              <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                  <div class="w-3 h-3 bg-blue-600 rounded-full animate-pulse"></div>
                  <h3 class="text-xl font-bold text-slate-900">
                    {{ searchResults.length }} results for "{{ searchQuery }}"
                  </h3>
                </div>
                <button
                  @click="clearSearch"
                  class="px-4 py-2 text-slate-600 hover:text-slate-800 font-medium transition-colors duration-200 text-sm bg-blue-50 hover:bg-blue-100 rounded-xl border border-blue-200"
                >
                  Clear all
                </button>
              </div>

              <!-- Results Grid -->
              <div class="grid gap-4">
                <div
                  v-for="result in searchResults"
                  :key="`${result.type}-${result.id}`"
                  class="group bg-white/95 backdrop-blur-sm rounded-2xl shadow-xl shadow-slate-500/10 border-2 border-white/80 hover:shadow-2xl hover:shadow-blue-500/20 hover:border-blue-300/50 transition-all duration-500 overflow-hidden"
                >
                  <!-- Result Header -->
                  <div class="p-6 border-b-2 border-blue-100/60 bg-gradient-to-r from-blue-50 to-white">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-4">
                        <div 
                          class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg"
                          :class="{
                            'bg-gradient-to-br from-blue-600 to-blue-700 shadow-blue-500/50': result.type === 'opportunity',
                            'bg-gradient-to-br from-teal-600 to-teal-700 shadow-teal-500/50': result.type === 'customer',
                            'bg-gradient-to-br from-blue-500 to-teal-600 shadow-blue-500/50': result.type === 'potential_customer',
                            'bg-gradient-to-br from-blue-700 to-teal-800 shadow-blue-500/50': result.type === 'rejected_opportunity'
                          }"
                        >
                          <component 
                            :is="getTypeIcon(result.type)" 
                            class="w-6 h-6" 
                          />
                        </div>
                        <div>
                          <h4 class="text-lg font-bold text-slate-900 group-hover:text-blue-700 transition-colors duration-300">
                            {{ result.data.name || result.data.company_name || 'Unnamed' }}
                          </h4>
                          <p class="text-slate-600 text-sm capitalize font-medium">{{ getTypeLabel(result.type) }}</p>
                        </div>
                      </div>
                      <div class="flex items-center space-x-3">
                        <div class="text-right">
                          <div class="text-xs text-slate-600 font-medium">Match score</div>
                          <div class="text-sm font-bold text-slate-900">{{ result.score }}%</div>
                        </div>
                        <button
                          @click="viewDetails(result)"
                          class="px-4 py-2 bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-700 hover:to-teal-700 text-white rounded-xl font-bold transition-all duration-300 flex items-center space-x-2 group/btn shadow-lg shadow-blue-500/40 hover:shadow-blue-600/50"
                        >
                          <Eye class="w-4 h-4 group-hover/btn:scale-110 transition-transform duration-300" />
                          <span>View</span>
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Result Details -->
                  <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                      <!-- Contact Info -->
                      <div v-if="result.data.email || result.data.phone" class="space-y-3">
                        <h5 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Contact</h5>
                        <div class="space-y-2">
                          <div v-if="result.data.email" class="flex items-center space-x-3 text-slate-700">
                            <Mail class="w-4 h-4 text-blue-600" />
                            <span class="text-sm font-medium">{{ result.data.email }}</span>
                          </div>
                          <div v-if="result.data.phone" class="flex items-center space-x-3 text-slate-700">
                            <Phone class="w-4 h-4 text-blue-600" />
                            <span class="text-sm font-medium">{{ result.data.phone }}</span>
                          </div>
                        </div>
                      </div>

                      <!-- Location Info -->
                      <div v-if="result.data.city || result.data.subcity" class="space-y-3">
                        <h5 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Location</h5>
                        <div class="space-y-2">
                          <div v-if="result.data.city" class="flex items-center space-x-3 text-slate-700">
                            <MapPin class="w-4 h-4 text-teal-600" />
                            <span class="text-sm font-medium">{{ result.data.city.name || result.data.city }}</span>
                          </div>
                          <div v-if="result.data.subcity" class="flex items-center space-x-3 text-slate-700">
                            <Navigation class="w-4 h-4 text-teal-600" />
                            <span class="text-sm font-medium">{{ result.data.subcity.name || result.data.subcity }}</span>
                          </div>
                        </div>
                      </div>

                      <!-- Status Info -->
                      <div class="space-y-3">
                        <h5 class="text-sm font-bold text-slate-900 uppercase tracking-wide">Status</h5>
                        <div class="space-y-2">
                          <span
                            v-if="result.data.status"
                            class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold border-2"
                            :class="getStatusClasses(result.data.status)"
                          >
                            {{ result.data.status }}
                          </span>
                          <div v-if="result.data.created_at" class="text-xs text-slate-600 font-medium">
                            Created {{ formatDate(result.data.created_at) }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Matching Fields -->
                    <div v-if="result.matches && result.matches.length > 0" class="mt-6 pt-6 border-t-2 border-blue-100/60">
                      <h5 class="text-sm font-bold text-slate-900 mb-3">Matching Fields</h5>
                      <div class="flex flex-wrap gap-2">
                        <span
                          v-for="match in result.matches"
                          :key="match.field"
                          class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-blue-100 to-teal-100 text-blue-800 rounded-lg text-xs font-bold border-2 border-blue-200/60 shadow-sm"
                        >
                          {{ match.field }}: "{{ match.value }}"
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div
              v-else-if="searchQuery && !loading"
              class="text-center py-16"
            >
              <div class="max-w-md mx-auto">
                <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg border border-blue-200">
                  <Search class="w-10 h-10 text-blue-500" />
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mb-3">No results found</h3>
                <p class="text-slate-700 mb-6 font-medium">
                  We couldn't find any matches for "<span class="font-bold">{{ searchQuery }}</span>". Try different keywords.
                </p>
                <button
                  @click="clearSearch"
                  class="px-6 py-3 bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-700 hover:to-teal-700 text-white rounded-xl font-bold transition-all duration-300 shadow-lg hover:shadow-xl"
                >
                  Clear Search
                </button>
              </div>
            </div>

            <!-- Initial State -->
            <div
              v-else-if="!searchQuery"
              class="text-center py-16"
            >
              <div class="max-w-2xl mx-auto">
                <div class="grid grid-cols-3 gap-8 mb-12">
                  <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                      <Briefcase class="w-6 h-6 text-white" />
                    </div>
                    <h4 class="font-bold text-slate-900 mb-1">Opportunities</h4>
                    <p class="text-sm text-slate-700 font-medium">Active deals & leads</p>
                  </div>
                  <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                      <UserCircle2 class="w-6 h-6 text-white" />
                    </div>
                    <h4 class="font-bold text-slate-900 mb-1">Customers</h4>
                    <p class="text-sm text-slate-700 font-medium">Client database</p>
                  </div>
                  <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                      <Users class="w-6 h-6 text-white" />
                    </div>
                    <h4 class="font-bold text-slate-900 mb-1">Potential Customers</h4>
                    <p class="text-sm text-slate-700 font-medium">Future clients</p>
                  </div>
                </div>
                <p class="text-slate-700 text-lg font-medium">
                  Enter a search term above to find opportunities, customers, and more across your CRM.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Search, Eye, Mail, Phone, MapPin, Navigation, Briefcase, UserCircle2, Users, FileText } from 'lucide-vue-next'
import Sidebar from './Sidebar.vue'

// Props
defineProps({
  tables: {
    type: Array,
    default: () => []
  }
})

const page = usePage()

// Get user role from page props
const userRole = computed(() => {
  return page.props.auth?.user?.roles?.length ? page.props.auth.user.roles[0].name : 'user'
})

// Reactive data
const searchQuery = ref('')
const searchResults = ref([])
const loading = ref(false)
const activeFilters = ref(['opportunities', 'customers', 'potential_customers', 'rejected_opportunities'])

// Debounced search function
let searchTimeout = null
const performSearch = () => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }

  if (!searchQuery.value.trim()) {
    searchResults.value = []
    return
  }

  loading.value = true
  searchTimeout = setTimeout(() => {
    router.get('/admin/search', {
      q: searchQuery.value,
      filters: activeFilters.value
    }, {
      preserveState: true,
      onSuccess: (page) => {
        searchResults.value = page.props.searchResults || []
        loading.value = false
      },
      onError: () => {
        loading.value = false
      }
    })
  }, 500)
}

// Clear search
const clearSearch = () => {
  searchQuery.value = ''
  searchResults.value = []
}

// View details
const viewDetails = (result) => {
  const routes = {
    opportunity: '/admin/opportunities',
    customer: '/admin/customers',
    potential_customer: '/admin/potential-customers',
    rejected_opportunity: '/admin/rejected-opportunities'
  }
  
  if (routes[result.type]) {
    router.get(`${routes[result.type]}/${result.id}`)
  }
}

// Helper functions
const getTypeLabel = (type) => {
  const labels = {
    opportunity: 'Opportunity',
    customer: 'Customer',
    potential_customer: 'Potential Customer',
    rejected_opportunity: 'Rejected Opportunity'
  }
  return labels[type] || type
}

const getTypeIcon = (type) => {
  const icons = {
    opportunity: Briefcase,
    customer: UserCircle2,
    potential_customer: Users,
    rejected_opportunity: FileText
  }
  return icons[type] || FileText
}

const getStatusClasses = (status) => {
  const statusClasses = {
    'active': 'bg-gradient-to-r from-teal-500 to-teal-600 text-white border-teal-600 shadow-lg shadow-teal-500/30',
    'won': 'bg-gradient-to-r from-blue-500 to-blue-600 text-white border-blue-600 shadow-lg shadow-blue-500/30',
    'lost': 'bg-gradient-to-r from-blue-700 to-teal-800 text-white border-blue-800 shadow-lg shadow-blue-700/30',
    'pending': 'bg-gradient-to-r from-blue-400 to-teal-500 text-white border-teal-500 shadow-lg shadow-blue-400/30',
    'new': 'bg-gradient-to-r from-blue-300 to-teal-400 text-white border-teal-400 shadow-lg shadow-blue-300/30',
    'default': 'bg-gradient-to-r from-blue-500 to-teal-600 text-white border-teal-600 shadow-lg shadow-blue-500/30'
  }
  
  const statusKey = status.toLowerCase()
  return statusClasses[statusKey] || statusClasses.default
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
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

/* Smooth transitions */
* {
  transition-property: all;
  transition-duration: 300ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>