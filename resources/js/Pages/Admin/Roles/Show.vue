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
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">Role Details</h1>
                <p class="text-slate-600 mt-2 flex items-center">
                  <span class="w-2 h-2 bg-rose-500 rounded-full mr-2 animate-pulse"></span>
                  View role information and permissions
                </p>
              </div>
              <Link
                href="/admin/roles"
                class="group bg-gradient-to-r from-blue-600 to-teal-600 text-white px-6 py-3.5 rounded-xl hover:from-blue-700 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center transform hover:-translate-y-0.5"
              >
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                  </svg>
                </div>
                Back to Roles
              </Link>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-6xl mx-auto">
            <!-- Role Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100/50 p-8 mb-8">
              <!-- Role Header -->
              <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-6">
                  <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <Shield class="w-10 h-10 text-white" />
                  </div>
                  <div>
                    <h2 class="text-3xl font-bold text-slate-900">{{ role.name }}</h2>
                    <p class="text-slate-600 mt-2">Role Information & Permissions</p>
                  </div>
                </div>
                <div class="flex items-center space-x-3">
                  <span class="px-4 py-2 bg-blue-100 text-blue-800 rounded-full text-sm font-medium border border-blue-200">
                    {{ role.permissions?.length || 0 }} Permissions
                  </span>
                </div>
              </div>

              <!-- Role Details Grid -->
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Role Information -->
                <div class="bg-gradient-to-br from-blue-50 to-teal-50 rounded-2xl p-6 border border-blue-200/50">
                  <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Role Information
                  </h3>
                  <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-blue-100">
                      <span class="text-slate-600">Role Name:</span>
                      <span class="font-semibold text-slate-900">{{ role.name }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-blue-100">
                      <span class="text-slate-600">Total Permissions:</span>
                      <span class="font-semibold text-slate-900">{{ role.permissions?.length || 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                      <span class="text-slate-600">Created Date:</span>
                      <span class="font-semibold text-slate-900">{{ formatDate(role.created_at) }}</span>
                    </div>
                  </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-2xl p-6 border border-rose-200/50">
                  <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-rose-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Quick Actions
                  </h3>
                  <div class="space-y-3">
                    <Link
                      :href="`/admin/roles/${role.id}/edit`"
                      class="w-full bg-gradient-to-r from-blue-600 to-teal-600 text-white px-4 py-3 rounded-xl hover:from-blue-700 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl text-center font-semibold flex items-center justify-center transform hover:-translate-y-0.5"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                      Edit Role
                    </Link>
                    <button
                      @click="deleteRole(role.id)"
                      class="w-full bg-white text-rose-600 border border-rose-200 px-4 py-3 rounded-xl hover:bg-rose-50 transition-all duration-300 text-center font-semibold flex items-center justify-center transform hover:-translate-y-0.5"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                      Delete Role
                    </button>
                  </div>
                </div>

                <!-- Permission Summary -->
                <div class="bg-gradient-to-br from-teal-50 to-emerald-50 rounded-2xl p-6 border border-teal-200/50">
                  <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <svg class="w-5 h-5 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Permission Summary
                  </h3>
                  <div class="text-center py-4">
                    <div class="text-4xl font-bold text-teal-600 mb-2">{{ role.permissions?.length || 0 }}</div>
                    <p class="text-slate-600">Total Permissions</p>
                  </div>
                </div>
              </div>

              <!-- Permissions Section -->
              <div>
                <div class="flex items-center justify-between mb-6">
                  <h3 class="text-xl font-semibold text-slate-800">Role Permissions</h3>
                  <span class="px-4 py-2 bg-teal-100 text-teal-800 rounded-full text-sm font-medium border border-teal-200">
                    {{ role.permissions?.length || 0 }} permissions assigned
                  </span>
                </div>
                
                <div v-if="role.permissions && role.permissions.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div
                    v-for="permission in role.permissions"
                    :key="permission.id"
                    class="group bg-white border border-teal-200 rounded-2xl p-4 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                  >
                    <div class="flex items-center justify-between">
                      <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-teal-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                          <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                          </svg>
                        </div>
                        <span class="text-slate-800 font-semibold">{{ permission.name }}</span>
                      </div>
                      <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                      </svg>
                    </div>
                  </div>
                </div>
                
                <div v-else class="text-center py-12 bg-gradient-to-br from-slate-50 to-gray-100 rounded-2xl border border-slate-200">
                  <div class="w-16 h-16 bg-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                  </div>
                  <h4 class="text-xl font-semibold text-slate-900 mb-3">No Permissions Assigned</h4>
                  <p class="text-slate-600 mb-6 max-w-md mx-auto">This role doesn't have any permissions assigned yet. Add permissions to define what actions this role can perform.</p>
                  <Link
                    :href="`/admin/roles/${role.id}/edit`"
                    class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-teal-600 text-white rounded-xl hover:from-blue-700 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                  >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Add Permissions
                  </Link>
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
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { Shield } from 'lucide-vue-next'
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const role = computed(() => page.props.role)
const tables = computed(() => page.props.tables || [])

function deleteRole(id) {
  if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
    router.delete(`/admin/roles/${id}`)
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script>