<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Log;
use Auth;
use Exception;
use App\Models\User;
use App\Models\Agency;
use App\Models\Lawyer;
use App\Models\Representative;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Notification;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use App\Notifications\RepresentativeToAllNotification;

class RepresentativeService
{
    /**
     * Reply notification response to user and lawyer
     * @param array $data
     * @return array
     */
    public function sendResponse(array $data)
    {
        $agency = Agency::find($data['agency_id']);
        $user = User::find($agency->user_id);
        $lawyer = Lawyer::find($agency->lawyer_id);
        try {
            DB::beginTransaction();
            $agency->sequential_number = $data['sequential_number'];
            $agency->record_number = $data['record_number'];
            $agency->place_of_issue = $data['place_of_issue'];
            $agency->status = $data['status'];

            if ($data['status'] === 'approved') {
                $agency->is_active = true;
            }

            $agency->save();
            Notification::send([$user, $lawyer], new RepresentativeToAllNotification($agency));
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
}
