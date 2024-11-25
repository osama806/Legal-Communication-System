<?php

namespace App\Services;

use Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;
use Notification;
use App\Models\Agency;
use App\Models\Lawyer;
use App\Models\Representative;
use App\Notifications\LawyerToRepresentativeNotification;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage;


class LawyerService
{
    /**
     * Update agency record and send notification to representative
     * @param array $data
     * @return array
     */
    public function send(array $data)
    {
        $agency = Agency::find($data['agency_id']);
        $representative = Representative::find($data['representative_id']);
        try {
            DB::beginTransaction();
            $agency->representative_id = $data['representative_id'];
            $agency->type = $data['type'];
            $agency->authorizations = $data['authorizations'];
            $agency->exceptions = $data['exceptions'];
            $agency->save();
            Notification::send($representative, new LawyerToRepresentativeNotification($agency));
            DB::commit();

            return ['status' => true];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Get lawyer avatar
     * @param mixed $filename
     * @return array
     */
    public function avatar($filename)
    {
        // تحقق مما إذا كانت الصورة موجودة في التخزين
        if (!Storage::disk('public')->exists("Images/{$filename}")) {
            return [
                'status' => false,
                'msg' => 'Image not found',
                'code' => 404
            ];
        }

        // جلب محتوى الصورة
        $fileContent = Storage::disk('public')->get("Images/{$filename}");
        $mimeType = Storage::disk('public')->mimeType("Images/{$filename}");

        // عرض الصورة مع تحديد نوع المحتوى
        return [
            'status' => true,
            'avatar' => $fileContent,
            'type' => $mimeType
        ];
    }
}
