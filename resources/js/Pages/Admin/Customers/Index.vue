<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-teal-50 flex">
    <!-- Sidebar -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content Area -->
    <div class="flex-1 min-w-0 flex flex-col overflow-hidden">
      <!-- Mobile/Tablet Header -->
      <div class="lg:hidden sticky top-0 z-20 bg-white shadow-sm border-b border-gray-200">
        <div class="flex items-center justify-between px-4 py-3">
          <div class="w-12 flex-shrink-0"></div>
          <div class="flex-1 text-center min-w-0">
            <h1 class="text-lg font-semibold text-gray-900 flex items-center justify-center gap-2 truncate">
              <span class="truncate">Customers</span>
              <div class="w-5 h-5 bg-gradient-to-r from-blue-400/30 to-blue-500/30 rounded flex items-center justify-center flex-shrink-0">
                <UserGroupIcon class="w-3 h-3 text-blue-600/70" />
              </div>
            </h1>
          </div>
          <div class="w-12 flex-shrink-0"></div>
        </div>
      </div>
    
      <!-- Desktop Header -->
      <div class="hidden lg:block sticky top-0 z-30 bg-white shadow-sm border-b border-gray-200">
        <div class="px-6 py-4">
          <div class="flex justify-between items-center">
            <div class="min-w-0">
              <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3 truncate">
                <span class="truncate">Customers</span>
                <div class="w-7 h-7 bg-gradient-to-r from-teal-400/30 to-blue-400/30 rounded-lg flex items-center justify-center flex-shrink-0">
                  <UserGroupIcon class="w-4 h-4 text-teal-600/70" />
                </div>
              </h1>
              <p class="text-gray-600 mt-1 truncate">Manage and review approved customers</p>
            </div>
          </div>
        </div>

        <!-- Flash Messages -->
        <div v-if="flashMessage" class="px-3 md:px-4 lg:px-6 py-2">
          <div :class="flashMessageClass" class="rounded-lg p-3 md:p-4 shadow-sm border">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <CheckCircleIcon v-if="flashMessageType === 'success'" class="w-5 h-5 text-green-500 flex-shrink-0" />
                <ExclamationCircleIcon v-else class="w-5 h-5 text-red-500 flex-shrink-0" />
                <p class="font-medium text-sm truncate">{{ flashMessage }}</p>
              </div>
              <button 
                @click="clearFlashMessage" 
                class="text-gray-400 hover:text-gray-600 flex-shrink-0"
              >
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 overflow-auto">
        <!-- Compact Filters Section -->
        <div class="bg-white border-b border-gray-200 px-3 md:px-4 lg:px-6 py-3 md:py-4 filters-section">
          <div class="flex flex-col gap-3">
            <!-- Search and Status Filters -->
            <div class="flex flex-col md:flex-row md:items-center gap-3">
              <!-- Search Input -->
              <div class="relative flex-1 min-w-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <MagnifyingGlassIcon class="h-4 w-4 md:h-5 md:w-5 text-gray-400" />
                </div>
                <input
                  v-model="search"
                  @input="onSearchInput"
                  type="text"
                  placeholder="Search customers..."
                  class="block w-full pl-9 md:pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500 truncate transition-all duration-300"
                />
              </div>

              <!-- Status Filters -->
              <div class="flex flex-wrap gap-2">
                <button 
                  v-for="filter in statusFilters"
                  :key="filter.key"
                  @click="setStatusFilter(filter.key)"
                  class="inline-flex items-center px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all duration-300 shadow-sm hover:shadow whitespace-nowrap"
                  :class="getFilterButtonClass(filter.key)"
                >
                  <span class="font-bold mr-1.5 text-white bg-white/20 px-1 py-0.5 rounded">
                    {{ getFilterCount(filter.key) }}
                  </span>
                  <span class="truncate">{{ filter.label }}</span>
                </button>
              </div>
            </div>

            <!-- Advanced Filters -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <button
                  @click="isFilterExpanded = !isFilterExpanded"
                  class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg hover:from-blue-600 hover:to-purple-600 transition-all duration-300 shadow-sm hover:shadow whitespace-nowrap"
                >
                  <FunnelIcon class="w-4 h-4 flex-shrink-0" />
                  <span class="truncate">Advanced Filters</span>
                  <ChevronDownIcon class="w-4 h-4 transition-transform duration-300 flex-shrink-0" :class="{'rotate-180': isFilterExpanded}" />
                </button>

                <!-- Active Filters Badge -->
                <button
                  v-if="hasActiveFilters"
                  @click="clearAllFilters"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-gradient-to-r from-red-500 to-orange-500 rounded-lg hover:from-red-600 hover:to-orange-600 transition-all duration-300 shadow-sm hover:shadow whitespace-nowrap"
                >
                  <XMarkIcon class="w-3 h-3 mr-1 flex-shrink-0" />
                  Clear Filters ({{ activeFilterCount }})
                </button>
              </div>
            </div>

            <!-- Expanded Filters Dropdown -->
            <transition name="dropdown">
              <div v-if="isFilterExpanded" class="bg-white rounded-lg shadow border border-gray-200 p-4 transition-all duration-300">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- City Filter -->
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-2 flex items-center">
                      <BuildingOfficeIcon class="w-4 h-4 mr-1 text-blue-500 flex-shrink-0" />
                      <span>City</span>
                    </label>
                    <select
                      v-model="cityFilter"
                      @change="onCityFilterChange"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white appearance-none transition-all duration-300"
                    >
                      <option value="">All Cities</option>
                      <option v-for="city in cities" :key="city.id" :value="city.id">
                        {{ city.name }}
                      </option>
                    </select>
                  </div>

                  <!-- Payment Status Filter -->
                  <div>
                    <label class="block text-xs font-medium text-gray-700 mb-2 flex items-center">
                      <CreditCardIcon class="w-4 h-4 mr-1 text-green-500 flex-shrink-0" />
                      <span>Payment Status</span>
                    </label>
                    <select
                      v-model="paymentStatusFilter"
                      @change="onPaymentStatusChange"
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white appearance-none transition-all duration-300"
                    >
                      <option value="">All Payment Status</option>
                      <option value="paid">Paid</option>
                      <option value="half_paid">Half Paid</option>
                      <option value="pending">Pending</option>
                      <option value="not_paid">Not Paid</option>
                    </select>
                  </div>
                </div>

                <!-- Active Filters Tags -->
                <div v-if="hasActiveFilters && activeFilterTags.length > 0" class="mt-4 pt-4 border-t border-gray-200">
                  <div class="flex flex-wrap gap-1.5">
                    <div 
                      v-for="tag in activeFilterTags"
                      :key="tag.key"
                      class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border shadow-sm whitespace-nowrap transition-all duration-300"
                      :class="tag.class"
                    >
                      <component :is="tag.icon" class="w-3 h-3 mr-1.5 flex-shrink-0" />
                      <span class="truncate">{{ tag.label }}</span>
                      <button @click="tag.remove()" class="ml-1.5 hover:opacity-75 flex-shrink-0 transition-all duration-200">
                        <XMarkIcon class="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </transition>
          </div>
        </div>

        <!-- Main Content Area -->
        <div class="p-3 md:p-4 lg:p-5">
          <!-- Loading State -->
          <div v-if="loading" class="flex items-center justify-center p-8">
            <div class="text-center">
              <div class="w-12 h-12 border-4 border-teal-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
              <p class="text-gray-600">Loading customers...</p>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else-if="!hasCustomers" class="bg-white rounded-lg border border-gray-200 p-6 text-center shadow-sm">
            <div class="w-16 h-16 bg-gradient-to-br from-teal-100 to-teal-200 rounded-xl flex items-center justify-center mx-auto mb-4">
              <UserGroupIcon class="w-8 h-8 text-teal-600" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No customers found</h3>
            <p class="text-gray-600 mb-4 max-w-md mx-auto text-sm">
              Customer records will appear here after approval from potential customers.
            </p>
            <button 
              @click="clearAllFilters"
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-teal-500 to-teal-600 text-white text-sm font-medium rounded-lg hover:from-teal-600 hover:to-teal-700 transition-all duration-300 shadow-sm hover:shadow"
            >
              <ArrowPathIcon class="w-4 h-4 mr-2" />
              Clear Filters
            </button>
          </div>

          <!-- Customers Content -->
          <div v-else class="space-y-4">
            <!-- Stats Summary -->
            <div class="bg-white rounded-lg border border-gray-200 p-4 shadow-sm">
              <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                <div>
                  <h3 class="text-sm font-semibold text-gray-900">Customer Records</h3>
                  <p class="text-gray-600 text-xs mt-0.5">
                    Showing {{ filteredCustomers.length }} of {{ totalCustomers }} results
                    <span v-if="statusFilter !== 'all'" class="ml-2 px-1.5 py-0.5 rounded text-xs" :class="getFilterButtonClass(statusFilter)">
                      {{ getStatusLabel(statusFilter) }}
                    </span>
                  </p>
                </div>
                <div class="text-xs text-gray-500">
                  Page {{ customers.meta?.current_page || 1 }} of {{ customers.meta?.last_page || 1 }}
                </div>
              </div>
            </div>

            <!-- Customers Grid - Mobile & Desktop -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div 
                v-for="customer in filteredCustomers" 
                :key="customer?.id"
                class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition-all duration-300"
              >
                <!-- Customer Header -->
                <div class="flex items-start justify-between mb-3">
                  <div class="flex items-center space-x-3 min-w-0">
                    <div class="relative flex-shrink-0">
                      <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-teal-600 rounded-lg flex items-center justify-center shadow-sm">
                        <UserCircleIcon class="w-5 h-5 text-white" />
                      </div>
                      <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-white rounded-full border border-white shadow-sm">
                        <div class="w-full h-full rounded-full" :class="getStatusDotClass(customer?.status)"></div>
                      </div>
                    </div>
                    <div class="min-w-0">
                      <h3 class="font-semibold text-gray-900 text-sm truncate">
                        {{ customer?.name || 'Unknown Customer' }}
                      </h3>
                      <p class="text-gray-500 text-xs truncate">
                        {{ customer?.email || 'No email' }}
                      </p>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-xs font-medium text-gray-600">ID: {{ customer?.id || 'N/A' }}</div>
                    <div class="text-xs text-gray-500">{{ formatDate(customer?.updated_at) }}</div>
                  </div>
                </div>

                <!-- Customer Details -->
                <div class="space-y-3">
                  <!-- Contact -->
                  <div class="flex items-center space-x-2">
                    <PhoneIcon class="w-4 h-4 text-gray-400 flex-shrink-0" />
                    <span class="text-sm text-gray-700 truncate">{{ customer?.phone || 'No phone' }}</span>
                  </div>

                  <!-- Location -->
                  <div class="flex items-start space-x-2">
                    <BuildingOfficeIcon class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" />
                    <div class="min-w-0">
                      <span class="text-sm text-gray-900">
                        {{ customer?.city?.name || 'No city' }}
                        <span v-if="customer?.subcity" class="text-gray-600">
                          / {{ customer.subcity.name }}
                        </span>
                      </span>
                      <div v-if="customer?.text_location" class="text-xs text-gray-500 truncate mt-0.5">
                        {{ truncateText(customer.text_location, 40) }}
                      </div>
                    </div>
                  </div>

                  <!-- Payment Status -->
                  <div>
                    <div class="flex items-center justify-between mb-1">
                      <span :class="getPaymentStatusBadgeClass(customer?.payment_status)" 
                        class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-lg shadow-sm">
                        <CreditCardIcon class="w-3 h-3 mr-1" />
                        {{ formatPaymentStatus(customer?.payment_status) }}
                      </span>
                      <div class="text-xs font-medium text-gray-900">
                        ${{ formatNumber(customer?.paid_amount || 0) }}/${{ formatNumber(customer?.total_payment_amount || 0) }}
                      </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                      <div 
                        class="h-1.5 rounded-full transition-all duration-500"
                        :class="getPaymentProgressClass(customer?.payment_status)"
                        :style="{ width: (customer?.payment_progress || 0) + '%' }"
                      ></div>
                    </div>
                  </div>

                  <!-- Commission -->
                  <div v-if="customer?.commission_user" class="space-y-2">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center min-w-0">
                        <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center mr-2 flex-shrink-0">
                          <UserIcon class="w-3 h-3 text-white" />
                        </div>
                        <span class="text-xs font-medium text-gray-900 truncate">
                          {{ customer.commission_user.name }}
                        </span>
                      </div>
                      <span class="text-xs font-bold text-teal-600 bg-teal-50 px-2 py-1 rounded">
                        {{ customer.commission_rate }}%
                      </span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-600">
                      <span>Commission: ${{ formatNumber(customer?.commission_amount || 0) }}</span>
                      <span>Paid: ${{ formatNumber(customer?.paid_commission || 0) }}</span>
                    </div>
                  </div>
                  <div v-else class="text-center py-2">
                    <span class="text-xs text-gray-500 italic">No commission assigned</span>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-200">
                  <div class="text-xs text-gray-600 font-medium">
                    Status: {{ formatStatus(customer?.status) }}
                  </div>
                  <div class="flex items-center space-x-1">
                    <button 
                      @click="viewCustomerDetails(customer)"
                      class="bg-teal-500 hover:bg-teal-600 text-white p-1.5 rounded-lg text-xs font-medium transition-all duration-300 flex items-center justify-center shadow-sm hover:shadow"
                      title="View Details"
                    >
                      <EyeIcon class="w-3.5 h-3.5" />
                    </button>
                    <button 
                      v-if="permissions.edit"
                      @click="editCustomer(customer)"
                      class="bg-blue-500 hover:bg-blue-600 text-white p-1.5 rounded-lg text-xs font-medium transition-all duration-300 flex items-center justify-center shadow-sm hover:shadow"
                      title="Edit Customer"
                    >
                      <PencilIcon class="w-3.5 h-3.5" />
                    </button>
                    <button 
                      v-if="permissions.delete"
                      @click="deleteCustomer(customer.id)"
                      class="bg-red-500 hover:bg-red-600 text-white p-1.5 rounded-lg text-xs font-medium transition-all duration-300 flex items-center justify-center shadow-sm hover:shadow"
                      title="Delete Customer"
                    >
                      <TrashIcon class="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="customers.links && customers.links.length > 3" class="bg-white rounded-lg border border-gray-200 p-4">
              <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                <div class="text-xs text-gray-600">
                  Showing {{ customers.meta?.from || 0 }}-{{ customers.meta?.to || 0 }} of {{ customers.meta?.total || 0 }}
                </div>
                <div class="flex items-center gap-1">
                  <!-- Previous Page -->
                  <button 
                    v-if="customers.links[0].url"
                    @click="visitPage(customers.links[0].url)"
                    class="p-2 text-gray-600 hover:text-white hover:bg-gradient-to-r hover:from-teal-500 hover:to-teal-600 rounded-lg transition-all duration-300 shadow-sm hover:shadow"
                    title="Previous"
                  >
                    <ChevronLeftIcon class="w-4 h-4" />
                  </button>

                  <!-- Page Numbers -->
                  <div class="flex items-center gap-1">
                    <button 
                      v-for="link in customers.links.slice(1, -1)"
                      :key="link.label"
                      :disabled="!link.url"
                      @click="visitPage(link.url)"
                      class="min-w-[32px] h-8 px-2 text-xs font-medium rounded-lg transition-all duration-300 shadow-sm"
                      :class="link.active 
                        ? 'bg-gradient-to-r from-teal-500 to-teal-600 text-white shadow' 
                        : 'text-gray-600 bg-white border border-gray-300 hover:bg-gradient-to-r hover:from-teal-50 hover:to-teal-100 hover:border-teal-300'"
                      v-html="link.label"
                    ></button>
                  </div>

                  <!-- Next Page -->
                  <button 
                    v-if="customers.links[customers.links.length - 1].url"
                    @click="visitPage(customers.links[customers.links.length - 1].url)"
                    class="p-2 text-gray-600 hover:text-white hover:bg-gradient-to-r hover:from-teal-500 hover:to-teal-600 rounded-lg transition-all duration-300 shadow-sm hover:shadow"
                    title="Next"
                  >
                    <ChevronRightIcon class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Customer Modal -->
    <EditCustomerModal 
      v-if="showEditModal"
      :is-open="showEditModal"
      :customer="selectedCustomer"
      :cities="cities"
      :subcities="subcities"
      @close="closeEditModal"
      @success="onEditSuccess"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import EditCustomerModal from '@/Pages/Admin/Customers/EditCustomerModal.vue'
