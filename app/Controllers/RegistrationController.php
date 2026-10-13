<?php
/**
 * کنترلر مدیریت فرآیند ثبت‌نام چندمرحله‌ای
 */
class RegistrationController extends Controller
{
    private Applicant $applicantModel;
    private JobDetail $jobDetailModel;
    private PropertyDetail $propertyDetailModel;
    private Document $documentModel;
    private StatusLog $statusLogModel;

    public function __construct()
    {
        $this->applicantModel = new Applicant();
        $this->jobDetailModel = new JobDetail();
        $this->propertyDetailModel = new PropertyDetail();
        $this->documentModel = new Document();
        $this->statusLogModel = new StatusLog();
    }

    /**
     * مرحله ۱: اطلاعات هویتی
     */
    public function step1(): void
    {
        $stepData = Session::getStep('step1', []);
        $this->render('registration/step1', [
            'title' => 'مرحله ۱ - اطلاعات هویتی',
            'stepData' => $stepData,
            'errors' => Session::get('errors', []),
            'currentStep' => 1
        ]);
        Session::remove('errors');
    }

    /**
     * پردازش مرحله ۱
     */
    public function processStep1(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/register');
        }

        $data = $this->allInputs();

        // ----- محاسبهٔ تاریخ تولد پیش از اعتبارسنجی اصلی -----
        $birth_date = '';
        if (empty($data['birth_year']) || empty($data['birth_month']) || empty($data['birth_day'])) {
            // فیلدهای تاریخ کاملاً پر نشده‌اند → الزامی
            $birthError = 'required';
        } else {
            $jy = (int)$data['birth_year'];
            $jm = (int)$data['birth_month'];
            $jd = (int)$data['birth_day'];
            if (!is_valid_persian_date($jy, $jm, $jd)) {
                $birthError = 'invalid'; // تاریخ نامعتبر
            } else {
                $birth_date = persian_to_gregorian($jy, $jm, $jd);
                $birthError = null;
            }
        }
        $data['birth_date'] = $birth_date;

        // اکنون Validator را با دادهٔ کامل می‌سازیم
        $validator = new Validator($data);

        // اعتبارسنجی تمام فیلدها
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
                ->enum('marital_status', ['مجرد', 'متأهل'], 'وضعیت تأهل')
                ->required('physical_condition', 'وضعیت جسمانی')
                ->enum('physical_condition', ['سالم', 'معلولیت'], 'وضعیت جسمانی')
                ->required('has_court_case', 'پرونده قضایی')
                ->enum('has_court_case', ['بله', 'خیر'], 'پرونده قضایی')
                ->required('has_active_loan', 'وام فعال')
                ->enum('has_active_loan', ['بله', 'خیر'], 'وام فعال')
                ->required('has_overdue_installments', 'اقساط معوقه')
                ->enum('has_overdue_installments', ['بله', 'خیر'], 'اقساط معوقه');

        // بررسی خطای تاریخ تولد با addError
        if ($birthError === 'required') {
            $validator->addError('birth_date', 'تاریخ تولد الزامی است.');
        } elseif ($birthError === 'invalid') {
            $validator->addError('birth_date', 'تاریخ تولد معتبر نیست.');
        }

        // وضعیت معلولیت
        if ($data['physical_condition'] === 'معلولیت') {
            $validator->required('disability_details', 'توضیحات معلولیت');
        }

        // در صورت متأهل بودن
        if ($data['marital_status'] === 'متأهل') {
            $validator->required('children_count', 'تعداد فرزندان')
                    ->unsignedInt('children_count', 'تعداد فرزندان');
        } else {
            $data['children_count'] = 0;
        }

        // دهک درآمدی اختیاری
        if (!empty($data['income_decile'])) {
            $validator->unsignedInt('income_decile', 'دهک درآمدی');
        }

        if ($validator->hasErrors()) {
            Session::set('errors', $validator->getErrors());
            Session::setStep('step1', $data);
            $this->redirect('/register');
        }

