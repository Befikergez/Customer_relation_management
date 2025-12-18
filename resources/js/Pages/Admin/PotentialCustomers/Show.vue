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

                  <!-- View All Payments Button -->
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
            
            <!-- Payments Table -->
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
            
            <!-- View All Payments Link -->
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
        
        <form @submit.prevent="handlePaymentSubmit" class="space-y-4" ref="paymentFormRef">
          <!-- Add hidden CSRF token field -->
          <input type="hidden" name="_token" :value="csrfToken">
          
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
        
        <!-- Add hidden CSRF token field -->
        <input type="hidden" name="_token" :value="csrfToken">
        
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
import { ref, reactive, computed, onMounted, watch, nextTick } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
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
  },
  csrf_token: {
    type: String,
    default: ''
  }
})

// Reactive state for potentialCustomer so we can update it
const potentialCustomer = ref(props.potentialCustomer)

// Watch for props changes
watch(() => props.potentialCustomer, (newVal) => {
  potentialCustomer.value = newVal
}, { deep: true })

// CSRF token
const csrfToken = computed(() => {
  if (typeof document !== 'undefined') {
    const metaTag = document.querySelector('meta[name="csrf-token"]')
    return metaTag ? metaTag.getAttribute('content') : props.csrf_token
  }
  return props.csrf_token
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

// Submission tracking
const isSubmitting = ref(false)

// Flash message state
const flashMessage = ref(props.flash?.message || '')
const flashMessageType = ref(props.flash?.type || 'success')

// Delete loading state
const deleteLoading = ref(false)

// Use Inertia forms for better CSRF handling
const editForm = useForm({
  potential_customer_name: '',
  email: '',
  phone: '',
  location: '',
  remarks: '',
  status: 'draft',
  city_id: '',
  subcity_id: '',
  _method: 'PUT' // Important for Laravel to recognize PUT requests
})

const proposalForm = useForm({
  title: '',
  description: '',
  price: ''
})

const paymentForm = useForm({
  amount: '',
  method: '',
  schedule: '',
  payment_date: new Date().toISOString().split('T')[0],
  remarks: ''
})

const rejectForm = useForm({
  reason: '',
  _method: 'POST'
})

// Computed properties
const availableSubcities = computed(() => {
  if (!editForm.city_id) return []
  return props.subcities.filter(sub => sub.city_id == editForm.city_id)
})

const flashMessageClass = computed(() => {
  return flashMessageType.value === 'success'
    ? 'bg-green-50 border-green-200 text-green-800'
    : 'bg-red-50 border-red-200 text-red-800'
})

// FIXED: Simplified filteredPayments - show only first 3 payments without complex filtering
const filteredPayments = computed(() => {
  if (!potentialCustomer.value.payments || !Array.isArray(potentialCustomer.value.payments)) return []
  
  // Simply return the first 3 payments
  return potentialCustomer.value.payments.slice(0, 3)
})

const totalPaymentAmount = computed(() => {
  return filteredPayments.value.reduce((sum, payment) => {
    const amount = parseFloat(payment.amount) || 0
    return sum + amount
  }, 0)
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
  
  setTimeout(() => {
    clearFlashMessage()
  }, 5000)
}

// Actions
const goBack = () => {
  router.get('/admin/potential-customers')
}

// Modals
const openEditModal = () => {
  if (!potentialCustomer.value) return
  
  editForm.potential_customer_name = potentialCustomer.value.potential_customer_name || ''
  editForm.email = potentialCustomer.value.email || ''
  editForm.phone = potentialCustomer.value.phone || ''
  editForm.location = potentialCustomer.value.location || ''
  editForm.remarks = potentialCustomer.value.remarks || ''
  editForm.status = potentialCustomer.value.status || 'draft'
  editForm.city_id = potentialCustomer.value.city_id || ''
  editForm.subcity_id = potentialCustomer.value.subcity_id || ''
  
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
  paymentForm.amount = ''
  paymentForm.method = ''
  paymentForm.schedule = ''
  paymentForm.payment_date = new Date().toISOString().split('T')[0]
  paymentForm.remarks = ''
  
  isSubmitting.value = false
  
  showCreatePaymentModal.value = true
}

const closeCreatePaymentModal = () => {
  if (paymentForm.processing) {
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
  editForm.put(`/admin/potential-customers/${potentialCustomer.value.id}`, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page) => {
      closeEditModal()
      showFlashMessage('Customer updated successfully!', 'success')
      
      if (page.props.potentialCustomer) {
        potentialCustomer.value = page.props.potentialCustomer
      }
    },
    onError: (errors) => {
      showFlashMessage('Failed to update customer.', 'error')
    }
  })
}

const submitProposal = () => {
  proposalForm.post(`/admin/potential-customers/${potentialCustomer.value.id}/create-proposal`, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page) => {
      closeCreateProposalModal()
      showFlashMessage('Proposal created successfully!', 'success')
      
      if (page.props.potentialCustomer) {
        potentialCustomer.value = page.props.potentialCustomer
      }
    },
    onError: (errors) => {
      showFlashMessage('Failed to create proposal.', 'error')
    }
  })
}