import {
  EyeIcon,
  UserGroupIcon,
  UserCircleIcon,
  PencilIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  XMarkIcon,
  TrashIcon,
  FunnelIcon,
  ChevronDownIcon,
  BuildingOfficeIcon,
  ArrowPathIcon,
  CheckIcon,
  EnvelopeIcon,
  PhoneIcon,
  MagnifyingGlassIcon,
  CreditCardIcon,
  UserIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  XCircleIcon,
  DocumentIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  customers: Object,
  permissions: Object,
  tables: Array,
  cities: Array,
  subcities: Array,
  filters: Object
})

// State
const loading = ref(false)
const isFilterExpanded = ref(false)
const search = ref(props.filters?.search || '')
const statusFilter = ref(props.filters?.status || 'all')
const cityFilter = ref(props.filters?.city_id || '')
const paymentStatusFilter = ref(props.filters?.payment_status || '')

// Edit Modal State
const showEditModal = ref(false)
const selectedCustomer = ref(null)

// Flash Message State
const flashMessage = ref('')
const flashMessageType = ref('success')

// Status filters
const statusFilters = [
  { key: 'all', label: 'All Customers' },
  { key: 'draft', label: 'Draft' },
  { key: 'contract_created', label: 'Contract Created' },
  { key: 'accepted', label: 'Accepted' },
  { key: 'rejected', label: 'Rejected' }
]

