<?php

namespace App\Http\Controllers;

use App\Http\Requests\Issue\FilterAiRequest;
use App\Http\Requests\Issue\FilterRequest;
use App\Http\Requests\Issue\StoreIssueRequest;
use App\Http\Requests\Issue\FinishIssueStatusRequest;
use App\Http\Requests\Issue\UpdateStatusRequest;
use App\Http\Resources\IssueResource;
use App\Models\Issue;
use App\Http\Services\IssueService;
use App\Traits\ResponseTrait;
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
            ? $this->getResponse("data", $response['issues'], 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
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
            ? $this->getResponse('msg', 'Created Issue Successfully', 201)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Display the specified issue.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $issue = Issue::where("id", $id)->where('lawyer_id', Auth::guard('lawyer')->id())->first();
        if (!$issue) {
            return $this->getResponse('error', 'Issue Not Found', 404);
        }
        return $this->getResponse('issue', new IssueResource($issue), 200);
    }

    /**
     * Change issue status by lawyer
     * @param \App\Http\Requests\Issue\UpdateStatusRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateStatus(UpdateStatusRequest $request, $id)
    {
        $issue = Issue::where("id", $id)->where('lawyer_id', Auth::guard('lawyer')->id())->first();
        if (!$issue) {
            return $this->getResponse('error', 'Issue Not Found', 404);
        }
        $response = $this->issueService->changeStatus($request->validated(), $issue);

        return $response['status']
            ? $this->getResponse('msg', 'Changed Issue Status Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Issue is finishing by lawyer
     * @param \App\Http\Requests\Issue\FinishIssueStatusRequest $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function endIssue(FinishIssueStatusRequest $request, $id)
    {
        $issue = Issue::where("id", $id)->where('lawyer_id', Auth::guard('lawyer')->id())->first();
        if (!$issue) {
            return $this->getResponse('error', 'Issue Not Found', 404);
        }
        $response = $this->issueService->endIssue($request->validated(), $issue);

        return $response['status']
            ? $this->getResponse('msg', 'Ended Issue Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Remove the specified issue from storage by lawyer.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        if (Auth::guard('lawyer')->check() && Auth::guard('lawyer')->user()->role->name !== 'lawyer') {
            return $this->getResponse('error', 'This action is unauthorized', 422);
        }
        $issue = Issue::where("id", $id)->where('lawyer_id', Auth::guard('lawyer')->id())->first();

        $response = $this->issueService->removeIssue($issue);
        return $response['status']
            ? $this->getResponse('msg', 'Deleted Issue Successfully', 200)
            : $this->getResponse('error', $response['msg'], $response['code']);
    }

    /**
     * Get list of issues forward to AI
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function issuesAI()
    {
        $issues = Issue::all();
        return $this->getResponse('issues', IssueResource::collection($issues), 200);
    }
}
