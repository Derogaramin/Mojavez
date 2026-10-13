<?php
/**
 * کنترلر گزارش‌های PDF و CSV
 */
class ReportController extends Controller
{
    private Applicant $applicantModel;
    private JobDetail $jobDetailModel;
    private PropertyDetail $propertyDetailModel;
    private Document $documentModel;
    private StatusLog $statusLogModel;
    private PostalCodeChange $postalCodeChangeModel;

    public function __construct()
    {
        $this->applicantModel = new Applicant();
        $this->jobDetailModel = new JobDetail();
        $this->propertyDetailModel = new PropertyDetail();
        $this->documentModel = new Document();
        $this->statusLogModel = new StatusLog();
        $this->postalCodeChangeModel = new PostalCodeChange();
    }

    /**
     * بررسی دسترسی مدیر
     */
    private function checkAdmin(): void
    {
        if (!Session::get('admin_logged_in')) {
            $this->redirect('/admin/login');
        }
    }

    /**
     * خروجی CSV
     */
    public function csvReport(): void
    {
        $this->checkAdmin();
        $filters = $this->getFiltersFromRequest();

        $applicants = $this->applicantModel->getAll($filters);

        $headers = [
            'کد رهگیری', 'نام', 'نام خانوادگی', 'کد ملی', 'موبایل',
            'کد پستی', 'وضعیت', 'اولویت', 'تاریخ شروع', 'تاریخ تکمیل',
            'تاریخ تولد', 'نام پدر', 'تأهل', 'وضعیت جسمانی',
            'رشته', 'استان', 'شهر', 'روستا', 'وضعیت'];

        $rows = [];
        foreach ($applicants as $app) {
            $rows[] = [
                $app['tracking_code'],
                $app['first_name'],
                $app['last_name'],
                $app['national_code'],
                $app['mobile'],
                $app['postal_code'],
                $app['birth_date'] ?? '',
                $app['father_name'] ?? '',
                $app['marital_status'],
                $app['physical_condition'],
                $job['license_title'] ?? '',
                $prop['province'] ?? '',
                $prop['city'] ?? '',
                $prop['village'] ?? '',
                $app['current_status'],
                $app['current_status'],
                $app['priority'] ? 'بله' : 'خیر',
                $app['registration_started_at'] ?? '',
                $app['registration_completed_at'] ?? ''
            ];
        }

        CsvExporter::export('گزارش_درخواست‌ها', $headers, $rows);
    }

