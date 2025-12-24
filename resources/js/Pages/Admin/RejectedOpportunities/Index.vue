<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-teal-50 flex">
    <!-- Sidebar -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content -->
    <div class="flex-1 min-h-screen w-full overflow-x-hidden">
      <!-- Header -->
      <div class="bg-white/105 backdrop-blur-lg border-b border-blue-200/60 px-4 md:px-6 py-4 md:py-6 sticky top-0 z-20 shadow-lg">
        <div class="flex items-center">
          <!-- Mobile spacing for sidebar hamburger button -->
          <div v-if="!isDesktop" class="w-12 flex-shrink-0"></div>
          
          <div class="flex-1 flex flex-col md:flex-row md:justify-between md:items-center gap-3">
            <div class="flex items-center space-x-3 md:space-x-4">
              <!-- Hidden on tablet/mobile (hamburger button replaces this) -->
              <div class="hidden lg:flex w-10 h-10 md:w-12 md:h-12 bg-gradient-to-r from-rose-500 to-red-500 rounded-lg md:rounded-xl items-center justify-center shadow-lg flex-shrink-0">
                <ArchiveBoxIcon class="w-5 h-5 md:w-6 md:h-6 text-white" />
              </div>
              <div class="min-w-0 flex-1">
                <!-- Tablet/Mobile Title -->
                <div class="lg:hidden">
                  <h1 class="text-lg md:text-xl font-bold bg-gradient-to-r from-rose-600 to-red-600 bg-clip-text text-transparent flex items-center space-x-1 md:space-x-2">
                    <span class="truncate">Rejected</span>
                    <ExclamationTriangleIcon class="w-4 h-4 md:w-5 md:h-5 text-amber-500 flex-shrink-0" />
                  </h1>
                  <p class="text-slate-600 text-xs md:text-sm mt-0.5 truncate">View rejected items</p>
                </div>
                
                <!-- Desktop Title -->
                <div class="hidden lg:block">
                  <h1 class="text-xl md:text-2xl font-bold bg-gradient-to-r from-rose-600 to-red-600 bg-clip-text text-transparent flex items-center space-x-2">
                    <span>Rejected Opportunities</span>
                    <ExclamationTriangleIcon class="w-5 h-5 text-amber-500" />
                  </h1>
                  <p class="text-slate-600 text-sm md:text-base mt-1">View all rejected items from opportunities, potential customers, and customers</p>
                </div>
              </div>
            </div>
            <div class="text-xs text-slate-600 font-medium whitespace-nowrap mt-2 md:mt-0">
              {{ rejectedOpportunities.data?.length || 0 }} items
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="p-3 md:p-4 lg:p-5">
        <!-- Success/Error Messages -->
        <div v-if="$page.props.flash && $page.props.flash.success" class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 px-4 md:px-6 py-3 md:py-4 rounded-xl mb-4 md:mb-6 shadow-sm w-full">
          <div class="flex items-center">
            <div class="w-6 h-6 md:w-8 md:h-8 bg-green-100 rounded-lg flex items-center justify-center mr-2 md:mr-3 flex-shrink-0">
              <svg class="w-3 h-3 md:w-4 md:h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <span class="text-sm truncate">{{ $page.props.flash.success }}</span>
          </div>
        </div>
        
        <div v-if="$page.props.flash && $page.props.flash.error" class="bg-gradient-to-r from-red-50 to-rose-50 border border-red-200 text-red-700 px-4 md:px-6 py-3 md:py-4 rounded-xl mb-4 md:mb-6 shadow-sm w-full">
          <div class="flex items-center">
            <div class="w-6 h-6 md:w-8 md:h-8 bg-red-100 rounded-lg flex items-center justify-center mr-2 md:mr-3 flex-shrink-0">
              <svg class="w-3 h-3 md:w-4 md:h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </div>
            <span class="text-sm truncate">{{ $page.props.flash.error }}</span>
          </div>
        </div>

        <!-- Check if data is loading or empty -->
        <div v-if="!rejectedOpportunities || !rejectedOpportunities.data" class="text-center py-8 md:py-10 w-full">
          <div class="inline-block animate-spin rounded-full h-10 w-10 md:h-12 md:w-12 border-4 border-rose-500 border-t-transparent"></div>
          <p class="mt-3 md:mt-4 text-slate-600 text-sm">Loading rejected items...</p>
        </div>
        
        <!-- Empty State -->
        <div v-else-if="rejectedOpportunities.data.length === 0" class="bg-white rounded-xl md:rounded-2xl border border-rose-100/50 p-4 md:p-6 lg:p-8 text-center shadow-lg w-full">
          <div class="w-12 h-12 md:w-14 md:h-14 bg-gradient-to-br from-rose-100 to-red-100 rounded-xl flex items-center justify-center mx-auto mb-3 md:mb-4 shadow-inner">
            <ArchiveBoxIcon class="w-6 h-6 md:w-7 md:h-7 text-rose-500" />
          </div>
          <h3 class="text-base md:text-lg font-semibold text-slate-800 mb-1.5 md:mb-2">No rejected items found</h3>
          <p class="text-slate-600 mb-3 md:mb-4 text-xs md:text-sm px-2 mx-auto max-w-md">
            {{ hasActiveFilters ? 'Try adjusting your filters to see more results.' : 'Rejected items from opportunities, potential customers, and customers will appear here.' }}
          </p>
          <button
            v-if="hasActiveFilters"
            @click="clearFilters"
            class="bg-gradient-to-r from-rose-500 to-red-500 hover:from-rose-600 hover:to-red-600 text-white px-3 py-2 md:px-4 md:py-2.5 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center space-x-2 mx-auto shadow-lg hover:shadow-xl transform hover:scale-105 text-xs md:text-sm"
          >
            <XMarkIcon class="w-3.5 h-3.5 md:w-4 md:h-4" />
            <span>Clear Filters</span>
          </button>
        </div>

        <!-- Main Content (when data exists) -->
        <div v-else class="w-full">
          <!-- Stats Summary (Mobile) -->
          <div class="md:hidden bg-white rounded-xl border border-rose-100/50 p-3 shadow-lg mb-3 w-full">
            <div class="flex items-center justify-between">
              <div class="text-xs font-medium text-rose-600 bg-rose-50 px-2.5 py-1 rounded-full whitespace-nowrap">
                {{ rejectedOpportunities.data.length }} rejected items
              </div>
              <div class="text-xs text-slate-700 whitespace-nowrap">
                Page {{ rejectedOpportunities.meta?.current_page || 1 }} of {{ rejectedOpportunities.meta?.last_page || 1 }}
              </div>
            </div>
          </div>

          <!-- Table Container -->
          <div class="bg-white rounded-xl md:rounded-2xl border border-rose-100/50 overflow-hidden shadow-lg w-full">
            <!-- Filters Section -->
            <div class="border-b border-rose-100/50 bg-gradient-to-r from-rose-50 to-red-50/30 w-full">
              <div class="p-3 md:p-4">
                <div class="mb-2">
                  <h3 class="text-base md:text-lg font-bold text-slate-800">Rejected Items</h3>
                  <!-- Mobile pagination info -->
                  <div v-if="rejectedOpportunities.links && rejectedOpportunities.links.length > 3" class="md:hidden text-xs text-slate-600 mt-0.5">
                    Tap cards for details
                  </div>
                </div>
                
                <!-- Active Filters Count - Mobile -->
                <div v-if="hasActiveFilters" class="md:hidden text-xs text-rose-600 font-medium bg-rose-50 px-2.5 py-1 rounded-full whitespace-nowrap mb-2 inline-block">
                  {{ activeFilterCount }} filter{{ activeFilterCount !== 1 ? 's' : '' }} active
                </div>
                
                <!-- Filters Row -->
                <div class="space-y-3">
                  <!-- Compact Filters -->
                  <div class="flex flex-wrap items-center gap-2">
                    <!-- City Filter -->
                    <div class="flex items-center space-x-1.5">
                      <label class="text-xs font-medium text-slate-700 whitespace-nowrap">City:</label>
                      <select
                        v-model="filters.city_id"
                        @change="onCityChange"
                        class="px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition-all duration-200 w-28 md:w-32"
                      >
                        <option value="">All Cities</option>
                        <option v-for="city in cities" :key="city.id" :value="city.id">
                          {{ city.name }}
                        </option>
                      </select>
                    </div>

                    <!-- Subcity Filter -->
                    <div class="flex items-center space-x-1.5">
                      <label class="text-xs font-medium text-slate-700 whitespace-nowrap">Subcity:</label>
                      <select
                        v-model="filters.subcity_id"
                        @change="applyFilters"
                        class="px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition-all duration-200 w-28 md:w-32 disabled:bg-slate-100 disabled:cursor-not-allowed"
                        :disabled="!filters.city_id"
                      >
                        <option value="">All Subcities</option>
                        <option v-for="subcity in filteredSubcities" :key="subcity.id" :value="subcity.id">
                          {{ subcity.name }}
                        </option>
                      </select>
                    </div>

                    <!-- Source Filter -->
                    <div class="flex items-center space-x-1.5">
                      <label class="text-xs font-medium text-slate-700 whitespace-nowrap">Source:</label>
                      <select
                        v-model="filters.source"
                        @change="applyFilters"
                        class="px-2 py-1.5 bg-white border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition-all duration-200 w-32 md:w-36"
                      >
                        <option value="">All Sources</option>
                        <option value="opportunity">Opportunity</option>
                        <option value="potential_customer">Potential Customer</option>
                        <option value="customer">Customer</option>
                      </select>
                    </div>

                    <!-- Clear Filters Button -->
                    <button
                      @click="clearFilters"
                      class="px-2 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-medium transition-all duration-200 flex items-center space-x-1 whitespace-nowrap"
                    >
                      <XMarkIcon class="w-3 h-3" />
                      <span>Clear</span>
                    </button>
                  </div>
                  
                  <!-- Active Filters Badges -->
                  <div v-if="hasActiveFilters" class="flex flex-wrap gap-1.5">
                    <span 
                      v-if="filters.city_id" 
                      class="inline-flex items-center px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-medium border border-rose-200 whitespace-nowrap"
                    >
                      City: {{ getCityName(filters.city_id) }}
                      <button @click="removeFilter('city_id')" class="ml-1.5 hover:text-rose-900 transition-colors">
                        <XMarkIcon class="w-3 h-3" />
                      </button>
                    </span>
                    <span 
                      v-if="filters.subcity_id" 
                      class="inline-flex items-center px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-medium border border-rose-200 whitespace-nowrap"
                    >
                      Subcity: {{ getSubcityName(filters.subcity_id) }}
                      <button @click="removeFilter('subcity_id')" class="ml-1.5 hover:text-rose-900 transition-colors">
                        <XMarkIcon class="w-3 h-3" />
                      </button>
                    </span>
                    <span 
                      v-if="filters.source" 
                      class="inline-flex items-center px-2.5 py-1 bg-rose-50 text-rose-700 rounded-lg text-xs font-medium border border-rose-200 whitespace-nowrap"
                    >
                      Source: {{ formatSource(filters.source) }}
                      <button @click="removeFilter('source')" class="ml-1.5 hover:text-rose-900 transition-colors">
                        <XMarkIcon class="w-3 h-3" />
                      </button>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Mobile Card View -->
            <div v-if="!isDesktop" class="divide-y divide-rose-100/30">
              <div 
                v-for="item in rejectedOpportunities.data" 
                :key="item.id" 
                class="p-3 hover:bg-rose-50/50 transition-all duration-200"
              >
                <!-- Customer Header -->
                <div class="flex items-start justify-between mb-2 gap-2">
                  <div class="flex items-center space-x-2 min-w-0 flex-1">
                    <div class="w-8 h-8 bg-gradient-to-br from-rose-400 to-red-400 rounded-lg flex items-center justify-center shadow-sm flex-shrink-0">
                      <UserCircleIcon class="w-4 h-4 text-white" />
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="font-bold text-slate-800 text-sm truncate">{{ item.potential_customer_name || 'Unknown Customer' }}</h4>
                      <div class="text-xs text-slate-500 mt-0.5 truncate">{{ item.remarks || 'No remarks' }}</div>
                    </div>
                  </div>
                  <span :class="getSourceBadgeClass(item.rejected_from)" class="px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap flex-shrink-0">
                    {{ formatSource(item.rejected_from) }}
                  </span>
                </div>

                <!-- Contact & Location Info -->
                <div class="space-y-1.5 mb-2">
                  <div class="flex items-center space-x-2">
                    <EnvelopeIcon class="w-3.5 h-3.5 text-rose-500 flex-shrink-0" />
                    <span class="text-sm text-slate-700 truncate">{{ item.email || 'No email' }}</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <PhoneIcon class="w-3.5 h-3.5 text-red-500 flex-shrink-0" />
                    <span class="text-sm text-slate-700 truncate">{{ item.phone || 'No phone' }}</span>
                  </div>
                  <div class="flex items-center space-x-2">
                    <MapPinIcon class="w-3.5 h-3.5 text-purple-500 flex-shrink-0" />
                    <span class="text-sm text-slate-700 truncate">{{ item.city?.name || 'No city' }}</span>
                  </div>
                  <div v-if="item.subcity?.name" class="flex items-center space-x-2">
                    <BuildingOfficeIcon class="w-3.5 h-3.5 text-indigo-500 flex-shrink-0" />
                    <span class="text-sm text-slate-600 truncate">{{ item.subcity.name }}</span>
                  </div>
                </div>

                <!-- Rejection Details -->
                <div class="mb-2">
                  <div class="bg-slate-50 rounded-lg p-2">
                    <h4 class="font-medium text-slate-800 text-xs mb-1">Rejection Reason</h4>
                    <p class="text-slate-600 text-xs leading-relaxed line-clamp-2">{{ item.reason || 'No reason provided' }}</p>
                    <p class="text-slate-500 text-xs mt-1">By {{ item.created_by?.name || 'System' }}</p>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between">
                  <div class="text-xs text-slate-600 font-medium truncate max-w-[120px]">
                    {{ formatDate(item.created_at) }}
                  </div>
                  <div class="flex items-center space-x-1.5 flex-shrink-0">
                    <button 
                      @click="viewItem(item.id)"
                      class="bg-rose-500 hover:bg-rose-600 text-white p-1 rounded-lg text-xs font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[28px] h-7"
                      title="View"
                    >
                      <EyeIcon class="w-3 h-3" />
                    </button>
                    
                    <button 
                      v-if="permissions.edit"
                      @click="editItem(item.id)"
                      class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded-lg text-xs font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[28px] h-7"
                      title="Edit"
                    >
                      <PencilSquareIcon class="w-3 h-3" />
                    </button>

                    <button 
                      v-if="permissions.create"
                      @click="revertItem(item.id, item.rejected_from)"
                      class="bg-green-500 hover:bg-green-600 text-white p-1 rounded-lg text-xs font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[28px] h-7"
                      title="Revert"
                    >
                      <ArrowPathIcon class="w-3 h-3" />
                    </button>
                    
                    <button 
                      v-if="permissions.delete"
                      @click="deleteItem(item.id)"
                      class="bg-red-500 hover:bg-red-600 text-white p-1 rounded-lg text-xs font-medium transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[28px] h-7"
                      title="Delete"
                    >
                      <TrashIcon class="w-3 h-3" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Desktop & Tablet Table View - FIXED FOR NO HORIZONTAL SCROLL -->
            <div v-else class="w-full">
              <div class="w-full overflow-hidden">
                <table class="w-full divide-y divide-rose-100/30">
                  <thead class="bg-gradient-to-r from-rose-50 to-red-50/50">
                    <tr>
                      <th class="px-3 py-2 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-rose-100/50 w-[20%]">Customer</th>
                      <th class="px-3 py-2 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-rose-100/50 w-[18%]">Contact</th>
                      <th class="px-3 py-2 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-rose-100/50 w-[15%]">Location</th>
                      <th class="px-3 py-2 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-rose-100/50 w-[12%]">Source</th>
                      <th class="px-3 py-2 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-rose-100/50 w-[25%]">Rejection Details</th>
                      <th class="px-3 py-2 text-left text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-rose-100/50 w-[10%]">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-rose-100/30">
                    <tr 
                      v-for="item in rejectedOpportunities.data" 
                      :key="item.id" 
                      class="hover:bg-rose-50/50 transition-all duration-200 group bg-white"
                    >
                      <!-- Customer Details - Compact -->
                      <td class="px-3 py-2">
                        <div class="flex items-center">
                          <div class="w-6 h-6 bg-gradient-to-br from-rose-400 to-red-400 rounded flex items-center justify-center mr-2 shadow-sm group-hover:shadow-md transition-shadow flex-shrink-0">
                            <UserCircleIcon class="w-3 h-3 text-white" />
                          </div>
                          <div class="min-w-0">
                            <div class="font-semibold text-slate-800 text-xs truncate">{{ item.potential_customer_name || 'Unknown Customer' }}</div>
                            <div class="text-slate-500 text-xs truncate">{{ item.remarks || 'No remarks' }}</div>
                          </div>
                        </div>
                      </td>
                      
                      <!-- Contact Info - Compact -->
                      <td class="px-3 py-2">
                        <div class="space-y-0.5">
                          <div class="text-xs text-slate-900 truncate" :title="item.email || 'No email'">
                            {{ item.email || 'No email' }}
                          </div>
                          <div class="text-slate-500 text-xs truncate" :title="item.phone || 'No phone'">
                            {{ item.phone || 'No phone' }}
                          </div>
                        </div>
                      </td>
                      
                      <!-- Location - Compact -->
                      <td class="px-3 py-2">
                        <div class="space-y-0.5">
                          <div v-if="item.city" class="font-medium text-slate-900 text-xs truncate">
                            {{ item.city.name }}
                          </div>
                          <div v-if="item.subcity" class="text-slate-500 text-xs truncate">
                            {{ item.subcity.name }}
                          </div>
                        </div>
                      </td>
                      
                      <!-- Source Badge - Compact -->
                      <td class="px-3 py-2">
                        <span class="px-2 py-1 rounded-full text-xs font-semibold border whitespace-nowrap inline-block max-w-full truncate" :class="getSourceBadgeClass(item.rejected_from)">
                          {{ formatSource(item.rejected_from) }}
                        </span>
                      </td>
                      
                      <!-- Rejection Details - Compact -->
                      <td class="px-3 py-2">
                        <div>
                          <div class="font-medium text-slate-900 text-xs mb-0.5 line-clamp-2 leading-tight" :title="item.reason || 'No reason provided'">
                            {{ item.reason || 'No reason provided' }}
                          </div>
                          <div class="text-slate-500 text-xs truncate">By {{ item.created_by?.name || 'System' }}</div>
                          <div class="text-slate-400 text-xs mt-0.5 truncate">{{ formatDate(item.created_at) }}</div>
                        </div>
                      </td>
                      
                      <!-- Actions - Compact -->
                      <td class="px-3 py-2">
                        <div class="flex flex-wrap gap-1">
                          <button 
                            @click="viewItem(item.id)"
                            class="bg-rose-500 hover:bg-rose-600 text-white p-1 rounded text-xs transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[26px] h-6"
                            title="View Details"
                          >
                            <EyeIcon class="w-3 h-3" />
                          </button>

                          <button 
                            v-if="permissions.edit"
                            @click="editItem(item.id)"
                            class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded text-xs transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[26px] h-6"
                            title="Edit Item"
                          >
                            <PencilSquareIcon class="w-3 h-3" />
                          </button>

                          <button 
                            v-if="permissions.create"
                            @click="revertItem(item.id, item.rejected_from)"
                            class="bg-green-500 hover:bg-green-600 text-white p-1 rounded text-xs transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[26px] h-6"
                            title="Revert Item"
                          >
                            <ArrowPathIcon class="w-3 h-3" />
                          </button>
                          
                          <button 
                            v-if="permissions.delete"
                            @click="deleteItem(item.id)"
                            class="bg-red-500 hover:bg-red-600 text-white p-1 rounded text-xs transition-all duration-200 flex items-center justify-center shadow-md hover:shadow-lg min-w-[26px] h-6"
                            title="Delete Permanently"
                          >
                            <TrashIcon class="w-3 h-3" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Pagination - Desktop & Tablet -->
            <div v-if="rejectedOpportunities.links && rejectedOpportunities.links.length > 3 && isDesktop" class="px-4 py-3 bg-white border-t border-rose-100/50 flex justify-between items-center gap-2 w-full">
              <div class="text-xs text-slate-700 whitespace-nowrap">
                Page {{ rejectedOpportunities.meta?.current_page || 1 }} of {{ rejectedOpportunities.meta?.last_page || 1 }}
              </div>
              <div class="flex flex-wrap gap-1.5 justify-end">
                <button 
                  v-for="link in rejectedOpportunities.links"
                  :key="link.label"
                  :disabled="!link.url"
                  @click="visitPage(link.url)"
                  class="px-2 py-1 text-xs border rounded-lg transition-all duration-200 min-w-[32px] text-center"
                  :class="{
                    'bg-gradient-to-r from-rose-500 to-red-500 text-white border-transparent shadow-md': link.active,
                    'text-slate-600 border-rose-200 hover:bg-rose-50 hover:border-rose-300 hover:shadow-sm': !link.active && link.url,
                    'text-slate-400 border-rose-100 cursor-not-allowed': !link.url
                  }"
                  v-html="link.label"
                ></button>
              </div>
            </div>

            <!-- Pagination - Mobile -->
            <div v-if="rejectedOpportunities.links && rejectedOpportunities.links.length > 3 && !isDesktop" class="px-3 py-2 bg-white border-t border-rose-100/50 w-full">
              <div class="flex flex-wrap gap-1.5 justify-center">
                <button 
                  v-for="link in rejectedOpportunities.links"
                  :key="link.label"
                  :disabled="!link.url"
                  @click="visitPage(link.url)"
                  class="px-1.5 py-1 text-xs border rounded-lg transition-all duration-200 min-w-[32px] text-center"
                  :class="{
                    'bg-gradient-to-r from-rose-500 to-red-500 text-white border-transparent shadow-md': link.active,
                    'text-slate-600 border-rose-200 hover:bg-rose-50 hover:border-rose-300 hover:shadow-sm': !link.active && link.url,
                    'text-slate-400 border-rose-100 cursor-not-allowed': !link.url
                  }"
                  v-html="link.label === 'Next &raquo;' ? 'Next' : link.label === '&laquo; Previous' ? 'Prev' : link.label"
                ></button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  EyeIcon,
  TrashIcon,
  ArchiveBoxIcon,
  ArrowPathIcon,
  XMarkIcon,
  UserCircleIcon,
  EnvelopeIcon,
  PhoneIcon,
  MapPinIcon,
  BuildingOfficeIcon,
  PencilSquareIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  rejectedOpportunities: {
    type: Object,
    default: () => ({ data: [] })
  },
  permissions: {
    type: Object,
    default: () => ({})
  },
  tables: {
    type: Array,
    default: () => []
  },
  flash: {
    type: Object,
    default: () => ({})
  },
  cities: {
    type: Array,
    default: () => []
  },
  subcities: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  }
})

