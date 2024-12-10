<?php

namespace App\Http\Controllers;

use App\Http\Requests\Issue\FilterForAdminAndEmployee;
use App\Http\Requests\Issue\FilterForUserRequest;
use App\Http\Requests\Issue\FilterRequest;
use App\Http\Requests\Issue\StoreIssueRequest;
use App\Http\Requests\Issue\FinishIssueStatusRequest;
use App\Http\Requests\Issue\UpdateStatusRequest;
use App\Http\Resources\IssueResource;
use App\Models\Issue;
use App\Http\Services\IssueService;
use App\Traits\ResponseTrait;
use Cache;
use Illuminate\Support\Facades\Auth;

class IssueController extends Controller
{
    use ResponseTrait;
    protected $issueService;

    public function __construct(IssueService $issueService)
    {
        $this->issueService = $issueService;
    }

    /**
     * Display a listing of the issues related to lawyer.
     * @param \App\Http\Requests\Issue\FilterRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index(FilterRequest $request)
    {
        $response = $this->issueService->getList($request->validated());
        return $response['status']
            ? $this->success("data", $response['issues'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Store a newly created issue in storage by lawyer.
     * @param \App\Http\Requests\Issue\StoreIssueRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function store(StoreIssueRequest $request)
    {
        $response = $this->issueService->storeIssue($request->validated());
        return $response['status']
            ? $this->success('msg', 'Created Issue Successfully', 201)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified issue by lawyer.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $issue = Cache::remember('issue_' . $id, 600, function () use ($id) {
            return Issue::where("id", $id)->where('lawyer_id', Auth::guard('lawyer')->id())->first();
        });
        if (!$issue) {
            return $this->error('Issue Not Found', 404);
        }

        return $this->success('issue', new IssueResource($issue), 200);
    }

    /**
     * Change issue status by lawyer
     * @param \App\Http\Requests\Issue\UpdateStatusRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateStatus(UpdateStatusRequest $request, $id)
    {
        $issue = Cache::remember('issue_' . $id, 600, function () use ($id) {
            return Issue::where("id", $id)->where('lawyer_id', Auth::guard('lawyer')->id())->first();
        });
        if (!$issue) {
            return $this->error('Issue Not Found', 404);
        }

        $response = $this->issueService->changeStatus($request->validated(), $issue);
        return $response['status']
            ? $this->success('msg', 'Changed Issue Status Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Issue is finishing by lawyer
     * @param \App\Http\Requests\Issue\FinishIssueStatusRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function endIssue(FinishIssueStatusRequest $request, $id)
    {
        $issue = Cache::remember('issue_' . $id, 600, function () use ($id) {
            return Issue::where("id", $id)->where('lawyer_id', Auth::guard('lawyer')->id())->first();
        });
        if (!$issue) {
            return $this->error('Issue Not Found', 404);
        }

        $response = $this->issueService->endIssue($request->validated(), $issue);
        return $response['status']
            ? $this->success('msg', 'Ended Issue Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Remove the specified issue from storage by lawyer.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $response = $this->issueService->removeIssue($id);
        return $response['status']
            ? $this->success('msg', 'Deleted Issue Successfully', 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Get list of issues forward to AI
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function issuesAI()
    {
        $issues = Cache::remember('issues', 1200, function () {
            return Issue::all();
        });

        return $this->success('issues', IssueResource::collection($issues), 200);
    }

    /**
     * Display a listing of the issues related to user.
     * @param \App\Http\Requests\Issue\FilterForUserRequest $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function indexForUser(FilterForUserRequest $request)
    {
        $response = $this->issueService->getListForUser($request->validated());
        return $response['status']
            ? $this->success("data", $response['issues'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified issue related to user.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function showForUser($id)
    {
        $issue = Cache::remember('issue_' . Auth::guard('api')->id(), 600, function () use ($id) {
            return Issue::whereHas('agency', function ($query) {
                return $query->where('user_id', Auth::guard('api')->id());
            })->with('agency')->find($id);
        });

        if (!$issue) {
            return $this->error('Issue Not Found', 404);
        }
        return $this->success('issue', new IssueResource($issue), 200);
    }

    /**
     * Get listing of the issues by admin and employee
     * @param \App\Http\Requests\Issue\FilterForAdminAndEmployee $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getForAdminAndEmployee(FilterForAdminAndEmployee $request)
    {
        $response = $this->issueService->AdminAndEmployee($request->validated());
        return $response['status']
            ? $this->success("data", $response['issues'], 200)
            : $this->error($response['msg'], $response['code']);
    }

    /**
     * Display the specified issue by admin and employee.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function showForAdminAndEmployee($id)
    {
        if (!Auth::guard('api')->check() || Auth::guard('api')->user()->hasRole('user')) {
            return $this->error('This action is unauthorized', 422);
        }
        $issue = Cache::remember('issue_' . $id, 600, function () use ($id) {
            return Issue::find($id);
        });

        if (!$issue) {
            return $this->error('Issue Not Found', 404);
        }
        return $this->success('issue', new IssueResource($issue), 200);
    }
}
