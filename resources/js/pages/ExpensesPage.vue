<template>
  <div>
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Pengeluaran</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
          Kelola catatan pengeluaran bisnis
        </p>
      </div>
      <button
        @click="openDialog()"
        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Pengeluaran
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col md:flex-row gap-4 items-center justify-between mb-6">
      <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300 whitespace-nowrap">
        <span>Tampilkan</span>
        <select
          v-model="perPage"
          @change="fetchExpenses()"
          class="px-3 py-1.5 border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500"
        >
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
        </select>
        <span>baris</span>
      </div>

      <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
        <!-- Category Filter -->
        <select
          v-model="categoryFilter"
          @change="fetchExpenses()"
          class="px-4 py-2 text-sm border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500"
        >
          <option value="">Semua Kategori</option>
          <option value="operasional">Operasional</option>
          <option value="gaji">Gaji</option>
          <option value="pembelian_stok">Pembelian Stok</option>
          <option value="lainnya">Lainnya</option>
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
            placeholder="Cari deskripsi..."
            class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-brand-500"
          />
        </div>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Total Pengeluaran</p>
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">
          Rp {{ formatCurrency(stats.total_amount) }}
        </p>
      </div>
      <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah Transaksi</p>
        <p class="text-2xl font-bold text-gray-800 dark:text-white">
          {{ stats.total_expenses }}
        </p>
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
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Deskripsi</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Kategori</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Metode</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-right text-gray-700 uppercase dark:text-gray-400">Jumlah</th>
              <th class="px-6 py-3 text-xs font-bold tracking-wider text-center text-gray-700 uppercase dark:text-gray-400">Aksi</th>
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
            <tr v-else-if="expenses.length === 0">
              <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                Belum ada data pengeluaran
              </td>
            </tr>
            <tr v-else v-for="(expense, index) in expenses" :key="expense.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
              <td class="px-6 py-4 text-sm text-gray-800 dark:text-white">
                {{ (pagination.meta.from || 1) + index }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-800 dark:text-white">
                {{ formatDate(expense.expense_date) }}
              </td>
              <td class="px-6 py-4">
                <p class="text-sm font-medium text-gray-800 dark:text-white">{{ expense.description }}</p>
                <p v-if="expense.notes" class="text-xs text-gray-500 dark:text-gray-400">{{ expense.notes }}</p>
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getCategoryClass(expense.category)">
                  {{ getCategoryLabel(expense.category) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getPaymentClass(expense.payment_method)">
                  {{ expense.payment_method === 'cash' ? 'Tunai' : 'QRIS' }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                  - Rp {{ formatCurrency(expense.amount) }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <button
                  @click="openDialog(expense)"
                  class="px-3 py-1 text-xs font-medium text-brand-600 hover:bg-brand-50 rounded-lg dark:text-brand-400 dark:hover:bg-brand-500/10"
                >
                  Edit
                </button>
                <button
                  @click="handleDelete(expense)"
                  class="px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50 rounded-lg dark:text-red-400 dark:hover:bg-red-500/10 ml-2"
                >
                  Hapus
                </button>
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

    <!-- Add/Edit Dialog -->
    <div
      v-if="showDialog"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      @click.self="showDialog = false"
    >
      <div class="w-full max-w-md overflow-hidden bg-white shadow-2xl rounded-2xl dark:bg-gray-800">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <div class="flex items-center justify-between">
            <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
              {{ editingExpense ? 'Edit Pengeluaran' : 'Tambah Pengeluaran' }}
            </h3>
            <button @click="showDialog = false" class="p-1 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>

        <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Deskripsi <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.description"
              type="text"
              required
              placeholder="Contoh: Bayar listrik"
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
          </div>

          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Jumlah <span class="text-red-500">*</span>
            </label>
            <input
              v-model.number="form.amount"
              type="number"
              required
              min="0"
              step="1000"
              placeholder="0"
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Kategori <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.category"
                required
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              >
                <option value="operasional">Operasional</option>
                <option value="gaji">Gaji</option>
                <option value="pembelian_stok">Pembelian Stok</option>
                <option value="lainnya">Lainnya</option>
              </select>
            </div>
            <div>
              <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                Metode <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.payment_method"
                required
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
              >
                <option value="cash">Tunai</option>
                <option value="qris">QRIS</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Tanggal <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.expense_date"
              type="date"
              required
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
          </div>

          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">
              Catatan (Opsional)
            </label>
            <textarea
              v-model="form.notes"
              rows="2"
              placeholder="Catatan tambahan..."
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            ></textarea>
          </div>

          <div class="flex gap-3 pt-2">
            <button
              type="button"
              @click="showDialog = false"
              class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="formLoading"
              class="flex-1 px-4 py-3 text-sm font-semibold text-white rounded-lg bg-brand-500 hover:bg-brand-600 disabled:opacity-50"
            >
              {{ formLoading ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import Pagination from '@/components/ui/Pagination.vue'
import { useNotificationStore } from '@/stores/notification'
import { useConfirmStore } from '@/stores/confirm'

const notify = useNotificationStore()
const confirm = useConfirmStore()

const loading = ref(false)
const formLoading = ref(false)
const expenses = ref([])
const pagination = ref({ links: [], meta: {} })
const stats = ref({ total_expenses: 0, total_amount: 0 })
const showDialog = ref(false)
const editingExpense = ref(null)
const perPage = ref(10)
const categoryFilter = ref('')
const search = ref('')
const currentPage = ref(1)

const form = ref({
  description: '',
  amount: null,
  category: 'lainnya',
  payment_method: 'cash',
  expense_date: new Date().toISOString().split('T')[0],
  notes: '',
})

const fetchExpenses = async (page = 1) => {
  loading.value = true
  currentPage.value = page
  try {
    const params = { page, per_page: perPage.value }
    if (categoryFilter.value) params.category = categoryFilter.value
    if (search.value) params.search = search.value

    const response = await axios.get('/api/expenses', { params })
    if (response.data.success) {
      expenses.value = response.data.data.data
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
    console.error('Failed to fetch expenses:', error)
  } finally {
    loading.value = false
  }
}

let searchTimeout
watch(search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => fetchExpenses(1), 500)
})

const handlePageChange = (page) => {
  fetchExpenses(page)
}

const openDialog = (expense = null) => {
  editingExpense.value = expense
  if (expense) {
    form.value = {
      description: expense.description,
      amount: expense.amount,
      category: expense.category,
      payment_method: expense.payment_method,
      expense_date: expense.expense_date,
      notes: expense.notes || '',
    }
  } else {
    form.value = {
      description: '',
      amount: null,
      category: 'lainnya',
      payment_method: 'cash',
      expense_date: new Date().toISOString().split('T')[0],
      notes: '',
    }
  }
  showDialog.value = true
}

const handleSubmit = async () => {
  formLoading.value = true
  try {
    const url = editingExpense.value
      ? `/api/expenses/${editingExpense.value.id}`
      : '/api/expenses'
    const method = editingExpense.value ? 'put' : 'post'

    const response = await axios[method](url, form.value)
    if (response.data.success) {
      notify.success(response.data.message)
      showDialog.value = false
      fetchExpenses(currentPage.value)
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal menyimpan')
  } finally {
    formLoading.value = false
  }
}

const handleDelete = async (expense) => {
  const confirmed = await confirm.show({
    title: 'Hapus Pengeluaran',
    message: `Yakin ingin menghapus "${expense.description}"?`,
    confirmText: 'Hapus',
    type: 'danger',
  })

  if (!confirmed) return

  try {
    const response = await axios.delete(`/api/expenses/${expense.id}`)
    if (response.data.success) {
      notify.success(response.data.message)
      fetchExpenses(currentPage.value)
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal menghapus')
  }
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value || 0)
}

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

const getCategoryLabel = (category) => {
  const labels = {
    operasional: 'Operasional',
    gaji: 'Gaji',
    pembelian_stok: 'Pembelian Stok',
    lainnya: 'Lainnya',
  }
  return labels[category] || category
}

const getCategoryClass = (category) => {
  const classes = {
    operasional: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
    gaji: 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
    pembelian_stok: 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
    lainnya: 'bg-gray-100 text-gray-700 dark:bg-gray-500/20 dark:text-gray-400',
  }
  return classes[category] || classes.lainnya
}

const getPaymentClass = (method) => {
  return method === 'cash'
    ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400'
    : 'bg-brand-100 text-brand-700 dark:bg-brand-500/20 dark:text-brand-400'
}

onMounted(() => {
  fetchExpenses()
})
</script>
