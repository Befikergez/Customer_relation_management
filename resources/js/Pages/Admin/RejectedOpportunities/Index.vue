<template>
  <div class="min-h-screen bg-slate-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1 flex flex-col min-w-0">
      <!-- Fixed Transparent Header -->
      <div class="fixed top-0 right-0 left-0 lg:left-64 bg-white/80 backdrop-blur-md border-b border-teal-100/50 px-6 py-4 z-30 transition-all duration-300">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">Rejected Opportunities</h1>
            <p class="text-slate-600 text-sm mt-1">
              View all rejected items from opportunities, potential customers, and customers
            </p>
          </div>
        </div>
      </div>

      <!-- Main Content Area with padding for fixed header -->
      <div class="flex-1 overflow-auto pt-20">
        <div class="min-h-full bg-gradient-to-br from-blue-50 via-white to-teal-50">
          <div class="p-6">
            <div class="max-w-full mx-auto">
              <!-- Success/Error Messages -->
              <div v-if="$page.props.flash && $page.props.flash.success" class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl mb-6 shadow-sm">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                  </div>
                  {{ $page.props.flash.success }}
                </div>
              </div>
              <div v-if="$page.props.flash && $page.props.flash.error" class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-6 shadow-sm">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                  </div>
                  {{ $page.props.flash.error }}
                </div>
              </div>

              <!-- Main Content Card with Compact Integrated Filters -->
              <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
                <!-- Table Header with Compact Integrated Filters -->
                <div class="border-b border-slate-100">
                  <!-- Compact Filters Section -->
                  <div class="px-4 py-3 bg-gradient-to-r from-blue-50 to-teal-50 border-b border-slate-200">
                    <div class="flex flex-wrap items-center gap-3">
                      <!-- City Filter -->
                      <div class="flex items-center space-x-2">
                        <label class="text-xs font-medium text-slate-700 whitespace-nowrap">City:</label>
                        <select
                          v-model="filters.city_id"
                          @change="onCityChange"
                          class="px-2 py-1 bg-white border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 w-32"
                        >
                          <option value="">All Cities</option>
                          <option v-for="city in cities" :key="city.id" :value="city.id">
                            {{ city.name }}
                          </option>
                        </select>
                      </div>

                      <!-- Subcity Filter -->
                      <div class="flex items-center space-x-2">
                        <label class="text-xs font-medium text-slate-700 whitespace-nowrap">Subcity:</label>
                        <select
                          v-model="filters.subcity_id"
                          @change="applyFilters"
                          class="px-2 py-1 bg-white border border-slate-300 rounded text-xs focus:ring-1 focus:ring-teal-500 focus:border-teal-500 transition-all duration-200 w-32 disabled:bg-slate-100 disabled:cursor-not-allowed"
                          :disabled="!filters.city_id"
                        >
                          <option value="">All Subcities</option>
                          <option v-for="subcity in filteredSubcities" :key="subcity.id" :value="subcity.id">
                            {{ subcity.name }}
                          </option>
                        </select>
                      </div>

                      <!-- Source Filter -->
                      <div class="flex items-center space-x-2">
                        <label class="text-xs font-medium text-slate-700 whitespace-nowrap">Source:</label>
                        <select
                          v-model="filters.source"
                          @change="applyFilters"
                          class="px-2 py-1 bg-white border border-slate-300 rounded text-xs focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 w-36"
                        >
                          <option value="">All Sources</option>
                          <option value="opportunity">Opportunity</option>
                          <option value="potential_customer">Potential Customer</option>
                          <option value="customer">Customer</option>
                        </select>
                      </div>

                      <!-- Clear Filters Button -->
                      <button
                        @click="clearFilters"
                        class="px-2 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded text-xs font-medium transition-all duration-200 flex items-center space-x-1 whitespace-nowrap"
                      >
                        <XMarkIcon class="w-3 h-3" />
                        <span>Clear</span>
                      </button>

                      <!-- Active Filters Count -->
                      <div v-if="hasActiveFilters" class="flex items-center space-x-2">
                        <span class="text-xs text-slate-600 font-medium">{{ filteredResultsText }}</span>
                      </div>
                    </div>

                    <!-- Active Filters Badges - Compact -->
                    <div v-if="hasActiveFilters" class="mt-2 flex flex-wrap gap-1">
                      <span 
                        v-if="filters.city_id" 
                        class="inline-flex items-center px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-medium border border-blue-200"
                      >
                        City: {{ getCityName(filters.city_id) }}
                        <button @click="removeFilter('city_id')" class="ml-1 hover:text-blue-900 transition-colors">
                          <XMarkIcon class="w-3 h-3" />
                        </button>
                      </span>
                      <span 
                        v-if="filters.subcity_id" 
                        class="inline-flex items-center px-2 py-0.5 bg-teal-50 text-teal-700 rounded text-xs font-medium border border-teal-200"
                      >
                        Subcity: {{ getSubcityName(filters.subcity_id) }}
                        <button @click="removeFilter('subcity_id')" class="ml-1 hover:text-teal-900 transition-colors">
                          <XMarkIcon class="w-3 h-3" />
                        </button>
                      </span>
                      <span 
                        v-if="filters.source" 
                        class="inline-flex items-center px-2 py-0.5 bg-blue-50 text-blue-700 rounded text-xs font-medium border border-blue-200"
                      >
                        Source: {{ formatSource(filters.source) }}
                        <button @click="removeFilter('source')" class="ml-1 hover:text-blue-900 transition-colors">
                          <XMarkIcon class="w-3 h-3" />
                        </button>
                      </span>
                    </div>
                  </div>

                  <!-- Table Title and Results -->
                  <div class="px-6 py-3 bg-white flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-slate-800">Rejected Items</h3>
                    <div v-if="rejectedOpportunities.data && rejectedOpportunities.data.length > 0" class="text-sm text-slate-600">
                      {{ filteredResultsText }}
                    </div>
                  </div>
                </div>
                
                <!-- Empty State -->
                <div v-if="!rejectedOpportunities.data || rejectedOpportunities.data.length === 0" class="p-12 text-center">
                  <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-teal-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <ArchiveBoxIcon class="w-8 h-8 text-blue-600" />
                  </div>
                  <h3 class="text-lg font-semibold text-slate-900 mb-2">No rejected opportunities found</h3>
                  <p class="text-slate-600 text-sm mb-6 max-w-md mx-auto">
                    {{ hasActiveFilters ? 'Try adjusting your filters to see more results.' : 'Rejected items from opportunities, potential customers, and customers will appear here.' }}
                  </p>
                  <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-700 hover:to-teal-700 text-white px-6 py-2 rounded-lg font-medium transition-all duration-200"
                  >
                    Clear Filters
                  </button>
                </div>

                <!-- Mobile Cards View -->
                <div v-else-if="isMobile" class="divide-y divide-slate-200">
                  <div 
                    v-for="item in rejectedOpportunities.data" 
                    :key="item.id" 
                    class="p-4 hover:bg-blue-50/50 transition-all duration-200"
                  >
                    <!-- Customer Header -->
                    <div class="flex items-center justify-between mb-3">
                      <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center">
                          <UserCircleIcon class="w-5 h-5 text-white" />
                        </div>
                        <div class="min-w-0 flex-1">
                          <h3 class="font-semibold text-slate-900 text-sm truncate">{{ item.potential_customer_name || 'Unknown Customer' }}</h3>
                          <p class="text-slate-500 text-xs truncate">{{ item.remarks || 'No remarks' }}</p>
                        </div>
                      </div>
                      <span :class="getSourceBadgeClass(item.rejected_from)" class="px-2 py-1 rounded-full text-xs font-semibold">
                        {{ formatSource(item.rejected_from) }}
                      </span>
                    </div>

                    <!-- Contact & Location Info -->
                    <div class="grid grid-cols-2 gap-3 mb-3">
                      <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                          <EnvelopeIcon class="w-3 h-3 text-slate-400 flex-shrink-0" />
                          <span class="text-xs text-slate-700 truncate">{{ item.email || 'No email' }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                          <PhoneIcon class="w-3 h-3 text-slate-400 flex-shrink-0" />
                          <span class="text-xs text-slate-700 truncate">{{ item.phone || 'No phone' }}</span>
                        </div>
                      </div>
                      <div class="space-y-1">
                        <div class="flex items-center space-x-2">
                          <MapPinIcon class="w-3 h-3 text-slate-400 flex-shrink-0" />
                          <span class="text-xs text-slate-700 truncate">{{ item.city?.name || 'No city' }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                          <BuildingOfficeIcon class="w-3 h-3 text-slate-400 flex-shrink-0" />
                          <span class="text-xs text-slate-700 truncate">{{ item.subcity?.name || 'No subcity' }}</span>
                        </div>
                      </div>
                    </div>

                    <!-- Rejection Details -->
                    <div class="mb-3">
                      <div class="bg-slate-50 rounded-lg p-3">
                        <h4 class="font-medium text-slate-800 text-sm mb-1">Rejection Reason</h4>
                        <p class="text-slate-600 text-xs leading-relaxed">{{ item.reason || 'No reason provided' }}</p>
                        <p class="text-slate-500 text-xs mt-1">By {{ item.created_by?.name || 'System' }}</p>
                      </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2">
                      <button 
                        @click="viewItem(item.id)"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white p-2 rounded text-xs font-medium transition-all duration-200 flex items-center justify-center space-x-1"
                      >
                        <EyeIcon class="w-3 h-3" />
                        <span>View</span>
                      </button>

                      <button 
                        v-if="permissions.edit"
                        @click="editItem(item.id)"
                        class="flex-1 bg-teal-600 hover:bg-teal-700 text-white p-2 rounded text-xs font-medium transition-all duration-200 flex items-center justify-center space-x-1"
                      >
                        <PencilSquareIcon class="w-3 h-3" />
                        <span>Edit</span>
                      </button>

                      <button 
                        v-if="permissions.create"
                        @click="revertItem(item.id, item.rejected_from)"
                        class="flex-1 bg-green-600 hover:bg-green-700 text-white p-2 rounded text-xs font-medium transition-all duration-200 flex items-center justify-center space-x-1"
                      >
                        <ArrowPathIcon class="w-3 h-3" />
                        <span>Revert</span>
                      </button>
                      
                      <button 
                        v-if="permissions.delete"
                        @click="deleteItem(item.id)"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white p-2 rounded text-xs font-medium transition-all duration-200 flex items-center justify-center space-x-1"
                      >
                        <TrashIcon class="w-3 h-3" />
                        <span>Delete</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- Desktop Table View - Compact & Narrow -->
                <div v-else class="overflow-x-auto">
                  <table class="w-full min-w-max">
                    <thead class="bg-slate-50">
                      <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider w-40">Customer</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider w-32">Contact</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider w-32">Location</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider w-24">Source</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider w-40">Rejection Reason</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-slate-700 uppercase tracking-wider w-28">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                      <tr 
                        v-for="item in rejectedOpportunities.data" 
                        :key="item.id" 
                        class="hover:bg-blue-50/30 transition-all duration-200"
                      >
                        <!-- Customer Details - Compact -->
                        <td class="px-3 py-2">
                          <div class="flex items-center">
                            <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-teal-500 rounded flex items-center justify-center mr-2 flex-shrink-0">
                              <UserCircleIcon class="w-3 h-3 text-white" />
                            </div>
                            <div class="min-w-0">
                              <div class="font-semibold text-slate-900 text-xs truncate max-w-[120px]">{{ item.potential_customer_name || 'Unknown' }}</div>
                              <div class="text-slate-500 text-xs truncate max-w-[120px]">{{ item.remarks || 'No remarks' }}</div>
                            </div>
                          </div>
                        </td>
                        
                        <!-- Contact Info - Compact -->
                        <td class="px-3 py-2">
                          <div class="space-y-0.5">
                            <div class="text-xs text-slate-900 truncate max-w-[110px]" :title="item.email || 'No email'">
                              {{ item.email || 'No email' }}
                            </div>
                            <div class="text-slate-500 text-xs truncate max-w-[110px]" :title="item.phone || 'No phone'">
                              {{ item.phone || 'No phone' }}
                            </div>
                          </div>
                        </td>
                        
                        <!-- Location - Compact -->
                        <td class="px-3 py-2">
                          <div class="space-y-0.5">
                            <div v-if="item.city" class="font-medium text-slate-900 text-xs truncate max-w-[110px]">
                              {{ item.city.name }}
                            </div>
                            <div v-if="item.subcity" class="text-slate-500 text-xs truncate max-w-[110px]">
                              {{ item.subcity.name }}
                            </div>
                          </div>
                        </td>
                        
                        <!-- Source Badge - Compact -->
                        <td class="px-3 py-2">
                          <span class="px-2 py-1 rounded-full text-xs font-semibold border whitespace-nowrap" :class="getSourceBadgeClass(item.rejected_from)">
                            {{ formatSource(item.rejected_from) }}
                          </span>
                        </td>
                        
                        <!-- Rejection Details - Compact -->
                        <td class="px-3 py-2">
                          <div>
                            <div class="font-medium text-slate-900 text-xs mb-0.5 line-clamp-2 leading-tight" :title="item.reason || 'No reason provided'">
                              {{ item.reason || 'No reason provided' }}
                            </div>
                            <div class="text-slate-500 text-xs">By {{ item.created_by?.name || 'System' }}</div>
                          </div>
                        </td>
                        
                        <!-- Actions - Compact -->
                        <td class="px-3 py-2">
                          <div class="flex flex-wrap gap-1">
                            <button 
                              @click="viewItem(item.id)"
                              class="bg-blue-600 hover:bg-blue-700 text-white p-1 rounded text-xs transition-all duration-200 flex items-center justify-center"
                              title="View Details"
                            >
                              <EyeIcon class="w-3 h-3" />
                            </button>

                            <button 
                              v-if="permissions.edit"
                              @click="editItem(item.id)"
                              class="bg-teal-600 hover:bg-teal-700 text-white p-1 rounded text-xs transition-all duration-200 flex items-center justify-center"
                              title="Edit Item"
                            >
                              <PencilSquareIcon class="w-3 h-3" />
                            </button>

                            <button 
                              v-if="permissions.create"
                              @click="revertItem(item.id, item.rejected_from)"
                              class="bg-green-600 hover:bg-green-700 text-white p-1 rounded text-xs transition-all duration-200 flex items-center justify-center"
                              title="Revert Item"
                            >
                              <ArrowPathIcon class="w-3 h-3" />
                            </button>
                            
                            <button 
                              v-if="permissions.delete"
                              @click="deleteItem(item.id)"
                              class="bg-red-600 hover:bg-red-700 text-white p-1 rounded text-xs transition-all duration-200 flex items-center justify-center"
                              title="Delete Permanently"
                            >
                              <TrashIcon class="w-3 h-3" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <div v-if="rejectedOpportunities.links && rejectedOpportunities.links.length > 3" class="px-6 py-4 bg-white border-t border-slate-100 flex flex-col sm:flex-row justify-between items-center gap-2">
                  <div class="text-sm text-slate-700">
                    Page {{ rejectedOpportunities.meta?.current_page || 1 }} of {{ rejectedOpportunities.meta?.last_page || 1 }}
                  </div>
                  <div class="flex flex-wrap gap-1 justify-center">
                    <button 
                      v-for="link in rejectedOpportunities.links"
                      :key="link.label"
                      :disabled="!link.url"
                      @click="visitPage(link.url)"
                      class="px-3 py-1.5 text-xs border rounded-lg transition-all duration-200 min-w-[2rem]"
                      :class="{
                        'bg-gradient-to-r from-blue-600 to-teal-600 text-white border-transparent': link.active,
                        'text-slate-600 border-slate-300 hover:bg-slate-50': !link.active && link.url,
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
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  EyeIcon,
  TrashIcon,
  ArchiveBoxIcon,
  ArrowPathIcon,
  XMarkIcon,
  UserCircleIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon,
  BuildingOfficeIcon,
  TagIcon,
  PencilSquareIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  rejectedOpportunities: {
    type: Object,
    default: () => ({ data: [] })
  },
  permissions: {
    type: Object,
    default: () => ({})
  },
  tables: {
    type: Array,
    default: () => []
  },
  flash: {
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
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

// Reactive filters
const filters = ref({
  city_id: props.filters.city_id || '',
  subcity_id: props.filters.subcity_id || '',
  source: props.filters.source || ''
})

// Responsive detection
const windowWidth = ref(window.innerWidth)
const isMobile = computed(() => windowWidth.value < 768)

// Computed properties
const filteredSubcities = computed(() => {
  if (!filters.value.city_id) return []
  return props.subcities.filter(subcity => subcity.city_id == filters.value.city_id)
})

const hasActiveFilters = computed(() => {
  return filters.value.city_id || filters.value.subcity_id || filters.value.source
})

const filteredResultsText = computed(() => {
  const total = props.rejectedOpportunities.meta?.total || 0
  if (hasActiveFilters.value) {
    return `${total} filtered result${total !== 1 ? 's' : ''}`
  }
  return `${total} item${total !== 1 ? 's' : ''}`
})

// Window resize handler
const updateWindowWidth = () => {
  windowWidth.value = window.innerWidth
}

onMounted(() => {
  window.addEventListener('resize', updateWindowWidth)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateWindowWidth)
})

// Methods
const applyFilters = () => {
  router.get('/admin/rejected-opportunities', filters.value, {
    preserveState: true,
    replace: true
  })
}

const onCityChange = () => {
  // Reset subcity when city changes
  filters.value.subcity_id = ''
  applyFilters()
}

const clearFilters = () => {
  filters.value = {
    city_id: '',
    subcity_id: '',
    source: ''
  }
  applyFilters()
}

// Helper methods
const getSourceBadgeClass = (source) => {
  const classes = {
    opportunity: 'bg-blue-100 text-blue-800 border-blue-200',
    potential_customer: 'bg-teal-100 text-teal-800 border-teal-200',
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

const getCityName = (cityId) => {
  const city = props.cities.find(c => c.id == cityId)
  return city ? city.name : 'Unknown'
}

const getSubcityName = (subcityId) => {
  const subcity = props.subcities.find(s => s.id == subcityId)
  return subcity ? subcity.name : 'Unknown'
}

const removeFilter = (filterKey) => {
  filters.value[filterKey] = ''
  applyFilters()
}

// Navigation methods
const viewItem = (id) => {
  router.get(`/admin/rejected-opportunities/${id}`)
}

const editItem = (id) => {
  router.get(`/admin/rejected-opportunities/${id}/edit`)
}

const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

const revertItem = (id, source) => {
  const sourceName = formatSource(source)
  if (confirm(`Are you sure you want to revert this item? It will be moved back to ${sourceName}.`)) {
    router.post(`/admin/rejected-opportunities/${id}/revert`, {}, {
      onSuccess: () => {
        // Success handled by controller
      },
      onError: (errors) => {
        alert('Failed to revert item. Please try again.')
      }
    })
  }
}

const deleteItem = (id) => {
  if (confirm('Are you sure you want to permanently delete this rejected opportunity?')) {
    router.delete(`/admin/rejected-opportunities/${id}`, {
      onSuccess: () => {
        // Success handled by controller
      },
      onError: (errors) => {
        alert('Failed to delete item. Please try again.')
      }
    })
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Custom scrollbar for better appearance */
.overflow-x-auto::-webkit-scrollbar {
  height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>