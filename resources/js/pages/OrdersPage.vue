<template>
  <div class="p-4 md:p-6 space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
      <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Riwayat Pesanan F&B</h1>
      
      <!-- Status Filter -->
      <select 
        v-model="statusFilter" 
        @change="fetchOrders"
        class="w-full sm:w-auto px-4 py-2 text-sm border border-gray-300 rounded-lg dark:bg-gray-800 dark:border-gray-700 dark:text-white"
      >
        <option value="">Semua Status</option>
        <option value="pending">Pending</option>
        <option value="completed">Completed</option>
      </select>
    </div>

    <!-- Orders List -->
    <div class="overflow-x-auto bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
      <table class="w-full min-w-[800px]">
        <thead class="bg-gray-200 dark:bg-gray-900">
          <tr>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">No</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Order #</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Customer / Meja</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Waktu</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Kasir</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Item</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Total</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Status</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-if="loading">
             <td colspan="9" class="px-6 py-8 text-center text-gray-500">Memuat data...</td>
          </tr>
          <tr v-else-if="orders.length === 0">
             <td colspan="9" class="px-6 py-8 text-center text-gray-500">Belum ada pesanan</td>
          </tr>
          <tr v-else v-for="(order, index) in orders" :key="order.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm font-medium text-gray-900 dark:text-white">{{ (pagination.meta.from || 1) + index }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm font-medium text-gray-900 dark:text-white">{{ order.order_number }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-900 dark:text-white">
              <div v-if="order.session">
                <span class="font-medium text-brand-600 dark:text-brand-400">Meja {{ order.session.table?.table_number }}</span>
                <p class="text-xs text-gray-500">{{ order.session.customer_name || 'Tamu' }}</p>
              </div>
              <div v-else>
                <span class="font-medium">{{ order.customer_name || 'Walk-in Customer' }}</span>
                <p class="text-xs text-gray-500">Standalone</p>
              </div>
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-600 dark:text-gray-400">
              {{ new Date(order.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) }}
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-900 dark:text-white">{{ order.cashier?.name || '-' }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-600 dark:text-gray-400">{{ order.items?.length || 0 }} items</td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm font-semibold text-gray-900 dark:text-white">
              Rp {{ Number(order.total).toLocaleString('id-ID') }}
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <span
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="getStatusClass(order.status)"
              >
                {{ formatStatus(order.status) }}
              </span>
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4 space-x-2">
              <button
                @click="viewOrderDetails(order)"
                class="px-3 py-1 text-xs font-medium text-brand-600 border border-brand-200 rounded-lg hover:bg-brand-50 dark:text-brand-400 dark:border-brand-800 dark:hover:bg-brand-900/40"
              >
                Detail
              </button>
              <button
                v-if="!order.session_id && order.status === 'pending'"
                @click="showPaymentDialog(order)"
                class="px-3 py-1 text-xs font-medium text-white bg-green-500 rounded-lg hover:bg-green-600"
              >
                Bayar
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Order Details Dialog -->
    <div
      v-if="selectedOrder"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      @click.self="selectedOrder = null"
    >
      <div class="w-full max-w-2xl bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700 max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">Detail Pesanan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ selectedOrder.order_number }}</p>
          </div>
          <button
            @click="selectedOrder = null"
            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
          >
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-4">
          <!-- Order Info -->
          <div class="grid grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Customer</p>
              <p class="font-medium text-gray-900 dark:text-white">{{ selectedOrder.customer_name || (selectedOrder.session ? 'Tamu Meja' : 'Walk-in') }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Kasir</p>
              <p class="font-medium text-gray-900 dark:text-white">{{ selectedOrder.cashier?.name || '-' }}</p>
            </div>
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Waktu</p>
              <p class="font-medium text-gray-900 dark:text-white">
                {{ new Date(selectedOrder.created_at).toLocaleString('id-ID') }}
              </p>
            </div>
            <div v-if="selectedOrder.session">
              <p class="text-xs text-gray-500 dark:text-gray-400">Link Meja</p>
              <p class="font-medium text-brand-600">Meja {{ selectedOrder.session.table?.table_number }}</p>
            </div>
          </div>

          <!-- Order Items -->
          <div>
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Item Pesanan</h4>
            <div class="space-y-2">
              <div
                v-for="item in selectedOrder.items"
                :key="item.id"
                class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-900 rounded-lg"
              >
                <div>
                  <p class="font-medium text-gray-900 dark:text-white">{{ item.product?.name }}</p>
                  <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ item.quantity }} × Rp {{ Number(item.price).toLocaleString('id-ID') }}
                  </p>
                </div>
                <p class="font-semibold text-gray-900 dark:text-white">
                  Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}
                </p>
              </div>
            </div>
          </div>

          <!-- Totals -->
          <div class="space-y-2 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
              <span class="font-medium text-gray-900 dark:text-white">
                Rp {{ Number(selectedOrder.subtotal).toLocaleString('id-ID') }}
              </span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-gray-600 dark:text-gray-400">Pajak (10%):</span>
              <span class="font-medium text-gray-900 dark:text-white">
                Rp {{ Number(selectedOrder.tax).toLocaleString('id-ID') }}
              </span>
            </div>
            <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200 dark:border-gray-700">
              <span class="text-gray-900 dark:text-white">Total:</span>
              <span class="text-brand-500">Rp {{ Number(selectedOrder.total).toLocaleString('id-ID') }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Dialog Reused -->
    <PosPaymentDialog
      :show="showPayment"
      :order="paymentOrder"
      @close="showPayment = false"
      @success="handlePaymentSuccess"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import PosPaymentDialog from '@/components/PosPaymentDialog.vue'
import Pagination from '@/components/ui/Pagination.vue'

const orders = ref([])
const pagination = ref({ links: [], meta: {} })
const loading = ref(false)
const selectedOrder = ref(null)
const statusFilter = ref('')
const currentPage = ref(1)

// Payment State
const showPayment = ref(false)
const paymentOrder = ref(null)

const fetchOrders = async (page = 1) => {
  loading.value = true
  currentPage.value = page
  try {
    const params = { page }
    if (statusFilter.value) params.status = statusFilter.value
    
    const response = await axios.get('/api/pos/orders', { params })
    orders.value = response.data.data || []
    
    // PosController returns simpler pagination without links array in meta usually if manually constructed
    // Let's check PosController.php:154
    // It returns 'data', 'meta' { current_page, last_page, per_page, total }
    // It does NOT return 'links' array like Laravel default resource collection?
    // Wait, PosController uses $query->paginate(20) and returns $orders->items() and manual meta.
    // It does NOT return the links array needed for my Pagination component (which expects Laravel style links).
    // I should generate links manually or update Pagination component to handle missing links.
    // My Pagination.vue uses `links` prop for page numbers.
    // I will construct simple links array manually here if missing.
    
    const meta = response.data.meta
    const links = []
    
    // Prev
    if (meta.current_page > 1) {
        links.push({ url: `?page=${meta.current_page - 1}`, label: '&laquo; Previous', active: false })
    } else {
        links.push({ url: null, label: '&laquo; Previous', active: false })
    }
    
    // Numbers
    for (let i = 1; i <= meta.last_page; i++) {
        links.push({ 
            url: `?page=${i}`, 
            label: i.toString(), 
            active: i === meta.current_page 
        })
    }
    
    // Next
    if (meta.current_page < meta.last_page) {
        links.push({ url: `?page=${meta.current_page + 1}`, label: 'Next &raquo;', active: false })
    } else {
        links.push({ url: null, label: 'Next &raquo;', active: false })
    }
    
    pagination.value = {
        links: links,
        meta: {
            current_page: meta.current_page,
            last_page: meta.last_page,
            from: (meta.current_page - 1) * meta.per_page + 1,
            to: Math.min(meta.current_page * meta.per_page, meta.total),
            total: meta.total
        }
    }
    
  } catch (error) {
    console.error('Failed to fetch orders:', error)
  } finally {
    loading.value = false
  }
}

const handlePageChange = (page) => {
    fetchOrders(page)
}

const viewOrderDetails = (order) => {
  selectedOrder.value = order
}

const showPaymentDialog = (order) => {
  paymentOrder.value = order
  showPayment.value = true
}

const handlePaymentSuccess = () => {
  showPayment.value = false
  paymentOrder.value = null
  fetchOrders() // Refresh list
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400',
    completed: 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
    cancelled: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
  }
  return classes[status] || classes.pending
}

const formatStatus = (status) => {
  if (status === 'completed') return 'Selesai / Lunas'
  if (status === 'pending') return 'Belum Lunas'
  return status
}

onMounted(() => {
  fetchOrders()
})
</script>
