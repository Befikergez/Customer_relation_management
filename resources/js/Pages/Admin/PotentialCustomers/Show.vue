<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Mobile Sidebar Overlay -->
    <div v-if="mobileSidebarOpen" class="fixed inset-0 flex z-40 lg:hidden">
      <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="mobileSidebarOpen = false"></div>
      <div class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
        <div class="absolute top-0 right-0 -mr-12 pt-2">
          <button
            type="button"
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
            type="button"
            @click="mobileSidebarOpen = true"
            class="text-gray-500 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                type="button"
                @click="goBack"
                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
              >
                <ArrowLeftIcon class="w-4 h-4" />
                <span>Back to Customers</span>
              </button>
              <div>
                <h1 class="text-2xl font-bold text-gray-900">Customer Details</h1>
                <p class="text-gray-600 mt-1">Manage customer information, proposals, and payments</p>
              </div>
            </div>
            <div class="flex items-center space-x-3">
              <span :class="getStatusBadgeClass(potentialCustomer.status)" class="px-4 py-2 rounded-full text-sm font-semibold">
                {{ formatStatus(potentialCustomer.status) }}
              </span>
              <div v-if="potentialCustomer.payments && potentialCustomer.payments.length > 0" 
                   class="flex items-center space-x-1 bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">
                <BanknotesIcon class="w-4 h-4" />
                <span>{{ potentialCustomer.payments?.length || 0 }} payment(s)</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 p-4 lg:p-6">
        <div class="max-w-7xl mx-auto">
          <!-- Debug Section -->
          <div v-if="showDebug" class="mb-6 p-4 bg-gray-800 text-white rounded-lg">
            <div class="flex justify-between items-center mb-2">
              <h3 class="font-bold">Inertia Data Debug</h3>
              <button @click="showDebug = !showDebug" class="text-sm bg-gray-700 px-2 py-1 rounded">
                {{ showDebug ? 'Hide' : 'Show' }}
              </button>
            </div>
            <div class="text-sm font-mono overflow-auto max-h-64">
              <pre>{{ debugData }}</pre>
            </div>
          </div>

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
                  type="button"
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
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center">
                  <UserCircleIcon class="w-8 h-8 text-white" />
                </div>
                <div>
                  <h2 class="text-2xl font-bold text-gray-900">{{ potentialCustomer.potential_customer_name }}</h2>
                  <p class="text-gray-600">Potential Customer Lead</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <p class="text-gray-900 font-medium">{{ potentialCustomer.email || 'Not provided' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <p class="text-gray-900 font-medium">{{ potentialCustomer.phone || 'Not provided' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <p class="text-gray-900 font-medium">{{ potentialCustomer.location || 'Not provided' }}</p>
                  </div>
                </div>
                <div class="space-y-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                    <p class="text-gray-900 font-medium">{{ potentialCustomer.city?.name || 'Not specified' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subcity</label>
                    <p class="text-gray-900 font-medium">{{ potentialCustomer.subcity?.name || 'Not specified' }}</p>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Created By</label>
                    <p class="text-gray-900 font-medium">{{ potentialCustomer.created_by?.name || 'System' }}</p>
                  </div>
                </div>
              </div>

              <!-- Remarks -->
              <div class="mt-6 pt-6 border-t border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                <p class="text-gray-700 bg-gray-50 rounded-lg p-4">
                  {{ potentialCustomer.remarks || 'No remarks provided.' }}
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
                    v-if="hasPermission('update')"
                    type="button"
                    @click="openEditModal"
                    class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                  >
                    <PencilIcon class="w-4 h-4" />
                    <span>Edit Customer</span>
                  </button>

                  <!-- Create Proposal Button -->
                  <button 
                    v-if="hasPermission('create') && (potentialCustomer.status === 'draft' || potentialCustomer.status === 'proposal_sent')"
                    type="button"
                    @click="openCreateProposalModal"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                  >
                    <DocumentTextIcon class="w-4 h-4" />
                    <span>Create Proposal</span>
                  </button>

                  <!-- Create Payment Button -->
                  <button 
                    v-if="hasPermission('create') && potentialCustomer.status === 'accepted'"
                    type="button"
                    @click="openCreatePaymentModal"
                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                  >
                    <CurrencyDollarIcon class="w-4 h-4" />
                    <span>Create Payment</span>
                  </button>

                  <!-- Approve Customer Button -->
                  <button 
                    v-if="hasPermission('approve') && (potentialCustomer.status === 'draft' || potentialCustomer.status === 'proposal_sent')"
                    type="button"
                    @click="approveCustomer"
                    :disabled="approveLoading"
                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                  >
                    <CheckIcon class="w-4 h-4" />
                    <span v-if="approveLoading">Approving...</span>
                    <span v-else>Approve Customer</span>
                  </button>

                  <!-- Reject Customer Button -->
                  <button 
                    v-if="hasPermission('reject') && (potentialCustomer.status === 'draft' || potentialCustomer.status === 'proposal_sent')"
                    type="button"
                    @click="openRejectModal"
                    :disabled="rejectLoading"
                    class="w-full bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                  >
                    <XMarkIcon class="w-4 h-4" />
                    <span v-if="rejectLoading">Rejecting...</span>
                    <span v-else>Reject Customer</span>
                  </button>

                  <!-- View All Payments Button - ONLY SHOW WHEN CUSTOMER IS APPROVED -->
                  <button 
                    v-if="hasPermission('view') && potentialCustomer.status === 'accepted'"
                    type="button"
                    @click="viewAllPayments"
                    class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
                  >
                    <BanknotesIcon class="w-4 h-4" />
                    <span>View All Payments ({{ potentialCustomer.payments?.length || 0 }})</span>
                  </button>

                  <!-- Delete Customer Button -->
                  <button 
                    v-if="hasPermission('delete')"
                    type="button"
                    @click="deleteCustomer"
                    class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center justify-center space-x-2"
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
                      <p class="text-sm text-gray-500">{{ formatDate(potentialCustomer.created_at) }}</p>
                    </div>
                  </div>
                  <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">Last Updated</p>
                      <p class="text-sm text-gray-500">{{ formatDate(potentialCustomer.updated_at) }}</p>
                    </div>
                  </div>
                  <div v-if="potentialCustomer.approved_at" class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">Approved</p>
                      <p class="text-sm text-gray-500">{{ formatDate(potentialCustomer.approved_at) }}</p>
                    </div>
                  </div>
                  <div v-if="potentialCustomer.payments && potentialCustomer.payments.length > 0" class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">Payments</p>
                      <p class="text-sm text-gray-500">{{ potentialCustomer.payments.length }} payment(s)</p>
                    </div>
                  </div>
                  <div v-if="potentialCustomer.proposals && potentialCustomer.proposals.length > 0" class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">Proposals</p>
                      <p class="text-sm text-gray-500">{{ potentialCustomer.proposals.length }} proposal(s)</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Summary -->
          <div v-if="potentialCustomer.payments && potentialCustomer.payments.length > 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Payment Summary</h3>
                <p class="text-gray-600 text-sm mt-1">Total: {{ potentialCustomer.payments.length }} payment(s) • ${{ totalPaymentAmount.toFixed(2) }}</p>
              </div>
              <div class="flex items-center space-x-2">
                <button 
                  v-if="hasPermission('create') && potentialCustomer.status === 'accepted'"
                  type="button"
                  @click="openCreatePaymentModal"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                >
                  <PlusIcon class="w-4 h-4" />
                  <span>Add Payment</span>
                </button>
                <button 
                  v-if="hasPermission('view') && potentialCustomer.payments.length > 0 && potentialCustomer.status === 'accepted'"
                  type="button"
                  @click="viewAllPayments"
                  class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                >
                  <EyeIcon class="w-4 h-4" />
                  <span>View All Payments</span>
                </button>
              </div>
            </div>
            
            <!-- Payments Table - FILTER OUT DUPLICATE PAYMENTS -->
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Schedule</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Payment Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="payment in filteredPayments" :key="payment.id" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                      <span class="font-bold text-gray-900">${{ payment.amount }}</span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex items-center space-x-2">
                        <span class="text-lg">{{ getPaymentMethodIcon(payment.method) }}</span>
                        <span class="text-gray-700">{{ payment.method }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="text-gray-700">{{ payment.schedule }}</span>
                    </td>
                    <td class="px-6 py-4">
                      <span class="text-gray-700">{{ formatDate(payment.payment_date) }}</span>
                    </td>
                    <td class="px-6 py-4">
                      <span :class="getPaymentStatusClass(payment)" class="px-3 py-1 rounded-full text-sm font-semibold">
                        {{ getPaymentStatus(payment) }}
                      </span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex gap-2">
                        <button 
                          v-if="hasPermission('edit')"
                          type="button"
                          @click="editPayment(payment.id)"
                          class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
                        >
                          <PencilIcon class="w-3 h-3" />
                          <span>Edit</span>
                        </button>
                        <button 
                          v-if="hasPermission('delete')"
                          type="button"
                          @click="deletePayment(payment.id)"
                          class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
                        >
                          <TrashIcon class="w-3 h-3" />
                          <span>Delete</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- View All Payments Link - ONLY SHOW WHEN CUSTOMER IS APPROVED -->
            <div v-if="potentialCustomer.payments.length > 3 && potentialCustomer.status === 'accepted'" class="mt-4 pt-4 border-t border-gray-200 text-center">
              <button 
                type="button"
                @click="viewAllPayments"
                class="text-purple-600 hover:text-purple-700 font-medium text-sm transition-colors flex items-center space-x-1 mx-auto"
              >
                <span>View all {{ potentialCustomer.payments.length }} payments</span>
                <ChevronRightIcon class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Payment Summary - Empty State -->
          <div v-else class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center mb-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900">Payment Summary</h3>
                <p class="text-gray-600 text-sm mt-1">No payments yet</p>
              </div>
              <div class="flex items-center space-x-2">
                <button 
                  v-if="hasPermission('create') && potentialCustomer.status === 'accepted'"
                  type="button"
                  @click="openCreatePaymentModal"
                  class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                >
                  <PlusIcon class="w-4 h-4" />
                  <span>Add First Payment</span>
                </button>
                <button 
                  v-if="hasPermission('view') && potentialCustomer.status === 'accepted'"
                  type="button"
                  @click="viewAllPayments"
                  class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2"
                >
                  <EyeIcon class="w-4 h-4" />
                  <span>View Payments Page</span>
                </button>
              </div>
            </div>
            <div class="text-center py-8">
              <BanknotesIcon class="w-12 h-12 text-gray-400 mx-auto mb-3" />
              <p class="text-gray-600">No payments have been added yet.</p>
            </div>
          </div>

          <!-- Proposals Section -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
              <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">Proposals</h3>
                  <p class="text-gray-600 text-sm mt-1">Total: {{ (potentialCustomer.proposals || []).length }} proposal(s)</p>
                </div>
                <button 
                  v-if="hasPermission('create') && (potentialCustomer.status === 'draft' || potentialCustomer.status === 'proposal_sent')"
                  type="button"
                  @click="openCreateProposalModal"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 text-sm"
                >
                  <PlusIcon class="w-4 h-4" />
                  <span>Create Proposal</span>
                </button>
              </div>
            </div>
            
            <!-- Proposals Table -->
            <div v-if="potentialCustomer.proposals && potentialCustomer.proposals.length > 0" class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr v-for="proposal in potentialCustomer.proposals" :key="proposal.id" class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                      <div class="font-medium text-gray-900">{{ proposal.title }}</div>
                      <div class="text-sm text-gray-500 line-clamp-2">{{ proposal.description }}</div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="font-semibold text-gray-900">${{ proposal.price }}</span>
                    </td>
                    <td class="px-6 py-4">
                      <div class="space-y-2">
                        <span :class="getProposalStatusBadgeClass(proposal)" class="px-3 py-1 rounded-full text-sm font-semibold">
                          {{ formatProposalStatus(proposal) }}
                        </span>
                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-200 rounded-full h-2">
                          <div 
                            class="h-2 rounded-full transition-all duration-500 ease-out"
                            :class="getProposalProgressClass(proposal)"
                          ></div>
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                      {{ formatDate(proposal.created_at) }}
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex gap-2">
                        <button 
                          type="button"
                          @click="viewProposal(proposal.id)"
                          class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
                        >
                          <EyeIcon class="w-3 h-3" />
                          <span>View</span>
                        </button>
                        <button 
                          v-if="hasPermission('edit') && !proposal.is_rejected && proposal.status === 'unsigned'"
                          type="button"
                          @click="editProposal(proposal.id)"
                          class="bg-gray-600 hover:bg-gray-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
                        >
                          <PencilIcon class="w-3 h-3" />
                          <span>Edit</span>
                        </button>
                        <button 
                          v-if="hasPermission('delete')"
                          type="button"
                          @click="deleteProposal(proposal.id)"
                          class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm font-medium transition-colors flex items-center space-x-1"
                        >
                          <TrashIcon class="w-3 h-3" />
                          <span>Delete</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- No Proposals State -->
            <div v-else class="text-center py-12">
              <DocumentTextIcon class="w-16 h-16 text-gray-400 mx-auto mb-4" />
              <p class="text-gray-600 mb-4">No proposals created for this customer yet.</p>
              <button 
                v-if="hasPermission('create') && (potentialCustomer.status === 'draft' || potentialCustomer.status === 'proposal_sent')"
                type="button"
                @click="openCreateProposalModal"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center space-x-2 mx-auto"
              >
                <DocumentTextIcon class="w-4 h-4" />
                <span>Create First Proposal</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Customer Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-200 shadow-xl">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
              <PencilIcon class="w-5 h-5 text-blue-600" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-900">Edit Potential Customer</h3>
              <p class="text-gray-600 text-sm">Update customer information</p>
            </div>
          </div>
          <button 
            type="button"
            @click="closeEditModal" 
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>
        
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Customer Name *</label>
              <input
                type="text"
                v-model="editForm.potential_customer_name"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter customer name"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
              <input
                type="email"
                v-model="editForm.email"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter email address"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
              <input
                type="text"
                v-model="editForm.phone"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter phone number"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
              <input
                type="text"
                v-model="editForm.location"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter location"
              />
            </div>

            <!-- City Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
              <select
                v-model="editForm.city_id"
                @change="onCityChange"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="">Select City</option>
                <option v-for="city in cities" :key="city.id" :value="city.id">
                  {{ city.name }}
                </option>
              </select>
            </div>

            <!-- Subcity Selection -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Subcity</label>
              <select
                v-model="editForm.subcity_id"
                :disabled="!editForm.city_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
              >
                <option value="">Select Subcity</option>
                <option v-for="subcity in availableSubcities" :key="subcity.id" :value="subcity.id">
                  {{ subcity.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
            <select
              v-model="editForm.status"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="draft">Draft</option>
              <option value="proposal_sent">Proposal Sent</option>
              <option value="accepted">Accepted</option>
              <option value="rejected">Rejected</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
            <textarea
              v-model="editForm.remarks"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
              placeholder="Enter remarks"
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button 
              type="button"
              @click="closeEditModal"
              class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium text-sm transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="editForm.processing"
              class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
            >
              <span v-if="editForm.processing">Updating...</span>
              <span v-else>Update Customer</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Proposal Modal -->
    <div v-if="showCreateProposalModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-200 shadow-xl">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
              <DocumentTextIcon class="w-5 h-5 text-blue-600" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-900">Create Proposal</h3>
              <p class="text-gray-600 text-sm">For: {{ potentialCustomer.potential_customer_name }}</p>
            </div>
          </div>
          <button 
            type="button"
            @click="closeCreateProposalModal" 
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>
        
        <form @submit.prevent="submitProposal" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
            <input
              type="text"
              v-model="proposalForm.title"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Enter proposal title"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
            <textarea
              v-model="proposalForm.description"
              required
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
              placeholder="Enter proposal description"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Price ($) *</label>
            <input
              type="number"
              step="0.01"
              min="0"
              v-model="proposalForm.price"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="0.00"
            />
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button 
              type="button"
              @click="closeCreateProposalModal"
              class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium text-sm transition-colors"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="proposalForm.processing"
              class="bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
            >
              <span v-if="proposalForm.processing">Creating...</span>
              <span v-else>Create Proposal</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Create Payment Modal -->
    <div v-if="showCreatePaymentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto border border-gray-200 shadow-xl">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
              <CurrencyDollarIcon class="w-5 h-5 text-green-600" />
            </div>
            <div>
              <h3 class="text-lg font-bold text-gray-900">Create Payment</h3>
              <p class="text-gray-600 text-sm">For: {{ potentialCustomer.potential_customer_name }}</p>
            </div>
          </div>
          <button 
            type="button"
            @click="closeCreatePaymentModal" 
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <XMarkIcon class="w-6 h-6" />
          </button>
        </div>
        
        <!-- FIXED: Form with proper submit prevention -->
        <form @submit.prevent="handlePaymentSubmit" class="space-y-4" ref="paymentFormRef">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Amount ($) *</label>
              <input
                type="number"
                step="0.01"
                min="0.01"
                v-model="paymentForm.amount"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="0.00"
                @keydown.prevent.enter
                :disabled="paymentForm.processing"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
              <select
                v-model="paymentForm.method"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                :disabled="paymentForm.processing"
              >
                <option value="">Select Payment Method</option>
                <option value="Cash">Cash</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Check">Check</option>
                <option value="Digital Wallet">Digital Wallet</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Payment Schedule *</label>
              <select
                v-model="paymentForm.schedule"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                :disabled="paymentForm.processing"
              >
                <option value="">Select Payment Schedule</option>
                <option value="One-time">One-time</option>
                <option value="Monthly">Monthly</option>
                <option value="Quarterly">Quarterly</option>
                <option value="Semi-annual">Semi-annual</option>
                <option value="Annual">Annual</option>
                <option value="Custom">Custom</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Payment Date *</label>
              <input
                type="date"
                v-model="paymentForm.payment_date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                :disabled="paymentForm.processing"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">Reference Number</label>
              <input
                type="text"
                v-model="paymentForm.reference_number"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                placeholder="Optional reference number"
                :disabled="paymentForm.processing"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
            <textarea
              v-model="paymentForm.remarks"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
              placeholder="Enter payment remarks (optional)"
              :disabled="paymentForm.processing"
            ></textarea>
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button 
              type="button"
              @click="closeCreatePaymentModal"
              :disabled="paymentForm.processing"
              class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium text-sm transition-colors disabled:text-gray-400 disabled:cursor-not-allowed"
            >
              Cancel
            </button>
            <button 
              type="submit"
              :disabled="paymentForm.processing || isSubmitting"
              class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white px-6 py-2 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
              ref="submitButtonRef"
            >
              <span v-if="paymentForm.processing">Creating Payment...</span>
              <span v-else>Create Payment</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Reject Customer Modal -->
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
            class="bg-red-600 hover:bg-red-700 disabled:bg-red-400 text-white px-4 py-2 sm:px-6 sm:py-2.5 rounded-lg font-semibold transition-all duration-200 text-xs sm:text-sm shadow hover:shadow-lg disabled:cursor-not-allowed"
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
import { ref, reactive, computed, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  ArrowLeftIcon,
  CheckIcon,
  XMarkIcon,
  DocumentTextIcon,
  EyeIcon,
  UserCircleIcon,
  PencilIcon,
  PlusIcon,
  TrashIcon,
  CurrencyDollarIcon,
  Bars3Icon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  BanknotesIcon,
  ChevronRightIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  potentialCustomer: {
    type: Object,
    default: () => ({ payments: [] })
  },
  permissions: {
    type: Object,
    default: () => ({})
  },
  tables: {
    type: Array,
    default: () => []
  },
  errors: {
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
  flash: {
    type: Object,
    default: () => ({})
  }
})

// Modal states
const mobileSidebarOpen = ref(false)
const showRejectModal = ref(false)
const showCreateProposalModal = ref(false)
const showEditModal = ref(false)
const showCreatePaymentModal = ref(false)
const loading = ref(false)
const approveLoading = ref(false)
const rejectLoading = ref(false)

// Form refs
const paymentFormRef = ref(null)
const submitButtonRef = ref(null)

// Debug state
const showDebug = ref(false)

// Submission tracking
const isSubmitting = ref(false)
const submissionId = ref(null)

// Flash message state
const flashMessage = ref(props.flash?.message || '')
const flashMessageType = ref(props.flash?.type || 'success')

// Forms
const rejectForm = reactive({
  reason: ''
})

const proposalForm = reactive({
  title: '',
  description: '',
  price: '',
  processing: false
})

const paymentForm = reactive({
  amount: '',
  method: '',
  schedule: '',
  payment_date: new Date().toISOString().split('T')[0],
  reference_number: '',
  remarks: '',
  processing: false
})

const editForm = reactive({
  potential_customer_name: '',
  email: '',
  phone: '',
  location: '',
  remarks: '',
  status: 'draft',
  city_id: '',
  subcity_id: '',
  processing: false
})

// Computed
const availableSubcities = computed(() => {
  if (!editForm.city_id) return []
  return props.subcities.filter(sub => sub.city_id == editForm.city_id)
})

const flashMessageClass = computed(() => {
  return flashMessageType.value === 'success'
    ? 'bg-green-50 border-green-200 text-green-800'
    : 'bg-red-50 border-red-200 text-red-800'
})

// Filter out duplicate payments - only show synced ones
const filteredPayments = computed(() => {
  if (!props.potentialCustomer.payments || !Array.isArray(props.potentialCustomer.payments)) return []
  
  // Filter to only show payments that are properly synced with the customer
  // Look for payments that have the potential_customer_id set
  const uniquePayments = []
  const seenIds = new Set()
  
  props.potentialCustomer.payments.forEach(payment => {
    // Only include payments that are synced with this customer
    if (payment.potential_customer_id === props.potentialCustomer.id) {
      if (!seenIds.has(payment.id)) {
        seenIds.add(payment.id)
        uniquePayments.push(payment)
      }
    }
  })
  
  // If no synced payments found, show all but limit to avoid duplicates
  if (uniquePayments.length === 0) {
    // Fallback: show first 3 unique payments based on amount, method, and date
    const seenCombos = new Set()
    return props.potentialCustomer.payments.filter(payment => {
      const combo = `${payment.amount}-${payment.method}-${payment.payment_date}`
      if (!seenCombos.has(combo)) {
        seenCombos.add(combo)
        return true
      }
      return false
    }).slice(0, 3)
  }
  
  return uniquePayments.slice(0, 3)
})

const totalPaymentAmount = computed(() => {
  return filteredPayments.value.reduce((sum, payment) => {
    const amount = parseFloat(payment.amount) || 0
    return sum + amount
  }, 0)
})

// Debug data
const debugData = computed(() => {
  return {
    potentialCustomer: {
      id: props.potentialCustomer?.id,
      name: props.potentialCustomer?.potential_customer_name,
      paymentsCount: props.potentialCustomer?.payments?.length || 0,
      filteredPaymentsCount: filteredPayments.value.length,
      payments: props.potentialCustomer?.payments || [],
      filteredPayments: filteredPayments.value,
      paymentsArray: Array.isArray(props.potentialCustomer?.payments) ? 'Yes' : 'No',
      paymentsType: typeof props.potentialCustomer?.payments,
      status: props.potentialCustomer?.status
    },
    permissions: props.permissions,
    hasPaymentsPermission: hasPermission('view'),
    totalPaymentAmount: totalPaymentAmount.value,
    flashMessage: flashMessage.value,
    flashMessageType: flashMessageType.value
  }
})

// Helper function to safely check permissions
const hasPermission = (permission) => {
  if (!props.permissions || typeof props.permissions !== 'object') {
    return false
  }
  return Boolean(props.permissions[permission])
}

// Helpers
function getStatusBadgeClass(status) {
  const map = {
    draft: 'bg-blue-100 text-blue-800',
    proposal_sent: 'bg-orange-100 text-orange-800',
    accepted: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return map[status] || 'bg-gray-100 text-gray-800'
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
  return parseFloat(num).toFixed(2)
}

function formatStatus(status) {
  const map = {
    draft: 'Draft',
    proposal_sent: 'Proposal Sent',
    accepted: 'Accepted',
    rejected: 'Rejected'
  }
  return map[status] || status
}

function getProposalStatusBadgeClass(proposal) {
  if (proposal.is_rejected === true || proposal.status === 'reject' || proposal.status === 'rejected')
    return 'bg-red-100 text-red-800'

  if (proposal.status === 'signed')
    return 'bg-green-100 text-green-800'

  if (proposal.status === 'unsigned')
    return 'bg-yellow-100 text-yellow-800'

  return 'bg-gray-100 text-gray-800'
}

function getProposalProgressClass(proposal) {
  if (proposal.is_rejected === true || proposal.status === 'reject' || proposal.status === 'rejected')
    return 'w-full bg-red-500'

  if (proposal.status === 'signed')
    return 'w-full bg-green-500'

  if (proposal.status === 'unsigned')
    return 'w-1/2 bg-yellow-500'

  return 'w-0 bg-gray-400'
}

function formatProposalStatus(proposal) {
  if (proposal.is_rejected === true || proposal.status === 'reject' || proposal.status === 'rejected')
    return 'Rejected'
  if (proposal.status === 'signed')
    return 'Signed'
  if (proposal.status === 'unsigned')
    return 'Unsigned'
  return proposal.status || 'Unknown'
}

function formatDate(dateString) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
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
const goBack = () => router.get('/admin/potential-customers')

// Modals
const openEditModal = () => {
  if (!props.potentialCustomer) return
  Object.assign(editForm, props.potentialCustomer)
  showEditModal.value = true
}
const closeEditModal = () => (showEditModal.value = false)

const openCreateProposalModal = () => {
  proposalForm.title = ''
  proposalForm.description = ''
  proposalForm.price = ''
  showCreateProposalModal.value = true
}
const closeCreateProposalModal = () => (showCreateProposalModal.value = false)

const openCreatePaymentModal = () => {
  // Reset form state
  paymentForm.amount = ''
  paymentForm.method = ''
  paymentForm.schedule = ''
  paymentForm.payment_date = new Date().toISOString().split('T')[0]
  paymentForm.reference_number = ''
  paymentForm.remarks = ''
  paymentForm.processing = false
  
  // Reset submission tracking
  isSubmitting.value = false
  submissionId.value = Date.now() // Generate unique submission ID
  
  showCreatePaymentModal.value = true
}

const closeCreatePaymentModal = () => {
  // Prevent closing if processing
  if (paymentForm.processing) {
    console.log('DEBUG: Cannot close modal while processing payment')
    return
  }
  showCreatePaymentModal.value = false
}

const openRejectModal = () => {
  rejectForm.reason = ''
  showRejectModal.value = true
}
const closeRejectModal = () => {
  showRejectModal.value = false
  rejectForm.reason = ''
}

// Submissions
const submitEdit = () => {
  editForm.processing = true
  router.put(
    `/admin/potential-customers/${props.potentialCustomer.id}`,
    editForm,
    {
      preserveScroll: true,
      onError: () => (editForm.processing = false),
      onSuccess: closeEditModal
    }
  )
}

const submitProposal = () => {
  proposalForm.processing = true
  router.post(
    `/admin/potential-customers/${props.potentialCustomer.id}/create-proposal`,
    proposalForm,
    {
      preserveScroll: true,
      onError: () => (proposalForm.processing = false),
      onSuccess: closeCreateProposalModal
    }
  )
}

// FIXED: Payment submission handler - prevents double submission
const handlePaymentSubmit = async (event) => {
  // Prevent default browser behavior
  event.preventDefault()
  event.stopPropagation()
  
  // Generate submission ID for this attempt
  const currentSubmissionId = Date.now()
  submissionId.value = currentSubmissionId
  
  console.log(`DEBUG [${currentSubmissionId}]: Starting payment submission`)
  
  // Prevent double submission
  if (isSubmitting.value || paymentForm.processing) {
    console.log(`DEBUG [${currentSubmissionId}]: Already submitting, skipping`)
    return
  }
  
  // Set submission flags
  isSubmitting.value = true
  paymentForm.processing = true
  
  // Disable the submit button to prevent double clicks
  if (submitButtonRef.value) {
    submitButtonRef.value.disabled = true
  }
  
  // Validate required fields
  if (!paymentForm.amount || !paymentForm.method || !paymentForm.schedule || !paymentForm.payment_date) {
    showFlashMessage('Please fill in all required fields', 'error')
    paymentForm.processing = false
    isSubmitting.value = false
    if (submitButtonRef.value) submitButtonRef.value.disabled = false
    return
  }
  
  console.log(`DEBUG [${currentSubmissionId}]: Validated fields, preparing data`)
  
  // Prepare payment data - ensure it's synced with customer
  const paymentData = {
    amount: parseFloat(paymentForm.amount),
    method: paymentForm.method,
    schedule: paymentForm.schedule,
    payment_date: paymentForm.payment_date,
    reference_number: paymentForm.reference_number || '',
    remarks: paymentForm.remarks || '',
    potential_customer_id: props.potentialCustomer.id, // Ensure sync
    _submission_id: currentSubmissionId // Add submission ID for tracking
  }
  
  console.log(`DEBUG [${currentSubmissionId}]: Sending payment data:`, paymentData)
  
  try {
    // Use Inertia to submit the form
    await router.post(`/admin/potential-customers/${props.potentialCustomer.id}/payments`, paymentData, {
      preserveScroll: true,
      preserveState: true,
      onStart: () => {
        console.log(`DEBUG [${currentSubmissionId}]: Request started`)
      },
      onSuccess: (page) => {
        console.log(`DEBUG [${currentSubmissionId}]: Payment created successfully!`)
        console.log(`DEBUG [${currentSubmissionId}]: Updated payments:`, page.props.potentialCustomer?.payments)
        
        // Reset form
        paymentForm.processing = false
        isSubmitting.value = false
        
        // Clear form fields
        paymentForm.amount = ''
        paymentForm.method = ''
        paymentForm.schedule = ''
        paymentForm.payment_date = new Date().toISOString().split('T')[0]
        paymentForm.reference_number = ''
        paymentForm.remarks = ''
        
        // Close modal
        closeCreatePaymentModal()
        
        // Show success message
        showFlashMessage('Payment created and synced with customer successfully!', 'success')
        
        // Force a page reload to get fresh data
        setTimeout(() => {
          router.reload({
            only: ['potentialCustomer'],
            preserveScroll: true
          })
        }, 1000)
      },
      onError: (errors) => {
        console.error(`DEBUG [${currentSubmissionId}]: Payment creation errors:`, errors)
        paymentForm.processing = false
        isSubmitting.value = false
        
        if (submitButtonRef.value) submitButtonRef.value.disabled = false
        
        if (errors && typeof errors === 'object') {
          const errorMessages = Object.values(errors).join(', ')
          showFlashMessage(`Error: ${errorMessages}`, 'error')
        } else {
          showFlashMessage('Failed to create payment. Please try again.', 'error')
        }
      },
      onFinish: () => {
        console.log(`DEBUG [${currentSubmissionId}]: Request finished`)
        // Reset submission flags after completion
        setTimeout(() => {
          isSubmitting.value = false
          paymentForm.processing = false
          if (submitButtonRef.value) submitButtonRef.value.disabled = false
        }, 1000)
      }
    })
  } catch (error) {
    console.error(`DEBUG [${currentSubmissionId}]: Exception during payment creation:`, error)
    paymentForm.processing = false
    isSubmitting.value = false
    if (submitButtonRef.value) submitButtonRef.value.disabled = false
    showFlashMessage('An unexpected error occurred. Please try again.', 'error')
  }
}

// Payment Actions
const editPayment = (paymentId) => {
  router.get(`/admin/potential-customers/${props.potentialCustomer.id}/payments/${paymentId}/edit`)
}

const deletePayment = (paymentId) => {
  if (!window.confirm('Are you sure you want to delete this payment?')) {
    return
  }
  
  router.delete(`/admin/potential-customers/${props.potentialCustomer.id}/payments/${paymentId}`, {
    preserveScroll: true,
    onError: () => {
      showFlashMessage('Failed to delete payment.', 'error')
    },
    onSuccess: () => {
      showFlashMessage('Payment deleted successfully!', 'success')
      
      // Reload the page to show updated payment info
      setTimeout(() => {
        router.reload({
          only: ['potentialCustomer'],
          preserveScroll: true
        })
      }, 500)
    }
  })
}

// FIXED: viewAllPayments function
const viewAllPayments = () => {
  window.location.href = `/admin/potential-customers/${props.potentialCustomer.id}/payments`
}

// Approve Customer
const approveCustomer = () => {
  if (!window.confirm(`Approve "${props.potentialCustomer.potential_customer_name}" and move to customers?`)) {
    return
  }
  
  approveLoading.value = true
  
  router.post(`/admin/potential-customers/${props.potentialCustomer.id}/approve`, {}, {
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
  
  if (!window.confirm(`Reject "${props.potentialCustomer.potential_customer_name}"?`)) {
    return
  }
  
  rejectLoading.value = true
  
  router.post(`/admin/potential-customers/${props.potentialCustomer.id}/reject`, rejectForm, {
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
  if (confirm('Delete this customer?')) {
    router.delete(`/admin/potential-customers/${props.potentialCustomer.id}`, {
      preserveScroll: true,
      onSuccess: goBack
    })
  }
}

const viewProposal = id => router.get(`/admin/proposals/${id}`)
const editProposal = id => router.get(`/admin/proposals/${id}/edit`)
const deleteProposal = id => {
  if (confirm('Delete this proposal?')) {
    router.delete(`/admin/proposals/${id}`, {
      preserveScroll: true,
      onSuccess: () => router.reload({ only: ['potentialCustomer'] })
    })
  }
}

const onCityChange = () => (editForm.subcity_id = '')

// Mounted
onMounted(() => {
  if (props.flash?.message) {
    flashMessage.value = props.flash.message
    flashMessageType.value = props.flash.type || 'success'
  }
  
  // Debug: Log initial data
  console.log('DEBUG: Component mounted')
  console.log('DEBUG: Initial potentialCustomer:', props.potentialCustomer)
  console.log('DEBUG: Customer status:', props.potentialCustomer.status)
  console.log('DEBUG: All payments count:', props.potentialCustomer.payments?.length || 0)
  console.log('DEBUG: Filtered payments count:', filteredPayments.value.length)
  console.log('DEBUG: Filtered payments:', filteredPayments.value)
})
</script>

<style scoped>
/* Prevent double-click selection on buttons */
button {
  user-select: none;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
}

/* Disable pointer events during processing */
button:disabled {
  pointer-events: none;
  opacity: 0.7;
  cursor: not-allowed;
}

/* Line clamp for proposal description */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Prevent form element double submission */
form {
  position: relative;
}

/* Add overlay when processing */
form.processing::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.7);
  z-index: 10;
  pointer-events: none;
}

/* Disable text selection on form inputs */
input:disabled, 
select:disabled, 
textarea:disabled {
  cursor: not-allowed;
  background-color: #f9fafb;
}
</style>