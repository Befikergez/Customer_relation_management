<template>
  <div class="flex min-h-screen bg-gradient-to-br from-blue-50 to-teal-50">
    <!-- Sidebar -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
      <!-- Fixed Transparent Header -->
      <div class="bg-transparent backdrop-blur-lg border-b border-blue-200/30 shadow-sm fixed top-0 left-0 right-0 lg:left-64 z-50 transition-all duration-300">
        <div class="max-w-6xl mx-auto px-8 py-6">
          <div class="flex items-center space-x-4">
            <div class="relative">
              <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-teal-500 rounded-2xl blur opacity-20"></div>
              <div class="relative p-3 bg-gradient-to-r from-blue-500 to-teal-500 rounded-xl shadow-lg">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </div>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-black">
                System Administration
              </h1>
              <p class="text-black/70 text-lg mt-1">
                Manage Users, Roles, Products, Categories, Industries, Cities & Subcities
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Area with top padding for fixed header -->
      <div class="flex-1 px-8 pt-32 pb-8">
        <!-- Vertical Accordion -->
        <div class="max-w-6xl mx-auto space-y-4">
          
          <!-- Users Management Section -->
          <div class="group relative">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-teal-500 rounded-2xl blur opacity-0 group-hover:opacity-10 transition duration-500"></div>
            
            <div class="relative bg-white/90 backdrop-blur-sm rounded-xl border border-blue-200/50 shadow-sm hover:shadow-lg transition-all duration-500 overflow-hidden">
              <!-- Card Header -->
              <div 
                class="p-6 cursor-pointer hover:bg-blue-50/30 transition-all duration-300"
                @click="toggleSection('users')"
                :class="{ 'bg-blue-50/50': isSectionExpanded('users') }"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div 
                      :class="[
                        'bg-gradient-to-r from-blue-500 to-cyan-500', 
                        'p-3 rounded-xl shadow-lg flex-shrink-0 transition-all duration-500 transform',
                        { 'scale-110 rotate-12': isSectionExpanded('users') }
                      ]"
                    >
                      <svg class="w-6 h-6 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-xl font-bold text-black mb-1">User</h3>
                      <p class="text-black/70 text-sm">Manage system users and their permissions</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                      <div 
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="users_data.length > 0 ? 'bg-green-500 animate-pulse' : 'bg-rose-500'"
                      ></div>
                      <span class="text-sm text-black/60 font-medium">{{ users_count }} Users</span>
                    </div>
                    <svg
                      class="w-6 h-6 text-blue-400 transition-all duration-500 transform flex-shrink-0"
                      :class="{ 'rotate-180 scale-110 text-teal-500': isSectionExpanded('users') }"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Expanded Content -->
              <div v-if="isSectionExpanded('users')" class="border-t border-blue-100/50">
                <div class="p-6">
                  <!-- Users Grid -->
                  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                    <div
                      v-for="user in users_data"
                      :key="user.id"
                      class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-500 border border-gray-200/50 hover:border-teal-300/50 overflow-hidden backdrop-blur-sm"
                    >
                      <!-- User Card Header -->
                      <div class="p-6 border-b border-gray-200/50">
                        <div class="flex items-center justify-between">
                          <div class="flex items-center space-x-4">
                            <div class="relative">
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

                      <!-- Commission Rate Section -->
                      <div v-if="hasCommissionRole(user)" class="px-6 pb-4">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                          <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                          </svg>
                          Commission Rate
                        </h3>
                        <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-amber-200 rounded-xl p-3">
                          <div class="flex items-center justify-between">
                            <span class="text-amber-800 font-semibold text-sm">Current Rate</span>
                            <span class="text-amber-700 font-bold text-lg">{{ user.commission_rate || 0 }}%</span>
                          </div>
                        </div>
                      </div>

                      <!-- Actions -->
                      <div class="px-6 pb-6">
                        <div class="flex space-x-3">
                          <button
                            @click="openEditUserModal(user)"
                            class="flex-1 group/edit bg-gradient-to-r from-blue-50 to-gray-50 hover:from-blue-100 hover:to-gray-100 text-gray-700 px-4 py-3 rounded-xl transition-all duration-300 text-center font-semibold flex items-center justify-center border border-gray-300/50 hover:border-blue-300"
                          >
                            <svg class="w-4 h-4 mr-2 text-blue-600 group-hover/edit:text-blue-700 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Edit
                          </button>
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

                  <!-- Empty State -->
                  <div v-if="users_data.length === 0" class="text-center py-16">
                    <div class="max-w-md mx-auto">
                      <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-teal-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                        </svg>
                      </div>
                      <h3 class="text-xl font-semibold text-gray-800 mb-2">No users found</h3>
                      <p class="text-gray-600 mb-6">Get started by creating your first user account.</p>
                      <button
                        @click="openCreateUserModal"
                        class="inline-flex items-center px-6 py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 shadow-md border border-blue-500/20"
                      >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create First User
                      </button>
                    </div>
                  </div>

                  <!-- Create User Button -->
                  <div class="flex justify-center mt-8">
                    <button
                      @click="openCreateUserModal"
                      class="group relative bg-gradient-to-r from-blue-600 to-blue-700 text-white px-6 py-3.5 rounded-xl hover:shadow-lg transition-all duration-300 shadow-md flex items-center font-semibold overflow-hidden border border-blue-500/20"
                    >
                      <div class="absolute inset-0 bg-gradient-to-r from-blue-700 to-blue-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                      <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      <span class="relative z-10">Create New User</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Roles Management Section -->
          <div class="group relative">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-teal-500 rounded-2xl blur opacity-0 group-hover:opacity-10 transition duration-500"></div>
            
            <div class="relative bg-white/90 backdrop-blur-sm rounded-xl border border-blue-200/50 shadow-sm hover:shadow-lg transition-all duration-500 overflow-hidden">
              <!-- Card Header -->
              <div 
                class="p-6 cursor-pointer hover:bg-blue-50/30 transition-all duration-300"
                @click="toggleSection('roles')"
                :class="{ 'bg-blue-50/50': isSectionExpanded('roles') }"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div 
                      :class="[
                        'bg-gradient-to-r from-blue-600 to-teal-500', 
                        'p-3 rounded-xl shadow-lg flex-shrink-0 transition-all duration-500 transform',
                        { 'scale-110 rotate-12': isSectionExpanded('roles') }
                      ]"
                    >
                      <svg class="w-6 h-6 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-xl font-bold text-black mb-1">Role</h3>
                      <p class="text-black/70 text-sm">Configure user roles and access levels</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                      <div 
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="roles_data.length > 0 ? 'bg-green-500 animate-pulse' : 'bg-rose-500'"
                      ></div>
                      <span class="text-sm text-black/60 font-medium">{{ roles_count }} Roles</span>
                    </div>
                    <svg
                      class="w-6 h-6 text-blue-400 transition-all duration-500 transform flex-shrink-0"
                      :class="{ 'rotate-180 scale-110 text-teal-500': isSectionExpanded('roles') }"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Expanded Content -->
              <div v-if="isSectionExpanded('roles')" class="border-t border-blue-100/50">
                <div class="p-6">
                  <!-- Roles Table -->
                  <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100/50 overflow-hidden mb-8">
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
                          v-for="role in roles_data" 
                          :key="role.id" 
                          class="hover:bg-blue-50/50 transition-all duration-200 group"
                        >
                          <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
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
                              <button
                                @click="openEditRoleModal(role)"
                                class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                                title="Edit Role"
                              >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                              </button>
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
                    <div v-if="roles_data.length === 0" class="text-center py-16">
                      <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                      </div>
                      <h3 class="text-xl font-semibold text-slate-900 mb-3">No roles found</h3>
                      <p class="text-slate-600 mb-8 max-w-md mx-auto">Get started by creating your first role to manage system permissions.</p>
                      <button
                        @click="openCreateRoleModal"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-teal-600 text-white rounded-xl hover:from-blue-700 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                      >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Your First Role
                      </button>
                    </div>
                  </div>

                  <!-- Create Role Button -->
                  <div class="flex justify-center">
                    <button
                      @click="openCreateRoleModal"
                      class="group relative bg-gradient-to-r from-blue-600 to-teal-600 text-white px-6 py-3.5 rounded-xl hover:from-blue-700 hover:to-teal-700 transition-all duration-300 shadow-md flex items-center font-semibold overflow-hidden border border-blue-500/20"
                    >
                      <div class="absolute inset-0 bg-gradient-to-r from-blue-700 to-teal-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                      <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      <span class="relative z-10">Create New Role</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Products Management Section -->
          <div class="group relative">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-teal-500 rounded-2xl blur opacity-0 group-hover:opacity-10 transition duration-500"></div>
            
            <div class="relative bg-white/90 backdrop-blur-sm rounded-xl border border-blue-200/50 shadow-sm hover:shadow-lg transition-all duration-500 overflow-hidden">
              <!-- Card Header -->
              <div 
                class="p-6 cursor-pointer hover:bg-blue-50/30 transition-all duration-300"
                @click="toggleSection('products')"
                :class="{ 'bg-blue-50/50': isSectionExpanded('products') }"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div 
                      :class="[
                        'bg-gradient-to-r from-blue-500 to-cyan-500', 
                        'p-3 rounded-xl shadow-lg flex-shrink-0 transition-all duration-500 transform',
                        { 'scale-110 rotate-12': isSectionExpanded('products') }
                      ]"
                    >
                      <svg class="w-6 h-6 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-xl font-bold text-black mb-1">Product</h3>
                      <p class="text-black/70 text-sm">Manage your product catalog and inventory</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                      <div 
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="products_data.length > 0 ? 'bg-green-500 animate-pulse' : 'bg-rose-500'"
                      ></div>
                      <span class="text-sm text-black/60 font-medium">{{ products_count }} Products</span>
                    </div>
                    <svg
                      class="w-6 h-6 text-blue-400 transition-all duration-500 transform flex-shrink-0"
                      :class="{ 'rotate-180 scale-110 text-teal-500': isSectionExpanded('products') }"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Expanded Content -->
              <div v-if="isSectionExpanded('products')" class="border-t border-blue-100/50">
                <div class="p-6">
                  <!-- Products Table -->
                  <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100/50 overflow-hidden mb-8">
                    <table class="min-w-full divide-y divide-slate-200">
                      <thead class="bg-gradient-to-r from-blue-50 to-teal-50">
                        <tr>
                          <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Product</th>
                          <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Category</th>
                          <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Industry</th>
                          <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Description</th>
                          <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 bg-white">
                        <tr 
                          v-for="product in products_data" 
                          :key="product.id" 
                          class="hover:bg-blue-50/50 transition-all duration-200 group"
                        >
                          <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                              </div>
                              <div>
                                <span class="text-sm font-semibold text-slate-900">{{ product.name }}</span>
                                <p class="text-xs text-slate-500 mt-1">Created by {{ product.creator?.name }}</p>
                              </div>
                            </div>
                          </td>
                          <td class="px-6 py-4">
                            <span v-if="product.category" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800 border border-teal-200">
                              {{ product.category.name }}
                            </span>
                            <span v-else class="text-slate-400 text-sm">No category</span>
                          </td>
                          <td class="px-6 py-4">
                            <span v-if="product.industry" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                              {{ product.industry.name }}
                            </span>
                            <span v-else class="text-slate-400 text-sm">No industry</span>
                          </td>
                          <td class="px-6 py-4">
                            <p class="text-sm text-slate-600 max-w-xs truncate">{{ product.description || 'No description provided' }}</p>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                              <button
                                @click="openEditProductModal(product)"
                                class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                                title="Edit Product"
                              >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                              </button>
                              <button
                                @click="deleteProduct(product.id)"
                                class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                                title="Delete Product"
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
                    <div v-if="products_data.length === 0" class="text-center py-16">
                      <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                      </div>
                      <h3 class="text-xl font-semibold text-slate-900 mb-3">No products found</h3>
                      <p class="text-slate-600 mb-8 max-w-md mx-auto">Get started by creating your first product to build your catalog.</p>
                      <button
                        @click="openCreateProductModal"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                      >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Your First Product
                      </button>
                    </div>
                  </div>

                  <!-- Create Product Button -->
                  <div class="flex justify-center">
                    <button
                      @click="openCreateProductModal"
                      class="group relative bg-gradient-to-r from-blue-500 to-teal-500 text-white px-6 py-3.5 rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-md flex items-center font-semibold overflow-hidden border border-blue-500/20"
                    >
                      <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-teal-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                      <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      <span class="relative z-10">Create New Product</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Categories Management Section -->
          <div class="group relative">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-teal-500 rounded-2xl blur opacity-0 group-hover:opacity-10 transition duration-500"></div>
            
            <div class="relative bg-white/90 backdrop-blur-sm rounded-xl border border-blue-200/50 shadow-sm hover:shadow-lg transition-all duration-500 overflow-hidden">
              <!-- Card Header -->
              <div 
                class="p-6 cursor-pointer hover:bg-blue-50/30 transition-all duration-300"
                @click="toggleSection('categories')"
                :class="{ 'bg-blue-50/50': isSectionExpanded('categories') }"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div 
                      :class="[
                        'bg-gradient-to-r from-blue-500 to-cyan-500', 
                        'p-3 rounded-xl shadow-lg flex-shrink-0 transition-all duration-500 transform',
                        { 'scale-110 rotate-12': isSectionExpanded('categories') }
                      ]"
                    >
                      <svg class="w-6 h-6 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-xl font-bold text-black mb-1">Category</h3>
                      <p class="text-black/70 text-sm">Manage your product categories and classifications</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                      <div 
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="categories_data.length > 0 ? 'bg-green-500 animate-pulse' : 'bg-rose-500'"
                      ></div>
                      <span class="text-sm text-black/60 font-medium">{{ categories_count }} Categories</span>
                    </div>
                    <svg
                      class="w-6 h-6 text-blue-400 transition-all duration-500 transform flex-shrink-0"
                      :class="{ 'rotate-180 scale-110 text-teal-500': isSectionExpanded('categories') }"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Expanded Content -->
              <div v-if="isSectionExpanded('categories')" class="border-t border-blue-100/50">
                <div class="p-6">
                  <!-- Categories Grid -->
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div
                      v-for="category in categories_data"
                      :key="category.id"
                      class="group bg-gradient-to-br from-white to-blue-50 rounded-2xl p-6 border border-blue-200 hover:border-teal-300 shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                    >
                      <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                          <h3 class="text-lg font-semibold text-slate-900 group-hover:text-blue-700 transition-colors">
                            {{ category.name }}
                          </h3>
                          <p class="text-slate-600 text-sm mt-1 line-clamp-2">
                            {{ category.description || 'No description provided' }}
                          </p>
                        </div>
                        <div class="flex space-x-2 ml-4">
                          <span 
                            :class="category.is_active ? 
                              'bg-teal-100 text-teal-800 border-teal-200' : 
                              'bg-rose-100 text-rose-800 border-rose-200'"
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                          >
                            {{ category.is_active ? 'Active' : 'Inactive' }}
                          </span>
                        </div>
                      </div>

                      <div class="flex items-center justify-end mt-6 pt-4 border-t border-slate-100">
                        <div class="flex space-x-2">
                          <button
                            @click="openEditCategoryModal(category)"
                            class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                            title="Edit Category"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                          </button>
                          <button
                            @click="deleteCategory(category.id)"
                            class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                            title="Delete Category"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Empty State -->
                  <div v-if="categories_data.length === 0" class="text-center py-16">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                      <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                      </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">No categories found</h3>
                    <p class="text-slate-600 mb-8 max-w-md mx-auto">Get started by creating your first product category to organize your products.</p>
                    <button
                      @click="openCreateCategoryModal"
                      class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      Create Your First Category
                    </button>
                  </div>

                  <!-- Create Category Button -->
                  <div class="flex justify-center">
                    <button
                      @click="openCreateCategoryModal"
                      class="group relative bg-gradient-to-r from-blue-500 to-teal-500 text-white px-6 py-3.5 rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-md flex items-center font-semibold overflow-hidden border border-blue-500/20"
                    >
                      <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-teal-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                      <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      <span class="relative z-10">Create New Category</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Industries Management Section -->
          <div class="group relative">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-teal-500 rounded-2xl blur opacity-0 group-hover:opacity-10 transition duration-500"></div>
            
            <div class="relative bg-white/90 backdrop-blur-sm rounded-xl border border-blue-200/50 shadow-sm hover:shadow-lg transition-all duration-500 overflow-hidden">
              <!-- Card Header -->
              <div 
                class="p-6 cursor-pointer hover:bg-blue-50/30 transition-all duration-300"
                @click="toggleSection('industries')"
                :class="{ 'bg-blue-50/50': isSectionExpanded('industries') }"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div 
                      :class="[
                        'bg-gradient-to-r from-blue-500 to-cyan-500', 
                        'p-3 rounded-xl shadow-lg flex-shrink-0 transition-all duration-500 transform',
                        { 'scale-110 rotate-12': isSectionExpanded('industries') }
                      ]"
                    >
                      <svg class="w-6 h-6 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-xl font-bold text-black mb-1">Industry</h3>
                      <p class="text-black/70 text-sm">Manage business industry sectors</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                      <div 
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="industries_data.length > 0 ? 'bg-green-500 animate-pulse' : 'bg-rose-500'"
                      ></div>
                      <span class="text-sm text-black/60 font-medium">{{ industries_count }} Industries</span>
                    </div>
                    <svg
                      class="w-6 h-6 text-blue-400 transition-all duration-500 transform flex-shrink-0"
                      :class="{ 'rotate-180 scale-110 text-teal-500': isSectionExpanded('industries') }"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Expanded Content -->
              <div v-if="isSectionExpanded('industries')" class="border-t border-blue-100/50">
                <div class="p-6">
                  <!-- Industries List -->
                  <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100/50 overflow-hidden mb-8">
                    <div class="divide-y divide-blue-100">
                      <div
                        v-for="industry in industries_data"
                        :key="industry.id"
                        class="group px-6 py-5 hover:bg-blue-50/50 transition-all duration-300"
                      >
                        <div class="flex items-center justify-between">
                          <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-teal-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                              </svg>
                            </div>
                            <div>
                              <h4 class="text-lg font-semibold text-slate-800 group-hover:text-blue-700 transition-colors">
                                  {{ industry.name }}
                              </h4>
                              <p class="text-slate-600 text-sm mt-1 max-w-2xl">
                                {{ industry.description || 'No description provided' }}
                              </p>
                            </div>
                          </div>
                          <div class="flex items-center space-x-2">
                            <button
                              @click="openEditIndustryModal(industry)"
                              class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 transform hover:scale-105 group/btn"
                              title="Edit Industry"
                            >
                              <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                              </svg>
                            </button>
                            
                            <button
                              @click="deleteIndustry(industry.id)"
                              class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200 transform hover:scale-105 group/btn"
                              title="Delete Industry"
                            >
                              <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                              </svg>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="industries_data.length === 0" class="text-center py-16">
                      <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                      </div>
                      <h3 class="text-xl font-semibold text-slate-800 mb-3">No industries yet</h3>
                      <p class="text-slate-600 mb-8 max-w-md mx-auto">Start by creating your first industry sector to categorize businesses.</p>
                      <button
                        @click="openCreateIndustryModal"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                      >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create First Industry
                      </button>
                    </div>
                  </div>

                  <!-- Create Industry Button -->
                  <div class="flex justify-center">
                    <button
                      @click="openCreateIndustryModal"
                      class="group relative bg-gradient-to-r from-blue-500 to-teal-500 text-white px-6 py-3.5 rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-md flex items-center font-semibold overflow-hidden border border-blue-500/20"
                    >
                      <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-teal-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                      <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      <span class="relative z-10">Create New Industry</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Cities Management Section -->
          <div class="group relative">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-teal-500 rounded-2xl blur opacity-0 group-hover:opacity-10 transition duration-500"></div>
            
            <div class="relative bg-white/90 backdrop-blur-sm rounded-xl border border-blue-200/50 shadow-sm hover:shadow-lg transition-all duration-500 overflow-hidden">
              <!-- Card Header -->
              <div 
                class="p-6 cursor-pointer hover:bg-blue-50/30 transition-all duration-300"
                @click="toggleSection('cities')"
                :class="{ 'bg-blue-50/50': isSectionExpanded('cities') }"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div 
                      :class="[
                        'bg-gradient-to-r from-blue-500 to-cyan-500', 
                        'p-3 rounded-xl shadow-lg flex-shrink-0 transition-all duration-500 transform',
                        { 'scale-110 rotate-12': isSectionExpanded('cities') }
                      ]"
                    >
                      <svg class="w-6 h-6 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-xl font-bold text-black mb-1">City</h3>
                      <p class="text-black/70 text-sm">Manage cities and their subcities</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                      <div 
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="cities_data.length > 0 ? 'bg-green-500 animate-pulse' : 'bg-rose-500'"
                      ></div>
                      <span class="text-sm text-black/60 font-medium">{{ cities_count }} Cities</span>
                    </div>
                    <svg
                      class="w-6 h-6 text-blue-400 transition-all duration-500 transform flex-shrink-0"
                      :class="{ 'rotate-180 scale-110 text-teal-500': isSectionExpanded('cities') }"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Expanded Content -->
              <div v-if="isSectionExpanded('cities')" class="border-t border-blue-100/50">
                <div class="p-6">
                  <!-- Cities Grid -->
                  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div
                      v-for="city in cities_data"
                      :key="city.id"
                      class="group bg-gradient-to-br from-white to-blue-50 rounded-2xl p-6 border border-blue-200 hover:border-teal-300 shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1"
                    >
                      <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                          <h3 class="text-lg font-semibold text-slate-900 group-hover:text-blue-700 transition-colors">
                            {{ city.name }}
                          </h3>
                        </div>
                      </div>

                      <!-- Subcities Count -->
                      <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-100">
                        <div class="flex items-center space-x-2 text-sm text-slate-600">
                          <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                          </svg>
                          <span>{{ city.subcities?.length || 0 }} subcities</span>
                        </div>
                        <div class="flex space-x-2">
                          <button
                            @click="openEditCityModal(city)"
                            class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                            title="Edit City"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                          </button>
                          <button
                            @click="deleteCity(city.id)"
                            class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                            title="Delete City"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Empty State -->
                  <div v-if="cities_data.length === 0" class="text-center py-16">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                      <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-3">No cities found</h3>
                    <p class="text-slate-600 mb-8 max-w-md mx-auto">Get started by creating your first city to organize locations.</p>
                    <button
                      @click="openCreateCityModal"
                      class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      Create Your First City
                    </button>
                  </div>

                  <!-- Create City Button -->
                  <div class="flex justify-center">
                    <button
                      @click="openCreateCityModal"
                      class="group relative bg-gradient-to-r from-blue-500 to-teal-500 text-white px-6 py-3.5 rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-md flex items-center font-semibold overflow-hidden border border-blue-500/20"
                    >
                      <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-teal-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                      <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      <span class="relative z-10">Create New City</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Subcities Management Section -->
          <div class="group relative">
            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-teal-500 rounded-2xl blur opacity-0 group-hover:opacity-10 transition duration-500"></div>
            
            <div class="relative bg-white/90 backdrop-blur-sm rounded-xl border border-blue-200/50 shadow-sm hover:shadow-lg transition-all duration-500 overflow-hidden">
              <!-- Card Header -->
              <div 
                class="p-6 cursor-pointer hover:bg-blue-50/30 transition-all duration-300"
                @click="toggleSection('subcities')"
                :class="{ 'bg-blue-50/50': isSectionExpanded('subcities') }"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-4">
                    <div 
                      :class="[
                        'bg-gradient-to-r from-blue-500 to-cyan-500', 
                        'p-3 rounded-xl shadow-lg flex-shrink-0 transition-all duration-500 transform',
                        { 'scale-110 rotate-12': isSectionExpanded('subcities') }
                      ]"
                    >
                      <svg class="w-6 h-6 text-white transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-xl font-bold text-black mb-1">Subcity</h3>
                      <p class="text-black/70 text-sm">Manage subcities within cities</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                      <div 
                        class="w-2 h-2 rounded-full transition-all duration-300"
                        :class="subcities_data.length > 0 ? 'bg-green-500 animate-pulse' : 'bg-rose-500'"
                      ></div>
                      <span class="text-sm text-black/60 font-medium">{{ subcities_count }} Subcities</span>
                    </div>
                    <svg
                      class="w-6 h-6 text-blue-400 transition-all duration-500 transform flex-shrink-0"
                      :class="{ 'rotate-180 scale-110 text-teal-500': isSectionExpanded('subcities') }"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                  </div>
                </div>
              </div>

              <!-- Expanded Content -->
              <div v-if="isSectionExpanded('subcities')" class="border-t border-blue-100/50">
                <div class="p-6">
                  <!-- Subcities Table -->
                  <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100/50 overflow-hidden mb-8">
                    <table class="min-w-full divide-y divide-slate-200">
                      <thead class="bg-gradient-to-r from-blue-50 to-teal-50">
                        <tr>
                          <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Subcity Details</th>
                          <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Parent City</th>
                          <th class="px-6 py-4 text-left text-sm font-semibold text-slate-700 uppercase tracking-wider">Actions</th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-slate-100 bg-white">
                        <tr 
                          v-for="subcity in subcities_data" 
                          :key="subcity.id" 
                          class="hover:bg-blue-50/50 transition-all duration-200 group"
                        >
                          <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                              <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                              </div>
                              <div>
                                <span class="text-sm font-semibold text-slate-900">{{ subcity.name }}</span>
                              </div>
                            </div>
                          </td>
                          <td class="px-6 py-4">
                            <span v-if="subcity.city" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-teal-100 text-teal-800 border border-teal-200">
                              {{ subcity.city.name }}
                            </span>
                            <span v-else class="text-slate-400 text-sm">No parent city</span>
                          </td>
                          <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex space-x-2">
                              <button
                                @click="openEditSubcityModal(subcity)"
                                class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                                title="Edit Subcity"
                              >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                              </button>
                              <button
                                @click="deleteSubcity(subcity.id)"
                                class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200 transform hover:scale-105"
                                title="Delete Subcity"
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
                    <div v-if="subcities_data.length === 0" class="text-center py-16">
                      <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                      </div>
                      <h3 class="text-xl font-semibold text-slate-900 mb-3">No subcities found</h3>
                      <p class="text-slate-600 mb-8 max-w-md mx-auto">Get started by creating your first subcity within a city.</p>
                      <button
                        @click="openCreateSubcityModal"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                      >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Your First Subcity
                      </button>
                    </div>
                  </div>

                  <!-- Create Subcity Button -->
                  <div class="flex justify-center">
                    <button
                      @click="openCreateSubcityModal"
                      class="group relative bg-gradient-to-r from-blue-500 to-teal-500 text-white px-6 py-3.5 rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-md flex items-center font-semibold overflow-hidden border border-blue-500/20"
                    >
                      <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-teal-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                      <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                      </svg>
                      <span class="relative z-10">Create New Subcity</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Modal Backdrop -->
    <div v-if="showUserModal || showRoleModal || showProductModal || showCategoryModal || showIndustryModal || showCityModal || showSubcityModal" 
         class="fixed inset-0 bg-black/50 z-50" 
         @click="closeAllModals">
    </div>

    <!-- User Form Modal -->
    <div v-if="showUserModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl shadow-2xl border border-blue-100/50 w-full max-w-4xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="bg-gradient-to-r from-blue-50 via-white to-teal-50 px-8 py-6 border-b border-gray-200/50">
          <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-700 to-teal-700 bg-clip-text text-transparent">
            {{ selectedUser ? 'Edit User' : 'Create New User' }}
          </h2>
          <p class="text-gray-600 mt-1">
            {{ selectedUser ? 'Update user information and permissions' : 'Add a new user to the system' }}
          </p>
        </div>
        <UserFormModal
          :user="selectedUser"
          :roles="all_roles"
          @saved="handleUserSaved"
          @cancelled="closeUserModal"
        />
      </div>
    </div>

    <!-- Role Form Modal -->
    <div v-if="showRoleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl shadow-2xl border border-blue-100/50 w-full max-w-4xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="bg-gradient-to-r from-blue-50 via-white to-teal-50 px-8 py-6 border-b border-gray-200/50">
          <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-700 to-teal-700 bg-clip-text text-transparent">
            {{ selectedRole ? 'Edit Role' : 'Create New Role' }}
          </h2>
          <p class="text-gray-600 mt-1">
            {{ selectedRole ? 'Update role information and permissions' : 'Add a new role to the system' }}
          </p>
        </div>
        <RoleFormModal
          :role="selectedRole"
          :permissions="all_permissions"
          @saved="handleRoleSaved"
          @cancelled="closeRoleModal"
        />
      </div>
    </div>

    <!-- Product Form Modal -->
    <div v-if="showProductModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl shadow-2xl border border-blue-100/50 w-full max-w-4xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="bg-gradient-to-r from-blue-50 via-white to-teal-50 px-8 py-6 border-b border-gray-200/50">
          <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-700 to-teal-700 bg-clip-text text-transparent">
            {{ selectedProduct ? 'Edit Product' : 'Create New Product' }}
          </h2>
          <p class="text-gray-600 mt-1">
            {{ selectedProduct ? 'Update product information and categorization' : 'Add a new product to your catalog' }}
          </p>
        </div>
        <ProductFormModal
          :product="selectedProduct"
          :categories="all_categories"
          :industries="all_industries"
          @saved="handleProductSaved"
          @cancelled="closeProductModal"
        />
      </div>
    </div>

    <!-- Category Form Modal -->
    <div v-if="showCategoryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl shadow-2xl border border-blue-100/50 w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="bg-gradient-to-r from-blue-50 via-white to-teal-50 px-8 py-6 border-b border-gray-200/50">
          <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-700 to-teal-700 bg-clip-text text-transparent">
            {{ selectedCategory ? 'Edit Category' : 'Create New Category' }}
          </h2>
          <p class="text-gray-600 mt-1">
            {{ selectedCategory ? 'Update category information' : 'Add a new product category to organize your products' }}
          </p>
        </div>
        <CategoryFormModal
          :category="selectedCategory"
          @saved="handleCategorySaved"
          @cancelled="closeCategoryModal"
        />
      </div>
    </div>

    <!-- Industry Form Modal -->
    <div v-if="showIndustryModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl shadow-2xl border border-blue-100/50 w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="bg-gradient-to-r from-blue-50 via-white to-teal-50 px-8 py-6 border-b border-gray-200/50">
          <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-700 to-teal-700 bg-clip-text text-transparent">
            {{ selectedIndustry ? 'Edit Industry' : 'Create New Industry' }}
          </h2>
          <p class="text-gray-600 mt-1">
            {{ selectedIndustry ? 'Update industry sector information' : 'Define a new business industry category' }}
          </p>
        </div>
        <IndustryFormModal
          :industry="selectedIndustry"
          @saved="handleIndustrySaved"
          @cancelled="closeIndustryModal"
        />
      </div>
    </div>

    <!-- City Form Modal -->
    <div v-if="showCityModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl shadow-2xl border border-blue-100/50 w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="bg-gradient-to-r from-blue-50 via-white to-teal-50 px-8 py-6 border-b border-gray-200/50">
          <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-700 to-teal-700 bg-clip-text text-transparent">
            {{ selectedCity ? 'Edit City' : 'Create New City' }}
          </h2>
          <p class="text-gray-600 mt-1">
            {{ selectedCity ? 'Update city information' : 'Add a new city to organize locations' }}
          </p>
        </div>
        <CityFormModal
          :city="selectedCity"
          @saved="handleCitySaved"
          @cancelled="closeCityModal"
        />
      </div>
    </div>

    <!-- Subcity Form Modal -->
    <div v-if="showSubcityModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-3xl shadow-2xl border border-blue-100/50 w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="bg-gradient-to-r from-blue-50 via-white to-teal-50 px-8 py-6 border-b border-gray-200/50">
          <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-700 to-teal-700 bg-clip-text text-transparent">
            {{ selectedSubcity ? 'Edit Subcity' : 'Create New Subcity' }}
          </h2>
          <p class="text-gray-600 mt-1">
            {{ selectedSubcity ? 'Update subcity information' : 'Add a new subcity within a city' }}
          </p>
        </div>
        <SubcityFormModal
          :subcity="selectedSubcity"
          :cities="all_cities"
          @saved="handleSubcitySaved"
          @cancelled="closeSubcityModal"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import Sidebar from './Sidebar.vue'
