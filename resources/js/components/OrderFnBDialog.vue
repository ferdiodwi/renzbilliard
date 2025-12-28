<template>
  <div
    v-if="show"
    class="fixed inset-0 z-50 flex items-center justify-center p-2 md:p-4 bg-black/50"
    @click.self="closeDialog"
  >
    <div
      class="w-full max-w-full md:max-w-4xl h-[90vh] md:h-[85vh] overflow-hidden flex flex-col transition-all transform bg-white shadow-2xl rounded-xl md:rounded-2xl dark:bg-gray-800"
    >
      <!-- Header -->
      <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 z-10">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-base md:text-xl font-semibold text-gray-800 dark:text-white">
              Order F&B - Meja {{ session?.table?.table_number }}
            </h3>
            <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
              Pelanggan: {{ session?.customer_name }}
            </p>
          </div>
          <button
            @click="closeDialog"
            class="p-2 text-gray-400 transition rounded-lg hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Content Layout -->
      <div class="flex flex-1 overflow-hidden">
        <!-- Products Section (Left) -->
        <div class="flex-1 flex flex-col border-r border-gray-200 dark:border-gray-700">
          <!-- Filter & Search -->
          <div class="p-3 md:p-4 bg-gray-50 dark:bg-gray-900 grid gap-2 md:gap-3">
            <input
              v-model="search"
              type="text"
              placeholder="Cari menu makanan atau minuman..."
              class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
            
            <div class="flex gap-2 overflow-x-auto pb-1">
              <button
                v-for="cat in categories"
                :key="cat.value"
                @click="selectedCategory = cat.value"
                class="px-4 py-1.5 text-sm font-medium rounded-full whitespace-nowrap transition-colors"
                :class="selectedCategory === cat.value
                  ? 'bg-brand-500 text-white'
                  : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700'"
              >
                {{ cat.label }}
              </button>
            </div>
          </div>

          <!-- Products Grid -->
          <div class="flex-1 overflow-y-auto p-3 md:p-4">
            <div v-if="loading" class="flex justify-center py-12">
              <div class="w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
            
            <div v-else-if="filteredProducts.length === 0" class="text-center py-12">
              <p class="text-gray-500 dark:text-gray-400">Menu tidak ditemukan</p>
            </div>

            <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
              <button
                v-for="product in filteredProducts"
                :key="product.id"
                @click="addToCart(product)"
                class="flex flex-col text-left p-3 bg-white border border-gray-100 rounded-xl hover:shadow-md hover:border-brand-200 transition dark:bg-gray-800 dark:border-gray-700 dark:hover:border-brand-700 group h-full"
              >
                <div class="aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg mb-3 flex items-center justify-center relative overflow-hidden group-hover:shadow-sm transition-all">
                  <img 
                    :src="getProductImage(product.category)" 
                    :alt="product.name"
                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                  />
                  
                  <!-- Quantity Badge in Grid -->
                  <div v-if="getProductQtyInCart(product.id) > 0" class="absolute top-2 right-2 bg-brand-500 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center shadow-md z-10 border border-white dark:border-gray-800">
                    {{ getProductQtyInCart(product.id) }}
                  </div>
                </div>
                
                <h4 class="font-semibold text-gray-800 dark:text-white line-clamp-2 min-h-[40px]">
                  {{ product.name }}
                </h4>
                
                <div class="mt-auto pt-2 flex items-center justify-between">
                  <span class="text-brand-600 dark:text-brand-400 font-bold">
                    Rp {{ formatCurrency(product.price) }}
                  </span>
                  <span v-if="product.stock < 5" class="text-xs text-red-500 font-medium">
                    Sisa {{ product.stock }}
                  </span>
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- Cart Section (Right) -->
        <div class="hidden md:flex w-80 flex-col bg-white dark:bg-gray-800">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <h4 class="font-semibold text-gray-800 dark:text-white">Pesanan Saat Ini</h4>
          </div>

          <div class="flex-1 overflow-y-auto p-4">
            <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400">
              <svg class="w-12 h-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              <p class="text-sm">Belum ada menu yang dipilih</p>
            </div>

            <div v-else class="space-y-4">
              <div v-for="item in cart" :key="item.product.id" class="flex gap-3">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden shrink-0">
                  <img 
                    :src="getProductImage(item.product.category)" 
                    :alt="item.product.name"
                    class="w-full h-full object-cover"
                  />
                </div>
                <div class="flex-1 min-w-0">
                  <h5 class="text-sm font-medium text-gray-800 dark:text-white truncate">
                    {{ item.product.name }}
                  </h5>
                  <p class="text-xs text-brand-600 dark:text-brand-400 font-medium my-1">
                    Rp {{ formatCurrency(item.product.price * item.quantity) }}
                  </p>
                  
                  <!-- Quantity Control -->
                  <div class="flex items-center gap-3">
                    <button
                      @click="decreaseQty(item)"
                      class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 transition"
                    >
                      -
                    </button>
                    <span class="text-sm font-medium w-4 text-center dark:text-white">
                      {{ item.quantity }}
                    </span>
                    <button
                      @click="addToCart(item.product)"
                      class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 transition"
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Cart Footer -->
          <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 space-y-3">
            <div class="flex justify-between items-center text-sm">
              <span class="text-gray-600 dark:text-gray-400">Total Item</span>
              <span class="font-medium dark:text-white">{{ totalItems }} item</span>
            </div>
            <div class="flex justify-between items-center text-lg font-bold">
              <span class="text-gray-800 dark:text-white">Total</span>
              <span class="text-brand-600 dark:text-brand-400">Rp {{ formatCurrency(totalPrice) }}</span>
            </div>
            
            <button
              @click="submitOrder"
              :disabled="cart.length === 0 || submitting"
              class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:shadow-none disabled:cursor-not-allowed transition-all"
            >
              {{ submitting ? 'Memproses...' : 'Proses Pesanan' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Cart Button (Only visible when cart has items) -->
      <button
        v-if="cart.length > 0"
        @click.stop="showMobileCart = true"
        class="md:hidden fixed bottom-20 right-4 z-50 bg-brand-500 text-white px-5 py-3 rounded-full shadow-2xl flex items-center gap-2 font-semibold text-sm"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span>{{ totalItems }}</span>
        <span>•</span>
        <span>Rp {{ formatCurrency(totalPrice) }}</span>
      </button>

      <!-- Mobile Cart Overlay -->
      <div
        v-if="showMobileCart"
        class="md:hidden fixed inset-0 z-[60] bg-gray-900/50"
        @click.stop="showMobileCart = false"
      >
        <div
          @click.stop
          class="absolute right-0 top-0 bottom-0 w-full max-w-sm bg-white dark:bg-gray-800 shadow-2xl flex flex-col"
        >
          <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <h4 class="font-semibold text-gray-800 dark:text-white">Pesanan Saat Ini</h4>
            <button @click.stop="showMobileCart = false" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Cart Items Mobile -->
          <div class="flex-1 overflow-y-auto p-4">
            <div v-if="cart.length === 0" class="h-full text-center text-gray-500 flex flex-col items-center justify-center">
              <p class="text-sm">Belum ada menu yang dipilih</p>
            </div>

            <div v-else class="space-y-4">
              <div v-for="item in cart" :key="item.product.id" class="flex gap-3">
                <div class="flex-1">
                  <div class="flex justify-between items-start">
                    <h5 class="font-medium text-sm text-gray-800 dark:text-white">{{ item.product.name }}</h5>
                    <button @click="removeFromCart(item.product.id)" class="text-red-500 hover:text-red-700">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                  
                  <p class="text-xs text-brand-600 dark:text-brand-400 font-semibold">Rp {{ formatCurrency(item.product.price) }}</p>
                  
                  <div class="flex items-center gap-2 mt-2">
                    <button
                      @click="decreaseQty(item)"
                      class="w-7 h-7 flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                    </button>
                    <span class="w-8 text-center font-medium text-sm dark:text-white">{{ item.quantity }}</span>
                    <button
                      @click="addToCart(item.product)"
                      class="w-7 h-7 flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </button>
                  </div>

                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Subtotal: Rp {{ formatCurrency(item.product.price * item.quantity) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Cart Footer Mobile -->
          <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 space-y-3">
            <div class="flex justify-between items-center text-sm">
              <span class="text-gray-600 dark:text-gray-400">Total Item</span>
              <span class="font-medium dark:text-white">{{ totalItems }} item</span>
            </div>
            <div class="flex justify-between items-center text-lg font-bold">
              <span class="text-gray-800 dark:text-white">Total</span>
              <span class="text-brand-600 dark:text-brand-400">Rp {{ formatCurrency(totalPrice) }}</span>
            </div>
            
            <button
              @click="submitOrder"
              :disabled="cart.length === 0 || submitting"
              class="w-full py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:shadow-none disabled:cursor-not-allowed transition-all"
            >
              {{ submitting ? 'Memproses...' : 'Proses Pesanan' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'

const notify = useNotificationStore()

const props = defineProps({
  show: Boolean,
  session: Object,
})

const emit = defineEmits(['close', 'success'])

// State
const products = ref([])
const cart = ref([])
const loading = ref(false)
const submitting = ref(false)
const search = ref('')
const selectedCategory = ref('all')
const showMobileCart = ref(false)

const categories = [
  { label: 'Semua', value: 'all' },
  { label: 'Makanan', value: 'makanan' },
  { label: 'Minuman', value: 'minuman' },
  { label: 'Snack', value: 'snack' },
]

// Methods
const fetchProducts = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/pos/products')
    if (response.data.success) {
      products.value = response.data.data
    }
  } catch (error) {
    console.error('Failed to fetch products:', error)
  } finally {
    loading.value = false
  }
}

const filteredProducts = computed(() => {
  return products.value.filter(product => {
    const matchesSearch = product.name.toLowerCase().includes(search.value.toLowerCase())
    const matchesCategory = selectedCategory.value === 'all' || product.category === selectedCategory.value
    return matchesSearch && matchesCategory
  })
})

const addToCart = (product) => {
  const existingItem = cart.value.find(item => item.product.id === product.id)
  
  if (existingItem) {
    // Check stock
    if (existingItem.quantity >= product.stock) return
    existingItem.quantity++
  } else {
    // Check stock
    if (product.stock < 1) return
    cart.value.push({
      product,
      quantity: 1
    })
  }
}

const decreaseQty = (item) => {
  if (item.quantity > 1) {
    item.quantity--
  } else {
    const index = cart.value.indexOf(item)
    if (index > -1) {
      cart.value.splice(index, 1)
    }
  }
}

const getProductQtyInCart = (productId) => {
  const item = cart.value.find(i => i.product.id === productId)
  return item ? item.quantity : 0
}

const totalItems = computed(() => cart.value.reduce((acc, item) => acc + item.quantity, 0))

const totalPrice = computed(() => {
  return cart.value.reduce((acc, item) => acc + (item.product.price * item.quantity), 0)
})

const getProductEmoji = (category) => {
  switch(category) {
    case 'makanan': return '🍛'
    case 'minuman': return '🥤'
    case 'snack': return '🍟'
    default: return '🍽️'
  }
}

const getProductImage = (category) => {
  switch(category) {
    case 'makanan': return '/images/product/custom-food.jpg'
    case 'minuman': return '/images/product/custom-drink.jpg'
    case 'snack': return '/images/product/custom-snack.jpg'
    default: return '/images/product/product-04.jpg'
  }
}

const formatCurrency = (val) => new Intl.NumberFormat('id-ID').format(val)

const closeDialog = () => {
  cart.value = []
  search.value = ''
  selectedCategory.value = 'all'
  emit('close')
}

const submitOrder = async () => {
  if (cart.value.length === 0) return
  
  submitting.value = true
  try {
    const items = cart.value.map(item => ({
      product_id: item.product.id,
      quantity: item.quantity
    }))
    
    await axios.post(`/api/sessions/${props.session.id}/order`, { items })
    
    notify.success('Pesanan berhasil ditambahkan')
    emit('success')
    closeDialog()
  } catch (error) {
    console.error('Failed to submit order:', error)
    notify.error('Gagal memproses pesanan')
  } finally {
    submitting.value = false
  }
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    fetchProducts()
  }
})

onMounted(() => {
  if (props.show) {
    fetchProducts()
  }
})
</script>
