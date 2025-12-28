<template>
  <div class="relative" ref="containerRef">
    <!-- Bell Icon Button -->
    <button
      @click.stop="toggleDropdown"
      class="relative p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-brand-500"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      
      <!-- Badge Counter -->
      <span
        v-if="unreadCount > 0"
        class="absolute top-1 right-1 flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full animate-pulse"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown Panel -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-80 md:w-96 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
    >
      <!-- Header -->
      <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-900">
        <h3 class="font-semibold text-gray-900 dark:text-white">Notifikasi</h3>
        <button
          v-if="notifications.length > 0"
          @click="markAllAsRead"
          class="text-xs text-brand-600 hover:text-brand-700 dark:text-brand-400 font-medium"
        >
          Tandai semua dibaca
        </button>
      </div>

      <!-- Notification List -->
      <div class="max-h-96 overflow-y-auto">
        <div v-if="notifications.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
          <svg class="w-12 h-12 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
          </svg>
          <p class="text-sm">Tidak ada notifikasi</p>
        </div>

        <div
          v-for="notification in notifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-900 cursor-pointer border-b border-gray-100 dark:border-gray-700 last:border-0 transition-colors"
          :class="{ 'bg-brand-50/30 dark:bg-brand-900/10': !notification.read }"
        >
          <div class="flex gap-3">
            <!-- Icon -->
            <div class="shrink-0">
              <div
                class="w-10 h-10 rounded-full flex items-center justify-center"
                :class="notification.type === 'session_expired' || notification.type === 'session_expiring'
                  ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' 
                  : 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400'"
              >
                <svg v-if="notification.type === 'session_expired' || notification.type === 'session_expiring'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
              </div>
            </div>
            
            <!-- Content -->
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 dark:text-white" v-html="notification.title"></p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ notification.message }}</p>
              <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ formatTime(notification.timestamp) }}</p>
            </div>

            <!-- Unread Indicator -->
            <div v-if="!notification.read" class="shrink-0">
              <div class="w-2 h-2 bg-brand-500 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div v-if="notifications.length > 0" class="px-4 py-3 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700">
        <button
          @click="clearAll"
          class="w-full text-center text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
        >
          Hapus semua notifikasi
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const containerRef = ref(null)
const isOpen = ref(false)
const notifications = ref([])

const unreadCount = computed(() => 
  notifications.value.filter(n => !n.read).length
)

const toggleDropdown = () => {
  isOpen.value = !isOpen.value
}

const closeDropdown = () => {
  isOpen.value = false
}

const handleNotificationClick = (notification) => {
  // Mark as read
  notification.read = true
  saveNotifications()

  // Handle action based on type
  if ((notification.type === 'session_expired' || notification.type === 'session_expiring') && notification.tableId) {
    router.push('/tables')
  } else if (notification.type === 'new_transaction') {
    router.push('/transactions')
  }

  closeDropdown()
}

const markAllAsRead = () => {
  notifications.value.forEach(n => n.read = true)
  saveNotifications()
}

const clearAll = () => {
  notifications.value = []
  saveNotifications()
}

const formatTime = (timestamp) => {
  const now = new Date()
  const time = new Date(timestamp)
  const diff = Math.floor((now - time) / 1000) // seconds

  if (diff < 60) return 'Baru saja'
  if (diff < 3600) return `${Math.floor(diff / 60)} menit yang lalu`
  if (diff < 86400) return `${Math.floor(diff / 3600)} jam yang lalu`
  return time.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' })
}

// Add notification from external
const addNotification = (notification) => {
  const newNotif = {
    id: Date.now() + Math.random(),
    ...notification,
    timestamp: new Date().toISOString(),
    read: false
  }
  
  notifications.value.unshift(newNotif)
  
  // Keep only last 20 notifications
  if (notifications.value.length > 20) {
    notifications.value = notifications.value.slice(0, 20)
  }
  
  saveNotifications()
}

const saveNotifications = () => {
  localStorage.setItem('appNotifications', JSON.stringify(notifications.value))
}

const loadNotifications = () => {
  const saved = localStorage.getItem('appNotifications')
  if (saved) {
    notifications.value = JSON.parse(saved)
  }
}

// Listen for notification events
const handleNotificationEvent = (event) => {
  addNotification(event.detail)
}

// Handle click outside
const handleClickOutside = (event) => {
  if (containerRef.value && !containerRef.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  loadNotifications()
  window.addEventListener('app-notification', handleNotificationEvent)
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  window.removeEventListener('app-notification', handleNotificationEvent)
  document.removeEventListener('click', handleClickOutside)
})

defineExpose({ addNotification })
</script>
