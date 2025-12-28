<template>
  <div class="p-4 md:p-6 space-y-4 md:space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
      <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Products Management</h1>
      <button
        @click="showDialog = true; editingProduct = null"
        class="w-full sm:w-auto px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg transition text-sm"
      >
        + Add Product
      </button>
    </div>

    <!-- Filter -->
    <div class="flex gap-3">
      <select
        v-model="filterCategory"
        class="w-full sm:w-auto px-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
      >
        <option value="">All Categories</option>
        <option value="makanan">Makanan</option>
        <option value="minuman">Minuman</option>
        <option value="snack">Snack</option>
      </select>
    </div>

    <!-- Products Table -->
    <div class="overflow-x-auto bg-white border border-gray-200 rounded-xl shadow-sm dark:bg-gray-800 dark:border-gray-700">
      <table class="w-full min-w-[640px]">
        <thead class="bg-gray-200 dark:bg-gray-900">
          <tr>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">No</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Name</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Category</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Price</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Stock</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Status</th>
            <th class="px-3 md:px-6 py-3 text-xs font-bold tracking-wider text-left text-gray-700 uppercase dark:text-gray-400">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="(product, index) in filteredProducts" :key="product.id" class="hover:bg-gray-50 dark:hover:bg-gray-900">
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm font-medium text-gray-900 dark:text-white">
                {{ (pagination.meta.from || 1) + index }}
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-900 dark:text-white">{{ product.name }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400">
                {{ product.category }}
              </span>
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-900 dark:text-white">Rp {{ Number(product.price).toLocaleString() }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4 text-sm text-gray-900 dark:text-white">{{ product.stock || '∞' }}</td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <span
                class="px-2 py-1 text-xs font-semibold rounded-full"
                :class="product.is_available ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400'"
              >
                {{ product.is_available ? 'Available' : 'Unavailable' }}
              </span>
            </td>
            <td class="px-3 md:px-6 py-3 md:py-4">
              <div class="flex gap-2">
                <button
                  @click="handleEdit(product)"
                  class="px-3 py-1 text-sm text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium"
                >
                  Edit
                </button>
                <button
                  @click="handleDelete(product)"
                  class="px-3 py-1 text-sm text-red-600 hover:text-red-700 dark:text-red-400 font-medium"
                >
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination 
        v-if="pagination.meta.total > 0"
        :links="pagination.links"
        :meta="pagination.meta"
        @page-change="handlePageChange"
        class="mt-4"
    />

    <!-- Product Dialog -->
    <div
      v-if="showDialog"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
      @click.self="showDialog = false"
    >
      <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
          {{ editingProduct ? 'Edit Product' : 'Add Product' }}
        </h3>

        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category</label>
            <select
              v-model="form.category"
              required
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
            >
              <option value="makanan">Makanan</option>
              <option value="minuman">Minuman</option>
              <option value="snack">Snack</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Price</label>
            <input
              v-model.number="form.price"
              type="number"
              min="0"
              step="100"
              required
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stock (optional)</label>
            <input
              v-model.number="form.stock"
              type="number"
              min="0"
              class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white"
              placeholder="Leave empty for unlimited"
            />
          </div>

          <div class="flex items-center gap-2">
            <input
              v-model="form.is_available"
              type="checkbox"
              id="available"
              class="w-4 h-4 text-brand-500 border-gray-300 rounded focus:ring-brand-500"
            />
            <label for="available" class="text-sm font-medium text-gray-700 dark:text-gray-300">Available</label>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white font-semibold rounded-lg disabled:opacity-50"
            >
              {{ loading ? 'Saving...' : 'Save' }}
            </button>
            <button
              type="button"
              @click="showDialog = false"
              class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useNotificationStore } from '@/stores/notification'
import { useConfirmStore } from '@/stores/confirm'
import Pagination from '@/components/ui/Pagination.vue'

const notify = useNotificationStore()
const confirm = useConfirmStore()

const products = ref([])
const pagination = ref({ links: [], meta: {} })
const loading = ref(false)
const showDialog = ref(false)
const editingProduct = ref(null)
const filterCategory = ref('')
const currentPage = ref(1)

const form = ref({
  name: '',
  category: 'makanan',
  price: 0,
  stock: null,
  is_available: true,
})

const filteredProducts = computed(() => {
  // If we are filtering, we might typically do it on backend or frontend. 
  // Since we paginate, backend filtering is better.
  // But for now, if the user requested simple pagination, let's assume backend returns paginated data.
  // If we filter locally on paginated data it will be weird (only filtering current page).
  // So ideally we pass filters to the API.
  return products.value
})

const fetchProducts = async (page = 1) => {
  loading.value = true
  currentPage.value = page
  try {
    const response = await axios.get('/api/products', {
        params: { 
            page,
            category: filterCategory.value || undefined
        }
    })
    products.value = response.data.data.data
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
  } catch (error) {
    console.error('Failed to fetch products:', error)
  } finally {
    loading.value = false
  }
}

// Watch category change to refetch
import { watch } from 'vue'
watch(filterCategory, () => {
    fetchProducts(1)
})

const handlePageChange = (page) => {
    fetchProducts(page)
}

const handleEdit = (product) => {
  editingProduct.value = product
  form.value = {
    name: product.name,
    category: product.category,
    price: product.price,
    stock: product.stock,
    is_available: product.is_available,
  }
  showDialog.value = true
}

const handleSubmit = async () => {
  loading.value = true
  try {
    if (editingProduct.value) {
      await axios.put(`/api/products/${editingProduct.value.id}`, form.value)
      notify.success('Product updated successfully!')
    } else {
      await axios.post('/api/products', form.value)
      notify.success('Product created successfully!')
    }
    
    showDialog.value = false
    resetForm()
    fetchProducts()
  } catch (error) {
    notify.error(error.response?.data?.message || 'Failed to save product')
  } finally {
    loading.value = false
  }
}

const handleDelete = async (product) => {
  const confirmed = await confirm.show({
    title: 'Delete Product',
    message: `Are you sure you want to delete ${product.name}?`,
    confirmText: 'Delete',
    type: 'danger'
  })

  if (!confirmed) return

  try {
    await axios.delete(`/api/products/${product.id}`)
    notify.success('Product deleted successfully!')
    fetchProducts()
  } catch (error) {
    notify.error(error.response?.data?.message || 'Failed to delete product')
  }
}

const resetForm = () => {
  form.value = {
    name: '',
    category: 'makanan',
    price: 0,
    stock: null,
    is_available: true,
  }
  editingProduct.value = null
}

onMounted(() => {
  fetchProducts()
})
</script>
