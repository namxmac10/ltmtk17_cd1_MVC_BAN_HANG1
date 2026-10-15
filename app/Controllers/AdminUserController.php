<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

final class AdminUserController extends Controller
{
    public function index(): void
    {
        Auth::requireAdmin();
        $this->render('admin/users/index', ['title' => 'Quản lý tài khoản', 'users' => User::all()]);
    }

    public function create(): void
    {
        Auth::requireAdmin();
        $this->render('admin/users/form', ['title' => 'Thêm tài khoản', 'account' => null]);
    }

    public function edit(int $id): void
    {
        Auth::requireAdmin();
        $account = User::find($id);
        if (!$account) {
            abort();
        }
        $this->render('admin/users/form', ['title' => 'Sửa tài khoản', 'account' => $account]);
    }

    public function store(): void
    {
        Auth::requireAdmin();
        $data = $this->input(true);
        if (User::findByEmail($data['email'])) {
            flash('error', 'Email đã tồn tại.');
            redirect('/admin/users');
        }
        try {
            User::create($data);
        } catch (\PDOException $error) {
            if ((string) $error->getCode() !== '23000') {
                throw $error;
            }
            flash('error', 'Email đã tồn tại.');
            redirect('/admin/users');
        }
        flash('success', 'Đã thêm tài khoản.');
        redirect('/admin/users');
    }

    public function update(int $id): void
    {
        $admin = Auth::requireAdmin();
        $account = User::find($id);
        if (!$account) {
            abort();
        }
        $data = $this->input(false);
        if ((int) $admin['id'] === $id && $data['role'] !== 'admin') {
            flash('error', 'Bạn không thể tự hạ quyền quản trị.');
            redirect('/admin/users');
        }
        $existing = User::findByEmail($data['email']);
        if ($existing && (int) $existing['id'] !== $id) {
            flash('error', 'Email đã tồn tại.');
            redirect('/admin/users');
        }
        try {
            User::update($id, $data);
        } catch (\PDOException $error) {
            if ((string) $error->getCode() !== '23000') {
                throw $error;
            }
            flash('error', 'Email đã tồn tại.');
            redirect('/admin/users');
        }
        flash('success', 'Đã cập nhật tài khoản.');
        redirect('/admin/users');
    }

    public function delete(int $id): void
    {
        $admin = Auth::requireAdmin();
        if ((int) $admin['id'] === $id) {
            flash('error', 'Không thể xóa chính tài khoản đang đăng nhập.');
            redirect('/admin/users');
        }
        if (!User::find($id)) {
            abort();
        }
        User::delete($id);
        flash('success', 'Đã xóa tài khoản. Các đơn hàng cũ được giữ lại.');
        redirect('/admin/users');
    }

    private function input(bool $requiredPassword): array
    {
        $name = trim(postText('full_name'));
        $email = strtolower(trim(postText('email')));
        $password = postText('password');
        $role = postText('role', 'customer');
        if ($name === '' || strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190 || !in_array($role, ['admin', 'customer'], true) || ($requiredPassword && strlen($password) < 6) || ($password !== '' && (strlen($password) < 6 || strlen($password) > 255))) {
            flash('error', 'Thông tin tài khoản không hợp lệ. Mật khẩu tối thiểu 6 ký tự.');
            redirect('/admin/users');
        }
        return ['full_name' => $name, 'email' => $email, 'password' => $password, 'role' => $role];
    }
}
