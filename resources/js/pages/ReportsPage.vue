<template>
  <div>
    <div class="flex flex-col justify-between mb-6 md:flex-row md:items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Laporan</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Analisis pendapatan dan statistik bisnis
        </p>
      </div>
      
      <!-- Actions -->
      <div class="flex flex-col gap-4 mt-4 md:flex-row md:items-center md:mt-0">
        <!-- Date Filter -->
        <div class="flex items-center p-1 bg-white border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600">
            <input 
              type="date" 
              v-model="filters.start_date"
              class="px-3 py-1.5 text-sm bg-transparent border-none focus:ring-0 dark:text-white cursor-pointer"
              onclick="this.showPicker()"
            >
            <span class="text-gray-400 dark:text-gray-500">-</span>
            <input 
              type="date" 
              v-model="filters.end_date"
              class="px-3 py-1.5 text-sm bg-transparent border-none focus:ring-0 dark:text-white cursor-pointer"
              onclick="this.showPicker()"
            >
        </div>
        
        <button 
          @click="fetchReports"
          class="px-4 py-2 text-sm font-medium text-white transition rounded-lg bg-brand-600 hover:bg-brand-700 shadow-sm"
        >
          Terapkan
        </button>

        <div class="flex items-center gap-2 border-l border-gray-300 pl-4 dark:border-gray-600">
             <button 
                @click="downloadExport('pdf')"
                :disabled="isExporting"
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 transition bg-white border border-red-200 rounded-lg hover:bg-red-50 dark:bg-gray-800 dark:border-red-900/30 dark:text-red-400 disabled:opacity-50"
            >
                <svg v-if="!isExporting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                PDF
            </button>
             <button 
                @click="downloadExport('excel')"
                :disabled="isExporting"
                class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-green-600 transition bg-white border border-green-200 rounded-lg hover:bg-green-50 dark:bg-gray-800 dark:border-green-900/30 dark:text-green-400 disabled:opacity-50"
            >
                <svg v-if="!isExporting" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <svg v-else class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Excel
            </button>
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid gap-6 mb-6 sm:grid-cols-3">
      <div class="p-6 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pendapatan</p>
        <p class="mt-2 text-3xl font-bold text-success-600 dark:text-success-400">
          Rp {{ formatCurrency(stats.revenue) }}
        </p>
      </div>
      <div class="p-6 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sesi</p>
        <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">{{ stats.sessions }}</p>
      </div>
      <div class="p-6 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Transaksi</p>
        <p class="mt-2 text-3xl font-bold text-gray-800 dark:text-white">{{ stats.transactions }}</p>
      </div>
    </div>

    <!-- Charts Row -->
    <div class="grid gap-6 mb-6 lg:grid-cols-2">
         <!-- Revenue Breakdown -->
        <div class="p-6 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Sumber Pendapatan</h2>
            <div class="h-64">
                <apexchart type="donut" height="100%" :options="pieOptions" :series="pieSeries" />
            </div>
        </div>
        
         <!-- Payment Methods -->
        <div class="p-6 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
            <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Metode Pembayaran</h2>
            <div class="h-64">
                 <apexchart type="bar" height="100%" :options="barOptions" :series="barSeries" />
            </div>
        </div>
    </div>

    <!-- Revenue Trend Chart -->
    <div class="p-6 mb-6 bg-white border border-gray-200 rounded-xl dark:bg-gray-800 dark:border-gray-700">
      <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white">Trend Pendapatan Harian</h2>
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

    <!-- Transactions Table -->
    <div class="overflow-hidden bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Rincian Transaksi</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-gray-200 dark:bg-gray-900">
            <tr>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">No</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Tanggal</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Invoice</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Kasir</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Metode</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-right text-gray-700 uppercase dark:text-gray-400">Jumlah</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="(tx, index) in transactions" :key="tx.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
              <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white whitespace-nowrap">
                {{ (pagination.meta.from || 1) + index }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-800 dark:text-white whitespace-nowrap">
                {{ formatDateTime(tx.paid_at) }}
              </td>
              <td class="px-6 py-4 text-sm font-medium text-gray-800 dark:text-white whitespace-nowrap">
                {{ tx.invoice_number }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">
                {{ tx.cashier ? tx.cashier.name : '-' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-800 dark:text-white whitespace-nowrap capitalize">
                 <span class="px-2 py-1 text-xs rounded-full bg-gray-100 dark:bg-gray-700">
                    {{ tx.payment_method }}
                 </span>
              </td>
              <td class="px-6 py-4 text-sm font-bold text-right text-success-600 dark:text-success-400 whitespace-nowrap">
                Rp {{ formatCurrency(tx.total_amount) }}
              </td>
            </tr>
            <tr v-if="transactions.length === 0">
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    Tidak ada data transaksi pada periode ini.
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
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import VueApexCharts from 'vue3-apexcharts'

// State
import Pagination from '@/components/ui/Pagination.vue'

const filters = ref({
  start_date: new Date().toISOString().split('T')[0], // Today
  end_date: new Date().toISOString().split('T')[0]
})

// Set default to start of month
const today = new Date()
const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
filters.value.start_date = startOfMonth.toISOString().split('T')[0]

const stats = ref({
  revenue: 0,
  sessions: 0,
  transactions: 0
})

const transactions = ref([])
const pagination = ref({ links: [], meta: {} })
const chartData = ref([])
const breakdown = ref({ billiard: 0, fnb: 0 })
const paymentMethods = ref([])
const currentPage = ref(1)
// --- Charts Config ---

// 1. Revenue Breakdown (Pie/Donut)
const pieSeries = computed(() => [breakdown.value.billiard, breakdown.value.fnb])
const pieOptions = computed(() => ({
    labels: ['Sewa Meja', 'F&B/Produk'],
    colors: ['#0ea5e9', '#f59e0b'],
    legend: { position: 'bottom' },
    dataLabels: { enabled: true },
    plotOptions: { pie: { donut: { size: '60%' } } }
}))

// 2. Payment Methods (Bar)
const barSeries = computed(() => [{
    name: 'Total',
    data: paymentMethods.value.map(p => p.total)
}])
const barOptions = computed(() => ({
    chart: { type: 'bar', toolbar: { show: false } },
    plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '50%' } },
    dataLabels: { enabled: true, formatter: (val) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(val) },
    xaxis: { categories: paymentMethods.value.map(p => p.payment_method), labels: { show: false } },
    colors: ['#22c55e']
}))

// 3. Trend (Area)
const chartSeries = computed(() => [{
  name: 'Pendapatan',
  data: chartData.value.map(d => ({
    x: d.date,
    y: d.revenue
  }))
}])

const chartOptions = computed(() => ({
  chart: {
    type: 'area',
    toolbar: { show: false },
    fontFamily: 'Inter, sans-serif',
    background: 'transparent',
    zoom: { enabled: false }
  },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  xaxis: {
    type: 'datetime',
    tooltip: { enabled: false },
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: {
        datetimeFormatter: {
            year: 'yyyy',
            month: 'MMM \'yy',
            day: 'dd MMM',
        },
        style: { colors: '#64748b' }
    }
  },
  yaxis: {
    labels: {
      formatter: (value) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value),
      style: { colors: '#64748b' }
    }
  },
  colors: ['#0ea5e9'],
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
  theme: { mode: 'light' },
  tooltip: {
    y: { formatter: (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value) },
    x: { format: 'dd MMM yyyy' }
  }
}))

// Methods
const fetchReports = async (page = 1) => {
  currentPage.value = page
  try {
    const response = await axios.get('/api/reports', {
      params: {
        start_date: filters.value.start_date,
        end_date: filters.value.end_date,
        page: page
      }
    })

    if (response.data.success) {
      const data = response.data.data
      stats.value = data.summary
      chartData.value = data.chart
      transactions.value = data.transactions.data
      pagination.value = {
        links: data.transactions.links,
        meta: {
            current_page: data.transactions.current_page,
            last_page: data.transactions.last_page,
            from: data.transactions.from,
            to: data.transactions.to,
            total: data.transactions.total
        }
      }
      
      // New data
      breakdown.value = data.breakdown || { billiard: 0, fnb: 0 }
      paymentMethods.value = data.payment_methods || []
    }
  } catch (error) {
    console.error('Failed to fetch reports:', error)
  }
}

const handlePageChange = (page) => {
    fetchReports(page)
}

const isExporting = ref(false)

const downloadExport = async (type) => {
    isExporting.value = true
    try {
        const response = await axios.get('/api/reports/export', {
            params: {
                start_date: filters.value.start_date,
                end_date: filters.value.end_date,
                type: type
            },
            responseType: 'blob' // Important
        })

        // Create download link
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        
        // Try to get filename from headers
        const contentDisposition = response.headers['content-disposition']
        let fileName = `Laporan.${type === 'excel' ? 'csv' : 'pdf'}`
        if (contentDisposition) {
            const fileNameMatch = contentDisposition.match(/filename="?([^"]+)"?/)
            if (fileNameMatch && fileNameMatch.length === 2)
                fileName = fileNameMatch[1]
        }
        
        link.setAttribute('download', fileName)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('Download failed:', error)
        alert('Gagal mengunduh laporan')
    } finally {
        isExporting.value = false
    }
}

const formatCurrency = (val) => new Intl.NumberFormat('id-ID').format(val || 0)

const formatDateTime = (isoString) => {
  if (!isoString) return '-'
  return new Date(isoString).toLocaleString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

onMounted(() => {
  fetchReports()
})
</script>
