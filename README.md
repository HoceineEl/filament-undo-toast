<p class="filament-hidden"><img src="https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/cover.jpg" alt="Undo Toast"></p>

# Undo Toast for Filament

A Gmail-style "Deleted 3 customers · Undo" toast for Filament 4 and 5 panels. After a delete, restore, edit or detach, a toast at the bottom of the screen lets the user reverse the change for a few seconds, by clicking Undo or pressing `⌘Z` / `Ctrl+Z`.

You register one plugin per panel. Resources, tables and actions stay as they are, and your `before()` / `after()` callbacks are left alone.

![Undo toast after deleting three customers](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/hero.png)

## Requirements

| Package | Version |
| --- | --- |
| PHP | 8.3+ |
| Laravel | 11, 12 or 13 |
| Filament | 4 or 5 |

Undo entries live in a cache store. Your app's default store is used unless you set another (see [Cache store](#cache-store)).

## Installation

```bash
composer require hoceineel/filament-undo-toast
php artisan filament:assets
```

Run `filament:assets` again after each update. If your `composer.json` runs `filament:upgrade` on `post-autoload-dump`, that already happens. No custom theme is needed; the plugin loads its own stylesheet.

Optional publishes:

```bash
php artisan vendor:publish --tag=undo-toast-config
php artisan vendor:publish --tag=undo-toast-translations
php artisan vendor:publish --tag=undo-toast-views
```

## Quick start

Register the plugin on each panel that should show undo toasts:

```php
use HoceineEl\UndoToast\UndoToastPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        // ...
        ->plugin(UndoToastPlugin::make());
}
```

