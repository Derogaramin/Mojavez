<?php
/**
 * کنترلر نمایش امن مدارک
 */
class DocumentController extends Controller
{
    private Document $documentModel;

    public function __construct()
    {
        $this->documentModel = new Document();
    }

    public function view(): void
    {
        // فقط مدیران واردشده
        if (!Session::get('admin_logged_in')) {
            http_response_code(403);
            echo 'دسترسی غیرمجاز';
            exit;
        }

        $documentId = (int)$_POST['document_id'] ?? 0;
        $applicantId = (int)$_POST['applicant_id'] ?? 0;

        $document = $this->documentModel->findById($documentId);
        if (!$document || ($applicantId > 0 && $document['applicant_id'] != $applicantId)) {
            http_response_code(404);
            echo 'فایل یافت نشد یا دسترسی ندارید';
            exit;
        }

        $filePath = UPLOAD_DIR . $document['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'فایل موجود نیست';
            exit;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-store');
        readfile($filePath);
        exit;
    }

    /**
     * نمایش فایل
     * @param int $id شناسه مدرک
     */
    public function serve(int $id): void
    {
        // فقط مدیران می‌توانند مدارک را ببینند
        if (!Session::get('admin_logged_in')) {
            http_response_code(403);
            echo 'دسترسی غیرمجاز';
            exit;
        }

        $document = $this->documentModel->findById($id);
        if (!$document) {
            http_response_code(404);
            echo 'فایل یافت نشد';
            exit;
        }

        $filePath = UPLOAD_DIR . $document['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo 'فایل موجود نیست';
            exit;
        }

        // تعیین MIME Type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $document['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-store, no-cache, must-revalidate');

        readfile($filePath);
        exit;
    }

    public function thumb(int $id): void
    {
        // فقط مدیران واردشده
        if (!Session::get('admin_logged_in')) {
            http_response_code(403);
            exit;
        }
        $document = $this->documentModel->findById($id);
        if (!$document) {
            http_response_code(404);
            exit;
        }
        $filePath = UPLOAD_DIR . $document['file_path'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            exit;
        }
        $mime = mime_content_type($filePath);
        header('Content-Type: ' . $mime);
        header('Cache-Control: no-store');
        readfile($filePath);
        exit;
    }
}