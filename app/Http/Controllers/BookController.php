<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;//co cai nay moi tim kiem duoc


class BookController extends Controller
{
    public function layNgauNhien(): JsonResponse
    {
        try {
            $books = DB::table('sach')
                ->leftJoin('tacgia', 'sach.maTG', '=', 'tacgia.maTG')
                ->leftJoin('loaisach', 'sach.maLoai', '=', 'loaisach.maLoai')
                ->select('sach.*', 'tacgia.tenTG', 'loaisach.tenLoai')
                ->inRandomOrder()
                ->limit(6)
                ->get();

            if ($books->isEmpty()) {
                return response()->json(["message" => "Không có dữ liệu"], 200, [], JSON_UNESCAPED_UNICODE);
            }

            return response()->json($books, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json([
                "error" => "Lỗi server: " . $e->getMessage()
            ], 500);
        }
    }
    public function layTheoLoai(): JsonResponse
    {
        try {
            $books = DB::table('sach')
                ->leftJoin('tacgia', 'sach.maTG', '=', 'tacgia.maTG')
                ->leftJoin('loaisach', 'sach.maLoai', '=', 'loaisach.maLoai')
                ->select('sach.*', 'tacgia.tenTG', 'loaisach.tenLoai')
                ->inRandomOrder()
                ->limit(9)
                ->get();

            if ($books->isEmpty()) {
                return response()->json(["message" => "Không có dữ liệu"], 200, [], JSON_UNESCAPED_UNICODE);
            }

            return response()->json($books, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            return response()->json([
                "error" => "Lỗi server: " . $e->getMessage()
            ], 500);
        }
    }
    public function getProductById(Request $request): JsonResponse //lay theo id
    {
        try {
            $maSach = trim($request->query('maSach'));
    
            if (empty($maSach)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Thiếu tham số maSach'
                ], 400, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
    
            // Lấy thông tin sách từ database
            $product = DB::table('sach')->where('maSach', $maSach)->first();
    
            if ($product) {
                return response()->json([
                    'status' => 'success',
                    'data' => $product
                ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy sản phẩm'
                ], 404, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi server: ' . $e->getMessage()
            ], 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }
    public function search(Request $request) //tim kiem
    {
        $query = $request->query('query');

        if (!$query) {
            return response()->json(['error' => 'Thiếu từ khóa tìm kiếm'], 400);
        }

        $books = DB::table('sach')
            ->where('tenSach', 'like', "%$query%")
            ->get();

        return response()->json($books);
    }
}
