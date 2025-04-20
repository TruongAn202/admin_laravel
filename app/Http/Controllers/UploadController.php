<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $originalName = $image->getClientOriginalName(); // Giữ tên gốc của ảnh

            // Lưu ảnh vào thư mục public/images với tên gốc
            $path = $image->storeAs('images', $originalName, 'public');

            return response()->json([
                'message' => 'Image uploaded successfully!',
                'path' => $path
            ], 200);
        }

        return response()->json(['message' => 'No image uploaded.'], 400);
    }
}
