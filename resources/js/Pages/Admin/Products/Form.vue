<template>
  <form @submit.prevent="submit" class="bg-white rounded-2xl shadow-xl border border-blue-100 p-8 space-y-8 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">
        {{ isEdit ? 'Update Product' : 'Create New Product' }}
      </h2>
      <p class="text-slate-600 mt-3">
        {{ isEdit ? 'Modify product information and categorization' : 'Add a new product to your catalog' }}
      </p>
    </div>

    <!-- Product Name -->
    <div class="bg-gradient-to-r from-blue-50 to-teal-50 rounded-2xl p-6 border border-blue-200">
      <label class="block text-sm font-semibold text-slate-800 mb-3 flex items-center">
        <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Product Name
      </label>
      <input
        v-model="form.name"
        type="text"
        placeholder="Enter product name"
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

    <!-- Category & Industry -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Category -->
      <div class="bg-gradient-to-r from-teal-50 to-blue-50 rounded-2xl p-6 border border-teal-200">
        <label class="block text-sm font-semibold text-slate-800 mb-3 flex items-center">
          <svg class="w-4 h-4 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          Category
        </label>
        <select
          v-model="form.category_id"
          class="w-full px-4 py-3 border border-teal-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-teal-500 transition-all duration-200 bg-white"
          :class="{ 'border-rose-500 focus:ring-rose-500 focus:border-rose-500': form.errors.category_id }"
        >
          <option value="">Select a category</option>
          <option v-for="category in categories" :key="category.id" :value="category.id">
            {{ category.name }}
          </option>
        </select>
        <p v-if="form.errors.category_id" class="mt-2 text-sm text-rose-600 flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ form.errors.category_id }}
        </p>
      </div>

      <!-- Industry -->
      <div class="bg-gradient-to-r from-blue-50 to-teal-50 rounded-2xl p-6 border border-blue-200">
        <label class="block text-sm font-semibold text-slate-800 mb-3 flex items-center">
          <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
          Industry
        </label>
        <select
          v-model="form.industry_id"
          class="w-full px-4 py-3 border border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white"
          :class="{ 'border-rose-500 focus:ring-rose-500 focus:border-rose-500': form.errors.industry_id }"
        >
          <option value="">Select an industry</option>
          <option v-for="industry in industries" :key="industry.id" :value="industry.id">
            {{ industry.name }}
          </option>
        </select>
        <p v-if="form.errors.industry_id" class="mt-2 text-sm text-rose-600 flex items-center">
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ form.errors.industry_id }}
        </p>
      </div>
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
        placeholder="Enter product description..."
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
        {{ form.processing ? (isEdit ? 'Updating Product...' : 'Creating Product...') : (isEdit ? 'Update Product' : 'Create Product') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  product: Object,
  categories: Array,
  industries: Array,
})

const emit = defineEmits(['saved', 'cancelled'])

const isEdit = computed(() => !!props.product)

const form = useForm({
  name: props.product?.name || '',
  description: props.product?.description || '',
  category_id: props.product?.category_id || '',
  industry_id: props.product?.industry_id || '',
})

function submit() {
  if (isEdit.value) {
    form.patch(`/admin/products/${props.product.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
      }
    })
  } else {
    form.post('/admin/products', {
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