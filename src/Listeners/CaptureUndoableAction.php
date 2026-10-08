<?php

declare(strict_types=1);

namespace HoceineEl\UndoToast\Listeners;

use Filament\Actions\Action;
use Filament\Actions\Enums\ActionStatus;
use Filament\Actions\Events\ActionCalled;
use Filament\Actions\Events\ActionCalling;
use Filament\Facades\Filament;
use HoceineEl\UndoToast\Contracts\ConfirmsOutcome;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use HoceineEl\UndoToast\Support\PendingUndoRegistry;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Support\SuccessNotification;
use HoceineEl\UndoToast\Support\UndoEntry;
use HoceineEl\UndoToast\Support\UndoStore;
use HoceineEl\UndoToast\UndoToastPlugin;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CaptureUndoableAction
{
    public const SESSION_KEY = 'undo-toast.pending';

    public function __construct(
        protected PendingUndoRegistry $registry,
        protected UndoStore $store,
    ) {}

    public function calling(Action|ActionCalling $event): void
    {
        $action = $event instanceof ActionCalling ? $event->getAction() : $event;
        $plugin = UndoToastPlugin::current();

        if (! $plugin?->appliesTo($action)) {
            return;
        }

        foreach ($plugin->getStrategies() as $strategy) {
            if (! $strategy->supports($action)) {
                continue;
            }

            $snapshot = $strategy->snapshot($action);

            if ($snapshot?->count() && $plugin->allowsOperation($snapshot->operation)) {
                $this->registry->put($action, $strategy, $snapshot);
            }

            return;
        }
    }

    public function called(Action|ActionCalled $event): void
    {
        $action = $event instanceof ActionCalled ? $event->getAction() : $event;
        $pending = $this->registry->pull($action);
        $plugin = UndoToastPlugin::current();

        if (! $pending || ! $plugin) {
            return;
        }

        [$strategy, $snapshot] = $pending;
        $succeeded = $action->getStatus() === ActionStatus::Success;

        if ($strategy instanceof ConfirmsOutcome) {
            $snapshot = $strategy->confirm($snapshot);
        } elseif (! $succeeded) {
            return;
        }

        if (! $snapshot?->count()) {
            return;
        }

        if ($succeeded && $plugin->shouldReplaceSuccessNotification($action)) {
            SuccessNotification::suppress($action);
        }

        $livewire = $action->getLivewire();

        DB::afterCommit(fn () => $this->finalize($plugin, $strategy, $snapshot, $livewire));
    }

    protected function finalize(UndoToastPlugin $plugin, UndoStrategy $strategy, Snapshot $snapshot, mixed $livewire): void
    {
        $entry = UndoEntry::make(
            strategy: $strategy::class,
            snapshot: $snapshot,
            message: $strategy->describe($snapshot),
            seconds: $plugin->getDuration(),
            userId: UndoToastPlugin::currentUserId(),
            panelId: Filament::getCurrentPanel()?->getId(),
        );

        $this->store->put($entry);

        $toast = $entry->toToast();

        if ($livewire instanceof Component) {
            $livewire->dispatch('undo-toast-push', ...$toast);
        }

        session()->flash(self::SESSION_KEY, array_slice([...session(self::SESSION_KEY, []), $toast], -5));
    }
}
