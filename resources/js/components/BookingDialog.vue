<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @click.self="closeDialog"
  >
    <div class="w-full max-w-2xl overflow-hidden bg-white shadow-2xl rounded-2xl dark:bg-gray-800 max-h-[90vh] flex flex-col">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
            {{ booking ? 'Edit Booking' : 'Buat Booking Baru' }}
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
      <div class="px-6 py-4 overflow-y-auto flex-1">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Customer Info -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Nama Customer <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.customer_name"
                type="text"
                required
                placeholder="Nama lengkap"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />
            </div>
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                No. HP <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.customer_phone"
                type="tel"
                required
                placeholder="08xxx"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              />
            </div>
          </div>

          <!-- Date & Time -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Tanggal <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.booking_date"
                type="date"
                required
                :min="minDate"
                onclick="this.showPicker()"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-pointer"
              />
            </div>
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Jam Mulai <span class="text-red-500">*</span>
              </label>
              <TimePicker
                v-model="form.start_time"
                placeholder="Pilih jam"
                :minute-step="1"
              />
              <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Format 24 jam</p>
            </div>
          </div>

          <!-- Duration & Table -->
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Durasi <span class="text-red-500">*</span>
              </label>
              <select
                v-model.number="form.duration_minutes"
                required
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              >
                <option value="">Pilih durasi</option>
                <option :value="60">1 jam</option>
                <option :value="120">2 jam</option>
                <option :value="180">3 jam</option>
                <option :value="240">4 jam</option>
                <option :value="300">5 jam</option>
                <option :value="360">6 jam</option>
                <option :value="420">7 jam</option>
                <option :value="480">8 jam</option>
                <option :value="540">9 jam</option>
                <option :value="600">10 jam</option>
                <option :value="660">11 jam</option>
                <option :value="720">12 jam</option>
              </select>
            </div>
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Meja <span class="text-red-500">*</span>
              </label>
              <select
                v-model.number="form.table_id"
                required
                @change="checkAvailability"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              >
                <option value="">Pilih meja</option>
                <option v-for="table in tables" :key="table.id" :value="table.id">
                  {{ table.table_number }}
                </option>
              </select>
            </div>
          </div>

          <!-- Rate -->
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Tarif <span class="text-red-500">*</span>
            </label>
            <select
              v-model.number="form.rate_id"
              required
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            >
              <option value="">Pilih tarif</option>
              <option v-for="rate in ratesArray" :key="rate.id" :value="rate.id">
                {{ rate.name }} - Rp {{ formatCurrency(rate.price_per_hour) }}/jam
              </option>
            </select>
          </div>

          <!-- Availability Check Result -->
          <div v-if="availabilityChecked" class="p-4 rounded-lg" :class="isAvailable ? 'bg-green-50 border border-green-400' : 'bg-red-50 border border-red-400'">
            <div class="flex items-start gap-2">
              <svg v-if="isAvailable" class="w-5 h-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <svg v-else class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              <div class="flex-1">
                <p class="font-semibold" :class="isAvailable ? 'text-green-800' : 'text-red-800'">
                  {{ isAvailable ? 'Meja Tersedia' : 'Meja Tidak Tersedia' }}
                </p>
                <p v-if="!isAvailable" class="text-sm text-red-700 mt-1">
                  Ada booking lain yang bentrok pada waktu tersebut
                </p>
              </div>
            </div>
          </div>

          <!-- Estimated Price -->
          <div v-if="estimatedPrice > 0" class="p-4 rounded-lg bg-brand-50 dark:bg-brand-500/10 border-2 border-brand-500">
            <div class="flex justify-between items-center">
              <span class="font-semibold text-gray-800 dark:text-white">Estimasi Harga</span>
              <span class="text-xl font-bold text-brand-600 dark:text-brand-400">
                Rp {{ formatCurrency(estimatedPrice) }}
              </span>
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Catatan (Optional)
            </label>
            <textarea
              v-model="form.notes"
              rows="3"
              placeholder="Catatan tambahan..."
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            ></textarea>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 pt-2">
            <button
              type="button"
              @click="closeDialog"
              class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 transition bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="loading || (availabilityChecked && !isAvailable)"
              class="flex-1 px-4 py-3 text-sm font-semibold text-white transition rounded-lg bg-brand-500 hover:bg-brand-600 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ loading ? 'Menyimpan...' : (booking ? 'Update' : 'Buat Booking') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'
import TimePicker from '@/components/ui/TimePicker.vue'

const notify = useNotificationStore()

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  booking: {
    type: Object,
    default: null,
  },
  tables: {
    type: Array,
    default: () => [],
  },
  rates: {
    type: [Array, Object],  // Accept both Array and Object
    default: () => [],
  },
})

const emit = defineEmits(['close', 'success'])

const loading = ref(false)
const availabilityChecked = ref(false)
const isAvailable = ref(false)

const form = ref({
  customer_name: '',
  customer_phone: '',
  booking_date: '',
  start_time: '',
  duration_minutes: '',
  table_id: '',
  rate_id: '',
  notes: '',
})

