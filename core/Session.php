<?php
/**
 * کلاس مدیریت نشست‌ها
 */
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    /**
     * دریافت تمام داده‌های یک مرحله از ویزارد
     */
    public static function getStep(string $step, mixed $default = []): mixed
    {
        return $_SESSION['registration'][$step] ?? $default;
    }

    /**
     * ذخیره داده‌های یک مرحله از ویزارد
     */
    public static function setStep(string $step, mixed $data): void
    {
        $_SESSION['registration'][$step] = $data;
    }

    /**
     * پاکسازی داده‌های ویزارد
     */
    public static function clearRegistration(): void
    {
        unset($_SESSION['registration']);
    }
}