<?php
return [
    'transitions' => [
        'expert' => [
            'from' => ['تأیید اولیه'],
            'to'   => ['بارگذاری مدارک به سازمان', 'لغو'],
        ],
        'document_uploader' => [
            'from' => ['بارگذاری مدارک به سازمان'],
            'to'   => ['صدور مجوز', 'لغو'],
        ],
        'admin' => [
            'from' => [], // admin can change any status
            'to'   => [],
        ],
    ],
    // برای نمایش وظایف هر نقش، درخواست‌های دارای وضعیت from
];