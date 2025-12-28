<template>
  <div class="p-4 md:p-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4 md:mb-6">
      <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800 dark:text-white">Tarif Billiard</h1>
        <p class="mt-1 text-xs md:text-sm text-gray-500 dark:text-gray-400">
          Kelola tarif harga per jam billiard
        </p>
      </div>
      <button
        @click="showAddDialog = true"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white transition rounded-lg bg-brand-500 hover:bg-brand-600"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Tarif
      </button>
    </div>

    <!-- Rates Table -->
    <div class="overflow-x-auto bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
      <table class="w-full min-w-[500px]">
        <thead class="bg-gray-200 dark:bg-gray-900">
          <tr>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
              No
            </th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">
              Nama Tarif
            </th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-right text-gray-700 uppercase dark:text-gray-400">
              Harga/Jam
            </th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-center text-gray-700 uppercase dark:text-gray-400">
              Aksi
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="loading">
            <td colspan="4" class="px-6 py-8 text-center text-gray-500">Memuat data...</td>
          </tr>
          <tr v-else-if="rates.length === 0">
            <td colspan="4" class="px-6 py-8 text-center text-gray-500">Belum ada tarif</td>
          </tr>
          <tr v-else v-for="(rate, index) in rates" :key="rate.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-3 md:px-6 py-3 md:py-4">
              <p class="text-sm font-medium text-gray-900 dark:text-white">{{ (pagination.meta.from || 1) + index }}</p>
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <p class="text-sm font-medium text-gray-800 dark:text-white">{{ rate.name }}</p>
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-right">
              <p class="text-sm font-semibold text-gray-800 dark:text-white">
                Rp {{ formatCurrency(rate.price_per_hour) }}
              </p>
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <div class="flex justify-center gap-2">
                <button
                  @click="editRate(rate)"
                  class="px-3 py-1.5 text-xs font-medium text-brand-600 transition hover:bg-brand-50 rounded-lg dark:text-brand-400"
                >
                  Edit
                </button>
                <button
                  @click="deleteRate(rate)"
                  class="px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50 rounded-lg"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Rate Dialog -->
    <RateDialog
      :show="showAddDialog || showEditDialog"
      :rate="editingRate"
      @close="closeDialog"
      @success="handleSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import RateDialog from '@/components/RateDialog.vue'
import Pagination from '@/components/ui/Pagination.vue'
import { useNotificationStore } from '@/stores/notification'
import { useConfirmStore } from '@/stores/confirm'

const notify = useNotificationStore()
const confirm = useConfirmStore()

const loading = ref(false)
const rates = ref([])
const pagination = ref({ links: [], meta: {} })
const editingRate = ref(null)
const showAddDialog = ref(false)
const showEditDialog = ref(false)
const currentPage = ref(1)

const fetchRates = async (page = 1) => {
  loading.value = true
  currentPage.value = page
  try {
    const response = await axios.get('/api/rates', {
        params: { page }
    })
    if (response.data.success) {
      rates.value = response.data.data.data
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
    console.error('Failed to fetch rates:', error)
  } finally {
    loading.value = false
  }
}

const handlePageChange = (page) => {
    fetchRates(page)
}

const editRate = (rate) => {
  editingRate.value = rate
  showEditDialog.value = true
}

const deleteRate = async (rate) => {
  const confirmed = await confirm.show({
    title: 'Hapus Tarif',
    message: `Yakin ingin menghapus tarif "${rate.name}"?`,
    confirmText: 'Hapus',
    type: 'danger'
  })
  
  if (!confirmed) return

  try {
    const response = await axios.delete(`/api/rates/${rate.id}`)
    if (response.data.success) {
      notify.success(response.data.message)
      fetchRates()
    }
  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal menghapus tarif')
  }
}

const closeDialog = () => {
  showAddDialog.value = false
  showEditDialog.value = false
  editingRate.value = null
}

const handleSuccess = () => {
  closeDialog()
  fetchRates()
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('id-ID').format(value)
}

onMounted(() => {
  fetchRates()
})
</script>
