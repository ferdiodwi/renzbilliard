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
            Mulai Sesi - {{ table?.table_number }}
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
        <!-- Customer Name -->
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Nama Pelanggan<span class="text-red-500">*</span>
          </label>
          <input
            v-model="formData.customer_name"
            type="text"
            required
            maxlength="255"
            placeholder="Masukkan nama pelanggan"
            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          />
        </div>

        <!-- Rate Selection -->
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Pilih Tarif<span class="text-red-500">*</span>
          </label>
          <select
            v-model="formData.rate_id"
            required
            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          >
            <option value="">-- Pilih Tarif --</option>
            <option v-for="rate in rates" :key="rate.id" :value="rate.id">
              {{ rate.name }} - Rp {{ formatCurrency(rate.price_per_hour) }}/jam
            </option>
          </select>
        </div>

        <!-- Duration -->
        <div>
          <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            Durasi (menit)<span class="text-red-500">*</span>
          </label>
          <input
            v-model.number="formData.duration_minutes"
            type="number"
            min="2"
            max="480"
            step="1"
            required
            placeholder="contoh: 60"
            class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          />
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Minimal 2 menit, maksimal 480 menit (8 jam)
          </p>
        </div>

        <!-- Auto Stop -->
        <div class="flex items-center gap-3">
          <input
            v-model="formData.auto_stop"
            type="checkbox"
            id="auto_stop"
            class="w-5 h-5 text-brand-500 border-gray-300 rounded focus:ring-brand-500"
          />
          <label for="auto_stop" class="text-sm text-gray-700 dark:text-gray-300">
            Auto stop saat waktu habis
          </label>
        </div>

        <!-- Price Preview -->
        <div
          v-if="selectedRate && formData.duration_minutes"
          class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900"
        >
          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
              Estimasi Total
            </span>
            <span class="text-xl font-bold text-gray-800 dark:text-white">
              Rp {{ formatCurrency(estimatedPrice) }}
            </span>
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
            :disabled="loading || !formData.rate_id || !formData.duration_minutes"
            class="flex-1 px-4 py-3 text-sm font-semibold text-white transition rounded-lg bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Memproses...' : 'Mulai Sesi' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
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
const rates = ref([])
const formData = ref({
  customer_name: '',
  rate_id: '',
  duration_minutes: 60,
  auto_stop: true,
})

const selectedRate = computed(() => {
  return rates.value.find((r) => r.id === formData.value.rate_id)
})

const estimatedPrice = computed(() => {
  if (!selectedRate.value || !formData.value.duration_minutes) return 0
  const hours = formData.value.duration_minutes / 60
  return Math.ceil(selectedRate.value.price_per_hour * hours)
})

const fetchRates = async () => {
  try {
    const response = await axios.get('/api/rates', { params: { all: 1 } })
    if (response.data.success) {
      rates.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch rates:', error)
  }
}

const handleSubmit = async () => {
  if (!props.table) return

  loading.value = true
  try {
    const response = await axios.post('/api/sessions/start', {
      table_id: props.table.id,
      customer_name: formData.value.customer_name,
      rate_id: formData.value.rate_id,
      duration_minutes: formData.value.duration_minutes,
      auto_stop: formData.value.auto_stop,
    })

    if (response.data.success) {
      notify.success('Sesi berhasil dimulai')
      emit('success', response.data.data)
      closeDialog()
      // Reset form
      formData.value = {
        customer_name: '',
        rate_id: '',
        duration_minutes: 60,
        auto_stop: true,
      }
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Gagal memulai sesi'
    notify.error(message)
  } finally {
    loading.value = false
  }
}

const closeDialog = () => {
  emit('close')
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

watch(
  () => props.show,
  (newVal) => {
    if (newVal) {
      fetchRates()
    }
  }
)

onMounted(() => {
  if (props.show) {
    fetchRates()
  }
})
</script>
