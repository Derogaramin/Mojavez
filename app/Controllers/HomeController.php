<?php
class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home', ['title' => 'سامانه مجوزهای صنایع دستی']);
    }
    public function guide(): void
    {
        $this->render('guide', ['title' => 'راهنمای ثبت‌نام']);
    }
}