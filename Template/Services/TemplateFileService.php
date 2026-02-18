<?php

namespace App\Modules\Template\Services;

use App\Modules\Contract\Models\Contract;
use App\Modules\Template\Models\Template;
use App\Modules\Template\Models\TemplateType;
use App\Modules\Template\Enums\TemplateModule;
use App\Services\AbstractService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class TemplateFileService extends AbstractService
{
    /**
     * Синхронизация файловых шаблонов с контрактом
     * @param Contract $contract
     * @param array|null $uploadedFiles Массив файлов, где ключ - slug типа шаблона или type_id
     * @return Contract
     */
    public function syncFileTemplatesToContract(Contract $contract, ?array $uploadedFiles): Contract
    {
        $currentTemplates = $contract->documentTemplates()
            ->whereNotNull('filename')
            ->whereNotNull('filepath')
            ->get();

        // Удаляем шаблоны, которые не были переданы в запросе
        $currentTemplates->each(function ($template) use ($uploadedFiles) {
            $templateKey = $this->getTemplateKey($template);
            $shouldKeep = false;

            if ($uploadedFiles !== null && isset($uploadedFiles[$templateKey])) {
                $shouldKeep = true;
            } elseif ($this->request->input("document_template.{$templateKey}") === $template->filename) {
                $shouldKeep = true;
            }

            if (!$shouldKeep) {
                // Удаляем файл и шаблон
                if ($template->filepath && Storage::disk('public')->exists($template->filepath)) {
                    Storage::disk('public')->delete($template->filepath);
                }
                $template->filename = null;
                $template->filepath = null;
                $template->save();
            }
        });

        // Загружаем новые файлы
        if ($uploadedFiles !== null) {
            foreach ($uploadedFiles as $key => $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                // Определяем type_id по ключу (может быть slug или type_id)
                $typeId = $this->getTypeIdByKey($key, $contract->workspace_id);
                if (!$typeId) {
                    continue;
                }

                // Ищем существующий шаблон или создаем новый
                $template = $contract->documentTemplates()
                    ->where('type_id', $typeId)
                    ->first();

                if (!$template) {
                    // Создаем новый шаблон
                    $templateType = TemplateType::find($typeId);
                    if (!$templateType) {
                        continue;
                    }

                    $template = Template::create([
                        'name' => $templateType->name,
                        'slug' => $templateType->slug . '_' . $contract->id,
                        'type_id' => $typeId,
                        'module' => TemplateModule::CONTRACT->value,
                        'owner_id' => $contract->id,
                        'workspace_id' => $contract->workspace_id,
                    ]);
                }

                // Загружаем файл
                $template->filename = $file;
                $template->save();
            }
        }

        return $contract;
    }

    /**
     * Получить ключ шаблона для идентификации (slug или type_id)
     */
    private function getTemplateKey(Template $template): string
    {
        if ($template->type && $template->type->slug) {
            return $template->type->slug;
        }
        return (string)$template->type_id;
    }

    /**
     * Получить type_id по ключу (slug или type_id)
     */
    private function getTypeIdByKey(string $key, ?int $workspaceId = null): ?int
    {
        // Если ключ - число, это type_id
        if (is_numeric($key)) {
            return (int)$key;
        }

        // Иначе ищем по slug (учитываем workspace_id для поиска)
        $query = TemplateType::where('slug', $key);
        if ($workspaceId !== null) {
            $query->where(function ($q) use ($workspaceId) {
                $q->where('workspace_id', $workspaceId)
                  ->orWhereNull('workspace_id');
            });
        } else {
            $query->whereNull('workspace_id');
        }

        $templateType = $query->first();
        return $templateType?->id;
    }
}

