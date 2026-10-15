<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

final class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::user()) {
            redirect(Auth::user()['role'] === 'admin' ? '/admin' : '/');
        }
        $this->render('auth/login', ['title' => 'Đăng nhập']);
    }

    public function login(): void
    {
        $email = strtolower(trim(postText('email')));
        $password = postText('password');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            flash('error', 'Vui lòng nhập email và mật khẩu hợp lệ.');
            redirect('/login');
        }
        $user = User::findByEmail($email);
        if (!$user || !hash_equals((string) $user['password'], $password)) {
            flash('error', 'Email hoặc mật khẩu không chính xác.');
            redirect('/login');
        }
        Auth::login((int) $user['id']);
        flash('success', 'Đăng nhập thành công.');
        redirect($user['role'] === 'admin' ? '/admin' : '/');
    }

    public function registerForm(): void
    {
        if (Auth::user()) {
            redirect('/');
        }
        $this->render('auth/register', ['title' => 'Tạo tài khoản']);
    }

    public function register(): void
    {
        $name = trim(postText('full_name'));
        $email = strtolower(trim(postText('email')));
        $password = postText('password');
        $confirm = postText('confirm_password');
        if ($name === '' || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || strlen($password) < 6 || strlen($password) > 255 || $password !== $confirm) {
            flash('error', 'Thông tin không hợp lệ. Mật khẩu từ 6 ký tự và phải trùng xác nhận.');
            redirect('/register');
        }
        if (User::findByEmail($email)) {
            flash('error', 'Email này đã được sử dụng.');
            redirect('/register');
        }
        try {
            $id = User::create(['full_name' => $name, 'email' => $email, 'password' => $password, 'role' => 'customer']);
        } catch (\PDOException $error) {
            if ((string) $error->getCode() !== '23000') {
                throw $error;
            }
            flash('error', 'Email này đã được sử dụng.');
            redirect('/register');
        }
        Auth::login($id);
        flash('success', 'Tài khoản đã được tạo.');
        redirect('/');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'Đã đăng xuất.');
        redirect('/');
    }
}
