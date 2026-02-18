<?php

namespace App\Modules\Template\Services;

use App\Exceptions\ServerException;
use App\Modules\Template\Models\TemplateType;
use App\Modules\Template\Repositories\TemplateTypeRepository;
use App\Modules\Workspace\Repositories\WorkspaceRepository;
use App\Services\AbstractService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TemplateTypeService extends AbstractService
{
    private ?int $workspaceId;

    public function __construct(
        Request $request,
        private readonly TemplateTypeRepository $repository,
        private readonly WorkspaceRepository $workspaceRepository
    ) {
        parent::__construct($request);
        $this->workspaceId = $request->route('workspace_id');
    }

    public function index(): Collection
    {
        return $this->repository->list($this->workspaceId, $this->fields, $this->filters, $this->orderBy, $this->meta);
    }

    public function create(): TemplateType
    {
        $data = $this->request->all();

        if ($this->workspaceId !== null) {
            $data['workspace_id'] = $this->workspaceId;
        }

        $templateType = $this->repository->create($data);

        return $templateType->refresh();
    }

    public function update(int $templateTypeId): TemplateType
    {
        $templateType = $this->repository->get($templateTypeId);
        if (!$templateType) {
            throw new ServerException('Тип шаблона не найден', 404);
        }

        // Проверяем доступ к workspace типа шаблона (если он отличается от workspace_id в роуте)
        if ($templateType->workspace_id !== null && $templateType->workspace_id !== $this->workspaceId) {
            $this->workspaceRepository->get($templateType->workspace_id);
        }
        
        $data = $this->request->all();

        if ($this->workspaceId !== null) {
            $data['workspace_id'] = $this->workspaceId;
        }

        $this->repository->update($templateType, $data);

        return $templateType->refresh();
    }

    public function delete(int $templateTypeId): bool
    {
        $templateType = $this->repository->get($templateTypeId);
        if (!$templateType) {
            throw new ServerException('Тип шаблона не найден', 404);
        }

        // Проверяем доступ к workspace типа шаблона (если он отличается от workspace_id в роуте)
        if ($templateType->workspace_id !== null && $templateType->workspace_id !== $this->workspaceId) {
            $this->workspaceRepository->get($templateType->workspace_id);
        }

        return $this->repository->delete($templateType);
    }
}