import UserFormModal from './Modals/UserFormModal.vue'
import RoleFormModal from './Modals/RoleFormModal.vue'
import ProductFormModal from './Modals/ProductFormModal.vue'
import CategoryFormModal from './Modals/CategoryFormModal.vue'
import IndustryFormModal from './Modals/IndustryFormModal.vue'
import CityFormModal from './Modals/CityFormModal.vue'
import SubcityFormModal from './Modals/SubcityFormModal.vue'

// Define props
const props = defineProps({
  users_count: Number,
  roles_count: Number,
  products_count: Number,
  categories_count: Number,
  industries_count: Number,
  cities_count: Number,
  subcities_count: Number,
  active_users_count: Number,
  active_products_count: Number,
  active_categories_count: Number,
  active_industries_count: Number,
  active_cities_count: Number,
  active_subcities_count: Number,
  tables: Array,
  users_data: Array,
  roles_data: Array,
  products_data: Array,
  categories_data: Array,
  industries_data: Array,
  cities_data: Array,
  subcities_data: Array,
  all_roles: Array,
  all_permissions: Array,
  all_categories: Array,
  all_industries: Array,
  all_cities: Array,
})

// Reactive states
const searchQuery = ref('')
// Start with all sections CLOSED (empty array)
const expandedSections = ref([])

