<template>
  <div>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Sesi Aktif</h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Monitor dan kelola semua sesi billiard yang sedang berlangsung
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center h-64">
      <div class="text-center">
        <svg
          class="w-12 h-12 mx-auto text-brand-500 animate-spin"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          />
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          />
        </svg>
        <p class="mt-2 text-sm text-gray-500">Memuat data...</p>
      </div>
    </div>

    <!-- Sessions Grid -->
    <div v-else-if="sessions.length > 0" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="session in sessions"
        :key="session.id"
        class="overflow-hidden transition-all border-2 border-red-500 rounded-2xl bg-white dark:bg-gray-800 hover:shadow-lg"
      >
        <!-- Card Header -->
        <div class="p-6 pb-4">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-red-100 dark:bg-red-500/20">
                <svg
                  class="w-6 h-6 text-red-600 dark:text-red-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                  />
                </svg>
              </div>
              <div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                  Meja {{ session.table.table_number }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  {{ session.rate.name }}
                </p>
              </div>
            </div>
            <span
              class="px-3 py-1.5 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400"
            >
              Bermain
            </span>
          </div>

          <!-- Countdown Timer -->
          <div class="p-6 mb-4 text-center rounded-xl bg-gray-50 dark:bg-gray-900">
            <p class="mb-2 text-xs font-medium text-gray-600 dark:text-gray-400">
              WAKTU TERSISA
            </p>
            <div class="text-4xl font-bold tabular-nums" :class="getTimerColor(session.remaining_seconds)">
              {{ formatTime(session.remaining_seconds) }}
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
              Berakhir: {{ formatEndTime(session.end_time) }}
            </p>
          </div>

          <!-- Session Info -->
          <div class="space-y-2">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">Mulai</span>
              <span class="font-medium text-gray-800 dark:text-white">
                {{ formatStartTime(session.start_time) }}
              </span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">Durasi</span>
              <span class="font-medium text-gray-800 dark:text-white">
                {{ session.duration_minutes }} menit
              </span>
            </div>
            <div class="flex items-center justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
              <span class="font-medium text-gray-600 dark:text-gray-400">Total</span>
              <span class="text-xl font-bold text-gray-800 dark:text-white">
                Rp {{ formatCurrency(session.total_price) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-2 px-6 pb-6">
          <button
            @click="handleExtend(session)"
            class="flex-1 px-4 py-2.5 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600"
          >
            Perpanjang
          </button>
          <button
            @click="handleStop(session)"
            class="flex-1 px-4 py-2.5 text-sm font-medium text-white transition rounded-lg bg-red-500 hover:bg-red-600"
          >
            Stop & Bayar
          </button>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-gray-100 dark:bg-gray-800">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tidak ada sesi aktif</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Mulai sesi baru dari halaman Meja Billiard
      </p>
      <router-link
        to="/tables"
        class="inline-flex items-center gap-2 px-4 py-2 mt-4 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600"
      >
        Lihat Meja
      </router-link>
    </div>

    <!-- Extend Dialog -->
    <ExtendSessionDialog
      :show="showExtendDialog"
      :session="selectedSession"
      @close="showExtendDialog = false"
      @success="handleExtended"
    />

    <!-- Payment Dialog -->
    <PaymentDialog
      :show="showPaymentDialog"
      :session-data="paymentSessionData"
      @close="showPaymentDialog = false"
      @success="handlePaymentSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import ExtendSessionDialog from '@/components/ExtendSessionDialog.vue'
import PaymentDialog from '@/components/PaymentDialog.vue'
import { useNotificationStore } from '@/stores/notification'
import { useConfirmStore } from '@/stores/confirm'

const notify = useNotificationStore()
const confirm = useConfirmStore()

const loading = ref(false)
const sessions = ref([])
const selectedSession = ref(null)
const showExtendDialog = ref(false)
const showPaymentDialog = ref(false)
const paymentSessionData = ref(null)

let refreshInterval = null
let countdownInterval = null

const fetchSessions = async () => {
  try {
    const response = await axios.get('/api/sessions/active')
    if (response.data.success) {
      sessions.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch sessions:', error)
  } finally {
    loading.value = false
  }
}

const handleExtend = (session) => {
  selectedSession.value = session
  showExtendDialog.value = true
}

const handleExtended = () => {
  fetchSessions()
}

const handlePaymentSuccess = () => {
  // Refresh sessions after payment
  fetchSessions()
}

const handleStop = async (session) => {
  const confirmed = await confirm.show({
      title: 'Hentikan Sesi',
      message: `Yakin ingin menghentikan sesi Meja ${session.table.table_number}?`,
      confirmText: 'Stop & Bayar',
      type: 'warning'
  })

  if (!confirmed) return

  try {
    const response = await axios.post(`/api/sessions/${session.id}/stop`)
    if (response.data.success) {
      const data = response.data.data
      
      // Show payment dialog with stopped session data + F&B
      paymentSessionData.value = {
        session_id: session.id,
        table_number: session.table.table_number,
        session_charges: data.session_charges,
        fnb_charges: data.fnb_charges,
        total_charges: data.total_charges,
        fnb_orders: data.fnb_orders || [],
      }
      showPaymentDialog.value = true
      
      // Refresh sessions list
      fetchSessions()
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal menghentikan sesi')
  }
}

const updateCountdown = () => {
  sessions.value = sessions.value.map((session) => ({
    ...session,
    remaining_seconds: Math.max(0, session.remaining_seconds - 1),
  }))
}

const formatTime = (seconds) => {
  const hours = Math.floor(seconds / 3600)
  const minutes = Math.floor((seconds % 3600) / 60)
  const secs = seconds % 60
  return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
}

const formatStartTime = (isoString) => {
  const date = new Date(isoString)
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

const formatEndTime = (isoString) => {
  const date = new Date(isoString)
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

const getTimerColor = (seconds) => {
  if (seconds <= 300) return 'text-red-600 dark:text-red-400' // 5 minutes
  if (seconds <= 600) return 'text-warning-600 dark:text-warning-400' // 10 minutes
  return 'text-brand-600 dark:text-brand-400'
}

onMounted(() => {
  loading.value = true
  fetchSessions()

  // Refresh data every 30 seconds
  refreshInterval = setInterval(fetchSessions, 30000)

  // Update countdown every second
  countdownInterval = setInterval(updateCountdown, 1000)
})

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval)
  if (countdownInterval) clearInterval(countdownInterval)
})
</script>
