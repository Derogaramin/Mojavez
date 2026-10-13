<?php
/**
 * کنترلر پیگیری درخواست با کد رهگیری
 */
class TrackingController extends Controller
{
    private Applicant $applicantModel;
    private StatusLog $statusLogModel;
    private PostalCodeChange $postalCodeChangeModel;
    private JobDetail $jobDetailModel;
    private PropertyDetail $propertyDetailModel;
    private Document $documentModel;

    public function __construct()
    {
        $this->applicantModel = new Applicant();
        $this->statusLogModel = new StatusLog();
        $this->postalCodeChangeModel = new PostalCodeChange();
        $this->jobDetailModel = new JobDetail();
        $this->propertyDetailModel = new PropertyDetail();
        $this->documentModel = new Document();
    }

    /**
     * صفحه جستجوی کد رهگیری
     */
    public function search(): void
    {
        // اجرای لغو خودکار قبل از نمایش
        AutoCancelService::cancelExpired();
        AutoCancelService::updatePriorities();

        $this->render('tracking/search', [
            'title' => 'پیگیری درخواست',
            'error' => Session::get('tracking_error')
        ]);
        Session::remove('tracking_error');
    }

    /**
     * پردازش جستجو و نمایش وضعیت
     */
    public function find(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/tracking');
        }

        $code = trim($this->input('tracking_code', ''));
        if (empty($code)) {
            Session::set('tracking_error', 'کد رهگیری را وارد کنید.');
            $this->redirect('/tracking');
        }

        $applicant = $this->applicantModel->findByTrackingCode($code);
        if (!$applicant) {
            Session::set('tracking_error', 'کد رهگیری یافت نشد.');
            $this->redirect('/tracking');
        }

        $this->redirect('/tracking/status/' . $code);
    }

    /**
     * نمایش وضعیت و تاریخچه
     */
    public function status(string $trackingCode): void
    {
        $applicant = $this->applicantModel->findByTrackingCode($trackingCode);
        if (!$applicant) {
            Session::set('tracking_error', 'کد رهگیری نامعتبر است.');
            $this->redirect('/tracking');
        }

        $statusLogs = $this->statusLogModel->findByApplicantId($applicant['id']);
        $postalChanges = $this->postalCodeChangeModel->findByApplicantId($applicant['id']);

        // اجرای لغو خودکار
        AutoCancelService::cancelExpired();
        AutoCancelService::updatePriorities();

        $this->render('tracking/status', [
            'title' => 'وضعیت درخواست',
            'applicant' => $applicant,
            'statusLogs' => $statusLogs,
            'postalChanges' => $postalChanges,
            'changePostalError' => Session::get('change_postal_error'),
            'changePostalSuccess' => Session::get('change_postal_success')
        ]);

        Session::remove('change_postal_error');
        Session::remove('change_postal_success');
    }

    /**
     * درخواست تغییر کد پستی
     */
    public function changePostalCode(): void
    {
        if (!$this->isPost()) {
            $this->redirect('/tracking');
        }

        $trackingCode = $this->input('tracking_code', '');
        $newPostalCode = $this->input('new_postal_code', '');

        $applicant = $this->applicantModel->findByTrackingCode($trackingCode);
        if (!$applicant) {
            Session::set('tracking_error', 'کد رهگیری نامعتبر است.');
            $this->redirect('/tracking');
        }

        // اعتبارسنجی کد پستی جدید
        $validator = new Validator(['new_postal_code' => $newPostalCode]);
        $validator->required('new_postal_code', 'کد پستی جدید')
                  ->postalCode('new_postal_code', 'کد پستی جدید');

        if ($validator->hasErrors()) {
            Session::set('change_postal_error', $validator->firstError());
            $this->redirect('/tracking/status/' . $trackingCode);
        }

        // ثبت درخواست تغییر
        $this->postalCodeChangeModel->create([
            'applicant_id' => $applicant['id'],
            'old_postal_code' => $applicant['postal_code'],
            'new_postal_code' => $newPostalCode
        ]);

        // ثبت رویداد
        $this->statusLogModel->create([
            'applicant_id' => $applicant['id'],
            'status_name' => 'درخواست تغییر کد پستی',
            'description' => "از {$applicant['postal_code']} به {$newPostalCode}"
        ]);

        // توجه: کد پستی در applicants تغییر نمی‌کند مگر توسط مدیر

        Session::set('change_postal_success', 'درخواست تغییر کد پستی با موفقیت ثبت شد.');
        $this->redirect('/tracking/status/' . $trackingCode);
    }

    /**
     * نمایش نسخه چاپی پرونده
     */
    public function printView(string $trackingCode): void
    {
        $applicant = $this->applicantModel->findByTrackingCode($trackingCode);
        if (!$applicant) {
            $this->redirect('/tracking');
        }

        // ثبت رویداد مشاهده و چاپ
        $this->statusLogModel->create([
            'applicant_id' => $applicant['id'],
            'status_name' => 'مشاهده و چاپ پرونده',
            'description' => 'کاربر نسخه چاپی پرونده را مشاهده کرد.'
        ]);

        $jobDetail = $this->jobDetailModel->findByApplicantId($applicant['id']);
        $propertyDetail = $this->propertyDetailModel->findByApplicantId($applicant['id']);
        $documents = $this->documentModel->findByApplicantId($applicant['id']);
        $statusLogs = $this->statusLogModel->findByApplicantId($applicant['id']);
        $postalChanges = $this->postalCodeChangeModel->findByApplicantId($applicant['id']);

        $this->render('print', [
            'title' => 'پرونده قابل چاپ - ' . $trackingCode,
            'applicant' => $applicant,
            'jobDetail' => $jobDetail,
            'propertyDetail' => $propertyDetail,
            'documents' => $documents,
            'statusLogs' => $statusLogs,
            'postalChanges' => $postalChanges,
        ], 'layout_print'); // قالب مخصوص چاپ
    }
}