// Payment submission handler
const handlePaymentSubmit = async (event) => {
  event.preventDefault()
  event.stopPropagation()
  
  if (paymentForm.processing || isSubmitting.value) {
    return
  }
  
  isSubmitting.value = true
  paymentForm.processing = true
  
  if (submitButtonRef.value) {
    submitButtonRef.value.disabled = true
  }
  
  if (!paymentForm.amount || !paymentForm.method || !paymentForm.schedule || !paymentForm.payment_date) {
    showFlashMessage('Please fill in all required fields', 'error')
    paymentForm.processing = false
    isSubmitting.value = false
    if (submitButtonRef.value) submitButtonRef.value.disabled = false
    return
  }
  
  // Convert amount to number
  const paymentData = {
    ...paymentForm.data(),
    amount: parseFloat(paymentForm.amount)
  }
  
  paymentForm.post(`/admin/potential-customers/${potentialCustomer.value.id}/payments`, {
    preserveScroll: true,
    preserveState: true,
    onSuccess: (page) => {
      isSubmitting.value = false
      paymentForm.reset()
      
      closeCreatePaymentModal()
      
      showFlashMessage('Payment created successfully!', 'success')
      
      if (page.props.potentialCustomer) {
        potentialCustomer.value = page.props.potentialCustomer
      }
    },
    onError: (errors) => {
      isSubmitting.value = false
      paymentForm.processing = false
      
      if (submitButtonRef.value) submitButtonRef.value.disabled = false
      
      if (errors.message) {
        showFlashMessage(errors.message, 'error')
      } else if (errors.amount) {
        showFlashMessage(errors.amount[0], 'error')
      } else {
        showFlashMessage('Failed to create payment. Please try again.', 'error')
      }
    },
    onFinish: () => {
      isSubmitting.value = false
      paymentForm.processing = false
      if (submitButtonRef.value) submitButtonRef.value.disabled = false
    }
  })
}

// Payment Actions
const editPayment = (paymentId) => {
  console.log('Editing payment:', paymentId)
  router.get(`/admin/payments/${paymentId}/edit`)
}

// FIXED: Delete payment with proper routing
const deletePayment = async (paymentId) => {
  console.log('Attempting to delete payment:', paymentId)
  
  if (!window.confirm('Are you sure you want to delete this payment?')) {
    return
  }
  
  deleteLoading.value = true
  
  try {
    // Find the payment in the current data
    const payment = potentialCustomer.value.payments?.find(p => p.id === paymentId)
    if (!payment) {
      showFlashMessage('Payment not found.', 'error')
      deleteLoading.value = false
      return
    }
    
    console.log('Payment found:', {
      id: payment.id,
      amount: payment.amount,
      customer_id: payment.customer_id,
      potential_customer_id: payment.potential_customer_id
    })
    
    // Use the direct route that matches the PaymentController destroy method
    // The route should be: /admin/potential-customers/{customerId}/payments/{paymentId}
    const deleteUrl = `/admin/potential-customers/${potentialCustomer.value.id}/payments/${paymentId}`
    
    console.log('Using delete URL:', deleteUrl)
    
    // Use Inertia's delete method with proper configuration
    const response = await router.delete(deleteUrl, {
      preserveScroll: false,
      preserveState: false,
      onSuccess: (page) => {
        console.log('Payment deleted successfully, reloading page...')
        
        // Show success message
        showFlashMessage('Payment deleted successfully!', 'success')
        
        // Force a page reload after a short delay to ensure fresh data
        setTimeout(() => {
          window.location.reload()
        }, 500)
      },
      onError: (errors) => {
        console.error('Delete payment error:', errors)
        
        // Try the global route as fallback
        console.log('Trying global delete route...')
        router.delete(`/admin/payments/${paymentId}`, {
          preserveScroll: false,
          preserveState: false,
          onSuccess: (page) => {
            console.log('Global delete succeeded')
            showFlashMessage('Payment deleted successfully!', 'success')
            
            setTimeout(() => {
              window.location.reload()
            }, 500)
          },
          onError: (secondErrors) => {
            console.error('Global delete also failed:', secondErrors)
            
            // Show detailed error message
            const errorMsg = secondErrors.message || 
                           (typeof secondErrors === 'string' ? secondErrors : 'Unknown error')
            showFlashMessage(`Failed to delete payment: ${errorMsg}`, 'error')
            
            deleteLoading.value = false
          }
        })
      },
      onFinish: () => {
        deleteLoading.value = false
      }
    })
    
  } catch (error) {
    console.error('Unexpected error in deletePayment:', error)
    showFlashMessage('An unexpected error occurred while deleting the payment.', 'error')
    deleteLoading.value = false
  }
}