// Modal states
const showUserModal = ref(false)
const showRoleModal = ref(false)
const showProductModal = ref(false)
const showCategoryModal = ref(false)
const showIndustryModal = ref(false)
const showCityModal = ref(false)
const showSubcityModal = ref(false)

const selectedUser = ref(null)
const selectedRole = ref(null)
const selectedProduct = ref(null)
const selectedCategory = ref(null)
const selectedIndustry = ref(null)
const selectedCity = ref(null)
const selectedSubcity = ref(null)

// Computed properties
const filteredSections = computed(() => {
  if (!searchQuery.value) {
    return ['users', 'roles', 'products', 'categories', 'industries', 'cities', 'subcities']
  }

  const query = searchQuery.value.toLowerCase()
  const sections = []

  if ('users'.includes(query) || 'user management'.includes(query)) {
    sections.push('users')
  }
  if ('roles'.includes(query) || 'role management'.includes(query)) {
    sections.push('roles')
  }
  if ('products'.includes(query) || 'product management'.includes(query)) {
    sections.push('products')
  }
  if ('categories'.includes(query) || 'category management'.includes(query)) {
    sections.push('categories')
  }
  if ('industries'.includes(query) || 'industry management'.includes(query)) {
    sections.push('industries')
  }
  if ('cities'.includes(query) || 'city management'.includes(query)) {
    sections.push('cities')
  }
  if ('subcities'.includes(query) || 'subcity management'.includes(query)) {
    sections.push('subcities')
  }

  return sections
})

