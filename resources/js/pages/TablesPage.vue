<template>
  <div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Meja Biliar</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Monitoring status meja dan kelola sesi bermain
        </p>
      </div>
      <button
        v-if="isAdmin"
        @click="showAddTable = true"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 4v16m8-8H4"
          />
        </svg>
        Tambah Meja
      </button>
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

    <!-- Tables Grid -->
    <div v-else-if="tables.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 auto-rows-fr">
      <TableCard
        v-for="table in tables"
        :key="table.id"
        :table="table"
        :show-admin-actions="isAdmin"
        @start="handleStartSession"
        @stop="handleStopSession"
        @extend="handleExtendSession"
        @edit="handleEditTable"
        @delete="handleDeleteTable"
        @order="handleOrderFnB"
      />
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <div class="inline-flex items-center justify-center w-16 h-16 mb-4 rounded-full bg-gray-100 dark:bg-gray-800">
        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
          />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 dark:text-white">Belum ada meja</h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Mulai dengan menambahkan meja billiard baru
      </p>
      <button
        v-if="isAdmin"
        @click="showAddTable = true"
        class="inline-flex items-center gap-2 px-4 py-2 mt-4 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600"
      >
        Tambah Meja
      </button>
    </div>

    <!-- Dialogs -->
    <StartSessionDialog
      :show="showStartDialog"
      :table="selectedTable"
      @close="showStartDialog = false"
      @success="handleSessionStarted"
    />

    <!-- Extend Session Dialog -->
    <ExtendSessionDialog
      :show="showExtendDialog"
      :session="selectedSessionForExtend"
      @close="showExtendDialog = false"
      @success="handleExtendSuccess"
    />

    <!-- Add/Edit Table Dialog -->
    <TableDataDialog
      v-if="isAdmin"
      :show="showAddTable || showEditTable"
      :table="editingTable"
      @close="closeTableDialog"
      @success="handleTableSaved"
    />

    <PaymentDialog
      :show="showPaymentDialog"
      :sessionData="paymentData"
      @close="showPaymentDialog = false"
      @success="handlePaymentSuccess"
    />

    <!-- Order F&B Dialog -->
    <OrderFnBDialog
      :show="showOrderDialog"
      :session="selectedSessionForOrder"
      @close="showOrderDialog = false"
      @success="handleOrderSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useNotificationStore } from '@/stores/notification'
import { useConfirmStore } from '@/stores/confirm'
import axios from 'axios'
import TableCard from '@/components/TableCard.vue'
import StartSessionDialog from '@/components/StartSessionDialog.vue'
import ExtendSessionDialog from '@/components/ExtendSessionDialog.vue'
import TableDataDialog from '@/components/TableDataDialog.vue'

import PaymentDialog from '@/components/PaymentDialog.vue'
import OrderFnBDialog from '@/components/OrderFnBDialog.vue'
import { stopExpiredSessionSound } from '@/utils/audio'

const authStore = useAuthStore()
const notify = useNotificationStore()
const confirm = useConfirmStore()
const isAdmin = computed(() => authStore.isAdmin)

const availableTables = computed(() => 
  tables.value.filter(t => t.status === 'available').length
)

const playingTables = computed(() => 
  tables.value.filter(t => t.status === 'playing').length
)

const loading = ref(false)
const tables = ref([])
const selectedTable = ref(null)
const editingTable = ref(null)
const showStartDialog = ref(false)
const showExtendDialog = ref(false)
const showAddTable = ref(false)
const showEditTable = ref(false)
const showPaymentDialog = ref(false)
const showOrderDialog = ref(false)
const selectedSessionForOrder = ref(null)
const selectedSessionForExtend = ref(null)
const paymentData = ref(null)

let refreshInterval = null
let countdownInterval = null

