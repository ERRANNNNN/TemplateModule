<?php

namespace App\Modules\Template\Repositories;

use App\Modules\Template\Models\TemplateType;
use App\Traits\Repository\FilterTrait;
use App\Traits\Repository\OrderTrait;
use App\Traits\Repository\PaginateTrait;
use App\Traits\Repository\SelectTrait;
use Closure;
use Illuminate\Database\Eloquent\Collection;

class TemplateTypeRepository
{
    use PaginateTrait;
    use SelectTrait;
    use FilterTrait;
    use OrderTrait;

    public function list(?int $workspaceId, array $fields = [], Closure $filters = null, array $orderBy = [], array $meta = []): Collection
    {
        $query = TemplateType::query();
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

    public function get(int $templateTypeId): ?TemplateType
    {
        return TemplateType::find($templateTypeId);
    }

    public function create(array $data): TemplateType
    {
        return TemplateType::create($data);
    }

    public function update(TemplateType $templateType, array $data): TemplateType
    {
        $templateType->update($data);
        return $templateType->fresh();
    }

    public function delete(TemplateType $templateType): bool
    {
        return $templateType->delete();
    }
}

