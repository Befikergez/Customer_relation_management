<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-teal-50 flex">
    <Sidebar :tables="tables" />
    
    <!-- Main content area -->
    <div class="flex-1">
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <div class="space-y-2">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
                <BuildingOfficeIcon class="w-6 h-6 text-white" />
              </div>
              <div>
                <h1 class="text-3xl font-bold text-slate-800">Industries</h1>
                <p class="text-slate-600 text-lg">Manage business industry sectors</p>
              </div>
            </div>
          </div>
          <button 
            v-if="permissions.create"
            @click="visitCreate"
            class="group bg-white/80 backdrop-blur-sm border border-blue-200 text-blue-700 hover:bg-blue-50 px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center space-x-3 transform hover:-translate-y-0.5"
          >
            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
              <PlusIcon class="w-4 h-4 text-blue-600" />
            </div>
            <span>New Industry</span>
          </button>
        </div>

        <!-- Content Area -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-blue-100 overflow-hidden">
          <!-- Table Header -->
          <div class="px-6 py-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-teal-50">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-slate-800">Industry Sectors</h3>
              <div class="text-sm text-slate-600">
                {{ industries.data?.length || 0 }} industries
              </div>
            </div>
          </div>

          <!-- Industries List -->
          <div v-if="industries.data && industries.data.length > 0" class="divide-y divide-blue-100">
            <div
              v-for="industry in industries.data"
              :key="industry.id"
              class="group px-6 py-5 hover:bg-blue-50/50 transition-all duration-300"
            >
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-teal-100 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <BuildingOfficeIcon class="w-5 h-5 text-blue-600" />
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
                    v-if="permissions.edit"
                    @click="visitEdit(industry.id)"
                    class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 transform hover:scale-105 group/btn"
                    title="Edit Industry"
                  >
                    <PencilIcon class="w-4 h-4 group-hover/btn:scale-110 transition-transform" />
                  </button>
                  
                  <button 
                    v-if="permissions.delete"
                    @click="deleteIndustry(industry.id)"
                    class="p-2 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-all duration-200 transform hover:scale-105 group/btn"
                    title="Delete Industry"
                  >
                    <TrashIcon class="w-4 h-4 group-hover/btn:scale-110 transition-transform" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-16">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
              <BuildingOfficeIcon class="w-10 h-10 text-blue-600" />
            </div>
            <h3 class="text-xl font-semibold text-slate-800 mb-3">No industries yet</h3>
            <p class="text-slate-600 mb-8 max-w-md mx-auto">Start by creating your first industry sector to categorize businesses.</p>
            <button 
              v-if="permissions.create"
              @click="visitCreate"
              class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <PlusIcon class="w-5 h-5 mr-2" />
              Create First Industry
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="industries.links && industries.links.length > 3" class="mt-6 bg-white/80 backdrop-blur-sm rounded-2xl p-6 border border-blue-100 shadow-sm">
          <div class="flex justify-between items-center">
            <div class="text-sm text-slate-600">
              Showing {{ industries.meta?.from || 0 }} to {{ industries.meta?.to || 0 }} of {{ industries.meta?.total || 0 }} industries
            </div>
            <div class="flex space-x-2">
              <button 
                v-for="link in industries.links"
                :key="link.label"
                :disabled="!link.url"
                @click="visitPage(link.url)"
                class="px-4 py-2 text-sm font-medium rounded-lg border transition-all duration-200"
                :class="{
                  'bg-gradient-to-r from-blue-500 to-teal-500 text-white border-transparent shadow-md': link.active,
                  'text-slate-700 border-slate-300 hover:bg-slate-50 hover:border-blue-300': !link.active && link.url,
                  'text-slate-400 border-slate-200 cursor-not-allowed': !link.url
                }"
                v-html="link.label"
              ></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  BuildingOfficeIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  industries: {
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

// Navigation methods
const visitCreate = () => {
  router.get('/admin/industries/create')
}

const visitEdit = (id) => {
  router.get(`/admin/industries/${id}/edit`)
}

const visitPage = (url) => {
  if (url) {
    router.get(url)
  }
}

const deleteIndustry = (id) => {
  if (confirm('Are you sure you want to delete this industry?')) {
    router.delete(`/admin/industries/${id}`)
  }
}
</script>