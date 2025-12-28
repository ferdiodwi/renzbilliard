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
            Perpanjang Waktu - Meja {{ session?.table?.table_number }}
          </h3>
          <button
            @click="closeDialog"
            class="p-1 text-gray-400 transition rounded-lg hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Body -->
      <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
        <!-- Current Info -->
        <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900">
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-gray-500 dark:text-gray-400">Waktu Tersisa</p>
              <p class="font-bold text-gray-800 dark:text-white">{{ formatTime(session?.remaining_seconds) }}</p>
            </div>
            <div>
              <p class="text-gray-500 dark:text-gray-400">Total Saat Ini</p>
              <p class="font-bold text-gray-800 dark:text-white">Rp {{ formatCurrency(session?.total_price) }}</p>
            </div>
          </div>
        </div>

        <!-- Additional Minutes -->
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Tambah Durasi (menit)<span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-3 gap-2 mb-3">
            <button
              v-for="preset in [15, 30, 60]"
              :key="preset"
              type="button"
              @click="additionalMinutes = preset"
              class="px-4 py-2 text-sm font-medium transition border rounded-lg"
              :class="additionalMinutes === preset
                ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400'
                : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300'"
            >
              {{ preset }} menit
            </button>
          </div>
          <input
            v-model.number="additionalMinutes"
            type="number"
            min="15"
            max="240"
            step="15"
            required
            placeholder="atau masukkan manual"
            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          />
        </div>

        <!-- Price Preview -->
        <div v-if="additionalMinutes && session" class="p-4 rounded-lg bg-brand-50 dark:bg-brand-500/10">
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Biaya tambahan</span>
              <span class="font-medium text-gray-800 dark:text-white">
                Rp {{ formatCurrency(additionalPrice) }}
              </span>
            </div>
            <div class="flex justify-between pt-2 font-bold border-t border-brand-200 dark:border-brand-500/30">
              <span class="text-gray-800 dark:text-white">Total Baru</span>
              <span class="text-lg text-brand-600 dark:text-brand-400">
                Rp {{ formatCurrency(newTotalPrice) }}
              </span>
            </div>
          </div>
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
            :disabled="loading || !additionalMinutes"
            class="flex-1 px-4 py-3 text-sm font-semibold text-white transition rounded-lg bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Memproses...' : 'Perpanjang' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'

const notify = useNotificationStore()

const props = defineProps({
  show: Boolean,
  session: Object,
})

const emit = defineEmits(['close', 'success'])

const loading = ref(false)
const additionalMinutes = ref(30)

const additionalPrice = computed(() => {
  if (!props.session || !additionalMinutes.value) return 0
  const hours = additionalMinutes.value / 60
  return Math.ceil(props.session.rate.price_per_hour * hours)
})

const newTotalPrice = computed(() => {
  return (props.session?.total_price || 0) + additionalPrice.value
})

const handleSubmit = async () => {
  if (!props.session) return

  loading.value = true
  try {
    const response = await axios.post(`/api/sessions/${props.session.id}/extend`, {
      additional_minutes: additionalMinutes.value,
    })

    if (response.data.success) {
      notify.success('Sesi berhasil diperpanjang')
      emit('success')
      closeDialog()
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal memperpanjang sesi')
  } finally {
    loading.value = false
  }
}

const closeDialog = () => {
  additionalMinutes.value = 30
  emit('close')
}

const formatTime = (seconds) => {
  if (!seconds) return '0:00:00'
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}
</script>
