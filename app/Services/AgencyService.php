<?php

namespace App\Services;

use App\Models\Agency;

class AgencyService
{
    /**
     * Create new agency request with send notification to lawyer
     * @param array $data
     * @return array
     */
    public function createAgency(array $data)
    {
        $lawyer = Lawyer::find($data["lawyer_id"]);
        try {
            // تحقق من عدد الطلبات التي قام بها المستخدم في اليوم الحالي
            $userId = Auth::guard('api')->id();
            $todayRequestsCount = Agency::where('user_id', $userId)->where('lawyer_id', $lawyer->id)
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($todayRequestsCount >= 3) {
                // إذا تجاوز المستخدم ثلاثة طلبات في نفس اليوم، قم بإرجاع رسالة
                return [
                    'status' => false,
                    'msg' => "You have exceeded your limit for requesting agencies today. Please try again tomorrow.",
                    'code' => 403
                ];
            }

            DB::beginTransaction();
            $agency = Agency::firstOrCreate([
                "user_id" => Auth::guard('api')->id(),
                "lawyer_id" => $data["lawyer_id"],
                "cause" => $data['cause']
            ]);

            Notification::send($lawyer, new UserToLawyerNotification($agency));
            DB::commit();

            return [
                'status' => true,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => false,
                'msg' => $e->getMessage(),
                'code' => 500
            ];
        }
    }

    /**
     * Agency isolate by user
     * @param \App\Models\Agency $agency
     * @return array
     */
    public function isolate(Agency $agency)
    {
        if (!$agency->is_active && $agency->status === 'pending') {
            return [
                'status' => false,
                'msg' => 'Agency Not Found',
                'code' => 404
            ];
        }

        if (!$agency->is_active || $agency->status !== 'approved') {
            return [
                'status' => false,
                'msg' => 'Agency is Expired',
                'code' => 404
            ];
        }

        $agency->is_active = false;
        $agency->save();
        return [
            'status' => true,
        ];
    }
}
