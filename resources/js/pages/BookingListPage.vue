<template>
  <div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Booking Meja</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Kelola booking dan reservasi meja billiard
        </p>
      </div>
      <button
        @click="showCreateDialog = true"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Buat Booking Baru
      </button>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-4">
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
        <input
          v-model="filters.date"
          type="date"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        />
      </div>
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
        <select
          v-model="filters.status"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        >
          <option value="">Semua Status</option>
          <option value="PENDING">Pending</option>
          <option value="PLAYING">Bermain</option>
          <option value="COMPLETED">Selesai</option>
          <option value="CANCELLED">Batal</option>
        </select>
      </div>
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Meja</label>
        <select
          v-model="filters.table_id"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        >
          <option value="">Semua Meja</option>
          <option v-for="table in tables" :key="table.id" :value="table.id">
            {{ table.table_number }}
          </option>
        </select>
      </div>
      <div>
        <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cari</label>
        <input
          v-model="filters.search"
          type="text"
          placeholder="Nama atau No. HP"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        />
      </div>
    </div>

    <!-- Quick Date Filters -->
    <div class="flex gap-2 mb-6">
      <button
        @click="setDateFilter('today')"
        class="px-4 py-2 text-sm font-medium transition rounded-lg"
        :class="isToday ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300'"
      >
        Hari Ini
      </button>
      <button
        @click="setDateFilter('tomorrow')"
        class="px-4 py-2 text-sm font-medium transition rounded-lg"
        :class="isTomorrow ? 'bg-brand-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300'"
      >
        Besok
      </button>
      <button
        @click="setDateFilter('week')"
        class="px-4 py-2 text-sm font-medium transition bg-gray-100 rounded-lg text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300"
      >
        Minggu Ini
      </button>
      <button
        @click="filters.date = ''"
        class="px-4 py-2 text-sm font-medium transition bg-gray-100 rounded-lg text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300"
      >
        Semua
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center h-64">
      <div class="text-center">
        <svg class="w-12 h-12 mx-auto text-brand-500 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
        </svg>
        <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
      </div>
    </div>

    <!-- Bookings List -->
    <div v-else-if="bookings.length > 0" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
      <div
        v-for="booking in bookings"
        :key="booking.id"
        class="p-4 transition bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-800 dark:border-gray-700 hover:shadow-md"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                Meja {{ booking.table.table_number }}
              </h3>
              <span
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="getStatusBadgeClass(booking.status)"
              >
                {{ getStatusText(booking.status) }}
              </span>
            </div>
            
            <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
              <p class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ booking.customer_name }} - {{ booking.customer_phone }}
              </p>
              <p class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                {{ formatDate(booking.start_time) }} - {{ formatTime(booking.start_time) }} ({{ booking.duration_minutes }} menit)
              </p>
              <p class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ booking.rate.name }} - Rp {{ formatCurrency(booking.estimated_price) }}
              </p>
              <p v-if="booking.notes" class="flex items-start gap-2 text-xs italic">
                <svg class="w-4 h-4 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                </svg>
                {{ booking.notes }}
              </p>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-2 ml-4">
            <button
              v-if="canCheckIn(booking)"
              @click="handleCheckIn(booking)"
              class="p-2 text-green-600 transition bg-green-50 rounded-lg hover:bg-green-100 dark:bg-green-500/10 dark:hover:bg-green-500/20"
              title="Check-in"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </button>
            <button
              v-if="canEdit(booking)"
              @click="handleEdit(booking)"
              class="p-2 text-blue-600 transition bg-blue-50 rounded-lg hover:bg-blue-100 dark:bg-blue-500/10 dark:hover:bg-blue-500/20"
              title="Edit"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </button>
            <button
              v-if="canCancel(booking)"
              @click="handleCancel(booking)"
              class="p-2 text-red-600 transition bg-red-50 rounded-lg hover:bg-red-100 dark:bg-red-500/10 dark:hover:bg-red-500/20"
              title="Batalkan"
            >
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="py-12 text-center">
      <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-gray-100 dark:bg-gray-800">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum ada booking</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Mulai dengan membuat booking baru
      </p>
    </div>

    <!-- Dialogs -->
    <BookingDialog
      :show="showCreateDialog || showEditDialog"
      :booking="editingBooking"
      :tables="tables"
      :rates="rates"
      @close="closeDialog"
      @success="handleSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'
import { useConfirmStore } from '@/stores/confirm'
import BookingDialog from '@/components/BookingDialog.vue'

const notify = useNotificationStore()
const confirm = useConfirmStore()

const loading = ref(false)
const bookings = ref([])
const tables = ref([])
const rates = ref([])
const showCreateDialog = ref(false)
const showEditDialog = ref(false)
const editingBooking = ref(null)

const filters = ref({
  date: '',
  status: '',
  table_id: '',
  search: '',
})

