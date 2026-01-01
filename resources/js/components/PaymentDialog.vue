<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @click.self="closeDialog"
  >
    <div class="w-full max-w-md overflow-hidden bg-white shadow-2xl rounded-2xl dark:bg-gray-800">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
            Pembayaran - Meja {{ sessionData?.table_number }}
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
      <div class="px-6 py-4 space-y-4">
        <!-- Invoice Summary -->
        <div v-if="sessionData" class="space-y-4">
          <!-- Session Charges -->
          <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900">
            <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Biaya Billiard</h4>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400"> Meja {{ sessionData.table_number }}</span>
                <span class="font-medium text-gray-800 dark:text-white">
                  Rp {{ formatCurrency(sessionData.session_charges) }}
                </span>
              </div>
            </div>
          </div>

          <!-- F&B Items (if any) -->
          <div v-if="sessionData.fnb_orders && sessionData.fnb_orders.length > 0" class="p-4 rounded-lg bg-gray-50 dark:bg-gray-900">
            <h4 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">F&B Orders</h4>
            <div class="space-y-3 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
              <div v-for="order in sessionData.fnb_orders" :key="order.id" class="space-y-2">
                <div class="text-xs text-gray-500 dark:text-gray-400 sticky top-0 bg-gray-50 dark:bg-gray-900 py-1">
                  Order #{{ order.id }} <!-- Changed from order_number to id for brevity/correctness if needed, sticking to original logical structure but making header sticky -->
                </div>
                <div v-for="item in order.items" :key="item.id" class="flex justify-between text-sm pl-2">
                  <div class="flex-1">
                    <span class="text-gray-800 dark:text-white">{{ item.product?.name }}</span>
                    <span class="ml-2 text-gray-500 dark:text-gray-400">×{{ item.quantity }}</span>
                  </div>
                  <span class="font-medium text-gray-800 dark:text-white">
                    Rp {{ formatCurrency(item.subtotal) }}
                  </span>
                </div>
              </div>
            </div>
             <div class="pt-2 mt-2 border-t border-gray-200 dark:border-gray-700">
                <div class="flex justify-between text-sm font-medium">
                  <span class="text-gray-600 dark:text-gray-400">Total F&B:</span>
                  <span class="text-gray-800 dark:text-white">
                    Rp {{ formatCurrency(sessionData.fnb_charges) }}
                  </span>
                </div>
              </div>
          </div>

          <!-- Grand Total -->
          <div class="p-4 rounded-lg bg-brand-50 dark:bg-brand-500/10 border-2 border-brand-500">
            <div class="flex justify-between items-center">
              <span class="text-lg font-bold text-gray-800 dark:text-white">TOTAL BAYAR</span>
              <span class="text-2xl font-bold text-brand-600 dark:text-brand-400">
                Rp {{ formatCurrency(sessionData.total_charges) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Payment Method Selection -->
        <div>
          <label class="block mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
            Metode Pembayaran<span class="text-red-500">*</span>
          </label>
          <div class="grid grid-cols-2 gap-3">
            <button
              v-for="method in paymentMethods"
              :key="method.value"
              type="button"
              @click="selectedMethod = method.value"
              class="flex flex-col items-center gap-2 p-4 transition border-2 rounded-xl"
              :class="selectedMethod === method.value
                ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10'
                : 'border-gray-300 bg-white hover:border-gray-400 dark:bg-gray-800 dark:border-gray-700 dark:hover:border-gray-600'"
            >
              <span class="text-2xl">{{ method.icon }}</span>
              <span class="text-xs font-medium text-gray-800 dark:text-white">
                {{ method.label }}
              </span>
            </button>
          </div>
        </div>

        <!-- Cash Input (if payment method is cash) -->
        <div v-if="selectedMethod === 'cash'" class="space-y-3">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Uang Dibayar
            </label>
            <input
              v-model.number="cashPaid"
              type="number"
              :min="sessionData?.total_charges"
              step="1000"
              placeholder="Masukkan jumlah uang"
              class="w-full px-4 py-3 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
          </div>
          <div v-if="cashPaid >= (sessionData?.total_charges || 0)" class="p-3 rounded-lg bg-success-50 dark:bg-success-500/10">
            <div class="flex justify-between text-sm">
              <span class="font-medium text-gray-700 dark:text-gray-300">Kembalian</span>
              <span class="font-bold text-success-600 dark:text-success-400">
                Rp {{ formatCurrency(cashPaid - (sessionData?.total_charges || 0)) }}
              </span>
            </div>
          </div>
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
            @click="handlePayment"
            :disabled="loading || !selectedMethod || (selectedMethod === 'cash' && cashPaid < (sessionData?.total_charges || 0))"
            class="flex-1 px-4 py-3 text-sm font-semibold text-white transition rounded-lg bg-success-500 hover:bg-success-600 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Memproses...' : 'Bayar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'
import { useOrderStore } from '@/stores/order'

const notify = useNotificationStore()
const orderStore = useOrderStore()

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  sessionData: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'success'])

const loading = ref(false)
const selectedMethod = ref('cash')
const cashPaid = ref(0)

const paymentMethods = [
  { value: 'cash', label: 'Tunai', icon: '💵' },
  { value: 'qris', label: 'QRIS', icon: '📱' },
]

const handlePayment = async () => {
  if (!props.sessionData || !selectedMethod.value) return

  loading.value = true
  try {
    let sessionIdToPay = props.sessionData.session_id

    // NEW: If this is a create-and-pay flow, create session first!
    if (props.sessionData.is_create_session) {
        const startResponse = await axios.post('/api/sessions/start', props.sessionData.create_payload)
        if (startResponse.data.success) {
            sessionIdToPay = startResponse.data.data.id
        } else {
            throw new Error('Gagal membuat sesi')
        }
    } else {
        // Step 1: Stop the session (Always call stop to finish the session)
        const stopResponse = await axios.post(`/api/sessions/${props.sessionData.session_id}/stop`)
        
        if (!stopResponse.data.success) {
          throw new Error('Gagal menghentikan sesi')
        }
    }

    // Step 2: Create transaction
    if (props.sessionData.total_charges > 0) {
        const response = await axios.post('/api/transactions', {
          session_ids: [sessionIdToPay],
          payment_method: selectedMethod.value,
        })
    
        if (response.data.success) {
          const change = selectedMethod.value === 'cash' ? cashPaid.value - props.sessionData.total_charges : 0
          
          notify.success(`Pembayaran berhasil! Invoice: ${response.data.data.invoice_number}`)
          orderStore.fetchPendingCount() // Update badge count
          if (change > 0) {
            notify.info(`Kembalian: Rp ${formatCurrency(change)}`, 'Kembalian', 5000)
          }
          
          emit('success', response.data.data)
          closeDialog()
        }
    } else {
        // Zero charges (Prepaid), just finish
        notify.success('Sesi berhasil dihentikan (Sudah Lunas)')
        emit('success', null)
        closeDialog()
    }
  } catch (error) {
    const message = error.response?.data?.message || error.message || 'Gagal memproses pembayaran'
    notify.error(message)
  } finally {
    loading.value = false
  }
}

const closeDialog = () => {
  selectedMethod.value = 'cash'
  cashPaid.value = 0
  emit('close')
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

watch(() => props.sessionData, (newData) => {
  if (newData) {
    cashPaid.value = null // Kosong, tampilkan placeholder
  }
}, { immediate: true })
</script>
