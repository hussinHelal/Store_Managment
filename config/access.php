<?php

return [
    'pages' => [
        'dashboard' => 'لوحة التحكم',
        'categories' => 'الأصناف',
        'products' => 'المنتجات',
        'maintenance' => 'الصيانة',
        'sales' => 'المبيعات',
        'invoices' => 'الفواتير',
        'customers' => 'العملاء',
        'suppliers' => 'الموردون والحسابات الدائنة',
        'installments' => 'الأقساط والديون',
        'staff' => 'الموظفون',
        'profile' => 'الملف الشخصي',
        'settings' => 'الإعدادات',
        'backup' => 'النسخ الاحتياطي',
        'notifications' => 'الإشعارات',
        'damaged' => 'هالك',
        'wallets' => 'المحافظ',
        'wallet_cashier' => 'كاشير المحافظ',
    ],
    'descriptions' => [
        'wallets' => [
            'view' => 'عرض المحافظ والمصروفات والتقارير.',
            'manage' => 'إضافة المحافظ وتعديلها، وضبط الأرصدة، وإدارة المصروفات وعكس المعاملات.',
        ],
        'wallet_cashier' => [
            'view' => 'عرض شاشة كاشير المحافظ والديون.',
            'manage' => 'تسجيل المعاملات وتحصيل المبالغ الآجلة.',
        ],
        'damaged' => [
            'view' => 'عرض سجلات الهالك والتقرير.',
            'manage' => 'تسجيل الهالك وإلغاء قيد المخزون التالف.',
        ],
    ],
    'superadmin_only' => ['assign-roles'],
];
