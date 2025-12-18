<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Mobile Sidebar Overlay -->
    <div v-if="mobileSidebarOpen" class="fixed inset-0 flex z-40 lg:hidden">
      <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="mobileSidebarOpen = false"></div>
      <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
        <div class="absolute top-0 right-0 -mr-12 pt-2">
          <button
            @click="mobileSidebarOpen = false"
            class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
          >
            <XMarkIcon class="h-6 w-6 text-white" />
          </button>
        </div>
        <Sidebar :tables="tables" />
      </div>
    </div>

    <!-- Static sidebar for desktop -->
    <div class="hidden lg:flex lg:flex-shrink-0">
      <div class="flex flex-col w-64">
        <Sidebar :tables="tables" />
      </div>
    </div>

    <div class="flex-1 min-w-0 flex flex-col overflow-hidden">
      <!-- Fixed Header Section -->
      <div class="sticky top-0 z-30 bg-white shadow-sm border-b border-gray-200">
        <!-- Mobile top header -->
        <div class="lg:hidden">
          <div class="flex items-center justify-between px-4 py-3">
            <button
              @click="mobileSidebarOpen = true"
              class="text-gray-500 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 p-1 rounded-lg"
            >
              <Bars3Icon class="h-6 w-6" />
            </button>
            <div class="flex-1 text-center">
              <h1 class="text-lg font-semibold text-gray-900 flex items-center justify-center gap-2">
                <span>Potential Customers</span>
                <div class="w-5 h-5 bg-gradient-to-r from-blue-400/30 to-purple-400/30 rounded flex items-center justify-center">
                  <UserGroupIcon class="w-3 h-3 text-blue-600/70" />
                </div>
              </h1>
            </div>
            <div class="w-6"></div>
          </div>
        </div>

        <!-- Desktop Header -->
        <div class="hidden lg:block">
          <div class="px-6 py-4">
            <div class="flex justify-between items-center">
              <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                  <span>Potential Customers</span>
                  <div class="w-7 h-7 bg-gradient-to-r from-blue-400/30 to-purple-400/30 rounded-lg flex items-center justify-center">
                    <UserGroupIcon class="w-4 h-4 text-blue-600/70" />
                  </div>
                </h1>
                <p class="text-gray-600 mt-1">Manage and review potential customer leads</p>
              </div>
            </div>
          </div>
        </div>
      </div>
      

      <!-- Main Content - Fixed height with overflow -->
      <div class="flex-1 overflow-hidden flex flex-col">
        <!-- Compact Filters Section -->
        <div class="bg-white border-b border-gray-200 px-4 py-3 lg:px-6 lg:py-4 filters-section">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <!-- Left side: Status Filters -->
            <div class="flex-1">
              <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-gray-700 hidden lg:block">Filter:</span>
                <div class="flex flex-wrap gap-2">
                  <button 
                    v-for="filter in statusFilters"
                    :key="filter.key"
                    @click="setStatusFilter(filter.key)"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 shadow-sm hover:shadow"
                    :class="getFilterButtonClass(filter.key)"
                  >
                    <span class="font-bold mr-1.5 text-white bg-white/20 px-1 py-0.5 rounded">
                      {{ getFilterCount(filter.key) }}
                    </span>
                    <span>{{ filter.label }}</span>
                    <component 
                      :is="getFilterIcon(filter.key)" 
                      class="ml-1.5 w-3 h-3" 
                    />
                  </button>
                </div>
              </div>
            </div>

            <!-- Right side: Location Filters -->
            <div class="flex items-center gap-3">
              <!-- Location Filters Dropdown -->
              <div class="relative">
                <button
                  @click="isFilterExpanded = !isFilterExpanded"
                  class="flex items-center gap-2 px-3 py-1.5 text-sm font-medium text-white bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all duration-200 shadow-sm hover:shadow"
                >
                  <MapPinIcon class="w-4 h-4" />
                  <span>Location Filter</span>
                  <ChevronDownIcon class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': isFilterExpanded}" />
                </button>

                <!-- Expanded Location Filters Dropdown -->
                <div v-if="isFilterExpanded" class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                  <div class="p-4">
                    <div class="flex items-center justify-between mb-3">
                      <h3 class="text-sm font-semibold text-gray-900">Location Filters</h3>
                      <button @click="isFilterExpanded = false" class="text-gray-400 hover:text-gray-600">
                        <XMarkIcon class="w-4 h-4" />
                      </button>
                    </div>
                    
                    <!-- City Filter -->
                    <div class="mb-4">
                      <label class="block text-xs font-medium text-gray-700 mb-2 flex items-center">
                        <BuildingOfficeIcon class="w-4 h-4 mr-1 text-blue-500" />
                        <span>City</span>
                      </label>
                      <div class="relative">
                        <select
                          v-model="cityFilter"
                          @change="onCityFilterChange"
                          class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white appearance-none"
                        >
                          <option value="">All Cities</option>
                          <option v-for="city in cities" :key="city.id" :value="city.id">
                            {{ city.name }}
                          </option>
                        </select>
                        <BuildingOfficeIcon class="absolute left-3 top-2.5 w-4 h-4 text-blue-500 pointer-events-none" />
                        <ChevronDownIcon class="absolute right-3 top-2.5 w-4 h-4 text-gray-400 pointer-events-none" />
                      </div>
                    </div>

                    <!-- Subcity Filter -->
                    <div class="mb-4">
                      <label class="block text-xs font-medium text-gray-700 mb-2 flex items-center">
                        <BuildingOffice2Icon class="w-4 h-4 mr-1 text-green-500" />
                        <span>Subcity</span>
                      </label>
                      <div class="relative">
                        <select
                          v-model="subcityFilter"
                          :disabled="!cityFilter || filteredSubcities.length === 0"
                          class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white appearance-none disabled:bg-gray-50 disabled:cursor-not-allowed"
                        >
                          <option value="">All Subcities</option>
                          <option v-for="subcity in filteredSubcities" :key="subcity.id" :value="subcity.id">
                            {{ subcity.name }}
                          </option>
                        </select>
                        <BuildingOffice2Icon :class="!cityFilter || filteredSubcities.length === 0 ? 'text-gray-300' : 'text-green-500'" 
                          class="absolute left-3 top-2.5 w-4 h-4 pointer-events-none" />
                        <ChevronDownIcon class="absolute right-3 top-2.5 w-4 h-4 text-gray-400 pointer-events-none" />
                      </div>
                      <p v-if="cityFilter && filteredSubcities.length === 0" class="text-xs text-gray-500 mt-1">
                        No subcities available for selected city
                      </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2 pt-3 border-t border-gray-200">
                      <button
                        @click="clearAllFilters"
                        class="flex-1 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors flex items-center justify-center gap-1"
                        :disabled="!hasActiveFilters"
                        :class="{'opacity-50 cursor-not-allowed': !hasActiveFilters}"
                      >
                        <ArrowPathIcon class="w-4 h-4" />
                        Clear All
                      </button>
                      <button
                        @click="applyFilters"
                        class="flex-1 px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
                      >
                        <CheckIcon class="w-4 h-4 inline mr-1" />
                        Apply
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Active Filters Badge -->
              <div v-if="hasActiveFilters" class="flex items-center">
                <button
                  @click="clearAllFilters"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-gradient-to-r from-red-500 to-orange-500 rounded-lg hover:from-red-600 hover:to-orange-600 transition-all duration-200 shadow-sm hover:shadow"
                >
                  <XMarkIcon class="w-3 h-3 mr-1" />
                  Clear Filters ({{ activeFilterCount }})
                </button>
              </div>
            </div>
          </div>

          <!-- Active Filters Tags -->
          <div v-if="hasActiveFilters && activeFilterTags.length > 0" class="mt-2">
            <div class="flex flex-wrap gap-1.5">
              <div 
                v-for="tag in activeFilterTags"
                :key="tag.key"
                class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium border shadow-sm"
                :class="tag.class"
              >
                <component :is="tag.icon" class="w-3 h-3 mr-1.5" />
                <span>{{ tag.label }}</span>
                <button @click="tag.remove()" class="ml-1.5 hover:opacity-75">
                  <XMarkIcon class="w-3 h-3" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Area with Scrolling -->
        <div class="flex-1 overflow-auto">
          <div class="p-4 lg:p-6">
            <!-- Loading State -->
            <div v-if="loading" class="flex items-center justify-center p-8">
              <div class="text-center">
                <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-gray-600">Loading potential customers...</p>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!hasCustomers" class="bg-white rounded-lg border border-gray-200 p-6 lg:p-8 text-center">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center mx-auto mb-4">
                <UserGroupIcon class="w-8 h-8 text-blue-600" />
              </div>
              <h3 class="text-lg font-semibold text-gray-900 mb-2">No potential customers found</h3>
              <p class="text-gray-600 mb-4 max-w-md mx-auto text-sm">
                Potential customer leads will appear here after approval from opportunities.
              </p>
              <button 
                @click="clearAllFilters"
                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-medium rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow"
              >
                <ArrowPathIcon class="w-4 h-4 mr-2" />
                Clear Filters
              </button>
            </div>

            <!-- Customers Table -->
            <div v-else class="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
              <!-- Table Summary -->
              <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                  <div>
                    <h3 class="text-sm font-semibold text-gray-900">Customer Leads</h3>
                    <p class="text-gray-600 text-xs mt-0.5">
                      Showing {{ filteredCustomers.length }} of {{ totalCustomers }} results
                      <span v-if="statusFilter !== 'all'" class="ml-2 px-1.5 py-0.5 rounded text-xs" :class="getFilterButtonClass(statusFilter)">
                        {{ getStatusLabel(statusFilter) }}
                      </span>
                    </p>
                  </div>
                  <div class="text-xs text-gray-500">
                    Page {{ potentialCustomers.meta?.current_page || 1 }} of {{ potentialCustomers.meta?.last_page || 1 }}
                  </div>
                </div>
              </div>
              
              <!-- Table Container -->
              <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                  <div class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                      <thead class="bg-gray-50">
                        <tr>
                          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                              <UserCircleIcon class="w-4 h-4 mr-2 text-blue-500" />
                              <span>Customer</span>
                            </div>
                          </th>
                          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                              <EnvelopeIcon class="w-4 h-4 mr-2 text-green-500" />
                              <span>Contact</span>
                            </div>
                          </th>
                          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                              <MapPinIcon class="w-4 h-4 mr-2 text-purple-500" />
                              <span>Location</span>
                            </div>
                          </th>
                          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                              <TagIcon class="w-4 h-4 mr-2 text-orange-500" />
                              <span>Status</span>
                            </div>
                          </th>
                          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                              <DocumentTextIcon class="w-4 h-4 mr-2 text-indigo-500" />
                              <span>Proposals</span>
                            </div>
                          </th>
                          <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center">
                              <Cog6ToothIcon class="w-4 h-4 mr-2 text-gray-500" />
                              <span>Actions</span>
                            </div>
                          </th>
                        </tr>
                      </thead>
                      <tbody class="bg-white divide-y divide-gray-200">
                        <tr 
                          v-for="customer in filteredCustomers" 
                          :key="customer?.id" 
                          class="hover:bg-blue-50/30 transition-all duration-150"
                        >
                          <!-- Customer Column -->
                          <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center">
                              <div class="relative">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-sm">
                                  <UserCircleIcon class="w-4 h-4 text-white" />
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-white rounded-full border border-white shadow-sm">
                                  <div class="w-full h-full rounded-full" :class="getStatusDotClass(customer?.status)"></div>
                                </div>
                              </div>
                              <div class="ml-3">
                                <div class="font-semibold text-gray-900 text-sm truncate max-w-[120px]">
                                  {{ customer?.potential_customer_name || 'Unknown Customer' }}
                                </div>
                                <div class="text-gray-500 text-xs truncate max-w-[120px]">
                                  ID: {{ customer?.id || 'N/A' }}
                                </div>
                              </div>
                            </div>
                          </td>

                          <!-- Contact Column -->
                          <td class="px-4 py-3 whitespace-nowrap">
                            <div class="space-y-1">
                              <div class="flex items-center text-gray-900 text-sm">
                                <EnvelopeIcon class="w-3 h-3 mr-1.5 text-gray-400 flex-shrink-0" />
                                <span class="truncate max-w-[120px]">{{ customer?.email || 'No email' }}</span>
                              </div>
                              <div class="flex items-center text-gray-500 text-xs">
                                <PhoneIcon class="w-3 h-3 mr-1.5 text-gray-400 flex-shrink-0" />
                                <span class="truncate max-w-[120px]">{{ customer?.phone || 'No phone' }}</span>
                              </div>
                            </div>
                          </td>

                          <!-- Location Column -->
                          <td class="px-4 py-3 whitespace-nowrap">
                            <div class="space-y-1">
                              <div class="flex items-center text-gray-900 text-sm">
                                <BuildingOfficeIcon class="w-3 h-3 mr-1.5 text-blue-500 flex-shrink-0" />
                                <span class="truncate max-w-[100px]">{{ customer?.city?.name || 'No city' }}</span>
                              </div>
                              <div class="flex items-center text-gray-500 text-xs">
                                <BuildingOffice2Icon class="w-3 h-3 mr-1.5 text-gray-400 flex-shrink-0" />
                                <span class="truncate max-w-[100px]">{{ customer?.subcity?.name || 'No subcity' }}</span>
                              </div>
                            </div>
                          </td>

                          <!-- Status Column -->
                          <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex flex-col">
                              <span :class="getStatusBadgeClass(customer?.status)" 
                                class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-semibold rounded-lg shadow-sm">
                                <component :is="getFilterIcon(customer?.status)" class="w-3 h-3 mr-1.5" />
                                {{ formatStatus(customer?.status) }}
                              </span>
                              <div class="mt-1 text-xs text-gray-400">
                                {{ formatDate(customer?.updated_at) }}
                              </div>
                            </div>
                          </td>

                          <!-- Proposals Column -->
                          <td class="px-4 py-3 whitespace-nowrap">
                            <div v-if="customer?.proposals && customer.proposals.length > 0" class="space-y-2">
                              <div v-for="proposal in customer.proposals.slice(0, 1)" :key="proposal.id">
                                <div class="bg-gradient-to-r from-gray-50 to-white border border-gray-200 rounded-lg p-2 shadow-sm">
                                  <div class="flex justify-between items-center mb-2">
                                    <span class="font-bold text-gray-900 text-xs truncate max-w-[80px]">
                                      {{ proposal.title }}
                                    </span>
                                    <span class="font-bold text-gray-900 text-xs bg-gradient-to-r from-yellow-100 to-yellow-50 px-2 py-1 rounded">
                                      ${{ proposal.price }}
                                    </span>
                                  </div>
                                  <div class="w-full bg-gray-200 rounded-full h-1.5 mb-1.5">
                                    <div 
                                      :class="getProposalProgressClass(proposal)"
                                      class="h-1.5 rounded-full transition-all duration-500 shadow-sm"
                                    ></div>
                                  </div>
                                  <div class="flex justify-between items-center">
                                    <span class="text-xs font-medium" :class="getProposalStatusClass(proposal)">
                                      {{ formatProposalStatus(proposal) }}
                                    </span>
                                    <DocumentTextIcon class="w-3 h-3 text-gray-400" />
                                  </div>
                                </div>
                              </div>
                              <div v-if="customer.proposals.length > 1" class="text-center">
                                <button 
                                  @click="viewCustomerDetails(customer)"
                                  class="text-xs font-medium bg-gradient-to-r from-blue-50 to-blue-100 text-blue-700 px-2 py-1 rounded hover:from-blue-100 hover:to-blue-200 transition-all duration-200"
                                >
                                  +{{ customer.proposals.length - 1 }} more
                                </button>
                              </div>
                            </div>
                            <div v-else class="text-center py-2">
                              <div class="w-8 h-8 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-1">
                                <DocumentTextIcon class="w-4 h-4 text-gray-400" />
                              </div>
                              <span class="text-xs text-gray-500">No proposals</span>
                            </div>
                          </td>

                          <!-- Actions Column -->
                          <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex gap-1">
                              <button 
                                @click="viewCustomerDetails(customer)"
                                class="p-1.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow"
                                title="View Details"
                              >
                                <EyeIcon class="w-4 h-4" />
                              </button>
                              <button 
                                v-if="permissions.edit"
                                @click="openEditModal(customer)"
                                class="p-1.5 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg hover:from-green-600 hover:to-emerald-600 transition-all duration-200 shadow-sm hover:shadow"
                                title="Edit Customer"
                              >
                                <PencilIcon class="w-4 h-4" />
                              </button>
                              <button 
                                v-if="permissions.delete"
                                @click="deleteCustomer(customer.id)"
                                class="p-1.5 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-lg hover:from-red-600 hover:to-pink-600 transition-all duration-200 shadow-sm hover:shadow"
                                title="Delete Customer"
                              >
                                <TrashIcon class="w-4 h-4" />
                              </button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              <!-- Pagination -->
              <div v-if="potentialCustomers.links && potentialCustomers.links.length > 3" class="px-4 py-3 bg-gradient-to-r from-gray-50 to-white border-t border-gray-200">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                  <div class="text-xs text-gray-600">
                    Showing {{ potentialCustomers.meta?.from || 0 }}-{{ potentialCustomers.meta?.to || 0 }} of {{ potentialCustomers.meta?.total || 0 }}
                  </div>
                  <div class="flex items-center gap-1">
                    <!-- Previous Page -->
                    <button 
                      v-if="potentialCustomers.links[0].url"
                      @click="visitPage(potentialCustomers.links[0].url)"
                      class="p-2 text-gray-600 hover:text-white hover:bg-gradient-to-r hover:from-blue-500 hover:to-blue-600 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
                      title="Previous"
                    >
                      <ChevronLeftIcon class="w-4 h-4" />
                    </button>

                    <!-- Page Numbers -->
                    <div class="flex items-center gap-1">
                      <button 
                        v-for="link in potentialCustomers.links.slice(1, -1)"
                        :key="link.label"
                        :disabled="!link.url"
                        @click="visitPage(link.url)"
                        class="min-w-[32px] h-8 px-2 text-sm font-medium rounded-lg transition-all duration-200 shadow-sm"
                        :class="link.active 
                          ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow' 
                          : 'text-gray-600 bg-white border border-gray-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100 hover:border-blue-300'"
                        v-html="link.label"
                      ></button>
                    </div>

                    <!-- Next Page -->
                    <button 
                      v-if="potentialCustomers.links[potentialCustomers.links.length - 1].url"
                      @click="visitPage(potentialCustomers.links[potentialCustomers.links.length - 1].url)"
                      class="p-2 text-gray-600 hover:text-white hover:bg-gradient-to-r hover:from-blue-500 hover:to-blue-600 rounded-lg transition-all duration-200 shadow-sm hover:shadow"
                      title="Next"
                    >
                      <ChevronRightIcon class="w-4 h-4" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Customer Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto border border-gray-200 shadow-xl">
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-200">
          <div>
            <h2 class="text-2xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
              Edit Potential Customer
            </h2>
            <p class="text-gray-600 text-sm mt-1">Update customer information and details</p>
          </div>
          <button 
            @click="closeEditModal"
            class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100 transition-colors"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>

        <!-- Success/Error Messages -->
        <div v-if="successMessage" class="mb-4">
          <div class="bg-green-50 border-l-4 border-green-500 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <CheckCircleIcon class="h-5 w-5 text-green-400" />
              </div>
              <div class="ml-3">
                <p class="text-sm text-green-700">{{ successMessage }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="errorMessage" class="mb-4">
          <div class="bg-red-50 border-l-4 border-red-500 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <XCircleIcon class="h-5 w-5 text-red-400" />
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-700">{{ errorMessage }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitEdit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Customer Name -->
            <div class="md:col-span-2">
              <label for="potential_customer_name" class="block text-sm font-medium text-gray-700 mb-2">
                Customer Name *
              </label>
              <input
                type="text"
                id="potential_customer_name"
                v-model="editForm.potential_customer_name"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
                required
              />
              <div v-if="editForm.errors.potential_customer_name" class="text-red-600 text-sm mt-1">
                {{ editForm.errors.potential_customer_name }}
              </div>
            </div>

            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                Email Address
              </label>
              <input
                type="email"
                id="email"
                v-model="editForm.email"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
              />
              <div v-if="editForm.errors.email" class="text-red-600 text-sm mt-1">
                {{ editForm.errors.email }}
              </div>
            </div>

            <!-- Phone -->
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                Phone Number
              </label>
              <input
                type="tel"
                id="phone"
                v-model="editForm.phone"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
              />
              <div v-if="editForm.errors.phone" class="text-red-600 text-sm mt-1">
                {{ editForm.errors.phone }}
              </div>
            </div>

            <!-- Location -->
            <div class="md:col-span-2">
              <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                Location
              </label>
              <input
                type="text"
                id="location"
                v-model="editForm.location"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
              />
              <div v-if="editForm.errors.location" class="text-red-600 text-sm mt-1">
                {{ editForm.errors.location }}
              </div>
            </div>

            <!-- City -->
            <div>
              <label for="city_id" class="block text-sm font-medium text-gray-700 mb-2">
                City
              </label>
              <select
                id="city_id"
                v-model="editForm.city_id"
                @change="onCityChange"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
              >
                <option value="">Select City</option>
                <option v-for="city in cities" :key="city.id" :value="city.id">
                  {{ city.name }}
                </option>
              </select>
              <div v-if="editForm.errors.city_id" class="text-red-600 text-sm mt-1">
                {{ editForm.errors.city_id }}
              </div>
            </div>

            <!-- Subcity -->
            <div>
              <label for="subcity_id" class="block text-sm font-medium text-gray-700 mb-2">
                Subcity
              </label>
              <select
                id="subcity_id"
                v-model="editForm.subcity_id"
                :disabled="!editForm.city_id"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                <option value="">Select Subcity</option>
                <option v-for="subcity in availableSubcities" :key="subcity.id" :value="subcity.id">
                  {{ subcity.name }}
                </option>
              </select>
              <div v-if="editForm.errors.subcity_id" class="text-red-600 text-sm mt-1">
                {{ editForm.errors.subcity_id }}
              </div>
            </div>

            <!-- Remarks -->
            <div class="md:col-span-2">
              <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                Remarks
              </label>
              <textarea
                id="remarks"
                v-model="editForm.remarks"
                rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 resize-none"
                placeholder="Add any additional remarks or notes about this customer..."
              ></textarea>
              <div v-if="editForm.errors.remarks" class="text-red-600 text-sm mt-1">
                {{ editForm.errors.remarks }}
              </div>
            </div>

            <!-- Status -->
            <div class="md:col-span-2">
              <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                Status
              </label>
              <select
                id="status"
                v-model="editForm.status"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200"
              >
                <option value="draft">Draft</option>
                <option value="proposal_sent">Proposal Sent</option>
                <option value="accepted">Accepted</option>
                <option value="rejected">Rejected</option>
              </select>
              <div v-if="editForm.errors.status" class="text-red-600 text-sm mt-1">
                {{ editForm.errors.status }}
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex flex-wrap gap-3 mt-8 pt-6 border-t border-gray-200">
            <button
              type="submit"
              :disabled="editForm.processing"
              class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg"
            >
              <CheckIcon class="w-4 h-4" />
              <span>{{ editForm.processing ? 'Updating...' : 'Update Customer' }}</span>
            </button>
            
            <button
              type="button"
              @click="closeEditModal"
              class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg"
            >
              <XMarkIcon class="w-4 h-4" />
              <span>Cancel</span>
            </button>

            <button
              type="button"
              @click="resetEditForm"
              class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow hover:shadow-lg"
            >
              <ArrowPathIcon class="w-4 h-4" />
              <span>Reset</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  EyeIcon,
  UserGroupIcon,
  UserCircleIcon,
  PencilIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  XMarkIcon,
  Bars3Icon,
  TrashIcon,
  FunnelIcon,
  ChevronDownIcon,
  MapPinIcon,
  BuildingOfficeIcon,
  BuildingOffice2Icon,
  ArrowPathIcon,
  CheckIcon,
  TagIcon,
  EnvelopeIcon,
  PhoneIcon,
  DocumentTextIcon,
  Cog6ToothIcon,
  // Status icons
  DocumentIcon,
  PaperAirplaneIcon,
  CheckCircleIcon,
  XCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  potentialCustomers: Object,
  permissions: Object,
  tables: Array,
  cities: Array,
  subcities: Array
})

