<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import {
  UserCircle2,
  Briefcase,
  XCircle,
  FileText,
  FileSignature,
  LogOut,
  LayoutDashboard,
  Settings,
  ChevronLeft,
  ChevronRight
} from 'lucide-vue-next'
import { ref, computed } from 'vue'

defineProps({
  tables: {
    type: Array,
    default: () => []
  }
})

const page = usePage()
const isCollapsed = ref(false)

const logout = () => {
  router.post('/logout')
}

// Create URL for each table
const getUrl = (table) => {
  if (table.url && table.url !== '/dashboard') {
    return table.url
  }

  const tableName = table.name.toLowerCase().replace(/\s+/g, '-')
  return `/admin/${tableName}`
}

// Check active state
const isActive = (table) => {
  const url = getUrl(table)
  if (!url || url === '#') return false
  const currentPath = page.url
  return currentPath.startsWith(url)
}

// Check if dashboard is active
const isDashboardActive = computed(() => {
  return page.url === '/dashboard'
})

// Check if settings is active
const isSettingsActive = () => {
  return page.url.startsWith('/admin/settings')
}

// Icons for navigation items
const getIcon = (name) => {
  switch (name) {
    case 'Dashboard': return LayoutDashboard
    case 'Customers': return UserCircle2
    case 'Opportunities': return Briefcase
    case 'Potential Customers': return UserCircle2
    case 'Rejected Opportunities': return XCircle
    case 'Contracts': return FileText
    case 'Proposals': return FileSignature
    case 'Settings': return Settings
    default: return FileText
  }
}

// Filter out tables that are included in settings
const getFilteredTables = (tables) => {
  if (!Array.isArray(tables)) {
    return []
  }
  
  const settingsTables = ['Users', 'Roles', 'Products', 'Product Categories', 'Industries']
  return tables.filter(table => !settingsTables.includes(table.name))
}

// Define the navigation items in exact order
const getNavigationItems = (tables) => {
  const filteredTables = getFilteredTables(tables)
  
  // Start with Dashboard as the first item
  const navigationItems = [
    { name: 'Dashboard', url: '/dashboard', icon: LayoutDashboard }
  ]
  
  // Add the main tables in exact order
  const mainTablesOrder = [
    'Opportunities',
    'Potential Customers', 
    'Customers',
    'Rejected Opportunities'
  ]
  
  mainTablesOrder.forEach(tableName => {
    const table = filteredTables.find(t => t.name === tableName)
    if (table) {
      navigationItems.push(table)
    }
  })
  
  // End with Settings as the last item
  navigationItems.push({ name: 'Settings', url: '/admin/settings', icon: Settings })
  
  return navigationItems
}

// Toggle sidebar collapse
const toggleSidebar = () => {
  console.log('Toggling sidebar from:', isCollapsed.value, 'to:', !isCollapsed.value)
  isCollapsed.value = !isCollapsed.value
}

// Sidebar width classes
const sidebarWidth = computed(() => {
  return isCollapsed.value ? 'w-20' : 'w-64'
})
</script>

<template>
  <!-- Sidebar -->
  <aside 
    class="bg-gradient-to-b from-blue-900 to-blue-800 text-white min-h-screen flex flex-col transition-all duration-300 ease-in-out relative"
    :class="sidebarWidth"
  >
    
    <!-- Logo Section with Toggle Button -->
    <div class="py-6 border-b border-blue-700 px-4 relative">
      <!-- Logo -->
      <div class="flex items-center justify-center mb-4">
        <img 
          src="/omislogo.jpg" 
          alt="Logo" 
          class="w-12 h-12 rounded-full border-2 border-blue-300 transition-all duration-300"
        />
        <h1 
          v-if="!isCollapsed"
          class="text-xl font-bold text-white ml-3 transition-all duration-300"
        >
          CRM System
        </h1>
      </div>

      <!-- Toggle Button - Below Logo -->
      <button
        @click="toggleSidebar"
        class="absolute -right-3 bottom-2 bg-blue-700 hover:bg-blue-600 text-white p-2 rounded-full border-2 border-blue-800 z-10 transition-all duration-200 hover:scale-110 shadow-lg flex items-center justify-center"
      >
        <ChevronLeft v-if="!isCollapsed" class="w-4 h-4" />
        <ChevronRight v-else class="w-4 h-4" />
      </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 overflow-y-auto">
      <h2 
        v-if="!isCollapsed"
        class="text-sm font-semibold mb-4 text-blue-200 uppercase tracking-wider transition-all duration-300"
      >
        Navigation
      </h2>

      <!-- Navigation Items -->
      <div class="space-y-2">
        <Link 
          v-for="item in getNavigationItems(tables)"
          :key="item.name"
          :href="item.url || getUrl(item)"
          class="flex items-center gap-3 py-3 rounded-xl transition-all duration-200 group relative"
          :class="[
            (item.name === 'Dashboard' && isDashboardActive) || 
            (item.name === 'Settings' && isSettingsActive()) || 
            (item.name !== 'Dashboard' && item.name !== 'Settings' && isActive(item))
              ? 'bg-blue-700 text-white font-semibold shadow-md'
              : 'hover:bg-blue-600 hover:text-white hover:shadow-md',
            isCollapsed ? 'px-3 justify-center' : 'px-4'
          ]"
          :title="isCollapsed ? item.name : ''"
        >
          <!-- Active indicator dot for collapsed state -->
          <div 
            v-if="isCollapsed && ((item.name === 'Dashboard' && isDashboardActive) || (item.name === 'Settings' && isSettingsActive()) || (item.name !== 'Dashboard' && item.name !== 'Settings' && isActive(item)))"
            class="absolute left-1 w-1.5 h-1.5 bg-white rounded-full"
          ></div>

          <component 
            :is="item.icon || getIcon(item.name)" 
            class="w-5 h-5 flex-shrink-0 transition-all duration-300"
          />
          
          <span 
            v-if="!isCollapsed"
            class="transition-all duration-300 whitespace-nowrap"
          >
            {{ item.name }}
          </span>

          <!-- Tooltip for collapsed state -->
          <div 
            v-if="isCollapsed"
            class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50 pointer-events-none"
          >
            {{ item.name }}
          </div>
        </Link>
      </div>
    </nav>

    <!-- Logout -->
    <div class="p-4 border-t border-blue-700">
      <button
        @click="logout"
        class="flex items-center justify-center gap-2 w-full bg-blue-600 hover:bg-blue-500 text-white font-semibold py-3 rounded-xl transition-all duration-200 group relative"
        :class="isCollapsed ? 'px-3' : 'px-4'"
        :title="isCollapsed ? 'Logout' : ''"
      >
        <LogOut class="w-5 h-5 flex-shrink-0" />
        
        <span v-if="!isCollapsed" class="transition-all duration-300">
          Logout
        </span>

        <!-- Tooltip for collapsed state -->
        <div 
          v-if="isCollapsed"
          class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50 pointer-events-none"
        >
          Logout
        </div>
      </button>
    </div>
  </aside>
</template>

<style scoped>
aside {
  font-family: 'Inter', sans-serif;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 4px;
}
::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
}

/* Smooth transitions */
* {
  transition-property: all;
  transition-duration: 300ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>