<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Register - OmishtuJoy CRM" />

  <div class="min-h-screen flex flex-col justify-center items-center bg-blue-50">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-md">
      <div class="flex justify-center mb-6">
        <img src="/omislogo.jpg" alt="OmishtuJoy Logo" class="h-24 object-contain" />
      </div>

      <h2 class="text-2xl font-semibold text-center text-blue-800 mb-6">Create an Account</h2>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Name</label>
          <input v-model="form.name" type="text" required class="input" />
          <div v-if="form.errors.name" class="text-red-600 text-sm mt-1">{{ form.errors.name }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input v-model="form.email" type="email" required class="input" />
          <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Password</label>
          <input v-model="form.password" type="password" required class="input" />
          <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">{{ form.errors.password }}</div>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
          <input v-model="form.password_confirmation" type="password" required class="input" />
        </div>

        <button type="submit" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
          Register
        </button>
      </form>

      <p class="text-center mt-4">
        Already have an account?
        <a href="/login" class="text-blue-600 hover:text-blue-800">Login</a>
      </p>
    </div>
  </div>
</template>
