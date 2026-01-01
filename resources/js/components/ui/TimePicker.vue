<template>
  <div class="relative" ref="containerRef">
    <!-- Input Display -->
    <button
      type="button"
      @click="toggleDropdown"
      class="w-full px-4 py-2 text-left border rounded-lg focus:ring-2 focus:ring-brand-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-pointer flex items-center justify-between"
      :class="{ 'border-brand-500 ring-2 ring-brand-500': isOpen }"
    >
      <span :class="modelValue ? 'text-gray-800 dark:text-white' : 'text-gray-400'">
        {{ modelValue || placeholder }}
      </span>
      <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </button>

    <!-- Dropdown -->
    <Teleport to="body">
      <div
        v-if="isOpen"
        ref="dropdownRef"
        class="fixed z-[9999] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl p-4"
        :style="dropdownStyle"
      >
        <div class="flex gap-4">
          <!-- Hours -->
          <div class="flex flex-col items-center">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Jam</span>
            <div class="h-48 overflow-y-auto custom-scrollbar">
              <div class="flex flex-col gap-1">
                <button
                  v-for="h in 24"
                  :key="h - 1"
                  type="button"
                  @click="selectHour(h - 1)"
                  class="w-12 px-3 py-2 text-center rounded-lg transition font-medium"
                  :class="selectedHour === (h - 1) 
                    ? 'bg-brand-500 text-white' 
                    : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300'"
                >
                  {{ String(h - 1).padStart(2, '0') }}
                </button>
              </div>
            </div>
          </div>

          <!-- Separator -->
          <div class="flex items-center text-2xl font-bold text-gray-400">:</div>

          <!-- Minutes -->
          <div class="flex flex-col items-center">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">Menit</span>
            <div class="h-48 overflow-y-auto custom-scrollbar">
              <div class="flex flex-col gap-1">
                <button
                  v-for="m in minuteOptions"
                  :key="m"
                  type="button"
                  @click="selectMinute(m)"
                  class="w-12 px-3 py-2 text-center rounded-lg transition font-medium"
                  :class="selectedMinute === m 
                    ? 'bg-brand-500 text-white' 
                    : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300'"
                >
                  {{ String(m).padStart(2, '0') }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Confirm Button -->
        <button
          type="button"
          @click="confirmSelection"
          class="w-full mt-4 px-4 py-2 bg-brand-500 text-white font-medium rounded-lg hover:bg-brand-600 transition"
        >
          Pilih {{ displayTime }}
        </button>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Pilih jam'
  },
  minuteStep: {
    type: Number,
    default: 5
  }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const containerRef = ref(null)
const dropdownRef = ref(null)
const selectedHour = ref(0)
const selectedMinute = ref(0)
const dropdownStyle = ref({})

// Generate minute options based on step
const minuteOptions = computed(() => {
  const options = []
  for (let i = 0; i < 60; i += props.minuteStep) {
    options.push(i)
  }
  return options
})

// Display time for button
const displayTime = computed(() => {
  return `${String(selectedHour.value).padStart(2, '0')}:${String(selectedMinute.value).padStart(2, '0')}`
})

// Parse modelValue on mount
watch(() => props.modelValue, (val) => {
  if (val) {
    const [h, m] = val.split(':').map(Number)
    selectedHour.value = h || 0
    selectedMinute.value = m || 0
  }
}, { immediate: true })

const updateDropdownPosition = () => {
  if (!containerRef.value) return
  
  const rect = containerRef.value.getBoundingClientRect()
  const viewportHeight = window.innerHeight
  const dropdownHeight = 320 // approximate height
  
  // Check if dropdown should open above or below
  const spaceBelow = viewportHeight - rect.bottom
  const openAbove = spaceBelow < dropdownHeight && rect.top > dropdownHeight
  
  dropdownStyle.value = {
    top: openAbove ? `${rect.top - dropdownHeight - 8}px` : `${rect.bottom + 8}px`,
    left: `${rect.left}px`,
    width: `${Math.max(rect.width, 200)}px`
  }
}

const toggleDropdown = async () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    await nextTick()
    updateDropdownPosition()
  }
}

const selectHour = (h) => {
  selectedHour.value = h
}

const selectMinute = (m) => {
  selectedMinute.value = m
}

const confirmSelection = () => {
  emit('update:modelValue', displayTime.value)
  isOpen.value = false
}

// Close on click outside
const handleClickOutside = (e) => {
  if (
    containerRef.value && !containerRef.value.contains(e.target) &&
    dropdownRef.value && !dropdownRef.value.contains(e.target)
  ) {
    isOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  window.addEventListener('resize', updateDropdownPosition)
  window.addEventListener('scroll', updateDropdownPosition, true)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  window.removeEventListener('resize', updateDropdownPosition)
  window.removeEventListener('scroll', updateDropdownPosition, true)
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
