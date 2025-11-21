<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content -->
    <div class="flex-1">
      <div class="min-h-screen bg-gradient-to-br from-blue-50 to-white">
        <!-- Header -->
        <div class="bg-white border-b border-blue-100 px-6 py-4 shadow-sm">
          <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
              <div>
                <h1 class="text-2xl font-bold text-black">Create Role</h1>
                <p class="text-gray-600 mt-1">Add a new role to the system</p>
              </div>
              <Link
                href="/admin/roles"
                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200 shadow-md flex items-center"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Roles
              </Link>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-4xl mx-auto">
            <RoleForm :permissions="permissions" />
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
const permissions = computed(() => page.props.permissions || [])
const tables = computed(() => page.props.tables || [])
</script>