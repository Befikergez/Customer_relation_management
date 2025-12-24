<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-blue-50 flex">
    <!-- Sidebar -->
    <Sidebar :tables="tables" />
    
    <!-- Main Content -->
    <main class="flex-1 min-h-screen transition-all duration-300 overflow-x-hidden">
      <!-- Animated Background Elements -->
      <div class="absolute top-0 right-0 w-72 h-72 bg-gradient-to-br from-blue-100 to-cyan-100 rounded-full blur-3xl opacity-20 animate-pulse"></div>
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-gradient-to-tr from-blue-100 to-cyan-200 rounded-full blur-3xl opacity-30 animate-bounce"></div>
      
      <div class="relative">
        <!-- Fixed Header -->
        <header class="bg-white/80 backdrop-blur-sm border-b border-blue-200/50 px-4 md:px-6 py-4 md:py-6 sticky top-0 z-20">
          <div class="flex items-center">
            <!-- Left spacing for hamburger menu on mobile -->
            <div class="md:hidden w-12 flex-shrink-0"></div>
            
            <div class="flex-1 flex items-center justify-between">
              <!-- Title and User Info -->
              <div class="flex items-center space-x-3 md:space-x-4">
                <!-- Dashboard Icon and Title -->
                <div class="flex items-center">
                  <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <BarChart3 class="w-5 h-5 md:w-6 md:h-6 text-white" />
                  </div>
                  <div class="ml-3">
                    <h1 class="text-xl md:text-3xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                      Dashboard
                    </h1>
                    <!-- User Info under Dashboard -->
                    <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-2 mt-1">
                      <p class="text-sm md:text-base text-slate-600">
                        Welcome back, {{ user?.name || 'User' }}
                      </p>
                      <span class="text-xs bg-gradient-to-r from-blue-100 to-cyan-100 text-blue-600 px-2 py-1 rounded-full capitalize self-start">
                        {{ userRole }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Mobile: Empty div for spacing -->
              <div class="md:hidden w-0"></div>
            </div>
          </div>
        </header>

        <!-- Flash Message -->
        <div v-if="flashSuccess" class="mx-4 md:mx-6 mt-4 md:mt-6 bg-gradient-to-r from-emerald-100 to-cyan-100 text-emerald-700 p-4 rounded-2xl border border-emerald-200/50 shadow-lg backdrop-blur-sm relative z-10">
          <div class="flex items-center space-x-3">
            <CheckCircle class="w-5 h-5 text-emerald-600" />
            <span class="text-sm md:text-base">{{ flashSuccess }}</span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4 md:p-6 relative">
          <!-- Customer-only view -->
          <div v-if="!showCounts" class="relative bg-gradient-to-br from-white to-blue-50 rounded-3xl shadow-xl p-6 md:p-12 text-center border border-blue-200/50 overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-cyan-500"></div>
            <div class="absolute -top-20 -right-20 w-40 h-40 bg-blue-100 rounded-full opacity-20"></div>
            <div class="absolute -bottom-20 -left-20 w-40 h-40 bg-cyan-100 rounded-full opacity-20"></div>
            
            <div class="relative z-10">
              <div class="w-16 h-16 md:w-24 md:h-24 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-3xl flex items-center justify-center mx-auto mb-4 md:mb-6 shadow-lg">
                <FileText class="w-6 h-6 md:w-10 md:h-10 text-white" />
              </div>
              <h2 class="text-2xl md:text-4xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent mb-3 md:mb-4">
                Welcome to Your CRM Portal
              </h2>
              <p class="text-slate-600 text-base md:text-lg mb-6 md:mb-8 max-w-md mx-auto">
                Access your proposals and contracts in one place
              </p>
              <div class="flex flex-col md:flex-row justify-center gap-3 md:gap-4">
                <a href="/admin/proposals" class="bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white px-6 py-3 md:px-8 md:py-4 rounded-2xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 flex items-center justify-center space-x-3">
                  <FileText class="w-4 h-4 md:w-5 md:h-5" />
                  <span>View Proposals</span>
                </a>
                <a href="/admin/contracts" class="bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-600 hover:to-blue-700 text-white px-6 py-3 md:px-8 md:py-4 rounded-2xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105 flex items-center justify-center space-x-3">
                  <Briefcase class="w-4 h-4 md:w-5 md:h-5" />
                  <span>View Contracts</span>
                </a>
              </div>
            </div>
          </div>

          <!-- Admin/Staff view -->
          <div v-else>
            <!-- Card Counts Section -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
              <!-- Customers Card -->
              <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-4 md:p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
                <div class="absolute top-0 right-0 w-16 h-16 md:w-32 md:h-32 bg-cyan-400/30 rounded-full -translate-y-8 translate-x-8 md:-translate-y-16 md:translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-12 h-12 md:w-24 md:h-24 bg-cyan-300/20 rounded-full translate-y-6 -translate-x-6 md:translate-y-12 md:-translate-x-12"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                  <div>
                    <h2 class="text-base md:text-lg font-semibold text-white">Customers</h2>
                    <p class="text-2xl md:text-3xl font-bold text-white mt-1 md:mt-2">{{ safeCounts.customers ?? 0 }}</p>
                    <div class="flex items-center space-x-1 mt-2 md:mt-3">
                      <Users class="w-3 h-3 md:w-4 md:h-4 text-cyan-200" />
                      <span class="text-xs md:text-sm text-cyan-100">Active clients</span>
                    </div>
                  </div>
                  <div class="w-12 h-12 md:w-16 md:h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                    <Users class="w-5 h-5 md:w-8 md:h-8 text-white" />
                  </div>
                </div>
              </div>

              <!-- Industries Card -->
              <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-4 md:p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
                <div class="absolute top-0 right-0 w-16 h-16 md:w-32 md:h-32 bg-cyan-400/30 rounded-full -translate-y-8 translate-x-8 md:-translate-y-16 md:translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-12 h-12 md:w-24 md:h-24 bg-cyan-300/20 rounded-full translate-y-6 -translate-x-6 md:translate-y-12 md:-translate-x-12"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                  <div>
                    <h2 class="text-base md:text-lg font-semibold text-white">Industries</h2>
                    <p class="text-2xl md:text-3xl font-bold text-white mt-1 md:mt-2">{{ safeCounts.industries ?? 0 }}</p>
                    <div class="flex items-center space-x-1 mt-2 md:mt-3">
                      <Globe class="w-3 h-3 md:w-4 md:h-4 text-cyan-200" />
                      <span class="text-xs md:text-sm text-cyan-100">Diverse sectors</span>
                    </div>
                  </div>
                  <div class="w-12 h-12 md:w-16 md:h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                    <Globe class="w-5 h-5 md:w-8 md:h-8 text-white" />
                  </div>
                </div>
              </div>

              <!-- Opportunities Card -->
              <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-4 md:p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
                <div class="absolute top-0 right-0 w-16 h-16 md:w-32 md:h-32 bg-cyan-400/30 rounded-full -translate-y-8 translate-x-8 md:-translate-y-16 md:translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-12 h-12 md:w-24 md:h-24 bg-cyan-300/20 rounded-full translate-y-6 -translate-x-6 md:translate-y-12 md:-translate-x-12"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                  <div>
                    <h2 class="text-base md:text-lg font-semibold text-white">Opportunities</h2>
                    <p class="text-2xl md:text-3xl font-bold text-white mt-1 md:mt-2">{{ safeCounts.opportunities ?? 0 }}</p>
                    <div class="flex items-center space-x-1 mt-2 md:mt-3">
                      <Briefcase class="w-3 h-3 md:w-4 md:h-4 text-cyan-200" />
                      <span class="text-xs md:text-sm text-cyan-100">Hot leads</span>
                    </div>
                  </div>
                  <div class="w-12 h-12 md:w-16 md:h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                    <Briefcase class="w-5 h-5 md:w-8 md:h-8 text-white" />
                  </div>
                </div>
              </div>

              <!-- Contracts Card -->
              <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-4 md:p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
                <div class="absolute top-0 right-0 w-16 h-16 md:w-32 md:h-32 bg-cyan-400/30 rounded-full -translate-y-8 translate-x-8 md:-translate-y-16 md:translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-12 h-12 md:w-24 md:h-24 bg-cyan-300/20 rounded-full translate-y-6 -translate-x-6 md:translate-y-12 md:-translate-x-12"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                  <div>
                    <h2 class="text-base md:text-lg font-semibold text-white">Contracts</h2>
                    <p class="text-2xl md:text-3xl font-bold text-white mt-1 md:mt-2">{{ safeCounts.contracts ?? 0 }}</p>
                    <div class="flex items-center space-x-1 mt-2 md:mt-3">
                      <FileText class="w-3 h-3 md:w-4 md:h-4 text-cyan-200" />
                      <span class="text-xs md:text-sm text-cyan-100">Active deals</span>
                    </div>
                  </div>
                  <div class="w-12 h-12 md:w-16 md:h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                    <FileText class="w-5 h-5 md:w-8 md:h-8 text-white" />
                  </div>
                </div>
              </div>

              <!-- Proposals Card -->
              <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-4 md:p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
                <div class="absolute top-0 right-0 w-16 h-16 md:w-32 md:h-32 bg-cyan-400/30 rounded-full -translate-y-8 translate-x-8 md:-translate-y-16 md:translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-12 h-12 md:w-24 md:h-24 bg-cyan-300/20 rounded-full translate-y-6 -translate-x-6 md:translate-y-12 md:-translate-x-12"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                  <div>
                    <h2 class="text-base md:text-lg font-semibold text-white">Proposals</h2>
                    <p class="text-2xl md:text-3xl font-bold text-white mt-1 md:mt-2">{{ safeCounts.proposals ?? 0 }}</p>
                    <div class="flex items-center space-x-1 mt-2 md:mt-3">
                      <Send class="w-3 h-3 md:w-4 md:h-4 text-cyan-200" />
                      <span class="text-xs md:text-sm text-cyan-100">In progress</span>
                    </div>
                  </div>
                  <div class="w-12 h-12 md:w-16 md:h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                    <Send class="w-5 h-5 md:w-8 md:h-8 text-white" />
                  </div>
                </div>
              </div>

              <!-- Rejected Opportunities Card -->
              <div class="group relative overflow-hidden bg-gradient-to-br from-cyan-500 to-blue-600 rounded-2xl p-4 md:p-6 border border-cyan-400/50 shadow-lg hover:shadow-xl transition-all duration-500 hover:scale-105">
                <div class="absolute top-0 right-0 w-16 h-16 md:w-32 md:h-32 bg-cyan-400/30 rounded-full -translate-y-8 translate-x-8 md:-translate-y-16 md:translate-x-16"></div>
                <div class="absolute bottom-0 left-0 w-12 h-12 md:w-24 md:h-24 bg-cyan-300/20 rounded-full translate-y-6 -translate-x-6 md:translate-y-12 md:-translate-x-12"></div>
                
                <div class="relative z-10 flex items-center justify-between">
                  <div>
                    <h2 class="text-base md:text-lg font-semibold text-white">Rejected</h2>
                    <p class="text-2xl md:text-3xl font-bold text-white mt-1 md:mt-2">{{ safeCounts.rejected_opportunities ?? 0 }}</p>
                    <div class="flex items-center space-x-1 mt-2 md:mt-3">
                      <XCircle class="w-3 h-3 md:w-4 md:h-4 text-cyan-200" />
                      <span class="text-xs md:text-sm text-cyan-100">Not pursued</span>
                    </div>
                  </div>
                  <div class="w-12 h-12 md:w-16 md:h-16 bg-cyan-400/20 rounded-2xl flex items-center justify-center group-hover:rotate-12 transition-transform duration-300 border border-cyan-300/50">
                    <XCircle class="w-5 h-5 md:w-8 md:h-8 text-white" />
                  </div>
                </div>
              </div>
            </div>

            <!-- Opportunities and Customer Conversion Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
              <!-- Opportunities Chart -->
              <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl border border-blue-200/50 p-4 md:p-6 shadow-lg hover:shadow-xl transition-all duration-500">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-cyan-500 rounded-t-2xl"></div>
                <div class="flex items-center gap-3 mb-4 md:mb-6">
                  <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-xl flex items-center justify-center">
                    <BarChart3 class="w-5 h-5 md:w-6 md:h-6 text-white" />
                  </div>
                  <div>
                    <h2 class="font-bold text-slate-900 text-base md:text-lg">Opportunities Overview</h2>
                    <p class="text-slate-600 text-xs md:text-sm">Last 7 days performance</p>
                  </div>
                </div>
                
                <div class="h-64 md:h-80">
                  <Barchart :chartData="safeChartData" />
                </div>
              </div>

              <!-- Customer Conversion Funnel -->
              <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl border border-cyan-200/50 p-4 md:p-6 shadow-lg hover:shadow-xl transition-all duration-500">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-t-2xl"></div>
                <div class="flex items-center gap-3 mb-4 md:mb-6">
                  <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center">
                    <Users class="w-5 h-5 md:w-6 md:h-6 text-white" />
                  </div>
                  <div>
                    <h2 class="font-bold text-slate-900 text-base md:text-lg">Customer Conversion</h2>
                    <p class="text-slate-600 text-xs md:text-sm">Potential → Approved → Rejected</p>
                  </div>
                </div>
                
                <!-- Conversion Funnel -->
                <div class="space-y-4 md:space-y-6">
                  <div class="grid grid-cols-3 gap-3 md:gap-4">
                    <div class="text-center p-3 md:p-4 bg-blue-50/50 rounded-xl border border-blue-200">
                      <div class="text-xl md:text-3xl font-bold text-blue-600">
                        {{ customerAnalyticsSummary.potential_count || 0 }}
                      </div>
                      <div class="text-xs md:text-sm font-medium text-blue-500">Potential</div>
                      <div class="text-xs text-blue-400 mt-1">
                        {{ customerAnalyticsSummary.potential_today || 0 }} today
                      </div>
                    </div>
                    <div class="text-center p-3 md:p-4 bg-emerald-50/50 rounded-xl border border-emerald-200">
                      <div class="text-xl md:text-3xl font-bold text-emerald-600">
                        {{ customerAnalyticsSummary.approved_count || 0 }}
                      </div>
                      <div class="text-xs md:text-sm font-medium text-emerald-500">Approved</div>
                      <div class="text-xs text-emerald-400 mt-1">
                        {{ customerAnalyticsSummary.approved_today || 0 }} today
                      </div>
                    </div>
                    <div class="text-center p-3 md:p-4 bg-red-50/50 rounded-xl border border-red-200">
                      <div class="text-xl md:text-3xl font-bold text-red-600">
                        {{ customerAnalyticsSummary.rejected_count || 0 }}
                      </div>
                      <div class="text-xs md:text-sm font-medium text-red-500">Rejected</div>
                      <div class="text-xs text-red-400 mt-1">
                        {{ customerAnalyticsSummary.rejected_today || 0 }} today
                      </div>
                    </div>
                  </div>
                  
                  <!-- Conversion Rates -->
                  <div class="grid grid-cols-2 gap-3 md:gap-4">
                    <div class="text-center p-3 md:p-4 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-xl text-white">
                      <div class="text-lg md:text-2xl font-bold">
                        {{ customerAnalyticsSummary.approval_rate || '0' }}%
                      </div>
                      <div class="text-xs md:text-sm font-medium">Approval Rate</div>
                    </div>
                    <div class="text-center p-3 md:p-4 bg-gradient-to-r from-cyan-500 to-blue-500 rounded-xl text-white">
                      <div class="text-lg md:text-2xl font-bold">
                        {{ customerAnalyticsSummary.rejection_rate || '0' }}%
                      </div>
                      <div class="text-xs md:text-sm font-medium">Rejection Rate</div>
                    </div>
                  </div>
                  
                  <!-- Customer Value -->
                  <div class="grid grid-cols-2 gap-3 md:gap-4">
                    <div class="text-center p-3 bg-blue-50/50 rounded-xl">
                      <div class="text-lg md:text-xl font-bold text-blue-600">
                        ${{ formatNumber(customerAnalyticsSummary.total_value_potential || 0) }}
                      </div>
                      <div class="text-xs md:text-sm text-blue-500">Potential Value</div>
                    </div>
                    <div class="text-center p-3 bg-emerald-50/50 rounded-xl">
                      <div class="text-lg md:text-xl font-bold text-emerald-600">
                        ${{ formatNumber(customerAnalyticsSummary.total_value_approved || 0) }}
                      </div>
                      <div class="text-xs md:text-sm text-emerald-500">Approved Value</div>
                    </div>
                  </div>
                  
                  <!-- Quick Actions -->
                  <div class="flex flex-col sm:flex-row gap-2 md:gap-3">
                    <a 
                      href="/admin/customers?status=draft"
                      class="flex-1 text-center bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 md:px-4 rounded-xl transition text-sm md:text-base"
                    >
                      Review Potential
                    </a>
                    <a 
                      href="/admin/customers?status=accepted"
                      class="flex-1 text-center bg-emerald-500 hover:bg-emerald-600 text-white py-2 px-3 md:px-4 rounded-xl transition text-sm md:text-base"
                    >
                      View Approved
                    </a>
                    <a 
                      href="/admin/customers?status=rejected"
                      class="flex-1 text-center bg-red-500 hover:bg-red-600 text-white py-2 px-3 md:px-4 rounded-xl transition text-sm md:text-base"
                    >
                      View Rejected
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Payment & Commission Status Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6 md:mb-8">
              <!-- Payment Status -->
              <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl border border-blue-200/50 p-4 md:p-6 shadow-lg hover:shadow-xl transition-all duration-500">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-cyan-500 rounded-t-2xl"></div>
                <div class="flex items-center gap-3 mb-4 md:mb-6">
                  <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-xl flex items-center justify-center">
                    <CreditCard class="w-5 h-5 md:w-6 md:h-6 text-white" />
                  </div>
                  <div>
                    <h2 class="font-bold text-slate-900 text-base md:text-lg">Payment Status</h2>
                    <p class="text-slate-600 text-xs md:text-sm">Distribution across all customers</p>
                  </div>
                </div>
                
                <div class="space-y-3 md:space-y-4">
                  <!-- Payment Status Visualization -->
                  <div v-if="hasPaymentData" class="space-y-3 md:space-y-4">
                    <!-- Payment Status Bars -->
                    <div v-for="(status, index) in paymentStatusData.labels" :key="index" class="space-y-2">
                      <div class="flex justify-between text-xs md:text-sm">
                        <span class="font-medium text-slate-700 capitalize">{{ formatStatus(status) }}</span>
                        <div class="flex items-center gap-1 md:gap-2">
                          <span class="font-semibold text-blue-600">{{ paymentStatusData.data[index] }}</span>
                          <span class="text-xs text-slate-500">(${{ formatNumber(paymentStatusData.amounts?.[index] || 0) }})</span>
                        </div>
                      </div>
                      <div class="h-2 md:h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div 
                          :style="{ 
                            width: getPaymentPercentage(status, paymentStatusData.data[index]) + '%',
                            backgroundColor: paymentStatusData.colors[status] 
                          }"
                          class="h-full rounded-full transition-all duration-500"
                        ></div>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-6 md:py-8 text-gray-400">
                    <CreditCard class="w-8 h-8 md:w-12 md:h-12 mx-auto mb-2 md:mb-3 opacity-50" />
                    <p class="text-sm">No payment data available</p>
                  </div>
                  
                  <!-- Payment Summary -->
                  <div class="grid grid-cols-2 gap-3 md:gap-4 pt-3 md:pt-4 border-t border-blue-100">
                    <div class="text-center p-2 md:p-3 bg-blue-50/50 rounded-xl">
                      <div class="text-lg md:text-2xl font-bold text-blue-600">
                        ${{ formatNumber(paymentCommissionSummary.total_payments || 0) }}
                      </div>
                      <div class="text-xs md:text-sm text-blue-500">Total Payments</div>
                    </div>
                    <div class="text-center p-2 md:p-3 bg-cyan-50/50 rounded-xl">
                      <div class="text-lg md:text-2xl font-bold text-cyan-600">
                        {{ paymentCommissionSummary.pending_payments || 0 }}
                      </div>
                      <div class="text-xs md:text-sm text-cyan-500">Pending Payments</div>
                    </div>
                    <div class="text-center p-2 md:p-3 bg-orange-50/50 rounded-xl">
                      <div class="text-lg md:text-2xl font-bold text-orange-600">
                        {{ paymentCommissionSummary.overdue_payments || 0 }}
                      </div>
                      <div class="text-xs md:text-sm text-orange-500">Overdue Payments</div>
                    </div>
                    <div class="text-center p-2 md:p-3 bg-emerald-50/50 rounded-xl">
                      <div class="text-lg md:text-2xl font-bold text-emerald-600">
                        {{ paymentCommissionSummary.payment_collection_rate || '0' }}%
                      </div>
                      <div class="text-xs md:text-sm text-emerald-500">Collection Rate</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Commission Status -->
              <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl border border-cyan-200/50 p-4 md:p-6 shadow-lg hover:shadow-xl transition-all duration-500">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-cyan-400 to-blue-500 rounded-t-2xl"></div>
                <div class="flex items-center gap-3 mb-4 md:mb-6">
                  <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-xl flex items-center justify-center">
                    <DollarSign class="w-5 h-5 md:w-6 md:h-6 text-white" />
                  </div>
                  <div>
                    <h2 class="font-bold text-slate-900 text-base md:text-lg">Commission Status</h2>
                    <p class="text-slate-600 text-xs md:text-sm">Distribution across all customers</p>
                  </div>
                </div>
                
                <div class="space-y-3 md:space-y-4">
                  <!-- Commission Status Visualization -->
                  <div v-if="hasCommissionData" class="space-y-3 md:space-y-4">
                    <!-- Commission Status Dots -->
                    <div v-for="(status, index) in commissionStatusData.labels" :key="index" 
                         class="flex items-center justify-between p-2 md:p-3 rounded-lg border text-xs md:text-sm"
                         :style="{ borderColor: commissionStatusData.colors[status] + '30', backgroundColor: commissionStatusData.colors[status] + '10' }">
                      <div class="flex items-center space-x-2 md:space-x-3">
                        <div class="w-2 h-2 md:w-3 md:h-3 rounded-full" :style="{ backgroundColor: commissionStatusData.colors[status] }"></div>
                        <span class="font-medium text-slate-700 capitalize">{{ formatStatus(status) }}</span>
                      </div>
                      <div class="flex items-center space-x-2 md:space-x-4">
                        <span class="font-semibold text-slate-900">{{ commissionStatusData.data[index] }}</span>
                        <span class="text-xs text-slate-500">
                          ${{ formatNumber(commissionStatusData.amounts?.[index] || 0) }}
                        </span>
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-center py-6 md:py-8 text-gray-400">
                    <DollarSign class="w-8 h-8 md:w-12 md:h-12 mx-auto mb-2 md:mb-3 opacity-50" />
                    <p class="text-sm">No commission data available</p>
                  </div>
                  
                  <!-- Commission Summary -->
                  <div class="grid grid-cols-2 gap-3 md:gap-4 pt-3 md:pt-4 border-t border-cyan-100">
                    <div class="text-center p-2 md:p-3 bg-blue-50/50 rounded-xl">
                      <div class="text-lg md:text-2xl font-bold text-blue-600">
                        ${{ formatNumber(paymentCommissionSummary.total_commissions || 0) }}
                      </div>
                      <div class="text-xs md:text-sm text-blue-500">Total Commissions</div>
                    </div>
                    <div class="text-center p-2 md:p-3 bg-cyan-50/50 rounded-xl">
                      <div class="text-lg md:text-2xl font-bold text-cyan-600">
                        {{ paymentCommissionSummary.pending_payments || 0 }}
                      </div>
                      <div class="text-xs md:text-sm text-cyan-500">Pending Commissions</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Recent Activities Section -->
            <div class="grid grid-cols-1 gap-4 md:gap-6 mb-6 md:mb-8">
              <!-- Recent Activities -->
              <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl border border-blue-200/50 p-4 md:p-6 shadow-lg hover:shadow-xl transition-all duration-500">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-400 to-cyan-500 rounded-t-2xl"></div>
                <div class="flex items-center gap-3 mb-4 md:mb-6">
                  <div class="w-10 h-10 md:w-12 md:h-12 bg-gradient-to-br from-blue-400 to-cyan-500 rounded-xl flex items-center justify-center">
                    <Clock class="w-5 h-5 md:w-6 md:h-6 text-white" />
                  </div>
                  <div>
                    <h2 class="font-bold text-slate-900 text-base md:text-lg">Recent Activities</h2>
                    <p class="text-slate-600 text-xs md:text-sm">Latest system activities</p>
                  </div>
                </div>

                <div class="space-y-2 md:space-y-3">
                  <div 
                    v-for="(activity, index) in recentActivities" 
                    :key="index"
                    class="flex items-center justify-between p-3 md:p-4 bg-gradient-to-r from-slate-50 to-white rounded-xl border border-slate-200/50 hover:border-cyan-300/50 transition-all duration-300 group/item hover:scale-105"
                  >
                    <div class="flex items-center space-x-3 md:space-x-4">
                      <div class="w-8 h-8 md:w-10 md:h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-lg flex items-center justify-center text-white font-bold text-xs md:text-sm">
                        {{ index + 1 }}
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="font-semibold text-slate-900 text-sm md:text-base truncate">{{ activity.message }}</div>
                        <div class="text-xs text-slate-500">{{ activity.time }}</div>
                      </div>
                    </div>
                    <div class="text-right ml-2">
                      <div class="font-bold text-slate-900 text-xs capitalize">{{ activity.type.replace('_', ' ') }}</div>
                    </div>
                  </div>
                  
                  <div v-if="recentActivities.length === 0" class="text-center py-6 md:py-8 text-slate-500">
                    <Clock class="w-8 h-8 md:w-12 md:h-12 mx-auto mb-2 md:mb-3 text-slate-300" />
                    <p class="text-sm">No recent activities</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import Sidebar from './Sidebar.vue'
