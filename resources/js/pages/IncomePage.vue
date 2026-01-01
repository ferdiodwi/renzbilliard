<template>
  <div>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Pemasukan</h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Riwayat semua pemasukan dari Billiard dan F&B
      </p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Pemasukan</p>
        <p class="text-2xl font-bold text-green-600 dark:text-green-400">
          Rp {{ formatCurrency(stats.total_income) }}
        </p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Pendapatan Billiard</p>
        <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">
          Rp {{ formatCurrency(stats.billiard_income) }}
        </p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Pendapatan F&B</p>
        <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">
          Rp {{ formatCurrency(stats.fnb_income) }}
        </p>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col md:flex-row gap-4 items-center justify-between mb-6">
      <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
        <span>Tampilkan</span>
        <select
          v-model="perPage"
          @change="fetchIncome()"
          class="px-3 py-1.5 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500"
        >
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select>
        <span>baris</span>
      </div>

      <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
        <!-- Type Filter -->
        <select
          v-model="typeFilter"
          @change="fetchIncome()"
          class="px-4 py-2 text-sm border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500"
        >
          <option value="">Semua Tipe</option>
          <option value="billiard">Billiard</option>
          <option value="fnb">F&B</option>
        </select>

        <!-- Search -->
        <div class="relative w-full md:w-64">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            v-model="search"
            type="text"
            placeholder="Cari invoice atau pelanggan..."
            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500"
          />
        </div>
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-200 dark:bg-gray-900">
            <tr>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">No</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Tanggal</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Invoice</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Tipe</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Keterangan</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Metode</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-right text-gray-700 uppercase dark:text-gray-400">Jumlah</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-if="loading">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                <svg class="inline w-6 h-6 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                <p class="mt-2">Memuat data...</p>
              </td>
            </tr>
            <tr v-else-if="incomeList.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                Belum ada data pemasukan
              </td>
            </tr>
            <tr v-else v-for="(item, index) in incomeList" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
              <td class="px-6 py-4 text-sm text-gray-800 dark:text-white">
                {{ (pagination.meta.from || 1) + index }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-800 dark:text-white">
                {{ formatDate(item.date) }}
              </td>
              <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white">
                {{ item.invoice }}
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getTypeClass(item.type)">
                  {{ item.type === 'billiard' ? '🎱 Billiard' : '🍔 F&B' }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-800 dark:text-white">
                {{ item.description || item.customer || '-' }}
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getPaymentClass(item.payment_method)">
                  {{ formatPaymentMethod(item.payment_method) }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                  + Rp {{ formatCurrency(item.amount) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <Pagination 
      v-if="pagination.meta.total > 0"
      :links="pagination.links"
      :meta="pagination.meta"
      @page-change="handlePageChange"
      class="mt-4"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import Pagination from '@/components/ui/Pagination.vue'

const loading = ref(false)
const incomeList = ref([])
const pagination = ref({ links: [], meta: {} })
const stats = ref({ total_income: 0, billiard_income: 0, fnb_income: 0 })
const perPage = ref(10)
const typeFilter = ref('')
const search = ref('')
const currentPage = ref(1)

const fetchIncome = async (page = 1) => {
  loading.value = true
  currentPage.value = page
  try {
    const params = { page, per_page: perPage.value }
    if (typeFilter.value) params.type = typeFilter.value
    if (search.value) params.search = search.value

    const response = await axios.get('/api/income', { params })
    if (response.data.success) {
      incomeList.value = response.data.data.data
      stats.value = response.data.stats
      pagination.value = {
        links: response.data.data.links,
        meta: {
          current_page: response.data.data.current_page,
          last_page: response.data.data.last_page,
          from: response.data.data.from,
          to: response.data.data.to,
          total: response.data.data.total,
        },
      }
    }
  } catch (error) {
    console.error('Failed to fetch income:', error)
  } finally {
    loading.value = false
  }
}

let searchTimeout
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchIncome(1), 500)
})

const handlePageChange = (page) => {
  fetchIncome(page)
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatPaymentMethod = (method) => {
  const methods = { cash: 'Tunai', qris: 'QRIS', transfer: 'Transfer' }
  return methods[method] || method || '-'
}

const getTypeClass = (type) => {
  return type === 'billiard'
    ? 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400'
    : 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400'
}

const getPaymentClass = (method) => {
  const classes = {
    cash: 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
    qris: 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400',
    transfer: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
  }
  return classes[method] || 'bg-gray-100 text-gray-700'
}

onMounted(() => {
  fetchIncome()
})
</script>
