<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-teal-50 flex">
    <Sidebar :tables="tables" />
    
    <!-- Main content area -->
    <div class="flex-1">
      <div class="p-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-teal-500 rounded-2xl flex items-center justify-center shadow-lg">
              <BuildingOfficeIcon class="w-6 h-6 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-slate-800">Edit Industry</h1>
              <p class="text-slate-600">Update industry sector information</p>
            </div>
          </div>
          <button 
            @click="visitIndex"
            class="bg-white/80 backdrop-blur-sm border border-slate-300 text-slate-700 hover:bg-slate-50 px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl flex items-center space-x-3 transform hover:-translate-y-0.5"
          >
            <ArrowLeftIcon class="w-4 h-4" />
            <span>Back to Industries</span>
          </button>
        </div>

        <!-- Form -->
        <IndustryForm
          :industry="industry"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import IndustryForm from './Form.vue'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import { router } from '@inertiajs/vue3'
import { ArrowLeftIcon, BuildingOfficeIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  industry: {
    type: Object,
    required: true
  },
  tables: {
    type: Array,
    default: () => []
  },
  permissions: {
    type: Object,
    default: () => ({})
  }
})

const visitIndex = () => {
  router.get('/admin/industries')
}
</script>