// Reactive filters
const filters = ref({
  city_id: props.filters.city_id || '',
  subcity_id: props.filters.subcity_id || '',
  source: props.filters.source || ''
})

// Responsive detection
const windowWidth = ref(window.innerWidth)
const isDesktop = computed(() => windowWidth.value >= 1024)

// Computed properties
const filteredSubcities = computed(() => {
  if (!filters.value.city_id) return []
  return props.subcities.filter(subcity => subcity.city_id == filters.value.city_id)
})

const hasActiveFilters = computed(() => {
  return filters.value.city_id || filters.value.subcity_id || filters.value.source
})

const activeFilterCount = computed(() => {
  let count = 0
  if (filters.value.city_id) count++
  if (filters.value.subcity_id) count++
  if (filters.value.source) count++
  return count
})

// Window resize handler
const updateWindowWidth = () => {
  windowWidth.value = window.innerWidth
}

onMounted(() => {
  window.addEventListener('resize', updateWindowWidth)
})

onUnmounted(() => {
  window.removeEventListener('resize', updateWindowWidth)
})

// Methods
const applyFilters = () => {
  router.get('/admin/rejected-opportunities', filters.value, {
    preserveState: true,
    replace: true
  })
}

const onCityChange = () => {
  // Reset subcity when city changes
  filters.value.subcity_id = ''
  applyFilters()
}