// State
const mobileSidebarOpen = ref(false)
const showEditModal = ref(false)
const selectedCustomer = ref(null)
const statusFilter = ref('all')
const cityFilter = ref('')
const subcityFilter = ref('')
const loading = ref(false)
const isFilterExpanded = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

// Forms
const editForm = reactive({
  potential_customer_name: '',
  email: '',
  phone: '',
  location: '',
  remarks: '',
  status: 'draft',
  city_id: '',
  subcity_id: '',
  processing: false,
  errors: {}
})

// Status filters
const statusFilters = [
  { key: 'all', label: 'All Leads' },
  { key: 'draft', label: 'Draft' },
  { key: 'proposal_sent', label: 'Proposal Sent' },
  { key: 'accepted', label: 'Accepted' },
  { key: 'rejected', label: 'Rejected' }
]

// Computed properties
const hasCustomers = computed(() => {
  return props.potentialCustomers?.data?.length > 0
})

const totalCustomers = computed(() => {
  return props.potentialCustomers?.data?.length || 0
})

const draftCount = computed(() => {
  return props.potentialCustomers?.data?.filter(c => c?.status === 'draft').length || 0
})

const proposalSentCount = computed(() => {
  return props.potentialCustomers?.data?.filter(c => c?.status === 'proposal_sent').length || 0
})

