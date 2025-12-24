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
                <span class="truncate">Customer Details</span>
                <div class="w-5 h-5 bg-gradient-to-r from-blue-500/30 to-teal-500/30 rounded flex items-center justify-center flex-shrink-0">
                  <UserCircleIcon class="w-3 h-3 text-blue-600/70" />
                </div>
              </h1>
              <p class="text-gray-600 text-xs mt-0.5 truncate">Manage customer information and payments</p>
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
                  @click="goBack"
                  class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-sm"
                >
                  <ArrowLeftIcon class="w-4 h-4" />
                  <span>Back to Customers</span>
                </button>
                <div class="min-w-0">
                  <h1 class="text-xl lg:text-2xl font-bold text-gray-900 truncate">Customer Details</h1>
                  <p class="text-gray-600 text-sm lg:text-base mt-1 truncate">Manage customer information, payments, and commission</p>
                </div>
              </div>
              <div class="flex items-center space-x-3">
                <span :class="getStatusBadgeClass(customer.status)" class="px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm">
                  {{ formatStatus(customer.status) }}
                </span>
                <div v-if="customer.payments && customer.payments.length > 0" 
                     class="flex items-center space-x-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white px-3 py-1.5 rounded-lg shadow-sm">
                  <CreditCardIcon class="w-3.5 h-3.5" />
                  <span class="text-sm">{{ customer.payments?.length || 0 }} payment(s)</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Contract Modal -->
      <ContractModal 
        v-if="showContractModal"
        :is-open="showContractModal"
        :customer="customer"
        :contract="selectedContract"
        @close="showContractModal = false"
        @success="onContractSuccess"
      />

      <!-- Main Content -->
      <div class="flex-1 overflow-hidden flex flex-col">
        <!-- Main Content Area -->
        <div class="flex-1 overflow-auto">
          <div class="pt-4 px-4 sm:px-6 lg:px-8 pb-6 sm:pb-8">
            <!-- Flash Messages -->
            <div v-if="flashMessage" class="mb-4 sm:mb-6">
              <div :class="flashMessageClass" class="rounded-lg p-4 shadow-sm border">
                <div class="flex items-center justify-between">
                  <div class="flex items-center space-x-3">
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

            <!-- Customer Overview Card -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 mb-6">
              <!-- Customer Information -->
              <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 lg:col-span-2">
                <div class="flex items-center space-x-3 sm:space-x-4 mb-4 sm:mb-6">
                  <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl sm:rounded-2xl flex items-center justify-center">
                    <UserCircleIcon class="w-6 h-6 sm:w-8 sm:h-8 text-white" />
                  </div>
                  <div class="min-w-0">
                    <h2 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">{{ customer.name }}</h2>
                    <p class="text-gray-600 text-sm sm:text-base">Customer ID: #{{ customer.id }}</p>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                  <div class="space-y-3 sm:space-y-4">
                    <div>
                      <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Email Address</label>
                      <p class="text-gray-900 font-medium text-sm sm:text-base">{{ customer.email || 'Not provided' }}</p>
                    </div>
                    <div>
                      <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                      <p class="text-gray-900 font-medium text-sm sm:text-base">{{ customer.phone || 'Not provided' }}</p>
                    </div>
                    <div>
                      <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">City</label>
                      <p class="text-gray-900 font-medium text-sm sm:text-base">{{ customer.city?.name || 'Not specified' }}</p>
                    </div>
                    <div>
                      <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Subcity</label>
                      <p class="text-gray-900 font-medium text-sm sm:text-base">{{ customer.subcity?.name || 'Not specified' }}</p>
                    </div>
                  </div>
                  <div class="space-y-3 sm:space-y-4">
                    <div>
                      <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Text Location Description</label>
                      <p class="text-gray-900 font-medium text-sm sm:text-base">{{ customer.text_location || 'Not specified' }}</p>
                    </div>
                    <div>
                      <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Map Location</label>
                      <div v-if="customer.map_location">
                        <a 
                          :href="customer.map_location" 
                          target="_blank" 
                          rel="noopener noreferrer"
                          class="text-blue-600 hover:text-blue-800 font-medium underline text-sm sm:text-base"
                        >
                          View on Map
                        </a>
                      </div>
                      <p v-else class="text-gray-900 font-medium text-sm sm:text-base">Not specified</p>
                    </div>
                    <div>
                      <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Created At</label>
                      <p class="text-gray-900 font-medium text-sm sm:text-base">{{ formatDate(customer.created_at) }}</p>
                    </div>
                  </div>
                </div>

                <!-- Remarks -->
                <div class="mt-4 sm:mt-6 pt-4 sm:pt-6 border-t border-gray-200">
                  <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">Remarks</label>
                  <p class="text-gray-700 bg-gray-50 rounded-lg p-3 sm:p-4 text-sm sm:text-base">
                    {{ customer.remarks || 'No remarks provided.' }}
                  </p>
                </div>
              </div>

              <!-- Quick Actions & Timeline -->
              <div class="space-y-4 sm:space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                  <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Quick Actions</h3>
                  <div class="space-y-2 sm:space-y-3">
                    <!-- Create Contract Button -->
                    <button 
                      v-if="permissions.create && isValidForContract"
                      @click="openContractModal"
                      class="w-full bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-sm sm:text-base"
                    >
                      <DocumentTextIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span>Create Contract</span>
                    </button>

                    <!-- Approve Button -->
                    <button 
                      v-if="showApproveButton"
                      @click="approveCustomer"
                      :disabled="approveLoading"
                      class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 disabled:from-green-300 disabled:to-green-300 text-white px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-sm sm:text-base"
                    >
                      <CheckIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span v-if="approveLoading">Approving...</span>
                      <span v-else>Approve Customer</span>
                    </button>

                    <!-- Reject Button -->
                    <button 
                      v-if="showRejectButton"
                      @click="openRejectModal"
                      :disabled="rejectLoading"
                      class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 disabled:from-red-300 disabled:to-red-300 text-white px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-sm sm:text-base"
                    >
                      <XMarkIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span v-if="rejectLoading">Rejecting...</span>
                      <span v-else>Reject Customer</span>
                    </button>

                    <!-- Delete Customer Button -->
                    <button 
                      v-if="permissions.delete"
                      @click="deleteCustomer"
                      class="w-full bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white px-3 py-2.5 sm:px-4 sm:py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2 text-sm sm:text-base"
                    >
                      <TrashIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                      <span>Delete Customer</span>
                    </button>
                  </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6">
                  <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">Timeline</h3>
                  <div class="space-y-3 sm:space-y-4">
                    <div class="flex items-start space-x-2 sm:space-x-3">
                      <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 sm:mt-2"></div>
                      <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Customer Created</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(customer.created_at) }}</p>
                      </div>
                    </div>
                    <div class="flex items-start space-x-2 sm:space-x-3">
                      <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5 sm:mt-2"></div>
                      <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Last Updated</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(customer.updated_at) }}</p>
                      </div>
                    </div>
                    <div v-if="customer.approved_at" class="flex items-start space-x-2 sm:space-x-3">
                      <div class="w-2 h-2 bg-green-500 rounded-full mt-1.5 sm:mt-2"></div>
                      <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Approved</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ formatDate(customer.approved_at) }}</p>
                      </div>
                    </div>
                    <div v-if="customer.payments && customer.payments.length > 0" class="flex items-start space-x-2 sm:space-x-3">
                      <div class="w-2 h-2 bg-purple-500 rounded-full mt-1.5 sm:mt-2"></div>
                      <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Payments</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ customer.payments.length }} payment(s)</p>
                      </div>
                    </div>
                    <div v-if="customer.contracts && customer.contracts.length > 0" class="flex items-start space-x-2 sm:space-x-3">
                      <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 sm:mt-2"></div>
                      <div>
                        <p class="text-xs sm:text-sm font-medium text-gray-900">Contracts</p>
                        <p class="text-xs sm:text-sm text-gray-500">{{ customer.contracts.length }} contract(s)</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment Summary - Editable -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-4 sm:mb-6">
              <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start sm:items-center gap-3 mb-4 sm:mb-6">
                <div class="min-w-0">
                  <h3 class="text-base sm:text-lg font-semibold text-gray-900">Payment Summary</h3>
                  <p class="text-gray-600 text-xs sm:text-sm mt-1">All amounts are editable</p>
                </div>
                <div v-if="permissions.edit" class="flex space-x-2">
                  <button 
                    v-if="!editingPayment"
                    @click="startEditingPayment"
                    class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-xs sm:text-sm"
                  >
                    <PencilIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    <span>Edit Payment</span>
                  </button>
                  <button 
                    v-else
                    @click="cancelEditingPayment"
                    class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-xs sm:text-sm"
                  >
                    <XMarkIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    <span>Cancel</span>
                  </button>
                  <button 
                    v-if="editingPayment"
                    @click="savePaymentInfo"
                    :disabled="savingPayment"
                    class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 disabled:from-green-300 disabled:to-green-300 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-xs sm:text-sm"
                  >
                    <CheckIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    <span v-if="savingPayment">Saving...</span>
                    <span v-else>Save</span>
                  </button>
                </div>
              </div>
              
              <!-- Payment Progress -->
              <div class="mb-4 sm:mb-6">
                <div class="flex justify-between mb-2">
                  <span class="text-xs sm:text-sm font-medium text-gray-700">Payment Progress</span>
                  <span class="text-xs sm:text-sm font-medium text-gray-700">{{ paymentProgress.toFixed(0) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 sm:h-2.5">
                  <div 
                    class="h-2 sm:h-2.5 rounded-full transition-all duration-500"
                    :class="getPaymentProgressClass(customer.payment_status)"
                    :style="{ width: paymentProgress + '%' }"
                  ></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 mt-3 sm:mt-4">
                  <!-- Total Amount -->
                  <div class="bg-blue-50 p-3 sm:p-4 rounded-lg">
                    <label class="block text-xs font-medium text-blue-700 mb-1">Total Amount</label>
                    <div v-if="!editingPayment">
                      <p class="mt-1 text-lg sm:text-xl font-semibold text-blue-900">${{ formatNumber(customer.total_payment_amount) }}</p>
                    </div>
                    <div v-else>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">$</span>
                        <input
                          v-model="paymentForm.total_payment_amount"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full pl-8 pr-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-blue-900 font-semibold text-sm sm:text-base"
                          @input="calculateRemainingAmount"
                        />
                      </div>
                    </div>
                  </div>
                  
                  <!-- Paid Amount -->
                  <div class="bg-green-50 p-3 sm:p-4 rounded-lg">
                    <label class="block text-xs font-medium text-green-700 mb-1">Paid Amount</label>
                    <div v-if="!editingPayment">
                      <p class="mt-1 text-lg sm:text-xl font-semibold text-green-900">${{ formatNumber(customer.paid_amount) }}</p>
                    </div>
                    <div v-else>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">$</span>
                        <input
                          v-model="paymentForm.paid_amount"
                          type="number"
                          step="0.01"
                          min="0"
                          :max="paymentForm.total_payment_amount"
                          class="w-full pl-8 pr-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-green-900 font-semibold text-sm sm:text-base"
                          @input="calculateRemainingAmount"
                        />
                      </div>
                    </div>
                  </div>
                  
                  <!-- Remaining Amount -->
                  <div class="bg-green-50 p-3 sm:p-4 rounded-lg">
                    <label class="block text-xs font-medium text-green-700 mb-1">Remaining</label>
                    <div v-if="!editingPayment">
                      <p class="mt-1 text-lg sm:text-xl font-semibold text-green-900">${{ formatNumber(customer.remaining_amount) }}</p>
                    </div>
                    <div v-else>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">$</span>
                        <input
                          v-model="paymentForm.remaining_amount"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full pl-8 pr-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-green-900 font-semibold text-sm sm:text-base bg-gray-100"
                          readonly
                        />
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Payment Status -->
                <div class="mt-3 sm:mt-4">
                  <label class="block text-xs font-medium text-gray-700 mb-1">Payment Status</label>
                  <span :class="getPaymentStatusBadgeClass(paymentForm.payment_status)" class="px-3 py-1 rounded-lg text-xs sm:text-sm font-semibold">
                    {{ formatPaymentStatus(paymentForm.payment_status) }}
                  </span>
                </div>
              </div>

              <!-- Recent Payments -->
              <div v-if="customer.payments && customer.payments.length > 0">
                <div class="overflow-x-auto -mx-2 sm:mx-0">
                  <table class="w-full min-w-full">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Amount</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Method</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Schedule</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr v-for="payment in customer.payments.slice(0, 5)" :key="payment.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-3 py-3">
                          <span class="font-bold text-gray-900 text-sm sm:text-base">${{ formatNumber(payment.amount) }}</span>
                        </td>
                        <td class="px-3 py-3">
                          <div class="flex items-center space-x-1 sm:space-x-2">
                            <span class="text-base sm:text-lg">{{ getPaymentMethodIcon(payment.method) }}</span>
                            <span class="text-gray-700 text-xs sm:text-sm">{{ payment.method }}</span>
                          </div>
                        </td>
                        <td class="px-3 py-3">
                          <span class="text-gray-700 text-xs sm:text-sm">{{ payment.schedule }}</span>
                        </td>
                        <td class="px-3 py-3">
                          <span class="text-gray-700 text-xs sm:text-sm">{{ formatDate(payment.payment_date) }}</span>
                        </td>
                        <td class="px-3 py-3">
                          <span :class="getPaymentStatusClass(payment)" class="px-2 py-1 rounded-full text-xs sm:text-sm font-semibold">
                            {{ getPaymentStatus(payment) }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                
                <div v-if="customer.payments.length > 5" class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 text-center">
                  <button 
                    @click="viewAllPayments"
                    class="text-purple-600 hover:text-purple-700 font-medium text-xs sm:text-sm transition-colors flex items-center space-x-1 mx-auto"
                  >
                    <span>View all {{ customer.payments.length }} payments</span>
                    <ChevronRightIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                  </button>
                </div>
              </div>
              <div v-else class="text-center py-6 sm:py-8">
                <CreditCardIcon class="w-10 h-10 sm:w-12 sm:h-12 text-gray-400 mx-auto mb-2 sm:mb-3" />
                <p class="text-gray-600 text-sm sm:text-base">No payments have been added yet.</p>
              </div>
            </div>

            <!-- Commission Summary - Editable -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sm:p-6 mb-4 sm:mb-6">
              <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start sm:items-center gap-3 mb-4 sm:mb-6">
                <div class="min-w-0">
                  <h3 class="text-base sm:text-lg font-semibold text-gray-900">Commission Summary</h3>
                  <p class="text-gray-600 text-xs sm:text-sm mt-1">Commission based on user's rate × payment amount</p>
                </div>
                <div v-if="permissions.edit" class="flex space-x-2">
                  <button 
                    v-if="!editingCommission"
                    @click="startEditingCommission"
                    class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-xs sm:text-sm"
                  >
                    <PencilIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    <span>Edit Commission</span>
                  </button>
                  <button 
                    v-else
                    @click="cancelEditingCommission"
                    class="bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-xs sm:text-sm"
                  >
                    <XMarkIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    <span>Cancel</span>
                  </button>
                  <button 
                    v-if="editingCommission"
                    @click="saveCommissionInfo"
                    :disabled="savingCommission"
                    class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 disabled:from-green-300 disabled:to-green-300 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-xs sm:text-sm"
                  >
                    <CheckIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    <span v-if="savingCommission">Saving...</span>
                    <span v-else>Save</span>
                  </button>
                </div>
              </div>

              <div class="mb-4 sm:mb-6">
                <div class="flex justify-between mb-2">
                  <span class="text-xs sm:text-sm font-medium text-gray-700">Commission Progress</span>
                  <span class="text-xs sm:text-sm font-medium text-gray-700">{{ commissionProgress.toFixed(0) }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2 sm:h-2.5">
                  <div 
                    class="h-2 sm:h-2.5 rounded-full bg-gradient-to-r from-green-500 to-green-600 transition-all duration-500"
                    :style="{ width: commissionProgress + '%' }"
                  ></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-3 sm:mt-4">
                  <!-- Commission User -->
                  <div class="bg-blue-50 p-3 sm:p-4 rounded-lg">
                    <label class="block text-xs font-medium text-blue-700 mb-1">Commission User</label>
                    <div v-if="!editingCommission">
                      <p v-if="commissionUserData" class="mt-1 font-semibold text-blue-900 text-sm sm:text-base">{{ commissionUserData.name }}</p>
                      <p v-else class="mt-1 text-gray-500 italic text-sm sm:text-base">Not assigned</p>
                    </div>
                    <div v-else>
                      <select
                        v-model="commissionForm.commission_user_id"
                        class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-xs sm:text-sm"
                        @change="onCommissionUserChange"
                      >
                        <option value="">Select User</option>
                        <option v-for="user in commissionUsers" :key="user.id" :value="user.id">
                          {{ user.name }} ({{ user.commission_rate }}%)
                        </option>
                      </select>
                    </div>
                  </div>
                  
                  <!-- Commission Rate -->
                  <div class="bg-blue-50 p-3 sm:p-4 rounded-lg">
                    <label class="block text-xs font-medium text-blue-700 mb-1">Commission Rate</label>
                    <div v-if="!editingCommission">
                      <p class="mt-1 font-semibold text-blue-900 text-sm sm:text-base">{{ customer.commission_rate }}%</p>
                    </div>
                    <div v-else>
                      <div class="relative">
                        <input
                          v-model="commissionForm.commission_rate"
                          type="number"
                          step="0.01"
                          min="0"
                          max="100"
                          class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-blue-900 font-semibold text-sm sm:text-base"
                          @input="calculateCommissionAmount"
                        />
                        <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">%</span>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Total Commission -->
                  <div class="bg-blue-50 p-3 sm:p-4 rounded-lg">
                    <label class="block text-xs font-medium text-blue-700 mb-1">Total Commission</label>
                    <div v-if="!editingCommission">
                      <p class="mt-1 text-lg sm:text-xl font-semibold text-blue-900">${{ formatNumber(customer.commission_amount) }}</p>
                    </div>
                    <div v-else>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">$</span>
                        <input
                          v-model="commissionForm.commission_amount"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full pl-8 pr-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-blue-900 font-semibold text-sm sm:text-base bg-gray-100"
                          readonly
                        />
                      </div>
                    </div>
                  </div>
                  
                  <!-- Paid Commission -->
                  <div class="bg-green-50 p-3 sm:p-4 rounded-lg">
                    <label class="block text-xs font-medium text-green-700 mb-1">Paid Commission</label>
                    <div v-if="!editingCommission">
                      <p class="mt-1 text-lg sm:text-xl font-semibold text-green-900">${{ formatNumber(customer.paid_commission) }}</p>
                    </div>
                    <div v-else>
                      <div class="relative">
                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm">$</span>
                        <input
                          v-model="commissionForm.paid_commission"
                          type="number"
                          step="0.01"
                          min="0"
                          class="w-full pl-8 pr-3 py-2 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-green-900 font-semibold text-sm sm:text-base"
                        />
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Commission Status -->
                <div class="mt-3 sm:mt-4">
                  <label class="block text-xs font-medium text-gray-700 mb-1">Commission Status</label>
                  <span :class="getCommissionStatusBadgeClass(commissionForm.commission_status)" class="px-3 py-1 rounded-lg text-xs sm:text-sm font-semibold">
                    {{ formatCommissionStatus(commissionForm.commission_status) }}
                  </span>
                </div>
              </div>

              <!-- Commission User Info -->
              <div v-if="commissionUserData && !editingCommission" class="bg-gradient-to-r from-blue-50 to-indigo-50 p-3 sm:p-4 rounded-lg">
                <div class="flex items-center space-x-3 sm:space-x-4">
                  <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                    <UserIcon class="w-5 h-5 sm:w-6 sm:h-6 text-white" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ commissionUserData.name }}</h4>
                    <p class="text-gray-600 text-xs sm:text-sm">Commission Rate: {{ customer.commission_rate }}%</p>
                  </div>
                  <div class="text-right min-w-0">
                    <p class="text-gray-600 text-xs sm:text-sm">Total Commission</p>
                    <p class="text-lg sm:text-xl font-bold text-blue-900 truncate">${{ formatNumber(customer.commission_amount) }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contracts Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
              <div class="px-4 py-3 sm:px-6 sm:py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                  <div class="min-w-0">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Contracts</h3>
                    <p class="text-gray-600 text-xs sm:text-sm mt-1">Total: {{ (customer.contracts || []).length }} contract(s)</p>
                  </div>
                  <!-- Create Contract Button - Opens Modal -->
                  <button 
                    v-if="permissions.create && isValidForContract"
                    @click="openContractModal"
                    class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-xs sm:text-sm"
                  >
                    <PlusIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                    <span>Create Contract</span>
                  </button>
                  <div v-else class="text-xs sm:text-sm text-gray-500">
                    <p v-if="!permissions.create">You don't have permission to create contracts</p>
                    <p v-else-if="!isValidForContract">Cannot create contract for customer with status: {{ customer.status }}</p>
                  </div>
                </div>
              </div>
              
              <!-- Contracts Table -->
              <div v-if="customer.contracts && customer.contracts.length > 0" class="overflow-x-auto -mx-2 sm:mx-0">
                <table class="w-full min-w-full">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-3 py-2 sm:px-6 sm:py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Title</th>
                      <th class="px-3 py-2 sm:px-6 sm:py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Value</th>
                      <th class="px-3 py-2 sm:px-6 sm:py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                      <th class="px-3 py-2 sm:px-6 sm:py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                      <th class="px-3 py-2 sm:px-6 sm:py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="contract in customer.contracts" :key="contract.id" class="hover:bg-gray-50 transition-colors">
                      <td class="px-3 py-3 sm:px-6 sm:py-4">
                        <div class="font-medium text-gray-900 text-sm sm:text-base">{{ contract.contract_title }}</div>
                        <div class="text-gray-500 text-xs sm:text-sm line-clamp-2">{{ contract.contract_description }}</div>
                      </td>
                      <td class="px-3 py-3 sm:px-6 sm:py-4">
                        <span class="font-semibold text-gray-900 text-sm sm:text-base">${{ formatNumber(contract.total_value) }}</span>
                      </td>
                      <td class="px-3 py-3 sm:px-6 sm:py-4">
                        <span :class="getStatusBadgeClass(contract.status)" class="px-2 py-1 sm:px-3 sm:py-1 rounded-lg text-xs sm:text-sm font-semibold">
                          {{ formatStatus(contract.status) }}
                        </span>
                      </td>
                      <td class="px-3 py-3 sm:px-6 sm:py-4 text-xs sm:text-sm text-gray-600">
                        {{ formatDate(contract.created_at) }}</td>
                      <td class="px-3 py-3 sm:px-6 sm:py-4">
                        <div class="flex gap-1 sm:gap-2">
                          <!-- View button -->
                          <button 
                            @click="viewContract(contract.id)"
                            class="p-1 sm:p-1.5 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-lg hover:from-teal-600 hover:to-teal-700 transition-all duration-200 shadow-sm hover:shadow"
                            title="View"
                          >
                            <EyeIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                          </button>
                          
                          <!-- Edit button - opens modal -->
                          <button 
                            v-if="permissions.edit"
                            @click="editContract(contract)"
                            class="p-1 sm:p-1.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow"
                            title="Edit"
                          >
                            <PencilIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                          </button>
                          
                          <!-- Delete button -->
                          <button 
                            v-if="permissions.delete"
                            @click="deleteContract(contract.id)"
                            class="p-1 sm:p-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-sm hover:shadow"
                            title="Delete"
                          >
                            <TrashIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- No Contracts State -->
              <div v-else class="text-center py-8 sm:py-12">
                <DocumentTextIcon class="w-12 h-12 sm:w-16 sm:h-16 text-gray-400 mx-auto mb-3 sm:mb-4" />
                <p class="text-gray-600 text-sm sm:text-base mb-3 sm:mb-4">No contracts created for this customer yet.</p>
                <button 
                  v-if="permissions.create && isValidForContract"
                  @click="openContractModal"
                  class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-xs sm:text-sm mx-auto"
                >
                  <DocumentTextIcon class="w-3.5 h-3.5 sm:w-4 sm:h-4" />
                  <span>Create First Contract</span>
                </button>
                <div v-else class="text-xs sm:text-sm text-gray-500">
                  <p v-if="!permissions.create">You don't have permission to create contracts</p>
                  <p v-else-if="!isValidForContract">Cannot create contract for customer with status: {{ customer.status }}</p>
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
            <h3 class="text-base sm:text-lg font-bold text-gray-900 truncate">Reject Customer</h3>
            <p class="text-gray-600 text-xs sm:text-sm">Please provide a reason for rejection</p>
          </div>
        </div>
        
        <textarea 
          v-model="rejectForm.reason"
          placeholder="Enter reason for rejecting this customer..."
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
            @click="rejectCustomer"
            :disabled="!rejectForm.reason.trim() || rejectLoading"
            class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 disabled:from-red-400 disabled:to-red-400 text-white px-3 py-1.5 sm:px-6 sm:py-2.5 rounded-lg font-semibold transition-all duration-200 text-xs sm:text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
          >
            <span v-if="rejectLoading">Rejecting...</span>
            <span v-else>Reject Customer</span>
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
import ContractModal from '@/Pages/Admin/Contracts/ContractModal.vue'
import {
  ArrowLeftIcon,
  CheckIcon,
  XMarkIcon,
  EyeIcon,
  UserCircleIcon,
  PencilIcon,
  PlusIcon,
  TrashIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  CreditCardIcon,
  ChevronRightIcon,
  DocumentTextIcon,
  UserIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  customer: {
    type: Object,
    default: () => ({ payments: [], contracts: [] })
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
  commissionUsers: {
    type: Array,
    default: () => []
  },
  paymentProgress: {
    type: Number,
    default: 0
  },
  commissionProgress: {
    type: Number,
    default: 0
  }
})

// Modal states
const showRejectModal = ref(false)
const showContractModal = ref(false)
const approveLoading = ref(false)
const rejectLoading = ref(false)

// Editing states
const editingPayment = ref(false)
const editingCommission = ref(false)
const savingPayment = ref(false)
const savingCommission = ref(false)

// Contract modal data
const selectedContract = ref(null)

// Forms
const rejectForm = reactive({
  reason: ''
})

const paymentForm = reactive({
  total_payment_amount: props.customer.total_payment_amount || 0,
  paid_amount: props.customer.paid_amount || 0,
  remaining_amount: props.customer.remaining_amount || 0,
  payment_status: props.customer.payment_status || 'not_paid'
})

const commissionForm = reactive({
  commission_user_id: props.customer.commission_user_id || '',
  commission_rate: props.customer.commission_rate || 0,
  commission_amount: props.customer.commission_amount || 0,
  paid_commission: props.customer.paid_commission || 0,
  commission_status: props.customer.commission_status || 'not_applicable'
})

// Flash message state
const flashMessage = ref(props.flash?.message || '')
const flashMessageType = ref(props.flash?.type || 'success')

// Sidebar state
const isSidebarOpen = ref(false)

// Listen for sidebar state changes
onMounted(() => {
  // Listen for sidebar state changes
  const handleSidebarStateChange = (event) => {
    isSidebarOpen.value = event.detail.isOpen
  }
  
  window.addEventListener('sidebar:state-changed', handleSidebarStateChange)
  
  // Check initial sidebar state by looking for mobile menu button
  const checkInitialSidebarState = () => {
    const mobileMenuBtn = document.getElementById('mobile-menu-toggle')
    const sidebar = document.querySelector('aside')
    if (mobileMenuBtn && sidebar) {
      // Check if sidebar is visible (translate-x-0 class)
      isSidebarOpen.value = sidebar.classList.contains('translate-x-0')
    }
  }
  
  // Wait a bit for DOM to be ready
  setTimeout(checkInitialSidebarState, 100)
  
  return () => {
    window.removeEventListener('sidebar:state-changed', handleSidebarStateChange)
  }
})

// Watch for changes in customer data
watch(() => props.customer, (newCustomer) => {
  // Update forms with new data
  if (newCustomer) {
    paymentForm.total_payment_amount = newCustomer.total_payment_amount || 0
    paymentForm.paid_amount = newCustomer.paid_amount || 0
    paymentForm.remaining_amount = newCustomer.remaining_amount || 0
    paymentForm.payment_status = newCustomer.payment_status || 'not_paid'
    
    commissionForm.commission_user_id = newCustomer.commission_user_id || ''
    commissionForm.commission_rate = newCustomer.commission_rate || 0
    commissionForm.commission_amount = newCustomer.commission_amount || 0
    commissionForm.paid_commission = newCustomer.paid_commission || 0
    commissionForm.commission_status = newCustomer.commission_status || 'not_applicable'
  }
}, { deep: true })

// Watch for flash messages
watch(() => props.flash, (newFlash) => {
  if (newFlash?.message) {
    flashMessage.value = newFlash.message
    flashMessageType.value = newFlash.type || 'success'
  }
}, { deep: true })

// Computed
const flashMessageClass = computed(() => {
  return flashMessageType.value === 'success'
    ? 'bg-green-50 border-green-200 text-green-800'
    : 'bg-red-50 border-red-200 text-red-800'
})

const paymentProgress = computed(() => {
  if (!paymentForm.total_payment_amount) return 0
  return Math.min(100, (paymentForm.paid_amount / paymentForm.total_payment_amount) * 100)
})

const commissionProgress = computed(() => {
  if (!commissionForm.commission_amount) return 0
  return Math.min(100, (commissionForm.paid_commission / commissionForm.commission_amount) * 100)
})

// Computed property for commission user data
const commissionUserData = computed(() => {
  // Try multiple possible property names for commission user data
  const user = props.customer.commissionUser || 
               props.customer.commission_user || 
               (props.customer.commission_user_id && 
                props.commissionUsers.find(user => user.id === props.customer.commission_user_id))
  
  // If we found a user object but it doesn't have a name, try to get it from commissionUsers
  if (user && !user.name && user.id) {
    const foundUser = props.commissionUsers.find(u => u.id === user.id)
    if (foundUser) {
      return foundUser
    }
  }
  
  return user
})

// Computed properties for button visibility
const showApproveButton = computed(() => {
  const canApprove = props.permissions.approve === true;
  const validStatus = props.customer.status === 'draft' || props.customer.status === 'contract_created';
  return canApprove && validStatus;
});

const showRejectButton = computed(() => {
  const canReject = props.permissions.reject === true;
  const validStatus = props.customer.status === 'draft' || props.customer.status === 'contract_created';
  return canReject && validStatus;
});

// New computed properties for contract creation
const isValidForContract = computed(() => {
  const validStatuses = ['draft', 'contract_created'];
  return validStatuses.includes(props.customer.status);
});

const hasContractPermissions = computed(() => {
  return props.permissions.create === true;
});

// Helper functions
function getStatusBadgeClass(status) {
  const map = {
    draft: 'bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 shadow-sm',
    contract_created: 'bg-gradient-to-r from-indigo-100 to-indigo-200 text-indigo-800 shadow-sm',
    accepted: 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 shadow-sm',
    rejected: 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 shadow-sm'
  }
  return map[status] || 'bg-gray-100 text-gray-800 shadow-sm'
}

function getPaymentProgressClass(status) {
  const map = {
    paid: 'bg-gradient-to-r from-green-500 to-green-600',
    half_paid: 'bg-gradient-to-r from-green-400 to-green-500',
    pending: 'bg-gradient-to-r from-green-300 to-green-400',
    not_paid: 'bg-gradient-to-r from-green-200 to-green-300'
  }
  return map[status] || 'bg-green-200'
}

function getPaymentStatusBadgeClass(status) {
  const map = {
    paid: 'bg-green-100 text-green-800',
    half_paid: 'bg-green-100 text-green-800',
    pending: 'bg-green-100 text-green-800',
    not_paid: 'bg-green-100 text-green-800'
  }
  return map[status] || 'bg-green-100 text-green-800'
}

function getCommissionStatusBadgeClass(status) {
  const map = {
    paid: 'bg-green-100 text-green-800',
    pending: 'bg-green-100 text-green-800',
    not_paid: 'bg-green-100 text-green-800',
    not_applicable: 'bg-green-100 text-green-800'
  }
  return map[status] || 'bg-green-100 text-green-800'
}

function getPaymentStatusClass(payment) {
  const today = new Date().setHours(0, 0, 0, 0)
  const paymentDate = new Date(payment.payment_date).setHours(0, 0, 0, 0)
  
  if (paymentDate < today) return 'bg-green-100 text-green-800'
  if (paymentDate === today) return 'bg-green-100 text-green-800'
  return 'bg-green-100 text-green-800'
}

function getPaymentStatus(payment) {
  const today = new Date().setHours(0, 0, 0, 0)
  const paymentDate = new Date(payment.payment_date).setHours(0, 0, 0, 0)
  
  if (paymentDate < today) return 'Paid'
  if (paymentDate === today) return 'Paid'
  return 'Paid'
}

function getPaymentMethodIcon(method) {
  const icons = {
    'Cash': '💰',
    'Bank Transfer': '🏦',
    'Credit Card': '💳',
    'Check': '📝',
    'Digital Wallet': '📱',
    'Other': '💸'
  }
  return icons[method] || '💸'
}

function formatPaymentStatus(status) {
  const map = {
    paid: 'Fully Paid',
    half_paid: 'Half Paid',
    pending: 'Partially Paid',
    not_paid: 'Not Paid'
  }
  return map[status] || status
}

function formatCommissionStatus(status) {
  const map = {
    paid: 'Commission Paid',
    pending: 'Commission Pending',
    not_paid: 'Commission Not Paid',
    not_applicable: 'No Commission'
  }
  return map[status] || status
}

function formatNumber(num) {
  if (!num) return '0.00'
  return parseFloat(num).toFixed(2)
}

function formatStatus(status) {
  const map = {
    draft: 'Draft',
    contract_created: 'Contract Created',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return map[status] || status
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Helper function to get CSRF token
function getCsrfToken() {
  // Try multiple ways to get the CSRF token
  const tokenFromMeta = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
  const tokenFromInput = document.querySelector('input[name="_token"]')?.value
  const tokenFromWindow = window.csrfToken
  
  const token = tokenFromMeta || tokenFromInput || tokenFromWindow
  
  if (!token) {
    console.error('CSRF token not found. Checked meta tag, input field, and window object.')
  }
  
  return token
}

// Payment editing methods
const startEditingPayment = () => {
  paymentForm.total_payment_amount = parseFloat(props.customer.total_payment_amount) || 0
  paymentForm.paid_amount = parseFloat(props.customer.paid_amount) || 0
  paymentForm.remaining_amount = parseFloat(props.customer.remaining_amount) || 0
  paymentForm.payment_status = props.customer.payment_status || 'not_paid'
  editingPayment.value = true
}

const cancelEditingPayment = () => {
  editingPayment.value = false
}

const calculateRemainingAmount = () => {
  const total = parseFloat(paymentForm.total_payment_amount) || 0
  const paid = parseFloat(paymentForm.paid_amount) || 0
  paymentForm.remaining_amount = Math.max(0, total - paid)
  
  // Update payment status
  if (total > 0) {
    if (paid >= total) {
      paymentForm.payment_status = 'paid'
    } else if (paid >= (total * 0.5)) {
      paymentForm.payment_status = 'half_paid'
    } else if (paid > 0) {
      paymentForm.payment_status = 'pending'
    } else {
      paymentForm.payment_status = 'not_paid'
    }
  } else {
    paymentForm.payment_status = 'not_paid'
  }
}

const savePaymentInfo = async () => {
  savingPayment.value = true
  
  try {
    calculateRemainingAmount()
    
    const csrfToken = getCsrfToken()
    if (!csrfToken) {
      throw new Error('CSRF token not found. Please refresh the page and try again.')
    }
    
    const response = await fetch(`/admin/customers/${props.customer.id}/update-payment-info`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        total_payment_amount: parseFloat(paymentForm.total_payment_amount) || 0,
        paid_amount: parseFloat(paymentForm.paid_amount) || 0,
        remaining_amount: parseFloat(paymentForm.remaining_amount) || 0,
        payment_status: paymentForm.payment_status
      })
    })
    
    const data = await response.json()
    
    if (response.ok && data.success) {
      showFlashMessage('Payment information updated successfully!', 'success')
      editingPayment.value = false
      
      // Refresh the page to get updated data
      router.reload({ preserveScroll: true, preserveState: false })
    } else {
      showFlashMessage(data.message || 'Failed to update payment information.', 'error')
    }
  } catch (error) {
    console.error('Error updating payment info:', error)
    showFlashMessage('Failed to update payment information. ' + error.message, 'error')
  } finally {
    savingPayment.value = false
  }
}

// Commission editing methods
const startEditingCommission = () => {
  commissionForm.commission_user_id = props.customer.commission_user_id || ''
  commissionForm.commission_rate = parseFloat(props.customer.commission_rate) || 0
  commissionForm.commission_amount = parseFloat(props.customer.commission_amount) || 0
  commissionForm.paid_commission = parseFloat(props.customer.paid_commission) || 0
  commissionForm.commission_status = props.customer.commission_status || 'not_applicable'
  editingCommission.value = true
}

const cancelEditingCommission = () => {
  editingCommission.value = false
}

const onCommissionUserChange = () => {
  if (commissionForm.commission_user_id) {
    const user = props.commissionUsers.find(u => u.id == commissionForm.commission_user_id)
    if (user) {
      commissionForm.commission_rate = user.commission_rate || 0
      calculateCommissionAmount()
    }
  } else {
    commissionForm.commission_rate = 0
    commissionForm.commission_amount = 0
    commissionForm.commission_status = 'not_applicable'
  }
}

const calculateCommissionAmount = () => {
  const total = parseFloat(paymentForm.total_payment_amount) || parseFloat(props.customer.total_payment_amount) || 0
  const rate = parseFloat(commissionForm.commission_rate) || 0
  commissionForm.commission_amount = (total * rate) / 100
  
  // Update commission status
  if (commissionForm.commission_amount > 0) {
    if (commissionForm.paid_commission >= commissionForm.commission_amount) {
      commissionForm.commission_status = 'paid'
    } else if (commissionForm.paid_commission > 0) {
      commissionForm.commission_status = 'pending'
    } else {
      commissionForm.commission_status = 'not_paid'
    }
  } else {
    commissionForm.commission_status = 'not_applicable'
  }
}

const saveCommissionInfo = async () => {
  savingCommission.value = true
  
  try {
    calculateCommissionAmount()
    
    const csrfToken = getCsrfToken()
    if (!csrfToken) {
      throw new Error('CSRF token not found. Please refresh the page and try again.')
    }
    
    const response = await fetch(`/admin/customers/${props.customer.id}/update-commission-info`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({
        commission_user_id: commissionForm.commission_user_id || null,
        commission_rate: parseFloat(commissionForm.commission_rate) || 0,
        commission_amount: parseFloat(commissionForm.commission_amount) || 0,
        paid_commission: parseFloat(commissionForm.paid_commission) || 0,
        commission_status: commissionForm.commission_status
      })
    })
    
    const data = await response.json()
    
    if (response.ok && data.success) {
      showFlashMessage('Commission information updated successfully!', 'success')
      editingCommission.value = false
      
      // Update the local customer data with the response data
      if (data.data) {
        // Update commission user info
        if (data.data.commissionUser || data.data.commission_user) {
          props.customer.commissionUser = data.data.commissionUser || data.data.commission_user
        }
        // Update commission fields
        props.customer.commission_user_id = data.data.commission_user_id
        props.customer.commission_rate = data.data.commission_rate
        props.customer.commission_amount = data.data.commission_amount
        props.customer.paid_commission = data.data.paid_commission
        props.customer.commission_status = data.data.commission_status
        
        // Also update the commission form for consistency
        commissionForm.commission_user_id = data.data.commission_user_id
        commissionForm.commission_rate = data.data.commission_rate
        commissionForm.commission_amount = data.data.commission_amount
        commissionForm.paid_commission = data.data.paid_commission
        commissionForm.commission_status = data.data.commission_status
      }
      
    } else {
      showFlashMessage(data.message || 'Failed to update commission information.', 'error')
    }
  } catch (error) {
    console.error('Error updating commission info:', error)
    showFlashMessage('Failed to update commission information. ' + error.message, 'error')
  } finally {
    savingCommission.value = false
  }
}

// Also update the rejectCustomer method to use the same CSRF token function
const rejectCustomer = () => {
  if (!rejectForm.reason.trim()) {
    showFlashMessage('Please provide a rejection reason.', 'error')
    return
  }
  
  if (!window.confirm(`Reject "${props.customer.name}"? This action cannot be undone.`)) {
    return
  }
  
  rejectLoading.value = true
  
  const csrfToken = getCsrfToken()
  if (!csrfToken) {
    showFlashMessage('CSRF token not found. Please refresh the page and try again.', 'error')
    rejectLoading.value = false
    return
  }
  
  fetch(`/admin/customers/${props.customer.id}/reject`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': csrfToken,
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify(rejectForm)
  })
  .then(async response => {
    const data = await response.json()
    
    if (response.ok && data.success) {
      showFlashMessage(data.message, 'success')
      closeRejectModal()
      
      setTimeout(() => {
        router.get('/admin/customers')
      }, 1500)
    } else {
      if (data.errors && data.errors.reason) {
        showFlashMessage(data.errors.reason[0], 'error')
      } else if (data.message) {
        showFlashMessage(data.message, 'error')
      } else {
        showFlashMessage('Failed to reject customer.', 'error')
      }
    }
  })
  .catch(error => {
    console.error('Reject error:', error)
    showFlashMessage('Failed to reject customer. ' + error.message, 'error')
  })
  .finally(() => {
    rejectLoading.value = false
  })
}

