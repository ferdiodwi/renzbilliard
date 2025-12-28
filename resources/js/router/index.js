import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/LoginPage.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('@/pages/MainLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('@/pages/DashboardPage.vue'),
            },
            {
                path: 'tables',
                name: 'tables',
                component: () => import('@/pages/TablesPage.vue'),
            },
            {
                path: 'rates',
                name: 'rates',
                component: () => import('@/pages/RatesPage.vue'),
                meta: { adminOnly: true },
            },
            {
                path: 'sessions',
                name: 'sessions',
                component: () => import('@/pages/SessionsPage.vue'),
            },
            {
                path: 'transactions',
                name: 'transactions',
                component: () => import('@/pages/TransactionsPage.vue'),
            },
            {
                path: 'reports',
                name: 'reports',
                component: () => import('@/pages/ReportsPage.vue'),
            },
            {
                path: 'profile',
                name: 'profile',
                component: () => import('@/pages/ProfilePage.vue'),
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('@/pages/SettingsPage.vue'),
            },
            {
                path: 'users',
                name: 'users',
                component: () => import('@/pages/UsersPage.vue'),
                meta: { adminOnly: true },
            },
            {
                path: 'products',
                name: 'products',
                component: () => import('@/pages/ProductsPage.vue'),
                meta: { adminOnly: true },
            },
            {
                path: 'pos',
                name: 'pos',
                component: () => import('@/pages/PosPage.vue'),
            },
            {
                path: 'orders',
                name: 'orders',
                component: () => import('@/pages/OrdersPage.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'dashboard' });
    } else if (to.meta.adminOnly && !authStore.isAdmin) {
        next({ name: 'dashboard' });
    } else {
        next();
    }
});

export default router;
