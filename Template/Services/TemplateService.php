<?php

namespace App\Modules\Template\Services;

use App\Exceptions\ServerException;
use App\Modules\Template\Models\Template;
use App\Modules\Template\Repositories\TemplateRepository;
use App\Modules\Workspace\Repositories\WorkspaceRepository;
use App\Services\AbstractService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Str;

class TemplateService extends AbstractService
{
    private ?int $workspaceId;

    public function __construct(
        Request $request,
        private readonly TemplateRepository $repository,
        private readonly WorkspaceRepository $workspaceRepository
    ) {
        parent::__construct($request);
        $this->workspaceId = $request->route('workspace_id');
    }

    public function index(): Collection
    {
        return $this->repository->list($this->workspaceId, $this->fields, $this->filters, $this->orderBy, $this->meta);
    }

    public function create(): Template
    {
        $data = $this->request->all();

        if ($this->workspaceId !== null) {
            $data['workspace_id'] = $this->workspaceId;
        }

        // Обработка загрузки файла, если он передан
        if ($this->request->hasFile('filename')) {
            $file = $this->request->file('filename');
            $fileName = time() . '_' . Str::slug($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('templates', $fileName, 'public');

            $data['filename'] = $fileName;
            $data['filepath'] = $filePath;
        }

        $template = $this->repository->create($data);

        return $template->refresh();
    }

    public function update(int $templateId): Template
    {
        $template = $this->repository->get($templateId);
        if (!$template) {
            throw new ServerException('Шаблон не найден', 404);
        }

        // Проверяем доступ к workspace шаблона (если он отличается от workspace_id в роуте)
        if ($template->workspace_id !== null && $template->workspace_id !== $this->workspaceId) {
            $this->workspaceRepository->get($template->workspace_id);
        }

        $data = $this->request->all();

        if ($this->workspaceId !== null) {
            $data['workspace_id'] = $this->workspaceId;
        }

        // Обработка загрузки файла, если он передан
        if ($this->request->hasFile('filename')) {
            $data['filename'] = $this->request->file('filename');
        } elseif ($this->request->has('filename') && ($this->request->input('filename') === null || $this->request->input('filename') === '')) {
            // Удаление файла, если передано null или пустая строка
            $data['filename'] = null;
        }

        $this->repository->update($template, $data);

        return $template->refresh();
    }

    public function delete(int $templateId): bool
    {
        $template = $this->repository->get($templateId);
        if (!$template) {
            throw new ServerException('Шаблон не найден', 404);
        }

        // Проверяем доступ к workspace шаблона (если он отличается от workspace_id в роуте)
        if ($template->workspace_id !== null && $template->workspace_id !== $this->workspaceId) {
            $this->workspaceRepository->get($template->workspace_id);
        }

        return $this->repository->delete($template);
    }

    /**
     * Получить шаблоны по владельцу
     */
    public function getByOwner(string $module, int $ownerId): Collection
    {
        return $this->repository->getByOwner($module, $ownerId, $this->workspaceId);
    }
}

