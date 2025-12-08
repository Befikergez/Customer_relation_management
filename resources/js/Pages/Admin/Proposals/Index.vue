<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-teal-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="p-6">
        <div class="flex justify-between items-center mb-8">
          <div class="space-y-2">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
                <DocumentTextIcon class="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 class="text-3xl font-bold text-slate-800">Proposals</h1>
                <p class="text-slate-600 text-lg">Manage business proposals</p>
              </div>
            </div>
          </div>
          <button 
            v-if="permissions.create"
            @click="visitCreate"
            class="group bg-gradient-to-r from-blue-500 to-teal-500 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center space-x-3 transform hover:-translate-y-0.5 hover:from-blue-600 hover:to-teal-600"
          >
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
              <PlusIcon class="w-4 h-4 text-white" />
            </div>
            <span>New Proposal</span>
          </button>
        </div>

        <!-- Results Count -->
        <div v-if="proposals.data && proposals.data.length > 0" class="mb-4">
          <p class="text-sm text-slate-600">
            Showing {{ proposals.meta?.from || 0 }} to {{ proposals.meta?.to || 0 }} of {{ proposals.meta?.total || 0 }} proposals
          </p>
        </div>

        <div v-if="proposals.data && proposals.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
          <div
            v-for="proposal in proposals.data"
            :key="proposal.id"
            class="bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden hover:shadow-xl transition-all duration-300 group hover:border-blue-200"
          >
            <div class="h-1.5 bg-slate-100 relative overflow-hidden">
              <div 
                class="h-full transition-all duration-1000 ease-out"
                :class="{
                  'w-full bg-gradient-to-r from-teal-500 to-blue-500': proposal.status === 'signed',
                  'w-1/2 bg-gradient-to-r from-blue-500 to-teal-500': proposal.status === 'unsigned' && !proposal.is_rejected, 
                  'w-full bg-gradient-to-r from-rose-500 to-red-500': proposal.is_rejected
                }"
              ></div>
            </div>

            <div class="p-6">
              <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-teal-500 rounded-xl flex items-center justify-center shadow-md">
                  <DocumentTextIcon class="w-6 h-6 text-white" />
                </div>
                <div class="flex flex-col items-end">
                  <span :class="`px-3 py-1 rounded-full text-xs font-semibold ${
                    proposal.status === 'signed' 
                      ? 'bg-teal-100 text-teal-800 border border-teal-200' 
                      : proposal.is_rejected
                      ? 'bg-rose-100 text-rose-800 border border-rose-200'
                      : 'bg-blue-100 text-blue-800 border border-blue-200'
                  }`">
                    {{ proposal.is_rejected ? 'Rejected' : proposal.status }}
                  </span>
                  <span class="text-lg font-bold text-slate-800 mt-1">${{ proposal.price }}</span>
                </div>
              </div>

              <h3 class="text-lg font-bold text-slate-800 mb-3 line-clamp-2 group-hover:text-blue-700 transition-colors">
                {{ proposal.title }}
              </h3>
              <p class="text-slate-600 text-sm mb-4 line-clamp-3 leading-relaxed">
                {{ proposal.description }}
              </p>

              <div class="space-y-3 mb-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center border border-slate-300">
                    <UserIcon class="w-4 h-4 text-slate-600" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-slate-500 font-medium">Created By</p>
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ proposal.created_by?.name || 'Unknown' }}</p>
                  </div>
                </div>
                <div class="flex items-center space-x-3">
                  <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center border border-slate-300">
                    <BuildingOfficeIcon class="w-4 h-4 text-slate-600" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs text-slate-500 font-medium">Potential Customer</p>
                    <p class="text-sm font-semibold text-slate-800 truncate">{{ proposal.potential_customer?.potential_customer_name || 'Unknown' }}</p>
                  </div>
                </div>
              </div>

              <div v-if="proposal.file" class="flex items-center space-x-3 mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <DocumentIcon class="w-5 h-5 text-blue-600" />
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-blue-800 truncate">{{ getFileName(proposal.file) }}</p>
                </div>
                <button
                  @click="downloadFile(proposal.id)"
                  class="px-3 py-1 bg-blue-500 text-white text-xs rounded-lg hover:bg-blue-600 transition-colors font-medium"
                  title="Download File"
                >
                  Download
                </button>
              </div>

              <div class="flex flex-col gap-2 pt-4 border-t border-slate-200">
                <div class="flex gap-2">
                  <button 
                    @click.prevent="viewProposal(proposal.id)"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs font-medium transition-all duration-200 flex items-center space-x-1"
                  >
                    <EyeIcon class="w-3 h-3" />
                    <span>View</span>
                  </button>
                  
                  <button 
                    v-if="permissions.edit && !proposal.is_rejected"
                    @click="visitEdit(proposal.id)"
                    class="flex-1 flex items-center justify-center space-x-2 px-3 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all duration-200 text-sm font-semibold"
                  >
                    <PencilIcon class="w-4 h-4" />
                    <span>Edit</span>
                  </button>
                </div>

                <button 
                  v-if="permissions.delete"
                  @click="deleteProposal(proposal.id)"
                  class="w-full flex items-center justify-center space-x-2 px-3 py-2.5 bg-white border border-rose-300 text-rose-700 rounded-lg hover:bg-rose-50 hover:border-rose-400 transition-all duration-200 text-sm font-semibold"
                >
                  <TrashIcon class="w-4 h-4" />
                  <span>Delete Proposal</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-16">
          <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <DocumentTextIcon class="w-10 h-10 text-white" />
          </div>
          <h3 class="text-xl font-bold text-slate-800 mb-3">No proposals yet</h3>
          <p class="text-slate-600 mb-8 max-w-md mx-auto">Start by creating your first proposal for potential customers.</p>
          <button 
            v-if="permissions.create"
            @click="visitCreate"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-semibold"
          >
            <PlusIcon class="w-5 h-5 mr-2" />
            Create First Proposal
          </button>
        </div>

        <!-- Enhanced Pagination -->
        <div v-if="proposals.links && proposals.links.length > 3" class="mt-8 bg-white rounded-2xl p-6 border border-slate-200 shadow-sm">
          <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-slate-600">
              Page {{ proposals.meta?.current_page || 1 }} of {{ proposals.meta?.last_page || 1 }} • 
              Showing {{ proposals.meta?.from || 0 }} to {{ proposals.meta?.to || 0 }} of {{ proposals.meta?.total || 0 }} proposals
            </div>
            <div class="flex flex-wrap gap-2 justify-center">
              <!-- First Page -->
              <button 
                v-if="proposals.meta?.current_page > 1"
                @click="visitPage(proposals.links[0].url)"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-slate-400 transition-all duration-200 flex items-center space-x-2"
              >
                <ChevronDoubleLeftIcon class="w-4 h-4" />
                <span>First</span>
              </button>

              <!-- Previous Page -->
              <button 
                v-if="proposals.links[0].url"
                @click="visitPage(proposals.links[0].url)"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-slate-400 transition-all duration-200 flex items-center space-x-2"
              >
                <ChevronLeftIcon class="w-4 h-4" />
                <span>Previous</span>
              </button>

              <!-- Page Numbers -->
              <button 
                v-for="link in proposals.links.slice(1, -1)"
                :key="link.label"
                :disabled="!link.url"
                @click="visitPage(link.url)"
                class="px-4 py-2 text-sm font-medium rounded-lg border transition-all duration-200 min-w-[3rem]"
                :class="{
                  'bg-gradient-to-r from-blue-500 to-teal-500 text-white border-transparent shadow-md': link.active,
                  'text-slate-700 border-slate-300 hover:bg-slate-50 hover:border-blue-300': !link.active && link.url,
                  'text-slate-400 border-slate-200 cursor-not-allowed': !link.url
                }"
                v-html="link.label"
              ></button>

              <!-- Next Page -->
              <button 
                v-if="proposals.links[proposals.links.length - 1].url"
                @click="visitPage(proposals.links[proposals.links.length - 1].url)"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-slate-400 transition-all duration-200 flex items-center space-x-2"
              >
                <span>Next</span>
                <ChevronRightIcon class="w-4 h-4" />
              </button>

              <!-- Last Page -->
              <button 
                v-if="proposals.meta?.current_page < proposals.meta?.last_page"
                @click="visitPage(proposals.links[proposals.links.length - 1].url)"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:border-slate-400 transition-all duration-200 flex items-center space-x-2"
              >
                <span>Last</span>
                <ChevronDoubleRightIcon class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  DocumentTextIcon,
  EyeIcon,
  UserIcon,
  BuildingOfficeIcon,
  DocumentIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  ChevronDoubleLeftIcon,
  ChevronDoubleRightIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  proposals: {
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
  }
})

const loading = ref(false)

const getFileName = (filePath) => {
  return filePath ? filePath.split('/').pop() : ''
}

const visitCreate = () => {
  router.get('/admin/proposals/create')
}

const viewProposal = (proposalId) => {
  window.location.href = `/admin/proposals/${proposalId}`
}

const visitEdit = (id) => {
  router.get(`/admin/proposals/${id}/edit`)
}

const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

const downloadFile = (id) => {
  window.location.href = `/admin/proposals/${id}/download`
}

const deleteProposal = (id) => {
  if (confirm('Are you sure you want to delete this proposal?')) {
    loading.value = true
    router.delete(`/admin/proposals/${id}`, {
      preserveScroll: true,
      onSuccess: () => {
        router.reload({ only: ['proposals'] })
      },
      onFinish: () => {
        loading.value = false
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

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>