// Computed properties
const hasCustomers = computed(() => {
  return props.customers?.data?.length > 0
})

const totalCustomers = computed(() => {
  return props.customers?.meta?.total || props.customers?.data?.length || 0
})

const draftCount = computed(() => {
  return props.customers?.data?.filter(c => c?.status === 'draft').length || 0
})

const contractCreatedCount = computed(() => {
  return props.customers?.data?.filter(c => c?.status === 'contract_created').length || 0
})

const acceptedCount = computed(() => {
  return props.customers?.data?.filter(c => c?.status === 'accepted').length || 0
})

const rejectedCount = computed(() => {
  return props.customers?.data?.filter(c => c?.status === 'rejected').length || 0
})

const activeFilterCount = computed(() => {
  let count = 0
  if (statusFilter.value !== 'all') count++
  if (cityFilter.value) count++
  if (paymentStatusFilter.value) count++
  if (search.value) count++
  return count
})

const hasActiveFilters = computed(() => {
  return statusFilter.value !== 'all' || cityFilter.value || paymentStatusFilter.value || search.value
})

const activeFilterTags = computed(() => {
  const tags = []
  
  if (statusFilter.value !== 'all') {
    tags.push({
      key: 'status',
      label: `Status: ${getStatusLabel(statusFilter.value)}`,
      icon: getFilterIcon(statusFilter.value),
      class: 'bg-blue-50 text-blue-700 border-blue-200',
      remove: () => {
        statusFilter.value = 'all'
        applyFilters()
      }
    })
  }
  
  if (cityFilter.value) {
    const cityName = getCityName(cityFilter.value)
    tags.push({
      key: 'city',
      label: `City: ${cityName}`,
      icon: BuildingOfficeIcon,
      class: 'bg-green-50 text-green-700 border-green-200',
      remove: () => {
        cityFilter.value = ''
        applyFilters()
      }
    })
  }
  
  if (paymentStatusFilter.value) {
    tags.push({
      key: 'payment_status',
      label: `Payment: ${formatPaymentStatus(paymentStatusFilter.value)}`,
      icon: CreditCardIcon,
      class: 'bg-purple-50 text-purple-700 border-purple-200',
      remove: () => {
        paymentStatusFilter.value = ''
        applyFilters()
      }
    })
  }
  
  if (search.value) {
    tags.push({
      key: 'search',
      label: `Search: "${search.value}"`,
      icon: MagnifyingGlassIcon,
      class: 'bg-yellow-50 text-yellow-700 border-yellow-200',
      remove: () => {
        search.value = ''
        applyFilters()
      }
    })
  }
  
  return tags
})

