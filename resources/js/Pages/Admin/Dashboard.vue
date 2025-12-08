<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-blue-50 flex">
    <!-- Sidebar -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content -->
    <div class="flex-1 relative">
      <!-- Animated Background Elements -->
      <div class="absolute top-0 right-0 w-72 h-72 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full blur-3xl opacity-20 animate-pulse"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-blue-100 to-cyan-200 rounded-full blur-3xl opacity-30 animate-bounce"></div>
      
      <div class="relative">
        <!-- Fixed Header -->
        <div class="bg-white/80 backdrop-blur-sm border-b border-blue-200/50 px-6 py-6 sticky top-0 z-40">
          <div class="flex justify-between items-center">
            <div class="flex items-center space-x-4">
              <div class="relative">
                <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-2xl flex items-center justify-center shadow-lg">
                  <BarChart3 class="w-7 h-7 text-white" />
                </div>
                <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-400 rounded-full border-2 border-white animate-ping"></div>
              </div>
              <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                  Dashboard
                </h1>
                <p class="text-slate-600 mt-1 flex items-center space-x-2">
                  <span>Welcome back, {{ user?.name || 'User' }}</span>
                  <span class="text-xs bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-600 px-2 py-1 rounded-full capitalize">
                    {{ userRole }}
                  </span>
                </p>
              </div>
            </div>
            
            <div class="flex items-center space-x-4">
              <!-- Notifications -->
              <div class="relative">
                <button
                  id="notificationButton"
                  @click="toggleNotifications"
                  class="relative bg-white/80 backdrop-blur-sm border border-blue-200/50 p-3 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 z-50"
                >
                  <Bell class="w-6 h-6 text-blue-600" />
                  <span
                    v-if="enhancedNotifications.length > 0"
                    class="absolute -top-1 -right-1 w-5 h-5 bg-blue-400 text-white text-xs rounded-full border-2 border-white flex items-center justify-center animate-bounce"
                  >
                    {{ enhancedNotifications.length }}
                  </span>
                </button>

                <!-- Notifications Dropdown - Highest z-index -->
                <div
                  id="notificationDropdown"
                  v-show="showNotifications"
                  class="absolute right-0 mt-3 w-96 bg-white/95 backdrop-blur-sm border border-blue-200/50 rounded-2xl shadow-2xl z-[9999] max-h-96 overflow-y-auto transition-all"
                >
                  <div class="divide-y divide-blue-100">
                    <!-- Header -->
                    <div class="flex justify-between items-center px-4 py-3 bg-blue-50/80 border-b border-blue-100">
                      <span class="font-semibold text-sm text-blue-800">Notifications</span>
                      <div class="flex items-center gap-2">
                        <span class="text-xs text-blue-600 bg-blue-100 px-2 py-1 rounded-full">
                          {{ enhancedNotifications.length }} unread
                        </span>
                        <button
                          v-if="enhancedNotifications.length > 0"
                          @click="markAllAsRead"
                          class="text-xs text-blue-600 hover:text-blue-800 hover:underline font-medium"
                        >
                          Mark all as read
                        </button>
                      </div>
                    </div>

                    <!-- Notifications List -->
                    <div v-if="enhancedNotifications.length > 0">
                      <div
                        v-for="(notification, index) in enhancedNotifications"
                        :key="notification.id"
                        class="p-4 hover:bg-blue-50/50 transition border-b border-blue-100 last:border-b-0"
                      >
                        <div class="flex gap-3">
                          <!-- Notification Icon -->
                          <div class="flex-shrink-0">
                            <component
                              :is="getNotificationIcon(notification.type).icon"
                              :class="['w-5 h-5', getNotificationIcon(notification.type).color]"
                            />
                          </div>
                          
                          <!-- Notification Content -->
                          <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between mb-1">
                              <p class="text-sm font-medium text-blue-900 truncate">
                                {{ notification.proposal_title }}
                              </p>
                              <span 
                                :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', getBadgeColor(notification.type)]"
                              >
                                {{ notification.action }}
                              </span>
                            </div>
                            
                            <p class="text-sm text-blue-600 mb-2">
                              {{ notification.message }}
                            </p>
                            
                            <div class="flex items-center justify-between">
                              <div class="flex items-center gap-2 text-xs text-blue-500">
                                <span>By: {{ notification.actor_name }}</span>
                                <span v-if="notification.actor_role" class="capitalize">({{ notification.actor_role }})</span>
                              </div>
                              <span class="text-xs text-blue-400">
                                {{ notification.created_at }}
                              </span>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex gap-2 mt-3">
                              <a
                                :href="notification.url"
                                class="text-xs bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition"
                                @click="markAsRead(notification.id)"
                              >
                                View Proposal
                              </a>
                              <button
                                @click="markAsRead(notification.id)"
                                class="text-xs text-blue-500 hover:text-blue-700 underline"
                              >
                                Mark as read
                              </button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="p-6 text-center">
                      <Bell class="w-12 h-12 text-blue-300 mx-auto mb-2" />
                      <p class="text-blue-500 text-sm">No new notifications</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Flash Message -->
        <div v-if="flashSuccess" class="mx-6 mt-6 bg-gradient-to-r from-emerald-100 to-cyan-100 text-emerald-700 p-4 rounded-2xl border border-emerald-200/50 shadow-lg backdrop-blur-sm relative z-30">
          <div class="flex items-center space-x-3">
            <CheckCircle class="w-5 h-5 text-emerald-600" />
            <span>{{ flashSuccess }}</span>
          </div>
        </div>

        <!-- Content - Lower z-index -->
        <div class="p-6 relative z-10">
          <!-- Role-based Quick Stats -->
          <div v-if="Object.keys(safeProposalStats).length > 0" class="mb-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
              <div
                v-for="(value, key) in safeProposalStats"
                :key="key"
                class="bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl border border-cyan-400/50 p-6 text-center shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105 group"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-semibold text-cyan-100 uppercase tracking-wide capitalize">
                      {{ key.replace(/_/g, ' ') }}
                    </p>
                    <p class="text-3xl font-bold text-white mt-2">
                      {{ value }}
                    </p>
                  </div>
                  <div class="w-10 h-10 bg-cyan-400/20 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 border border-cyan-300/50">
                    <FileText class="w-5 h-5 text-white" />
                  </div>
                </div>
                <div class="w-16 h-1 bg-cyan-300 rounded-full mx-auto mt-4 group-hover:w-20 transition-all duration-300"></div>
              </div>
            </div>
          </div>

          <!-- Creative Metrics Cards - Blueish Green Theme -->
          <div
            v-if="showCounts"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8"
          >
            <!-- Products Card -->
            <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
              <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-400/30 rounded-full -translate-y-16 translate-x-16"></div>
              <div class="absolute bottom-0 left-0 w-24 h-24 bg-cyan-300/20 rounded-full translate-y-12 -translate-x-12"></div>
              
              <div class="relative z-10 flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-white">Products</h2>
                  <p class="text-3xl font-bold text-white mt-2">{{ safeCounts.products ?? 0 }}</p>
                  <div class="flex items-center space-x-1 mt-3">
                    <TrendingUp class="w-4 h-4 text-cyan-200" />
                    <span class="text-sm text-cyan-100">Growth</span>
                  </div>
                </div>
                <div class="w-16 h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                  <Package class="w-8 h-8 text-white" />
                </div>
              </div>
            </div>

            <!-- Customers Card -->
            <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
              <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-400/30 rounded-full -translate-y-16 translate-x-16"></div>
              <div class="absolute bottom-0 left-0 w-24 h-24 bg-cyan-300/20 rounded-full translate-y-12 -translate-x-12"></div>
              
              <div class="relative z-10 flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-white">Customers</h2>
                  <p class="text-3xl font-bold text-white mt-2">{{ safeCounts.customers ?? 0 }}</p>
                  <div class="flex items-center space-x-1 mt-3">
                    <Users class="w-4 h-4 text-cyan-200" />
                    <span class="text-sm text-cyan-100">Active clients</span>
                  </div>
                </div>
                <div class="w-16 h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                  <Users class="w-8 h-8 text-white" />
                </div>
              </div>
            </div>

            <!-- Opportunities Card -->
            <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
              <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-400/30 rounded-full -translate-y-16 translate-x-16"></div>
              <div class="absolute bottom-0 left-0 w-24 h-24 bg-cyan-300/20 rounded-full translate-y-12 -translate-x-12"></div>
              
              <div class="relative z-10 flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-white">Opportunities</h2>
                  <p class="text-3xl font-bold text-white mt-2">{{ safeCounts.opportunities ?? 0 }}</p>
                  <div class="flex items-center space-x-1 mt-3">
                    <Briefcase class="w-4 h-4 text-cyan-200" />
                    <span class="text-sm text-cyan-100">Hot leads</span>
                  </div>
                </div>
                <div class="w-16 h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                  <Briefcase class="w-8 h-8 text-white" />
                </div>
              </div>
            </div>

            <!-- Industries Card -->
            <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
              <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-400/30 rounded-full -translate-y-16 translate-x-16"></div>
              <div class="absolute bottom-0 left-0 w-24 h-24 bg-cyan-300/20 rounded-full translate-y-12 -translate-x-12"></div>
              
              <div class="relative z-10 flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-white">Industries</h2>
                  <p class="text-3xl font-bold text-white mt-2">{{ safeCounts.industries ?? 0 }}</p>
                  <div class="flex items-center space-x-1 mt-3">
                    <Globe class="w-4 h-4 text-cyan-200" />
                    <span class="text-sm text-cyan-100">Diverse sectors</span>
                  </div>
                </div>
                <div class="w-16 h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                  <Globe class="w-8 h-8 text-white" />
                </div>
              </div>
            </div>

            <!-- Contracts Card -->
            <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
              <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-400/30 rounded-full -translate-y-16 translate-x-16"></div>
              <div class="absolute bottom-0 left-0 w-24 h-24 bg-cyan-300/20 rounded-full translate-y-12 -translate-x-12"></div>
              
              <div class="relative z-10 flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-white">Contracts</h2>
                  <p class="text-3xl font-bold text-white mt-2">{{ safeCounts.contracts ?? 0 }}</p>
                  <div class="flex items-center space-x-1 mt-3">
                    <FileText class="w-4 h-4 text-cyan-200" />
                    <span class="text-sm text-cyan-100">Active deals</span>
                  </div>
                </div>
                <div class="w-16 h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                  <FileText class="w-8 h-8 text-white" />
                </div>
              </div>
            </div>

            <!-- Proposals Card -->
            <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
              <div class="absolute top-0 right-0 w-32 h-32 bg-cyan-400/30 rounded-full -translate-y-16 translate-x-16"></div>
              <div class="absolute bottom-0 left-0 w-24 h-24 bg-cyan-300/20 rounded-full translate-y-12 -translate-x-12"></div>
              
              <div class="relative z-10 flex items-center justify-between">
                <div>
                  <h2 class="text-lg font-semibold text-white">Proposals</h2>
                  <p class="text-3xl font-bold text-white mt-2">{{ safeCounts.proposals ?? 0 }}</p>
                  <div class="flex items-center space-x-1 mt-3">
                    <Send class="w-4 h-4 text-cyan-200" />
                    <span class="text-sm text-cyan-100">In progress</span>
                  </div>
                </div>
                <div class="w-16 h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                  <Send class="w-8 h-8 text-white" />
                </div>
              </div>
            </div>
          </div>

          <!-- Chart + Top Products Section -->
          <div v-if="showCounts" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Opportunities Chart -->
            <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl border border-blue-200/50 p-6 shadow-lg hover:shadow-xl transition-all duration-500">
              <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-cyan-500 rounded-t-2xl"></div>
              <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-xl flex items-center justify-center">
                  <BarChart3 class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h2 class="font-bold text-slate-900 text-lg">Opportunities Overview</h2>
                  <p class="text-slate-600 text-sm">Last 7 days performance</p>
                </div>
              </div>
              
              <Barchart :chartData="safeChartData" />
            </div>

            <!-- Top Products Table -->
            <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl border border-cyan-200/50 p-6 shadow-lg hover:shadow-xl transition-all duration-500">
              <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-t-2xl"></div>
              <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center">
                  <Package class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h2 class="font-bold text-slate-900 text-lg">Top Products</h2>
                  <p class="text-slate-600 text-sm">Best performing items</p>
                </div>
              </div>

              <div class="space-y-3">
                <div 
                  v-for="(product, index) in topProductsSafe" 
                  :key="product.id || index"
                  class="flex items-center justify-between p-4 bg-gradient-to-r from-slate-50 to-white rounded-xl border border-slate-200/50 hover:border-cyan-300/50 transition-all duration-300 group/item hover:scale-105"
                >
                  <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                      {{ index + 1 }}
                    </div>
                    <div>
                      <div class="font-semibold text-slate-900">{{ product.name }}</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="font-bold text-slate-900">${{ product.sales }}</div>
                    <div class="text-xs text-blue-600 font-medium">Sales</div>
                  </div>
                </div>
                
                <div v-if="topProductsSafe.length === 0" class="text-center py-8 text-slate-500">
                  <Package class="w-12 h-12 mx-auto mb-3 text-slate-300" />
                  <p>No product data available</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Customer-only view -->
          <div v-else class="relative bg-gradient-to-br from-white to-blue-50 rounded-3xl shadow-xl p-12 text-center border border-blue-200/50 overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-cyan-500"></div>
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-100 rounded-full opacity-20"></div>
            <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-cyan-100 rounded-full opacity-20"></div>
            
            <div class="relative z-10">
              <div class="w-24 h-24 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                <FileText class="w-10 h-10 text-white" />
              </div>
              <h2 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent mb-4">
                Welcome to Your CRM Portal
              </h2>
              <p class="text-slate-600 text-lg mb-8 max-w-md mx-auto">
                Access your proposals and contracts in one place
              </p>
              <div class="flex justify-center gap-4">
                <a href="/admin/proposals" class="bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white px-8 py-4 rounded-2xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 flex items-center space-x-3">
                  <FileText class="w-5 h-5" />
                  <span>View Proposals</span>
                </a>
                <a href="/admin/contracts" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white px-8 py-4 rounded-2xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 flex items-center space-x-3">
                  <Briefcase class="w-5 h-5" />
                  <span>View Contracts</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import Sidebar from './Sidebar.vue'
