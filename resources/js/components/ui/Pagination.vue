<template>
  <div v-if="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 dark:bg-gray-800 dark:border-gray-700 sm:px-6">
    <!-- Mobile View -->
    <div class="flex justify-between flex-1 sm:hidden">
      <button 
        @click="changePage(meta.current_page - 1)" 
        :disabled="meta.current_page === 1"
        class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
      >
        Previous
      </button>
      <button 
        @click="changePage(meta.current_page + 1)" 
        :disabled="meta.current_page === meta.last_page"
        class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600"
      >
        Next
      </button>
    </div>

    <!-- Desktop View -->
    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700 dark:text-gray-300">
          Showing
          <span class="font-medium">{{ meta.from || 0 }}</span>
          to
          <span class="font-medium">{{ meta.to || 0 }}</span>
          of
          <span class="font-medium">{{ meta.total || 0 }}</span>
          results
        </p>
      </div>
      <div>
        <nav class="inline-flex -space-x-px rounded-md shadow-sm isolate" aria-label="Pagination">
          <!-- Previous -->
          <button 
            @click="changePage(meta.current_page - 1)"
            :disabled="meta.current_page === 1"
            class="relative inline-flex items-center px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 rounded-l-md disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:ring-gray-600 dark:hover:bg-gray-600"
          >
            <span class="sr-only">Previous</span>
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
            </svg>
          </button>
          
          <!-- Page Numbers -->
          <template v-for="(link, index) in links" :key="index">
            <!-- Render standard page links -->
            <button 
                v-if="!link.label.includes('Previous') && !link.label.includes('Next')"
                @click="changePageLink(link.url)"
                :class="[
                    link.active ? 'z-10 bg-brand-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 dark:bg-brand-500' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0 dark:text-gray-300 dark:ring-gray-600 dark:bg-gray-700 dark:hover:bg-gray-600',
                    'relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20'
                ]"
                v-html="link.label"
            />
          </template>

          <!-- Next -->
          <button 
            @click="changePage(meta.current_page + 1)"
            :disabled="meta.current_page === meta.last_page"
            class="relative inline-flex items-center px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 rounded-r-md disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:border-gray-600 dark:ring-gray-600 dark:hover:bg-gray-600"
          >
            <span class="sr-only">Next</span>
            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
            </svg>
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  links: {
    type: Array,
    required: true
  },
  meta: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['page-change'])

const changePage = (page) => {
    if (page >= 1 && page <= props.meta.last_page) {
        emit('page-change', page)
    }
}

const changePageLink = (url) => {
    if (url) {
        // Extract page number from URL if needed, depending on how API is called.
        // Usually assuming standard Laravel Pagination URL formatting ?page=X
        const urlParams = new URL(url).searchParams
        const page = urlParams.get('page')
        if (page) {
            emit('page-change', parseInt(page))
        }
    }
}
</script>
