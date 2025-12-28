<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @click.self="closeDialog"
  >
    <div class="w-full max-w-md overflow-hidden bg-white shadow-2xl rounded-2xl dark:bg-gray-800">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
          {{ isEdit ? 'Edit Tarif' : 'Tambah Tarif Baru' }}
        </h3>
      </div>

      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Nama Tarif<span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.name"
            type="text"
            required
            placeholder="contoh: Reguler, VIP, Weekend"
            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          />
        </div>

        <div>
          <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Harga per Jam<span class="text-red-500">*</span>
          </label>
          <input
            v-model.number="formData.price_per_hour"
            type="number"
            min="0"
            step="1000"
            required
            placeholder="contoh: 50000"
            class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          />
        </div>

        <div class="flex gap-3 pt-2">
          <button
            type="button"
            @click="closeDialog"
            class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
          >
            Batal
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="flex-1 px-4 py-3 text-sm font-semibold text-white rounded-lg bg-brand-500 hover:bg-brand-600 disabled:opacity-50"
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
  show: Boolean,
  rate: Object,
})

const emit = defineEmits(['close', 'success'])

const loading = ref(false)
const formData = ref({ name: '', price_per_hour: 0 })

const isEdit = computed(() => !!props.rate)

const handleSubmit = async () => {
  loading.value = true
  try {
    let response
    if (isEdit.value) {
      response = await axios.put(`/api/rates/${props.rate.id}`, formData.value)
    } else {
      response = await axios.post('/api/rates', formData.value)
    }

    if (response.data.success) {
      notify.success(response.data.message)
      emit('success')
      closeDialog()
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal menyimpan tarif')
  } finally {
    loading.value = false
  }
}

const closeDialog = () => {
  formData.value = { name: '', price_per_hour: 0 }
  emit('close')
}

watch(() => props.rate, (newRate) => {
  if (newRate) {
    formData.value = { ...newRate }
  } else {
    formData.value = { name: '', price_per_hour: 0 }
  }
}, { immediate: true })
</script>
