<?php
/**
 * کنترلر پنل مدیریت
 */
class AdminController extends Controller
{
    private User $userModel;
    private Applicant $applicantModel;
    private JobDetail $jobDetailModel;
    private PropertyDetail $propertyDetailModel;
    private Document $documentModel;
    private StatusLog $statusLogModel;
    private PostalCodeChange $postalCodeChangeModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->applicantModel = new Applicant();
        $this->jobDetailModel = new JobDetail();
        $this->propertyDetailModel = new PropertyDetail();
        $this->documentModel = new Document();
        $this->statusLogModel = new StatusLog();
        $this->postalCodeChangeModel = new PostalCodeChange();
    }

    /**
     * بررسی احراز هویت مدیر
     */
    private function checkAuth(): void
    {
        if (!Session::get('admin_logged_in')) {
            $this->redirect('/admin/login');
        }
    }

    /**
     * صفحه ورود
     */
    public function loginForm(): void
    {
        // اگر قبلاً وارد شده، به داشبورد برو
        if (Session::get('admin_logged_in')) {
            $this->redirect('/admin/dashboard');
        }

        $this->render('admin/login', [
            'title' => 'ورود به پنل مدیریت',
            'error' => Session::get('login_error')
        ]);
        Session::remove('login_error');
    }

    /**
     * پردازش ورود
     */
    public function login(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/admin/login');
        }

        $username = $this->input('username', '');
        $password = $this->input('password', '');

        if (empty($username) || empty($password)) {
            Session::set('login_error', 'نام کاربری و رمز عبور را وارد کنید.');
            $this->redirect('/admin/login');
        }

        $user = $this->userModel->findByUsername($username);
        if (!$user || !$this->userModel->verifyPassword($password, $user['password'])) {
            Session::set('login_error', 'نام کاربری یا رمز عبور اشتباه است.');
            $this->redirect('/admin/login');
        }

        // ورود موفق
        Session::set('admin_role', $user['role']);
        Session::set('admin_logged_in', true);
        Session::set('admin_username', $user['username']);
        Session::set('admin_display_name', $user['display_name'] ?? $user['username']);

        $this->redirect('/admin/dashboard');
    }

    /**
     * خروج
     */
    public function logout(): void
    {
        Session::destroy();
        $this->redirect('/admin/login');
    }

    /**
     * داشبورد مدیریت
     */
    public function dashboard(): void
    {
        $this->checkAuth();
        AutoCancelService::cancelExpired();
        AutoCancelService::updatePriorities();

        $db = Database::getInstance()->getConnection();

        // آمار کلی
        $total = $db->query("SELECT COUNT(*) FROM applicants")->fetchColumn();
        $expired = $db->query("SELECT COUNT(*) FROM applicants WHERE current_status = 'لغو'")->fetchColumn();
        $pending = $db->query("SELECT COUNT(*) FROM applicants WHERE current_status NOT IN ('صدور مجوز','لغو')")->fetchColumn();

        // آمار هر وضعیت
        $statuses = [
            'پیش‌نویس',
            'تکمیل اطلاعات',
            'احراز نشانی',
            'ثبت درخواست صدور',
            'بارگذاری مدارک به سازمان',
            'تأیید اولیه',
            'صدور مجوز',
            'لغو'
        ];
        $statusCounts = [];
        foreach ($statuses as $st) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM applicants WHERE current_status = ?");
            $stmt->execute([$st]);
            $statusCounts[$st] = $stmt->fetchColumn();
        }

        $this->render('admin/dashboard', [
            'title' => 'داشبورد',
            'total' => $total,
            'expired' => $expired,
            'pending' => $pending,
            'statusCounts' => $statusCounts,
            'currentPage' => 'dashboard'
        ], 'admin_layout');
    }


    public function applications(): void
    {
        $this->checkAuth();
        if (Session::get('admin_role', 'admin') !== 'admin') {
            $this->redirect('/admin/my-tasks');
        }

        // اجرای لغو خودکار و به‌روزرسانی اولویت‌ها
        AutoCancelService::cancelExpired();
        AutoCancelService::updatePriorities();

        // فیلترها
        $filters = [
            'tracking_code' => $_GET['tracking_code'] ?? '',
            'name' => $_GET['name'] ?? '',
            'national_code' => $_GET['national_code'] ?? '',
            'mobile' => $_GET['mobile'] ?? '',
            'province' => $_GET['province'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        $applicants = $this->applicantModel->getAll($filters);

        $this->render('admin/applications', [
            'title' => 'همه درخواست‌ها',
            'applicants' => $applicants,
            'filters' => $filters,
            'currentPage' => 'applications'
        ], 'admin_layout');
    }

    /**
     * مشاهده جزئیات یک درخواست
     */
    public function viewApplication(int $id): void
    {
        $this->checkAuth();

        $applicant = $this->applicantModel->findById($id);
        if (!$applicant) {
            $this->redirect('/admin/dashboard');
        }

        $jobDetail = $this->jobDetailModel->findByApplicantId($id);
        $propertyDetail = $this->propertyDetailModel->findByApplicantId($id);
        $documents = $this->documentModel->findByApplicantId($id);
        $statusLogs = $this->statusLogModel->findByApplicantId($id);
        $postalChanges = $this->postalCodeChangeModel->findByApplicantId($id);

        $this->render('admin/view_application', [
            'title' => 'جزئیات درخواست',
            'applicant' => $applicant,
            'jobDetail' => $jobDetail,
            'propertyDetail' => $propertyDetail,
            'documents' => $documents,
            'statusLogs' => $statusLogs,
            'postalChanges' => $postalChanges,
            'statuses' => [
                'پیش‌نویس',
                'تکمیل اطلاعات',
                'احراز نشانی',
                'ثبت درخواست صدور',
                'بارگذاری مدارک به سازمان',
                'تأیید اولیه',
                'صدور مجوز',
                'لغو'
            ]
        ], 'admin_layout');
    }

    /**
     * تغییر وضعیت درخواست توسط مدیر
     */
    public function changeStatus(): void
    {
        $this->checkAuth();

        if (!$this->isPost()) {
            $this->redirect('/admin/dashboard');
        }

        $applicantId = (int)$this->input('applicant_id', 0);
        $newStatus = $this->input('new_status', '');

        $applicant = $this->applicantModel->findById($applicantId);
        if (!$applicant) {
            $this->redirect('/admin/dashboard');
        }

        $currentStatus = $applicant['current_status'];
        $role = Session::get('admin_role', 'admin');

        // بارگذاری workflow
        $workflow = require CONFIG_PATH . '/workflow.php';
        $transitions = $workflow['transitions'];

        // admin مجاز به همه تغییرات
        $allowed = ($role === 'admin');
        if (!$allowed && isset($transitions[$role])) {
            $rule = $transitions[$role];
            if (in_array($currentStatus, $rule['from']) && in_array($newStatus, $rule['to'])) {
                $allowed = true;
            }
        }

        if (!$allowed) {
            $this->redirect('/admin/view/' . $applicantId);
        }

        // وضعیت‌های مجاز برای تغییر
        $allowedStatuses = [
            'پیش‌نویس', 'تکمیل اطلاعات', 'احراز نشانی',
            'ثبت درخواست صدور', 'بارگذاری مدارک به سازمان',
            'تأیید اولیه', 'صدور مجوز', 'لغو'
        ];

        if (!in_array($newStatus, $allowedStatuses)) {
            $this->redirect('/admin/view/' . $applicantId);
        }

        // جلوگیری از بازگشت به وضعیت قبلی (مسیر یکطرفه)
        $currentStatus = $applicant['current_status'];
        $statusOrder = [
            'پیش‌نویس' => 1,
            'تکمیل اطلاعات' => 2,
            'احراز نشانی' => 3,
            'ثبت درخواست صدور' => 4,
            'بارگذاری مدارک به سازمان' => 5,
            'تأیید اولیه' => 6,
            'صدور مجوز' => 7,
            'لغو' => 99
        ];

        $currentOrder = $statusOrder[$currentStatus] ?? 0;
        $newOrder = $statusOrder[$newStatus] ?? 0;

        if ($newStatus !== 'لغو' && $newOrder <= $currentOrder) {
            // فقط می‌تواند جلو برود یا لغو شود
            $this->redirect('/admin/view/' . $applicantId);
        }

        // به‌روزرسانی وضعیت
        $this->applicantModel->updateStatus($applicantId, $newStatus);

        // ثبت در تاریخچه
        $this->statusLogModel->create([
            'applicant_id' => $applicantId,
            'status_name' => $newStatus,
            'description' => 'تغییر وضعیت توسط مدیر'
        ]);

        $this->redirect('/admin/view/' . $applicantId);
    }

    /**
     * نمایش فرم ثبت‌نام دستی متقاضی
     */
    public function registerForm(): void
    {
        $this->checkAuth();
        $this->render('admin/register', [
            'title' => 'ثبت نام متقاضی جدید (بایگانی)',
            'errors' => Session::get('errors', []),
            'formData' => Session::get('formData', []),
            'currentPage' => 'register'
        ], 'admin_layout');
        Session::remove('errors');
        Session::remove('formData');
    }

        /**
     * ذخیره ثبت‌نام دستی توسط مدیر
     */
    public function registerProcess(): void
    {
        $this->checkAuth();
        if (!$this->isPost()) {
            $this->redirect('/admin/register');
        }

        $data = $this->allInputs();

        // تاریخ تولد: تبدیل و اعتبارسنجی
        if (!empty($data['birth_year']) && !empty($data['birth_month']) && !empty($data['birth_day'])) {
            $jy = (int)$data['birth_year'];
            $jm = (int)$data['birth_month'];
            $jd = (int)$data['birth_day'];
            if (!is_valid_persian_date($jy, $jm, $jd)) {
                $data['birth_date'] = '';
            } else {
                $data['birth_date'] = persian_to_gregorian($jy, $jm, $jd);
            }
        } else {
            $data['birth_date'] = '';
        }

        // اگر «سایر» انتخاب شده
        if ($data['license_title'] === 'سایر') {
            $data['license_title'] = $data['custom_license_title'] ?? '';
        }

        $validator = new Validator($data);

        // ---- اعتبارسنجی هویتی ----
        $validator->required('first_name', 'نام')
                ->persianAlpha('first_name', 'نام')
                ->maxLength('first_name', 50, 'نام')
                ->required('last_name', 'نام خانوادگی')
                ->persianAlpha('last_name', 'نام خانوادگی')
                ->maxLength('last_name', 50, 'نام خانوادگی')
                ->required('national_code', 'کد ملی')
                ->nationalCode('national_code', 'کد ملی')
                ->unique('national_code', 'applicants', 'national_code', 'کد ملی')
                ->required('postal_code', 'کد پستی')
                ->postalCode('postal_code', 'کد پستی')
                ->required('mobile', 'شماره تلفن همراه')
                ->mobile('mobile', 'شماره تلفن همراه')
                ->unique('mobile', 'applicants', 'mobile', 'شماره تلفن همراه')
                ->required('father_name', 'نام پدر')
                ->persianAlpha('father_name', 'نام پدر')
                ->maxLength('father_name', 50, 'نام پدر')
                ->required('marital_status', 'وضعیت تأهل')
                ->enum('marital_status', ['مجرد','متأهل'], 'وضعیت تأهل')
                ->required('physical_condition', 'وضعیت جسمانی')
                ->required('has_court_case', 'پرونده قضایی')
                ->required('has_active_loan', 'وام فعال')
                ->required('has_overdue_installments', 'اقساط معوقه');

        // تاریخ تولد
        if (empty($data['birth_date'])) {
            $validator->addError('birth_date', 'تاریخ تولد الزامی و معتبر است.');
        }

        // وضعیت معلولیت
        if ($data['physical_condition'] === 'معلولیت') {
            $validator->required('disability_details', 'توضیحات معلولیت');
        }

        // تأهل
        if ($data['marital_status'] === 'متأهل') {
            $validator->required('children_count', 'تعداد فرزندان')
                    ->unsignedInt('children_count', 'تعداد فرزندان');
        } else {
            $data['children_count'] = 0;
        }

        if (!empty($data['income_decile'])) {
            $validator->unsignedInt('income_decile', 'دهک درآمدی');
        }

        // ---- اطلاعات شغلی ----
        $validator->required('license_title', 'عنوان مجوز')
                ->maxLength('license_title', 100, 'عنوان مجوز')
                ->required('experience_months', 'سابقه فعالیت')
                ->unsignedInt('experience_months', 'سابقه فعالیت')
                ->required('training_method', 'نحوه آموزش')
                ->enum('training_method', ['در خانه','استاد شاگردی','کارگاهی'], 'نحوه آموزش');

        // ---- اطلاعات ملک ----
        $validator->required('ownership_status', 'وضعیت مالکیت')
                ->enum('ownership_status', ['شخص','مستأجر','سرپرست'], 'وضعیت مالکیت')
                ->required('province', 'استان')
                ->required('county', 'شهرستان')
                ->required('city', 'شهر')
                ->required('electricity_bill_id', 'شناسه قبض برق')
                ->required('address_street', 'خیابان')
                ->required('address_plaque', 'پلاک');

        if (!empty($data['address_alley'])) {
            $validator->maxLength('address_alley', 100, 'کوچه');
        }

        if ($validator->hasErrors()) {
            Session::set('errors', $validator->getErrors());
            Session::set('formData', $data);
            $this->redirect('/admin/register');
        }

        // ---- ذخیره‌سازی ----
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            $trackingCode = Applicant::generateTrackingCode();

            $applicantId = $this->applicantModel->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'national_code' => $data['national_code'],
                'postal_code' => $data['postal_code'],
                'mobile' => $data['mobile'],
                'birth_date' => $data['birth_date'],
                'father_name' => $data['father_name'],
                'marital_status' => $data['marital_status'] ?? 'مجرد',
                'children_count' => $data['children_count'] ?? 0,
                'physical_condition' => $data['physical_condition'] ?? 'سالم',
                'disability_details' => $data['disability_details'] ?? null,
                'income_decile' => $data['income_decile'] ?? null,
                'has_court_case' => $data['has_court_case'] ?? 'خیر',
                'has_active_loan' => $data['has_active_loan'] ?? 'خیر',
                'has_overdue_installments' => $data['has_overdue_installments'] ?? 'خیر',
                'tracking_code' => $trackingCode
            ]);

            // شغلی
            $this->jobDetailModel->create([
                'applicant_id' => $applicantId,
                'license_title' => $data['license_title'],
                'experience_months' => $data['experience_months'],
                'training_method' => $data['training_method']
            ]);

            // ملک
            $this->propertyDetailModel->create([
                'applicant_id' => $applicantId,
                'ownership_status' => $data['ownership_status'],
                'province' => $data['province'],
                'county' => $data['county'],
                'city' => $data['city'],
                'district' => $data['district'] ?? null,
                'rural_district' => $data['rural_district'] ?? null,
                'village' => $data['village'] ?? null,
                'electricity_bill_id' => $data['electricity_bill_id'],
                'address_street' => $data['address_street'] ?? null,
                'address_alley' => $data['address_alley'] ?? null,
                'address_plaque' => $data['address_plaque'] ?? null
            ]);

            // مدارک
            $uploadDir = UPLOAD_DIR . '/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $saveFile = function ($file, $docType) use ($applicantId, $uploadDir) {
                if (!$file || $file['error'] !== UPLOAD_ERR_OK) return;
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $uniqueName = bin2hex(random_bytes(16)) . '.' . $ext;
                $destPath = $uploadDir . '/' . $uniqueName;
                if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                    throw new \RuntimeException('خطا در ذخیره فایل ' . $file['name']);
                }
                $this->documentModel->create([
                    'applicant_id' => $applicantId,
                    'doc_type' => $docType,
                    'file_name' => $uniqueName,
                    'original_name' => $file['name'],
                    'file_path' => str_replace(UPLOAD_DIR, '', $destPath)
                ]);
            };

            $saveFile($_FILES['national_card_front'] ?? null, 'national_card');
            $saveFile($_FILES['national_card_back'] ?? null, 'national_card');
            $saveFile($_FILES['birth_cert'] ?? null, 'birth_cert');
            $saveFile($_FILES['ownership_doc'] ?? null, 'ownership_doc');
            $saveFile($_FILES['vocational_cert'] ?? null, 'vocational_cert');
            $saveFile($_FILES['education_degree'] ?? null, 'education_degree');

            for ($i = 1; $i <= 3; $i++) {
                $saveFile($_FILES["optional_{$i}"] ?? null, 'optional');
            }

            if (!empty($_FILES['product_photos']['name']) && is_array($_FILES['product_photos']['name'])) {
                foreach ($_FILES['product_photos']['tmp_name'] as $index => $tmpName) {
                    if ($_FILES['product_photos']['error'][$index] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['product_photos']['name'][$index],
                            'tmp_name' => $tmpName,
                            'size' => $_FILES['product_photos']['size'][$index],
                            'error' => $_FILES['product_photos']['error'][$index],
                        ];
                        $saveFile($file, 'product_photos');
                    }
                }
            }

            $this->applicantModel->updateStatus($applicantId, 'تکمیل اطلاعات');
            $nowDate = date('Y-m-d');
            $stmt = $db->prepare("UPDATE applicants SET registration_started_at = :start_date, registration_completed_at = :end_date WHERE id = :id");
            $stmt->execute([
                'start_date' => $nowDate,
                'end_date' => $nowDate,
                'id' => $applicantId
            ]);

            $this->statusLogModel->create([
                'applicant_id' => $applicantId,
                'status_name' => 'تکمیل اطلاعات',
                'description' => 'ثبت نام توسط مدیر'
            ]);

            $db->commit();

            Session::remove('errors');
            Session::remove('formData');

            $this->redirect('/admin/view/' . $applicantId);

        } catch (\Exception $e) {
            $db->rollBack();
            Session::set('errors', ['system' => ['خطا در ثبت نام: ' . $e->getMessage()]]);
            Session::set('formData', $data);
            $this->redirect('/admin/register');
        }
    }

    /**
     * نمایش فرم ویرایش متقاضی
     */
    public function editApplication(int $id): void
    {
        $this->checkAuth();
        $applicant = $this->applicantModel->findById($id);
        if (!$applicant) {
            $this->redirect('/admin/dashboard');
        }
        $jobDetail = $this->jobDetailModel->findByApplicantId($id);
        $propertyDetail = $this->propertyDetailModel->findByApplicantId($id);
        $documents = $this->documentModel->findByApplicantId($id);

        $this->render('admin/edit_application', [
            'title' => 'ویرایش متقاضی',
            'applicant' => $applicant,
            'jobDetail' => $jobDetail,
            'propertyDetail' => $propertyDetail,
            'documents' => $documents,
            'errors' => Session::get('errors', []),
            'formData' => Session::get('formData', []),
        ], 'admin_layout');
        Session::remove('errors');
        Session::remove('formData');
    }

    /**
     * پردازش ویرایش متقاضی
     */
    public function updateApplication(): void
    {
        $this->checkAuth();
        if (!$this->isPost()) {
            $this->redirect('/admin/dashboard');
        }

        $applicantId = (int)$this->input('applicant_id', 0);
        $data = $this->allInputs();
        $validator = new Validator($data);

        $validator->required('first_name', 'نام')
                ->persianAlpha('first_name', 'نام')
                ->maxLength('first_name', 50, 'نام')
                ->required('last_name', 'نام خانوادگی')
                ->persianAlpha('last_name', 'نام خانوادگی')
                ->maxLength('last_name', 50, 'نام خانوادگی')
                ->required('mobile', 'شماره تلفن همراه')
                ->mobile('mobile', 'شماره تلفن همراه')
                ->required('postal_code', 'کد پستی')
                ->postalCode('postal_code', 'کد پستی');

        if ($validator->hasErrors()) {
            Session::set('errors', $validator->getErrors());
            Session::set('formData', $data);
            $this->redirect('/admin/edit/' . $applicantId);
        }

        // آپدیت فیلدهای اصلی
        $stmt = $this->applicantModel->db->prepare(
            "UPDATE applicants SET first_name=?, last_name=?, mobile=?, postal_code=?, updated_at=NOW() WHERE id=?"
        );
        $stmt->execute([
            $data['first_name'],
            $data['last_name'],
            $data['mobile'],
            $data['postal_code'],
            $applicantId
        ]);

        // در صورت تمایل می‌توان آدرس را نیز ویرایش کرد؛ در صورت ارسال فیلدهای property
        if (!empty($data['address_street']) || !empty($data['address_alley']) || !empty($data['address_plaque'])) {
            $propSql = "UPDATE property_details SET address_street=?, address_alley=?, address_plaque=? WHERE applicant_id=?";
            $this->propertyDetailModel->db->prepare($propSql)->execute([
                $data['address_street'] ?? '',
                $data['address_alley'] ?? '',
                $data['address_plaque'] ?? '',
                $applicantId
            ]);
        }

        // ثبت لاگ
        $this->statusLogModel->create([
            'applicant_id' => $applicantId,
            'status_name' => 'ویرایش اطلاعات توسط مدیر',
            'description' => 'اطلاعات هویتی یا آدرس ویرایش شد.'
        ]);

        $this->redirect('/admin/view/' . $applicantId);
    }

    /**
     * افزودن مدرک جدید به پرونده
     */
    public function addDocument(): void
    {
        $this->checkAuth();
        if (!$this->isPost()) {
            $this->redirect('/admin/dashboard');
        }

        $applicantId = (int)$this->input('applicant_id', 0);
        $docType = $this->input('doc_type', 'optional');

        $file = $_FILES['document_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Session::set('errors', ['document' => 'لطفاً یک فایل انتخاب کنید.']);
            $this->redirect('/admin/view/' . $applicantId);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ALLOWED_EXTENSIONS)) {
            Session::set('errors', ['document' => 'پسوند فایل مجاز نیست.']);
            $this->redirect('/admin/view/' . $applicantId);
        }
        if ($file['size'] > MAX_UPLOAD_SIZE) {
            Session::set('errors', ['document' => 'حجم فایل بیش از حد مجاز است.']);
            $this->redirect('/admin/view/' . $applicantId);
        }

        $uploadDir = UPLOAD_DIR . '/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        $uniqueName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = $uploadDir . '/' . $uniqueName;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Session::set('errors', ['document' => 'خطا در ذخیره فایل.']);
            $this->redirect('/admin/view/' . $applicantId);
        }

        $this->documentModel->create([
            'applicant_id' => $applicantId,
            'doc_type' => $docType,
            'file_name' => $uniqueName,
            'original_name' => $file['name'],
            'file_path' => str_replace(UPLOAD_DIR, '', $destPath)
        ]);

        $this->statusLogModel->create([
            'applicant_id' => $applicantId,
            'status_name' => 'افزودن مدرک توسط مدیر',
            'description' => 'مدرک ' . $file['name'] . ' اضافه شد.'
        ]);

        $this->redirect('/admin/view/' . $applicantId);
    }

    public function myTasks(): void
    {
        $this->checkAuth();
        $role = Session::get('admin_role', 'admin');
        if ($role === 'admin') {
            $this->applications();
            return;
        }
        $workflow = require CONFIG_PATH . '/workflow.php';
        $allowedFrom = $workflow['transitions'][$role]['from'] ?? [];
        if (empty($allowedFrom)) {
            $applicants = [];
        } else {
            $in = implode("','", $allowedFrom);
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("SELECT * FROM applicants WHERE current_status IN ('$in') ORDER BY priority DESC, registration_completed_at DESC");
            $applicants = $stmt->fetchAll();
        }

        // فیلترهای پیش‌فرض کامل
        $filters = [
            'tracking_code' => '',
            'name' => '',
            'national_code' => '',
            'mobile' => '',
            'province' => '',
            'status' => '',
            'license_title' => '',
            'village' => '',
        ];

        $this->render('admin/applications', [
            'title' => 'وظایف من',
            'applicants' => $applicants,
            'filters' => $filters,
            'currentPage' => 'tasks'
        ], 'admin_layout');
    }
}