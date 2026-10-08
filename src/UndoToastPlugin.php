<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast;

use Closure;
use Filament\Actions\Action;
use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\View\PanelsRenderHook;
use HoceineEl\UndoToast\Contracts\HasUndoableRelations;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use HoceineEl\UndoToast\Enums\ToastPosition;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Livewire\UndoToast;
use HoceineEl\UndoToast\Strategies\ClosureStrategy;
use HoceineEl\UndoToast\Strategies\DetachStrategy;
use HoceineEl\UndoToast\Strategies\EditStrategy;
use HoceineEl\UndoToast\Strategies\HardDeleteStrategy;
use HoceineEl\UndoToast\Strategies\RestoreStrategy;
use HoceineEl\UndoToast\Strategies\SoftDeleteStrategy;
use HoceineEl\UndoToast\Support\Undoable;
use HoceineEl\UndoToast\Support\UndoEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

class UndoToastPlugin implements Plugin
{
    use EvaluatesClosures;

    protected int|Closure $duration = 8;

    protected ToastPosition|Closure $position = ToastPosition::BottomCenter;

    protected int|Closure $maxVisible = 3;

    /** @var array<int, UndoableOperation>|Closure|null */
    protected array|Closure|null $operations = null;

    /** @var array<int, class-string>|Closure */
    protected array|Closure $exceptResources = [];

    /** @var array<int, class-string<Model>>|Closure */
    protected array|Closure $exceptModels = [];

    protected bool|Closure $hardDeletes = true;

    /** @var array<int, string>|Closure */
    protected array|Closure $keyBindings = ['mod+z'];

    /** @var array<int, class-string<UndoStrategy>> */
    protected array $customStrategies = [];

    protected ?Closure $authorizeUndoUsing = null;

    protected ?Closure $recordUrlUsing = null;

    protected bool|Closure $replaceSuccessNotification = true;

    /** @var array<class-string<Model>, array<int, string>>|Closure */
    protected array|Closure $restoreRelations = [];

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public static function current(): ?static
    {
        $panel = Filament::getCurrentPanel();
        $id = app(static::class)->getId();

        if (! $panel?->hasPlugin($id)) {
            return null;
        }

        $plugin = $panel->getPlugin($id);

        return $plugin instanceof static ? $plugin : null;
    }

    public static function currentUserId(): ?string
    {
        $id = Filament::getCurrentPanel() ? Filament::auth()->id() : auth()->id();

        return $id === null ? null : (string) $id;
    }

    public function getId(): string
    {
        return 'undo-toast';
    }

