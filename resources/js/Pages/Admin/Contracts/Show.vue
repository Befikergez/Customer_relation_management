<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-teal-50 flex">
    <!-- Sidebar - With higher z-index to appear above header -->
    <Sidebar :tables="tables" />
    
    <div class="flex-1 min-w-0 flex flex-col overflow-hidden w-full">
      <!-- Fixed Header Section - Lower z-index than sidebar -->
      <div 
        id="header-section"
        class="sticky top-0 z-20 bg-white/90 backdrop-blur-lg border-b border-blue-200/30 shadow-sm transition-all duration-300 ease-in-out"
        :class="{
          'md:translate-x-0': isSidebarOpen,
          'translate-x-0': !isSidebarOpen
        }"
      >
        <!-- Mobile/Tablet Header -->
        <div class="lg:hidden">
          <div class="flex items-center justify-between px-4 py-3">
            <!-- Mobile spacing for sidebar hamburger button -->
            <div class="w-12 flex-shrink-0"></div>
            
            <!-- Center: Title -->
            <div class="flex-1 text-center min-w-0">
              <h1 class="text-lg font-semibold text-gray-900 flex items-center justify-center gap-2 truncate">
                <span class="truncate">Contract Details</span>
                <div class="w-5 h-5 bg-gradient-to-r from-blue-500/30 to-teal-500/30 rounded flex items-center justify-center flex-shrink-0">
                  <DocumentTextIcon class="w-3 h-3 text-blue-600/70" />
                </div>
              </h1>
              <p class="text-gray-600 text-xs mt-0.5 truncate">View and manage contract information</p>
            </div>
            
            <!-- Right spacer for balance -->
            <div class="w-12 flex-shrink-0"></div>
          </div>
        </div>

        <!-- Desktop Header (1024px and above) -->
        <div class="hidden lg:block">
          <div class="px-4 lg:px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
              <div class="flex items-center space-x-4">
                <button 
                  @click="goToCustomer"
                  class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-sm sm:text-base"
                >
                  <ArrowLeftIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                  <span>Back to Customer</span>
                </button>
                <div class="min-w-0">
                  <h1 class="text-xl lg:text-2xl font-bold text-gray-900 truncate">Contract Details</h1>
                  <p class="text-gray-600 text-sm lg:text-base mt-1 truncate">View and manage contract information</p>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <span :class="getStatusBadgeClass(contract?.status)" class="px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm">
                  {{ formatStatus(contract?.status) }}
                </span>
                <button 
                  v-if="contract?.file"
                  @click="downloadFile"
                  class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-xs sm:text-sm"
                >
                  <ArrowDownTrayIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                  <span>Download File</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading.contract" class="flex-1 flex items-center justify-center">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-teal-600 mx-auto"></div>
          <p class="mt-4 text-gray-600 text-sm sm:text-base">Loading contract details...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="flex-1 flex items-center justify-center">
        <div class="text-center max-w-md p-4 sm:p-6">
          <ExclamationCircleIcon class="h-12 w-12 sm:h-16 sm:w-16 text-red-500 mx-auto mb-3 sm:mb-4" />
          <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">Contract Not Found</h3>
          <p class="text-gray-600 text-sm sm:text-base mb-4 sm:mb-6">The contract you're looking for could not be found or you don't have permission to view it.</p>
          <button 
            @click="goToCustomer"
            class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 py-2.5 sm:px-6 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 mx-auto text-sm sm:text-base"
          >
            <ArrowLeftIcon class="w-4 h-4" />
            <span>Back to Customer</span>
          </button>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else class="flex-1 overflow-hidden flex flex-col">
        <!-- Flash Messages -->
        <div v-if="flashMessage" class="m-3 sm:m-4 lg:m-6">
          <div :class="flashMessageClass" class="rounded-lg p-4 shadow-sm border">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2 sm:space-x-3">
                <CheckCircleIcon v-if="flashMessageType === 'success'" class="w-5 h-5 text-green-500" />
                <ExclamationCircleIcon v-else class="w-5 h-5 text-red-500" />
                <p class="font-medium text-sm sm:text-base">{{ flashMessage }}</p>
              </div>
              <button 
                @click="clearFlashMessage" 
                class="text-gray-400 hover:text-gray-600"
              >
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto">
          <div class="pt-4 px-4 sm:px-6 lg:px-8 pb-6 sm:pb-8">
            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">
              <!-- Left Column: Contract Information -->
              <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                <!-- Contract Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                  <div class="flex items-center space-x-3 sm:space-x-4 mb-4 sm:mb-6">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl sm:rounded-2xl flex items-center justify-center">
                      <DocumentTextIcon class="w-6 h-6 sm:w-8 sm:h-8 text-white" />
                    </div>
                    <div class="flex-1 min-w-0">
                      <h2 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">{{ contract?.contract_title || 'Untitled Contract' }}</h2>
                      <p class="text-gray-600 text-sm sm:text-base">Contract ID: #{{ contract?.id || 'N/A' }}</p>
                      <div class="flex items-center flex-wrap gap-1 sm:gap-2 mt-1 sm:mt-2">
                        <span class="text-xs sm:text-sm text-gray-500">Created by:</span>
                        <span class="text-xs sm:text-sm font-medium text-gray-700">{{ contract?.created_by?.name || 'N/A' }}</span>
                        <span class="text-xs sm:text-sm text-gray-500">on</span>
                        <span class="text-xs sm:text-sm font-medium text-gray-700">{{ formatDate(contract?.created_at) }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Contract Details -->
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div class="space-y-3 sm:space-y-4">
                      <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Customer</label>
                        <div v-if="contract?.customer" class="flex items-center space-x-2">
                          <UserCircleIcon class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                          <div class="min-w-0">
                            <p class="font-medium text-gray-900 text-sm sm:text-base truncate">{{ contract.customer.name || 'N/A' }}</p>
                            <p class="text-xs sm:text-sm text-gray-500 truncate">{{ contract.customer.email || 'N/A' }}</p>
                          </div>
                        </div>
                        <p v-else class="text-gray-500 text-sm sm:text-base">No customer assigned</p>
                      </div>
                      
                      <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Proposal</label>
                        <div v-if="contract?.proposal" class="flex items-center space-x-2">
                          <DocumentTextIcon class="w-4 h-4 sm:w-5 sm:h-5 text-gray-400" />
                          <div class="min-w-0">
                            <p class="font-medium text-gray-900 text-sm sm:text-base truncate">{{ contract.proposal.title || 'N/A' }}</p>
                            <p class="text-xs sm:text-sm text-gray-500">${{ formatNumber(contract.proposal.price) }}</p>
                          </div>
                        </div>
                        <p v-else class="text-gray-500 text-sm sm:text-base">No linked proposal</p>
                      </div>
                      
                      <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Contract Value</label>
                        <p class="text-lg sm:text-2xl font-bold text-teal-600">${{ formatNumber(contract?.total_value) }}</p>
                      </div>
                    </div>
                    
                    <div class="space-y-3 sm:space-y-4">
                      <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Contract Period</label>
                        <div class="space-y-1.5 sm:space-y-2">
                          <div class="flex justify-between">
                            <span class="text-gray-600 text-xs sm:text-sm">Start Date:</span>
                            <span class="font-medium text-gray-900 text-xs sm:text-sm">{{ formatDate(contract?.start_date) }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-600 text-xs sm:text-sm">End Date:</span>
                            <span class="font-medium text-gray-900 text-xs sm:text-sm">{{ formatDate(contract?.end_date) }}</span>
                          </div>
                          <div class="flex justify-between">
                            <span class="text-gray-600 text-xs sm:text-sm">Duration:</span>
                            <span class="font-medium text-gray-900 text-xs sm:text-sm">{{ calculateDuration(contract?.start_date, contract?.end_date) }}</span>
                          </div>
                        </div>
                      </div>
                      
                      <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Last Updated</label>
                        <p class="text-gray-900 font-medium text-sm sm:text-base">{{ formatDate(contract?.updated_at) }}</p>
                      </div>
                    </div>
                  </div>

                  <!-- Contract Description -->
                  <div class="mb-4 sm:mb-6">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Contract Description</label>
                    <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                      <p class="text-gray-700 whitespace-pre-line text-sm sm:text-base">{{ contract?.contract_description || 'No description provided' }}</p>
                    </div>
                  </div>

                  <!-- Payment Terms -->
                  <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Payment Terms</label>
                    <div class="bg-blue-50 rounded-lg p-3 sm:p-4">
                      <p class="text-gray-700 whitespace-pre-line text-sm sm:text-base">{{ contract?.payment_terms || 'No payment terms specified' }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Right Column: Timeline, Customer Info & Actions -->
              <div class="space-y-4 sm:space-y-6">
                <!-- Action Buttons -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                  <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Actions</h3>
                  <div class="space-y-2 sm:space-y-3">
                    <!-- View Customer Button -->
                    <button 
                      v-if="contract?.customer"
                      @click="viewCustomer"
                      class="w-full bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-sm sm:text-base"
                    >
                      <EyeIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span>View Customer</span>
                    </button>

                    <!-- Edit Contract Button -->
                    <button 
                      v-if="permissions?.edit"
                      @click="editContract"
                      class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-sm sm:text-base"
                    >
                      <PencilIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span>Edit Contract</span>
                    </button>

                    <!-- Accept Contract Button -->
                    <button 
                      v-if="permissions?.approve && contract?.status === 'contract_created'"
                      @click="acceptContract"
                      :disabled="loading.accept"
                      class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 disabled:from-green-300 disabled:to-green-300 text-white px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-sm sm:text-base"
                    >
                      <CheckIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span v-if="loading.accept">Accepting...</span>
                      <span v-else>Accept Contract</span>
                    </button>

                    <!-- Reject Contract Button -->
                    <button 
                      v-if="permissions?.reject && contract?.status === 'contract_created'"
                      @click="openRejectModal"
                      :disabled="loading.reject"
                      class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 disabled:from-red-300 disabled:to-red-300 text-white px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-sm sm:text-base"
                    >
                      <XMarkIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span v-if="loading.reject">Rejecting...</span>
                      <span v-else>Reject Contract</span>
                    </button>

                    <!-- Mark as Unsigned Button -->
                    <button 
                      v-if="permissions?.edit && contract?.status === 'accepted'"
                      @click="markAsUnsigned"
                      :disabled="loading.unsigned"
                      class="w-full bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 disabled:from-yellow-300 disabled:to-yellow-300 text-white px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-sm sm:text-base"
                    >
                      <ArrowPathIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span v-if="loading.unsigned">Updating...</span>
                      <span v-else>Mark as Unsigned</span>
                    </button>

                    <!-- Delete Contract Button -->
                    <button 
                      v-if="permissions?.delete && contract?.status !== 'accepted'"
                      @click="deleteContract"
                      class="w-full bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-sm sm:text-base"
                    >
                      <TrashIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span>Delete Contract</span>
                    </button>
                  </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                  <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Contract Timeline</h3>
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex items-start space-x-2 sm:space-x-3">
                      <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 sm:mt-2"></div>
                      <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Contract Created</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(contract?.created_at) }}</p>
                      </div>
                    </div>
                    
                    <div v-if="contract?.start_date" class="flex items-start space-x-2 sm:space-x-3">
                      <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5 sm:mt-2"></div>
                      <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Contract Start</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(contract.start_date) }}</p>
                      </div>
                    </div>
                    
                    <div v-if="contract?.end_date" class="flex items-start space-x-2 sm:space-x-3">
                      <div class="w-2 h-2 bg-red-500 rounded-full mt-1.5 sm:mt-2"></div>
                      <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Contract End</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(contract.end_date) }}</p>
                      </div>
                    </div>
                    
                    <div v-if="contract?.customer_signed_at" class="flex items-start space-x-2 sm:space-x-3">
                      <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5 sm:mt-2"></div>
                      <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Customer Signed</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(contract.customer_signed_at) }}</p>
                      </div>
                    </div>
                    
                    <div v-if="contract?.customer_rejected_at" class="flex items-start space-x-2 sm:space-x-3">
                      <div class="w-2 h-2 bg-red-500 rounded-full mt-1.5 sm:mt-2"></div>
                      <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Customer Rejected</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(contract.customer_rejected_at) }}</p>
                        <p v-if="contract.customer_review" class="text-xs sm:text-sm text-gray-500 mt-1">Reason: {{ contract.customer_review }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Customer Info -->
                <div v-if="contract?.customer" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                  <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Customer Information</h3>
                  <div class="space-y-2 sm:space-y-3">
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Name</label>
                      <p class="text-sm font-medium text-gray-900 truncate">{{ contract.customer.name }}</p>
                    </div>
                    
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                      <p class="text-sm text-gray-900 truncate">{{ contract.customer.email || 'N/A' }}</p>
                    </div>
                    
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Phone</label>
                      <p class="text-sm text-gray-900">{{ contract.customer.phone || 'N/A' }}</p>
                    </div>
                    
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Location</label>
                      <p class="text-sm text-gray-900 truncate">{{ contract.customer.location || 'N/A' }}</p>
                    </div>
                    
                    <div>
                      <label class="block text-xs font-medium text-gray-500 mb-1">Customer Status</label>
                      <span :class="getStatusBadgeClass(contract.customer.status)" class="px-2 py-1 rounded text-xs font-semibold">
                        {{ formatStatus(contract.customer.status) }}
                      </span>
                    </div>
                    
                    <div v-if="contract.customer.total_payment_amount" class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200">
                      <label class="block text-xs font-medium text-gray-500 mb-1">Payment Summary</label>
                      <div class="space-y-1">
                        <div class="flex justify-between">
                          <span class="text-xs text-gray-600">Total:</span>
                          <span class="text-xs font-medium text-gray-900">${{ formatNumber(contract.customer.total_payment_amount) }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-xs text-gray-600">Paid:</span>
                          <span class="text-xs font-medium text-green-600">${{ formatNumber(contract.customer.paid_amount || 0) }}</span>
                        </div>
                        <div class="flex justify-between">
                          <span class="text-xs text-gray-600">Remaining:</span>
                          <span class="text-xs font-medium text-red-600">${{ formatNumber(contract.customer.remaining_amount || contract.customer.total_payment_amount) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Contract File Section -->
                <div v-if="contract?.file" class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                  <div class="mb-3 sm:mb-4">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Contract File</h3>
                    <p class="text-gray-600 text-xs sm:text-sm mt-1">Download the attached contract document</p>
                  </div>
                  
                  <div class="flex items-center space-x-3 p-3 sm:p-4 bg-gray-50 rounded-lg">
                    <DocumentTextIcon class="w-8 h-8 sm:w-12 sm:h-12 text-blue-500" />
                    <div class="flex-1 min-w-0">
                      <p class="font-medium text-gray-900 text-sm sm:text-base truncate">{{ getFileName(contract.file) }}</p>
                      <p class="text-xs sm:text-sm text-gray-500">Contract document file</p>
                    </div>
                  </div>
                  
                  <div class="flex gap-2 mt-3 sm:mt-4">
                    <button 
                      @click="viewFile"
                      class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-xs sm:text-sm"
                    >
                      <EyeIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span>View</span>
                    </button>
                    <button 
                      @click="downloadFile"
                      class="flex-1 bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-3 py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-xs sm:text-sm"
                    >
                      <ArrowDownTrayIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span>Download</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-4 sm:p-6 w-full max-w-md border border-gray-200 shadow-xl">
        <div class="flex items-center space-x-2 sm:space-x-3 mb-3 sm:mb-4">
          <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-lg sm:rounded-xl flex items-center justify-center">
            <XMarkIcon class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" />
          </div>
          <div class="min-w-0">
            <h3 class="text-base sm:text-lg font-bold text-gray-900 truncate">Reject Contract</h3>
            <p class="text-gray-600 text-xs sm:text-sm">Please provide a reason for rejecting this contract</p>
          </div>
        </div>
        
        <textarea 
          v-model="rejectForm.reason"
          placeholder="Enter reason for rejecting this contract..."
          class="w-full h-24 sm:h-32 p-3 sm:p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm resize-none"
          required
        ></textarea>
        
        <div class="flex justify-end space-x-2 sm:space-x-3 mt-3 sm:mt-4 sm:mt-6">
          <button 
            type="button"
            @click="closeRejectModal" 
            class="px-3 py-1.5 sm:px-4 sm:py-2.5 text-gray-600 hover:text-gray-800 font-medium text-xs sm:text-sm transition-colors"
          >
            Cancel
          </button>
          <button 
            type="button"
            @click="rejectContract"
            :disabled="!rejectForm.reason.trim() || loading.reject"
            class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 disabled:from-red-400 disabled:to-red-400 text-white px-3 py-1.5 sm:px-6 sm:py-2.5 rounded-lg font-semibold transition-all duration-200 text-xs sm:text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
          >
            <span v-if="loading.reject">Rejecting...</span>
            <span v-else>Reject Contract</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  ArrowDownTrayIcon,
  ArrowPathIcon,
  CheckIcon,
  XMarkIcon,
  EyeIcon,
  UserCircleIcon,
  PencilIcon,
  TrashIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  contract: {
    type: Object,
    default: () => ({})
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
  errors: {
    type: Object,
    default: () => ({})
  }
})

// State
const showRejectModal = ref(false)
const loading = reactive({
  accept: false,
  reject: false,
  unsigned: false,
  contract: true
})

// Error state
const error = ref(false)

// Forms
const rejectForm = reactive({
  reason: ''
})

// Flash message state
const flashMessage = ref(props.flash?.message || '')
const flashMessageType = ref(props.flash?.type || 'success')

// Sidebar state
const isSidebarOpen = ref(false)

// Computed
const flashMessageClass = computed(() => {
  return flashMessageType.value === 'success'
    ? 'bg-green-50 border-green-200 text-green-800'
    : 'bg-red-50 border-red-200 text-red-800'
})

// Listen for sidebar state changes
onMounted(() => {
  const handleSidebarStateChange = (event) => {
    isSidebarOpen.value = event.detail.isOpen
  }
  
  window.addEventListener('sidebar:state-changed', handleSidebarStateChange)
  
  const checkInitialSidebarState = () => {
    const mobileMenuBtn = document.getElementById('mobile-menu-toggle')
    const sidebar = document.querySelector('aside')
    if (mobileMenuBtn && sidebar) {
      isSidebarOpen.value = sidebar.classList.contains('translate-x-0')
    }
  }
  
  setTimeout(checkInitialSidebarState, 100)
  
  return () => {
    window.removeEventListener('sidebar:state-changed', handleSidebarStateChange)
  }
})

// Watch for contract prop changes
watch(() => props.contract, (newContract) => {
  if (newContract && Object.keys(newContract).length > 0) {
    loading.contract = false
    error.value = false
  } else if (props.errors && Object.keys(props.errors).length > 0) {
    loading.contract = false
    error.value = true
    showFlashMessage('Contract not found or access denied.', 'error')
  }
}, { immediate: true })

// Helper functions with safe access
function getStatusBadgeClass(status) {
  if (!status) return 'bg-gray-100 text-gray-800 shadow-sm'
  
  const map = {
    draft: 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 shadow-sm',
    contract_created: 'bg-gradient-to-r from-indigo-100 to-indigo-200 text-indigo-800 shadow-sm',
    accepted: 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 shadow-sm',
    rejected: 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 shadow-sm'
  }
  return map[status] || 'bg-gray-100 text-gray-800 shadow-sm'
}

function formatNumber(num) {
  if (num === null || num === undefined) return '0.00'
  return parseFloat(num).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

function formatStatus(status) {
  if (!status) return 'Unknown'
  
  const map = {
    draft: 'Draft',
    contract_created: 'Contract Created',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return map[status] || status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  try {
    return new Date(dateString).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  } catch (e) {
    return 'Invalid Date'
  }
}

function calculateDuration(startDate, endDate) {
  if (!startDate || !endDate) return 'N/A'
  
  try {
    const start = new Date(startDate)
    const end = new Date(endDate)
    const diffTime = Math.abs(end - start)
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    if (diffDays === 1) return '1 day'
    if (diffDays < 30) return `${diffDays} days`
    
    const diffMonths = Math.floor(diffDays / 30)
    const remainingDays = diffDays % 30
    
    if (remainingDays === 0) return `${diffMonths} month${diffMonths > 1 ? 's' : ''}`
    return `${diffMonths} month${diffMonths > 1 ? 's' : ''} and ${remainingDays} day${remainingDays > 1 ? 's' : ''}`
  } catch (e) {
    return 'Invalid Duration'
  }
}

function getFileName(filePath) {
  if (!filePath) return ''
  return filePath.split('/').pop()
}

// Flash methods
const clearFlashMessage = () => {
  flashMessage.value = ''
  flashMessageType.value = 'success'
}

const showFlashMessage = (msg, type = 'success') => {
  flashMessage.value = msg
  flashMessageType.value = type
  
  setTimeout(() => {
    clearFlashMessage()
  }, 5000)
}

// Navigation Actions
const goToCustomer = () => {
  if (props.contract?.customer?.id) {
    router.get(`/admin/customers/${props.contract.customer.id}`)
  } else {
    router.get('/admin/customers')
  }
}

const viewCustomer = () => {
  if (props.contract?.customer?.id) {
    router.get(`/admin/customers/${props.contract.customer.id}`)
  }
}

const editContract = () => {
  if (props.contract?.id) {
    router.get(`/admin/contracts/${props.contract.id}/edit`)
  }
}

const downloadFile = () => {
  if (props.contract?.file && props.contract?.id) {
    window.open(`/admin/contracts/${props.contract.id}/download`, '_blank')
  }
}

const viewFile = () => {
  if (props.contract?.file) {
    // Check if it's a PDF file
    if (props.contract.file.toLowerCase().endsWith('.pdf')) {
      window.open(props.contract.file, '_blank')
    } else {
      downloadFile()
    }
  }
}

// Accept Contract
const acceptContract = () => {
  if (!props.contract?.id || !window.confirm(`Accept contract "${props.contract.contract_title}"?`)) {
    return
  }
  
  loading.accept = true
  
  router.post(`/admin/contracts/${props.contract.id}/accept`, {}, {
    preserveScroll: false,
    preserveState: false,
    onSuccess: () => {
      loading.accept = false
      showFlashMessage('Contract accepted successfully! Page will reload...', 'success')
      
      setTimeout(() => {
        window.location.reload()
      }, 1500)
    },
    onError: (errors) => {
      loading.accept = false
      showFlashMessage(errors?.message || 'Failed to accept contract.', 'error')
    }
  })
}

// Reject Contract
const openRejectModal = () => {
  rejectForm.reason = ''
  showRejectModal.value = true
}

const closeRejectModal = () => {
  showRejectModal.value = false
  rejectForm.reason = ''
}

const rejectContract = () => {
  if (!rejectForm.reason.trim()) {
    showFlashMessage('Please provide a rejection reason.', 'error')
    return
  }
  
  if (!props.contract?.id || !window.confirm(`Reject contract "${props.contract.contract_title}"?`)) {
    return
  }
  
  loading.reject = true
  
  router.post(`/admin/contracts/${props.contract.id}/reject`, rejectForm, {
    preserveScroll: false,
    preserveState: false,
    onSuccess: () => {
      loading.reject = false
      closeRejectModal()
      showFlashMessage('Contract rejected successfully! Page will reload...', 'success')
      
      setTimeout(() => {
        window.location.reload()
      }, 1500)
    },
    onError: (errors) => {
      loading.reject = false
      showFlashMessage(errors?.message || 'Failed to reject contract.', 'error')
    }
  })
}

// Mark as Unsigned
const markAsUnsigned = () => {
  if (!props.contract?.id || !window.confirm(`Mark contract "${props.contract.contract_title}" as unsigned?`)) {
    return
  }
  
  loading.unsigned = true
  
  router.post(`/admin/contracts/${props.contract.id}/mark-unsigned`, {}, {
    preserveScroll: false,
    preserveState: false,
    onSuccess: () => {
      loading.unsigned = false
      showFlashMessage('Contract marked as unsigned successfully! Page will reload...', 'success')
      
      setTimeout(() => {
        window.location.reload()
      }, 1500)
    },
    onError: (errors) => {
      loading.unsigned = false
      showFlashMessage(errors?.message || 'Failed to mark contract as unsigned.', 'error')
    }
  })
}

// Delete Contract
const deleteContract = () => {
  if (!props.contract?.id || !window.confirm('Are you sure you want to delete this contract?')) {
    return
  }
  
  router.delete(`/admin/contracts/${props.contract.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      showFlashMessage('Contract deleted successfully!', 'success')
      setTimeout(() => goToCustomer(), 1000)
    },
    onError: (errors) => {
      showFlashMessage(errors?.message || 'Failed to delete contract.', 'error')
    }
  })
}

// Mounted
onMounted(() => {
  if (props.flash?.message) {
    flashMessage.value = props.flash.message
    flashMessageType.value = props.flash.type || 'success'
  }
  
  // Check if contract data is available
  if (!props.contract || Object.keys(props.contract).length === 0) {
    if (props.errors && Object.keys(props.errors).length > 0) {
      error.value = true
      loading.contract = false
    }
  } else {
    loading.contract = false
    error.value = false
  }
})
</script>

<style scoped>
/* Add any custom styles if needed */
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Ensure proper mobile scrolling */
@media (max-width: 1023px) {
  .max-h-[90vh] {
    max-height: 85vh;
  }
}

/* Header transition */
#header-section {
  transition: all 300ms cubic-bezier(0.4, 0, 0.2, 1);
}

/* Ensure proper z-index layering */
#header-section {
  z-index: 20 !important;
}

/* Prevent horizontal overflow */
html, body {
  overflow-x: hidden;
}
</style>