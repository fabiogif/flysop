<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenantResource;
use App\Services\TenantService;
use Illuminate\Http\Request;

class TenantApiController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    public function index(Request $request)
    {
        $pre_page = (int)$request->get('pre_page', 15);

        $tenant = $this->tenantService->getAllTenants($pre_page);

        return TenantResource::collection($tenant);
    }

    public function show($uuid)
    {
        $tenant = $this->tenantService->getTenantByUuid($uuid);

        if (!$tenant) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return new TenantResource($tenant);
    }
}