const acceptedCount = computed(() => {
  return props.potentialCustomers?.data?.filter(c => c?.status === 'accepted').length || 0
})

const rejectedCount = computed(() => {
  return props.potentialCustomers?.data?.filter(c => c?.status === 'rejected').length || 0
})

const activeFilterCount = computed(() => {
  let count = 0
  if (statusFilter.value !== 'all') count++
  if (cityFilter.value) count++
  if (subcityFilter.value) count++
  return count
})

const filteredSubcities = computed(() => {
  if (!cityFilter.value) return []
  return props.subcities.filter(subcity => subcity.city_id == cityFilter.value)
})

const availableSubcities = computed(() => {
  if (!editForm.city_id) return []
  return props.subcities.filter(subcity => subcity.city_id == editForm.city_id)
})

const filteredCustomers = computed(() => {
  if (!props.potentialCustomers?.data) return []
  
  let filtered = props.potentialCustomers.data.filter(customer => customer != null)
  
  if (statusFilter.value !== 'all') {
    filtered = filtered.filter(customer => customer?.status === statusFilter.value)
  }
  
  if (cityFilter.value) {
    filtered = filtered.filter(customer => customer?.city_id == cityFilter.value)
  }
  
  if (subcityFilter.value) {
    filtered = filtered.filter(customer => customer?.subcity_id == subcityFilter.value)
  }
  
  return filtered
})

