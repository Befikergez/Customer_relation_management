<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-teal-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-lg border-b border-blue-100/50 px-6 py-6 shadow-sm">
          <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
              <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">Users</h1>
                <p class="text-slate-600 mt-2">Manage your users and their roles</p>
              </div>
              <button 
                @click="$inertia.visit('/admin/users/create')"
                class="bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-700 hover:to-teal-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
              >
                <PlusIcon class="w-5 h-5" />
                <span>Add New User</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-7xl mx-auto">
            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl mb-6 shadow-sm">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                  <CheckCircleIcon class="w-4 h-4 text-green-600" />
                </div>
                {{ $page.props.flash.success }}
              </div>
            </div>
            <div v-if="$page.props.flash && $page.props.flash.error" class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl mb-6 shadow-sm">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                  <XCircleIcon class="w-4 h-4 text-red-600" />
                </div>
                {{ $page.props.flash.error }}
              </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
              <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-teal-50">
                <h3 class="text-lg font-semibold text-slate-800">User List</h3>
                <p class="text-slate-600 text-sm mt-1">
                  Showing {{ users.meta?.from || 0 }} to {{ users.meta?.to || 0 }} of {{ users.meta?.total || 0 }} users
                </p>
              </div>
              
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-slate-50">
                    <tr>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">User</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Roles</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Commission</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Status</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Created At</th>
                      <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    <tr 
                      v-for="user in users.data" 
                      :key="user.id" 
                      class="hover:bg-blue-50/50 transition-all duration-200"
                    >
                      <td class="px-6 py-4">
                        <div class="flex items-center">
                          <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center mr-4">
                            <UserIcon class="w-5 h-5 text-white" />
                          </div>
                          <div>
                            <div class="font-semibold text-slate-900">{{ user.name }}</div>
                            <div class="text-sm text-slate-600">{{ user.email }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-2">
                          <span
                            v-for="role in user.roles"
                            :key="role.id"
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                            :class="getRoleBadgeClass(role.name)"
                          >
                            {{ role.name }}
                          </span>
                          <span v-if="!user.roles || user.roles.length === 0" class="text-slate-400 text-sm italic">No roles</span>
                        </div>
                      </td>
                      <td class="px-6 py-4">
                        <div v-if="hasCommissionRole(user)" class="flex items-center">
                          <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-amber-200 rounded-lg px-3 py-1.5">
                            <span class="text-amber-700 font-bold text-sm">{{ user.commission_rate || 0 }}%</span>
                          </div>
                        </div>
                        <div v-else class="text-slate-400 text-sm italic">N/A</div>
                      </td>
                      <td class="px-6 py-4">
                        <span 
                          class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                          :class="user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                        >
                          {{ user.status || 'inactive' }}
                        </span>
                      </td>
                      <td class="px-6 py-4 text-sm text-slate-600">
                        {{ formatDate(user.created_at) }}
                      </td>
                      <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                          <button 
                            @click="$inertia.visit(`/admin/users/${user.id}/edit`)"
                            class="bg-blue-600 hover:bg-blue-700 text-white p-2 rounded-lg font-medium transition-all duration-200 flex items-center justify-center"
                            title="Edit User"
                          >
                            <PencilIcon class="w-4 h-4" />
                          </button>
                          <button 
                            @click="deleteUser(user.id)"
                            class="bg-red-600 hover:bg-red-700 text-white p-2 rounded-lg font-medium transition-all duration-200 flex items-center justify-center"
                            title="Delete User"
                          >
                            <TrashIcon class="w-4 h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Empty State -->
              <div v-if="!users.data || users.data.length === 0" class="text-center py-12">
                <UserIcon class="w-16 h-16 text-slate-400 mx-auto mb-4" />
                <h3 class="text-lg font-semibold text-slate-800 mb-2">No users found</h3>
                <p class="text-slate-600 mb-6">Get started by creating your first user.</p>
                <button 
                  @click="$inertia.visit('/admin/users/create')"
                  class="bg-gradient-to-r from-blue-600 to-teal-600 text-white px-6 py-3 rounded-lg font-semibold hover:from-blue-700 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl"
                >
                  Add Your First User
                </button>
              </div>

              <!-- Pagination -->
              <div v-if="users.links && users.links.length > 3" class="px-6 py-4 bg-white border-t border-slate-100 flex justify-between items-center">
                <div class="text-sm text-slate-700">
                  Page {{ users.meta?.current_page || 1 }} of {{ users.meta?.last_page || 1 }}
                </div>
                <div class="flex space-x-2">
                  <button 
                    v-for="link in users.links"
                    :key="link.label"
                    :disabled="!link.url"
                    @click="visitPage(link.url)"
                    class="px-3 py-1.5 text-sm border rounded-lg transition-all duration-200"
                    :class="{
                      'bg-gradient-to-r from-blue-600 to-teal-600 text-white border-transparent shadow-lg': link.active,
                      'text-slate-600 border-slate-300 hover:bg-slate-50 hover:border-blue-300': !link.active && link.url,
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
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  UserIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  users: {
    type: Object,
    default: () => ({ data: [] })
  },
  tables: {
    type: Array,
    default: () => []
  },
  flash: {
    type: Object,
    default: () => ({})
  }
})

// Helper methods
const formatDate = (dateString) => {
  if (!dateString) return 'Unknown date'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Check if user has any commission-enabled role
const hasCommissionRole = (user) => {
  return user.commission_rate !== null && user.commission_rate !== undefined
}

// Get badge class based on role
const getRoleBadgeClass = (roleName) => {
  const classes = {
    'admin': 'bg-red-100 text-red-800',
    'salesperson': 'bg-orange-100 text-orange-800',
    'sales-manager': 'bg-purple-100 text-purple-800',
    'manager': 'bg-purple-100 text-purple-800',
    'user': 'bg-blue-100 text-blue-800'
  }
  return classes[roleName] || 'bg-gray-100 text-gray-800'
}

// Navigation methods
const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

// Delete user method
const deleteUser = (id) => {
  if (confirm('Are you sure you want to delete this user?')) {
    router.delete(`/admin/users/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
        // Success handled by controller
      },
      onError: () => {
        alert('Failed to delete user. Please try again.')
      }
    })
  }
}
</script>