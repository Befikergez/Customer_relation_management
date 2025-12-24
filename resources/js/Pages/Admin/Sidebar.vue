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
  ChevronRight,
  Search,
  Menu,
  X
} from 'lucide-vue-next'
import { ref, computed, onMounted, onUnmounted } from 'vue'

defineProps({
  tables: {
    type: Array,
    default: () => []
  }
})

const page = usePage()
const isCollapsed = ref(false)
const isMobileMenuOpen = ref(false)
const isMobile = ref(false)

// Check screen size
const checkScreenSize = () => {
  isMobile.value = window.innerWidth < 768
  console.log('Screen size checked, isMobile:', isMobile.value, 'width:', window.innerWidth)
  // On mobile, close menu automatically
  if (!isMobile.value) {
    isMobileMenuOpen.value = false
  }
}

// Toggle sidebar collapse
const toggleSidebar = () => {
  console.log('Toggle sidebar called, isMobile:', isMobile.value, 'current state:', isMobileMenuOpen.value)
  if (isMobile.value) {
    isMobileMenuOpen.value = !isMobileMenuOpen.value
  } else {
    isCollapsed.value = !isCollapsed.value
  }
}

// Close mobile menu
const closeMobileMenu = () => {
  if (isMobile.value && isMobileMenuOpen.value) {
    console.log('Closing mobile menu')
    isMobileMenuOpen.value = false
  }
}

// Handle navigation and close mobile menu
const handleNavigation = () => {
  if (isMobile.value) {
    // Close menu immediately when a link is clicked
    setTimeout(() => {
      closeMobileMenu()
    }, 50)
  }
}

// Initialize and add event listener
onMounted(() => {
  checkScreenSize()
  window.addEventListener('resize', checkScreenSize)
})

onUnmounted(() => {
  window.removeEventListener('resize', checkScreenSize)
})

const logout = () => {
  router.post('/logout')
  closeMobileMenu()
}

// Navigate to search page
const goToSearch = () => {
  router.get('/admin/search')
  closeMobileMenu()
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
  return page.url === '/dashboard' || page.url === '/' || page.url.startsWith('/dashboard')
})

// Check if search is active
const isSearchActive = computed(() => {
  return page.url === '/admin/search' || page.url.startsWith('/admin/search')
})

// Check if settings is active
const isSettingsActive = () => {
  return page.url.startsWith('/admin/settings')
}