const fetchTables = async () => {
  try {
    const response = await axios.get('/api/tables')
    if (response.data.success) {
      tables.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch tables:', error)
  } finally {
    loading.value = false
  }
}

const handleStartSession = (table) => {
  selectedTable.value = table
  showStartDialog.value = true
}

const handleSessionStarted = () => {
  fetchTables()
}

const handleOrderFnB = (table) => {
  selectedSessionForOrder.value = table.active_session
  showOrderDialog.value = true
}

const handleOrderSuccess = () => {
    // maybe added some logic here
}

const handleStopSession = async (table) => {
  const confirmed = await confirm.show({
      title: 'Stop & Bayar',
      message: 'Lanjut ke pembayaran untuk meja ini?',
      confirmText: 'Lanjut Bayar',
      type: 'info'
  })
  
  if (!confirmed) return
  
  // Stop any playing expired session alert AFTER user confirms payment
  stopExpiredSessionSound()
  
  // Mark this session as acknowledged so alerts won't play again
  const acknowledgedSessions = JSON.parse(localStorage.getItem('acknowledgedExpiredSessions') || '[]')
  const sessionId = table.active_session.id
  if (!acknowledgedSessions.includes(sessionId)) {
    acknowledgedSessions.push(sessionId)
    localStorage.setItem('acknowledgedExpiredSessions', JSON.stringify(acknowledgedSessions))
  }

  try {
    const session = table.active_session
    
    // Calculate current charges without stopping session
    const now = new Date()
    const scheduledEndTime = session.end_time ? new Date(session.end_time) : null
    const effectiveEndTime = (scheduledEndTime && now > scheduledEndTime) ? scheduledEndTime : now
    
    const startTime = new Date(session.start_time)
    const actualDuration = Math.floor((effectiveEndTime - startTime) / (1000 * 60)) // minutes
    
    // Calculate session charges based on rate
    const sessionCharges = Math.ceil((actualDuration / 60) * session.rate.price_per_hour)
    
    // Get F&B orders linked to this session
    let fnbOrders = []
    let fnbTotal = 0
    try {
        const ordersResponse = await axios.get(`/api/sessions/${session.id}/order`)
        if (ordersResponse.data.data) {
            fnbOrders = [ordersResponse.data.data] // Wrapping in array as API returns single order object or null
            fnbTotal = ordersResponse.data.data.total || 0
        }
    } catch (e) {
        console.log('No active orders found or failed to fetch', e)
    }

    // Ensure fnbTotal is a number
    fnbTotal = Number(fnbTotal)
    
    // Prepare payment data for dialog
    paymentData.value = {
      session_id: session.id,
      table_number: table.table_number,
      session_charges: sessionCharges,
      fnb_charges: fnbTotal,
      total_charges: sessionCharges + fnbTotal,
      fnb_orders: fnbOrders,
    }
    
    // Show payment dialog (session masih aktif)
    showPaymentDialog.value = true
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal memuat data pembayaran')
  }
}

const handlePaymentSuccess = (transaction) => {
  // Session already stopped in PaymentDialog before payment
  showPaymentDialog.value = false
  
  // Clean up acknowledged session from localStorage since it's now paid
  if (paymentData.value) {
    const acknowledgedSessions = JSON.parse(localStorage.getItem('acknowledgedExpiredSessions') || '[]')
    const filtered = acknowledgedSessions.filter(id => id !== paymentData.value.session_id)
    localStorage.setItem('acknowledgedExpiredSessions', JSON.stringify(filtered))
  }
  paymentData.value = null
  fetchTables()
}

const handleExtendSession = (table) => {
  selectedSessionForExtend.value = table.active_session
  showExtendDialog.value = true
}

const handleExtendSuccess = () => {
    fetchTables()
}

// extendSession function removed as it is handled by the dialog now

const handleEditTable = (table) => {
  editingTable.value = table
  showEditTable.value = true
}

const handleDeleteTable = async (table) => {
  const confirmed = await confirm.show({
    title: 'Hapus Meja',
    message: `Yakin ingin menghapus Meja ${table.table_number}?`,
    confirmText: 'Hapus',
    type: 'danger'
  })

  if (!confirmed) return

  try {
    const response = await axios.delete(`/api/tables/${table.id}`)
    if (response.data.success) {
      notify.success('Meja berhasil dihapus')
      fetchTables()
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal menghapus meja')
  }
}

const closeTableDialog = () => {
  showAddTable.value = false
  showEditTable.value = false
  editingTable.value = null
}

const handleTableSaved = () => {
  closeTableDialog()
  fetchTables()
}

const updateCountdown = () => {
  tables.value = tables.value.map((table) => {
    if (table.active_session && table.active_session.remaining_seconds > 0) {
      return {
        ...table,
        active_session: {
          ...table.active_session,
          remaining_seconds: table.active_session.remaining_seconds - 1
        }
      }
    }
    return table
  })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

onMounted(() => {
  loading.value = true
  fetchTables()

  // Auto refresh every 5 seconds to update data
  refreshInterval = setInterval(fetchTables, 5000)
  
  // Update countdown every second for real-time display
  countdownInterval = setInterval(updateCountdown, 1000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
})
</script>
