<template>
  <form @submit.prevent="submit" class="bg-white/80 backdrop-blur-sm p-8 rounded-2xl shadow-lg border border-blue-100/50 space-y-8 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="text-center mb-8">
      <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-teal-600 bg-clip-text text-transparent">
        {{ isEdit ? 'Update Role' : 'Create New Role' }}
      </h2>
      <p class="text-slate-600 mt-2">
        {{ isEdit ? 'Modify role information and permissions' : 'Define a new role with specific permissions' }}
      </p>
    </div>

    <!-- Name -->
    <div class="bg-gradient-to-r from-blue-50 to-teal-50 rounded-2xl p-6 border border-blue-200/50">
      <label class="block text-sm font-semibold text-slate-800 mb-3 flex items-center">
        <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        Role Name
      </label>
      <input
        v-model="form.name"
        type="text"
        placeholder="Enter role name (e.g., Administrator, Editor, Viewer)"
        class="w-full px-4 py-3 border border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 bg-white/80 backdrop-blur-sm"
        :class="{ 'border-rose-500 focus:ring-rose-500 focus:border-rose-500': form.errors.name }"
      />
      <p v-if="form.errors.name" class="mt-2 text-sm text-rose-600 flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ form.errors.name }}
      </p>
    </div>

    <!-- Permissions -->
    <div class="bg-gradient-to-r from-teal-50 to-blue-50 rounded-2xl p-6 border border-teal-200/50">
      <label class="block text-sm font-semibold text-slate-800 mb-4 flex items-center">
        <svg class="w-4 h-4 text-teal-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        Role Permissions
        <span class="ml-2 px-2 py-1 bg-white/80 text-slate-600 rounded-full text-xs border border-teal-200">
          {{ permissions.length }} available
        </span>
      </label>
      
      <!-- Select All -->
      <div class="mb-4 p-3 bg-white/80 rounded-xl border border-teal-200">
        <label class="flex items-center space-x-3 cursor-pointer">
          <input 
            type="checkbox" 
            :checked="allSelected"
            @change="toggleAllPermissions"
            class="w-4 h-4 text-teal-600 bg-white border-teal-300 rounded focus:ring-teal-500"
          />
          <span class="text-sm font-medium text-slate-800">Select All Permissions</span>
          <span class="text-xs text-slate-500">({{ form.permissions.length }}/{{ permissions.length }} selected)</span>
        </label>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto p-2">
        <label
          v-for="permission in permissions"
          :key="permission.id"
          class="flex items-center gap-4 p-4 rounded-xl border transition-all duration-200 cursor-pointer transform hover:scale-105"
          :class="form.permissions.includes(permission.name) 
            ? 'bg-teal-100 border-teal-300 shadow-md' 
            : 'bg-white border-teal-200 hover:bg-teal-50'"
        >
          <input 
            type="checkbox" 
            :value="permission.name" 
            v-model="form.permissions" 
            class="w-4 h-4 text-teal-600 bg-white border-teal-300 rounded focus:ring-teal-500"
          />
          <div class="flex-1">
            <span class="text-sm font-medium text-slate-800">{{ permission.name }}</span>
          </div>
          <div class="w-2 h-2 bg-teal-400 rounded-full" v-if="form.permissions.includes(permission.name)"></div>
        </label>
      </div>
      <p v-if="form.errors.permissions" class="mt-2 text-sm text-rose-600 flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ form.errors.permissions }}
      </p>
    </div>

    <!-- Selected Permissions Summary -->
    <div v-if="form.permissions.length > 0" class="bg-gradient-to-r from-blue-50 to-rose-50 rounded-2xl p-6 border border-rose-200/50">
      <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center">
        <svg class="w-4 h-4 text-rose-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Selected Permissions ({{ form.permissions.length }})
      </h3>
      <div class="flex flex-wrap gap-3">
        <span
          v-for="permission in form.permissions"
          :key="permission"
          class="inline-flex items-center px-3 py-2 rounded-xl text-sm font-medium bg-white text-slate-800 border border-rose-200 shadow-sm"
        >
          {{ permission }}
          <button
            type="button"
            @click="removePermission(permission)"
            class="ml-2 text-rose-500 hover:text-rose-700 transition-colors"
          >
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </span>
      </div>
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
        class="px-8 py-3 bg-gradient-to-r from-blue-600 to-teal-600 text-white rounded-xl hover:from-blue-700 hover:to-teal-700 transition-all duration-300 shadow-lg hover:shadow-xl flex items-center disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-0.5"
      >
        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ form.processing ? (isEdit ? 'Updating Role...' : 'Creating Role...') : (isEdit ? 'Update Role' : 'Create Role') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  role: Object,
  permissions: Array,
  rolePermissions: Array,
})

const emit = defineEmits(['saved', 'cancelled'])

const isEdit = computed(() => !!props.role)

const form = useForm({
  name: props.role?.name || '',
  permissions: props.rolePermissions?.map(permission => permission.name) || [],
})

const allSelected = computed(() => {
  return form.permissions.length === props.permissions.length && props.permissions.length > 0
})

function toggleAllPermissions() {
  if (allSelected.value) {
    form.permissions = []
  } else {
    form.permissions = props.permissions.map(p => p.name)
  }
}

function removePermission(permission) {
  form.permissions = form.permissions.filter(p => p !== permission)
}

function submit() {
  if (isEdit.value) {
    form.put(`/admin/roles/${props.role.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
      }
    })
  } else {
    form.post('/admin/roles', {
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