import Barchart from './Barchart.vue'
import { computed } from 'vue'
import { 
  Users, Briefcase, FileText, BarChart3, CheckCircle, 
  XCircle, Send, Globe, CreditCard, DollarSign, Clock
} from 'lucide-vue-next'

// Props from Inertia
const props = defineProps({
  user: Object,
  tables: Array,
  counts: Object,
  paymentCommissionData: Object,
  customerAnalytics: Object,
  chartData: Object,
  recentActivities: Array,
  proposalStats: Object,
  userRole: String
})

// Safe flash messages with proper error handling
const page = usePage()
const flashSuccess = computed(() => {
  return page.props.flash?.success || null
})

// Role detection with fallback
const userRole = computed(() => {
  return props.userRole || 
         (props.user?.roles?.length ? props.user.roles[0].name : null) || 
         'user'
})

const showCounts = computed(() => !['customer'].includes(userRole.value))

// Payment & Commission Data with safe fallbacks
const safePaymentCommissionData = computed(() => {
  const data = props.paymentCommissionData || {}
  return {
    payment_status: data.payment_status || {
      labels: ['pending', 'paid', 'overdue', 'failed'],
      data: [0, 0, 0, 0],
      amounts: [0, 0, 0, 0],
      colors: {
        'pending': '#FFB74D',
        'paid': '#4CAF50',
        'overdue': '#F44336',
        'failed': '#757575'
      }
    },
    commission_status: data.commission_status || {
      labels: ['pending', 'calculated', 'paid', 'disputed'],
      data: [0, 0, 0, 0],
      amounts: [0, 0, 0, 0],
      colors: {
        'pending': '#2196F3',
        'calculated': '#FF9800',
        'paid': '#4CAF50',
        'disputed': '#F44336'
      }
    },
    summary: data.summary || {
      total_payments: 0,
      total_commissions: 0,
      pending_payments: 0,
      overdue_payments: 0,
      payment_collection_rate: 0
    }
  }
})