// Watch for changes in payment total to recalculate commission
watch(() => paymentForm.total_payment_amount, () => {
  if (editingCommission.value) {
    calculateCommissionAmount()
  }
})

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

// Actions
const goBack = () => router.get('/admin/customers')
const viewAllPayments = () => {
  router.get(`/admin/customers/${props.customer.id}/payments`)
}

// Modals
const openRejectModal = () => {
  rejectForm.reason = ''
  showRejectModal.value = true
}

const closeRejectModal = () => {
  showRejectModal.value = false
  rejectForm.reason = ''
}

// Contract Modal Methods
const openContractModal = () => {
  selectedContract.value = null
  showContractModal.value = true
}

const editContract = (contract) => {
  selectedContract.value = contract
  showContractModal.value = true
}

const onContractSuccess = (message) => {
  showFlashMessage(message, 'success')
  router.reload({ preserveScroll: true, preserveState: false })
}

// View contract method
const viewContract = (contractId) => {
  router.get(`/admin/contracts/${contractId}`)
}

// Approve Customer
const approveCustomer = () => {
  if (!window.confirm(`Approve "${props.customer.name}"?`)) {
    return
  }
  
  approveLoading.value = true
  
  router.post(`/admin/customers/${props.customer.id}/approve`, {}, {
    preserveScroll: false,
    preserveState: false,
    onSuccess: () => {
      approveLoading.value = false
      showFlashMessage('Customer approved successfully! Page will reload...', 'success')
      
      setTimeout(() => {
        router.reload({ preserveScroll: true, preserveState: false })
      }, 1500)
    },
    onError: () => {
      approveLoading.value = false
      showFlashMessage('Failed to approve customer.', 'error')
    }
  })
}

// Other actions
const deleteCustomer = () => {
  if (confirm('Are you sure you want to delete this customer?')) {
    router.delete(`/admin/customers/${props.customer.id}`, {
      preserveScroll: true,
      onSuccess: goBack
    })
  }
}

// Contract actions
const deleteContract = (id) => {
  if (confirm('Are you sure you want to delete this contract?')) {
    router.delete(`/admin/contracts/${id}`, {
      preserveScroll: true,
      onSuccess: () => router.reload()
    })
  }
}

// Mounted
onMounted(() => {
  if (props.flash?.message) {
    flashMessage.value = props.flash.message
    flashMessageType.value = props.flash.type || 'success'
  }
  
  // Initialize forms
  calculateRemainingAmount()
  calculateCommissionAmount()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

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
/* Sidebar has z-index: 30 in sidebar.vue */
/* Header should be lower to appear under sidebar when open on mobile */
#header-section {
  z-index: 20 !important;
}

/* Prevent horizontal overflow */
html, body {
  overflow-x: hidden;
}
</style>