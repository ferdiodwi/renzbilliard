<template>
  <aside
    class="fixed left-0 top-0 z-50 h-screen w-[290px] border-r bg-white text-gray-900 transition-transform duration-300 dark:bg-gray-900 dark:text-white"
    :class="{
      '-translate-x-full lg:translate-x-0 lg:w-[90px]': !isExpanded && !isHovered && !isMobileOpen,
      'w-[290px] translate-x-0': isExpanded || isMobileOpen || isHovered,
      '-translate-x-full': !isMobileOpen && isMobile,
      'border-gray-200 dark:border-gray-800': true,
    }"
    @mouseenter="!isMobile && !isExpanded && setIsHovered(true)"
    @mouseleave="!isMobile && setIsHovered(false)"
  >
    <div
      class="border-b border-gray-200 px-5 py-[18px] dark:border-gray-800"
      :class="{ 'flex items-center justify-center': !isExpanded && !isHovered && !isMobileOpen }"
    >
      <router-link to="/" class="flex items-center gap-3">
        <!-- Logo Icon -->
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 shrink-0">
          <span class="text-xl font-bold text-white">R</span>
        </div>
        <!-- Logo Text -->
        <div v-if="isExpanded || isHovered || isMobileOpen" class="flex flex-col">
          <span class="text-lg font-bold text-gray-900 dark:text-white leading-tight">RenzBilliard</span>
          <span class="text-xs text-gray-500 dark:text-gray-400">Billing System</span>
        </div>
      </router-link>
    </div>

    <div
      class="overflow-y-auto px-5 pt-8 custom-scrollbar h-[calc(100vh-80px)]"
      :class="isExpanded || isHovered || isMobileOpen ? 'space-y-8' : 'space-y-4'"
    >
      <div v-for="(group, groupIndex) in menuGroups" :key="groupIndex">
        <h3
          v-if="isExpanded || isHovered || isMobileOpen"
          class="pb-5 pl-3 mb-4 text-xs font-semibold uppercase text-gray-400 dark:text-gray-500"
        >
          {{ group.name }}
        </h3>
        <ul class="flex flex-col gap-2">
          <li v-for="(item, itemIndex) in group.menuItems" :key="itemIndex">
            <!-- Skip admin-only items if user is not admin -->
            <template v-if="!item.adminOnly || authStore.isAdmin">
              <template v-if="!item.children">
              <router-link
                :to="item.path"
                class="relative flex items-center h-10 gap-3 px-3 py-2 font-medium transition-colors rounded-lg group"
                :class="{
                  'bg-brand-500 text-white hover:bg-brand-600': isActive(item.path),
                  'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white': !isActive(item.path),
                  'justify-center': !isExpanded && !isHovered && !isMobileOpen,
                }"
              >
                <span class="flex items-center justify-center w-6 h-6">
                  <component :is="item.icon" />
                </span>
                <span v-if="isExpanded || isHovered || isMobileOpen" class="truncate">{{ item.name }}</span>
                <span
                  v-if="item.label"
                  class="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-500 px-1 text-[10px] font-medium text-white"
                >
                  {{ item.label }}
                </span>
              </router-link>
            </template>
            <template v-else>
              <div class="relative">
                <button
                  @click.prevent="handleToggle(item)"
                  class="flex w-full items-center justify-between h-10 gap-3 px-3 py-2 font-medium transition-colors rounded-lg group"
                  :class="{
                    'bg-brand-500 text-white hover:bg-brand-600': isSubActive(item),
                    'text-gray-700 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white': !isSubActive(item),
                    'justify-center': !isExpanded && !isHovered && !isMobileOpen,
                  }"
                >
                  <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-6 h-6">
                      <component :is="item.icon" />
                    </span>
                    <span v-if="isExpanded || isHovered || isMobileOpen" class="truncate">{{ item.name }}</span>
                  </div>
                  <ChevronDownIcon
                    v-if="isExpanded || isHovered || isMobileOpen"
                    class="w-5 h-5 transition-transform duration-200"
                    :class="{ 'rotate-180': isOpen(item) }"
                  />
                </button>
                <ul
                  v-if="(isExpanded || isHovered || isMobileOpen) && isOpen(item)"
                  class="pl-10 mt-1 space-y-1"
                >
                  <template v-for="(subItem, subIndex) in item.children" :key="subIndex">
                    <li v-if="!subItem.adminOnly || authStore.isAdmin">
                      <router-link
                        :to="subItem.path"
                        class="flex items-center h-9 px-3 text-sm font-medium transition-colors rounded-lg"
                      :class="{
                        'text-brand-600 dark:text-brand-400 bg-brand-50/50 dark:bg-brand-900/10': isActive(subItem.path),
                        'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800': !isActive(subItem.path)
                      }"
                    >
                      <span
                        class="mr-2 h-1.5 w-1.5 rounded-full border border-current"
                        :class="isActive(subItem.path) ? 'bg-current' : 'bg-transparent'"
                      ></span>
                        {{ subItem.name }}
                        <span
                          v-if="subItem.pro"
                          class="ml-auto rounded-md bg-brand-100 px-1.5 py-0.5 text-[10px] font-medium leading-3 text-brand-500 dark:bg-brand-500/15 dark:text-brand-400"
                        >
                          Pro
                        </span>
                        <span
                          v-if="subItem.badge > 0"
                          class="ml-auto flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1 text-[10px] font-bold text-white shadow-sm ring-1 ring-white dark:ring-gray-900"
                        >
                          {{ subItem.badge }}
                        </span>
                      </router-link>
                    </li>
                  </template>
                </ul>
              </div>
            </template>
            </template>
          </li>
        </ul>
      </div class="mt-auto">
       <SidebarWidget v-if="isExpanded || isHovered" />
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import { useSidebar } from '@/composables/useSidebar'
import { useAuthStore } from '@/stores/auth'
import { useOrderStore } from '@/stores/order'
import SidebarWidget from './SidebarWidget.vue'
import {
  GridIcon,
  CalenderIcon,
  TableIcon,
  DocsIcon,
  BarChartIcon,
  PieChartIcon,
  UserCircleIcon,
  BoxCubeIcon,
  PlusIcon,
  ListIcon,
  ChevronDownIcon,
  SettingsIcon,
} from '@/icons'