import Barchart from './Barchart.vue'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { 
  Package, Users, Briefcase, FileText, BarChart3, Bell, CheckCircle, 
  XCircle, MessageSquare, Send, TrendingUp, Globe 
} from 'lucide-vue-next'

// Props from Inertia
const props = defineProps({
  user: Object,
  tables: Array,
  counts: Object,
  chartData: Object,
  topProducts: Array,
  notifications: Array,
  proposalStats: Object,
  userRole: String
})

// Safe flash messages with proper error handling
const page = usePage()
const flashSuccess = computed(() => {
  return page.props.flash?.success || null
})

// Role detection with fallback
const userRole = computed(() => {
  return props.userRole || 
         (props.user?.roles?.length ? props.user.roles[0].name : null) || 
         'user'
})

const showCounts = computed(() => !['customer'].includes(userRole.value))

// Safe chartData with proper fallbacks
const safeChartData = computed(() => {
  const data = props.chartData || {}
  return {
    labels: data.labels || [],
    datasets: data.datasets?.map(dataset => ({
      label: dataset.label || 'Opportunities Created (Last 7 Days)',
      backgroundColor: dataset.backgroundColor || '#3B82F6',
      borderColor: dataset.borderColor || '#1D4ED8',
      data: dataset.data || [],
      borderWidth: 2
    })) || [{
      label: 'Opportunities Created (Last 7 Days)',
      backgroundColor: '#3B82F6',
      borderColor: '#1D4ED8',
      data: [],
      borderWidth: 2
    }]
  }
})