const isToday = computed(() => {
  const today = new Date()
  const year = today.getFullYear()
  const month = String(today.getMonth() + 1).padStart(2, '0')
  const day = String(today.getDate()).padStart(2, '0')
  const todayString = `${year}-${month}-${day}`
  return filters.value.date === todayString
})

const isTomorrow = computed(() => {
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  const year = tomorrow.getFullYear()
  const month = String(tomorrow.getMonth() + 1).padStart(2, '0')
  const day = String(tomorrow.getDate()).padStart(2, '0')
  const tomorrowString = `${year}-${month}-${day}`
  return filters.value.date === tomorrowString
})

const setDateFilter = (type) => {
  const date = new Date()
  const formatLocalDate = (d) => {
    const year = d.getFullYear()
    const month = String(d.getMonth() + 1).padStart(2, '0')
    const day = String(d.getDate()).padStart(2, '0')
    return `${year}-${month}-${day}`
  }
  
  if (type === 'today') {
    filters.value.date = formatLocalDate(date)
  } else if (type === 'tomorrow') {
    date.setDate(date.getDate() + 1)
    filters.value.date = formatLocalDate(date)
  } else if (type === 'week') {
    // For now, just set to today, we can enhance this later
    filters.value.date = formatLocalDate(date)
  }
}

const fetchBookings = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.date) params.date = filters.value.date
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.table_id) params.table_id = filters.value.table_id
    if (filters.value.search) params.search = filters.value.search

    const response = await axios.get('/api/bookings', { params })
    bookings.value = response.data.data.data || response.data.data
  } catch (error) {
    notify.error('Gagal memuat data booking')
  } finally {
    loading.value = false
  }
}

const fetchTables = async () => {
  try {
    const response = await axios.get('/api/tables')
    tables.value = response.data.data
  } catch (error) {
    console.error('Failed to fetch tables:', error)
  }
}

const fetchRates = async () => {
  try {
    const response = await axios.get('/api/rates', { params: { all: 1 } })
    let data = response.data.data
    
    // Handle pagination response (data.data) vs direct array
    if (data && typeof data === 'object' && 'data' in data) {
      data = data.data
    }
    
    rates.value = Array.isArray(data) ? data : Object.values(data || {})
  } catch (error) {
    console.error('Failed to fetch rates:', error)
  }
}

const canCheckIn = (booking) => {
  return booking.status === 'PENDING'
}

const canEdit = (booking) => {
  return booking.status === 'PENDING' || booking.status === 'CONFIRMED'
}

const canCancel = (booking) => {
  return booking.status === 'PENDING' || booking.status === 'CONFIRMED'
}

const handleCheckIn = async (booking) => {
  const confirmed = await confirm.show({
    title: 'Check-in Booking',
    message: `Check-in booking untuk ${booking.customer_name}? Session akan segera dimulai.`,
    confirmText: 'Check-in',
    type: 'info'
  })

  if (!confirmed) return

  try {
    const response = await axios.post(`/api/bookings/${booking.id}/check-in`)
    if (response.data.success) {
      notify.success('Check-in berhasil! Session dimulai.')
      fetchBookings()
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal check-in')
  }
}

const handleEdit = (booking) => {
  editingBooking.value = booking
  showEditDialog.value = true
}

const handleCancel = async (booking) => {
  const confirmed = await confirm.show({
    title: 'Batalkan Booking',
    message: `Yakin ingin membatalkan booking untuk ${booking.customer_name}?`,
    confirmText: 'Batalkan',
    type: 'danger'
  })

  if (!confirmed) return

  try {
    const response = await axios.delete(`/api/bookings/${booking.id}`)
    if (response.data.success) {
      notify.success('Booking berhasil dibatalkan')
      fetchBookings()
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal membatalkan booking')
  }
}

const closeDialog = () => {
  showCreateDialog.value = false
  showEditDialog.value = false
  editingBooking.value = null
}

const handleSuccess = () => {
  fetchBookings()
}

const getStatusBadgeClass = (status) => {
  const classes = {
    PENDING: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-500/10 dark:text-yellow-400',
    PLAYING: 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400',
    COMPLETED: 'bg-gray-100 text-gray-800 dark:bg-gray-500/10 dark:text-gray-400',
    CANCELLED: 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400',
  }
  return classes[status] || classes.PENDING
}

const getStatusText = (status) => {
  const texts = {
    PENDING: 'Pending',
    PLAYING: 'Bermain',
    COMPLETED: 'Selesai',
    CANCELLED: 'Batal',
  }
  return texts[status] || status
}

const formatDate = (dateString) => {
  // Parse as local date to avoid timezone conversion issues
  const date = new Date(dateString.replace(' ', 'T'))
  return date.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' })
}

const formatTime = (dateString) => {
  const date = new Date(dateString.replace(' ', 'T'))
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

// Watch filters and refetch
watch(filters, () => {
  fetchBookings()
}, { deep: true })

onMounted(() => {
  fetchBookings()
  fetchTables()
  fetchRates()
  
  // Set today as default
  setDateFilter('today')
})
</script>
