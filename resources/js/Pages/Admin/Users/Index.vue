<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-teal-50 flex">
    <!-- Sidebar -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <!-- Header -->
      <header class="bg-white/90 backdrop-blur-lg border-b border-gray-200/60 shadow-sm sticky top-0 z-10">
        <div class="px-8 py-6">
          <div class="flex justify-between items-center">
            <div class="space-y-1">
              <h1 class="text-3xl font-bold bg-gradient-to-r from-teal-700 via-blue-600 to-teal-600 bg-clip-text text-transparent">
                User Management
              </h1>
              <p class="text-gray-600 font-medium">Manage system users and their permissions</p>
            </div>
            <Link
              href="/admin/users/create"
              class="group relative bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3.5 rounded-xl hover:shadow-lg transition-all duration-300 shadow-md flex items-center font-semibold overflow-hidden border border-blue-500/20"
            >
              <div class="absolute inset-0 bg-gradient-to-r from-blue-700 to-blue-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
              <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              <span class="relative z-10">Create User</span>
            </Link>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 p-8">
        <div class="max-w-7xl mx-auto">
          <!-- Stats Overview -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-blue-100/50 backdrop-blur-sm">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-gray-600 text-sm font-medium">Total Users</p>
                  <p class="text-2xl font-bold text-gray-800 mt-1">{{ users.meta?.total || users.length }}</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-teal-100 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                  </svg>
                </div>
              </div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-rose-100/50 backdrop-blur-sm">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-gray-600 text-sm font-medium">Active Today</p>
                  <p class="text-2xl font-bold text-gray-800 mt-1">0</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-br from-rose-100 to-pink-100 rounded-xl flex items-center justify-center">
                  <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                </div>
              </div>
            </div>
          </div>

          <!-- Users Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
            <div
              v-for="user in (users.data || users)"
              :key="user.id"
              class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-200/50 hover:border-teal-300/50 overflow-hidden backdrop-blur-sm"
            >
              <!-- User Card Header -->
              <div class="p-6 border-b border-gray-200/50">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div class="relative">
                      <!-- User Image with Fallback - CORRECTED -->
                      <div v-if="user.profile_image_url" class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-teal-200 group-hover:border-blue-300 transition-all duration-300">
                        <img 
                          :src="user.profile_image_url" 
                          :alt="user.name"
                          class="w-full h-full object-cover"
                        />
                      </div>
                      <div v-else class="w-14 h-14 bg-gradient-to-br from-blue-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-md group-hover:from-blue-600 group-hover:to-teal-700 transition-all duration-300">
                        <span class="text-white font-bold text-lg">{{ user.name.charAt(0).toUpperCase() }}</span>
                      </div>
                      <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-400 border-2 border-white rounded-full"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h2 class="text-lg font-semibold text-gray-800 truncate">{{ user.name }}</h2>
                      <p class="text-gray-600 text-sm truncate mt-1">{{ user.email }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Roles Section -->
              <div class="p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                  <svg class="w-4 h-4 mr-2 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                  </svg>
                  Roles & Permissions
                </h3>
                <div class="flex flex-wrap gap-2">
                  <span
                    v-for="role in user.roles"
                    :key="role.id"
                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-50 to-teal-50 text-gray-700 border border-blue-200/50"
                  >
                    {{ role.name }}
                  </span>
                  <span v-if="!user.roles || user.roles.length === 0" class="text-gray-500 text-sm italic">No roles assigned</span>
                </div>
              </div>

              <!-- Actions -->
              <div class="px-6 pb-6">
                <div class="flex space-x-3">
                  <Link
                    :href="`/admin/users/${user.id}/edit`"
                    class="flex-1 group/edit bg-gradient-to-r from-blue-50 to-gray-50 hover:from-blue-100 hover:to-gray-100 text-gray-700 px-4 py-3 rounded-xl transition-all duration-300 text-center font-semibold flex items-center justify-center border border-gray-300/50 hover:border-blue-300"
                  >
                    <svg class="w-4 h-4 mr-2 text-blue-600 group-hover/edit:text-blue-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                  </Link>
                  <button
                    @click="deleteUser(user.id)"
                    class="flex-1 group/delete bg-gradient-to-r from-rose-50 to-pink-50 hover:from-rose-100 hover:to-pink-100 text-rose-700 px-4 py-3 rounded-xl transition-all duration-300 font-semibold flex items-center justify-center border border-rose-200/50 hover:border-rose-300"
                  >
                    <svg class="w-4 h-4 mr-2 text-rose-600 group-hover/delete:text-rose-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="users.meta" class="bg-white rounded-2xl shadow-sm border border-gray-200/50 p-6 backdrop-blur-sm">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-600">
                Showing {{ users.meta.from }} to {{ users.meta.to }} of {{ users.meta.total }} results
              </div>
              <div class="flex items-center space-x-2">
                <!-- Previous Page -->
                <Link
                  v-if="users.meta.current_page > 1"
                  :href="users.meta.prev_page_url"
                  class="px-4 py-2 bg-gradient-to-r from-blue-50 to-gray-50 text-gray-700 rounded-lg border border-gray-300/50 hover:from-blue-100 hover:to-gray-100 transition-all duration-300 flex items-center font-medium"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                  </svg>
                  Previous
                </Link>

                <!-- Page Numbers -->
                <div class="flex items-center space-x-1">
                  <Link
                    v-for="page in users.meta.links.slice(1, -1)"
                    :key="page.label"
                    :href="page.url"
                    class="px-3 py-2 rounded-lg border transition-all duration-300 font-medium min-w-[40px] text-center"
                    :class="{
                      'bg-gradient-to-r from-blue-600 to-blue-700 text-white border-blue-600 shadow-md': page.active,
                      'bg-white text-gray-700 border-gray-300/50 hover:bg-gray-50': !page.active
                    }"
                  >
                    {{ page.label }}
                  </Link>
                </div>

                <!-- Next Page -->
                <Link
                  v-if="users.meta.current_page < users.meta.last_page"
                  :href="users.meta.next_page_url"
                  class="px-4 py-2 bg-gradient-to-r from-blue-50 to-gray-50 text-gray-700 rounded-lg border border-gray-300/50 hover:from-blue-100 hover:to-gray-100 transition-all duration-300 flex items-center font-medium"
                >
                  Next
                  <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </Link>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="(users.data || users).length === 0" class="text-center py-16">
            <div class="max-w-md mx-auto">
              <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-teal-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-800 mb-2">No users found</h3>
              <p class="text-gray-600 mb-6">Get started by creating your first user account.</p>
              <Link
                href="/admin/users/create"
                class="inline-flex items-center px-6 py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 shadow-md border border-blue-500/20"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create First User
              </Link>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const users = computed(() => page.props.users || [])
const tables = computed(() => page.props.tables || [])

function deleteUser(id) {
  if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
    router.delete(`/admin/users/${id}`)
  }
}
</script>