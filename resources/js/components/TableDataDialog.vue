<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @click.self="closeDialog"
  >
    <div
      class="w-full max-w-md overflow-hidden transition-all transform bg-white shadow-2xl rounded-2xl dark:bg-gray-800"
    >
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
            {{ isEdit ? 'Edit Meja' : 'Tambah Meja Baru' }}
          </h3>
          <button
            @click="closeDialog"
            class="p-1 text-gray-400 transition rounded-lg hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Body -->
      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
        <!-- Table Number -->
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Nomor Meja<span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.table_number"
            type="text"
            required
            maxlength="10"
            placeholder="contoh: 1, A1, VIP-1"
            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          />
        </div>

        <!-- Status (only for edit) -->
        <div v-if="isEdit">
          <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Status
          </label>
          <select
            v-model="formData.status"
            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          >
            <option value="available">Tersedia</option>
            <option value="maintenance">Maintenance</option>
          </select>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Status "Sedang Bermain" tidak dapat diubah secara manual
          </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-2">
          <button
            type="button"
            @click="closeDialog"
            class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 transition bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 px-4 py-3 text-sm font-semibold text-white transition rounded-lg bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Menyimpan...' : isEdit ? 'Update' : 'Tambah' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'

const notify = useNotificationStore()

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  table: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'success'])

const loading = ref(false)
const formData = ref({
  table_number: '',
  status: 'available',
})

const isEdit = computed(() => !!props.table)

const handleSubmit = async () => {
  loading.value = true
  try {
    let response

    if (isEdit.value) {
      // Update existing table
      response = await axios.put(`/api/tables/${props.table.id}`, formData.value)
    } else {
      // Create new table
      response = await axios.post('/api/tables', {
        table_number: formData.value.table_number,
      })
    }

    if (response.data.success) {
      notify.success(response.data.message)
      emit('success')
      closeDialog()
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Gagal menyimpan data'
    notify.error(message)
  } finally {
    loading.value = false
  }
}

const closeDialog = () => {
  formData.value = {
    table_number: '',
    status: 'available',
  }
  emit('close')
}

watch(
  () => props.table,
  (newTable) => {
    if (newTable) {
      formData.value = {
        table_number: newTable.table_number,
        status: newTable.status,
      }
    } else {
      formData.value = {
        table_number: '',
        status: 'available',
      }
    }
  },
  { immediate: true }
)
</script>
