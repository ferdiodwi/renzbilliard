import { ref, computed, onMounted, onUnmounted, provide, inject } from 'vue'

const SidebarSymbol = Symbol()

export function useSidebarProvider() {
    const isExpanded = ref(true)
    const isMobileOpen = ref(false)
    const isMobile = ref(false)
    const isHovered = ref(false)
    const activeItem = ref(null)
    const openSubmenu = ref(['Operasional Biliar', 'F&B', 'Pengaturan'])

    const handleResize = () => {
        const mobile = window.innerWidth < 1024 // Changed from 768 to 1024
        isMobile.value = mobile
        if (!mobile) {
            isMobileOpen.value = false
        }
    }

    onMounted(() => {
        handleResize()
        window.addEventListener('resize', handleResize)
    })

    onUnmounted(() => {
        window.removeEventListener('resize', handleResize)
    })

    const toggleSidebar = () => {
        if (isMobile.value) {
            isMobileOpen.value = !isMobileOpen.value
        } else {
            isExpanded.value = !isExpanded.value
        }
    }

    const toggleMobileSidebar = () => {
        isMobileOpen.value = !isMobileOpen.value
    }

    const setIsHovered = (value) => {
        isHovered.value = value
    }

    const setActiveItem = (item) => {
        activeItem.value = item
    }

    const toggleSubmenu = (item) => {
        const index = openSubmenu.value.indexOf(item)
        if (index > -1) {
            openSubmenu.value.splice(index, 1) // Close if open
        } else {
            openSubmenu.value.push(item) // Open if closed
        }
    }

    const context = {
        isExpanded: computed(() => (isMobile.value ? false : isExpanded.value)),
        isMobileOpen,
        isMobile,
        isHovered,
        activeItem,
        openSubmenu,
        toggleSidebar,
        toggleMobileSidebar,
        setIsHovered,
        setActiveItem,
        toggleSubmenu,
    }

    provide(SidebarSymbol, context)

    return context
}

export function useSidebar() {
    const context = inject(SidebarSymbol)
    if (!context) {
        throw new Error(
            'useSidebar must be used within a component that has SidebarProvider as an ancestor',
        )
    }
    return context
}
