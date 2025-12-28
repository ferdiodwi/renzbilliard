<template>
  <div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Profile</h1>
    </div>

    <!-- Profile Card -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
      <form @submit.prevent="handleUpdateProfile" class="space-y-6">
        <!-- Avatar -->
        <div class="flex items-center gap-6">
          <div class="h-20 w-20 rounded-full bg-gradient-to-br from-brand-500 to-brand-600 flex items-center justify-center">
            <span class="text-3xl font-bold text-white">{{ userInitials }}</span>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ userName }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ userRole }}</p>
          </div>
        </div>

        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Full Name
          </label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500"
          />
        </div>

        <!-- Username -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Username
          </label>
          <input
            v-model="form.username"
            type="text"
            required
            class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500"
          />
        </div>

        <!-- Change Password Section -->
        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Change Password</h3>
          
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                New Password (leave blank to keep current)
              </label>
              <input
                v-model="form.password"
                type="password"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                placeholder="Enter new password"
              />
            </div>

            <div v-if="form.password">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Confirm Password
              </label>
              <input
                v-model="form.password_confirmation"
                type="password"
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-500"
                placeholder="Confirm new password"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-4">
          <button
            type="submit"
            :disabled="loading"
            class="px-6 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg transition disabled:opacity-50"
          >
            {{ loading ? 'Saving...' : 'Save Changes' }}
          </button>
          <button
            type="button"
            @click="resetForm"
            class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition"
          >
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import axios from 'axios'

const authStore = useAuthStore()
const notify = useNotificationStore()
const loading = ref(false)

const user = computed(() => authStore.user)
const userName = computed(() => user.value?.name || '')
const userRole = computed(() => {
  const role = user.value?.role || 'user'
  return role.charAt(0).toUpperCase() + role.slice(1)
})
const userInitials = computed(() => {
  const name = userName.value
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
})

const form = ref({
  name: '',
  username: '',
  password: '',
  password_confirmation: '',
})

const loadProfile = () => {
  form.value.name = user.value?.name || ''
  form.value.username = user.value?.username || ''
  form.value.password = ''
  form.value.password_confirmation = ''
}

const handleUpdateProfile = async () => {
  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    notify.warning('Password confirmation does not match')
    return
  }

  loading.value = true
  try {
    const payload = {
      name: form.value.name,
      username: form.value.username,
    }

    if (form.value.password) {
      payload.password = form.value.password
      payload.password_confirmation = form.value.password_confirmation
    }

    const response = await axios.put('/api/profile', payload)
    
    if (response.data.success) {
      // Update auth store
      authStore.user = response.data.data.user
      localStorage.setItem('user', JSON.stringify(response.data.data.user))
      
      notify.success('Profile updated successfully!')
      resetForm()
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Failed to update profile')
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  loadProfile()
}

onMounted(() => {
  loadProfile()
})
</script>
