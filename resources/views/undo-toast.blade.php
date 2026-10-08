@php
    use Filament\Support\Facades\FilamentAsset;
    use Filament\Support\Icons\Heroicon;
    use HoceineEl\UndoToast\Enums\UndoableOperation;
@endphp

<div
    wire:ignore
    x-load
    x-load-src="{{ FilamentAsset::getAlpineComponentSrc('undo-toast', 'hoceineel/filament-undo-toast') }}"
    x-load-css="[@js(FilamentAsset::getStyleHref('undo-toast', 'hoceineel/filament-undo-toast'))]"
    x-data="undoToast({ config: @js($config) })"
    x-on:undo-toast-push.window="push($event.detail)"
    x-on:undo-toast-undone.window="refreshPage()"
    x-bind:class="{ 'ut-paused': isPaused, 'ut-expanded': isExpanded }"
    class="ut-region ut-position-{{ $position?->value ?? 'bottom-center' }}"
>
    @if (filled($config['keyBindings'] ?? []))
        <span hidden data-keyboard-shortcut="{{ implode(' ', $config['keyBindings']) }}" data-keyboard-shortcut-label="{{ __('undo-toast::undo-toast.actions.undo_last') }}"></span>
    @endif

    <div
        x-ref="stack"
        role="status"
        aria-live="polite"
        aria-relevant="additions text"
        class="ut-stack"
        x-bind:style="{ height: stackHeight + 'px' }"
        x-on:mouseenter="hovering = true"
        x-on:mouseleave="hovering = false"
        x-on:focusin="focusWithin = true"
        x-on:focusout="focusWithin = $el.contains($event.relatedTarget)"
        x-on:click.outside="pinned = false"
    >
        <template x-for="(toast, index) in toasts" x-bind:key="toast.token">
            <div
                class="ut-toast"
                x-bind:data-state="toast.state"
                x-bind:data-entered="toast.entered"
                x-bind:data-leaving="toast.leaving"
                x-bind:data-depth="toast.leaving ? 0 : Math.min(stackIndex(toast), 3)"
                x-bind:data-hidden="isBeyondLimit(toast)"
                x-bind:inert="isBeyondLimit(toast)"
                x-bind:role="toast.state === 'error' ? 'alert' : null"
                x-bind:style="styleFor(toast)"
                x-on:keydown.escape.stop="dismiss(toast)"
                x-on:click="if (stackIndex(toast) > 0) pinned = true"
            >
                <span class="ut-icon" aria-hidden="true">
                    @foreach (UndoableOperation::cases() as $operation)
                        <span x-show="isPending(toast) && toast.operation === @js($operation->value)">
                            <x-filament::icon :icon="$operation->getIcon()" />
                        </span>
                    @endforeach

                    <span x-show="toast.state === 'done'" class="ut-icon-done">
                        <x-filament::icon :icon="Heroicon::OutlinedCheckCircle" />
                    </span>

                    <span x-show="toast.state === 'error'" class="ut-icon-error">
                        <x-filament::icon :icon="Heroicon::OutlinedExclamationCircle" />
                    </span>
                </span>

                <p class="ut-message" x-text="toast.message"></p>

                <span class="ut-sr-only" x-show="toast.state === 'idle'" x-text="countdownText(toast)"></span>

                <div class="ut-actions">
                    <button
                        type="button"
                        class="ut-undo"
                        x-show="isPending(toast)"
                        x-bind:disabled="toast.state !== 'idle'"
                        x-bind:aria-busy="toast.state === 'undoing'"
                        x-bind:aria-keyshortcuts="isNewest(toast) ? ariaShortcut : null"
                        x-on:click="undo(toast)"
                    >
                        <span x-show="toast.state === 'undoing'" class="ut-spinner">
                            <x-filament::loading-indicator />
                        </span>
                        <span x-text="toast.state === 'undoing' ? labels.undoing : labels.undo"></span>
                        <kbd
                            class="ut-kbd"
                            aria-hidden="true"
                            x-show="shortcutLabel && toast.state === 'idle'"
                            x-bind:data-visible="isNewest(toast) ? 'true' : 'false'"
                            x-text="shortcutLabel"
                        ></kbd>
                    </button>

                    <button
                        type="button"
                        class="ut-dismiss"
                        x-bind:aria-label="labels.dismiss"
                        x-bind:title="labels.dismiss"
                        x-on:click="dismiss(toast)"
                    >
                        <x-filament::icon :icon="Heroicon::XMark" />
                    </button>
                </div>

                <span class="ut-progress" aria-hidden="true" x-show="toast.state === 'idle'">
                    <span x-bind:style="progressStyle(toast)"></span>
                </span>
            </div>
        </template>
    </div>
</div>