// Helper functions
const toggleSection = (sectionId) => {
  if (expandedSections.value.includes(sectionId)) {
    expandedSections.value = expandedSections.value.filter(id => id !== sectionId)
  } else {
    expandedSections.value.push(sectionId)
  }
}

const isSectionExpanded = (sectionId) => {
  return expandedSections.value.includes(sectionId)
}

// Updated to check for any commission-enabled role
const hasCommissionRole = (user) => {
  return user.commission_rate !== null && user.commission_rate !== undefined
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const closeAllModals = () => {
  showUserModal.value = false
  showRoleModal.value = false
  showProductModal.value = false
  showCategoryModal.value = false
  showIndustryModal.value = false
  showCityModal.value = false
  showSubcityModal.value = false
  selectedUser.value = null
  selectedRole.value = null
  selectedProduct.value = null
  selectedCategory.value = null
  selectedIndustry.value = null
  selectedCity.value = null
  selectedSubcity.value = null
}

// Reset modal states when page loads or when Inertia visits complete
onMounted(() => {
  resetModalStates()
})

// Watch for page changes and reset modals
router.on('success', () => {
  resetModalStates()
})

function resetModalStates() {
  showUserModal.value = false
  showRoleModal.value = false
  showProductModal.value = false
  showCategoryModal.value = false
  showIndustryModal.value = false
  showCityModal.value = false
  showSubcityModal.value = false
  selectedUser.value = null
  selectedRole.value = null
  selectedProduct.value = null
  selectedCategory.value = null
  selectedIndustry.value = null
  selectedCity.value = null
  selectedSubcity.value = null
}

// User modal functions
const openCreateUserModal = () => {
  selectedUser.value = null
  showUserModal.value = true
}

const openEditUserModal = (user) => {
  selectedUser.value = user
  showUserModal.value = true
}

const closeUserModal = () => {
  showUserModal.value = false
  selectedUser.value = null
}

const handleUserSaved = () => {
  closeUserModal()
  router.visit('/admin/settings', {
    preserveState: true,
    preserveScroll: true,
  })
}

const deleteUser = (userId) => {
  if (confirm('Are you sure you want to delete this user?')) {
    router.delete(`/admin/users/${userId}`, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        router.visit('/admin/settings', {
          preserveState: true,
          preserveScroll: true,
        })
      }
    })
  }
}

