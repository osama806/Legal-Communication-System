<?php

namespace App\Http\Services;

use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use App\Models\Lawyer;
use App\Notifications\UserToLawyerNotification;
use App\Traits\PaginateResourceTrait;
use Auth;
use Cache;
use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Support\Facades\Notification;
use Log;

class AgencyService
{
    use PaginateResourceTrait;

    /**
     * Get listing of the agencies.
     * @param array $data
     * @return array
     */
    public function getList(array $data)
    {
        $agencies = Cache::remember('agencies', 1200, function () use ($data) {
            return Agency::filter($data)->paginate($data['per_page'] ?? 10);
        });

        if ($agencies->isEmpty()) {
            return [
                'status' => false,
                'msg' => "Not Found Any Agency!",
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'agencies' => $this->formatPagination($agencies, AgencyResource::class, 'agencies')
        ];
    }

    /**
     * Create new agency request with send notification to lawyer
     * @param array $data
     * @return array
     */
    public function createAgency(array $data)
    {
        $lawyer = Cache::remember('lawyer' . $data["lawyer_id"], 600, function () use ($data) {
            return Lawyer::find($data["lawyer_id"]);
        });

        try {
            // تحقق من عدد الطلبات التي قام بها المستخدم في اليوم الحالي
            $userId = Auth::guard('api')->id();
            $todayRequestsCount = Agency::where('user_id', $userId)->where('lawyer_id', $lawyer->id)
                ->whereDate('created_at', Carbon::today())
                ->count();

            // إذا تجاوز المستخدم ثلاثة طلبات في نفس اليوم، قم بإرجاع رسالة
            if ($todayRequestsCount >= 3) {
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

            if ($agency->wasRecentlyCreated) {
                Notification::send($lawyer, new UserToLawyerNotification($agency));
                DB::commit();

                Cache::forget('agencies');
                Cache::forget('agenciesForUser');
                Cache::forget('agenciesForLawyer');
                Cache::forget('agenciesForRepresentative');
                return ['status' => true];
            } else {
                return [
                    'status' => false,
                    'msg' => 'You Send Request to Same Lawyer Already!',
                    'code' => 403
                ];
            }
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

        Cache::forget('agencies');
        Cache::forget('agenciesForUser');
        Cache::forget('agenciesForLawyer');
        Cache::forget('agenciesForRepresentative');
        return ['status' => true];
    }

    /**
     * Get listing of the agencies related to user.
     * @param array $data
     * @return array
     */
    public function getListForUser(array $data)
    {
        $agencies = Cache::remember('agenciesForUser', 1200, function () use ($data) {
            return Agency::filter($data)->where('user_id', Auth::guard('api')->id())->paginate($data['per_page'] ?? 10);
        });

        if ($agencies->isEmpty()) {
            return [
                'status' => false,
                'msg' => "Not Found Any Agency!",
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'agencies' => $this->formatPagination($agencies, AgencyResource::class, 'agencies')
        ];
    }

    /**
     * Get listing of the agencies related to lawyer.
     * @param array $data
     * @return array
     */
    public function getListForLawyer(array $data)
    {
        $agencies = Cache::remember('agenciesForLawyer', 1200, function () use ($data) {
            return Agency::filter($data)->where('lawyer_id', Auth::guard('lawyer')->id())->paginate($data['per_page'] ?? 10);
        });

        if ($agencies->isEmpty()) {
            return [
                'status' => false,
                'msg' => "Not Found Any Agency!",
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'agencies' => $this->formatPagination($agencies, AgencyResource::class, 'agencies')
        ];
    }

    /**
     * Get listing of the agencies related to representative.
     * @param array $data
     * @return array
     */
    public function getListForRepresentative(array $data)
    {
        $agencies = Cache::remember('agenciesForRepresentative', 1200, function () use ($data) {
            return Agency::filter($data)->where('representative_id', Auth::guard('representative')->id())->paginate($data['per_page'] ?? 10);
        });

        if ($agencies->isEmpty()) {
            return [
                'status' => false,
                'msg' => "Not Found Any Agency!",
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'agencies' => $this->formatPagination($agencies, AgencyResource::class, 'agencies')
        ];
    }
}
