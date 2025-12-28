<template>
  <div class="p-4 md:p-6 space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
      <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Manajemen User</h1>
      <button
        @click="showDialog = true; editingUser = null"
        class="w-full sm:w-auto px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg transition text-sm"
      >
        + Tambah User
      </button>
    </div>

    <!-- Users Table -->
    <div class="overflow-x-auto bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
      <table class="w-full min-w-[640px]">
        <thead class="bg-gray-200 dark:bg-gray-900">
          <tr>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">No</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Name</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Username</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Role</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="(user, index) in users" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm font-medium text-gray-900 dark:text-white">{{ (pagination.meta.from || 1) + index }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-900 dark:text-white">{{ user.name }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-900 dark:text-white">{{ user.username }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <span
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="user.role === 'admin' ? 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400'"
              >
                {{ user.role }}
              </span>
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <div class="flex gap-2">
                <button
                  @click="handleEdit(user)"
                  class="px-3 py-1 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium"
                >
                  Edit
                </button>
                <button
                  @click="handleDelete(user)"
                  class="px-3 py-1 text-sm text-red-600 hover:text-red-700 dark:text-red-400 font-medium"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- User Dialog -->
    <div
      v-if="showDialog"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      @click.self="showDialog = false"
    >
      <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
          {{ editingUser ? 'Edit User' : 'Tambah User' }}
        </h3>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Username</label>
            <input
              v-model="form.username"
              type="text"
              required
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
            <select
              v-model="form.role"
              required
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
            >
              <option value="admin">Admin</option>
              <option value="kasir">Kasir</option>
            </select>
          </div>

          <div v-if="!editingUser">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Password</label>
            <input
              v-model="form.password"
              type="password"
              :required="!editingUser"
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
            />
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg disabled:opacity-50"
            >
              {{ loading ? 'Saving...' : 'Save' }}
            </button>
            <button
              type="button"
              @click="showDialog = false"
              class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import Pagination from '@/components/ui/Pagination.vue'
import { useNotificationStore } from '@/stores/notification'
import { useConfirmStore } from '@/stores/confirm'

const notify = useNotificationStore()
const confirm = useConfirmStore()

const users = ref([])
const pagination = ref({ links: [], meta: {} })
const loading = ref(false)
const showDialog = ref(false)
const editingUser = ref(null)
const currentPage = ref(1)

const form = ref({
  name: '',
  username: '',
  role: 'kasir',
  password: '',
})

const fetchUsers = async (page = 1) => {
  currentPage.value = page
  try {
    const response = await axios.get('/api/users', {
        params: { page }
    })
    users.value = response.data.data.data
    pagination.value = {
        links: response.data.data.links,
        meta: {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            from: response.data.data.from,
            to: response.data.data.to,
            total: response.data.data.total
        }
    }
  } catch (error) {
    console.error('Failed to fetch users:', error)
  }
}

const handlePageChange = (page) => {
    fetchUsers(page)
}

const handleEdit = (user) => {
  editingUser.value = user
  form.value = {
    name: user.name,
    username: user.username,
    role: user.role,
    password: '',
  }
  showDialog.value = true
}

const handleSubmit = async () => {
  loading.value = true
  try {
    if (editingUser.value) {
      // Update
      await axios.put(`/api/users/${editingUser.value.id}`, form.value)
      notify.success('User updated successfully!')
    } else {
      // Create
      await axios.post('/api/users', form.value)
      notify.success('User created successfully!')
    }
    
    showDialog.value = false
    resetForm()
    fetchUsers()
  } catch (error) {
    notify.error(error.response?.data?.message || 'Failed to save user')
  } finally {
    loading.value = false
  }
}

const handleDelete = async (user) => {
  const confirmed = await confirm.show({
    title: 'Delete User',
    message: `Are you sure you want to delete ${user.name}?`,
    confirmText: 'Delete',
    type: 'danger'
  })

  if (!confirmed) return

  try {
    await axios.delete(`/api/users/${user.id}`)
    notify.success('User deleted successfully!')
    fetchUsers()
  } catch (error) {
    notify.error(error.response?.data?.message || 'Failed to delete user')
  }
}

const resetForm = () => {
  form.value = {
    name: '',
    username: '',
    role: 'kasir',
    password: '',
  }
  editingUser.value = null
}

onMounted(() => {
  fetchUsers()
})
</script>
