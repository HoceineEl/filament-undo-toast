<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Support;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

use function Filament\Support\get_model_label;

final class ModelLabels
{
    /**
     * @param  class-string<Model>  $model
     * @return array{label: string, plural_label: string}
     */
    public static function for(Action $action, string $model): array
    {
        $label = (string) $action->getModelLabel();
        $pluralLabel = (string) $action->getPluralModelLabel();
        $default = get_model_label($model);

        if ($label !== $default) {
            return ['label' => $label, 'plural_label' => $pluralLabel];
        }

        $key = 'undo-toast::undo-toast.models.'.class_basename($model);

        return [
            'label' => self::translate("{$key}.label", $label),
            'plural_label' => in_array($pluralLabel, [$default, Str::plural($default)], true)
                ? self::translate("{$key}.plural_label", $pluralLabel)
                : $pluralLabel,
        ];
    }

    protected static function translate(string $key, string $label): string
    {
        foreach ([$key, $label] as $candidate) {
            $translation = __($candidate);

            if (is_string($translation) && $translation !== $candidate) {
                return $translation;
            }
        }

        return $label;
    }
}
