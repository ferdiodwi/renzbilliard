<template>
  <div
    class="flex flex-col overflow-hidden transition-all border-2 rounded-2xl bg-white dark:bg-gray-800 hover:shadow-lg h-full"
    :class="{
      'border-green-500': table.status === 'available',
      'border-red-500': table.status === 'playing',
      'border-yellow-500': table.status === 'maintenance'
    }"
  >
    <!-- Card Header -->
    <div class="p-2 pb-1.5">
      <div class="flex items-center justify-between mb-1.5">
        <div class="flex items-center gap-3">
          <div
            class="flex items-center justify-center w-7 h-7 rounded-lg"
            :class="{
              'bg-green-100 dark:bg-green-500/20': table.status === 'available',
              'bg-red-100 dark:bg-red-500/20': table.status === 'playing',
              'bg-yellow-100 dark:bg-yellow-500/20': table.status === 'maintenance'
            }"
          >
            <svg
              class="w-3.5 h-3.5"
              :class="{
                'text-green-600 dark:text-green-400': table.status === 'available',
                'text-red-600 dark:text-red-400': table.status === 'playing',
                'text-yellow-600 dark:text-yellow-400': table.status === 'maintenance'
              }"
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
            <h3 class="text-base font-bold text-gray-800 dark:text-white">
              Meja {{ table.table_number }}
            </h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
              {{ table.active_session ? table.active_session.rate.name : 'Billiard Table' }}
            </p>
          </div>
        </div>
        
        <div class="flex items-center gap-2">
          <!-- Status Badge -->
          <span
            class="px-2 py-0.5 text-xs font-semibold rounded-full"
            :class="{
              'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400': table.status === 'available',
              'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400': table.status === 'playing',
              'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400': table.status === 'maintenance'
            }"
          >
            {{ table.status === 'available' ? 'Tersedia' : table.status === 'playing' ? 'Sedang Bermain' : 'Maintenance' }}
          </span>
          
          <!-- Three Dot Menu for Admin -->
          <div v-if="showAdminActions" class="relative">
            <button
              @click="toggleMenu"
              class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition"
            >
              <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
              </svg>
            </button>
            
            <!-- Dropdown Menu -->
            <div
              v-if="isMenuOpen"
              class="absolute right-0 top-8 w-40 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 z-10"
            >
              <button
                @click="handleEdit"
                :disabled="table.status === 'playing'"
                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 rounded-t-lg flex items-center gap-2"
                :class="table.status === 'playing' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100 dark:hover:bg-gray-700'"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
              </button>
              <button
                @click="handleDelete"
                :disabled="table.status === 'playing'"
                class="w-full px-4 py-2 text-left text-sm text-red-600 dark:text-red-400 rounded-b-lg flex items-center gap-2"
                :class="table.status === 'playing' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-red-50 dark:hover:bg-red-900/20'"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 flex flex-col justify-center min-h-[180px]">
        <!-- Available State - Clean Design -->
        <div v-if="table.status === 'available'" class="flex flex-col items-center justify-center px-4">
          <!-- Play Icon -->
          <div class="w-16 h-16 mb-3 rounded-full bg-green-100 dark:bg-green-500/20 flex items-center justify-center">
            <svg class="w-7 h-7 text-green-600 dark:text-green-400 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M8 5v14l11-7z"/>
            </svg>
          </div>
          
          <!-- Title -->
          <h4 class="text-base font-bold text-gray-900 dark:text-white mb-1">Siap Digunakan</h4>
          
          <!-- Subtitle -->
          <p class="text-xs text-gray-500 dark:text-gray-400 text-center">Klik tombol di bawah untuk memulai sesi</p>
        </div>
        
        <!-- Maintenance State -->
        <div v-else-if="table.status === 'maintenance'" class="flex flex-col items-center justify-center px-4">
          <div class="w-16 h-16 mb-3 rounded-full bg-yellow-100 dark:bg-yellow-500/20 flex items-center justify-center">
            <svg class="w-7 h-7 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <h4 class="text-base font-bold text-gray-900 dark:text-white mb-1">Sedang Maintenance</h4>
          <p class="text-xs text-gray-500 dark:text-gray-400 text-center">Meja sedang dalam perbaikan</p>
        </div>

        <!-- Playing State -->
        <div v-else-if="table.status === 'playing'" class="space-y-1.5">
          <!-- Large Countdown Timer -->
          <div class="p-2 text-center rounded-lg bg-gray-50 dark:bg-gray-900">
            <p class="mb-1 text-xs font-medium text-gray-600 dark:text-gray-400">
              WAKTU TERSISA
            </p>
            <div class="text-2xl font-bold tabular-nums" :class="getTimerColor(table.active_session.remaining_seconds)">
              {{ formatTime(table.active_session.remaining_seconds) }}
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Berakhir: {{ formatEndTime(table.active_session.end_time) }}
            </p>
          </div>

          <!-- Session Details -->
          <div class="space-y-1.5 px-1">
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">Nama</span>
              <span class="font-semibold text-gray-900 dark:text-white truncate max-w-[140px]" :title="table.active_session.customer_name">
                {{ table.active_session.customer_name }}
              </span>
            </div>
            
            <div class="flex items-center justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">Durasi</span>
              <span class="font-medium text-gray-800 dark:text-white">
                {{ table.active_session.duration_minutes }} menit
              </span>
            </div>

            <div v-if="table.active_session.fnb_total > 0" class="flex flex-col pt-1.5 border-t border-dashed border-gray-200 dark:border-gray-700">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600 dark:text-gray-400">F&B Items</span>
                <span class="font-medium text-gray-800 dark:text-white">
                  Rp {{ formatCurrency(table.active_session.fnb_total) }}
                </span>
              </div>
              <div v-if="table.active_session.fnb_summary" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 italic truncate" :title="table.active_session.fnb_summary">
                {{ table.active_session.fnb_summary }}
              </div>
            </div>
            
            <div class="flex items-center justify-between pt-2 mt-1 border-t border-dashed border-gray-300 dark:border-gray-600">
              <span class="font-bold text-gray-700 dark:text-gray-300">Total Bayar</span>
              <span class="text-lg font-bold text-brand-600 dark:text-brand-400">
                Rp {{ formatCurrency(table.active_session.total_price) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="px-2 pb-2">
      <!-- Available Table Actions -->
      <div v-if="table.status === 'available'">
        <button
          @click="$emit('start', table)"
          class="w-full px-3 py-2 text-sm font-semibold text-white transition rounded-lg bg-green-500 hover:bg-green-600 flex items-center justify-center gap-2"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
            <path d="M8 5v14l11-7z"/>
          </svg>
          Mulai Sesi
        </button>
      </div>

      <!-- Playing Table Actions -->
      <div v-else-if="table.status === 'playing'" class="space-y-2">
        <button
          @click="$emit('order', table)"
          class="w-full px-3 py-2 text-sm font-semibold text-brand-700 bg-brand-50 border border-brand-200 rounded-lg hover:bg-brand-100 transition flex items-center justify-center gap-2 dark:bg-brand-900/20 dark:border-brand-800 dark:text-brand-300 dark:hover:bg-brand-900/40"
        >
          <span class="text-base">🍔</span> Order F&B
        </button>
        
        <div class="flex gap-2">
          <button
            @click="$emit('extend', table)"
            class="flex-1 px-2 py-1.5 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600"
          >
            Perpanjang
          </button>
          <button
            @click="$emit('stop', table)"
            class="flex-1 px-2 py-1.5 text-sm font-medium text-white transition rounded-lg bg-red-500 hover:bg-red-600"
          >
            Stop & Bayar
          </button>
        </div>
      </div>
      
      <!-- No actions for maintenance status -->
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const props = defineProps({
  table: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(['start', 'extend', 'stop', 'edit', 'delete'])

const authStore = useAuthStore()
const isMenuOpen = ref(false)

const showAdminActions = computed(() => authStore.isAdmin)

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const handleEdit = () => {
  isMenuOpen.value = false
  emit('edit', props.table)
}

const handleDelete = () => {
  isMenuOpen.value = false
  emit('delete', props.table)
}

const statusText = computed(() => {
  if (props.table.status === 'playing') return 'Sedang Bermain'
  if (props.table.status === 'maintenance') return 'Maintenance'
  return 'Tersedia'
})

const statusBorder = computed(() => {
  if (props.table.status === 'playing') return 'border-red-500'
  if (props.table.status === 'maintenance') return 'border-warning-500'
  return 'border-success-500'
})

const statusBadge = computed(() => {
  if (props.table.status === 'playing')
    return 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'
  if (props.table.status === 'maintenance')
    return 'bg-warning-100 text-warning-700 dark:bg-warning-500/20 dark:text-warning-400'
  return 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400'
})

const statusIconBg = computed(() => {
  if (props.table.status === 'playing') return 'bg-red-100 dark:bg-red-500/20'
  if (props.table.status === 'maintenance') return 'bg-warning-100 dark:bg-warning-500/20'
  return 'bg-success-100 dark:bg-success-500/20'
})

const statusIconColor = computed(() => {
  if (props.table.status === 'playing') return 'text-red-600 dark:text-red-400'
  if (props.table.status === 'maintenance') return 'text-warning-600 dark:text-warning-400'
  return 'text-success-600 dark:text-success-400'
})

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
</script>