        // تاریخ معتبر است، آن را در دادهٔ مرحله نگه می‌داریم
        $data['birth_date'] = $birth_date;
        Session::setStep('step1', $data);
        Session::remove('errors');
        $this->redirect('/register/step2');
    }
    
    /**
     * مرحله ۲: اطلاعات شغلی
     */
    public function step2(): void
    {
        if (!Session::has('registration') || !Session::getStep('step1')) {
            $this->redirect('/register');
        }

        $stepData = Session::getStep('step2', []);
        $this->render('registration/step2', [
            'title' => 'مرحله ۲ - اطلاعات شغلی',
            'stepData' => $stepData,
            'errors' => Session::get('errors', []),
            'currentStep' => 2
        ]);
        Session::remove('errors');
    }

    /**
     * پردازش مرحله ۲
     */
    public function processStep2(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/register/step2');
        }

        $data = $this->allInputs();
        $validator = new Validator($data);

        $validator->required('license_title', 'عنوان مجوز')
                  ->maxLength('license_title', 100, 'عنوان مجوز')
                  ->required('experience_months', 'سابقه فعالیت')
                  ->unsignedInt('experience_months', 'سابقه فعالیت')
                  ->required('training_method', 'نحوه آموزش')
                  ->enum('training_method', ['در خانه', 'استاد شاگردی', 'کارگاهی'], 'نحوه آموزش');

        if ($validator->hasErrors()) {
            Session::set('errors', $validator->getErrors());
            Session::setStep('step2', $data);
            $this->redirect('/register/step2');
        }

        Session::setStep('step2', $data);
        Session::remove('errors');
        $this->redirect('/register/step3');
    }

    /**
     * مرحله ۳: اطلاعات ملک
     */
    public function step3(): void
    {
        if (!Session::getStep('step1') || !Session::getStep('step2')) {
            $this->redirect('/register');
        }

        $stepData = Session::getStep('step3', []);
        $this->render('registration/step3', [
            'title' => 'مرحله ۳ - اطلاعات ملک و مالکیت',
            'stepData' => $stepData,
            'errors' => Session::get('errors', []),
            'currentStep' => 3
        ]);
        Session::remove('errors');
    }

    /**
     * پردازش مرحله ۳
     */
    public function processStep3(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/register/step3');
        }

        $data = $this->allInputs();
        $validator = new Validator($data);

        $validator->required('ownership_status', 'وضعیت مالکیت')
                  ->enum('ownership_status', ['شخص', 'مستأجر', 'سرپرست'], 'وضعیت مالکیت')
                  ->required('province', 'استان')
                  ->maxLength('province', 50, 'استان')
                  ->required('county', 'شهرستان')
                  ->maxLength('county', 50, 'شهرستان')
                  ->required('city', 'شهر')
                  ->maxLength('city', 50, 'شهر')
                  ->required('electricity_bill_id', 'شناسه قبض برق')
                  ->maxLength('electricity_bill_id', 20, 'شناسه قبض برق')
                  ->required('address_street', 'خیابان')
                  ->maxLength('address_street', 100, 'خیابان')
                  ->required('address_alley', 'کوچه')
                  ->maxLength('address_alley', 100, 'کوچه')
                  ->required('address_plaque', 'پلاک')
                  ->maxLength('address_plaque', 20, 'پلاک');

        if (!empty($data['district'])) {
            $validator->maxLength('district', 50, 'بخش');
        }
        if (!empty($data['rural_district'])) {
            $validator->maxLength('rural_district', 50, 'دهستان');
        }
        if (!empty($data['village'])) {
            $validator->maxLength('village', 50, 'روستا');
        }

        if ($validator->hasErrors()) {
            Session::set('errors', $validator->getErrors());
            Session::setStep('step3', $data);
            $this->redirect('/register/step3');
        }

        Session::setStep('step3', $data);
        Session::remove('errors');
        $this->redirect('/register/step4');
    }

    /**
     * مرحله ۴: بارگذاری مدارک
     */
    public function step4(): void
    {
        if (!Session::getStep('step1') || !Session::getStep('step2') || !Session::getStep('step3')) {
            $this->redirect('/register');
        }

        $ownershipStatus = Session::getStep('step3')['ownership_status'] ?? '';

        $this->render('registration/step4', [
            'title' => 'مرحله ۴ - بارگذاری مدارک',
            'errors' => Session::get('errors', []),
            'currentStep' => 4,
            'ownershipStatus' => $ownershipStatus
        ]);
        Session::remove('errors');
    }

    /**
     * پردازش نهایی (مرحله ۴)
     */
    public function processStep4(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/register/step4');
        }

        $step1 = Session::getStep('step1');
        $step2 = Session::getStep('step2');
        $step3 = Session::getStep('step3');

        if (!$step1 || !$step2 || !$step3) {
            $this->redirect('/register');
        }

        $ownershipStatus = $step3['ownership_status'];

        $errors = [];

        // کارت ملی (حداقل یک فایل)
        $nationalCardFront = $_FILES['national_card_front'] ?? null;
        $nationalCardBack = $_FILES['national_card_back'] ?? null;
        if ((!$nationalCardFront || $nationalCardFront['error'] !== UPLOAD_ERR_OK) &&
            (!$nationalCardBack || $nationalCardBack['error'] !== UPLOAD_ERR_OK)) {
            $errors['national_card'][] = 'تصویر کارت ملی (پشت یا رو) الزامی است.';
        }

        // شناسنامه
        $birthCert = $_FILES['birth_cert'] ?? null;
        if (!$birthCert || $birthCert['error'] !== UPLOAD_ERR_OK) {
            $errors['birth_cert'][] = 'تصویر شناسنامه الزامی است.';
        }

        // مدرک مالکیت
        $ownershipDoc = $_FILES['ownership_doc'] ?? null;
        if (!$ownershipDoc || $ownershipDoc['error'] !== UPLOAD_ERR_OK) {
            $errors['ownership_doc'][] = 'بارگذاری مدرک مربوط به وضعیت مالکیت الزامی است.';
        }

        // مدارک جدید (اختیاری)
        $vocationalCert = $_FILES['vocational_cert'] ?? null;
        $educationDegree = $_FILES['education_degree'] ?? null;

        // فایل‌های اختیاری ۱ تا ۳
        $optionalFiles = [];
        for ($i = 1; $i <= 3; $i++) {
            $file = $_FILES["optional_{$i}"] ?? null;
            if ($file && $file['error'] === UPLOAD_ERR_OK) {
                $optionalFiles[] = $file;
            }
        }

        // اعتبارسنجی پسوند و حجم تمام فایل‌ها
        $allFiles = array_filter([
            $nationalCardFront, $nationalCardBack, $birthCert, $ownershipDoc,
            $vocationalCert, $educationDegree,
            ...$optionalFiles
        ]);

        foreach ($allFiles as $file) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                $errors['files'][] = "پسوند فایل {$file['name']} مجاز نیست. فقط " . implode(', ', ALLOWED_EXTENSIONS) . " مجاز است.";
            }
            if ($file['size'] > MAX_UPLOAD_SIZE) {
                $errors['files'][] = "حجم فایل {$file['name']} بیش از ۲ مگابایت است.";
            }
        }

        if (!empty($errors)) {
            Session::set('errors', $errors);
            $this->redirect('/register/step4');
        }

        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();

        try {
            $trackingCode = Applicant::generateTrackingCode();

            $applicantId = $this->applicantModel->create([
                'first_name' => $step1['first_name'],
                'last_name' => $step1['last_name'],
                'national_code' => $step1['national_code'],
                'postal_code' => $step1['postal_code'],
                'mobile' => $step1['mobile'],
                'birth_date' => $step1['birth_date'] ?? null,
                'father_name' => $step1['father_name'] ?? null,
                'marital_status' => $step1['marital_status'] ?? 'مجرد',
                'children_count' => $step1['children_count'] ?? 0,
                'physical_condition' => $step1['physical_condition'] ?? 'سالم',
                'disability_details' => $step1['disability_details'] ?? null,
                'income_decile' => $step1['income_decile'] ?? null,
                'has_court_case' => $step1['has_court_case'] ?? 'خیر',
                'has_active_loan' => $step1['has_active_loan'] ?? 'خیر',
                'has_overdue_installments' => $step1['has_overdue_installments'] ?? 'خیر',
                'tracking_code' => $trackingCode
            ]);

            // اطلاعات شغلی
            if ($step2['license_title'] === 'سایر') {
                $step2['license_title'] = $step2['custom_license_title'] ?? 'سایر (نامشخص)';
            }
            $this->jobDetailModel->create([
                'applicant_id' => $applicantId,
                'license_title' => $step2['license_title'],
                'experience_months' => $step2['experience_months'],
                'training_method' => $step2['training_method']
            ]);

            // اطلاعات ملک
            $this->propertyDetailModel->create([
                'applicant_id' => $applicantId,
                'ownership_status' => $step3['ownership_status'],
                'province' => $step3['province'],
                'county' => $step3['county'],
                'city' => $step3['city'],
                'district' => $step3['district'] ?? null,
                'rural_district' => $step3['rural_district'] ?? null,
                'village' => $step3['village'] ?? null,
                'electricity_bill_id' => $step3['electricity_bill_id'],
                'address_street' => $step3['address_street'] ?? null,
                'address_alley' => $step3['address_alley'] ?? null,
                'address_plaque' => $step3['address_plaque'] ?? null
            ]);

            // ذخیره مدارک
            $uploadDir = UPLOAD_DIR . '/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $saveFile = function ($file, $docType) use ($applicantId, $uploadDir) {
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

            // کارت ملی
            if ($nationalCardFront && $nationalCardFront['error'] === UPLOAD_ERR_OK) {
                $saveFile($nationalCardFront, 'national_card');
            }
            if ($nationalCardBack && $nationalCardBack['error'] === UPLOAD_ERR_OK) {
                $saveFile($nationalCardBack, 'national_card');
            }

            // شناسنامه
            $saveFile($birthCert, 'birth_cert');

            // مدرک مالکیت
            $saveFile($ownershipDoc, 'ownership_doc');

            // گواهینامه فنی‌وحرفه‌ای (اختیاری)
            if ($vocationalCert && $vocationalCert['error'] === UPLOAD_ERR_OK) {
                $saveFile($vocationalCert, 'vocational_cert');
            }

            // آخرین مدرک تحصیلی (اختیاری)
            if ($educationDegree && $educationDegree['error'] === UPLOAD_ERR_OK) {
                $saveFile($educationDegree, 'education_degree');
            }

            // تصاویر محصولات
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

            // فایل‌های اختیاری (سایر)
            foreach ($optionalFiles as $file) {
                $saveFile($file, 'optional');
            }

            $this->applicantModel->completeRegistration($applicantId);

            $this->statusLogModel->create([
                'applicant_id' => $applicantId,
                'status_name' => 'تکمیل اطلاعات',
                'description' => 'ثبت‌نام نهایی توسط متقاضی انجام شد.'
            ]);

            $db->commit();

            Session::clearRegistration();
            $this->redirect('/register/success/' . $trackingCode);

        } catch (\Exception $e) {
            $db->rollBack();
            Session::set('errors', ['system' => ['خطایی در ثبت‌نام رخ داد. لطفاً دوباره تلاش کنید.']]);
            $this->redirect('/register/step4');
        }
    }

    /**
     * صفحه موفقیت ثبت‌نام
     */
    public function success(string $trackingCode): void
    {
        $applicant = $this->applicantModel->findByTrackingCode($trackingCode);
        if (!$applicant) {
            $this->redirect('/');
        }

        $this->render('registration/success', [
            'title' => 'ثبت‌نام با موفقیت انجام شد',
            'trackingCode' => $trackingCode
        ]);
    }
}