const hasActiveFilters = computed(() => {
  return statusFilter.value !== 'all' || cityFilter.value || subcityFilter.value
})

const activeFilterTags = computed(() => {
  const tags = []
  
  if (statusFilter.value !== 'all') {
    tags.push({
      key: 'status',
      label: `Status: ${getStatusLabel(statusFilter.value)}`,
      icon: getFilterIcon(statusFilter.value),
      class: 'bg-blue-50 text-blue-700 border-blue-200',
      remove: () => statusFilter.value = 'all'
    })
  }
  
  if (cityFilter.value) {
    tags.push({
      key: 'city',
      label: `City: ${getCityName(cityFilter.value)}`,
      icon: BuildingOfficeIcon,
      class: 'bg-green-50 text-green-700 border-green-200',
      remove: () => {
        cityFilter.value = ''
        subcityFilter.value = ''
      }
    })
  }
  
  if (subcityFilter.value) {
    tags.push({
      key: 'subcity',
      label: `Subcity: ${getSubcityName(subcityFilter.value)}`,
      icon: BuildingOffice2Icon,
      class: 'bg-purple-50 text-purple-700 border-purple-200',
      remove: () => subcityFilter.value = ''
    })
  }
  
  return tags
})

// Helper methods
function getFilterCount(filterKey) {
  switch (filterKey) {
    case 'all': return totalCustomers.value
    case 'draft': return draftCount.value
    case 'proposal_sent': return proposalSentCount.value
    case 'accepted': return acceptedCount.value
    case 'rejected': return rejectedCount.value
    default: return 0
  }
}

