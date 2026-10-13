<?php
/**
 * کلاس اعتبارسنجی داده‌ها
 */
class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * افزودن خطای دلخواه به یک فیلد
     */
    public function addError(string $field, string $message): self
    {
        $this->errors[$field][] = $message;
        return $this;
    }

    /**
     * اعتبارسنجی فیلد الزامی
     */
    public function required(string $field, string $label = ''): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->errors[$field][] = ($label ?: $field) . ' الزامی است.';
        }
        return $this;
    }

    /**
     * اعتبارسنجی حداقل طول
     */
    public function minLength(string $field, int $min, string $label = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && mb_strlen($value) < $min) {
            $this->errors[$field][] = ($label ?: $field) . " نباید کمتر از {$min} کاراکتر باشد.";
        }
        return $this;
    }

    /**
     * اعتبارسنجی حداکثر طول
     */
    public function maxLength(string $field, int $max, string $label = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (mb_strlen($value) > $max) {
            $this->errors[$field][] = ($label ?: $field) . " نباید بیشتر از {$max} کاراکتر باشد.";
        }
        return $this;
    }

    /**
     * اعتبارسنجی فقط حروف فارسی و فاصله
     */
    public function persianAlpha(string $field, string $label = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (!empty($value) && !preg_match('/^[\x{0600}-\x{06FF}\s]+$/u', $value)) {
            $this->errors[$field][] = ($label ?: $field) . ' فقط باید شامل حروف فارسی و فاصله باشد.';
        }
        return $this;
    }

    /**
     * اعتبارسنجی کد ملی (دقیقاً ۱۰ رقم)
     */
    public function nationalCode(string $field, string $label = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (!preg_match('/^\d{10}$/', $value)) {
            $this->errors[$field][] = ($label ?: $field) . ' باید دقیقاً ۱۰ رقم باشد.';
        }
        return $this;
    }

    /**
     * اعتبارسنجی کد پستی (دقیقاً ۱۰ رقم)
     */
    public function postalCode(string $field, string $label = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (!preg_match('/^\d{10}$/', $value)) {
            $this->errors[$field][] = ($label ?: $field) . ' باید دقیقاً ۱۰ رقم باشد.';
        }
        return $this;
    }

    /**
     * اعتبارسنجی شماره موبایل (با ۰۹ شروع شود، ۱۱ رقم)
     */
    public function mobile(string $field, string $label = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (!preg_match('/^09\d{9}$/', $value)) {
            $this->errors[$field][] = ($label ?: $field) . ' باید با ۰۹ شروع شده و ۱۱ رقم باشد.';
        }
        return $this;
    }

    /**
     * اعتبارسنجی عدد صحیح بزرگتر یا مساوی صفر
     */
    public function unsignedInt(string $field, string $label = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (!is_numeric($value) || (int)$value < 0 || (int)$value != $value) {
            $this->errors[$field][] = ($label ?: $field) . ' باید یک عدد صحیح بزرگتر یا مساوی صفر باشد.';
        }
        return $this;
    }

    /**
     * اعتبارسنجی مقدار Enum
     */
    public function enum(string $field, array $allowed, string $label = ''): self
    {
        $value = $this->data[$field] ?? '';
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field][] = ($label ?: $field) . ' مقدار معتبری نیست.';
        }
        return $this;
    }

    /**
     * بررسی یکتایی یک فیلد در جدول مشخص
     */
    public function unique(string $field, string $table, string $column, string $label = '', ?int $exceptId = null): self
    {
        $value = $this->data[$field] ?? '';
        if (empty($value)) return $this;

        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        if ($exceptId !== null) {
            $sql .= " AND id != :except_id";
        }
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':value', $value);
        if ($exceptId !== null) {
            $stmt->bindValue(':except_id', $exceptId, PDO::PARAM_INT);
        }
        $stmt->execute();

        if ($stmt->fetchColumn() > 0) {
            $this->errors[$field][] = ($label ?: $field) . ' قبلاً ثبت شده است.';
        }
        return $this;
    }

    /**
     * بررسی وجود خطا
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * دریافت تمام خطاها
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * دریافت خطاهای یک فیلد خاص
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * دریافت اولین خطا
     */
    public function firstError(): ?string
    {
        foreach ($this->errors as $fieldErrors) {
            return $fieldErrors[0] ?? null;
        }
        return null;
    }
}