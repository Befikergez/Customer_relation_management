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
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">Edit Role</h1>
                <p class="text-slate-600 mt-2 flex items-center">
                  <span class="w-2 h-2 bg-rose-500 rounded-full mr-2 animate-pulse"></span>
                  Update role information and permissions
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
            <RoleForm
              :role="role"
              :permissions="permissions"
              :rolePermissions="rolePermissions"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import RoleForm from './Form.vue'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const role = page.props.role
const permissions = computed(() => page.props.permissions || [])
const rolePermissions = computed(() => page.props.rolePermissions || [])
const tables = computed(() => page.props.tables || [])
</script>