const filteredCustomers = computed(() => {
  if (!props.customers?.data) return []
  
  let filtered = props.customers.data.filter(customer => customer != null)
  
  // Search filter
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    filtered = filtered.filter(customer => 
      customer.name?.toLowerCase().includes(searchLower) ||
      customer.email?.toLowerCase().includes(searchLower) ||
      customer.phone?.toLowerCase().includes(searchLower) ||
      customer.city?.name?.toLowerCase().includes(searchLower) ||
      customer.subcity?.name?.toLowerCase().includes(searchLower) ||
      customer.text_location?.toLowerCase().includes(searchLower)
    )
  }
  
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(customer => customer?.status === statusFilter.value)
  }
  
  if (cityFilter.value) {
    filtered = filtered.filter(customer => customer?.city_id == cityFilter.value)
  }
  
  if (paymentStatusFilter.value) {
    filtered = filtered.filter(customer => customer?.payment_status === paymentStatusFilter.value)
  }
  
  return filtered
})

const flashMessageClass = computed(() => {
  return flashMessageType.value === 'success'
    ? 'bg-green-50 border-green-200 text-green-800'
    : 'bg-red-50 border-red-200 text-red-800'
})

// Helper methods
function getFilterCount(filterKey) {
  switch (filterKey) {
    case 'all': return totalCustomers.value
    case 'draft': return draftCount.value
    case 'contract_created': return contractCreatedCount.value
    case 'accepted': return acceptedCount.value
    case 'rejected': return rejectedCount.value
    default: return 0
  }
}