    public function register(Panel $panel): void
    {
        $panel->renderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => static::current() ? Blade::render('@livewire(\''.UndoToast::NAME.'\')') : '',
        );
    }

    public function boot(Panel $panel): void {}

    public function duration(int|Closure $seconds): static
    {
        $this->duration = $seconds;

        return $this;
    }

    public function position(ToastPosition|Closure $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function maxVisible(int|Closure $count): static
    {
        $this->maxVisible = $count;

        return $this;
    }

    /**
     * @param  array<int, UndoableOperation>|Closure|null  $operations
     */
    public function operations(array|Closure|null $operations): static
    {
        $this->operations = $operations;

        return $this;
    }

    /**
     * @param  array<int, class-string>|Closure  $resources
     */
    public function except(array|Closure $resources): static
    {
        $this->exceptResources = $resources;

        return $this;
    }

    /**
     * @param  array<int, class-string<Model>>|Closure  $models
     */
    public function exceptModels(array|Closure $models): static
    {
        $this->exceptModels = $models;

        return $this;
    }

    public function hardDeletes(bool|Closure $condition = true): static
    {
        $this->hardDeletes = $condition;

        return $this;
    }

    /**
     * @param  array<int, string>|Closure  $keyBindings
     */
    public function keyBindings(array|Closure $keyBindings): static
    {
        $this->keyBindings = $keyBindings;

        return $this;
    }

    /**
     * @param  class-string<UndoStrategy>  $strategy
     */
    public function strategy(string $strategy): static
    {
        $this->customStrategies[] = $strategy;

        return $this;
    }

    public function authorizeUndoUsing(?Closure $callback): static
    {
        $this->authorizeUndoUsing = $callback;

        return $this;
    }

    public function recordUrlUsing(?Closure $callback): static
    {
        $this->recordUrlUsing = $callback;

        return $this;
    }

    public function replaceSuccessNotification(bool|Closure $condition = true): static
    {
        $this->replaceSuccessNotification = $condition;

        return $this;
    }

    /**
     * @param  array<class-string<Model>, array<int, string>>|Closure  $relations
     */
    public function restoreRelations(array|Closure $relations): static
    {
        $this->restoreRelations = $relations;

        return $this;
    }

    public function getDuration(): int
    {
        return max(1, (int) $this->evaluate($this->duration));
    }

    public function getPosition(): ToastPosition
    {
        return $this->evaluate($this->position);
    }

    public function getMaxVisible(): int
    {
        return max(1, (int) $this->evaluate($this->maxVisible));
    }

    /**
     * @return array<int, string>
     */
    public function getKeyBindings(): array
    {
        return array_values((array) $this->evaluate($this->keyBindings));
    }

    public function hasHardDeletes(): bool
    {
        return (bool) $this->evaluate($this->hardDeletes);
    }

    /**
     * @return array<int, UndoStrategy>
     */
    public function getStrategies(): array
    {
        return array_map(fn (string $strategy): UndoStrategy => app($strategy), $this->getStrategyClasses());
    }

    /**
     * @return array<int, class-string<UndoStrategy>>
     */
    public function getStrategyClasses(): array
    {
        return array_values(array_filter([
            ...$this->customStrategies,
            ClosureStrategy::class,
            SoftDeleteStrategy::class,
            $this->hasHardDeletes() ? HardDeleteStrategy::class : null,
            RestoreStrategy::class,
            EditStrategy::class,
            DetachStrategy::class,
        ]));
    }

    public function allowsOperation(UndoableOperation $operation): bool
    {
        $operations = $this->evaluate($this->operations);

        return $operations === null || in_array($operation, $operations, true);
    }

    public function appliesTo(Action $action): bool
    {
        if (Undoable::of($action)?->enabled === false) {
            return false;
        }

        $resources = (array) $this->evaluate($this->exceptResources);
        $livewire = $action->getLivewire();

        if ($livewire && method_exists($livewire, 'getResource') && in_array($livewire::getResource(), $resources, true)) {
            return false;
        }

        $model = $action->getModel();

        if (! $model) {
            return true;
        }

        $excludedModels = [
            ...(array) $this->evaluate($this->exceptModels),
            ...array_map(fn (string $resource): ?string => method_exists($resource, 'getModel') ? $resource::getModel() : null, $resources),
        ];

        foreach (array_filter($excludedModels) as $excluded) {
            if (is_a($model, $excluded, true)) {
                return false;
            }
        }

        return true;
    }

    public function canUndo(UndoEntry $entry): bool
    {
        return $this->authorizeUndoUsing === null
            || (bool) $this->evaluate($this->authorizeUndoUsing, ['entry' => $entry], [UndoEntry::class => $entry]);
    }

    public function shouldReplaceSuccessNotification(Action $action): bool
    {
        return (bool) $this->evaluate($this->replaceSuccessNotification, ['action' => $action], [Action::class => $action, $action::class => $action]);
    }

    /**
     * @return list<string>
     */
    public function getUndoableRelations(Model $record): array
    {
        $configured = collect((array) $this->evaluate($this->restoreRelations))
            ->filter(fn (mixed $relations, string $model): bool => $record instanceof $model)
            ->flatten()
            ->all();

        return array_values(array_unique([
            ...($record instanceof HasUndoableRelations ? $record->undoableRelations() : []),
            ...$configured,
        ]));
    }

    public function getRecordUrl(Model $record): ?string
    {
        if ($this->recordUrlUsing === null) {
            return null;
        }

        return $this->evaluate($this->recordUrlUsing, ['record' => $record], [Model::class => $record, $record::class => $record]);
    }
}
