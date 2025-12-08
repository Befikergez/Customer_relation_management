<template>
  <div class="p-8">
    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- Name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
          City Name *
        </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white/80 backdrop-blur-sm"
          placeholder="Enter city name"
        />
        <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">
          {{ form.errors.name }}
        </div>
      </div>

      <!-- Description -->
      <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
          Description
        </label>
        <textarea
          id="description"
          v-model="form.description"
          rows="3"
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white/80 backdrop-blur-sm"
          placeholder="Enter city description"
        ></textarea>
        <div v-if="form.errors.description" class="text-red-600 text-sm mt-1">
          {{ form.errors.description }}
        </div>
      </div>

      <!-- Form Actions -->
      <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
        <button
          type="button"
          @click="$emit('cancelled')"
          class="px-6 py-3 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all duration-300 font-medium"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="form.processing"
          class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="form.processing">Saving...</span>
          <span v-else>{{ city ? 'Update' : 'Create' }} City</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  city: Object
})

const emit = defineEmits(['saved', 'cancelled'])

const form = useForm({
  name: props.city?.name || '',
  description: props.city?.description || '',
})

const submitForm = () => {
  if (props.city) {
    form.put(`/admin/cities/${props.city.id}?from_settings=true`, {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
      }
    })
  } else {
    form.post('/admin/cities?from_settings=true', {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
      }
    })
  }
}
</script>