    /**
     * خروجی PDF (HTML ساده برای چاپ)
     */
    public function pdfReport(): void
    {
        $this->checkAdmin();
        $filters = $this->getFiltersFromRequest();
        $applicants = $this->applicantModel->getAll($filters);

        // پاک‌سازی خروجی
        if (ob_get_level()) {
            ob_end_clean();
        }

        // ساخت HTML
        $html = '<!DOCTYPE html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><title>گزارش درخواست‌ها</title>';

        if (USE_CDN){
            $html .= '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">';
        }
        else{
            $html .= '<link rel="stylesheet" href="'. BASE_URL . 'assets/bootstrap.rtl.min.css">';
        }

        $html .= '<style>body{font-family:Vazirmatn,Tahoma,sans-serif;padding:20px;} table{width:100%;border-collapse:collapse;} th,td{border:1px solid #999;padding:6px;text-align:right;}</style>';
        $html .= '</head><body>';
        $html .= '<h3 class="text-center mb-4">گزارش درخواست‌های مجوز صنایع دستی</h3>';
        $html .= '<p>تاریخ: ' . date('Y/m/d H:i') . '</p>';

        // جدول
        $html .= '<table><thead><tr>';
        $html .= '<th>کد رهگیری</th><th>نام</th><th>نام خانوادگی</th><th>کد ملی</th><th>موبایل</th><th>وضعیت</th><th>اولویت</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($applicants as $app) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($app['tracking_code']) . '</td>';
            $html .= '<td>' . htmlspecialchars($app['first_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($app['last_name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($app['national_code']) . '</td>';
            $html .= '<td>' . htmlspecialchars($app['mobile']) . '</td>';
            $html .= '<td>' . htmlspecialchars($app['current_status']) . '</td>';
            $html .= '<td>' . ($app['priority'] ? '⭐' : '') . '</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        $html .= '</body></html>';

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="report.html"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $html;
        exit;
    }

    public function byProvince(): void
    {
        $this->checkAdmin();
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT p.province, COUNT(*) as cnt FROM applicants a JOIN property_details p ON a.id=p.applicant_id GROUP BY p.province ORDER BY cnt DESC");
        $stats = $stmt->fetchAll();
        $this->renderStats('بر اساس استان', 'استان','province', $stats);
    }

    public function byCity(): void
    {
        $this->checkAdmin();
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT p.city, COUNT(*) as cnt FROM applicants a JOIN property_details p ON a.id=p.applicant_id GROUP BY p.city ORDER BY cnt DESC");
        $stats = $stmt->fetchAll();
        $this->renderStats('بر اساس شهر', 'شهر','city', $stats);
    }

    public function byVillage(): void
    {
        $this->checkAdmin();
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT p.village, COUNT(*) as cnt FROM applicants a JOIN property_details p ON a.id=p.applicant_id WHERE p.village IS NOT NULL GROUP BY p.village ORDER BY cnt DESC");
        $stats = $stmt->fetchAll();
        $this->renderStats('بر اساس روستا', 'روستا','village', $stats);
    }

    public function byLicense(): void
    {
        $this->checkAdmin();
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT j.license_title, COUNT(*) as cnt FROM job_details j JOIN applicants a ON j.applicant_id=a.id GROUP BY j.license_title ORDER BY cnt DESC");
        $stats = $stmt->fetchAll();
        $this->renderStats('بر اساس رشته', 'رشته','license_title', $stats);
    }

    public function byDate(): void
    {
        $this->checkAdmin();
        $pdo = Database::getInstance()->getConnection();
        $stmt = $pdo->query("SELECT DATE(registration_completed_at) as reg_date, COUNT(*) as cnt FROM applicants WHERE registration_completed_at IS NOT NULL GROUP BY DATE(registration_completed_at) ORDER BY reg_date DESC");
        $stats = $stmt->fetchAll();
        $this->renderStats('بر اساس تاریخ ثبت‌نام', 'تاریخ','reg_date', $stats);
    }

    private function renderStats(string $title, string $label, string $field , array $stats): void
    {
        $this->render('admin/report_stats', [
            'title' => $title,
            'label' => $label,
            'field' => $field,
            'stats' => $stats
        ], 'admin_layout');
    }

    // گزارش کلی (صفحه فیلتر + خروجی)
    public function generalReport(): void
    {
        $this->checkAdmin();
        $this->render('admin/output_general', ['title' => 'گزارش کلی', 'filters' => $_GET], 'admin_layout');
    }

    // گزارش متقاضی خاص
    public function singleApplicantReport(string $trackingCode): void
    {
        $this->checkAdmin();
        $applicant = $this->applicantModel->findByTrackingCode($trackingCode);
        if (!$applicant) {
            $this->redirect('/admin/dashboard');
        }
        // رندر یک ویوی قابل چاپ مشابه print.php
        $this->render('print', [
            'title' => 'پرونده متقاضی',
            'applicant' => $applicant,
            'jobDetail' => $this->jobDetailModel->findByApplicantId($applicant['id']),
            'propertyDetail' => $this->propertyDetailModel->findByApplicantId($applicant['id']),
            'documents' => $this->documentModel->findByApplicantId($applicant['id']),
            'statusLogs' => $this->statusLogModel->findByApplicantId($applicant['id']),
            'postalChanges' => $this->postalCodeChangeModel->findByApplicantId($applicant['id']),
        ], 'layout_print');
    }

    // فرم گزارش گروه‌بندی شده
    public function groupedReportForm(): void
    {
        $this->checkAdmin();
        $this->render('admin/output_grouped_form', ['title' => 'گزارش گروه‌بندی شده'], 'admin_layout');
    }

    public function groupedReportResult(): void
    {
        $this->checkAdmin();
        $groupBy = $_POST['group_by'] ?? 'province';
        $pdo = Database::getInstance()->getConnection();
        
        switch ($groupBy) {
            case 'province':
                $sql = "SELECT p.province AS label, COUNT(*) AS cnt FROM applicants a JOIN property_details p ON a.id=p.applicant_id GROUP BY p.province ORDER BY cnt DESC";
                break;
            case 'city':
                $sql = "SELECT p.city AS label, COUNT(*) AS cnt FROM applicants a JOIN property_details p ON a.id=p.applicant_id GROUP BY p.city ORDER BY cnt DESC";
                break;
            case 'village':
                $sql = "SELECT p.village AS label, COUNT(*) AS cnt FROM applicants a JOIN property_details p ON a.id=p.applicant_id WHERE p.village IS NOT NULL GROUP BY p.village ORDER BY cnt DESC";
                break;
            case 'license_title':
                $sql = "SELECT j.license_title AS label, COUNT(*) AS cnt FROM job_details j JOIN applicants a ON j.applicant_id=a.id GROUP BY j.license_title ORDER BY cnt DESC";
                break;
            case 'registration_date':
                $sql = "SELECT DATE(registration_completed_at) AS label, COUNT(*) AS cnt FROM applicants WHERE registration_completed_at IS NOT NULL GROUP BY DATE(registration_completed_at) ORDER BY label DESC";
                break;
            default:
                $sql = "SELECT 'نامشخص' AS label, 0 AS cnt";
        }
        
        $stmt = $pdo->query($sql);
        $stats = $stmt->fetchAll();
        
        $labelMap = [
            'province' => 'استان',
            'city' => 'شهر',
            'village' => 'روستا',
            'license_title' => 'رشته',
            'registration_date' => 'تاریخ ثبت‌نام'
        ];
        
        $this->render('admin/report_stats', [
            'title' => 'گزارش گروه‌بندی‌شده',
            'label' => $labelMap[$groupBy] ?? 'گروه',
            'stats' => $stats
        ], 'admin_layout');
    }

    private function getFiltersFromRequest(): array
    {
        return [
            'tracking_code' => $_GET['tracking_code'] ?? '',
            'name' => $_GET['name'] ?? '',
            'national_code' => $_GET['national_code'] ?? '',
            'mobile' => $_GET['mobile'] ?? '',
            'province' => $_GET['province'] ?? '',
            'city' => $_GET['city'] ?? '',
            'village' => $_GET['village'] ?? '',
            'status' => $_GET['status'] ?? '',
            'license_title' => $_GET['license_title'] ?? '',
        ];
    }
}