Delete a record from any table in that panel and the toast appears. After Undo, the table refreshes, the toast confirms, and a notification can link to the record if you set [`recordUrlUsing()`](#configuration):

![After undoing: the toast confirms, the row is back, and a notification links to the record](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/undone.png)

## What can be undone

| Action | Kept before the action runs | Undo does |
| --- | --- | --- |
| `DeleteAction`, `DeleteBulkAction` on a `SoftDeletes` model | primary keys | `restore()` on each record |
| `DeleteAction`, `DeleteBulkAction` on any other model | the raw rows (every column), plus [opted-in relations](#restoring-relations-on-hard-delete) | re-inserts the rows with their original keys |
| `RestoreAction`, `RestoreBulkAction` | keys of the records that were trashed | soft deletes them again |
| `EditAction` | the raw row | puts back the columns the edit changed |
| `DetachAction`, `DetachBulkAction` (`BelongsToMany`, `MorphToMany`) | the pivot rows with every pivot column | attaches the records again with their pivot data |
| Any action with `->undoable(fn ...)` | primary keys and your closure | calls your closure for each record |

Built-in strategies check the database after the action and keep only the records that really changed, so a partly successful bulk action offers undo for the records it touched. An action that fails, is halted or changes nothing creates no toast.

Hard deletes. The plugin reads the rows straight from the database (no global scopes, no casts) and re-inserts them with the same key and values, `created_at` and `updated_at` included. Rows removed by `ON DELETE CASCADE` or by your `deleting` / `deleted` events stay gone unless you list them as [relations to restore](#restoring-relations-on-hard-delete). If a row with the same key exists when Undo is clicked, nothing is inserted and the toast says why. Turn this off with `->hardDeletes(false)`; soft deletes keep working.

![Hard delete of a project, in dark mode](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/dark.png)

Edits. Only columns that changed are kept, ignoring `updated_at`. On Undo, each column is put back only if it still holds the value the edit wrote, so a later change by someone else survives. If every column changed since, nothing is written and the toast says so.

![Edit toast](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/edit.png)

Detach. If any of the records was attached again in the meantime, the undo stops with a conflict instead of attaching twice.

### What cannot be undone

- `ForceDeleteAction` and `ForceDeleteBulkAction`, even with `->undoable(fn ...)`.
- The Save button on a resource's Edit page. It submits the page form instead of calling an action, so Filament fires no action events. Only `EditAction` (modal editing) is covered.
- `DissociateAction`, `ReplicateAction`, `CreateAction`, `AttachAction` and `AssociateAction` have no built-in strategy. Use `->undoable()` or a [custom strategy](#custom-strategies).
- Changes your code makes outside a Filament action.

## How it works

1. On Filament's `ActionCalling` event, the plugin checks the action is not excluded and asks each strategy whether it supports the action. The first that does takes a snapshot.
2. The action runs normally. The plugin does not wrap its `action()` closure.
3. On `ActionCalled`, the strategy narrows the snapshot to what really changed. If the action succeeded, Filament's own success notification for that call is removed (see [Success notification](#filaments-success-notification)).
4. After the transaction commits, an encrypted entry is stored in the cache under `undo-toast:{token}` and the toast is pushed to the browser. The payload is also flashed to the session (last five entries), so the toast survives redirects such as deleting from an Edit page.
5. Undo calls a method on the toast's Livewire component (no extra route). It checks and claims the entry, runs the strategy's `undo()` inside `DB::transaction()`, then refreshes the other Livewire components on the page.

Entries live for the toast duration plus 30 seconds and are deleted once used.

## Configuration

Every option is a fluent method on the plugin, and every value can be a closure evaluated when needed.

| Method | Default | What it does |
| --- | --- | --- |
| `duration(int)` | `8` | Seconds undo is offered. Minimum 1. The countdown pauses on hover, on focus and while the tab is hidden. |
| `position(ToastPosition)` | `BottomCenter` | `BottomCenter`, `BottomStart` or `BottomEnd`. Start and end follow text direction. |
| `maxVisible(int)` | `3` | Toasts shown at once. Hidden ones stay undoable until they expire. Minimum 1. |
| `operations(?array)` | `null` (all) | Allowed `UndoableOperation` cases: `Delete`, `Restore`, `Edit`, `Detach`, `Custom`. |
| `except(array)` | `[]` | Resource classes to skip. Their models are skipped anywhere in the panel, relation managers included. |
| `exceptModels(array)` | `[]` | Model classes to skip, subclasses included. |
| `hardDeletes(bool)` | `true` | Undo deletes on models without `SoftDeletes`. Turning it off also invalidates pending hard-delete entries. |
| `keyBindings(array)` | `['mod+z']` | Shortcuts that undo the newest toast. `[]` turns the shortcut off. |
| `restoreRelations(array)` | `[]` | Relations to restore with a hard-deleted record, keyed by model class. |
| `replaceSuccessNotification(bool)` | `true` | Hide Filament's success notification when a toast is shown. The closure receives `$action`. |
| `authorizeUndoUsing(?Closure)` | `null` | Extra check when Undo is clicked. Receives `UndoEntry $entry`; return `false` to refuse. |
| `recordUrlUsing(?Closure)` | `null` | Adds an Open link to the notification after one record is restored. Receives `$record`; return a URL or `null`. |
| `strategy(string)` | none | Registers a custom strategy class. Call once per strategy. |

An excluded action behaves as if the plugin were not installed, and Filament's own notification shows.

```php
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use App\Models\Payment;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use HoceineEl\UndoToast\Enums\ToastPosition;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\UndoToastPlugin;
use Illuminate\Database\Eloquent\Model;

UndoToastPlugin::make()
    ->duration(fn (): int => auth()->user()?->prefers_slow_toasts ? 15 : 8)
    ->position(ToastPosition::BottomEnd)
    ->operations([UndoableOperation::Delete, UndoableOperation::Restore])
    ->except([AuditLogResource::class])
    ->exceptModels([Payment::class])
    ->keyBindings(['mod+z', 'mod+shift+z'])
    ->replaceSuccessNotification(fn (Action $action): bool => ! $action instanceof EditAction)
    ->recordUrlUsing(fn (Model $record): ?string => $record instanceof Customer
        ? CustomerResource::getUrl('edit', ['record' => $record])
        : null);
```

Each panel has its own registration and options. An entry made in one panel cannot be undone from another, and the user id comes from each panel's auth guard.

### Key bindings

A binding is modifiers plus one key, joined by `+`, case-insensitive. `mod` is `⌘` on macOS and iOS and `Ctrl` elsewhere; `ctrl`, `meta` (or `command`), `shift` and `alt` (or `option`) are also accepted. The key is matched against `KeyboardEvent.key` (`z`, `u`, `backspace`), and modifiers must match exactly, so `mod+z` does not fire on `mod+shift+z`.

The first binding shows as a hint chip next to Undo on the newest toast (hidden on touch devices and narrow screens).

![The shortcut hint next to Undo](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/keyboard-hint.png)

### Cache store

`config/undo-toast.php` has one option:

```php
return [
    'store' => env('UNDO_TOAST_STORE'),
];
```

`null` uses the default cache store. With more than one web server, use a shared store such as Redis or the database. The `array` store does not work across requests.

### Static helpers

`UndoToastPlugin::get()` returns the plugin on the current panel and fails if there is none. `UndoToastPlugin::current()` returns it or `null`. `UndoToastPlugin::currentUserId()` returns the current user's id as a string from the panel's guard. The getters the package uses (`getDuration()`, `allowsOperation()`, `appliesTo()`, `canUndo()` and so on) are public.

## Per-action opt-in and opt-out

Every Filament action gets an `undoable()` macro. Opt a built-in action out:

```php
DeleteAction::make()->undoable(false)
```

Give any action an undo. The closure runs once per affected record, which must be named `$record`; other parameters come from the container:

```php
Action::make('archive')
    ->action(fn (Order $record) => $record->archive())
    ->undoable(fn (Order $record) => $record->unarchive());

BulkAction::make('archive')
    ->action(fn (Collection $records) => $records->each->archive())
    ->undoable(fn (Order $record) => $record->unarchive());
```

The default message is the action label plus the model label ("Archive: 3 orders"). Pass your own as the second argument, as a string or a closure:

```php
->undoable(
    fn (Order $record) => $record->unarchive(),
    fn (Order $record): string => "Order {$record->number} archived",
)
```

- The closure is serialised into the encrypted cache entry and runs in a later request. Use `static fn`, avoid capturing `$this`, and resolve services inside it.
- Records are looked up again by key, without global scopes. If none exist, the undo fails with "The records are no longer there to undo."
- The toast only appears when the action reports success.
- A closure on a built-in action replaces the built-in strategy for that action.

## Restoring relations on hard delete

A hard delete often takes pivot and child rows with it, through cascading foreign keys or model events. List the relations to bring back, on the model:

```php
use HoceineEl\UndoToast\Contracts\HasUndoableRelations;

class Project extends Model implements HasUndoableRelations
{
    public function undoableRelations(): array
    {
        return ['tags', 'tasks'];
    }
}
```

Or on the plugin, for models you do not own (a class here also matches its subclasses; both sources are merged):

```php
UndoToastPlugin::make()->restoreRelations([
    Project::class => ['tags', 'tasks'],
    Invoice::class => ['lines'],
])
```

| Relation type | Kept | On undo |
| --- | --- | --- |
| `BelongsToMany`, `MorphToMany` | pivot rows with every pivot column | inserts missing pivot rows, skipping related records that no longer exist |
| `HasMany`, `HasOne`, `MorphMany`, `MorphOne` | related rows with every column | inserts rows whose key is still free |

Relations are restored in the same transaction, one level deep, with the query builder (no model events, nothing overwritten). Other relation types throw `InvalidArgumentException` when the delete runs, so a typo shows up in development. The rows are held in the cache entry, so keep these relations reasonably small.

## Custom strategies

Implement `HoceineEl\UndoToast\Contracts\UndoStrategy` and register it with `->strategy(ApproveInvoiceStrategy::class)`:

```php
use App\Models\Invoice;
use Filament\Actions\Action;
use HoceineEl\UndoToast\Contracts\UndoStrategy;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\Enums\UndoFailure;
use HoceineEl\UndoToast\Support\Snapshot;
use HoceineEl\UndoToast\Support\UndoFailed;

class ApproveInvoiceStrategy implements UndoStrategy
{
    public function supports(Action $action): bool
    {
        return $action->getName() === 'approve' && $action->getRecord() instanceof Invoice;
    }

    public function snapshot(Action $action): ?Snapshot
    {
        $invoice = $action->getRecord();

        return new Snapshot(
            operation: UndoableOperation::Custom,
            modelClass: Invoice::class,
            keys: [$invoice->getKey()],
            data: ['status' => $invoice->status],
        );
    }

    public function undo(Snapshot $snapshot): void
    {
        $updated = Invoice::query()->whereKey($snapshot->keys)->update(['status' => $snapshot->data['status']]);

        throw_if($updated === 0, UndoFailed::because(UndoFailure::Missing));
    }

    public function describe(Snapshot $snapshot): string
    {
        return __('Invoice approved');
    }
}
```

- Strategies come from the container. Custom ones are asked first, in registration order; the first whose `supports()` returns `true` handles the action.
- Return `null` (or a snapshot with no keys) from `snapshot()` to skip the toast. Keep `data` to plain values, since it is serialised.
- Throw `UndoFailed::because(UndoFailure::...)` to show a specific reason. Any other exception is reported, shown as a generic error, and rolled back.
- Implement `ConfirmsOutcome` to get `confirm(Snapshot $snapshot): ?Snapshot` after the action runs; return `$snapshot->with($keys, $data)` narrowed to what changed, or `null`. Without it, a toast appears only when the action reports success.
- To reuse the built-in wording in `describe()`, return `$snapshot->operation->describe($snapshot->count(), $snapshot->labels())`.
- Removing a strategy invalidates its pending entries.

`UndoFailure` cases: `Unavailable`, `Forbidden`, `Expired`, `Missing`, `Conflict`, `ChangedSince`, `Error`. A failed undo turns the toast red, keeps it until dismissed, and sends a "Couldn't undo" notification.

![A failed undo](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/error.png)

## Filament's success notification

When an action gets a toast, Filament's "Deleted" / "Saved" / "Detached" notification for that call is removed so the user does not read the same thing twice. It still shows when the action failed or changed nothing, when the action is excluded from undo, or when you customised or disabled it with `successNotification()` or `successNotificationTitle()` (including through `configureUsing()`). Bulk failure notifications are never touched. Turn this off with `->replaceSuccessNotification(false)`.

## Model labels

Messages use the labels your actions already use (`getModelLabel()`, `getPluralModelLabel()`). When a resource sets no label, Filament falls back to an English name from the class (`Customer` becomes "customer"). Only in that case, the plugin looks for a translation, first in the published package translations:

```php
// lang/vendor/undo-toast/ar/undo-toast.php
return [
    'models' => [
        'Customer' => ['label' => 'العميل', 'plural_label' => 'العملاء'],
    ],
];
```

then in your app's JSON translations (`"customer": "Kunde"` in `lang/de.json`). If neither exists, the default label is kept.

Messages use `trans_choice()` with explicit ranges:

```php
'delete' => '{1} :Label deleted|[0,*] Deleted :count :plural_label',
```

`:Label` is the capitalised label, `:label` the label as is, `:plural_label` the plural, `:count` the number of records. Languages with more plural forms list each range, so Russian reads "21 запись", "22 записи", "25 записей".

## The toast stack

Toasts stack with the newest in front. Hover, keyboard focus or a tap on a touch screen fans the stack out so every Undo button is reachable, and pauses every countdown. The shortcut always undoes the newest toast.

![Three toasts, expanded](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/stack-expanded.png)

On phones the toast spans the screen width and respects the bottom safe area.

![On a phone](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/mobile.png)

## Keyboard and accessibility

- Toasts sit in a `role="status"`, `aria-live="polite"` region and never move focus. Screen readers hear the message and how long undo stays available.
- A failed undo is announced with `role="alert"`.
- The newest Undo button has `aria-keyshortcuts`, and `aria-busy` while undoing. `Escape` dismisses the focused toast.
- Buttons are at least 44 by 44 pixels with visible focus rings.
- `mod+z` is ignored in text inputs, textareas, selects and editable content, so text undo keeps working. It works on checkboxes and buttons, such as right after selecting rows.
- Reduced motion fades instead of sliding. Windows High Contrast keeps the border, focus ring and progress line. The toast is hidden when printing.
- Right-to-left panels are mirrored automatically from Filament's `dir` attribute.

![Arabic, right to left](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/rtl.png)

A hidden `data-keyboard-shortcut` element labelled "Undo last action" lets cheat sheets such as [Keyboard Shortcuts](https://github.com/HoceineEl/filament-keyboard-shortcuts) list the shortcut.

## Translations

23 locales, each using Filament's own words for its actions: `ar`, `cs`, `de`, `en`, `es`, `fa`, `fr`, `he`, `hi`, `id`, `it`, `ja`, `ko`, `nl`, `pl`, `pt`, `pt_BR`, `ru`, `tr`, `uk`, `vi`, `zh_CN`, `zh_TW`. The locale follows `app()->getLocale()` in the request that ran the action.

![German](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/locale-de.png)

To change wording, publish the files and edit `lang/vendor/undo-toast/{locale}/undo-toast.php`. Keep only the keys you change; the rest fall back to the package.

```bash
php artisan vendor:publish --tag=undo-toast-translations
```

| Key | Used for |
| --- | --- |
| `messages.{delete,restore,edit,detach,custom}` | toast message after the action |
| `undone.{delete,restore,edit,detach,custom}` | toast and notification after undo |
| `actions.undo`, `actions.dismiss`, `actions.open`, `actions.undo_last` | buttons, record link, shortcut label |
| `states.undoing`, `states.countdown` | button while undoing, screen reader text (`:seconds`) |
| `failures.title`, `failures.{reason}` | failure messages |
| `models.{ClassBasename}.label`, `.plural_label` | model label translations |

## Theming

The toast uses an inverted surface, your panel's primary color for Undo and the countdown, and your panel font, and follows dark mode. Override `--ut-*` properties anywhere Filament loads CSS. The plugin's stylesheet loads after your theme, so prefix with `body`:

```css
body .ut-region {
    --ut-width: 32rem;
    --ut-accent: var(--success-400);
}

.dark body .ut-region {
    --ut-surface: var(--gray-950);
}
```

| Property | Default |
| --- | --- |
| `--ut-width` | `min(28rem, calc(100vw - 2rem))` |
| `--ut-offset` | `1rem`, plus the bottom safe area |
| `--ut-radius` | `0.75rem` |
| `--ut-z` | `40` (below Filament modals) |
| `--ut-surface` | `var(--gray-900)`; `var(--gray-800)` in dark mode |
| `--ut-text` | `var(--gray-50)` |
| `--ut-muted` | `var(--gray-400)` |
| `--ut-accent` | `var(--primary-400)` |
| `--ut-hover` | `color-mix(in oklab, #fff 8%, transparent)` |
| `--ut-border` | `transparent`; 10% white in dark mode |
| `--ut-track` | `color-mix(in oklab, #fff 10%, transparent)` |
| `--ut-shadow` | two-layer shadow, stronger in dark mode |
| `--ut-ease-enter` | `cubic-bezier(0.2, 0.8, 0.2, 1)` |

Stable class names: `.ut-region`, `.ut-stack`, `.ut-toast` (with `data-state="idle|undoing|done|error"`), `.ut-icon`, `.ut-message`, `.ut-undo`, `.ut-kbd`, `.ut-dismiss`, `.ut-progress`. For bigger changes, publish the view.

## Security

Only the user who made the change, in the same panel, can undo it. Entries are encrypted with your app key (hard-delete snapshots can hold any column), single-use through an atomic claim, and expire 30 seconds after the toast. The undo runs as a Livewire call, so CSRF, checksums and the panel's auth middleware apply.

Undo does not call your resource's `canRestore()` or `canDelete()`. If undo should need its own permission, or permissions can change within seconds, add a check:

```php
use HoceineEl\UndoToast\Support\UndoEntry;

UndoToastPlugin::make()->authorizeUndoUsing(
    fn (UndoEntry $entry): bool => auth()->user()->can('restore', $entry->snapshot->modelClass),
)
```

The entry exposes `snapshot->operation`, `snapshot->modelClass`, `snapshot->keys`, `snapshot->count()`, `userId`, `panelId`, `message` and `expiresAt`. A refused undo shows "You can't undo this change." Records are looked up without global scopes, so multi-tenant apps should check tenancy here too.

## FAQ

**No toast appears.** Check the plugin is on this panel, that you ran `php artisan filament:assets` (a 404 for `undo-toast.js` in the console means you did not), and that the action is covered and not excluded.

**Undo says it expired right away.** The cache store does not share entries between requests: the `array` store, or `file` behind a load balancer. Set `UNDO_TOAST_STORE` to a shared store.

**Filament's notification still shows next to the toast.** The action customises its success notification, maybe through `configureUsing()`. Customised notifications are kept.

**`mod+z` does nothing.** Focus is in a text field, where the browser's text undo wins. Press `Escape` or click outside first.

**My `undoable()` closure fails to serialise.** It captured something like a Livewire component through `$this`. Use `static fn`.

**Which model events fire on undo?** Soft-delete undo fires `restoring` / `restored`; restore undo fires `deleting` / `deleted`; edit undo fires the save and update events; hard-delete undo fires `saving`, `creating`, `created` and `saved` for the parent row only. Detach undo uses `attach()`.

## Testing

```bash
composer test               # Pest
vendor/bin/pest --parallel  # faster
composer analyse            # PHPStan
composer format             # Pint
```

The Playwright suite runs against the Testbench workbench in `workbench/`:

```bash
npm ci && npm run build
npx playwright install chromium
npm run test:e2e
```

`composer serve` starts the workbench at `/admin`; add `?locale=ar` (or `fr`, `de`, `ja`) to switch language. `UT_SCREENSHOTS=1 npm run test:e2e -- screenshots` retakes the README images.

To test undo in your app, read the token from the `undo-toast-push` event and call `undo` on the toast component:

```php
use Filament\Actions\Testing\TestAction;
use HoceineEl\UndoToast\Livewire\UndoToast;
use Livewire\Livewire;

it('undoes a delete', function (): void {
    $customer = Customer::factory()->create();

    $component = Livewire::test(ListCustomers::class)
        ->callAction(TestAction::make('delete')->table($customer))
        ->assertDispatched('undo-toast-push');

    $token = collect(data_get($component->effects, 'dispatches'))
        ->firstWhere('name', 'undo-toast-push')['params']['token'];

    Livewire::test(UndoToast::class)
        ->call('undo', $token)
        ->assertDispatched('undo-toast-undone')
        ->assertNotified('Customer restored');

    expect($customer->fresh()->trashed())->toBeFalse();
});
```

## More from Hoceine

Paid packages for Filament 4 and 5 by the same author:

<table>
<tr>
<td width="33%" valign="top"><a href="https://filamentcraft.dev?ref=undo-toast"><img src="https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/more/filamentcraft.jpg" alt="FilamentCraft"></a><br><b><a href="https://filamentcraft.dev?ref=undo-toast">FilamentCraft</a></b><br>A visual website builder inside your Filament panel: drag sections onto a live canvas, edit against a preview, publish.</td>
<td width="33%" valign="top"><a href="https://media-library.hoceine.com?ref=undo-toast"><img src="https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/more/media-library-pro.jpg" alt="Media Library Pro"></a><br><b><a href="https://media-library.hoceine.com?ref=undo-toast">Media Library Pro</a></b><br>One shared media library: folders, tags, focal points, chunked uploads, duplicate detection and usage tracking.</td>
<td width="33%" valign="top"><a href="https://scheduler-pro.hoceine.com?ref=undo-toast"><img src="https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/more/scheduler-pro.jpg" alt="Scheduler Pro"></a><br><b><a href="https://scheduler-pro.hoceine.com?ref=undo-toast">Scheduler Pro</a></b><br>Resource timelines, Gantt with dependencies, availability-aware booking, recurring events and Kanban boards.</td>
</tr>
</table>

## Contributing

Open an issue first for larger changes. After editing `resources/js` or `resources/css`, run `npm run build` to rebuild `dist/`, then run the tests above.

To add a language, copy `resources/lang/en/undo-toast.php` into a folder named with Filament's locale code, reuse Filament's own terms, and add the code to `TRANSLATION_LOCALES` in `tests/Unit/TranslationsTest.php`. The suite checks keys, placeholders and plural forms.

Report security issues privately as described in [SECURITY.md](SECURITY.md). Release notes are in [CHANGELOG.md](CHANGELOG.md).

## Credits

- [Hoceine El Idrissi](https://github.com/HoceineEl)
- Built on [Filament](https://filamentphp.com) and [Livewire](https://livewire.laravel.com)

## License

MIT. See [LICENSE.md](LICENSE.md).