// Check if chart has data
const hasChartData = computed(() => {
  const data = safeChartData.value
  return data.labels && data.labels.length > 0 && 
         data.datasets && data.datasets.length > 0 && 
         data.datasets[0].data && data.datasets[0].data.length > 0 &&
         data.datasets[0].data.some(value => value > 0)
})

// Notification type icons and colors
const getNotificationIcon = (type) => {
  switch (type) {
    case 'success': return { icon: CheckCircle, color: 'text-blue-500' }
    case 'error': return { icon: XCircle, color: 'text-blue-500' }
    case 'warning': return { icon: MessageSquare, color: 'text-cyan-500' }
    default: return { icon: Send, color: 'text-blue-500' }
  }
}

// Safe top products
const topProductsSafe = computed(() =>
  Array.isArray(props.topProducts)
    ? props.topProducts.map(p => ({ ...p, sales: p.sales ?? 'N/A' }))
    : []
)

// Safe counts
const safeCounts = computed(() => props.counts || {})

// Safe proposal stats
const safeProposalStats = computed(() => props.proposalStats || {})

// Enhanced notifications with fallbacks
const enhancedNotifications = computed(() => {
  if (!Array.isArray(props.notifications)) {
    return []
  }
  
  return props.notifications.map(notification => ({
    id: notification.id || Math.random().toString(36),
    message: notification.message || 'No message available',
    proposal_id: notification.proposal_id || null,
    proposal_title: notification.proposal_title || 'Unknown Proposal',
    action: notification.action || 'unknown',
    actor_name: notification.actor_name || 'Unknown User',
    actor_role: notification.actor_role || null,
    type: notification.type || 'info',
    url: notification.url || '#',
    created_at: notification.created_at || 'Just now',
  }))
})

// Notifications dropdown
const showNotifications = ref(false)
const toggleNotifications = () => (showNotifications.value = !showNotifications.value)

// Close dropdown when clicked outside
const handleClickOutside = (event) => {
  if (
    !event.target.closest('#notificationDropdown') &&
    !event.target.closest('#notificationButton')
  ) {
    showNotifications.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Mark all notifications as read
const markAllAsRead = async () => {
  try {
    await axios.post('/notifications/mark-as-read')
    window.location.reload()
  } catch (error) {
    console.error('Error marking notifications as read:', error)
  }
}

// Mark single notification as read
const markAsRead = async (notificationId) => {
  try {
    await axios.post(`/notifications/${notificationId}/mark-as-read`)
    window.location.reload()
  } catch (error) {
    console.error('Error marking notification as read:', error)
  }
}

// Get badge color based on notification type
const getBadgeColor = (type) => {
  switch (type) {
    case 'success': return 'bg-blue-100 text-blue-800'
    case 'error': return 'bg-blue-100 text-blue-800'
    case 'warning': return 'bg-cyan-100 text-cyan-800'
    default: return 'bg-blue-100 text-blue-800'
  }
}
</script>