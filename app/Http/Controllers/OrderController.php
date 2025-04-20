<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;//checkout
use Illuminate\Support\Facades\Validator;//checkout

class OrderController extends Controller
{
    public function getOrdersByEmail(Request $request)
    {
        $email = $request->query('email');

        if (empty($email)) {
            return response()->json(['message' => 'Missing email parameter'], 400);
        }

        try {
            $orders = DB::select("
                SELECT h.maHD, h.trangThaiHD, h.ngayLapHD, h.phuongThucThanhToan, h.phuongThucGiaoHang, h.ngayNhan,
                       ct.soLuong, ct.donGia, s.tenSach, s.maSach, s.anh, s.giaKM, 
                       h.tenNguoiNhan, h.diaChiNguoiNhan, h.soDienThoaiHD
                FROM hoadon h
                LEFT JOIN chitiethoadon ct ON h.maHD = ct.maHD
                LEFT JOIN sach s ON ct.maSach = s.maSach
                WHERE h.email = ? AND (h.trangThaiHD = 'dagiao' OR h.trangThaiHD = 'danggiao' OR h.trangThaiHD = 'choxuly')
                ORDER BY 
                    CASE 
                        WHEN h.trangThaiHD = 'dangiao' THEN 1
                        WHEN h.trangThaiHD = 'choxuly' THEN 2
                    END,
                    h.ngayLapHD DESC
            ", [$email]);

            if (empty($orders)) {
                return response()->json(['status' => 'error', 'message' => 'No orders found']);
            }

            // Tách ảnh thành mảng nếu là chuỗi nhiều ảnh
            foreach ($orders as &$order) {
                if (!empty($order->anh)) {
                    $order->anh = explode(',', $order->anh);
                } else {
                    $order->anh = [];
                }
            }

            return response()->json(['status' => 'success', 'data' => $orders], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Server error', 'detail' => $e->getMessage()], 500);
        }
    }
    public function checkOut(Request $request)
    {
        $data = $request->json()->all();

        $requiredFields = [
            'email', 'tenNguoiNhan', 'diaChiNguoiNhan', 'soDienThoaiHD',
            'phuongThucThanhToan', 'phuongThucGiaoHang', 'giohang'
        ];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return response()->json([
                    'success' => false,
                    'message' => "Thiếu thông tin: $field"
                ]);
            }
        }

        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $tenNguoiNhan = htmlspecialchars($data['tenNguoiNhan']);
        $diaChiNguoiNhan = htmlspecialchars($data['diaChiNguoiNhan']);
        $soDienThoaiHD = htmlspecialchars($data['soDienThoaiHD']);
        $phuongThucThanhToan = htmlspecialchars($data['phuongThucThanhToan']);
        $phuongThucGiaoHang = htmlspecialchars($data['phuongThucGiaoHang']);
        $giohang = $data['giohang'];

        $trangThaiHD = "choxuly";
        $ngayLapHD = date("Y-m-d");
        $maHD = "HD" . date('Ymd') . sprintf('%04d', mt_rand(0, 9999));

        DB::beginTransaction();

        try {
            DB::table('hoadon')->insert([
                'maHD' => $maHD,
                'email' => $email,
                'tenNguoiNhan' => $tenNguoiNhan,
                'diaChiNguoiNhan' => $diaChiNguoiNhan,
                'soDienThoaiHD' => $soDienThoaiHD,
                'ngayLapHD' => $ngayLapHD,
                'trangThaiHD' => $trangThaiHD,
                'phuongThucThanhToan' => $phuongThucThanhToan,
                'phuongThucGiaoHang' => $phuongThucGiaoHang,
            ]);

            foreach ($giohang as $item) {
                $maSach = $item['maSach'];
                $soLuong = (int) $item['soLuong'];

                $sach = DB::table('sach')->where('maSach', $maSach)->first();

                if (!$sach) {
                    throw new \Exception("Sản phẩm không tồn tại");
                }

                $donGia = $sach->giaKM > 0 ? $sach->giaKM : $sach->gia;
                $donGia *= $soLuong;

                DB::table('chitiethoadon')->insert([
                    'maSach' => $maSach,
                    'maHD' => $maHD,
                    'soLuong' => $soLuong,
                    'donGia' => $donGia
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công!',
                'maHD' => $maHD
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lỗi khi đặt hàng: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đặt hàng: ' . $e->getMessage()
            ]);
        }
    }
}

