<?php

namespace App\Http\Controllers;

use App\Http\Resources\AgencyResource;
use App\Models\Agency;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    use ResponseTrait;

    /**
     * Display a listing of the agencies.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $agencies = Agency::all();
        return $this->getResponse('agencies', AgencyResource::collection($agencies), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