// View All Payments
const viewAllPayments = () => {
  router.get(`/admin/potential-customers/${potentialCustomer.value.id}/payments`)
}

// Approve Customer
const approveCustomer = async () => {
  const customerName = potentialCustomer.value.potential_customer_name

  if (!window.confirm(`Approve "${customerName}" and move to customers?`)) {
    return
  }

  approveLoading.value = true
  
  // Create a form for the approval request
  const approvalForm = useForm({})
  
  approvalForm.post(`/admin/potential-customers/${potentialCustomer.value.id}/approve`, {
    preserveScroll: false,
    preserveState: false,
    onSuccess: (page) => {
      approveLoading.value = false
      
      // The page will automatically reload with the updated status
      // Flash message will be handled by the redirected page
    },
    onError: (errors) => {
      approveLoading.value = false
      
      if (errors.message) {
        showFlashMessage(errors.message, 'error')
      } else {
        showFlashMessage('Failed to approve customer. Please try again.', 'error')
      }
    },
    onFinish: () => {
      approveLoading.value = false
    }
  })
}

// Reject Customer
const rejectCustomer = async () => {
  if (!rejectForm.reason.trim()) {
    showFlashMessage('Please provide a rejection reason.', 'error')
    return
  }
  
  const customerName = potentialCustomer.value.potential_customer_name
  
  if (!window.confirm(`Reject "${customerName}"?`)) {
    return
  }
  
  rejectLoading.value = true
  
  rejectForm.post(`/admin/potential-customers/${potentialCustomer.value.id}/reject`, {
    preserveScroll: false,
    preserveState: false,
    onSuccess: (page) => {
      rejectLoading.value = false
      closeRejectModal()
      
      // Flash message will be handled by the redirected page
    },
    onError: (errors) => {
      rejectLoading.value = false
      
      if (errors.message) {
        showFlashMessage(errors.message, 'error')
      } else if (errors.reason) {
        showFlashMessage(errors.reason[0], 'error')
      } else {
        showFlashMessage('Failed to reject customer. Please try again.', 'error')
      }
    },
    onFinish: () => {
      rejectLoading.value = false
    }
  })
}

// Other actions
const deleteCustomer = () => {
  if (confirm('Delete this customer?')) {
    router.delete(`/admin/potential-customers/${potentialCustomer.value.id}`, {
      preserveScroll: true,
      onSuccess: goBack
    })
  }
}

const viewProposal = id => {
  router.get(`/admin/proposals/${id}`)
}

const editProposal = id => {
  router.get(`/admin/proposals/${id}/edit`)
}

const deleteProposal = id => {
  if (confirm('Delete this proposal?')) {
    router.delete(`/admin/proposals/${id}`, {
      preserveScroll: true,
      preserveState: true,
      onSuccess: (page) => {
        showFlashMessage('Proposal deleted successfully!', 'success')
        if (page.props.potentialCustomer) {
          potentialCustomer.value = page.props.potentialCustomer
        }
      },
      onError: () => {
        showFlashMessage('Failed to delete proposal.', 'error')
      }
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
  
  // Ensure CSRF token is available
  if (typeof document !== 'undefined') {
    nextTick(() => {
      let csrfMeta = document.querySelector('meta[name="csrf-token"]')
      if (!csrfMeta) {
        csrfMeta = document.createElement('meta')
        csrfMeta.name = 'csrf-token'
        csrfMeta.content = props.csrf_token || ''
        document.head.appendChild(csrfMeta)
      } else if (!csrfMeta.content && props.csrf_token) {
        csrfMeta.content = props.csrf_token
      }
    })
  }
})
</script>