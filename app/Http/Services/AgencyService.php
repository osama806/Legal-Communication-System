<?php

namespace App\Http\Services;

use App\Events\Agency\User\RequestNotificationEvent;
use App\Http\Resources\AgencyResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Models\Agency;
use App\Models\Lawyer;
use App\Notifications\UserToLawyerNotification;
use App\Traits\PaginateResourceTrait;
use Carbon\Carbon;

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
        $lawyer = Cache::remember('lawyer_' . $data["lawyer_id"], 600, function () use ($data) {
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
                event(new RequestNotificationEvent($agency));
                Notification::send($lawyer, new UserToLawyerNotification($agency));
                DB::commit();

                Cache::forget('agencies');
                return ['status' => true];
            } else {
                return [
                    'status' => false,
                    'msg' => 'You Send Request to Same Lawyer Already!',
                    'code' => 403
                ];
            }
        } catch (\Exception $e) {
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
     * Get listing of the agencies related to user, lawyer & representative.
     * @param array $data
     * @return array
     */
    public function getListForAll(array $data)
    {
        $guard = $this->checkGuard($data['role']);
        if (!$guard) {
            return [
                'status' => false,
                'msg' => 'No authenticated person found for the provided role!',
                'code' => 403
            ];
        }
        if (Auth::guard($guard)->check()) {
            if ($data['role'] !== Auth::guard($guard)->user()->role->name) {
                return [
                    'status' => false,
                    'msg' => 'This action is unauthorized',
                    'code' => 403
                ];
            }
        } else {
            return [
                'status' => false,
                'msg' => 'Role Unauthenticated',
                'code' => 401
            ];
        }

        $columnCondition = $guard === 'api' ? 'user_id' : $guard . '_id';
        $agencies = Agency::filter($data)->where($columnCondition, Auth::guard($guard)->id())->paginate($data['per_page'] ?? 10);

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
     * Display the specified agency related to user, lawyer & representative.
     * @param array $data
     * @param mixed $id
     * @return array
     */
    public function dispayOne(array $data, $id)
    {
        $guard = $this->checkGuard($data['role']);
        if (!$guard) {
            return [
                'status' => false,
                'msg' => 'No authenticated person found for the provided role!',
                'code' => 403
            ];
        }
        if (Auth::guard($guard)->check()) {
            if ($data['role'] !== Auth::guard($guard)->user()->role->name) {
                return [
                    'status' => false,
                    'msg' => 'This action is unauthorized',
                    'code' => 403
                ];
            }
        } else {
            return [
                'status' => false,
                'msg' => 'Role Unauthenticated',
                'code' => 401
            ];
        }

        $columnCondition = $guard === 'api' ? 'user_id' : $guard . '_id';
        $agency = Cache::remember('agency_' . $id, 600, function () use ($id, $columnCondition, $guard) {
            return Agency::where('id', $id)->where($columnCondition, Auth::guard($guard)->id())->first();
        });

        if (!$agency) {
            return [
                'status' => false,
                'msg' => 'Agency Not Found',
                'code' => 404
            ];
        }
        return [
            'status' => true,
            'agency' => $agency
        ];
    }

    /**
     * Check guard
     * @param mixed $role
     * @return string|null
     */
    private function checkGuard($role): string|null
    {
        $guard = match ($role) {
            'user' => 'api',
            'lawyer' => 'lawyer',
            'representative' => 'representative',
            default => null
        };

        return $guard;
    }
}
