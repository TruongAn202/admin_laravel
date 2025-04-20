<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserInfoController extends Controller
{
    public function getUserInfo(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return response()->json(['error' => 'Thiếu email'], 400);
        }

        $user = DB::table('roleadminuser')
            ->select('email', 'full_name', 'diaChi', 'soDienThoai')
            ->where('email', $email)
            ->first();

        if ($user) {
            return response()->json($user, 200);
        } else {
            return response()->json(['error' => 'Không tìm thấy người dùng'], 404);
        }
    }
    public function updateUserInfo(Request $request)
    {
        $data = $request->only(['email', 'full_name', 'diaChi', 'soDienThoai']);
    
        if (empty($data['email'])) {
            return response()->json(['error' => 'Thiếu email'], 400);
        }
    
        // Lọc ra các field cần update (trừ email)
        $updateFields = [];
        if (isset($data['full_name'])) $updateFields['full_name'] = $data['full_name'];
        if (isset($data['diaChi'])) $updateFields['diaChi'] = $data['diaChi'];
        if (isset($data['soDienThoai'])) $updateFields['soDienThoai'] = $data['soDienThoai'];
    
        if (empty($updateFields)) {
            return response()->json(['error' => 'Không có dữ liệu cập nhật'], 400);
        }
    
        try {
            $updated = DB::table('roleadminuser')
                ->where('email', $data['email'])
                ->update($updateFields);
    
            if ($updated) {
                return response()->json(['message' => 'Cập nhật thành công']);
            } else {
                return response()->json(['error' => 'Không tìm thấy hoặc dữ liệu không thay đổi'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Lỗi khi cập nhật thông tin', 'detail' => $e->getMessage()], 500);
        }
    }
}
