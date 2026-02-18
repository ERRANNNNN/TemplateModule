<?php

namespace App\Modules\Template\Models;

use App\Models\BaseModel;
use App\Modules\Contract\Models\Contract;
use App\Modules\Contract\Models\ContractType;
use App\Modules\Project\Models\Project;
use App\Modules\Template\Enums\TemplateModule;
use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $slug
 * @property int $type_id
 * @property string|null $html
 * @property string|null $filename
 * @property string|null $filepath
 * @property string $module
 * @property int $owner_id
 * @property int|null $workspace_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read TemplateType $type
 * @property-read Workspace|null $workspace
 * @property-read ContractType|Contract|Project|null $owner
 * @property-read string|null $file_url
 * @method static Builder<static>|Template newModelQuery()
 * @method static Builder<static>|Template newQuery()
 * @method static Builder<static>|Template query()
 * @method static Builder<static>|Template whereCreatedAt($value)
 * @method static Builder<static>|Template whereDescription($value)
 * @method static Builder<static>|Template whereHtml($value)
 * @method static Builder<static>|Template whereId($value)
 * @method static Builder<static>|Template whereModule($value)
 * @method static Builder<static>|Template whereName($value)
 * @method static Builder<static>|Template whereOwnerId($value)
 * @method static Builder<static>|Template whereSlug($value)
 * @method static Builder<static>|Template whereTypeId($value)
 * @method static Builder<static>|Template whereUpdatedAt($value)
 * @method static Builder<static>|Template whereWorkspaceId($value)
 * @mixin \Eloquent
 */
class Template extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'slug',
        'type_id',
        'html',
        'filename',
        'filepath',
        'module',
        'owner_id',
        'workspace_id',
    ];

    protected $appends = ['file_url'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(TemplateType::class, 'type_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    /**
     * Получить владельца шаблона через полиморфную связь
     */
    public function owner()
    {
        $modelClass = $this->getOwnerModel();
        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($this->owner_id);
    }

    /**
     * Получить модель владельца по типу модуля
     */
    public function getOwnerModel(): ?string
    {
        return match ($this->module) {
            TemplateModule::CONTRACT_TYPE->value => ContractType::class,
            TemplateModule::CONTRACT->value => Contract::class,
            TemplateModule::PROJECT->value => Project::class,
            default => null,
        };
    }

    /**
     * Аксессор для получения URL файла
     */
    public function getFileUrlAttribute(): ?string
    {
        if ($this->filename && $this->filepath) {
            return Storage::url($this->filepath);
        }

        return null;
    }

    /**
     * Проверка, является ли шаблон файловым
     */
    public function isFileTemplate(): bool
    {
        return $this->filename !== null && $this->filepath !== null;
    }

    /**
     * Проверка, является ли шаблон HTML
     */
    public function isHtmlTemplate(): bool
    {
        return $this->html !== null;
    }
}

