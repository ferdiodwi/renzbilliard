import { defineStore } from 'pinia';
import axios from 'axios';
import { useNotificationStore } from './notification';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.role === 'admin',
        isKasir: (state) => state.user?.role === 'kasir',
        userName: (state) => state.user?.name || '',
        permissions: (state) => {
            // Get permissions based on role
            const role = state.user?.role;
            if (!role) return [];

            // This matches the backend permissions config
            const permissionMap = {
                admin: [
                    'view-users', 'create-users', 'edit-users', 'delete-users',
                    'view-tables', 'create-tables', 'edit-tables', 'delete-tables',
                    'view-rates', 'create-rates', 'edit-rates', 'delete-rates',
                    'view-sessions', 'create-sessions', 'edit-sessions', 'delete-sessions',
                    'view-all-transactions', 'create-transactions',
                    'view-reports', 'manage-settings',
                ],
                kasir: [
                    'view-tables', 'view-rates',
                    'view-sessions', 'create-sessions', 'edit-sessions',
                    'view-own-transactions', 'create-transactions',
                    'view-own-reports',
                ],
            };

            return permissionMap[role] || [];
        },
        hasPermission: (state) => (permission) => {
            const role = state.user?.role;
            if (!role) return false;

            const permissionMap = {
                admin: [
                    'view-users', 'create-users', 'edit-users', 'delete-users',
                    'view-tables', 'create-tables', 'edit-tables', 'delete-tables',
                    'view-rates', 'create-rates', 'edit-rates', 'delete-rates',
                    'view-sessions', 'create-sessions', 'edit-sessions', 'delete-sessions',
                    'view-all-transactions', 'create-transactions',
                    'view-reports', 'manage-settings',
                ],
                kasir: [
                    'view-tables', 'view-rates',
                    'view-sessions', 'create-sessions', 'edit-sessions',
                    'view-own-transactions', 'create-transactions',
                    'view-own-reports',
                ],
            };

            const permissions = permissionMap[role] || [];
            return permissions.includes(permission);
        },
    },

    actions: {
        async login(username, password) {
            try {
                const response = await axios.post('/api/login', {
                    username,
                    password,
                });

                if (response.data.success) {
                    this.token = response.data.data.token;
                    this.user = response.data.data.user;

                    localStorage.setItem('token', this.token);
                    localStorage.setItem('user', JSON.stringify(this.user));

                    axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                    return { success: true };
                }
            } catch (error) {
                return {
                    success: false,
                    message: error.response?.data?.message || 'Login gagal',
                };
            }
        },

        async logout() {
            try {
                await axios.post('/api/logout');
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                delete axios.defaults.headers.common['Authorization'];
            }
        },

        initAuth(router) {
            if (this.token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            }

            let isRedirecting = false;

            // Add response interceptor to handle 401
            axios.interceptors.response.use(
                (response) => response,
                async (error) => {
                    if (error.response?.status === 401) {
                        if (isRedirecting) return Promise.reject(error);

                        isRedirecting = true;

                        // Token expired or invalid (e.g. logged in on another device)
                        const notify = useNotificationStore();
                        notify.error('Sesi Anda telah berakhir. Silakan login kembali.');

                        await this.logout();

                        // Delay slightly to let toast render, then force hard redirect
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 1000);
                    }
                    return Promise.reject(error);
                }
            );
        },
    },
});