// Role modal functions
const openCreateRoleModal = () => {
  selectedRole.value = null
  showRoleModal.value = true
}

const openEditRoleModal = (role) => {
  selectedRole.value = role
  showRoleModal.value = true
}

const closeRoleModal = () => {
  showRoleModal.value = false
  selectedRole.value = null
}

const handleRoleSaved = () => {
  closeRoleModal()
  router.visit('/admin/settings', {
    preserveState: true,
    preserveScroll: true,
  })
}

const deleteRole = (roleId) => {
  if (confirm('Are you sure you want to delete this role?')) {
    router.delete(`/admin/roles/${roleId}`, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        router.visit('/admin/settings', {
          preserveState: true,
          preserveScroll: true,
        })
      }
    })
  }
}

// Product modal functions
const openCreateProductModal = () => {
  selectedProduct.value = null
  showProductModal.value = true
}

const openEditProductModal = (product) => {
  selectedProduct.value = product
  showProductModal.value = true
}

const closeProductModal = () => {
  showProductModal.value = false
  selectedProduct.value = null
}

const handleProductSaved = () => {
  closeProductModal()
  router.visit('/admin/settings', {
    preserveState: true,
    preserveScroll: true,
  })
}

const deleteProduct = (productId) => {
  if (confirm('Are you sure you want to delete this product?')) {
    router.delete(`/admin/products/${productId}`, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        router.visit('/admin/settings', {
          preserveState: true,
          preserveScroll: true,
        })
      }
    })
  }
}

