<template>
  <div class="min-h-screen xl:flex bg-white dark:bg-gray-950">
    <app-sidebar />
    <Backdrop />
    <div
      class="flex-1 transition-all duration-300 ease-in-out bg-gray-50 dark:bg-gray-950"
      :class="[isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]']"
    >
      <app-header ref="appHeader" />
      <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import AppSidebar from './AppSidebar.vue'
import AppHeader from './AppHeader.vue'
import { useSidebar } from '@/composables/useSidebar'
import Backdrop from './Backdrop.vue'
import { onMounted, onUnmounted, ref } from 'vue'
import axios from 'axios'
import { playAlertSound, playTransactionSound, playExpiredSessionSound, stopExpiredSessionSound } from '@/utils/audio'
import { useNotificationStore } from '@/stores/notification'
import { useSessionAlerts } from '@/composables/useSessionAlerts'

const { isExpanded, isHovered } = useSidebar()
const notify = useNotificationStore()
const pollingInterval = ref(null)
const lastTransactionId = ref(0)
const appHeader = ref(null)
const isFirstLoad = ref(true)

// Use client-side session alerts
const sessionAlerts = useSessionAlerts()

// Check settings
const getSettings = () => {
  const savedSettings = localStorage.getItem('userSettings')
  return savedSettings 
    ? JSON.parse(savedSettings) 
    : { notifications: { sessionAlerts: true, transactionAlerts: true } }
}

// Handle session alerts (triggered from client-side calculation)
const handleSessionAlerts = (alerts) => {
  const settings = getSettings()
  if (!settings.notifications.sessionAlerts) return

  alerts.forEach(alert => {
    const { session, isExpired, remaining } = alert

    if (isExpired) {
      // Session expired - long urgent beep
      playExpiredSessionSound()
      notify.error(`⏰ WAKTU HABIS - Meja ${session.table_number}!`)
      
      // Dispatch to notification dropdown
      window.dispatchEvent(new CustomEvent('app-notification', {
        detail: {
          type: 'session_expired',
          title: `⏰ <strong>WAKTU HABIS</strong> - Meja ${session.table_number}`,
          message: `Session telah berakhir`,
          tableId: session.table_id
        }
      }))
    } else {
      // Session expiring soon - single warning beep
      playAlertSound()
      notify.warning(`⚠️ Meja ${session.table_number} - Sisa ${remaining} menit`)
      
      // Dispatch to notification dropdown
      window.dispatchEvent(new CustomEvent('app-notification', {
        detail: {
          type: 'session_expiring',
          title: `⚠️ Meja ${session.table_number}`,
          message: `Sisa ${remaining} menit lagi`,
          tableId: session.table_id
        }
      }))
    }
  })
}

// Fetch tables data and update session alerts
const fetchTablesForAlerts = async () => {
  try {
    const response = await axios.get('/api/tables')
    if (response.data.success) {
      sessionAlerts.updateSessions(response.data.data)
    }
  } catch (error) {
    console.error('Failed to fetch tables for alerts:', error)
  }
}

// Check for new transactions only
const checkTransactions = async () => {
  const settings = getSettings()
  if (!settings.notifications.transactionAlerts) return

  try {
    const response = await axios.get('/api/alerts/check')
    if (response.data.success) {
      const { latest_transaction_id } = response.data.data

      if (latest_transaction_id > 0 && latest_transaction_id > lastTransactionId.value) {
        // Don't trigger notification on first load, just set the baseline
        if (!isFirstLoad.value) {
          playTransactionSound()
          notify.info('New transaction received!')
          
          // Dispatch to notification dropdown
          window.dispatchEvent(new CustomEvent('app-notification', {
            detail: {
              type: 'new_transaction',
              title: '💰 Transaksi Baru',
              message: `Transaksi baru telah diterima`
            }
          }))
        }
      }
      
      lastTransactionId.value = latest_transaction_id
      
      // Mark first load as complete
      if (isFirstLoad.value) {
        isFirstLoad.value = false
      }
    }
  } catch (error) {
    console.error('Transaction check failed:', error)
  }
}

onMounted(() => {
  // Initial fetch
  fetchTablesForAlerts()
  
  // Do initial transaction check (silent, just to set baseline)
  checkTransactions()
  
  // Start client-side session alert checking (every 1 second)
  sessionAlerts.startChecking(handleSessionAlerts)
  
  // Refresh tables data every 10 seconds for session updates (faster alerts)
  setInterval(fetchTablesForAlerts, 10000)
  
  // Check transactions every 60 seconds (first check already done above)
  pollingInterval.value = setInterval(checkTransactions, 60000)
})

onUnmounted(() => {
  if (pollingInterval.value) clearInterval(pollingInterval.value)
  sessionAlerts.stopChecking()
  stopExpiredSessionSound()
})
</script>