// Extract payment and commission data
const paymentStatusData = computed(() => safePaymentCommissionData.value.payment_status)
const commissionStatusData = computed(() => safePaymentCommissionData.value.commission_status)
const paymentCommissionSummary = computed(() => safePaymentCommissionData.value.summary)

// Customer Analytics Data with safe fallbacks
const safeCustomerAnalytics = computed(() => {
  const data = props.customerAnalytics || {}
  return {
    conversion_data: data.conversion_data || {
      labels: ['potential', 'approved', 'rejected'],
      counts: [0, 0, 0],
      values: [0, 0, 0],
      colors: {
        'potential': '#2196F3',
        'approved': '#4CAF50',
        'rejected': '#F44336'
      }
    },
    summary: data.summary || {
      total_customers: 0,
      potential_count: 0,
      approved_count: 0,
      rejected_count: 0,
      approval_rate: 0,
      rejection_rate: 0,
      potential_today: 0,
      approved_today: 0,
      rejected_today: 0,
      total_value_potential: 0,
      total_value_approved: 0
    }
  }
})

const customerAnalyticsSummary = computed(() => safeCustomerAnalytics.value.summary)

// Check if data exists
const hasPaymentData = computed(() => {
  const data = paymentStatusData.value
  return data && data.data && data.data.length > 0 && data.data.some(value => value > 0)
})

