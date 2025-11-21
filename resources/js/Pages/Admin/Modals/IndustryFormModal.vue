<template>
  <div class="p-8">
    <form @submit.prevent="submit" class="space-y-8">
      <!-- Industry Name -->
      <div class="space-y-3">
        <label class="block text-sm font-semibold text-slate-800 flex items-center">
          <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
          Industry Name
        </label>
        <input
          v-model="form.name"
          type="text"
          placeholder="Enter industry name (e.g., Technology, Healthcare, Finance)"
          class="w-full px-4 py-3 border border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
          :class="{ 'border-rose-500 focus:ring-rose-500 focus:border-rose-500': form.errors.name }"
        />
        <p v-if="form.errors.name" class="text-rose-600 text-sm flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ form.errors.name }}
        </p>
      </div>

      <!-- Description -->
      <div class="space-y-3">
        <label class="block text-sm font-semibold text-slate-800 flex items-center">
          <svg class="w-4 h-4 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
          </svg>
          Description
        </label>
        <textarea
          v-model="form.description"
          rows="4"
          placeholder="Describe this industry sector and its characteristics..."
          class="w-full px-4 py-3 border border-teal-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all duration-200 bg-white/80 backdrop-blur-sm resize-none"
          :class="{ 'border-rose-500 focus:ring-rose-500 focus:border-rose-500': form.errors.description }"
        ></textarea>
        <p v-if="form.errors.description" class="text-rose-600 text-sm flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ form.errors.description }}
        </p>
      </div>

      <!-- Form Actions -->
      <div class="flex justify-end space-x-4 pt-8 border-t border-slate-200">
        <button
          type="button"
          @click="cancel"
          class="px-6 py-3 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-all duration-200 font-semibold"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="form.processing"
          class="px-6 py-3 bg-gradient-to-r from-blue-500 to-teal-500 text-white rounded-lg hover:from-blue-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          {{ form.processing ? (isEdit ? 'Updating...' : 'Creating...') : (isEdit ? 'Update Industry' : 'Create Industry') }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  industry: Object,
})

const emit = defineEmits(['saved', 'cancelled'])

const isEdit = computed(() => !!props.industry)

const form = useForm({
  name: props.industry?.name || '',
  description: props.industry?.description || '',
})

function submit() {
  if (isEdit.value) {
    form.patch(`/admin/industries/${props.industry.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
      }
    })
  } else {
    form.post('/admin/industries', {
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