function getFilterButtonClass(filterKey) {
  if (filterKey === statusFilter.value) {
    const activeColors = {
      all: 'bg-gradient-to-r from-teal-500 to-teal-600 text-white shadow',
      draft: 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow',
      contract_created: 'bg-gradient-to-r from-indigo-500 to-indigo-600 text-white shadow',
      accepted: 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow',
      rejected: 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow'
    }
    return activeColors[filterKey] || 'bg-gradient-to-r from-teal-500 to-teal-600 text-white shadow'
  }
  
  const inactiveColors = {
    all: 'bg-white text-gray-600 border border-gray-300 hover:bg-gradient-to-r hover:from-teal-50 hover:to-teal-100 hover:border-teal-300',
    draft: 'bg-white text-blue-600 border border-blue-200 hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100',
    contract_created: 'bg-white text-indigo-600 border border-indigo-200 hover:bg-gradient-to-r hover:from-indigo-50 hover:to-indigo-100',
    accepted: 'bg-white text-green-600 border border-green-200 hover:bg-gradient-to-r hover:from-green-50 hover:to-green-100',
    rejected: 'bg-white text-red-600 border border-red-200 hover:bg-gradient-to-r hover:from-red-50 hover:to-red-100'
  }
  return inactiveColors[filterKey] || 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'
}

