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

    <div class="flex-1 min-w-0 flex flex-col">
      <!-- Mobile top header -->
      <div class="lg:hidden">
        <div class="flex items-center justify-between bg-white shadow-sm border-b border-gray-200 px-4 py-2">
          <button
            @click="mobileSidebarOpen = true"
            class="text-gray-500 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-teal-500"
          >
            <Bars3Icon class="h-6 w-6" />
          </button>
          <div class="flex-1 text-center">
            <h1 class="text-lg font-semibold text-gray-900">Customer Details</h1>
          </div>
          <div class="w-6"></div>
        </div>
      </div>

      <!-- Desktop Header -->
      <div class="hidden lg:block bg-white shadow-sm border-b border-gray-200">
        <div class="px-6 py-4">
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div class="flex items-center space-x-4">
              <button 
                @click="goBack"
                class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2"
              >
                <ArrowLeftIcon class="w-4 h-4" />
                <span>Back to Customers</span>
              </button>
              <div>
                <h1 class="text-2xl font-bold text-gray-900">Customer Details</h1>
                <p class="text-gray-600 mt-1">Manage customer information, payments, and commission</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <span :class="getStatusBadgeClass(customer.status)" class="px-4 py-2 rounded-lg text-sm font-semibold shadow-sm">
                {{ formatStatus(customer.status) }}
              </span>
              <div v-if="customer.payments && customer.payments.length > 0" 
                   class="flex items-center space-x-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white px-4 py-2 rounded-lg shadow-sm">
                <CreditCardIcon class="w-4 h-4" />
                <span>{{ customer.payments?.length || 0 }} payment(s)</span>
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

      <!-- Content -->
      <div class="flex-1 p-4 lg:p-6">
        <div class="max-w-7xl mx-auto">
          <!-- Flash Messages -->
          <div v-if="flashMessage" class="mb-6">
            <div :class="flashMessageClass" class="rounded-lg p-4 shadow-sm border">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <CheckCircleIcon v-if="flashMessageType === 'success'" class="w-5 h-5 text-green-500" />
                  <ExclamationCircleIcon v-else class="w-5 h-5 text-red-500" />
                  <p class="font-medium">{{ flashMessage }}</p>
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
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Customer Information -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:col-span-2">
              <div class="flex items-center space-x-4 mb-6">
                <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-teal-600 rounded-2xl flex items-center justify-center">
                  <UserCircleIcon class="w-8 h-8 text-white" />
                </div>
                <div>
                  <h2 class="text-2xl font-bold text-gray-900">{{ customer.name }}</h2>
                  <p class="text-gray-600">Customer ID: #{{ customer.id }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <p class="text-gray-900 font-medium">{{ customer.email || 'Not provided' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <p class="text-gray-900 font-medium">{{ customer.phone || 'Not provided' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <p class="text-gray-900 font-medium">{{ customer.location || 'Not provided' }}</p>
                  </div>
                </div>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <p class="text-gray-900 font-medium">{{ customer.city?.name || 'Not specified' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Specific Location</label>
                    <p class="text-gray-900 font-medium">{{ customer.specific_location || 'Not specified' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                    <p class="text-gray-900 font-medium">{{ formatDate(customer.created_at) }}</p>
                  </div>
                </div>
              </div>

              <!-- Remarks -->
              <div class="mt-6 pt-6 border-t border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <p class="text-gray-700 bg-gray-50 rounded-lg p-4">
                  {{ customer.remarks || 'No remarks provided.' }}
                </p>
              </div>
            </div>

            <!-- Quick Actions & Timeline -->
            <div class="space-y-6">
              <!-- Quick Actions -->
              <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                  <!-- Edit Button -->
                  <button 
                    v-if="permissions.edit"
                    @click="goToEdit"
                    class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2"
                  >
                    <PencilIcon class="w-4 h-4" />
                    <span>Edit Customer</span>
                  </button>

                  <!-- Approve Button -->
                  <button 
                    v-if="showApproveButton"
                    @click="approveCustomer"
                    :disabled="approveLoading"
                    class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 disabled:from-green-300 disabled:to-green-300 text-white px-4 py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2"
                  >
                    <CheckIcon class="w-4 h-4" />
                    <span v-if="approveLoading">Approving...</span>
                    <span v-else>Approve Customer</span>
                  </button>

                  <!-- Reject Button -->
                  <button 
                    v-if="showRejectButton"
                    @click="openRejectModal"
                    :disabled="rejectLoading"
                    class="w-full bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 disabled:from-red-300 disabled:to-red-300 text-white px-4 py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2"
                  >
                    <XMarkIcon class="w-4 h-4" />
                    <span v-if="rejectLoading">Rejecting...</span>
                    <span v-else>Reject Customer</span>
                  </button>

                  <!-- Delete Customer Button -->
                  <button 
                    v-if="permissions.delete"
                    @click="deleteCustomer"
                    class="w-full bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white px-4 py-3 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center justify-center space-x-2"
                  >
                    <TrashIcon class="w-4 h-4" />
                    <span>Delete Customer</span>
                  </button>
                </div>
              </div>

              <!-- Timeline -->
              <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                <div class="space-y-4">
                  <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">Customer Created</p>
                      <p class="text-sm text-gray-500">{{ formatDate(customer.created_at) }}</p>
                    </div>
                  </div>
                  <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">Last Updated</p>
                      <p class="text-sm text-gray-500">{{ formatDate(customer.updated_at) }}</p>
                    </div>
                  </div>
                  <div v-if="customer.approved_at" class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">Approved</p>
                      <p class="text-sm text-gray-500">{{ formatDate(customer.approved_at) }}</p>
                    </div>
                  </div>
                  <div v-if="customer.payments && customer.payments.length > 0" class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">Payments</p>
                      <p class="text-sm text-gray-500">{{ customer.payments.length }} payment(s)</p>
                    </div>
                  </div>
                  <div v-if="customer.contracts && customer.contracts.length > 0" class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">Contracts</p>
                      <p class="text-sm text-gray-500">{{ customer.contracts.length }} contract(s)</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Summary -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Payment Summary</h3>
                <p class="text-gray-600 text-sm mt-1">Total: {{ customer.payments?.length || 0 }} payment(s) • ${{ totalPaymentAmount.toFixed(2) }}</p>
              </div>
            </div>
            
            <!-- Payment Progress -->
            <div class="mb-6">
              <div class="flex justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Payment Progress</span>
                <span class="text-sm font-medium text-gray-700">{{ customer.payment_progress || 0 }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div 
                  class="h-2.5 rounded-full transition-all duration-500"
                  :class="getPaymentProgressClass(customer.payment_status)"
                  :style="{ width: (customer.payment_progress || 0) + '%' }"
                ></div>
              </div>
              <div class="grid grid-cols-3 gap-4 mt-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                  <label class="block text-xs font-medium text-blue-700">Total Amount</label>
                  <p class="mt-1 text-xl font-semibold text-blue-900">${{ formatNumber(customer.total_payment_amount) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                  <label class="block text-xs font-medium text-green-700">Paid Amount</label>
                  <p class="mt-1 text-xl font-semibold text-green-900">${{ formatNumber(customer.paid_amount) }}</p>
                </div>
                <div class="bg-rose-50 p-4 rounded-lg">
                  <label class="block text-xs font-medium text-rose-700">Remaining</label>
                  <p class="mt-1 text-xl font-semibold text-rose-900">${{ formatNumber(customer.remaining_amount) }}</p>
                </div>
              </div>
            </div>

            <!-- Recent Payments -->
            <div v-if="customer.payments && customer.payments.length > 0">
              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Amount</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Method</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Schedule</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="payment in customer.payments.slice(0, 5)" :key="payment.id" class="hover:bg-gray-50 transition-colors">
                      <td class="px-4 py-4">
                        <span class="font-bold text-gray-900">${{ formatNumber(payment.amount) }}</span>
                      </td>
                      <td class="px-4 py-4">
                        <div class="flex items-center space-x-2">
                          <span class="text-lg">{{ getPaymentMethodIcon(payment.method) }}</span>
                          <span class="text-gray-700">{{ payment.method }}</span>
                        </div>
                      </td>
                      <td class="px-4 py-4">
                        <span class="text-gray-700">{{ payment.schedule }}</span>
                      </td>
                      <td class="px-4 py-4">
                        <span class="text-gray-700">{{ formatDate(payment.payment_date) }}</span>
                      </td>
                      <td class="px-4 py-4">
                        <span :class="getPaymentStatusClass(payment)" class="px-3 py-1 rounded-full text-sm font-semibold">
                          {{ getPaymentStatus(payment) }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <div v-if="customer.payments.length > 5" class="mt-4 pt-4 border-t border-gray-200 text-center">
                <button 
                  @click="viewAllPayments"
                  class="text-purple-600 hover:text-purple-700 font-medium text-sm transition-colors flex items-center space-x-1 mx-auto"
                >
                  <span>View all {{ customer.payments.length }} payments</span>
                  <ChevronRightIcon class="w-4 h-4" />
                </button>
              </div>
            </div>
            <div v-else class="text-center py-8">
              <CreditCardIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
              <p class="text-gray-600">No payments have been added yet.</p>
            </div>
          </div>

          <!-- Commission Summary -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Commission Summary</h3>
                <p v-if="customer.commission_user" class="text-gray-600 text-sm mt-1">
                  Assigned to: {{ customer.commission_user.name }} ({{ customer.commission_rate }}%)
                </p>
                <p v-else class="text-gray-600 text-sm mt-1">No commission user assigned</p>
              </div>
            </div>

            <div class="mb-6">
              <div class="flex justify-between mb-2">
                <span class="text-sm font-medium text-gray-700">Commission Progress</span>
                <span class="text-sm font-medium text-gray-700">{{ customer.commission_progress || 0 }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div 
                  class="h-2.5 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-500"
                  :style="{ width: (customer.commission_progress || 0) + '%' }"
                ></div>
              </div>
              <div class="grid grid-cols-2 gap-4 mt-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                  <label class="block text-xs font-medium text-blue-700">Total Commission</label>
                  <p class="mt-1 text-xl font-semibold text-blue-900">${{ formatNumber(customer.commission_amount) }}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                  <label class="block text-xs font-medium text-green-700">Paid Commission</label>
                  <p class="mt-1 text-xl font-semibold text-green-900">${{ formatNumber(customer.paid_commission) }}</p>
                </div>
              </div>
            </div>

            <!-- Commission User Info -->
            <div v-if="customer.commission_user" class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg">
              <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                  <UserIcon class="w-6 h-6 text-white" />
                </div>
                <div class="flex-1">
                  <h4 class="font-semibold text-gray-900">{{ customer.commission_user.name }}</h4>
                  <p class="text-sm text-gray-600">Commission Rate: {{ customer.commission_rate }}%</p>
                </div>
                <div class="text-right">
                  <p class="text-sm text-gray-600">Total Commission</p>
                  <p class="text-xl font-bold text-blue-900">${{ formatNumber(customer.commission_amount) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Contracts Section -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Contracts</h3>
                  <p class="text-gray-600 text-sm mt-1">Total: {{ (customer.contracts || []).length }} contract(s)</p>
                </div>
                <!-- Create Contract Button - Opens Modal -->
                <button 
                  v-if="permissions.create && isValidForContract"
                  @click="openContractModal"
                  class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 text-sm"
                >
                  <PlusIcon class="w-4 h-4" />
                  <span>Create Contract</span>
                </button>
                <div v-else class="text-sm text-gray-500">
                  <p v-if="!permissions.create">You don't have permission to create contracts</p>
                  <p v-else-if="!isValidForContract">Cannot create contract for customer with status: {{ customer.status }}</p>
                </div>
              </div>
            </div>
            
            <!-- Contracts Table -->
            <div v-if="customer.contracts && customer.contracts.length > 0" class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Value</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="contract in customer.contracts" :key="contract.id" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                      <div class="font-medium text-gray-900">{{ contract.contract_title }}</div>
                      <div class="text-sm text-gray-500 line-clamp-2">{{ contract.contract_description }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="font-semibold text-gray-900">${{ formatNumber(contract.total_value) }}</span>
                    </td>
                    <td class="px-6 py-4">
                      <span :class="getStatusBadgeClass(contract.status)" class="px-3 py-1 rounded-lg text-sm font-semibold">
                        {{ formatStatus(contract.status) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                      {{ formatDate(contract.created_at) }}
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex gap-2">
                        <!-- View button -->
                        <button 
                          @click="viewContract(contract)"
                          class="p-1.5 bg-gradient-to-r from-teal-500 to-teal-600 text-white rounded-lg hover:from-teal-600 hover:to-teal-700 transition-all duration-200 shadow-sm hover:shadow"
                          title="View"
                        >
                          <EyeIcon class="w-4 h-4" />
                        </button>
                        
                        <!-- Edit button - opens modal -->
                        <button 
                          v-if="permissions.edit"
                          @click="editContract(contract)"
                          class="p-1.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-sm hover:shadow"
                          title="Edit"
                        >
                          <PencilIcon class="w-4 h-4" />
                        </button>
                        
                        <!-- Delete button -->
                        <button 
                          v-if="permissions.delete"
                          @click="deleteContract(contract.id)"
                          class="p-1.5 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-sm hover:shadow"
                          title="Delete"
                        >
                          <TrashIcon class="w-4 h-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- No Contracts State -->
            <div v-else class="text-center py-12">
              <DocumentTextIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-600 mb-4">No contracts created for this customer yet.</p>
              <button 
                v-if="permissions.create && isValidForContract"
                @click="openContractModal"
                class="bg-gradient-to-r from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 shadow-sm hover:shadow flex items-center space-x-2 mx-auto"
              >
                <DocumentTextIcon class="w-4 h-4" />
                <span>Create First Contract</span>
              </button>
              <div v-else class="text-sm text-gray-500">
                <p v-if="!permissions.create">You don't have permission to create contracts</p>
                <p v-else-if="!isValidForContract">Cannot create contract for customer with status: {{ customer.status }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <div v-if="showRejectModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md border border-gray-200 shadow-xl">
        <div class="flex items-center space-x-3 mb-4">
          <div class="w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-lg sm:rounded-xl flex items-center justify-center">
            <XMarkIcon class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" />
          </div>
          <div>
            <h3 class="text-base sm:text-lg font-bold text-gray-900">Reject Customer</h3>
            <p class="text-gray-600 text-xs sm:text-sm">Please provide a reason for rejection</p>
          </div>
        </div>
        
        <textarea 
          v-model="rejectForm.reason"
          placeholder="Enter reason for rejecting this customer..."
          class="w-full h-24 sm:h-32 p-3 sm:p-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm resize-none"
          required
        ></textarea>
        
        <div class="flex justify-end space-x-2 sm:space-x-3 mt-4 sm:mt-6">
          <button 
            type="button"
            @click="closeRejectModal" 
            class="px-3 py-2 sm:px-4 sm:py-2.5 text-gray-600 hover:text-gray-800 font-medium text-xs sm:text-sm transition-colors"
          >
            Cancel
          </button>
          <button 
            type="button"
            @click="rejectCustomer"
            :disabled="!rejectForm.reason.trim() || rejectLoading"
            class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 disabled:from-red-400 disabled:to-red-400 text-white px-4 py-2 sm:px-6 sm:py-2.5 rounded-lg font-semibold transition-all duration-200 text-xs sm:text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
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
import { ref, reactive, computed, onMounted } from 'vue'
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
  Bars3Icon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  BanknotesIcon,
  ChevronRightIcon,
  CreditCardIcon,
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
  }
})

// Modal states
const mobileSidebarOpen = ref(false)
const showRejectModal = ref(false)
const showContractModal = ref(false)
const approveLoading = ref(false)
const rejectLoading = ref(false)

// Contract modal data
const selectedContract = ref(null)

// Forms
const rejectForm = reactive({
  reason: ''
})

// Flash message state
const flashMessage = ref(props.flash?.message || '')
const flashMessageType = ref(props.flash?.type || 'success')

// Computed
const flashMessageClass = computed(() => {
  return flashMessageType.value === 'success'
    ? 'bg-green-50 border-green-200 text-green-800'
    : 'bg-red-50 border-red-200 text-red-800'
})

const totalPaymentAmount = computed(() => {
  if (!props.customer.payments || !Array.isArray(props.customer.payments)) return 0
  return props.customer.payments.reduce((sum, payment) => {
    const amount = parseFloat(payment.amount) || 0
    return sum + amount
  }, 0)
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
  // Valid statuses for creating a contract (based on ContractController@create)
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
    half_paid: 'bg-gradient-to-r from-yellow-500 to-yellow-600',
    pending: 'bg-gradient-to-r from-blue-500 to-blue-600',
    not_paid: 'bg-gradient-to-r from-red-500 to-red-600'
  }
  return map[status] || 'bg-gray-400'
}

function getPaymentStatusClass(payment) {
  const today = new Date().setHours(0, 0, 0, 0)
  const paymentDate = new Date(payment.payment_date).setHours(0, 0, 0, 0)
  
  if (paymentDate < today) return 'bg-red-100 text-red-800'
  if (paymentDate === today) return 'bg-yellow-100 text-yellow-800'
  return 'bg-green-100 text-green-800'
}

function getPaymentStatus(payment) {
  const today = new Date().setHours(0, 0, 0, 0)
  const paymentDate = new Date(payment.payment_date).setHours(0, 0, 0, 0)
  
  if (paymentDate < today) return 'Overdue'
  if (paymentDate === today) return 'Due Today'
  return 'Upcoming'
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

// Flash methods
const clearFlashMessage = () => {
  flashMessage.value = ''
  flashMessageType.value = 'success'
}

const showFlashMessage = (msg, type = 'success') => {
  flashMessage.value = msg
  flashMessageType.value = type
}

// Actions
const goBack = () => router.get('/admin/customers')
const goToEdit = () => router.get(`/admin/customers/${props.customer.id}/edit`)

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
        window.location.reload()
      }, 1500)
    },
    onError: (errors) => {
      approveLoading.value = false
      showFlashMessage('Failed to approve customer.', 'error')
    }
  })
}

// Reject Customer
const rejectCustomer = () => {
  if (!rejectForm.reason.trim()) {
    showFlashMessage('Please provide a rejection reason.', 'error')
    return
  }
  
  if (!window.confirm(`Reject "${props.customer.name}"?`)) {
    return
  }
  
  rejectLoading.value = true
  
  router.post(`/admin/customers/${props.customer.id}/reject`, rejectForm, {
    preserveScroll: false,
    preserveState: false,
    onSuccess: () => {
      rejectLoading.value = false
      closeRejectModal()
      showFlashMessage('Customer rejected successfully! Page will reload...', 'success')
      
      setTimeout(() => {
        window.location.reload()
      }, 1500)
    },
    onError: (errors) => {
      rejectLoading.value = false
      showFlashMessage('Failed to reject customer.', 'error')
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
const viewContract = (contract) => {
  router.get(`/admin/contracts/${contract.id}`)
}

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
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>