function getFilterButtonClass(filterKey) {
  if (filterKey === statusFilter.value) {
    const activeColors = {
      all: 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow',
      draft: 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow',
      proposal_sent: 'bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow',
      accepted: 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow',
      rejected: 'bg-gradient-to-r from-red-500 to-red-600 text-white shadow'
    }
    return activeColors[filterKey] || 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow'
  }
  
  const inactiveColors = {
    all: 'bg-white text-gray-600 border border-gray-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100 hover:border-blue-300',
    draft: 'bg-white text-blue-600 border border-blue-200 hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100',
    proposal_sent: 'bg-white text-orange-600 border border-orange-200 hover:bg-gradient-to-r hover:from-orange-50 hover:to-orange-100',
    accepted: 'bg-white text-green-600 border border-green-200 hover:bg-gradient-to-r hover:from-green-50 hover:to-green-100',
    rejected: 'bg-white text-red-600 border border-red-200 hover:bg-gradient-to-r hover:from-red-50 hover:to-red-100'
  }
  return inactiveColors[filterKey] || 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50'
}

function getFilterIcon(filterKey) {
  const icons = {
    all: FunnelIcon,
    draft: DocumentIcon,
    proposal_sent: PaperAirplaneIcon,
    accepted: CheckCircleIcon,
    rejected: XCircleIcon
  }
  return icons[filterKey] || FunnelIcon
}

