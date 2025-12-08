<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-teal-50 flex">
    <Sidebar :tables="tables" />
    
    <div class="flex-1">
      <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white/80 backdrop-blur-lg border-b border-blue-100/50 px-6 py-6 shadow-sm">
          <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center">
              <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">Add New City</h1>
                <p class="text-slate-600 mt-2">Create a new city for your location system</p>
              </div>
              <button 
                @click="$inertia.visit('/admin/cities')"
                class="bg-slate-600 hover:bg-slate-700 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center space-x-2 shadow-lg hover:shadow-xl"
              >
                <ArrowLeftIcon class="w-5 h-5" />
                <span>Back to Cities</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-6">
          <div class="max-w-2xl mx-auto">
            <!-- Success/Error Messages -->
            <div v-if="$page.props.flash && $page.props.flash.success" class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 px-6 py-4 rounded-xl mb-6 shadow-sm">
              <div class="flex items-center">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                  <CheckCircleIcon class="w-4 h-4 text-green-600" />
                </div>
                {{ $page.props.flash.success }}
              </div>
            </div>

            <!-- Create Form -->
            <div class="bg-white rounded-2xl shadow-lg border border-blue-100 p-8">
              <form @submit.prevent="submit">
                <div class="space-y-6">
                  <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">City Name *</label>
                    <input
                      type="text"
                      v-model="form.name"
                      required
                      class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
                      placeholder="Enter city name"
                      :class="{ 'border-red-300': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                  </div>

                  <div class="flex justify-end space-x-4 pt-6 border-t border-slate-200">
                    <button 
                      type="button"
                      @click="$inertia.visit('/admin/cities')"
                      class="px-6 py-3 text-slate-600 hover:text-slate-800 font-medium text-sm transition-colors border border-slate-300 rounded-lg hover:bg-slate-50"
                    >
                      Cancel
                    </button>
                    <button 
                      type="submit"
                      :disabled="form.processing"
                      class="bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-700 hover:to-teal-700 disabled:from-blue-400 disabled:to-teal-400 text-white px-8 py-3 rounded-lg font-semibold transition-all duration-200 text-sm shadow hover:shadow-lg disabled:cursor-not-allowed flex items-center space-x-2"
                    >
                      <PlusIcon class="w-5 h-5" />
                      <span v-if="form.processing">Creating...</span>
                      <span v-else>Create City</span>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import Sidebar from '@/Pages/Admin/Sidebar.vue'
import {
  PlusIcon,
  ArrowLeftIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
  tables: {
    type: Array,
    default: () => []
  },
  flash: {
    type: Object,
    default: () => ({})
  }
})

const form = useForm({
  name: ''
})

const submit = () => {
  form.post('/admin/cities', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    }
  })
}
</script>