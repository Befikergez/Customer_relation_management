<template>
  <div class="max-w-4xl mx-auto">
    <form @submit.prevent="submit" class="bg-white rounded-3xl shadow-sm border border-gray-200/50 overflow-hidden backdrop-blur-sm" enctype="multipart/form-data">
      <!-- Form Header -->
      <div class="bg-gradient-to-r from-blue-50 via-white to-teal-50 px-8 py-6 border-b border-gray-200/50">
        <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-800 via-blue-700 to-teal-700 bg-clip-text text-transparent">
          {{ isEdit ? 'Edit User' : 'Create New User' }}
        </h2>
        <p class="text-gray-600 mt-1">
          {{ isEdit ? 'Update user information and permissions' : 'Add a new user to the system' }}
        </p>
      </div>

      <!-- Form Content -->
      <div class="p-8 space-y-8">
        <!-- Profile Image -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-teal-500 rounded-full mr-3"></div>
            Profile Image
          </h3>
          
          <div class="flex items-center space-x-6">
            <!-- Image Preview -->
            <div class="relative">
              <div v-if="imagePreview" class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-teal-200">
                <img 
                  :src="imagePreview" 
                  alt="Profile preview"
                  class="w-full h-full object-cover"
                />
              </div>
              <div v-else-if="isEdit && user.profile_image_url" class="w-20 h-20 rounded-2xl overflow-hidden border-2 border-teal-200">
                <img 
                  :src="user.profile_image_url" 
                  :alt="user.name"
                  class="w-full h-full object-cover"
                />
              </div>
              <div v-else class="w-20 h-20 bg-gradient-to-br from-blue-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-md border-2 border-teal-200">
                <span class="text-white font-bold text-xl">{{ form.name.charAt(0).toUpperCase() || 'U' }}</span>
              </div>
            </div>
            
            <!-- Image Upload -->
            <div class="flex-1">
              <label class="block text-sm font-semibold text-gray-700 mb-3">Upload Profile Picture</label>
              <input
                type="file"
                @change="handleImageUpload"
                accept="image/*"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gradient-to-r file:from-blue-50 file:to-gray-50 file:text-gray-700 hover:file:from-blue-100 hover:file:to-gray-100"
              />
              <p class="text-gray-600 text-sm mt-2">Recommended: Square image, 500x500 pixels, max 2MB</p>
              
              <!-- Show current image info for edit -->
              <div v-if="isEdit && user.profile_image" class="mt-2">
                <p class="text-xs text-gray-500">Current image: {{ user.profile_image }}</p>
                <button 
                  type="button" 
                  @click="removeImage"
                  class="text-xs text-rose-600 hover:text-rose-700 mt-1 flex items-center"
                >
                  <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  Remove current image
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Personal Information -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-teal-500 rounded-full mr-3"></div>
            Personal Information
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-3">Full Name</label>
              <input
                v-model="form.name"
                type="text"
                placeholder="Enter user's full name"
                class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                :class="{ 'border-red-400 focus:ring-red-500 focus:border-red-500': form.errors.name }"
              />
              <p v-if="form.errors.name" class="mt-2 text-sm text-red-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ form.errors.name }}
              </p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-3">Email Address</label>
              <input
                v-model="form.email"
                type="email"
                placeholder="Enter email address"
                class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                :class="{ 'border-red-400 focus:ring-red-500 focus:border-red-500': form.errors.email }"
              />
              <p v-if="form.errors.email" class="mt-2 text-sm text-red-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ form.errors.email }}
              </p>
            </div>
          </div>
        </div>

        <!-- Password Section -->
        <div v-if="!isEdit" class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <div class="w-2 h-2 bg-gradient-to-r from-green-500 to-teal-500 rounded-full mr-3"></div>
            Security
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-3">Password</label>
              <input
                v-model="form.password"
                type="password"
                placeholder="Enter secure password"
                class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
                :class="{ 'border-red-400 focus:ring-red-500 focus:border-red-500': form.errors.password }"
              />
              <p v-if="form.errors.password" class="mt-2 text-sm text-red-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ form.errors.password }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-3">Confirm Password</label>
              <input
                v-model="form.password_confirmation"
                type="password"
                placeholder="Confirm password"
                class="w-full px-4 py-3.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-300 bg-white/50 backdrop-blur-sm"
              />
            </div>
          </div>
        </div>

        <!-- Roles Section -->
        <div class="space-y-6">
          <h3 class="text-lg font-semibold text-gray-800 flex items-center">
            <div class="w-2 h-2 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full mr-3"></div>
            Roles & Permissions
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <label
              v-for="role in roles"
              :key="role.id"
              class="relative flex items-start gap-4 p-4 rounded-xl border-2 border-gray-200 bg-white hover:border-teal-300 hover:bg-teal-50/50 transition-all duration-300 cursor-pointer group"
              :class="{ 'border-blue-500 bg-blue-50/50 shadow-sm': form.roles.includes(role.name) }"
            >
              <input 
                type="checkbox" 
                :value="role.name" 
                v-model="form.roles" 
                class="w-5 h-5 text-blue-600 bg-white border-gray-300 rounded-lg focus:ring-blue-500 focus:ring-2 transition-all duration-300"
              />
              <div class="flex-1 min-w-0">
                <span class="text-gray-800 font-semibold group-hover:text-gray-700 transition-colors duration-300">
                  {{ role.name }}
                </span>
                <p class="text-gray-600 text-sm mt-1">User can access {{ role.name }} features</p>
              </div>
            </label>
          </div>
          <p v-if="form.errors.roles" class="text-sm text-red-600 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ form.errors.roles }}
          </p>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="bg-gradient-to-r from-gray-50 via-blue-50/50 to-teal-50/50 backdrop-blur-sm px-8 py-6 border-t border-gray-200/50">
        <div class="flex justify-end space-x-4">
          <button
            type="button"
            @click="cancel"
            class="px-8 py-3.5 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-white hover:border-gray-400 transition-all duration-300 font-semibold"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="group relative bg-gradient-to-r from-blue-600 to-blue-700 text-white px-8 py-3.5 rounded-xl hover:shadow-lg transition-all duration-300 shadow-md flex items-center font-semibold disabled:opacity-50 disabled:cursor-not-allowed overflow-hidden border border-blue-500/20"
          >
            <div class="absolute inset-0 bg-gradient-to-r from-blue-700 to-blue-800 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white relative z-10" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span class="relative z-10">
              {{ form.processing ? (isEdit ? 'Updating...' : 'Creating...') : (isEdit ? 'Update User' : 'Create User') }}
            </span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  user: Object,
  roles: Array,
  userRoles: Array,
})

const emit = defineEmits(['saved', 'cancelled'])

const isEdit = computed(() => !!props.user)
const imagePreview = ref(null)

// Initialize form with proper data
const form = useForm({
  name: props.user?.name || '',
  email: props.user?.email || '',
  password: '',
  password_confirmation: '',
  roles: props.userRoles?.map(role => role.name) || [],
  profile_image: null,
  _method: isEdit.value ? 'PUT' : 'POST',
})

// Set initial image preview for edit
if (isEdit.value && props.user?.profile_image_url) {
  imagePreview.value = props.user.profile_image_url
}

function handleImageUpload(event) {
  const file = event.target.files[0]
  if (file) {
    form.profile_image = file
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

function removeImage() {
  form.profile_image = null
  imagePreview.value = null
  // You might want to add a flag to remove the existing image
  form.remove_existing_image = true
}

function submit() {
  const submitData = form.transform((data) => ({
    ...data,
    _method: isEdit.value ? 'PUT' : 'POST'
  }))

  if (isEdit.value) {
    submitData.post(`/admin/users/${props.user.id}`, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        emit('saved')
      }
    })
  } else {
    submitData.post('/admin/users', {
      forceFormData: true,
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