const {
  isExpanded,
  isHovered,
  isMobileOpen,
  isMobile,
  openSubmenu,
  toggleSidebar,
  setIsHovered,
  toggleSubmenu,
} = useSidebar()
const route = useRoute()
const authStore = useAuthStore()
const orderStore = useOrderStore()
let pollingInterval = null

const menuGroups = computed(() => [
  {
    name: 'MENU UTAMA',
    menuItems: [
      {
        icon: GridIcon,
        name: 'Dashboard',
        path: '/',
        adminOnly: true,
      },
      {
        icon: TableIcon,
        name: 'Operasional Biliar',
        path: '/tables', // Fallback path, though it has children
        children: [
            {
                name: 'Meja Biliar',
                path: '/tables',
            },
            {
                name: 'Booking Meja',
                path: '/bookings',
            },
            {
                name: 'Transaksi Biliar',
                path: '/transactions',
            },
        ]
      },
      {
        icon: BoxCubeIcon,
        name: 'F&B',
        path: '/pos',
        children: [
            {
                name: 'Kasir F&B',
                path: '/pos',
            },
            {
                name: 'Produk',
                path: '/products',
                adminOnly: true,
            },
            {
                name: 'Kategori',
                path: '/categories',
                adminOnly: true,
            },
            {
                name: 'Riwayat Pesanan',
                path: '/orders',
                badge: orderStore.pendingCount,
            },
        ]
      },
      {
        icon: BarChartIcon,
        name: 'Keuangan',
        path: '/income',
        adminOnly: true,
        children: [
            {
                name: 'Pemasukan',
                path: '/income',
            },
            {
                name: 'Pengeluaran',
                path: '/expenses',
            },
            {
                name: 'Laporan',
                path: '/reports',
            },
        ]
      },
      {
        icon: SettingsIcon,
        name: 'Pengaturan',
        path: '/rates',
        adminOnly: true,
        children: [
            {
                name: 'Tarif Biliar',
                path: '/rates',
            },
            {
                name: 'Manajemen Pengguna',
                path: '/users',
                adminOnly: true,
            },
        ]
      },
    ],
  },
])

onMounted(() => {
  orderStore.fetchPendingCount()
})

const isActive = (path) => route.path === path
const isSubActive = (item) => {
  if (!item.children) return false
  return item.children.some((child) => isActive(child.path))
}
const isOpen = (item) => openSubmenu.value.includes(item.name)

const handleToggle = (item) => {
  toggleSubmenu(item.name)
}
</script>
