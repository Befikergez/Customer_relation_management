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
                Edit User
              </h1>
              <p class="text-gray-600 font-medium">Update user information and permissions for {{ user.name }}</p>
            </div>
            <div class="flex space-x-4">
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
          
          <!-- User Quick Stats -->
          <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl p-4 border border-blue-200/50 shadow-sm backdrop-blur-sm">
              <p class="text-sm text-gray-600 font-medium">Member Since</p>
              <p class="text-gray-800 font-semibold mt-1">{{ formatDate(user.created_at) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 border border-teal-200/50 shadow-sm backdrop-blur-sm">
              <p class="text-sm text-gray-600 font-medium">Last Updated</p>
              <p class="text-gray-800 font-semibold mt-1">{{ formatDate(user.updated_at) }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 border border-rose-200/50 shadow-sm backdrop-blur-sm">
              <p class="text-sm text-gray-600 font-medium">Assigned Roles</p>
              <p class="text-gray-800 font-semibold mt-1">{{ userRoles.length }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 border border-green-200/50 shadow-sm backdrop-blur-sm">
              <p class="text-sm text-gray-600 font-medium">Commission</p>
              <div class="flex items-center mt-1">
                <div v-if="user.commission_rate !== null" class="w-2 h-2 bg-amber-500 rounded-full mr-2"></div>
                <div v-else class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                <span class="text-gray-700 font-semibold">
                  {{ user.commission_rate !== null ? user.commission_rate + '%' : 'Not set' }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Content -->
      <main class="flex-1 p-8">
        <div class="max-w-6xl mx-auto">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Form -->
            <div class="lg:col-span-2">
              <UserForm
                :user="user"
                :roles="roles"
                :userRoles="userRoles"
              />
            </div>
            
            <!-- User Summary & Actions -->
            <div class="space-y-6">
              <!-- User Summary -->
              <div class="bg-white rounded-2xl shadow-sm border border-gray-200/50 p-6 backdrop-blur-sm">
                <div class="flex items-center space-x-4 mb-6">
                  <!-- User Image Display -->
                  <div class="relative">
                    <div v-if="user.profile_image" class="w-16 h-16 rounded-2xl overflow-hidden border-2 border-teal-200">
                      <img 
                        :src="user.profile_image" 
                        :alt="user.name"
                        class="w-full h-full object-cover"
                      />
                    </div>
                    <div v-else class="w-16 h-16 bg-gradient-to-br from-blue-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-md border-2 border-teal-200">
                      <span class="text-white font-bold text-xl">{{ user.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-400 border-2 border-white rounded-full"></div>
                  </div>
                  <div>
                    <h3 class="text-lg font-bold text-gray-800">{{ user.name }}</h3>
                    <p class="text-gray-600 text-sm">{{ user.email }}</p>
                  </div>
                </div>
                
                <div class="space-y-4">
                  <div>
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Current Roles</h4>
                    <div class="flex flex-wrap gap-2">
                      <span
                        v-for="role in userRoles"
                        :key="role.id"
                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-50 to-teal-50 text-gray-700 border border-blue-200/50"
                      >
                        {{ role.name }}
                      </span>
                      <span v-if="userRoles.length === 0" class="text-gray-500 text-sm italic">No roles assigned</span>
                    </div>
                  </div>

                  <!-- Commission Display -->
                  <div v-if="user.commission_rate !== null">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Commission Rate</h4>
                    <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-amber-200 rounded-lg px-4 py-2">
                      <span class="text-amber-700 font-bold">{{ user.commission_rate }}%</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Quick Actions -->
              <div class="bg-white rounded-2xl shadow-sm border border-rose-100/50 p-6 backdrop-blur-sm">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                  <button class="w-full flex items-center space-x-3 p-3 rounded-xl border border-gray-200/50 hover:bg-blue-50 transition-colors duration-200 group">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-teal-100 rounded-xl flex items-center justify-center group-hover:from-blue-200 group-hover:to-teal-200 transition-colors duration-200">
                      <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Reset Password</span>
                  </button>
                  
                  <button class="w-full flex items-center space-x-3 p-3 rounded-xl border border-gray-200/50 hover:bg-rose-50 transition-colors duration-200 group">
                    <div class="w-10 h-10 bg-gradient-to-br from-rose-100 to-pink-100 rounded-xl flex items-center justify-center group-hover:from-rose-200 group-hover:to-pink-200 transition-colors duration-200">
                      <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Login Activity</span>
                  </button>
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
const user = page.props.user
const roles = computed(() => page.props.roles || [])
const userRoles = computed(() => page.props.userRoles || [])
const tables = computed(() => page.props.tables || [])

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}
</script>