<template>
  <div class="flex h-[calc(100vh-80px)] -m-4 md:-m-6 overflow-hidden relative">
    <!-- Products Section (Left) -->
    <div class="flex-1 flex flex-col border-r border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
      <!-- Search & Filter Header -->
      <div class="p-3 md:p-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 grid gap-2 md:gap-3">
        <input
          v-model="search"
          type="text"
          placeholder="Cari menu makanan atau minuman..."
          class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        />
        
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
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
      <div class="flex-1 overflow-y-auto p-4 bg-gray-50/50 dark:bg-gray-900/50">
        <div v-if="loading" class="flex justify-center py-12">
          <div class="w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
        
        <div v-else-if="filteredProducts.length === 0" class="text-center py-12">
          <p class="text-gray-500 dark:text-gray-400">Menu tidak ditemukan</p>
        </div>

        <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-4">
          <button
            v-for="product in filteredProducts"
            :key="product.id"
            @click="addToCart(product)"
            class="flex flex-col text-left p-2 md:p-3 bg-white border border-gray-100 rounded-xl hover:shadow-md hover:border-brand-200 transition dark:bg-gray-800 dark:border-gray-700 dark:hover:border-brand-700 group h-full relative text-sm md:text-base"
            :disabled="!product.is_available"
            :class="!product.is_available && 'opacity-50 cursor-not-allowed'"
          >
            <!-- Image/Icon Area -->
            <div class="aspect-square bg-gray-100 dark:bg-gray-700 rounded-lg mb-3 flex items-center justify-center relative overflow-hidden group-hover:shadow-sm transition-all">
              <img 
                :src="getProductImage(product.category)" 
                :alt="product.name"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
              />
              
              <!-- Quantity Badge -->
              <div v-if="getProductQtyInCart(product.id) > 0" class="absolute top-2 right-2 bg-brand-500 text-white text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center shadow-md z-10 border border-white dark:border-gray-800">
                {{ getProductQtyInCart(product.id) }}
              </div>

               <!-- Stock Warning (Overlap) -->
              <div v-if="product.stock !== null && product.stock <= 5" class="absolute bottom-0 inset-x-0 bg-red-500/90 text-white text-[10px] text-center py-1 font-bold backdrop-blur-sm z-10">
                Sisa {{ product.stock }}
              </div>
            </div>
            
            <h4 class="font-semibold text-gray-800 dark:text-white line-clamp-2 min-h-[40px] text-sm">
              {{ product.name }}
            </h4>
            
            <div class="mt-auto pt-2 flex items-center justify-between">
              <span class="text-brand-600 dark:text-brand-400 font-bold text-sm">
                Rp {{ formatCurrency(product.price) }}
              </span>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Cart Section (Right) - Hidden on mobile, show on md+ -->
    <div class="hidden md:flex md:w-96 flex-col bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 shadow-xl z-10">
      <div class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
        <h4 class="font-semibold text-gray-800 dark:text-white mb-3">Pesanan Kasir (Walk-in)</h4>
        
        <!-- Customer Name Input -->
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <input
              v-model="customerName"
              type="text"
              placeholder="Nama Pelanggan (Opsional)"
              class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
            />
        </div>
      </div>

      <div class="flex-1 overflow-y-auto p-4">
        <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400">
          <svg class="w-12 h-12 mb-3 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          <p class="text-sm">Keranjang kosong</p>
        </div>

        <div v-else class="space-y-4">
          <div v-for="item in cart" :key="item.product_id" class="flex gap-3 group">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden shrink-0">
              <img 
                :src="getProductImage(item.category)" 
                :alt="item.name"
                class="w-full h-full object-cover"
              />
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex justify-between items-start">
                  <h5 class="text-sm font-medium text-gray-800 dark:text-white truncate pr-2">
                    {{ item.name }}
                  </h5>
                  <button @click="removeFromCart(item)" class="text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition">
                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                       </svg>
                  </button>
              </div>
              
              <p class="text-xs text-brand-600 dark:text-brand-400 font-medium my-1">
                Rp {{ formatCurrency(item.price * item.quantity) }}
              </p>
              
              <!-- Quantity Control -->
              <div class="flex items-center gap-3">
                <button
                  @click="updateQuantity(item, -1)"
                  class="w-6 h-6 rounded bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 flex items-center justify-center text-gray-600 dark:text-gray-300 transition"
                >
                  -
                </button>
                <span class="text-xs font-bold w-4 text-center dark:text-white">
                  {{ item.quantity }}
                </span>
                <button
                  @click="updateQuantity(item, 1)"
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

        <div class="flex justify-between items-center text-lg font-bold pt-2 border-t border-gray-200 dark:border-gray-700">
          <span class="text-gray-800 dark:text-white">Total Bayar</span>
          <span class="text-brand-600 dark:text-brand-400">Rp {{ formatCurrency(total) }}</span>
        </div>
        
        <div class="grid grid-cols-4 gap-2">
            <button
              @click="clearCart"
              :disabled="cart.length === 0"
              class="col-span-1 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-xl font-medium disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
             <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
             </svg>
            </button>
            <button
              @click="checkout"
              :disabled="cart.length === 0 || loading"
              class="col-span-3 py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:shadow-none disabled:cursor-not-allowed transition-all"
            >
              {{ loading ? 'Memproses...' : 'Bayar Sekarang' }}
            </button>
        </div>
      </div>
    </div>

    <!-- Mobile Cart Button (Fixed at bottom) -->
    <button
      v-if="cart.length > 0"
      @click="showMobileCart = true"
      class="md:hidden fixed bottom-4 right-4 z-50 bg-brand-500 text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-2 font-semibold hover:bg-brand-600 transition-all"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
      </svg>
      <span>{{ totalItems }}</span>
      <span class="text-sm">•</span>
      <span>Rp {{ formatCurrency(total) }}</span>
    </button>

    <!-- Mobile Cart Overlay -->
    <div
      v-if="showMobileCart"
      class="md:hidden fixed inset-0 z-50 bg-gray-900/50"
      @click="showMobileCart = false"
    >
      <div
        @click.stop
        class="absolute right-0 top-0 bottom-0 w-full max-w-sm bg-white dark:bg-gray-800 shadow-2xl flex flex-col"
      >
        <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
          <h4 class="font-semibold text-gray-800 dark:text-white">Pesanan Kasir</h4>
          <button @click="showMobileCart = false" class="p-1 hover:bg-gray-200 dark:hover:bg-gray-700 rounded">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>

        <!-- Customer Name in Mobile -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
          <input
            v-model="customerName"
            type="text"
            placeholder="Nama Pelanggan (Opsional)"
            class="w-full px-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
          />
        </div>

        <!-- Cart Items in Mobile -->
        <div class="flex-1 overflow-y-auto p-4">
          <div v-if="cart.length === 0" class="h-full text-center text-gray-500 dark:text-gray-400 flex flex-col items-center justify-center">
            <p class="text-sm">Keranjang kosong</p>
          </div>

          <div v-else class="space-y-4">
            <div v-for="item in cart" :key="item.product_id" class="flex gap-3 group">
              <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-lg overflow-hidden shrink-0">
                <img 
                  :src="getProductImage(item.category)" 
                  :alt="item.name"
                  class="w-full h-full object-cover"
                />
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start">
                  <h5 class="font-medium text-sm text-gray-800 dark:text-white truncate pr-2">{{ item.name }}</h5>
                  <button @click="removeFromCart(item.product_id)" class="text-red-500 hover:text-red-700 shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </div>
                
                <p class="text-xs text-brand-600 dark:text-brand-400 font-semibold">Rp {{ formatCurrency(item.price) }}</p>
                
                <div class="flex items-center gap-2 mt-2">
                  <button
                    @click="decreaseQuantity(item.product_id)"
                    class="w-7 h-7 flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded text-gray-600 dark:text-gray-300"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                  </button>
                  <span class="w-8 text-center font-medium text-sm dark:text-white">{{ item.quantity }}</span>
                  <button
                    @click="increaseQuantity(item.product_id)"
                    class="w-7 h-7 flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded text-gray-600 dark:text-gray-300"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  </button>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Subtotal: Rp {{ formatCurrency(item.price * item.quantity) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Cart Footer in Mobile -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 space-y-3">
          <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600 dark:text-gray-400">Total Item</span>
            <span class="font-medium dark:text-white">{{ totalItems }} item</span>
          </div>

          <div class="flex justify-between items-center text-lg font-bold pt-2 border-t border-gray-200 dark:border-gray-700">
            <span class="text-gray-800 dark:text-white">Total Bayar</span>
            <span class="text-brand-600 dark:text-brand-400">Rp {{ formatCurrency(total) }}</span>
          </div>
          
          <div class="grid grid-cols-4 gap-2">
            <button
              @click="clearCart"
              :disabled="cart.length === 0"
              class="col-span-1 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-xl font-medium disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
              <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
            <button
              @click="checkout"
              :disabled="cart.length === 0 || loading"
              class="col-span-3 py-3 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-xl shadow-lg shadow-brand-500/30 disabled:opacity-50 disabled:shadow-none disabled:cursor-not-allowed transition-all"
            >
              {{ loading ? 'Memproses...' : 'Bayar Sekarang' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Payment Dialog -->
    <PosPaymentDialog
      :show="showPaymentDialog"
      :order="createdOrder"
      @close="showPaymentDialog = false"
      @success="handlePaymentSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import PosPaymentDialog from '@/components/PosPaymentDialog.vue'
import { useNotificationStore } from '@/stores/notification'
import { useConfirmStore } from '@/stores/confirm'

const notify = useNotificationStore()
const confirm = useConfirmStore()

// State
const products = ref([])
const cart = ref([])
const loading = ref(false)
const search = ref('')
const selectedCategory = ref('all')
const customerName = ref('')
const showPaymentDialog = ref(false)
const createdOrder = ref(null)
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
  const existingItem = cart.value.find(item => item.product_id === product.id)
  
  if (existingItem) {
    // Check stock if needed
    if (product.stock !== null && existingItem.quantity >= product.stock) return
    existingItem.quantity++
  } else {
    // Check stock if needed
    if (product.stock !== null && product.stock < 1) return
    cart.value.push({
      product_id: product.id,
      name: product.name,
      price: product.price,
      quantity: 1,
      category: product.category // for emoji
    })
  }
}

const updateQuantity = (item, delta) => {
  item.quantity += delta
  if (item.quantity <= 0) {
    removeFromCart(item.product_id)
  }
}

const increaseQuantity = (productId) => {
  const item = cart.value.find(i => i.product_id === productId)
  if (item) {
    item.quantity++
  }
}

const decreaseQuantity = (productId) => {
  const item = cart.value.find(i => i.product_id === productId)
  if (item) {
    item.quantity--
    if (item.quantity <= 0) {
      removeFromCart(productId)
    }
  }
}

const removeFromCart = (productId) => {
  const index = cart.value.findIndex(item => item.product_id === productId)
  if (index > -1) {
    cart.value.splice(index, 1)
  }
}

const clearCart = async () => {
  const confirmed = await confirm.show({
      title: 'Hapus Keranjang',
      message: 'Kosongkan keranjang belanja?',
      confirmText: 'Ya, Kosongkan',
      type: 'warning'
  })
  
  if (confirmed) {
    cart.value = []
    customerName.value = ''
  }
}

const getProductQtyInCart = (productId) => {
  const item = cart.value.find(i => i.product_id === productId)
  return item ? item.quantity : 0
}

const totalItems = computed(() => cart.value.reduce((acc, item) => acc + item.quantity, 0))

const subtotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const total = computed(() => {
  return subtotal.value
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

const checkout = async () => {
  loading.value = true
  try {
    const orderData = {
      customer_name: customerName.value || null,
      session_id: null,
      items: cart.value.map(item => ({
        product_id: item.product_id,
        quantity: item.quantity,
      })),
    }

    const response = await axios.post('/api/pos/orders', orderData)
    
    cart.value = []
    customerName.value = ''
    
    createdOrder.value = response.data.data
    showPaymentDialog.value = true

  } catch (error) {
    notify.error(error.response?.data?.message || 'Gagal memproses pesanan')
  } finally {
    loading.value = false
  }
}

const handlePaymentSuccess = () => {
    showPaymentDialog.value = false
    createdOrder.value = null
}

onMounted(() => {
  fetchProducts()
})
</script>
