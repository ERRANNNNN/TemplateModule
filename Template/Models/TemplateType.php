<?php

namespace App\Modules\Template\Models;

use App\Models\BaseModel;
use App\Modules\Template\Enums\TemplateModule;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property array $modules
 * @property int|null $parent_id
 * @property bool $is_preparable
 * @property int|null $workspace_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TemplateType|null $parent
 * @property-read Workspace|null $workspace
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TemplateType> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Template> $templates
 * @property-read int|null $templates_count
 * @method static Builder<static>|TemplateType newModelQuery()
 * @method static Builder<static>|TemplateType newQuery()
 * @method static Builder<static>|TemplateType query()
 * @method static Builder<static>|TemplateType whereCreatedAt($value)
 * @method static Builder<static>|TemplateType whereId($value)
 * @method static Builder<static>|TemplateType whereIsPreparable($value)
 * @method static Builder<static>|TemplateType whereModules($value)
 * @method static Builder<static>|TemplateType whereName($value)
 * @method static Builder<static>|TemplateType whereParentId($value)
 * @method static Builder<static>|TemplateType whereSlug($value)
 * @method static Builder<static>|TemplateType whereUpdatedAt($value)
 * @method static Builder<static>|TemplateType whereWorkspaceId($value)
 * @mixin \Eloquent
 */
class TemplateType extends BaseModel
{
    protected $fillable = [
        'name',
        'slug',
        'modules',
        'parent_id',
        'is_preparable',
        'workspace_id',
    ];

    protected $casts = [
        'modules' => 'array',
        'is_preparable' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(TemplateType::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(TemplateType::class, 'parent_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class, 'type_id');
    }

    /**
     * Проверяет, доступен ли модуль для данного типа
     */
    public function hasModule(TemplateModule $module): bool
    {
        return in_array($module->value, $this->modules ?? []);
    }
}

