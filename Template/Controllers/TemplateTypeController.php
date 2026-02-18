<?php

namespace App\Modules\Template\Controllers;

use App\Exceptions\ApiValidationException;
use App\Http\Controllers\Controller;
use App\Modules\Template\Services\TemplateTypeService;
use App\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TemplateTypeController extends Controller
{
    public function index(TemplateTypeService $service): JsonResponse
    {
        return ApiResponse::get($service->index());
    }

    public function create(TemplateTypeService $service): JsonResponse
    {
        return ApiResponse::get($service->create(), ResponseAlias::HTTP_CREATED);
    }

    public function update(TemplateTypeService $service, int $workspaceId, int $templateTypeId): JsonResponse
    {
        return ApiResponse::get($service->update($templateTypeId));
    }

    public function delete(TemplateTypeService $service, int $workspaceId, int $templateTypeId): JsonResponse
    {
        $service->delete($templateTypeId);
        return ApiResponse::get(null, ResponseAlias::HTTP_NO_CONTENT);
    }
}

