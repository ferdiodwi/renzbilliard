<template>
  <div>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Riwayat Transaksi</h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Lihat semua transaksi pembayaran billiard
      </p>
    </div>

    <!-- Filters & Stats -->
    <div class="grid gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-4">
      <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Transaksi</p>
        <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ stats.total_transactions }}</p>
      </div>
      <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Pendapatan</p>
        <p class="text-2xl font-bold text-success-600 dark:text-success-400">
          Rp {{ formatCurrency(stats.total_revenue) }}
        </p>
      </div>
      <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Tunai</p>
        <p class="text-xl font-bold text-gray-800 dark:text-white">
          Rp {{ formatCurrency(stats.cash_total) }}
        </p>
      </div>
      <div class="p-4 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Non-Tunai</p>
        <p class="text-xl font-bold text-gray-800 dark:text-white">
          Rp {{ formatCurrency(stats.non_cash_total) }}
        </p>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-200 dark:bg-gray-900">
            <tr>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
                No
              </th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
                Invoice
              </th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
                Customer
              </th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
                Tanggal
              </th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
                Kasir
              </th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
                Metode
              </th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-right text-gray-700 uppercase dark:text-gray-400">
                Total
              </th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-center text-gray-700 uppercase dark:text-gray-400">
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-if="loading">
              <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                <svg class="inline w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                <p class="mt-2">Memuat data...</p>
              </td>
            </tr>
            <tr v-else-if="transactions.length === 0">
              <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                Belum ada transaksi
              </td>
            </tr>
            <tr v-else v-for="(transaction, index) in transactions" :key="transaction.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
              <td class="px-6 py-4">
                <p class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ (pagination.meta.from || 1) + index }}
                </p>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm font-medium text-gray-800 dark:text-white">
                  {{ transaction.invoice_number }}
                </p>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-gray-800 dark:text-white">
                  {{ getCustomerName(transaction) }}
                </p>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-gray-800 dark:text-white">
                  {{ formatDate(transaction.paid_at) }}
                </p>
              </td>
              <td class="px-6 py-4">
                <p class="text-sm text-gray-800 dark:text-white">
                  {{ transaction.cashier?.name || '-' }}
                </p>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full" :class="getPaymentMethodClass(transaction.payment_method)">
                  {{ formatPaymentMethod(transaction.payment_method) }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <p class="text-sm font-semibold text-gray-800 dark:text-white">
                  Rp {{ formatCurrency(transaction.total_amount) }}
                </p>
              </td>
              <td class="px-6 py-4 text-center">
                <button
                  @click="viewDetail(transaction)"
                  class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-brand-600 transition rounded-lg hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-500/10"
                >
                  Detail
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <Pagination 
        v-if="pagination.meta.total > 0"
        :links="pagination.links"
        :meta="pagination.meta"
        @page-change="handlePageChange"
        class="mt-4"
    />

    <!-- Transaction Detail Dialog -->
    <TransactionDetailDialog
      :show="showDetail"
      :transaction="selectedTransaction"
      @close="showDetail = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import TransactionDetailDialog from '@/components/TransactionDetailDialog.vue'
import Pagination from '@/components/ui/Pagination.vue'

const loading = ref(false)
const transactions = ref([])
const pagination = ref({ links: [], meta: {} })
const selectedTransaction = ref(null)
const showDetail = ref(false)
const currentPage = ref(1)

const stats = computed(() => {
  return {
    total_transactions: pagination.value.meta.total || transactions.value.length,
    // Note: total revenue here will only be for current page if not handled by dedicated stats API
    // For now we will keep it as is, but be aware it might only show stats for loaded items if we don't have a separate stats endpoint
    total_revenue: transactions.value.reduce((sum, t) => sum + parseFloat(t.total_amount), 0),
    cash_total: transactions.value.filter(t => t.payment_method === 'cash').reduce((sum, t) => sum + parseFloat(t.total_amount), 0),
    non_cash_total: transactions.value.filter(t => t.payment_method !== 'cash').reduce((sum, t) => sum + parseFloat(t.total_amount), 0),
  }
})

const fetchTransactions = async (page = 1) => {
  loading.value = true
  currentPage.value = page
  try {
    const response = await axios.get('/api/transactions', {
        params: { page }
    })
    if (response.data.success) {
      transactions.value = response.data.data.data
      pagination.value = {
        links: response.data.data.links,
        meta: {
            current_page: response.data.data.current_page,
            last_page: response.data.data.last_page,
            from: response.data.data.from,
            to: response.data.data.to,
            total: response.data.data.total
        }
      }
    }
  } catch (error) {
    console.error('Failed to fetch transactions:', error)
  } finally {
    loading.value = false
  }
}

const handlePageChange = (page) => {
    fetchTransactions(page)
}

const viewDetail = (transaction) => {
  selectedTransaction.value = transaction
  showDetail.value = true
}

const formatDate = (isoString) => {
  const date = new Date(isoString)
  return date.toLocaleString('id-ID', { 
    day: '2-digit',
    month: '2-digit', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

const formatPaymentMethod = (method) => {
  const methods = {
    cash: 'Tunai',
    qris: 'QRIS',
    transfer: 'Transfer'
  }
  return methods[method] || method
}

const getPaymentMethodClass = (method) => {
  const classes = {
    cash: 'bg-success-100 text-success-700 dark:bg-success-500/20 dark:text-success-400',
    qris: 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
    transfer: 'bg-blue-light-100 text-blue-light-700 dark:bg-blue-light-500/20 dark:text-blue-light-400'
  }
  return classes[method] || 'bg-gray-100 text-gray-700'
}

const getCustomerName = (transaction) => {
  // Try to get customer name from any item that has session
  const sessionItem = transaction.items?.find(item => item.session?.customer_name)
  if (sessionItem) {
    return sessionItem.session.customer_name
  }
  
  // If we store customer_name on transaction itself in future, check there too
  // if (transaction.customer_name) return transaction.customer_name

  return '-'
}

onMounted(() => {
  fetchTransactions()
})
</script>
