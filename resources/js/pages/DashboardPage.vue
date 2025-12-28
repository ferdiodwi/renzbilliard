<template>
  <div>
    <!-- Stats Grid -->
    <div class="grid gap-6 mb-6 sm:grid-cols-2 lg:grid-cols-4">
      <div class="p-6 bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Meja</p>
            <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">{{ stats.total_tables }}</p>
            <p class="mt-1 text-xs text-success-600">{{ stats.available_tables }} tersedia</p>
          </div>
          <div class="p-3 rounded-full bg-brand-100 dark:bg-brand-500/20">
            <svg class="w-8 h-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="p-6 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Sesi Aktif</p>
            <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">{{ stats.active_sessions }}</p>
            <p class="mt-1 text-xs text-gray-500">sedang bermain</p>
          </div>
          <div class="p-3 rounded-full bg-red-100 dark:bg-red-500/20">
            <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="p-6 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Transaksi Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">{{ stats.today_transactions }}</p>
            <p class="mt-1 text-xs text-gray-500">pembayaran</p>
          </div>
          <div class="p-3 rounded-full bg-blue-light-100 dark:bg-blue-light-500/20">
            <svg class="w-8 h-8 text-blue-light-600 dark:text-blue-light-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
        </div>
      </div>

      <div class="p-6 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pendapatan Hari Ini</p>
            <p class="mt-2 text-3xl font-bold text-success-600 dark:text-success-400">
              Rp {{ formatCurrency(stats.today_revenue) }}
            </p>
            <p class="mt-1 text-xs text-gray-500">{{ stats.today_transactions }} transaksi</p>
          </div>
          <div class="p-3 rounded-full bg-success-100 dark:bg-success-500/20">
            <svg class="w-8 h-8 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Revenue Chart -->
    <div class="p-6 mb-6 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Trend Pendapatan</h2>
        <select 
          v-model="chartPeriod" 
          @change="fetchChartData"
          class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        >
          <option value="week">Minggu Ini</option>
          <option value="month">Bulan Ini</option>
          <option value="year">Tahun Ini</option>
        </select>
      </div>
      <div v-if="chartSeries.length > 0" class="w-full h-80">
        <apexchart 
          type="area" 
          height="100%" 
          :options="chartOptions" 
          :series="chartSeries" 
        />
      </div>
      <div v-else class="flex items-center justify-center h-80 text-gray-500">
        Memuat grafik...
      </div>
    </div>

    <!-- Recent Transactions -->
    <div v-if="recentTransactions.length > 0" class="mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Transaksi Terbaru</h2>
        <router-link to="/transactions" class="text-sm font-medium text-brand-600 hover:text-brand-700">
          Lihat Semua →
        </router-link>
      </div>
      <div class="overflow-hidden bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <table class="w-full">
          <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
              <th class="px-4 py-3 text-xs font-medium text-left text-gray-500">Invoice</th>
              <th class="px-4 py-3 text-xs font-medium text-left text-gray-500">Waktu</th>
              <th class="px-4 py-3 text-xs font-medium text-right text-gray-500">Total</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="tx in recentTransactions" :key="tx.id">
              <td class="px-4 py-3 text-sm text-gray-800 dark:text-white">{{ tx.invoice_number }}</td>
              <td class="px-4 py-3 text-sm text-gray-500">{{ formatTime(tx.paid_at) }}</td>
              <td class="px-4 py-3 text-sm font-semibold text-right text-gray-800 dark:text-white">
                Rp {{ formatCurrency(tx.total_amount) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import VueApexCharts from 'vue3-apexcharts'

const stats = ref({
  total_tables: 0,
  available_tables: 0,
  active_sessions: 0,
  today_transactions: 0,
  today_revenue: 0,
})

const recentTransactions = ref([])
const chartPeriod = ref('week')
const chartData = ref([])

// Chart Configuration
const chartSeries = computed(() => [{
  name: 'Pendapatan',
  data: chartData.value.map(d => d.revenue)
}])

const chartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif',
    background: 'transparent',
  },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  xaxis: {
    categories: chartData.value.map(d => d.label),
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: {
      rotate: -45,
      rotateAlways: true,
      hideOverlappingLabels: false,
      trim: false
    }
  },
  yaxis: {
    labels: {
      formatter: (value) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value)
    }
  },
  colors: ['#0ea5e9'], // Brand color
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0.05,
      stops: [0, 90, 100]
    }
  },
  grid: {
    borderColor: '#f1f5f9',
    strokeDashArray: 4,
    yaxis: { lines: { show: true } } 
  },
  theme: { mode: 'light' }, // Can be dynamic based on dark mode
  tooltip: {
    y: {
      formatter: (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value)
    }
  }
}))

const fetchChartData = async () => {
    try {
        const response = await axios.get('/api/dashboard/chart', {
            params: { period: chartPeriod.value }
        })
        if (response.data.success) {
            chartData.value = response.data.data
        }
    } catch (error) {
        console.error('Failed to fetch chart data:', error)
    }
}

const fetchDashboardStats = async () => {
  try {
    const [tablesRes, sessionsRes, transactionsRes] = await Promise.all([
      axios.get('/api/tables'),
      axios.get('/api/sessions/active'),
      axios.get('/api/transactions'),
    ])

    if (tablesRes.data.success) {
      const tables = tablesRes.data.data
      stats.value.total_tables = tables.length
      stats.value.available_tables = tables.filter(t => t.status === 'available').length
    }

    if (sessionsRes.data.success) {
      stats.value.active_sessions = sessionsRes.data.data.length
    }

    if (transactionsRes.data.success) {
      const transactions = transactionsRes.data.data.data || transactionsRes.data.data
      const today = new Date().toISOString().split('T')[0]
      const todayTx = transactions.filter(tx => tx.paid_at.startsWith(today))
      
      stats.value.today_transactions = todayTx.length
      stats.value.today_revenue = todayTx.reduce((sum, tx) => sum + parseFloat(tx.total_amount), 0)
      
      recentTransactions.value = transactions.slice(0, 5)
    }
  } catch (error) {
    console.error('Failed to fetch dashboard stats:', error)
  }
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

const formatTime = (isoString) => {
  const date = new Date(isoString)
  return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchDashboardStats()
  fetchChartData()
})
</script>