// Icons for navigation items
const getIcon = (name) => {
  switch (name) {
    case 'Dashboard': return LayoutDashboard
    case 'Search': return Search
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
</script>

<template>
  <!-- Mobile Menu Button -->
  <button 
    v-if="isMobile"
    @click="toggleSidebar"
    id="mobile-menu-toggle" 
    class="mobile-menu-btn fixed top-4 left-4 z-50 bg-blue-600 hover:bg-blue-700 text-white p-3 rounded-lg shadow-lg transition-all duration-300"
  >
    <!-- Show Menu icon when sidebar is closed, X when open -->
    <Menu v-if="!isMobileMenuOpen" class="w-5 h-5" />
    <X v-else class="w-5 h-5" />
  </button>

  <!-- Mobile Overlay - Only show when mobile menu is open -->
  <div 
    v-if="isMobile && isMobileMenuOpen"
    @click="closeMobileMenu"
    class="fixed inset-0 bg-black/50 z-20"
  ></div>

  <!-- Sidebar -->
  <aside 
    class="bg-gradient-to-b from-blue-900 to-blue-800 text-white h-screen flex flex-col fixed left-0 top-0 transition-all duration-300 ease-in-out"
    :class="[
      isMobile 
        ? (isMobileMenuOpen ? 'translate-x-0 z-30 shadow-2xl' : '-translate-x-full z-30')
        : (isCollapsed ? 'w-20 z-30' : 'w-64 z-30')
    ]"
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
          v-if="!isCollapsed || isMobile"
          class="text-3xl font-bold text-white ml-3 transition-all duration-300"
        >
          CRM System
        </h1>
      </div>

      <!-- Toggle Button - Only show on desktop -->
      <button
        v-if="!isMobile"
        @click="toggleSidebar"
        class="absolute top-4 right-4 bg-blue-700 hover:bg-blue-600 text-white p-2 rounded-full border-2 border-blue-800 z-10 transition-all duration-200 hover:scale-110 shadow-lg flex items-center justify-center"
      >
        <ChevronLeft v-if="!isCollapsed" class="w-4 h-4" />
        <ChevronRight v-else class="w-4 h-4" />
      </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 overflow-y-auto">
      <h2 
        v-if="(!isCollapsed || isMobile)"
        class="text-base font-semibold mb-4 text-blue-200 uppercase tracking-wider transition-all duration-300"
      >
        Navigation
      </h2>

      <!-- Navigation Items -->
      <div class="space-y-2">
        <!-- Dashboard -->
        <Link 
          :href="'/dashboard'"
          class="flex items-center gap-3 py-3 rounded-xl transition-all duration-200 group relative"
          :class="[
            isDashboardActive
              ? 'bg-blue-700 text-white font-semibold shadow-md'
              : 'hover:bg-blue-600 hover:text-white hover:shadow-md',
            (isCollapsed && !isMobile) ? 'px-3 justify-center' : 'px-4'
          ]"
          :title="(isCollapsed && !isMobile) ? 'Dashboard' : ''"
          @click="handleNavigation"
        >
          <!-- Active indicator dot for collapsed state -->
          <div 
            v-if="(isCollapsed && !isMobile) && isDashboardActive"
            class="absolute left-1 w-1.5 h-1.5 bg-white rounded-full"
          ></div>

          <LayoutDashboard class="w-6 h-6 flex-shrink-0 transition-all duration-300" />
          
          <span 
            v-if="!isCollapsed || isMobile"
            class="transition-all duration-300 whitespace-nowrap text-lg font-medium"
          >
            Dashboard
          </span>

          <!-- Tooltip for collapsed state -->
          <div 
            v-if="(isCollapsed && !isMobile)"
            class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50 pointer-events-none"
          >
            Dashboard
          </div>
        </Link>

        <!-- Search Button - Below Dashboard -->
        <Link
          :href="'/admin/search'"
          class="flex items-center gap-3 py-3 rounded-xl transition-all duration-200 group relative"
          :class="[
            isSearchActive
              ? 'bg-blue-700 text-white font-semibold shadow-md'
              : 'hover:bg-blue-600 hover:text-white hover:shadow-md',
            (isCollapsed && !isMobile) ? 'px-3 justify-center' : 'px-4'
          ]"
          :title="(isCollapsed && !isMobile) ? 'Search' : ''"
          @click="handleNavigation"
        >
          <!-- Active indicator dot for collapsed state -->
          <div 
            v-if="(isCollapsed && !isMobile) && isSearchActive"
            class="absolute left-1 w-1.5 h-1.5 bg-white rounded-full"
          ></div>

          <Search class="w-6 h-6 flex-shrink-0 transition-all duration-300" />
          
          <span 
            v-if="!isCollapsed || isMobile"
            class="transition-all duration-300 whitespace-nowrap text-lg font-medium"
          >
            Search
          </span>

          <!-- Tooltip for collapsed state -->
          <div 
            v-if="(isCollapsed && !isMobile)"
            class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50 pointer-events-none"
          >
            Search
          </div>
        </Link>

        <!-- Other Navigation Items -->
        <Link 
          v-for="item in getNavigationItems(tables).filter(item => item.name !== 'Dashboard')"
          :key="item.name"
          :href="item.url || getUrl(item)"
          class="flex items-center gap-3 py-3 rounded-xl transition-all duration-200 group relative"
          :class="[
            (item.name === 'Settings' && isSettingsActive()) || 
            (item.name !== 'Settings' && isActive(item))
              ? 'bg-blue-700 text-white font-semibold shadow-md'
              : 'hover:bg-blue-600 hover:text-white hover:shadow-md',
            (isCollapsed && !isMobile) ? 'px-3 justify-center' : 'px-4'
          ]"
          :title="(isCollapsed && !isMobile) ? item.name : ''"
          @click="handleNavigation"
        >
          <!-- Active indicator dot for collapsed state -->
          <div 
            v-if="(isCollapsed && !isMobile) && ((item.name === 'Settings' && isSettingsActive()) || (item.name !== 'Settings' && isActive(item)))"
            class="absolute left-1 w-1.5 h-1.5 bg-white rounded-full"
          ></div>

          <component 
            :is="item.icon || getIcon(item.name)" 
            class="w-6 h-6 flex-shrink-0 transition-all duration-300"
          />
          
          <span 
            v-if="!isCollapsed || isMobile"
            class="transition-all duration-300 whitespace-nowrap text-lg font-medium"
          >
            {{ item.name }}
          </span>

          <!-- Tooltip for collapsed state -->
          <div 
            v-if="(isCollapsed && !isMobile)"
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
        :class="(isCollapsed && !isMobile) ? 'px-3' : 'px-4'"
        :title="(isCollapsed && !isMobile) ? 'Logout' : ''"
      >
        <LogOut class="w-6 h-6 flex-shrink-0" />
        
        <span v-if="!isCollapsed || isMobile" class="transition-all duration-300 text-lg font-medium">
          Logout
        </span>

        <!-- Tooltip for collapsed state -->
        <div 
          v-if="(isCollapsed && !isMobile)"
          class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50 pointer-events-none"
        >
          Logout
        </div>
      </button>
    </div>
  </aside>

  <!-- Main content spacer for desktop -->
  <div 
    v-if="!isMobile"
    class="transition-all duration-300"
    :class="isCollapsed ? 'w-20' : 'w-64'"
  ></div>
</template>

<style scoped>
aside {
  font-family: 'Inter', sans-serif;
  width: 256px; /* Default width for desktop */
}

/* Mobile specific styles */
@media (max-width: 767px) {
  aside {
    width: 256px;
    transform: translateX(-100%);
  }
  
  aside.translate-x-0 {
    transform: translateX(0);
    box-shadow: 0 0 30px rgba(0, 0, 0, 0.5);
  }
  
  aside.-translate-x-full {
    transform: translateX(-100%);
  }
}

/* Desktop specific styles */
@media (min-width: 768px) {
  aside.w-20 {
    width: 80px;
  }
  
  aside.w-64 {
    width: 256px;
  }
}

/* Custom scrollbar */
nav::-webkit-scrollbar {
  width: 6px;
}
nav::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 6px;
}

/* Smooth transitions */
aside, .mobile-menu-btn {
  transition-property: all;
  transition-duration: 300ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Mobile menu button styling */
.mobile-menu-btn {
  z-index: 1000;
}

/* Ensure proper layering */
aside {
  z-index: 30;
}

/* Ensure links are clickable */
a, button {
  cursor: pointer;
}
</style>