const hasCommissionData = computed(() => {
  const data = commissionStatusData.value
  return data && data.data && data.data.length > 0 && data.data.some(value => value > 0)
})

// Calculate payment percentage for visualization
const getPaymentPercentage = (status, count) => {
  const total = paymentStatusData.value.data.reduce((a, b) => a + b, 0)
  return total > 0 ? (count / total) * 100 : 0
}

// Helper function to format numbers
const formatNumber = (num) => {
  if (num === null || num === undefined) return '0'
  return new Intl.NumberFormat('en-US').format(num)
}

// Safe chartData for opportunities
const safeChartData = computed(() => {
  const data = props.chartData || {}
  return {
    labels: data.labels || [],
    datasets: data.datasets?.map(dataset => ({
      label: dataset.label || 'Opportunities Created (Last 7 Days)',
      backgroundColor: dataset.backgroundColor || '#3B82F6',
      borderColor: dataset.borderColor || '#1D4ED8',
      data: dataset.data || [],
      borderWidth: 2
    })) || [{
      label: 'Opportunities Created (Last 7 Days)',
      backgroundColor: '#3B82F6',
      borderColor: '#1D4ED8',
      data: [],
      borderWidth: 2
    }]
  }
})

// Check if chart has data
const hasChartData = computed(() => {
  const data = safeChartData.value
  return data.labels && data.labels.length > 0 && 
         data.datasets && data.datasets.length > 0 && 
         data.datasets[0].data && data.datasets[0].data.length > 0 &&
         data.datasets[0].data.some(value => value > 0)
})

