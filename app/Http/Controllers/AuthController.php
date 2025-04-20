<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Nhận dữ liệu JSON từ body
        $username = trim($request->input('username'));
        $password = trim($request->input('password'));

        if (!$username || !$password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Missing fields'
            ]);
        }

        // Truy vấn user
        $user = DB::table('roleadminuser')->where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found'
            ]);
        }

        // Kiểm tra trạng thái
        if ($user->trangThai === 'inactive') {
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản đã bị vô hiệu hóa'
            ]);
        }

        // Chặn tài khoản admin
        if ($user->vaiTro === 'admin') {
            return response()->json([
                'status' => 'error',
                'message' => 'Đây là tài khoản admin. Không thể đăng nhập vào ứng dụng.'
            ]);
        }

        // Kiểm tra mật khẩu (hash hoặc plain text)
        if (Hash::check($password, $user->password) || $password === $user->password) {
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'user' => [
                    'email' => $user->email,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'diaChi' => $user->diaChi,
                    'soDienThoai' => $user->soDienThoai,
                    'trangThai' => $user->trangThai,
                    'vaiTro' => $user->vaiTro
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid password'
            ]);
        }
    }
    public function register(Request $request)
    {
        // Kiểm tra dữ liệu từ client
        $data = $request->all();

        // Kiểm tra nếu thiếu thông tin
        if (!isset($data['username'], $data['email'], $data['password'], $data['confirm_password'])) {
            return response()->json(['status' => 'error', 'message' => 'Missing fields'], 400);
        }

        $username = trim($data['username']);
        $email = trim($data['email']);
        $password = trim($data['password']);
        $confirmPassword = trim($data['confirm_password']);

        // Kiểm tra mật khẩu có khớp không
        if ($password !== $confirmPassword) {
            return response()->json(['status' => 'error', 'message' => 'Passwords do not match'], 400);
        }

        // Kiểm tra email đã tồn tại chưa
        $emailExist = DB::table('roleadminuser')->where('email', $email)->exists();
        if ($emailExist) {
            return response()->json(['status' => 'error', 'message' => 'Email already exists'], 400);
        }

        // Kiểm tra username đã tồn tại chưa
        $usernameExist = DB::table('roleadminuser')->where('username', $username)->exists();
        if ($usernameExist) {
            return response()->json(['status' => 'error', 'message' => 'Username already exists'], 400);
        }

        // Mã hóa mật khẩu
        $hashedPassword = Hash::make($password);

        // Lưu thông tin người dùng vào database
        try {
            DB::table('roleadminuser')->insert([
                'email' => $email,
                'username' => $username,
                'password' => $hashedPassword,
                'trangThai' => 'active',
                'vaiTro' => 'user'
            ]);

            return response()->json(['status' => 'success', 'message' => 'Registration successful'], 201);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }
}