const clearFilters = () => {
  filters.value = {
    city_id: '',
    subcity_id: '',
    source: ''
  }
  applyFilters()
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Helper methods
const getSourceBadgeClass = (source) => {
  const classes = {
    opportunity: 'bg-blue-100 text-blue-800 border-blue-200',
    potential_customer: 'bg-teal-100 text-teal-800 border-teal-200',
    customer: 'bg-green-100 text-green-800 border-green-200'
  }
  return classes[source] || 'bg-slate-100 text-slate-800 border-slate-200'
}

const formatSource = (source) => {
  const sources = {
    opportunity: 'Opportunity',
    potential_customer: 'Potential Customer',
    customer: 'Customer'
  }
  return sources[source] || source
}

const getCityName = (cityId) => {
  const city = props.cities.find(c => c.id == cityId)
  return city ? city.name : 'Unknown'
}

const getSubcityName = (subcityId) => {
  const subcity = props.subcities.find(s => s.id == subcityId)
  return subcity ? subcity.name : 'Unknown'
}

const removeFilter = (filterKey) => {
  filters.value[filterKey] = ''
  applyFilters()
}

// Navigation methods
const viewItem = (id) => {
  router.get(`/admin/rejected-opportunities/${id}`)
}

const editItem = (id) => {
  router.get(`/admin/rejected-opportunities/${id}/edit`)
}

const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

const revertItem = (id, source) => {
  const sourceName = formatSource(source)
  if (confirm(`Are you sure you want to revert this item? It will be moved back to ${sourceName}.`)) {
    router.post(`/admin/rejected-opportunities/${id}/revert`, {}, {
      onSuccess: () => {
        // Success handled by controller
      },
      onError: (errors) => {
        alert('Failed to revert item. Please try again.')
      }
    })
  }
}

const deleteItem = (id) => {
  if (confirm('Are you sure you want to permanently delete this rejected opportunity?')) {
    router.delete(`/admin/rejected-opportunities/${id}`, {
      onSuccess: () => {
        // Success handled by controller
      },
      onError: (errors) => {
        alert('Failed to delete item. Please try again.')
      }
    })
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Smooth transitions */
.transition-all {
  transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1) !important;
}

/* Prevent horizontal scrolling on main container */
.overflow-x-hidden {
  overflow-x: hidden;
}

/* Ensure full width for all containers */
.w-full {
  width: 100%;
}

/* Table adjustments - fixed widths for columns */
table {
  table-layout: fixed;
  width: 100%;
}

/* Make table cells wrap properly */
td, th {
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Truncate long text */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Ensure content fits */
.max-w-full {
  max-width: 100%;
}

/* Prevent overflow */
.min-w-0 {
  min-width: 0;
}

/* Remove any horizontal scrollbar styles */
.overflow-x-auto::-webkit-scrollbar {
  display: none;
}
</style>