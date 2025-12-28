<?php

return [
    'admin' => [
        // Users
        'view-users',
        'create-users',
        'edit-users',
        'delete-users',
        
        // Tables
        'view-tables',
        'create-tables',
        'edit-tables',
        'delete-tables',
        
        // Rates
        'view-rates',
        'create-rates',
        'edit-rates',
        'delete-rates',
        
        // Sessions
        'view-sessions',
        'create-sessions',
        'edit-sessions',
        'delete-sessions',
        
        // Transactions
        'view-all-transactions',
        'create-transactions',
        
        // Products
        'view-products',
        'create-products',
        'edit-products',
        'delete-products',
        
        // Reports
        'view-reports',
        
        // Settings
        'manage-settings',
    ],
    
    'kasir' => [
        // Tables (read-only)
        'view-tables',
        
        // Rates (read-only)
        'view-rates',
        
        // Sessions
        'view-sessions',
        'create-sessions',
        'edit-sessions',
        
        // Transactions
        'view-own-transactions',
        'create-transactions',
        
        // Products (read-only)
        'view-products',
        
        // Reports (limited)
        'view-own-reports',
    ],
];