// Category modal functions
const openCreateCategoryModal = () => {
  selectedCategory.value = null
  showCategoryModal.value = true
}

const openEditCategoryModal = (category) => {
  selectedCategory.value = category
  showCategoryModal.value = true
}

const closeCategoryModal = () => {
  showCategoryModal.value = false
  selectedCategory.value = null
}

const handleCategorySaved = () => {
  closeCategoryModal()
  router.visit('/admin/settings', {
    preserveState: true,
    preserveScroll: true,
  })
}

const deleteCategory = (categoryId) => {
  if (confirm('Are you sure you want to delete this category?')) {
    router.delete(`/admin/categories/${categoryId}`, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        router.visit('/admin/settings', {
          preserveState: true,
          preserveScroll: true,
        })
      }
    })
  }
}

// Industry modal functions
const openCreateIndustryModal = () => {
  selectedIndustry.value = null
  showIndustryModal.value = true
}

const openEditIndustryModal = (industry) => {
  selectedIndustry.value = industry
  showIndustryModal.value = true
}

const closeIndustryModal = () => {
  showIndustryModal.value = false
  selectedIndustry.value = null
}

const handleIndustrySaved = () => {
  closeIndustryModal()
  router.visit('/admin/settings', {
    preserveState: true,
    preserveScroll: true,
  })
}

