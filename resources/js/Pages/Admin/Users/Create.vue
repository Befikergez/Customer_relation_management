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
                Create New User
              </h1>
              <p class="text-gray-600 font-medium">Add a new user to the system with appropriate roles and permissions</p>
            </div>
            <Link
              href="/admin/users"
              class="group relative bg-white text-gray-700 px-6 py-3.5 rounded-xl hover:bg-gray-50 transition-all duration-300 shadow-sm border border-gray-300/50 hover:shadow-md flex items-center font-semibold"
            >
              <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Back to Users
            </Link>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 p-8">
        <div class="max-w-6xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2">
              <UserForm :roles="roles" />
            </div>
            
            <!-- Help Card -->
            <div class="space-y-6">
              <!-- Tips Card -->
              <div class="bg-white rounded-2xl shadow-sm border border-blue-200/50 p-6 backdrop-blur-sm">
                <div class="flex items-center space-x-3 mb-4">
                  <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-teal-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                  </div>
                  <h3 class="text-lg font-semibold text-gray-800">Quick Tips</h3>
                </div>
                <ul class="space-y-3 text-sm text-gray-700">
                  <li class="flex items-start space-x-2">
                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></div>
                    <span>Use a valid email address for account verification</span>
                  </li>
                  <li class="flex items-start space-x-2">
                    <div class="w-1.5 h-1.5 bg-teal-500 rounded-full mt-1.5 flex-shrink-0"></div>
                    <span>Assign roles based on the user's responsibilities</span>
                  </li>
                  <li class="flex items-start space-x-2">
                    <div class="w-1.5 h-1.5 bg-rose-500 rounded-full mt-1.5 flex-shrink-0"></div>
                    <span>Strong passwords are automatically enforced</span>
                  </li>
                </ul>
              </div>
              
              <!-- Role Guide -->
              <div class="bg-white rounded-2xl shadow-sm border border-rose-100/50 p-6 backdrop-blur-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">About Roles</h3>
                <div class="space-y-4">
                  <div v-for="role in roles" :key="role.id" class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-rose-500 rounded-full mt-2 flex-shrink-0"></div>
                    <div>
                      <h4 class="font-semibold text-gray-800 text-sm">{{ role.name }}</h4>
                      <p class="text-gray-600 text-xs mt-1">Provides access to {{ role.name.toLowerCase() }} features and modules</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import UserForm from './Form.vue'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const roles = computed(() => page.props.roles || [])
const tables = computed(() => page.props.tables || [])
</script>