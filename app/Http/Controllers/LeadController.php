<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LeadController extends Controller
{
    /**
     * Lưu thông tin đăng ký từ form
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'license' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng điền đầy đủ thông tin',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $lead = Lead::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'license' => $request->license,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất.',
                'data' => $lead
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