function getStatusBadgeClass(status) {
  if (!status) return 'bg-gray-100 text-gray-800 shadow-sm'
  
  const classes = {
    draft: 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 shadow-sm',
    proposal_sent: 'bg-gradient-to-r from-orange-100 to-orange-200 text-orange-800 shadow-sm',
    accepted: 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 shadow-sm',
    rejected: 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 shadow-sm'
  }
  return classes[status] || 'bg-gray-100 text-gray-800 shadow-sm'
}

function getStatusDotClass(status) {
  if (!status) return 'bg-gray-400'
  
  const classes = {
    draft: 'bg-gradient-to-r from-blue-400 to-blue-500',
    proposal_sent: 'bg-gradient-to-r from-orange-400 to-orange-500',
    accepted: 'bg-gradient-to-r from-green-400 to-green-500',
    rejected: 'bg-gradient-to-r from-red-400 to-red-500'
  }
  return classes[status] || 'bg-gray-400'
}

function getProposalProgressClass(proposal) {
  if (proposal.is_rejected) {
    return 'w-full bg-gradient-to-r from-red-500 to-red-600'
  }
  
  if (proposal.status === 'signed') {
    return 'w-full bg-gradient-to-r from-green-500 to-green-600'
  }
  
  if (proposal.status === 'unsigned') {
    return 'w-1/2 bg-gradient-to-r from-yellow-500 to-yellow-600'
  }
  
  return 'w-0 bg-gray-400'
}