const deleteIndustry = (industryId) => {
  if (confirm('Are you sure you want to delete this industry?')) {
    router.delete(`/admin/industries/${industryId}`, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        router.visit('/admin/settings', {
          preserveState: true,
          preserveScroll: true,
        })
      }
    })
  }
}

// City modal functions
const openCreateCityModal = () => {
  selectedCity.value = null
  showCityModal.value = true
}

const openEditCityModal = (city) => {
  selectedCity.value = city
  showCityModal.value = true
}

const closeCityModal = () => {
  showCityModal.value = false
  selectedCity.value = null
}

const handleCitySaved = () => {
  closeCityModal()
  router.visit('/admin/settings', {
    preserveState: true,
    preserveScroll: true,
  })
}

const deleteCity = (cityId) => {
  if (confirm('Are you sure you want to delete this city?')) {
    router.delete(`/admin/cities/${cityId}`, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        router.visit('/admin/settings', {
          preserveState: true,
          preserveScroll: true,
        })
      }
    })
  }
}

// Subcity modal functions
const openCreateSubcityModal = () => {
  selectedSubcity.value = null
  showSubcityModal.value = true
}

const openEditSubcityModal = (subcity) => {
  selectedSubcity.value = subcity
  showSubcityModal.value = true
}

const closeSubcityModal = () => {
  showSubcityModal.value = false
  selectedSubcity.value = null
}

const handleSubcitySaved = () => {
  closeSubcityModal()
  router.visit('/admin/settings', {
    preserveState: true,
    preserveScroll: true,
  })
}

const deleteSubcity = (subcityId) => {
  if (confirm('Are you sure you want to delete this subcity?')) {
    router.delete(`/admin/subcities/${subcityId}`, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: () => {
        router.visit('/admin/settings', {
          preserveState: true,
          preserveScroll: true,
        })
      }
    })
  }
}

// Lifecycle
onMounted(() => {
  // Any initialization code can go here
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.backdrop-blur-sm {
  backdrop-filter: blur(4px);
}

.backdrop-blur-lg {
  backdrop-filter: blur(16px);
}
</style>