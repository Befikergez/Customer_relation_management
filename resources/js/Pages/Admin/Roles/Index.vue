<template>
  <div class="min-h-screen bg-slate-50 flex">
    <!-- Sidebar -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content -->
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-teal-50">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-lg border-b border-blue-100/50 px-6 py-6 shadow-sm">
          <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
              <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">Roles Management</h1>
                <p class="text-slate-600 mt-2 flex items-center">
                  <span class="w-2 h-2 bg-rose-500 rounded-full mr-2 animate-pulse"></span>
                  Manage system roles and permissions
                </p>
              </div>
              <Link
                href="/admin/roles/create"
                class="group bg-gradient-to-r from-blue-600 to-teal-600 text-white px-6 py-3.5 rounded-xl hover:from-blue-700 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center transform hover:-translate-y-0.5"
              >
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                </div>
                Create Role
              </Link>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-7xl mx-auto">
            <!-- Stats Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
              <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-blue-100/50 shadow-sm">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-slate-600 text-sm font-medium">Total Roles</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ roles.length }}</p>
                  </div>
                  <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <Shield class="w-6 h-6 text-blue-600" />
                  </div>
                </div>
              </div>
              
              <div class="bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-teal-100/50 shadow-sm">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-slate-600 text-sm font-medium">Active Permissions</p>
                    <p class="text-2xl font-bold text-slate-900 mt-1">{{ totalPermissions }}</p>
                  </div>
                  <div class="w-12 h-12 bg-teal-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                  </div>
                </div>
              </div>
            </div>

            <!-- Roles Table -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100/50 overflow-hidden">
              <div class="px-6 py-4 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Role List</h3>
              </div>
              
              <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-gradient-to-r from-blue-50 to-teal-50">
                  <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Role Details</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Permissions</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                  <tr 
                    v-for="role in roles" 
                    :key="role.id" 
                    class="hover:bg-blue-50/50 transition-all duration-200 group"
                  >
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                          <Shield class="w-5 h-5 text-white" />
                        </div>
                        <div>
                          <span class="text-sm font-semibold text-slate-900">{{ role.name }}</span>
                          <p class="text-xs text-slate-500 mt-1">Created {{ formatDate(role.created_at) }}</p>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex flex-wrap gap-2">
                        <span
                          v-for="permission in role.permissions?.slice(0, 3)"
                          :key="permission.id"
                          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800 border border-teal-200"
                        >
                          {{ permission.name }}
                        </span>
                        <span v-if="role.permissions?.length > 3" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                          +{{ role.permissions.length - 3 }} more
                        </span>
                        <span v-if="!role.permissions || role.permissions.length === 0" class="text-slate-400 text-sm">
                          No permissions
                        </span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex space-x-2">
                        <Link
                          :href="`/admin/roles/${role.id}`"
                          class="p-2 text-slate-600 hover:text-teal-600 hover:bg-teal-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                          title="View Role"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                          </svg>
                        </Link>
                        <Link
                          :href="`/admin/roles/${role.id}/edit`"
                          class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                          title="Edit Role"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                          </svg>
                        </Link>
                        <button
                          @click="deleteRole(role.id)"
                          class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                          title="Delete Role"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
              
              <!-- Empty State -->
              <div v-if="roles.length === 0" class="text-center py-16">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                  <Shield class="w-10 h-10 text-blue-600" />
                </div>
                <h3 class="text-xl font-semibold text-slate-900 mb-3">No roles found</h3>
                <p class="text-slate-600 mb-8 max-w-md mx-auto">Get started by creating your first role to manage system permissions and access controls.</p>
                <Link
                  href="/admin/roles/create"
                  class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-teal-600 text-white rounded-xl hover:from-blue-700 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                  Create Your First Role
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { Shield } from 'lucide-vue-next'
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const roles = computed(() => page.props.roles || [])
const tables = computed(() => page.props.tables || [])

const totalPermissions = computed(() => {
  return roles.value.reduce((total, role) => total + (role.permissions?.length || 0), 0)
})

function deleteRole(id) {
  if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
    router.delete(`/admin/roles/${id}`)
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>