// Handle conversion for VueDatePicker
const startTimeValue = computed({
  get: () => {
    if (!form.value.start_time) return null
    const [hours, minutes] = form.value.start_time.split(':').map(Number)
    return { hours, minutes }
  },
  set: (val) => {
    if (!val) {
      form.value.start_time = ''
    } else {
      const hours = String(val.hours).padStart(2, '0')
      const minutes = String(val.minutes).padStart(2, '0')
      form.value.start_time = `${hours}:${minutes}`
    }
  }
})

// Normalize rates to always be an array and filter out null values
const ratesArray = computed(() => {
  let result = []
  if (Array.isArray(props.rates)) {
    result = props.rates
  } else {
    // If it's an object, convert to array
    result = Object.values(props.rates || {})
  }
  // Filter out null/undefined values
  return result.filter(rate => rate !== null && rate !== undefined)
})

// Min date: today (no max date - unlimited future bookings)
const minDate = computed(() => {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
})

// Calculate estimated price
const estimatedPrice = computed(() => {
  if (!form.value.rate_id || !form.value.duration_minutes) return 0
  
  const rate = ratesArray.value.find(r => r.id === form.value.rate_id)
  if (!rate) return 0
  
  return Math.ceil((form.value.duration_minutes / 60) * rate.price_per_hour)
})

// Debounce timer for availability check
let availabilityCheckTimeout = null

// Check availability when form changes (debounced)
watch([() => form.value.table_id, () => form.value.booking_date, () => form.value.start_time, () => form.value.duration_minutes], () => {
  // Clear previous timeout
  if (availabilityCheckTimeout) {
    clearTimeout(availabilityCheckTimeout)
  }
  
  // Reset availability state while waiting
  availabilityChecked.value = false
  
  if (form.value.table_id && form.value.booking_date && form.value.start_time && form.value.duration_minutes) {
    // Debounce: wait 500ms after last change before checking
    availabilityCheckTimeout = setTimeout(() => {
      checkAvailability()
    }, 500)
  }
})

// Check table availability
const checkAvailability = async () => {
  if (!form.value.table_id || !form.value.booking_date || !form.value.start_time || !form.value.duration_minutes) {
    return
  }

  try {
    const response = await axios.get('/api/bookings/check-availability', {
      params: {
        table_id: form.value.table_id,
        date: form.value.booking_date,
        start_time: form.value.start_time,
        duration_minutes: form.value.duration_minutes,
      },
    })

    availabilityChecked.value = true
    isAvailable.value = response.data.available
  } catch (error) {
    console.error('Failed to check availability:', error)
  }
}

const handleSubmit = async () => {
  if (!isAvailable.value) {
    notify.error('Meja tidak tersedia pada waktu tersebut')
    return
  }

  loading.value = true
  try {
    const url = props.booking ? `/api/bookings/${props.booking.id}` : '/api/bookings'
    const method = props.booking ? 'put' : 'post'
    
    const response = await axios[method](url, form.value)

    if (response.data.success) {
      notify.success(props.booking ? 'Booking berhasil diupdate' : 'Booking berhasil dibuat')
      emit('success', response.data.data)
      closeDialog()
    }
  } catch (error) {
    const message = error.response?.data?.message || 'Gagal menyimpan booking'
    notify.error(message)
  } finally {
    loading.value = false
  }
}

const closeDialog = () => {
  form.value = {
    customer_name: '',
    customer_phone: '',
    booking_date: '',
    start_time: '',
    duration_minutes: '',
    table_id: '',
    rate_id: '',
    notes: '',
  }
  availabilityChecked.value = false
  isAvailable.value = false
  emit('close')
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

// Initialize form when editing
watch(() => props.booking, (newBooking) => {
  if (newBooking) {
    const startTime = new Date(newBooking.start_time)
    form.value = {
      customer_name: newBooking.customer_name,
      customer_phone: newBooking.customer_phone,
      booking_date: newBooking.booking_date,
      start_time: startTime.toTimeString().slice(0, 5),
      duration_minutes: newBooking.duration_minutes,
      table_id: newBooking.table_id,
      rate_id: newBooking.rate_id,
      notes: newBooking.notes || '',
    }
    checkAvailability()
  }
}, { immediate: true })

// Set default date to today when dialog opens for new booking
watch(() => props.show, (isShowing) => {
  if (isShowing && !props.booking) {
    // Reset and set default for new booking using local timezone
    const today = new Date()
    const year = today.getFullYear()
    const month = String(today.getMonth() + 1).padStart(2, '0')
    const day = String(today.getDate()).padStart(2, '0')
    form.value.booking_date = `${year}-${month}-${day}`
    availabilityChecked.value = false
    isAvailable.value = false
  } else if (!isShowing) {
    // Clear form when dialog closes
    form.value = {
      customer_name: '',
      customer_phone: '',
      booking_date: '',
      start_time: '',
      duration_minutes: '',
      table_id: '',
      rate_id: '',
      notes: '',
    }
    availabilityChecked.value = false
    isAvailable.value = false
  }
})

</script>
