<?php

namespace App\Http\Services;

use App\Http\Resources\IssueResource;
use App\Traits\PaginateResourceTrait;
use Auth;
use Cache;
use Exception;
use App\Models\Issue;
use App\Models\Agency;

class IssueService
{
    use PaginateResourceTrait;

    /**
     * Get listing of the issues.
     * @param array $data
     * @return array
     */
    public function getList(array $data)
    {
        $issues = Cache::remember("issues", 1200, function () use ($data) {
            return Issue::filter($data)->where('lawyer_id', Auth::guard('lawyer')->id())->paginate($data['per_page'] ?? 10);
        });

        if ($issues->isEmpty()) {
            return [
                'status' => false,
                'msg' => "Not Found Any Issue!",
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'issues' => $this->formatPagination($issues, IssueResource::class, 'issues'),
        ];
    }

    /**
     * Store a newly created issue in storage.
     * @param array $data
     * @return array
     */
    public function storeIssue(array $data)
    {
        $agency = Cache::remember('agency' . $data["agency_id"], 600, function () use ($data) {
            return Agency::find($data["agency_id"]);
        });

        if ((!$agency->is_active && $agency->status === 'pending') || $agency->status === 'rejected') {
            return [
                'status' => false,
                'msg' => "Agency Not Found",
                'code' => 404
            ];
        }

        if (!$agency->is_active) {
            return [
                'status' => false,
                'msg' => "This Agency is Expired",
                'code' => 403
            ];
        }

        try {
            Issue::create([
                "base_number" => $data['base_number'],
                "record_number" => $data['record_number'],
                "agency_id" => $agency->id,
                'lawyer_id' => Auth::guard('lawyer')->id(),
                "court_name" => $data['court_name'],
                "type" => $data['type'],
                "start_date" => $data['start_date'],
                "estimated_cost" => $data['estimated_cost'],
            ]);

            Cache::forget('issues');
            return ['status' => true];
        } catch (Exception $exception) {
            return [
                'status' => false,
                'msg' => $exception->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Finish the specified issue.
     * @param array $data
     * @param \App\Models\Issue $issue
     * @return array
     */
    public function endIssue(array $data, Issue $issue)
    {
        try {
            $agency = Cache::remember('agency' . $issue->agency_id, 600, function () use ($issue) {
                return Agency::find($issue->agency_id);
            });

            if (!$agency->is_active) {
                return [
                    'status' => false,
                    'msg' => "This Agency is Expired",
                    'code' => 403
                ];
            }

            if (!$issue->is_active || $issue->end_date !== null || $issue->success_rate !== null) {
                return [
                    'status' => false,
                    'msg' => 'This issue finished already',
                    'code' => 400
                ];
            }

            $filteredData = array_filter($data, function ($value) {
                return !is_null($value) && trim($value) !== '';
            });

            if (count($filteredData) < 1) {
                return [
                    'status' => false,
                    'msg' => 'Not Found Any Data to Update',
                    'code' => 404
                ];
            }

            if ($filteredData['end_date'] <= $issue->start_date) {
                return [
                    'status' => false,
                    'msg' => 'End date must be after start date',
                    'code' => 400
                ];
            }

            if (count($filteredData) < 2) {
                return [
                    'status' => false,
                    'msg' => 'You must enter success_rate & end_date',
                    'code' => 403
                ];
            }

            $issue->update($filteredData);
            $issue->is_active = false;
            $issue->save();

            Cache::forget('issue' . $issue->id);
            return ['status' => true];

        } catch (Exception $exception) {
            return [
                'status' => false,
                'msg' => $exception->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Update status the specified issue.
     * @param array $data
     * @param \App\Models\Issue $issue
     * @return array
     */
    public function changeStatus(array $data, Issue $issue)
    {
        try {
            $agency = Cache::remember('agency' . $issue->agency_id, 600, function () use ($issue) {
                return Agency::find($issue->agency_id);
            });

            if (!$agency->is_active) {
                return [
                    'status' => false,
                    'msg' => "This Agency is Expired",
                    'code' => 403
                ];
            }

            if (!$issue->is_active || $issue->end_date !== null || $issue->success_rate !== null) {
                return [
                    'status' => false,
                    'msg' => 'This issue finished already',
                    'code' => 400
                ];
            }

            $issue->status = $data['status'];
            $issue->save();

            Cache::forget('issue' . $issue->id);
            return ['status' => true];

        } catch (Exception $exception) {
            return [
                'status' => false,
                'msg' => $exception->getMessage(),
                'code' => 500,
            ];
        }
    }

    /**
     * Delete issue by lawyer
     * @param \App\Models\Issue $issue
     * @return array
     */
    public function removeIssue(string $id)
    {
        if (Auth::guard('lawyer')->check() && Auth::guard('lawyer')->user()->role->name !== 'lawyer') {
            return [
                'status' => false,
                'msg' => 'This action is unauthorized',
                'code' => 422
            ];
        }

        $issue = Issue::where("id", $id)->where('lawyer_id', Auth::guard('lawyer')->id())->first();
        if (!$issue) {
            return [
                'status' => false,
                'msg' => 'Issue Not Found',
                'code' => 404
            ];
        }

        $agency = Cache::remember('agency' . $issue->agency_id, 600, function () use ($issue) {
            return Agency::find($issue->agency_id);
        });

        if (!$agency->is_active) {
            return [
                'status' => false,
                'msg' => 'This Agency is Expired',
                'code' => 403
            ];
        }

        $issue->delete();
        Cache::forget('issues');
        return ['status' => true];
    }

    /**
     * Get listing of the issues related to user.
     * @param array $data
     * @return array
     */
    public function getListForUser(array $data)
    {
        $issues = Cache::remember("issuesUser" . Auth::guard('api')->id(), 1200, function () use ($data) {
            return Issue::whereHas('agency', function ($query) {
                $query->where('user_id', Auth::guard('api')->id());
            })
                ->with('agency')
                ->filter($data)
                ->paginate($data['per_page'] ?? 10);
        });

        if ($issues->isEmpty()) {
            return [
                'status' => false,
                'msg' => "Not Found Any Issue!",
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'issues' => $this->formatPagination($issues, IssueResource::class, 'issues'),
        ];
    }

    /**
     * Get listing of the issues forward to admin and employee.
     * @param array $data
     * @return array
     */
    public function AdminAndEmployee(array $data)
    {
        $issues = Cache::remember("issuesAdminEmployee", 1200, function () use ($data) {
            return Issue::filter($data)->paginate($data['per_page'] ?? 10);
        });

        if ($issues->isEmpty()) {
            return [
                'status' => false,
                'msg' => "Not Found Any Issue!",
                'code' => 404
            ];
        }

        return [
            'status' => true,
            'issues' => $this->formatPagination($issues, IssueResource::class, 'issues'),
        ];
    }
}
