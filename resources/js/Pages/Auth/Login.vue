<script setup>
import { Head, useForm } from '@inertiajs/vue3'

defineProps({
  canResetPassword: Boolean,
  status: String
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  // Post directly to /login (no Ziggy route())
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Login - OmishtuJoy CRM" />

  <div class="min-h-screen flex flex-col justify-center items-center bg-blue-50">
    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-md">
      <div class="flex justify-center mb-6">
        <img src="/omislogo.jpg" alt="OmishtuJoy Logo" class="h-24 object-contain" />
      </div>

      <h2 class="text-2xl font-semibold text-center text-blue-800 mb-6">Welcome Back</h2>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <input
            id="email"
            type="email"
            v-model="form.email"
            required
            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <div v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</div>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <input
            id="password"
            type="password"
            v-model="form.password"
            required
            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
          />
          <div v-if="form.errors.password" class="text-red-600 text-sm mt-1">{{ form.errors.password }}</div>
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center">
            <input type="checkbox" v-model="form.remember" class="rounded border-gray-300" />
            <span class="ml-2 text-sm text-gray-600">Remember me</span>
          </label>

          <a
            v-if="canResetPassword"
            href="/forgot-password"
            class="text-sm text-blue-600 hover:text-blue-800"
          >
            Forgot your password?
          </a>
        </div>

        <button
          type="submit"
          class="w-full py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
          :disabled="form.processing"
        >
          Log in
        </button>
      </form>

      <p v-if="status" class="text-green-600 text-center mt-4">{{ status }}</p>
    </div>
  </div>
</template>