function getProposalStatusClass(proposal) {
  if (proposal.is_rejected) {
    return 'text-red-600'
  }
  
  if (proposal.status === 'signed') {
    return 'text-green-600'
  }
  
  if (proposal.status === 'unsigned') {
    return 'text-yellow-600'
  }
  
  return 'text-gray-600'
}

function getStatusLabel(status) {
  const statusMap = {
    all: 'All Leads',
    draft: 'Draft',
    proposal_sent: 'Proposal Sent',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return statusMap[status] || status
}

function getCityName(cityId) {
  const city = props.cities.find(c => c.id == cityId)
  return city?.name || 'Unknown City'
}

function getSubcityName(subcityId) {
  const subcity = props.subcities.find(s => s.id == subcityId)
  return subcity?.name || 'Unknown Subcity'
}

function formatStatus(status) {
  if (!status) return 'Unknown'
  
  const statusMap = {
    draft: 'Draft',
    proposal_sent: 'Proposal Sent',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return statusMap[status] || status
}

function formatProposalStatus(proposal) {
  if (proposal.is_rejected) {
    return 'Rejected'
  }
  
  const statusMap = {
    unsigned: 'Unsigned',
    signed: 'Signed'
  }
  return statusMap[proposal.status] || proposal.status
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    year: date.getFullYear() !== new Date().getFullYear() ? 'numeric' : undefined
  })
}