function getFilterIcon(filterKey) {
  const icons = {
    all: FunnelIcon,
    draft: DocumentIcon,
    contract_created: DocumentIcon,
    accepted: CheckCircleIcon,
    rejected: XCircleIcon
  }
  return icons[filterKey] || FunnelIcon
}

function getStatusDotClass(status) {
  if (!status) return 'bg-gray-400'
  
  const classes = {
    draft: 'bg-gradient-to-r from-blue-400 to-blue-500',
    contract_created: 'bg-gradient-to-r from-indigo-400 to-indigo-500',
    accepted: 'bg-gradient-to-r from-green-400 to-green-500',
    rejected: 'bg-gradient-to-r from-red-400 to-red-500'
  }
  return classes[status] || 'bg-gray-400'
}

function getPaymentStatusBadgeClass(status) {
  if (!status) return 'bg-gray-100 text-gray-800 shadow-sm'
  
  const classes = {
    paid: 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 shadow-sm',
    half_paid: 'bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 shadow-sm',
    pending: 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 shadow-sm',
    not_paid: 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 shadow-sm'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 shadow-sm'
}

function getPaymentProgressClass(status) {
  if (!status) return 'bg-gray-400'
  
  const classes = {
    paid: 'bg-gradient-to-r from-green-500 to-green-600',
    half_paid: 'bg-gradient-to-r from-yellow-500 to-yellow-600',
    pending: 'bg-gradient-to-r from-blue-500 to-blue-600',
    not_paid: 'bg-gradient-to-r from-red-500 to-red-600'
  }
  return classes[status] || 'bg-gray-400'
}

function getStatusLabel(status) {
  const statusMap = {
    all: 'All Customers',
    draft: 'Draft',
    contract_created: 'Contract Created',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return statusMap[status] || status
}

function getCityName(cityId) {
  const city = props.cities.find(c => c.id == cityId)
  return city?.name || 'Unknown City'
}

function formatStatus(status) {
  if (!status) return 'Unknown'
  
  const statusMap = {
    draft: 'Draft',
    contract_created: 'Contract Created',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return statusMap[status] || status
}

function formatPaymentStatus(status) {
  if (!status) return 'Unknown'
  
  const statusMap = {
    paid: 'Paid',
    half_paid: 'Half Paid',
    pending: 'Pending',
    not_paid: 'Not Paid'
  }
  return statusMap[status] || status
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined
  })
}

function formatNumber(num) {
  if (!num) return '0.00'
  return parseFloat(num).toFixed(2)
}

function truncateText(text, maxLength) {
  if (!text || text.length <= maxLength) return text
  return text.substring(0, maxLength) + '...'
}

// Flash message methods
const clearFlashMessage = () => {
  flashMessage.value = ''
  flashMessageType.value = 'success'
}

const showFlashMessage = (msg, type = 'success') => {
  flashMessage.value = msg
  flashMessageType.value = type
  
  setTimeout(() => {
    clearFlashMessage()
  }, 5000)
}

// Edit Modal methods
const editCustomer = (customer) => {
  if (customer?.id) {
    selectedCustomer.value = customer
    showEditModal.value = true
  }
}

const closeEditModal = () => {
  showEditModal.value = false
  selectedCustomer.value = null
}

const onEditSuccess = (message) => {
  showFlashMessage(message || 'Customer updated successfully!', 'success')
  
  // Refresh the page to get updated data
  setTimeout(() => {
    router.reload({ preserveScroll: true, preserveState: false })
  }, 1500)
}

// Navigation methods
const visitPage = (url) => {
  if (url) {
    loading.value = true
    router.get(url, {}, {
      onFinish: () => {
        loading.value = false
      }
    })
  }
}

const setStatusFilter = (filter) => {
  statusFilter.value = filter
  applyFilters()
}

const onCityFilterChange = () => {
  isFilterExpanded.value = false
  applyFilters()
}

const onPaymentStatusChange = () => {
  isFilterExpanded.value = false
  applyFilters()
}

// Debounce for search
let searchTimeout = null

const onSearchInput = () => {
  // Clear previous timeout
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
  
  // Set new timeout
  searchTimeout = setTimeout(() => {
    applyFilters()
  }, 500)
}

const applyFilters = () => {
  const params = {}
  if (statusFilter.value !== 'all') params.status = statusFilter.value
  if (cityFilter.value) params.city_id = cityFilter.value
  if (paymentStatusFilter.value) params.payment_status = paymentStatusFilter.value
  if (search.value) params.search = search.value
  
  loading.value = true
  router.get('/admin/customers', params, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      loading.value = false
      isFilterExpanded.value = false
    }
  })
}

