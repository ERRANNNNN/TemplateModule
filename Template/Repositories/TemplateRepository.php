<?php

namespace App\Modules\Template\Repositories;

use App\Modules\Template\Models\Template;
use App\Traits\Repository\FilterTrait;
use App\Traits\Repository\OrderTrait;
use App\Traits\Repository\PaginateTrait;
use App\Traits\Repository\SelectTrait;
use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class TemplateRepository
{
    use PaginateTrait;
    use SelectTrait;
    use FilterTrait;
    use OrderTrait;

    public function list(?int $workspaceId, array $fields = [], Closure $filters = null, array $orderBy = [], array $meta = []): Collection
    {
        $query = Template::query();
        $query->select();
        $this->select($query, $fields);
        $this->filter($query, $filters);

        if ($workspaceId !== null) {
            $query->where(function ($q) use ($workspaceId) {
                $q->where('workspace_id', $workspaceId)
                  ->orWhereNull('workspace_id');
            });
        } else {
            $query->whereNull('workspace_id');
        }

        $this->order($query, $orderBy);
        $this->paginate($query, $meta);

        return $query->get();
    }

    public function get(int $templateId): ?Template
    {
        return Template::find($templateId);
    }

    public function create(array $data): Template
    {
        return Template::create($data);
    }

    public function update(Template $template, array $data): Template
    {
        $template->update($data);
        return $template->fresh();
    }

    public function delete(Template $template): bool
    {
        // Удаляем файл, если он существует
        if ($template->filepath && Storage::disk('public')->exists($template->filepath)) {
            Storage::disk('public')->delete($template->filepath);
        }

        return $template->delete();
    }

    /**
     * Получить шаблоны по владельцу
     */
    public function getByOwner(string $module, int $ownerId, ?int $workspaceId = null): Collection
    {
        $query = Template::query()
            ->where('module', $module)
            ->where('owner_id', $ownerId);

        if ($workspaceId !== null) {
            $query->where(function ($q) use ($workspaceId) {
                $q->where('workspace_id', $workspaceId)
                  ->orWhereNull('workspace_id');
            });
        } else {
            $query->whereNull('workspace_id');
        }

        return $query->get();
    }
}

