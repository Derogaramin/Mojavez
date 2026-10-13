<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? 'پرونده چاپی') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/font.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <style>
        body { background: #fff; font-family: 'Vazirmatn', Tahoma, sans-serif; }
        .print-container { max-width: 210mm; margin: 0 auto; padding: 15mm; }
        @media print {
            .print-container { padding: 0; margin: 0; max-width: none; }
        }
    </style>
    <style>
        .print-container { max-width: 210mm; margin: 0 auto; padding: 15mm; font-family: 'Vazirmatn', Tahoma, sans-serif; }
        .print-header { margin-bottom: 20px; }
        .section-title { background: #eee; padding: 5px 10px; border-radius: 5px; margin-top: 30px; font-weight: bold; }
        .print-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .print-table td, .print-table th { border: 1px solid #999; padding: 6px 8px; }
        .print-table td:first-child { width: 35%; font-weight: bold; background: #f9f9f9; }
        .page-break { page-break-before: always; }
        @media print {
            .print-container { padding: 0; margin: 0; max-width: none; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body>
    <?= $content ?>
</body>
</html>