<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
    @click.self="$emit('close')"
  >
    <div class="w-full max-w-2xl overflow-hidden bg-white shadow-2xl rounded-2xl dark:bg-gray-800">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-semibold text-gray-800 dark:text-white">
            Detail Transaksi
          </h3>
          <button
            @click="$emit('close')"
            class="p-1 text-gray-400 transition rounded-lg hover:bg-gray-100 hover:text-gray-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Body -->
      <div v-if="transaction" class="px-6 py-4 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-500">Invoice</p>
            <p class="font-semibold text-gray-800 dark:text-white">{{ transaction.invoice_number }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Tanggal</p>
            <p class="font-semibold text-gray-800 dark:text-white">
              {{ formatDate(transaction.paid_at) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Kasir</p>
            <p class="font-semibold text-gray-800 dark:text-white">{{ transaction.cashier?.name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Metode Pembayaran</p>
            <p class="font-semibold text-gray-800 dark:text-white">{{ formatPaymentMethod(transaction.payment_method) }}</p>
          </div>
        </div>

        <div class="pt-4 border-t">
          <h4 class="mb-3 font-semibold text-gray-800 dark:text-white">Item</h4>
          <div class="space-y-2">
            <div v-for="(item, index) in transaction.items" :key="index" class="flex justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
              <!-- Session Item -->
              <div v-if="item.type === 'session' || (item.session && !item.product)">
                <p class="font-medium text-gray-800 dark:text-white">
                  Meja {{ item.session?.table_number || item.session?.table?.table_number }}
                </p>
                <div class="text-xs text-gray-500 flex gap-2">
                  <span>{{ item.session?.duration_minutes }} menit</span>
                  <span>•</span>
                  <span>{{ formatCurrency(item.price) }}</span>
                </div>
              </div>
              
              <!-- Product Item -->
              <div v-else-if="item.type === 'product' || item.product">
                <p class="font-medium text-gray-800 dark:text-white">
                  {{ item.product?.name }}
                </p>
                <div class="text-xs text-gray-500">
                  {{ item.product?.category }}
                </div>
              </div>

              <!-- Fallback -->
              <div v-else>
                <p class="font-medium text-gray-800 dark:text-white">Item #{{ index + 1 }}</p>
              </div>

              <p class="font-semibold text-gray-800 dark:text-white">Rp {{ formatCurrency(item.price) }}</p>
            </div>
          </div>
        </div>

        <div class="flex justify-between pt-4 text-lg font-bold border-t">
          <span class="text-gray-800 dark:text-white">Total</span>
          <span class="text-gray-800 dark:text-white">Rp {{ formatCurrency(transaction.total_amount) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  transaction: Object,
})

defineEmits(['close'])

const formatDate = (isoString) => {
  const date = new Date(isoString)
  return date.toLocaleString('id-ID')
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
</script>
