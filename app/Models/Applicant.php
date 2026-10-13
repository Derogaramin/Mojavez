<?php
/**
 * مدل متقاضیان
 */
class Applicant
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * ایجاد متقاضی جدید
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO applicants 
                (first_name, last_name, national_code, postal_code, mobile, 
                birth_date, father_name, marital_status, children_count, 
                physical_condition, disability_details, income_decile, 
                has_court_case, has_active_loan, has_overdue_installments, 
                tracking_code, current_status, registration_started_at)
            VALUES 
                (:first_name, :last_name, :national_code, :postal_code, :mobile,
                :birth_date, :father_name, :marital_status, :children_count,
                :physical_condition, :disability_details, :income_decile,
                :has_court_case, :has_active_loan, :has_overdue_installments,
                :tracking_code, 'پیش‌نویس', NOW())"
        );

        $stmt->execute([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'national_code' => $data['national_code'],
            'postal_code' => $data['postal_code'],
            'mobile' => $data['mobile'],
            'birth_date' => $data['birth_date'] ?? null,
            'father_name' => $data['father_name'] ?? null,
            'marital_status' => $data['marital_status'] ?? 'مجرد',
            'children_count' => $data['children_count'] ?? 0,
            'physical_condition' => $data['physical_condition'] ?? 'سالم',
            'disability_details' => $data['disability_details'] ?? null,
            'income_decile' => $data['income_decile'] ?? null,
            'has_court_case' => $data['has_court_case'] ?? 'خیر',
            'has_active_loan' => $data['has_active_loan'] ?? 'خیر',
            'has_overdue_installments' => $data['has_overdue_installments'] ?? 'خیر',
            'tracking_code' => $data['tracking_code']
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * به‌روزرسانی وضعیت و تکمیل ثبت‌نام
     */
    public function completeRegistration(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE applicants SET current_status = 'تکمیل اطلاعات', registration_completed_at = NOW(), updated_at = NOW() WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);
    }

    /**
     * یافتن با کد رهگیری
     */
    public function findByTrackingCode(string $code): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM applicants WHERE tracking_code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        return $stmt->fetch() ?: null;
    }

    /**
     * یافتن با شناسه
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM applicants WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * به‌روزرسانی وضعیت
     */
    public function updateStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare("UPDATE applicants SET current_status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['status' => $status, 'id' => $id]);
    }

    /**
     * دریافت لیست متقاضیان با فیلتر و مرتب‌سازی
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT a.*, j.license_title, p.province, p.city, p.village 
                FROM applicants a
                LEFT JOIN job_details j ON a.id = j.applicant_id
                LEFT JOIN property_details p ON a.id = p.applicant_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['license_title'])) {
            $sql .= " AND j.license_title LIKE :license_title";
            $params['license_title'] = '%' . $filters['license_title'] . '%';
        }
        if (!empty($filters['village'])) {
            $sql .= " AND p.village LIKE :village";
            $params['village'] = '%' . $filters['village'] . '%';
        }
        if (!empty($filters['city'])) {
            $sql .= " AND p.city LIKE :city";
            $params['city'] = '%' . $filters['city'] . '%';
        }
        if (!empty($filters['tracking_code'])) {
            $sql .= " AND tracking_code LIKE :tracking_code";
            $params['tracking_code'] = '%' . $filters['tracking_code'] . '%';
        }
        if (!empty($filters['name'])) {
            $sql .= " AND (first_name LIKE :name OR last_name LIKE :name2)";
            $params['name'] = '%' . $filters['name'] . '%';
            $params['name2'] = '%' . $filters['name'] . '%';
        }
        if (!empty($filters['national_code'])) {
            $sql .= " AND national_code LIKE :national_code";
            $params['national_code'] = '%' . $filters['national_code'] . '%';
        }
        if (!empty($filters['mobile'])) {
            $sql .= " AND mobile LIKE :mobile";
            $params['mobile'] = '%' . $filters['mobile'] . '%';
        }
        if (!empty($filters['province'])) {
            $sql .= " AND id IN (SELECT applicant_id FROM property_details WHERE province LIKE :province)";
            $params['province'] = '%' . $filters['province'] . '%';
        }
        if (!empty($filters['status'])) {
            $sql .= " AND current_status = :status";
            $params['status'] = $filters['status'];
        }

        $sql .= " ORDER BY a.priority DESC, a.registration_completed_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * به‌روزرسانی پرچم اولویت
     */
    public function updatePriority(int $id, int $priority): void
    {
        $stmt = $this->db->prepare("UPDATE applicants SET priority = :priority WHERE id = :id");
        $stmt->execute(['priority' => $priority, 'id' => $id]);
    }

    /**
     * به‌روزرسانی کد پستی
     */
    public function updatePostalCode(int $id, string $postalCode): void
    {
        $stmt = $this->db->prepare("UPDATE applicants SET postal_code = :postal_code, updated_at = NOW() WHERE id = :id");
        $stmt->execute(['postal_code' => $postalCode, 'id' => $id]);
    }

    /**
     * یافتن درخواست‌های قابل لغو (بیش از ۱۴ روز از ثبت درخواست صدور)
     */
    public function findExpiredApplications(int $days): array
    {
        $sql = "SELECT a.* FROM applicants a
                INNER JOIN status_log sl ON a.id = sl.applicant_id
                WHERE sl.status_name = 'ثبت درخواست صدور'
                AND a.current_status IN ('پیش‌نویس','تکمیل اطلاعات','احراز نشانی','ثبت درخواست صدور','بارگذاری مدارک به سازمان','تأیید اولیه')
                AND a.current_status != 'لغو'
                AND sl.created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['days' => $days]);
        return $stmt->fetchAll();
    }

    /**
     * تولید کد رهگیری یکتا
     */
    public static function generateTrackingCode(): string
    {
        $prefix = 'HG';
        $unique = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $code = $prefix . '-' . $unique;

        // بررسی یکتایی
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM applicants WHERE tracking_code = :code");
        $stmt->execute(['code' => $code]);

        if ($stmt->fetchColumn() > 0) {
            return self::generateTrackingCode(); // تلاش مجدد
        }

        return $code;
    }
}