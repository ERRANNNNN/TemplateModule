<?php

namespace App\Modules\Template\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Template\Services\TemplateService;
use App\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class TemplateController extends Controller
{
    public function index(TemplateService $service): JsonResponse
    {
        return ApiResponse::get($service->index());
    }

    public function create(TemplateService $service): JsonResponse
    {
        return ApiResponse::get($service->create(), ResponseAlias::HTTP_CREATED);
    }

    public function update(TemplateService $service, int $workspaceId, int $templateId): JsonResponse
    {
        return ApiResponse::get($service->update($templateId));
    }

    public function delete(TemplateService $service, int $workspaceId, int $templateId): JsonResponse
    {
        $service->delete($templateId);
        return ApiResponse::get(null, ResponseAlias::HTTP_NO_CONTENT);
    }

    public function getByOwner(TemplateService $service, string $module, int $ownerId): JsonResponse
    {
        return ApiResponse::get($service->getByOwner($module, $ownerId));
    }
}