const clearAllFilters = () => {
  search.value = ''
  statusFilter.value = 'all'
  cityFilter.value = ''
  paymentStatusFilter.value = ''
  applyFilters()
}

const viewCustomerDetails = (customer) => {
  if (customer?.id) {
    router.get(`/admin/customers/${customer.id}`)
  }
}

const deleteCustomer = (customerId) => {
  if (confirm('Are you sure you want to delete this customer?')) {
    loading.value = true
    router.delete(`/admin/customers/${customerId}`, {
      preserveScroll: true,
      onSuccess: () => {
        loading.value = false
        showFlashMessage('Customer deleted successfully!', 'success')
        router.reload({ preserveScroll: true, preserveState: false })
      },
      onError: () => {
        loading.value = false
        showFlashMessage('Failed to delete customer. Please try again.', 'error')
      }
    })
  }
}

// Setup click outside listener for filters only
onMounted(() => {
  const handleClickOutside = (event) => {
    if (isFilterExpanded.value && !event.target.closest('.filters-section')) {
      isFilterExpanded.value = false
    }
  }
  
  document.addEventListener('click', handleClickOutside)
  
  return () => {
    document.removeEventListener('click', handleClickOutside)
  }
})

// Cleanup on unmount
onUnmounted(() => {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }
})
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #0d9488, #0891b2);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #0f766e, #0e7490);
}

/* Smooth transitions */
* {
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform, background-image;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Dropdown animation */
.dropdown-enter-active,
.dropdown-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.dropdown-enter-from {
  opacity: 0;
  transform: translateY(-10px);
}

.dropdown-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}

/* Prevent overflow */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.min-w-0 {
  min-width: 0;
}
</style>