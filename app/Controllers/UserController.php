<?php
class UserController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function checkAdmin(): void
    {
        if (!Session::get('admin_logged_in') || Session::get('admin_role') !== 'admin') {
            $this->redirect('/admin/login');
        }
    }

    public function index(): void
    {
        $this->checkAdmin();
        $users = $this->userModel->getAll();
        $this->render('admin/users_list', [
            'title' => 'مدیریت کاربران',
            'users' => $users
        ], 'admin_layout');
    }

    public function createForm(): void
    {
        $this->checkAdmin();
        $this->render('admin/user_form', [
            'title' => 'ایجاد کاربر جدید',
            'formData' => Session::get('formData', []),
            'errors' => Session::get('errors', []),
            'editMode' => false
        ], 'admin_layout');
        Session::remove('errors');
        Session::remove('formData');
    }

    public function store(): void
    {
        $this->checkAdmin();
        if (!$this->isPost()) $this->redirect('/admin/users/create');
        $data = $this->allInputs();
        $validator = new Validator($data);
        $validator->required('username', 'نام کاربری')
                  ->required('password', 'رمز عبور')
                  ->minLength('password', 6, 'رمز عبور');
        if ($validator->hasErrors()) {
            Session::set('errors', $validator->getErrors());
            Session::set('formData', $data);
            $this->redirect('/admin/users/create');
        }
        $this->userModel->create([
            'username' => $data['username'],
            'password' => $data['password'],
            'display_name' => $data['display_name'] ?? '',
            'role' => $data['role'] ?? 'expert'
        ]);
        $this->redirect('/admin/users');
    }

    public function editForm(int $id): void
    {
        $this->checkAdmin();
        $user = $this->userModel->findById($id);
        if (!$user) $this->redirect('/admin/users');
        $this->render('admin/user_form', [
            'title' => 'ویرایش کاربر',
            'formData' => $user,
            'errors' => Session::get('errors', []),
            'editMode' => true,
            'user' => $user
        ], 'admin_layout');
        Session::remove('errors');
        Session::remove('formData');
    }

    public function update(int $id): void
    {
        $this->checkAdmin();
        if (!$this->isPost()) $this->redirect('/admin/users');
        $data = $this->allInputs();
        $validator = new Validator($data);
        $validator->required('username', 'نام کاربری');
        if (!empty($data['password'])) {
            $validator->minLength('password', 6, 'رمز عبور');
        }
        if ($validator->hasErrors()) {
            Session::set('errors', $validator->getErrors());
            Session::set('formData', $data);
            $this->redirect('/admin/users/edit/' . $id);
        }
        $this->userModel->update($id, [
            'username' => $data['username'],
            'password' => $data['password'] ?? '',
            'display_name' => $data['display_name'] ?? '',
            'role' => $data['role']
        ]);
        $this->redirect('/admin/users');
    }

    public function delete(int $id): void
    {
        $this->checkAdmin();
        if ($id == 1) { // جلوگیری از حذف admin اصلی
            $this->redirect('/admin/users');
        }
        $this->userModel->delete($id);
        $this->redirect('/admin/users');
    }
}