// Navigation methods
const visitPage = (url) => {
  if (url) {
    loading.value = true
    router.get(url, {}, {
      onFinish: () => {
        loading.value = false
      }
    })
  }
}

const setStatusFilter = (filter) => {
  statusFilter.value = filter
}

const onCityFilterChange = () => {
  subcityFilter.value = ''
  isFilterExpanded.value = false
}

const applyFilters = () => {
  isFilterExpanded.value = false
}

const clearAllFilters = () => {
  statusFilter.value = 'all'
  cityFilter.value = ''
  subcityFilter.value = ''
  isFilterExpanded.value = false
}

const viewCustomerDetails = (customer) => {
  if (customer?.id) {
    router.get(`/admin/potential-customers/${customer.id}`)
  }
}

const deleteCustomer = (customerId) => {
  if (confirm('Are you sure you want to delete this potential customer?')) {
    loading.value = true
    router.delete(`/admin/potential-customers/${customerId}`, {
      preserveScroll: true,
      onSuccess: () => {
        loading.value = false
        router.reload()
      },
      onError: () => {
        loading.value = false
        alert('Failed to delete customer. Please try again.')
      }
    })
  }
}

// Edit Modal Methods
const openEditModal = (customer) => {
  selectedCustomer.value = customer
  editForm.potential_customer_name = customer.potential_customer_name || ''
  editForm.email = customer.email || ''
  editForm.phone = customer.phone || ''
  editForm.location = customer.location || ''
  editForm.remarks = customer.remarks || ''
  editForm.status = customer.status || 'draft'
  editForm.city_id = customer.city_id || ''
  editForm.subcity_id = customer.subcity_id || ''
  editForm.processing = false
  editForm.errors = {}
  successMessage.value = ''
  errorMessage.value = ''
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  selectedCustomer.value = null
  editForm.errors = {}
  successMessage.value = ''
  errorMessage.value = ''
}

const onCityChange = () => {
  editForm.subcity_id = ''
}

const submitEdit = () => {
  if (!selectedCustomer.value?.id) return

  editForm.processing = true
  editForm.errors = {}
  successMessage.value = ''
  errorMessage.value = ''

  router.put(`/admin/potential-customers/${selectedCustomer.value.id}`, editForm, {
    preserveScroll: true,
    onSuccess: () => {
      editForm.processing = false
      successMessage.value = 'Customer updated successfully!'
      
      // Refresh the page data after a short delay
      setTimeout(() => {
        router.reload({ only: ['potentialCustomers'] })
        closeEditModal()
      }, 1500)
    },
    onError: (errors) => {
      editForm.processing = false
      editForm.errors = errors
      errorMessage.value = 'Please fix the errors in the form.'
    }
  })
}

const resetEditForm = () => {
  if (selectedCustomer.value) {
    editForm.potential_customer_name = selectedCustomer.value.potential_customer_name || ''
    editForm.email = selectedCustomer.value.email || ''
    editForm.phone = selectedCustomer.value.phone || ''
    editForm.location = selectedCustomer.value.location || ''
    editForm.remarks = selectedCustomer.value.remarks || ''
    editForm.status = selectedCustomer.value.status || 'draft'
    editForm.city_id = selectedCustomer.value.city_id || ''
    editForm.subcity_id = selectedCustomer.value.subcity_id || ''
  }
  editForm.errors = {}
  successMessage.value = ''
  errorMessage.value = ''
}

// Close dropdown when clicking outside
onMounted(() => {
  const handleClickOutside = (event) => {
    if (isFilterExpanded.value && !event.target.closest('.filters-section')) {
      isFilterExpanded.value = false
    }
  }
  
  document.addEventListener('click', handleClickOutside)
  
  return () => {
    document.removeEventListener('click', handleClickOutside)
  }
})
</script>

<style>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3b82f6, #8b5cf6);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563eb, #7c3aed);
}

/* Smooth transitions */
* {
  transition-property: background-color, border-color, color, fill, stroke, opacity, box-shadow, transform, background-image;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Gradient text for active status */
.gradient-text {
  background: linear-gradient(to right, #3b82f6, #8b5cf6);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
</style>