// Safe counts
const safeCounts = computed(() => props.counts || {})

// Recent activities
const recentActivities = computed(() => Array.isArray(props.recentActivities) ? props.recentActivities.slice(0, 5) : [])

// Format status for display
const formatStatus = (status) => {
  const statusMap = {
    'not_paid': 'Not Paid',
    'half_paid': 'Half Paid',
    'pending': 'Pending',
    'paid': 'Paid',
    'overdue': 'Overdue',
    'not_applicable': 'Not Applicable',
    'calculated': 'Calculated',
    'disputed': 'Disputed'
  }
  return statusMap[status] || status.replace('_', ' ')
}
</script>

<style scoped>
/* Responsive adjustments */
@media (max-width: 640px) {
  .grid-cols-2 {
    grid-template-columns: 1fr;
  }
  
  .text-2xl {
    font-size: 1.5rem;
  }
  
  .text-3xl {
    font-size: 1.75rem;
  }
}

@media (max-width: 768px) {
  .grid-cols-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Smooth transitions */
* {
  transition-property: all;
  transition-duration: 300ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Prevent horizontal overflow on mobile */
@media (max-width: 768px) {
  .overflow-x-hidden {
    overflow-x: hidden;
  }
}

/* Ensure proper spacing on mobile */
@media (max-width: 768px) {
  .px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  .py-4 {
    padding-top: 1rem;
    padding-bottom: 1rem;
  }
}

/* Mobile header adjustments */
@media (max-width: 768px) {
  .sticky.top-0 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
  }
  
  .text-xl {
    font-size: 1.25rem;
  }
  
  .text-sm {
    font-size: 0.875rem;
  }
}
</style>