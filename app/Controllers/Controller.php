<?php
/**
 * کنترلر پایه - شامل متدهای کمکی برای رندر و هدایت
 */
class Controller
{
    /**
     * رندر یک نما با قالب اصلی
     * @param string $view مسیر نسبی فایل نما از پوشه Views
     * @param array $data داده‌های ارسالی به نما
     * @param string $layout نام فایل قالب (بدون پسوند .php)
     */
    public function render(string $view, array $data = [], string $layout = 'layout'): void
    {
        // استخراج داده‌ها به متغیرهای مستقل
        extract($data);

        // دریافت خروجی نما
        ob_start();
        $viewPath = VIEW_PATH . '/' . ltrim($view, '/') . '.php';
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }
        require $viewPath;
        $content = ob_get_clean();

        // رندر قالب اصلی
        $layoutPath = VIEW_PATH . '/' . $layout . '.php';
        if (!file_exists($layoutPath)) {
            throw new \RuntimeException("Layout file not found: {$layoutPath}");
        }
        // متغیرهای قابل استفاده در قالب
        $title = $data['title'] ?? 'سامانه مجوز صنایع دستی';
        require $layoutPath;
    }

    /**
     * هدایت به یک مسیر
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . BASE_URL . ltrim($url, '/'));
        exit;
    }

    /**
     * برگرداندن پاسخ JSON
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * بررسی درخواست POST
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * دریافت مقدار یک فیلد از درخواست
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * دریافت تمام ورودی‌ها
     */
    protected function allInputs(): array
    {
        return $_POST;
    }
}