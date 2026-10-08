<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Livewire;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use HoceineEl\UndoToast\Enums\UndoFailure;
use HoceineEl\UndoToast\Listeners\CaptureUndoableAction;
use HoceineEl\UndoToast\Support\UndoEntry;
use HoceineEl\UndoToast\Support\UndoFailed;
use HoceineEl\UndoToast\Support\UndoStore;
use HoceineEl\UndoToast\UndoToastPlugin;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Throwable;

class UndoToast extends Component
{
    public const NAME = 'filament-undo-toast';

    /**
     * @return array{ok: bool, message: string}
     */
    public function undo(string $token): array
    {
        $store = app(UndoStore::class);
        $plugin = UndoToastPlugin::current();

        try {
            throw_unless($plugin, UndoFailed::because(UndoFailure::Unavailable));

            $entry = $store->find($token);

            throw_unless($entry, UndoFailed::because(UndoFailure::Expired));
            throw_unless($this->owns($entry), UndoFailed::because(UndoFailure::Forbidden));
            throw_unless($plugin->canUndo($entry), UndoFailed::because(UndoFailure::Forbidden));
            throw_unless(in_array($entry->strategy, $plugin->getStrategyClasses(), true), UndoFailed::because(UndoFailure::Unavailable));

            $entry = $store->claim($token);

            throw_unless($entry, UndoFailed::because(UndoFailure::Expired));

            /** @var UndoStrategy $strategy */
            $strategy = app($entry->strategy);

            DB::transaction(fn () => $strategy->undo($entry->snapshot));
        } catch (UndoFailed $exception) {
            return $this->fail($exception->reason);
        } catch (Throwable $exception) {
            report($exception);

            return $this->fail(UndoFailure::Error);
        }

        $message = $entry->snapshot->operation->describeUndone($entry->snapshot->count(), $entry->snapshot->labels());

        $this->dispatch('undo-toast-undone', token: $token);

        $notification = Notification::make()->success()->title($message);

        if ($url = $this->recordUrl($plugin, $entry)) {
            $notification->actions([
                Action::make('view')->label(__('undo-toast::undo-toast.actions.open'))->url($url)->link(),
            ]);
        }

        $notification->send();

        return ['ok' => true, 'message' => $message];
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $plugin = UndoToastPlugin::current();

        return [
            'duration' => $plugin?->getDuration() ?? 8,
            'position' => $plugin?->getPosition()->value,
            'maxVisible' => $plugin?->getMaxVisible() ?? 3,
            'keyBindings' => $plugin?->getKeyBindings() ?? [],
            'pending' => $this->pendingToasts(),
            'labels' => [
                'undo' => __('undo-toast::undo-toast.actions.undo'),
                'dismiss' => __('undo-toast::undo-toast.actions.dismiss'),
                'undoing' => __('undo-toast::undo-toast.states.undoing'),
                'countdown' => __('undo-toast::undo-toast.states.countdown'),
                'error' => __('undo-toast::undo-toast.failures.error'),
            ],
        ];
    }

    public function render(): View
    {
        return view('undo-toast::undo-toast', [
            'config' => $this->getConfig(),
            'position' => UndoToastPlugin::current()?->getPosition(),
        ]);
    }

    /**
     * @return list<array{token: string, message: string, operation: string, seconds: int}>
     */
    protected function pendingToasts(): array
    {
        $store = app(UndoStore::class);

        /** @var array<int, mixed> $flashed */
        $flashed = (array) session(CaptureUndoableAction::SESSION_KEY, []);

        return collect($flashed)
            ->map(fn (mixed $toast): ?UndoEntry => is_array($toast) && is_string($toast['token'] ?? null) ? $store->find($toast['token']) : null)
            ->filter(fn (?UndoEntry $entry): bool => $entry !== null && $this->owns($entry) && $entry->remainingSeconds() > 0)
            ->map(fn (UndoEntry $entry): array => $entry->toToast())
            ->values()
            ->all();
    }

    protected function owns(UndoEntry $entry): bool
    {
        return $entry->userId === UndoToastPlugin::currentUserId()
            && $entry->panelId === Filament::getCurrentPanel()?->getId();
    }

    protected function recordUrl(UndoToastPlugin $plugin, UndoEntry $entry): ?string
    {
        if ($entry->snapshot->count() !== 1) {
            return null;
        }

        /** @var Model $model */
        $model = new $entry->snapshot->modelClass;
        $record = $model->newQueryWithoutScopes()->find($entry->snapshot->keys[0]);

        return $record instanceof Model ? $plugin->getRecordUrl($record) : null;
    }

    /**
     * @return array{ok: bool, message: string}
     */
    protected function fail(UndoFailure $reason): array
    {
        Notification::make()
            ->danger()
            ->title(__('undo-toast::undo-toast.failures.title'))
            ->body($reason->getMessage())
            ->send();

        return ['ok' => false, 'message' => $reason->getMessage()];
    }
}
