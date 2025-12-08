<template>
  <div class="p-8">
    <form @submit.prevent="submitForm" class="space-y-6">
      <!-- City Selection -->
      <div>
        <label for="city_id" class="block text-sm font-medium text-gray-700 mb-2">
          Parent City *
        </label>
        <select
          id="city_id"
          v-model="form.city_id"
          required
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white/80 backdrop-blur-sm"
          :disabled="!cities || cities.length === 0"
        >
          <option value="">Select a city</option>
          <option 
            v-for="city in cities" 
            :key="city.id" 
            :value="city.id"
          >
            {{ city.name }}
          </option>
        </select>
        <div v-if="form.errors.city_id" class="text-red-600 text-sm mt-1">
          {{ form.errors.city_id }}
        </div>
        <div v-if="!cities || cities.length === 0" class="text-yellow-600 text-sm mt-1">
          No cities available. Please create a city first.
        </div>
        <div v-else-if="cities.length > 0" class="text-green-600 text-sm mt-1">
          {{ cities.length }} cities available
        </div>
      </div>

      <!-- Name -->
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
          Subcity Name *
        </label>
        <input
          id="name"
          v-model="form.name"
          type="text"
          required
          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white/80 backdrop-blur-sm"
          placeholder="Enter subcity name"
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
          placeholder="Enter subcity description"
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
          :disabled="form.processing || !cities || cities.length === 0"
          class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-300 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="form.processing">Saving...</span>
          <span v-else>{{ subcity ? 'Update' : 'Create' }} Subcity</span>
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  subcity: Object,
  cities: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['saved', 'cancelled'])

console.log('SubcityFormModal - cities prop:', props.cities)
console.log('SubcityFormModal - subcity prop:', props.subcity)

const form = useForm({
  city_id: props.subcity?.city_id || '',
  name: props.subcity?.name || '',
  description: props.subcity?.description || '',
})

const submitForm = () => {
  console.log('Submitting form:', form)
  
  if (props.subcity) {
    form.put(`/admin/subcities/${props.subcity.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        console.log('Update successful')
        emit('saved')
      },
      onError: (errors) => {
        console.log('Update errors:', errors)
      }
    })
  } else {
    form.post('/admin/subcities', {
      preserveScroll: true,
      onSuccess: () => {
        console.log('Create successful')
        emit('saved')
      },
      onError: (errors) => {
        console.log('Create errors:', errors)
      }
    })
  }
}
</script>