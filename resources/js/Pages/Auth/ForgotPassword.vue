<script setup>
import { Head, useForm } from '@inertiajs/vue3'

const form = useForm({
  email: ''
})

const submit = () => {
  form.post('/forgot-password', {
    onFinish: () => form.reset('email'),
  })
}
</script>

<template>
  <Head title="Forgot Password - OmishtuJoy CRM" />

  <div class="min-h-screen flex flex-col justify-center items-center bg-blue-50">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-md">
      <div class="flex justify-center mb-6">
        <img src="/omislogo.jpg" alt="OmishtuJoy Logo" class="h-24 object-contain" />
      </div>

      <h2 class="text-2xl font-semibold text-center text-blue-800 mb-6">Reset Your Password</h2>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input v-model="form.email" type="email" required class="input" />
          <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
        </div>

        <button type="submit" class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
          Send Password Reset Link
        </button>
      </form>

      <p class="text-center mt-4">
        Remembered your password?
        <a href="/login" class="text-blue-600 hover:text-blue-800">Login</a>
      </p>
    </div>
  </div>
</template>
