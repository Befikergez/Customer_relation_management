<template>
  <div class="p-8">
    <form @submit.prevent="submit" class="space-y-8">
      <!-- Category Name -->
      <div class="bg-gradient-to-r from-blue-50 to-teal-50 rounded-2xl p-6 border border-blue-200">
        <label class="block text-sm font-semibold text-slate-800 mb-3 flex items-center">
          <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
          </svg>
          Category Name
        </label>
        <input
          v-model="form.name"
          type="text"
          placeholder="Enter category name (e.g., Hardware, Software, Services)"
          class="w-full px-4 py-3 border border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
          :class="{ 'border-rose-500 focus:ring-rose-500 focus:border-rose-500': form.errors.name }"
        />
        <p v-if="form.errors.name" class="mt-2 text-sm text-rose-600 flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ form.errors.name }}
        </p>
      </div>

      <!-- Description -->
      <div class="bg-gradient-to-r from-teal-50 to-blue-50 rounded-2xl p-6 border border-teal-200">
        <label class="block text-sm font-semibold text-slate-800 mb-3 flex items-center">
          <svg class="w-4 h-4 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
          </svg>
          Description
        </label>
        <textarea
          v-model="form.description"
          rows="4"
          placeholder="Describe what products belong to this category..."
          class="w-full px-4 py-3 border border-teal-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all duration-200 bg-white resize-none"
          :class="{ 'border-rose-500 focus:ring-rose-500 focus:border-rose-500': form.errors.description }"
        ></textarea>
        <p v-if="form.errors.description" class="mt-2 text-sm text-rose-600 flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ form.errors.description }}
        </p>
      </div>

      <!-- Status (Edit Mode Only) -->
      <div v-if="isEdit" class="bg-gradient-to-r from-rose-50 to-pink-50 rounded-2xl p-6 border border-rose-200">
        <label class="block text-sm font-semibold text-slate-800 mb-3 flex items-center">
          <svg class="w-4 h-4 text-rose-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
          Category Status
        </label>
        <div class="flex items-center space-x-4">
          <label class="flex items-center space-x-3 cursor-pointer">
            <input 
              type="radio" 
              v-model="form.is_active" 
              :value="true"
              class="w-4 h-4 text-blue-600 bg-white border-blue-300 focus:ring-blue-500"
            />
            <span class="text-slate-700 font-medium">Active</span>
            <span class="text-slate-500 text-sm">(Category is visible and can be used)</span>
          </label>
          <label class="flex items-center space-x-3 cursor-pointer">
            <input 
              type="radio" 
              v-model="form.is_active" 
              :value="false"
              class="w-4 h-4 text-rose-600 bg-white border-rose-300 focus:ring-rose-500"
            />
            <span class="text-slate-700 font-medium">Inactive</span>
            <span class="text-slate-500 text-sm">(Category is hidden from selection)</span>
          </label>
        </div>
        <p v-if="form.errors.is_active" class="mt-2 text-sm text-rose-600 flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ form.errors.is_active }}
        </p>
      </div>

      <!-- Form Actions -->
      <div class="flex justify-end space-x-4 pt-8 border-t border-slate-200">
        <button
          type="button"
          @click="cancel"
          class="px-8 py-3 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-all duration-200 font-semibold transform hover:-translate-y-0.5"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="form.processing"
          class="px-8 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-xl hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-0.5"
        >
          <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          {{ form.processing ? (isEdit ? 'Updating Category...' : 'Creating Category...') : (isEdit ? 'Update Category' : 'Create Category') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  category: Object,
})

const emit = defineEmits(['saved', 'cancelled'])

const isEdit = computed(() => !!props.category)

const form = useForm({
  name: props.category?.name || '',
  description: props.category?.description || '',
  is_active: props.category?.is_active ?? true,
})

function submit() {
  if (isEdit.value) {
    form.patch(`/admin/product-categories/${props.category.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
      }
    })
  } else {
    form.post('/admin/product-categories', {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
      }
    })
  }
}

function cancel() {
  emit('cancelled')
}
</script>