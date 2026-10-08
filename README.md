<p class="filament-hidden"><img src="https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/cover.jpg" alt="Undo Toast"></p>

# Undo Toast for Filament

Undo Toast adds a Gmail-style "Deleted 3 customers · Undo" toast to Filament 4 and 5 panels. After a delete, restore, edit or detach, a small toast at the bottom of the screen offers to reverse the change for a few seconds, with a countdown bar and a `⌘Z` / `Ctrl+Z` shortcut.

It works with the actions you already have. You register one plugin on a panel; you do not change your resources, tables or actions, and the plugin does not take over your `before()` / `after()` callbacks.

![Undo toast after deleting three customers](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/hero.png)

## Why

A confirmation modal asks "are you sure?" before every destructive action, and people learn to click through it. An undo toast lets the action happen straight away and makes a mistake cheap to fix afterwards. Filament already has soft deletes and restore actions, but restoring means finding the trashed filter, locating the record and clicking again. Undo Toast turns that into one click (or one key press) in the moment the mistake is made, and it covers cases a trash bin cannot: hard deletes, edits and detached relations.

## Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [What can be undone](#what-can-be-undone)
  - [Soft deletes](#soft-deletes)
  - [Hard deletes](#hard-deletes)
  - [Restore](#restore)
  - [Edits](#edits)
  - [Detach](#detach)
  - [What is not covered](#what-is-not-covered)
- [How it works](#how-it-works)
  - [Action lifecycle](#action-lifecycle)
  - [What gets snapshotted](#what-gets-snapshotted)
  - [Storage and expiry](#storage-and-expiry)
  - [Redirects](#redirects)
- [Configuration reference](#configuration-reference)
  - [duration()](#duration)
  - [position()](#position)
  - [maxVisible()](#maxvisible)
  - [operations()](#operations)
  - [except()](#except)
  - [exceptModels()](#exceptmodels)
  - [hardDeletes()](#harddeletes)
  - [keyBindings()](#keybindings)
  - [restoreRelations()](#restorerelations)
  - [replaceSuccessNotification()](#replacesuccessnotification)
  - [authorizeUndoUsing()](#authorizeundousing)
  - [recordUrlUsing()](#recordurlusing)
  - [strategy()](#strategy)
  - [Static helpers](#static-helpers)
  - [Config file](#config-file)
- [Per-action opt-in and opt-out](#per-action-opt-in-and-opt-out)
- [Restoring relations on hard delete](#restoring-relations-on-hard-delete)
- [Custom undo strategies](#custom-undo-strategies)
- [Filament's success notification](#filaments-success-notification)
- [Model labels and pluralisation](#model-labels-and-pluralisation)
- [The toast stack](#the-toast-stack)
- [Keyboard and accessibility](#keyboard-and-accessibility)
- [Right-to-left languages](#right-to-left-languages)
- [Translations](#translations)
- [Theming](#theming)
- [Multiple panels](#multiple-panels)
- [Security](#security)
- [FAQ and troubleshooting](#faq-and-troubleshooting)
- [Testing](#testing)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [Security vulnerabilities](#security-vulnerabilities)
- [Credits](#credits)
- [License](#license)

## Features

- Works out of the box for `DeleteAction`, `DeleteBulkAction`, `RestoreAction`, `RestoreBulkAction`, `EditAction`, `DetachAction` and `DetachBulkAction`.
- Soft deletes are restored. Hard-deleted rows are re-inserted with their original primary key and column values, and can bring their pivot rows and child records back with them.
- Edits revert only the columns the edit changed, and leave alone any column someone else changed in the meantime.
- Detached records are attached again with their original pivot data.
- Any other action can opt in with `->undoable(fn (Model $record) => ...)`, and any built-in action can opt out with `->undoable(false)`.
- The toast replaces Filament's own "Deleted" / "Saved" / "Detached" notification for the same action, so the user reads one message, not two. Notifications you customised are kept.
- `mod+z` (`⌘Z` on macOS, `Ctrl+Z` elsewhere) undoes the newest toast, and never steals text undo from an input.
- Several toasts stack and fan out on hover, focus or tap.
- Entries are encrypted, single-use, tied to the user and panel that made the change, and undone inside a database transaction.
- The toast survives redirects, so deleting from an edit page still shows it on the list page.
- Light and dark mode, right-to-left layouts, phones, reduced motion and Windows High Contrast mode.
- 23 languages with the correct plural forms for each.

## Requirements

| Package | Version |
| --- | --- |
| PHP | 8.3 or newer |
| Laravel | 11, 12 or 13 |
| Filament | 4 or 5 |
| Livewire | 3 (Filament 4) or 4 (Filament 5), installed by Filament |
| `spatie/laravel-package-tools` | 1.16 or newer, installed automatically |

A cache store is needed to hold undo entries. The default cache store of your app is used unless you choose another one (see [Config file](#config-file)).

## Installation

Install the package with Composer:

```bash
composer require hoceineel/filament-undo-toast
```

Publish Filament's assets so the toast's JavaScript and CSS are copied to `public/`:

```bash
php artisan filament:assets
```

Run it again after every update of the package. Filament's `filament:upgrade` command, which most Filament apps run from the `post-autoload-dump` script in `composer.json`, does this for you.

You do not need a custom Filament theme: the plugin ships its own stylesheet, loaded on demand.

Optionally publish the config file, the translations or the view:

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

That is all. Delete a record from any table in the panel and the toast appears at the bottom of the screen:

![A single delete](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/delete-single.png)

Click **Undo**, or press `⌘Z` / `Ctrl+Z`, and the record comes back. The table refreshes on its own, the toast confirms what happened, and a Filament notification offers a link to the record when you configure [`recordUrlUsing()`](#recordurlusing):

![After undoing: the toast confirms, the row is back, and a notification links to the record](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/undone.png)

## What can be undone

| Action | What is kept before the action runs | What Undo does |
| --- | --- | --- |
| `DeleteAction`, `DeleteBulkAction` on a model that uses `SoftDeletes` | the primary keys | calls `restore()` on each record |
| `DeleteAction`, `DeleteBulkAction` on any other model | the raw database rows (connection, table, every column), plus [opted-in relations](#restoring-relations-on-hard-delete) | re-inserts the rows with their original keys |
| `RestoreAction`, `RestoreBulkAction` | the primary keys of the records that were trashed | soft deletes them again |
| `EditAction` | the raw database row | puts back the columns the edit changed |
| `DetachAction`, `DetachBulkAction` on a `BelongsToMany` relation | the pivot rows, with every pivot column | attaches the records again with their pivot data |
| Any action with `->undoable(fn ...)` | the primary keys, your closure and an optional message | calls your closure for each record |

Bulk actions that only partly succeed offer undo for the records that really changed. An action that fails, is halted, is cancelled or changes nothing creates no toast.

### Soft deletes

Soft deletes are the safest case: the row never leaves the table, so Undo only clears `deleted_at` by calling the model's `restore()` method. Model events (`restoring`, `restored`) fire as usual. Records that were already trashed when the bulk delete ran are left out of the toast.

### Hard deletes

Models without `SoftDeletes` are covered too. Before the delete runs, the plugin reads the rows straight from the database (bypassing global scopes and casts) and keeps every column. Undo inserts them again with the same primary key and the same column values, including `created_at` and `updated_at`.

Re-inserting restores that row and the relations you opt in to. Anything else removed by `ON DELETE CASCADE` foreign keys, or by your own `deleting` / `deleted` model events, stays gone. Model events for the re-insert (`creating`, `created`, `saving`, `saved`) do fire. If a row with the same key exists by the time the user clicks Undo, nothing is inserted and the toast explains why.

If that trade-off does not suit your app, turn hard-delete undo off; soft deletes keep working:

```php
UndoToastPlugin::make()->hardDeletes(false)
```

![Hard delete of a project, in dark mode](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/dark.png)

### Restore

Restoring trashed records from the trashed filter offers an undo that soft deletes them again. Only records that were really trashed when the action ran are counted.

![Restore toast](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/restore.png)

### Edits

`EditAction` (editing in a modal, for example on a `ManageRecords` page or as a table record action) is covered. The plugin reads the raw row before the save and again after it, and keeps only the columns that changed, ignoring `updated_at`. An edit that saved the same values creates no toast.

On Undo, each changed column is put back only if it still holds the value the edit wrote. If someone changed that column again in the meantime, it is left alone. If every column was changed since, nothing is written and the toast says so.

![Edit toast](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/edit.png)

The **Save** button on a resource's Edit page is not an action call in Filament (it submits the page form), so it is not covered. See the [FAQ](#faq-and-troubleshooting).

### Detach

`DetachAction` and `DetachBulkAction` on a `BelongsToMany` (or `MorphToMany`) relation manager are covered. The plugin keeps the full pivot rows, including extra pivot columns such as a `role`, and attaches the records again with that data. If any of those records was attached again in the meantime, nothing is attached twice: the undo stops and the toast reports a conflict.

![Detach toast](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/detach.png)

### What is not covered

- `ForceDeleteAction` and `ForceDeleteBulkAction` are never undoable, not even with `->undoable(fn ...)`: the intent of a force delete is permanent.
- `DissociateAction`, `DissociateBulkAction`, `ReplicateAction`, `CreateAction`, `AttachAction` and `AssociateAction` are not covered by a built-in strategy. Use `->undoable()` or a [custom strategy](#custom-undo-strategies) when you need them.
- Saving the form on an Edit page (see [Edits](#edits)).
- Changes your own code makes outside a Filament action.

## How it works

### Action lifecycle

Filament dispatches two events around every action call: `Filament\Actions\Events\ActionCalling` just before the action runs, and `Filament\Actions\Events\ActionCalled` just after. Undo Toast listens to both. It does not wrap or replace the action's `action()` closure, so your `before()`, `after()` and `using()` callbacks behave exactly as they did.

1. **ActionCalling.** The plugin checks that it is registered on the current panel and that the action is not excluded (`except()`, `exceptModels()`, `->undoable(false)`). It then asks each strategy, in order, whether it supports the action. The first strategy that does takes a snapshot. If the snapshot contains at least one record and the operation is allowed by `operations()`, it is kept in memory for this request.
2. **The action runs** as usual.
3. **ActionCalled.** The plugin takes the snapshot back. Built-in strategies then check the database to see what really changed (which records are now trashed, which rows are gone, which columns differ) and narrow the snapshot to those records. If nothing changed, no toast is created. For `->undoable()` closures, the toast is only created when the action reports success.
4. If the action succeeded, Filament's own success notification is removed for this call (see [Filament's success notification](#filaments-success-notification)).
5. **After the database transaction commits** (`DB::afterCommit`), the plugin builds the message, stores an encrypted entry in the cache, dispatches an `undo-toast-push` browser event from the Livewire component that ran the action, and flashes the same payload to the session.
6. **Undo.** Clicking Undo calls the `undo()` method on the toast's own Livewire component; there is no extra route. The component checks the entry (see [Security](#security)), claims it so it cannot be used twice, and runs the strategy's `undo()` inside `DB::transaction()`. On success it dispatches `undo-toast-undone`, sends a Filament success notification, and refreshes every other Livewire component on the page so tables show the restored rows straight away.

### What gets snapshotted

| Operation | Snapshot contents |
| --- | --- |
| Soft delete, restore | model class, primary keys, model labels |
| Hard delete | model class, connection, table, the raw rows keyed by primary key, opted-in relation rows, model labels |
| Edit | model class, primary key, the changed columns before and after |
| Detach | parent class and key, relation name, the detached pivot rows |
| `->undoable()` | model class, primary keys, the serialised closure, the message and the action label |

The model labels are resolved when the action runs, in the user's locale, so the message reads naturally (see [Model labels and pluralisation](#model-labels-and-pluralisation)).

### Storage and expiry

Each entry is stored in the cache under `undo-toast:{token}`, encrypted with your app key. The token is 40 random alphanumeric characters. The entry lives for the toast duration plus a 30 second grace period, which covers slow networks and a click right at the end of the countdown. It is removed as soon as it is used.

### Redirects

Some actions redirect, such as `DeleteAction` on an Edit page. The toast payload is also flashed to the session (the last five entries), so the toast appears on the next page with the time it has left. Toasts the user already dismissed or used are remembered in `sessionStorage`, so a reload does not show them again.

## Configuration reference

Every option is a fluent method on the plugin. Every value can also be a closure, evaluated when it is needed, so you can decide per user, per locale or per request.

```php
use App\Filament\Resources\AuditLogs\AuditLogResource;
use App\Models\Payment;
use HoceineEl\UndoToast\Enums\ToastPosition;
use HoceineEl\UndoToast\Enums\UndoableOperation;
use HoceineEl\UndoToast\UndoToastPlugin;

UndoToastPlugin::make()
    ->duration(8)
    ->position(ToastPosition::BottomCenter)
    ->maxVisible(3)
    ->operations([UndoableOperation::Delete, UndoableOperation::Detach])
    ->except([AuditLogResource::class])
    ->exceptModels([Payment::class])
    ->hardDeletes(true)
    ->keyBindings(['mod+z'])
    ->replaceSuccessNotification(true);
```

| Method | Accepts | Default |
| --- | --- | --- |
| `duration()` | `int` seconds, or a closure | `8` |
| `position()` | `ToastPosition`, or a closure | `ToastPosition::BottomCenter` |
| `maxVisible()` | `int`, or a closure | `3` |
| `operations()` | array of `UndoableOperation`, `null`, or a closure | `null` (all operations) |
| `except()` | array of resource classes, or a closure | `[]` |
| `exceptModels()` | array of model classes, or a closure | `[]` |
| `hardDeletes()` | `bool`, or a closure | `true` |
| `keyBindings()` | array of strings, or a closure | `['mod+z']` |
| `restoreRelations()` | array of model class to relation names, or a closure | `[]` |
| `replaceSuccessNotification()` | `bool`, or a closure receiving `$action` | `true` |
| `authorizeUndoUsing()` | closure receiving `$entry`, or `null` | `null` (no extra check) |
| `recordUrlUsing()` | closure receiving `$record`, or `null` | `null` (no link) |
| `strategy()` | a class implementing `UndoStrategy` | none |

### duration()

How many seconds the toast offers undo. Values below 1 are raised to 1.

```php
UndoToastPlugin::make()->duration(10)

UndoToastPlugin::make()->duration(fn (): int => auth()->user()?->prefers_slow_toasts ? 15 : 8)
```

The countdown pauses while the pointer is over the toast, while keyboard focus is inside it and while the browser tab is hidden, so the user never loses time they could not use.

![The progress line shows the time left](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/countdown.png)

### position()

Where the toast stack sits. All positions are along the bottom edge and use logical sides, so `BottomStart` is on the left in left-to-right languages and on the right in right-to-left languages.

```php
use HoceineEl\UndoToast\Enums\ToastPosition;

UndoToastPlugin::make()->position(ToastPosition::BottomEnd)
```

| Case | Value |
| --- | --- |
| `ToastPosition::BottomCenter` | `bottom-center` (default) |
| `ToastPosition::BottomStart` | `bottom-start` |
| `ToastPosition::BottomEnd` | `bottom-end` |

### maxVisible()

How many toasts the stack shows at once. Older toasts beyond this number are hidden and cannot be focused, but stay undoable until they expire. Values below 1 are raised to 1.

```php
UndoToastPlugin::make()->maxVisible(5)
```

### operations()

Limit undo to some operations. `null` (the default) allows all of them.

```php
use HoceineEl\UndoToast\Enums\UndoableOperation;

UndoToastPlugin::make()->operations([
    UndoableOperation::Delete,
    UndoableOperation::Restore,
])
```

| Case | Covers |
| --- | --- |
| `UndoableOperation::Delete` | `DeleteAction`, `DeleteBulkAction` (soft and hard) |
| `UndoableOperation::Restore` | `RestoreAction`, `RestoreBulkAction` |
| `UndoableOperation::Edit` | `EditAction` |
| `UndoableOperation::Detach` | `DetachAction`, `DetachBulkAction` |
| `UndoableOperation::Custom` | actions using `->undoable(fn ...)`, and custom strategies that use this case |

When an operation is not allowed, the action behaves as if the plugin were not installed, and Filament's own notification is shown.

### except()

Skip whole resources. Actions on the pages of these resources get no toast, and neither do actions on their models anywhere else in the panel (for example in a relation manager of another resource).

```php
UndoToastPlugin::make()->except([
    AuditLogResource::class,
    PaymentResource::class,
])
```

### exceptModels()

Skip models, wherever their actions appear. Subclasses of a listed model are skipped too.

```php
UndoToastPlugin::make()->exceptModels([Payment::class, Invoice::class])
```

### hardDeletes()

Whether deletes on models without `SoftDeletes` are undoable. Defaults to `true`. See [Hard deletes](#hard-deletes) for what a re-insert does and does not restore.

```php
UndoToastPlugin::make()->hardDeletes(false)

UndoToastPlugin::make()->hardDeletes(fn (): bool => app()->isLocal())
```

When this returns `false`, entries already created by the hard-delete strategy can no longer be undone either.

### keyBindings()

The keyboard shortcuts that undo the newest toast. Pass an empty array to turn the shortcut off.

```php
UndoToastPlugin::make()->keyBindings(['mod+z', 'mod+shift+z'])

UndoToastPlugin::make()->keyBindings([])
```

A binding is a list of modifiers and one key, joined by `+`, case-insensitive:

| Modifier | Meaning |
| --- | --- |
| `mod` | `⌘` on macOS and iOS, `Ctrl` everywhere else |
| `ctrl` | `Ctrl` on every platform |
| `meta`, `command` | `⌘` / Windows key |
| `shift` | `Shift` |
| `alt`, `option` | `Alt` / `⌥` |

The key is compared with `KeyboardEvent.key`, so use `z`, `u`, `backspace` and so on. The modifiers must match exactly: `mod+z` does not fire on `mod+shift+z`.

The first binding is shown as a hint chip next to the Undo button of the newest toast, and announced to assistive technology through `aria-keyshortcuts`. The chip is hidden on touch devices and narrow screens.

![The shortcut hint next to Undo](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/keyboard-hint.png)

The plugin also renders a hidden `data-keyboard-shortcut` element with the label "Undo last action", which shortcut cheat sheets such as [Keyboard Shortcuts](https://github.com/HoceineEl/filament-keyboard-shortcuts) can list.

### restoreRelations()

Relations to bring back with a hard-deleted record, keyed by model class. See [Restoring relations on hard delete](#restoring-relations-on-hard-delete).

```php
UndoToastPlugin::make()->restoreRelations([
    Project::class => ['tags', 'tasks'],
    Invoice::class => ['lines'],
])
```

### replaceSuccessNotification()

Whether the toast replaces Filament's own success notification for the same action. Defaults to `true`. The closure receives the action as `$action`, or by type.

```php
use Filament\Actions\Action;
use Filament\Actions\EditAction;

UndoToastPlugin::make()->replaceSuccessNotification(false)

UndoToastPlugin::make()->replaceSuccessNotification(
    fn (Action $action): bool => ! $action instanceof EditAction,
)
```

See [Filament's success notification](#filaments-success-notification) for the rules.

### authorizeUndoUsing()

An extra authorization check that runs when the user clicks Undo, on top of the built-in ownership check. Return `false` to refuse. The closure receives the `UndoEntry` as `$entry`, or by type.

```php
use HoceineEl\UndoToast\Support\UndoEntry;

UndoToastPlugin::make()->authorizeUndoUsing(
    fn (UndoEntry $entry): bool => auth()->user()->can('restore', $entry->snapshot->modelClass),
)
```

Useful fields on the entry:

| Property | Type | Meaning |
| --- | --- | --- |
| `$entry->snapshot->operation` | `UndoableOperation` | what is being undone |
| `$entry->snapshot->modelClass` | `class-string<Model>` | the affected model |
| `$entry->snapshot->keys` | `list<int\|string>` | the affected primary keys |
| `$entry->snapshot->count()` | `int` | how many records |
| `$entry->userId` | `?string` | the user who made the change |
| `$entry->panelId` | `?string` | the panel it was made in |
| `$entry->message` | `string` | the toast message |
| `$entry->expiresAt` | `CarbonImmutable` | when the entry expires |

A refused undo shows "You can't undo this change." and the entry stays unused until it expires.

### recordUrlUsing()

Adds an **Open** link to the success notification shown after one record is brought back. It is most useful when the original action redirected away, such as deleting from an Edit page. The closure receives the restored record as `$record`, or by type. Return `null` for no link.

```php
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

UndoToastPlugin::make()->recordUrlUsing(
    fn (Model $record): ?string => $record instanceof Customer
        ? CustomerResource::getUrl('edit', ['record' => $record])
        : null,
)
```

No link is added when the undo affected more than one record.

### strategy()

Registers a custom undo strategy class. Call it once per strategy. Custom strategies are asked before the built-in ones. See [Custom undo strategies](#custom-undo-strategies).

```php
UndoToastPlugin::make()
    ->strategy(ApproveInvoiceStrategy::class)
    ->strategy(DissociateStrategy::class)
```

### Static helpers

| Method | Returns |
| --- | --- |
| `UndoToastPlugin::make()` | a new plugin instance, resolved from the container |
| `UndoToastPlugin::get()` | the plugin registered on the current panel; fails if there is none |
| `UndoToastPlugin::current()` | the plugin registered on the current panel, or `null` |
| `UndoToastPlugin::currentUserId()` | the current user's id as a string, from the panel's auth guard, or `null` |

Getters used by the package are public too, if you need them in your own code: `getDuration()`, `getPosition()`, `getMaxVisible()`, `getKeyBindings()`, `hasHardDeletes()`, `getStrategies()`, `getStrategyClasses()`, `allowsOperation()`, `appliesTo()`, `canUndo()`, `shouldReplaceSuccessNotification()`, `getUndoableRelations()` and `getRecordUrl()`.

### Config file

`config/undo-toast.php` has one option, the cache store for undo entries:

```php
return [
    'store' => env('UNDO_TOAST_STORE'),
];
```

`null` uses your app's default cache store. When you run more than one web server, use a shared store such as Redis or the database, so the server that handles the Undo click can read the entry written by the server that handled the delete. The `array` store does not work across requests.

## Per-action opt-in and opt-out

The package adds an `undoable()` macro to every Filament action.

Opt a built-in action out:

```php
use Filament\Actions\DeleteAction;

DeleteAction::make()->undoable(false)
```

Give any action an undo by passing a closure. The closure is called once per affected record, with the record as `$record`:

```php
use App\Models\Order;
use Filament\Actions\Action;

Action::make('archive')
    ->action(fn (Order $record) => $record->archive())
    ->undoable(fn (Order $record) => $record->unarchive());
```

It works for bulk actions too; the closure runs for each selected record:

```php
use Filament\Actions\BulkAction;
use Illuminate\Support\Collection;

BulkAction::make('archive')
    ->action(fn (Collection $records) => $records->each->archive())
    ->undoable(fn (Order $record) => $record->unarchive());
```

The default message is the action label followed by the model label, such as "Archive: order" or "Archive: 3 orders". Pass a message as the second argument, as a string or a closure evaluated by the action:

```php
->undoable(fn (Order $record) => $record->unarchive(), 'Order archived')

->undoable(
    fn (Order $record) => $record->unarchive(),
    fn (Order $record): string => "Order {$record->number} archived",
)
```

Things to know about `undoable()` closures:

- The closure is serialised into the encrypted cache entry with `laravel/serializable-closure` and runs in a later request. Prefer `static fn` and avoid capturing `$this` or large objects. Resolve services inside the closure instead.
- The closure must name its parameter `$record`. Other parameters are resolved from the container.
- Records are looked up again by primary key when Undo runs, without global scopes. If none of them exist any more, the undo fails with "The records are no longer there to undo."
- The toast is only created when the action reports success; a halted or failed action creates nothing.
- A closure on a built-in action (for example `DeleteAction::make()->undoable(fn ...)`) takes the place of the built-in strategy for that action.
- `ForceDeleteAction` and `ForceDeleteBulkAction` ignore `undoable()` closures.
- Calling `->undoable()` or `->undoable(true)` with no closure does nothing by itself; it is the default.

## Restoring relations on hard delete

When a record is hard deleted, its pivot rows and child rows are often deleted with it, by cascading foreign keys or by your own model events. Tell the plugin which relations belong to the record, and undoing the delete brings them back too.

Implement `HasUndoableRelations` on the model:

```php
use HoceineEl\UndoToast\Contracts\HasUndoableRelations;
use Illuminate\Database\Eloquent\Model;

class Project extends Model implements HasUndoableRelations
{
    public function undoableRelations(): array
    {
        return ['tags', 'tasks'];
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('role')->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
```

Or list them on the plugin, which is handy for models you do not own:

```php
UndoToastPlugin::make()->restoreRelations([
    Project::class => ['tags', 'tasks'],
    Invoice::class => ['lines', 'comments'],
])
```

Both sources are merged, and a model class in `restoreRelations()` also matches its subclasses. Before the delete runs, the plugin keeps a copy of:

| Relation type | What is kept | On undo |
| --- | --- | --- |
| `BelongsToMany`, `MorphToMany` | the pivot rows, with every pivot column | inserts the pivot rows that are missing, skipping links to related records that no longer exist |
| `HasMany`, `HasOne`, `MorphMany`, `MorphOne` | the related rows, with every column | inserts the rows whose primary key is free; a row whose key was reused since is left alone |

Details:

- Relations are restored in the same transaction as the parent row, right after it.
- Only one level is restored: a task's own comments are not.
- Rows are written with the query builder, so their model events do not fire, and existing rows are never overwritten.
- Relation snapshots apply to hard deletes only. Soft-deleted records keep their relations in the database already.
- Other relation types (`BelongsTo`, `HasManyThrough`, `HasOneThrough`) throw an `InvalidArgumentException` when the delete runs, so a typo or an unsupported relation surfaces during development.
- The copy lives in the encrypted cache entry. Keep these relations to a size you would be happy to hold in the cache for a few seconds.

## Custom undo strategies

A strategy decides whether it handles an action, takes a snapshot before the action runs, undoes it later, and describes it for the toast. Implement `HoceineEl\UndoToast\Contracts\UndoStrategy`:

```php
namespace App\Filament\Undo;

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

Register it on the plugin:

```php
UndoToastPlugin::make()->strategy(ApproveInvoiceStrategy::class)
```

How strategies are used:

- Strategies are resolved from the container, so constructor injection works.
- Custom strategies are asked first, in the order you registered them, then the built-in ones. The first strategy whose `supports()` returns `true` handles the action, even if its `snapshot()` returns `null`.
- Return `null` from `snapshot()`, or a snapshot with no keys, to skip the toast.
- The `Snapshot` is serialised and encrypted into the cache, so keep `data` to plain values.
- `undo()` runs inside `DB::transaction()`. Throw `UndoFailed::because(UndoFailure::...)` to show a specific reason; any other exception is reported to your exception handler and shown as a generic error, and the transaction is rolled back.
- `describe()` returns the toast message. To reuse the built-in wording, return `$snapshot->operation->describe($snapshot->count(), $snapshot->labels())`.
- A strategy that also implements `HoceineEl\UndoToast\Contracts\ConfirmsOutcome` gets a `confirm(Snapshot $snapshot): ?Snapshot` call after the action ran, and before the action's success is checked. Return a narrowed snapshot (`$snapshot->with($keys, $data)`) with only the records that really changed, or `null` when nothing changed. Strategies without `confirm()` only create a toast when the action reports success.
- The success message after undo comes from `undo-toast::undo-toast.undone.{operation}`.
- Entries are only undone by a strategy that is still registered on the panel. Removing a strategy invalidates its pending entries.

Failure reasons, from `HoceineEl\UndoToast\Enums\UndoFailure`:

| Case | Message (English) |
| --- | --- |
| `Unavailable` | Undo isn't available here. |
| `Forbidden` | You can't undo this change. |
| `Expired` | This undo has expired or was already used. |
| `Missing` | The records are no longer there to undo. |
| `Conflict` | A record with the same key already exists. |
| `ChangedSince` | Someone changed this record since, so it was left as is. |
| `Error` | Something went wrong while undoing. Nothing was changed. |

A failed undo turns the toast red, stays on screen until it is dismissed, and also sends a Filament danger notification titled "Couldn't undo".

![A failed undo](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/error.png)

## Filament's success notification

Without the plugin, a delete shows Filament's "Deleted" notification in the corner. With an undo toast at the bottom, that would say the same thing twice. So when an action gets an undo toast, the plugin removes Filament's success notification for that call.

It only does so when it is sure the toast will appear. Filament's notification still shows when:

- the action failed, was halted or was cancelled;
- the action changed nothing (for example, an edit that saved the same values);
- the action is excluded from undo (`except()`, `exceptModels()`, `operations()`, `->undoable(false)`);
- you customised the notification on the action, with `->successNotification(...)` or `->successNotificationTitle(...)`, directly or through `configureUsing()`, or you disabled it.

Failure and partial-failure notifications from bulk actions are never touched.

Turn the replacement off everywhere, or decide per action:

```php
UndoToastPlugin::make()->replaceSuccessNotification(false)

UndoToastPlugin::make()->replaceSuccessNotification(fn (Action $action): bool => ! $action instanceof EditAction)
```

## Model labels and pluralisation

Messages are built from the model labels your actions already use, `getModelLabel()` and `getPluralModelLabel()`. Labels you set on a resource (`$modelLabel`, `$pluralModelLabel`, `getModelLabel()`) or on a table are used as they are, in the current locale.

When a resource sets no label, Filament falls back to an English name made from the class (`Customer` becomes "customer"), which reads oddly in an Arabic or German panel. In that case only, the plugin looks for a translation, in this order:

1. `undo-toast::undo-toast.models.{ClassBasename}.label` and `.plural_label`, in your published package translations:

    ```php
    // lang/vendor/undo-toast/ar/undo-toast.php
    return [
        'models' => [
            'Customer' => ['label' => 'العميل', 'plural_label' => 'العملاء'],
        ],
    ];
    ```

2. Your app's own JSON translation of the default label, such as `"customer": "Kunde"` and `"customers": "Kunden"` in `lang/de.json`.

If neither exists, the default label is kept. Nothing is guessed.

Messages use Laravel's `trans_choice()` with explicit plural ranges, for example in English:

```php
'delete' => '{1} :Label deleted|[0,*] Deleted :count :plural_label',
```

So one record reads "Customer deleted" and several read "Deleted 3 customers". `:Label` is the label with its first letter capitalised, `:label` is as it is, `:plural_label` is the plural label and `:count` the number of records. Languages with more plural forms list each range, so Russian reads "21 запись", "22 записи" and "25 записей".

## The toast stack

Several toasts stack on top of each other. The newest is in front, older ones peek out behind it, and toasts beyond [`maxVisible()`](#maxvisible) are hidden.

![Three toasts, collapsed](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/stack-collapsed.png)

Hovering the stack, moving keyboard focus into it, or tapping a stacked toast on a touch screen fans it out so every toast and its Undo button can be reached. The countdown of every toast pauses while the stack is expanded.

![Three toasts, expanded](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/stack-expanded.png)

The keyboard shortcut always undoes the newest pending toast. After a successful undo, the toast shows a confirmation with a check icon for a moment, long enough to read, then leaves on its own.

On phones the toast spans the screen width, keeps 44 by 44 pixel touch targets and respects the safe area at the bottom of the screen:

![On a phone](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/mobile.png)

## Keyboard and accessibility

- Toasts live in a `role="status"` region with `aria-live="polite"`. They never move focus.
- Screen readers hear the message and how long undo stays available ("You can undo this for 8 seconds.") once, when the toast appears, and hear the result after undoing.
- A failed undo is announced as an alert (`role="alert"`) and stays on screen until dismissed.
- The Undo button of the newest toast carries `aria-keyshortcuts` with the first key binding, and `aria-busy` while the undo runs.
- `Escape` dismisses the focused toast. The dismiss button has an accessible label and a tooltip.
- All buttons are at least 44 by 44 pixels and have visible focus rings.
- The countdown pauses while the pointer is over the toasts, while focus is inside them and while the browser tab is hidden.
- `mod+z` is ignored while focus is in a text input, textarea, select or editable content, so text undo keeps working. It does work while a checkbox, radio or button has focus, such as right after selecting rows for a bulk action. Key repeats are ignored.
- With `prefers-reduced-motion`, toasts fade instead of sliding.
- In Windows High Contrast mode (`forced-colors`) the toast keeps a visible border, focus ring and progress line.
- The toast is hidden when printing.

## Right-to-left languages

The toast mirrors itself in right-to-left locales such as Arabic, Hebrew and Persian: the icon moves to the right, the Undo and dismiss buttons to the left, and the countdown line shrinks towards the left. Positions use logical sides, so `ToastPosition::BottomStart` sits on the right in a right-to-left panel. No configuration is needed; the toast follows the `dir` attribute Filament sets on the page.

![Arabic, right to left](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/rtl.png)

![Arabic on a phone, in dark mode](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/rtl-mobile.png)

## Translations

The toast ships in 23 languages, using the same words as Filament's own actions in each language, so it reads naturally next to the rest of your panel:

| Code | Language | Code | Language | Code | Language |
| --- | --- | --- | --- | --- | --- |
| `ar` | Arabic | `he` | Hebrew | `pl` | Polish |
| `cs` | Czech | `hi` | Hindi | `pt` | Portuguese (Portugal) |
| `de` | German | `id` | Indonesian | `pt_BR` | Portuguese (Brazil) |
| `en` | English | `it` | Italian | `ru` | Russian |
| `es` | Spanish | `ja` | Japanese | `tr` | Turkish |
| `fa` | Persian | `ko` | Korean | `uk` | Ukrainian |
| `fr` | French | `nl` | Dutch | `vi` | Vietnamese |
| | | | | `zh_CN` | Chinese (Simplified) |
| | | | | `zh_TW` | Chinese (Traditional) |

The locale follows `app()->getLocale()`, like the rest of Filament. The message is built in the locale of the request that ran the action.

![German](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/locale-de.png)

![Japanese](https://raw.githubusercontent.com/HoceineEl/filament-undo-toast/main/docs/images/locale-ja.png)

To change any wording, publish the language files and edit `lang/vendor/undo-toast/{locale}/undo-toast.php`:

```bash
php artisan vendor:publish --tag=undo-toast-translations
```

You only need to keep the keys you change; anything missing falls back to the package's own file. The keys are:

| Key | Used for |
| --- | --- |
| `messages.{delete,restore,edit,detach,custom}` | the toast message after the action |
| `undone.{delete,restore,edit,detach,custom}` | the toast and notification after a successful undo |
| `actions.undo`, `actions.dismiss` | the toast buttons |
| `actions.undo_last` | the label of the shortcut for cheat sheets |
| `actions.open` | the link added by `recordUrlUsing()` |
| `states.undoing` | the button label while undoing |
| `states.countdown` | the screen reader text, with `:seconds` |
| `failures.title` and `failures.{reason}` | failure notification and toast messages |
| `models.{ClassBasename}.label`, `.plural_label` | optional model label translations |

## Theming

The toast uses an inverted surface for contrast (gray 900 in light mode, gray 800 with a hairline border in dark mode), your panel's primary colour for the Undo button and the countdown line, and your panel font. It follows the panel's dark mode on its own.

Override any `--ut-*` custom property in your panel's theme or any stylesheet registered with Filament. The plugin's stylesheet is loaded on demand, after your theme, so add `body` to the selector to win on specificity:

```css
body .ut-region {
    --ut-width: 32rem;
    --ut-offset: 1.5rem;
    --ut-accent: var(--success-400);
}

.dark body .ut-region {
    --ut-surface: var(--gray-950);
}
```

| Property | Default |
| --- | --- |
| `--ut-width` | `min(28rem, calc(100vw - 2rem))` |
| `--ut-offset` | `1rem` from the viewport edge, plus the bottom safe area |
| `--ut-radius` | `0.75rem` |
| `--ut-z` | `40`, below Filament modals |
| `--ut-surface` | `var(--gray-900)`, `var(--gray-800)` in dark mode |
| `--ut-text` | `var(--gray-50)` |
| `--ut-muted` | `var(--gray-400)` |
| `--ut-accent` | `var(--primary-400)` |
| `--ut-hover` | `color-mix(in oklab, #fff 8%, transparent)` |
| `--ut-border` | `transparent`, a 10% white hairline in dark mode |
| `--ut-track` | `color-mix(in oklab, #fff 10%, transparent)` |
| `--ut-shadow` | a soft two-layer shadow, stronger in dark mode |
| `--ut-ease-enter` | `cubic-bezier(0.2, 0.8, 0.2, 1)` |

The main elements have stable class names if you need more: `.ut-region`, `.ut-stack`, `.ut-toast` (with `data-state="idle|undoing|done|error"`), `.ut-icon`, `.ut-message`, `.ut-undo`, `.ut-kbd`, `.ut-dismiss` and `.ut-progress`. For deeper changes, publish the view with `--tag=undo-toast-views`.

## Multiple panels

Register the plugin on each panel separately; each registration has its own options:

```php
// AdminPanelProvider
->plugin(UndoToastPlugin::make()->duration(10))

// AppPanelProvider
->plugin(UndoToastPlugin::make()->operations([UndoableOperation::Delete]))
```

Panels without the plugin show no toast and create no entries. An entry is tied to the panel it was created in: a token from one panel cannot be undone from another, even by the same user. The user id comes from each panel's own auth guard.

## Security

- Entries are encrypted with your app key before they reach the cache, because hard-delete snapshots can contain any column of the row.
- Tokens are 40 random characters, valid for the toast duration plus a 30 second grace period, and can be used once. An atomic claim in the cache stops double clicks and concurrent requests from undoing twice.
- Only the user who made the change, in the same panel, can undo it. Guests (no authenticated user) can only undo entries created as guests.
- `authorizeUndoUsing()` adds your own rule on top, such as a policy check. Undo does not run your Filament resource's `canRestore()` or `canDelete()` checks on its own; if your permissions can change within a few seconds, or undo should need a different permission than the original action, add them here.
- Only strategies still registered on the panel can run an entry.
- The undo runs in a database transaction. If it fails, nothing changes and the toast shows why. Unexpected exceptions are reported to your exception handler, and their details are not shown to the user.
- The undo endpoint is a Livewire method on the toast component, so it goes through Livewire's request checks (CSRF, checksums) and the panel's persistent middleware, such as authentication.

## FAQ and troubleshooting

**The toast does not appear.**
Check that the plugin is registered on the panel you are using, that you ran `php artisan filament:assets` after installing or updating, and that the action is one of the [covered actions](#what-can-be-undone) and not excluded. Open the browser console: a 404 for `undo-toast.js` means the assets were not published.

**The toast appears, but Undo says it has expired.**
The cache store does not keep entries between requests. This happens with the `array` store, or with a per-server store such as `file` behind a load balancer. Set `UNDO_TOAST_STORE` to a shared store.

**Saving the Edit page shows no toast.**
The Save button on a resource Edit page submits the page form instead of calling an action, so Filament dispatches no action events. Only `EditAction` (modal editing) is covered.

**Do I need soft deletes?**
No. Soft deletes are the simplest and most complete case, but hard deletes are covered by re-inserting the row (see [Hard deletes](#hard-deletes)). Turn that off with `->hardDeletes(false)` if you only want undo where soft deletes exist.

**Undo of a hard delete did not bring back related rows.**
Rows removed by `ON DELETE CASCADE` or model events are only restored for relations listed in `undoableRelations()` or `restoreRelations()`. See [Restoring relations on hard delete](#restoring-relations-on-hard-delete).

**Filament's "Deleted" notification still shows next to the toast.**
The action customises its success notification or title, possibly through `configureUsing()`. Customised notifications are kept on purpose. Remove the customisation, or accept both.

**`mod+z` does nothing.**
Focus is probably in a text field, where the browser's own text undo takes priority. Click outside it or press `Escape` first. Also check that `keyBindings()` is not empty.

**Can I undo `ForceDeleteAction`?**
No. A force delete is meant to be permanent, so it is never undoable.

**What about `DissociateAction`?**
Not covered by a built-in strategy. Write a [custom strategy](#custom-undo-strategies) that keeps the foreign key value in the snapshot and writes it back on undo.

**Does undo fire model events?**
Soft-delete restore fires `restoring` / `restored`. Restore undo fires `deleting` / `deleted`. Edit undo saves the model, so `saving`, `updating`, `updated` and `saved` fire. Hard-delete undo fires `saving`, `creating`, `created` and `saved` for the parent row; restored relation rows are written with the query builder and fire no events. Detach undo uses `attach()`.

**My `undoable()` closure throws a serialisation error.**
The closure captured something that cannot be serialised, such as a Livewire component through `$this`. Use `static fn` and resolve what you need inside the closure.

**Does it work with tenancy?**
Yes. Entries are tied to the user and panel. The undo looks records up by primary key without global scopes, so authorize cross-tenant concerns in `authorizeUndoUsing()` if your app needs it.

## Testing

The package itself:

```bash
composer test        # Pest
composer analyse     # PHPStan, level 6
composer format      # Pint
npm run test:e2e     # Playwright, in a real browser
```

Run Pest in parallel for a faster run:

```bash
vendor/bin/pest --parallel
```

The end-to-end suite boots the Testbench workbench in `workbench/`: a panel with a soft-delete customer resource, a hard-delete project resource with a tags relation manager, a tag resource with modal editing, and seeded data. It serves the workbench with `vendor/bin/testbench serve` on a free port and drives it with Chromium. It covers delete and Undo, bulk delete and `mod+z`, edits in a modal, detach, hard delete with relations, deleting from an Edit page, the replaced Filament notification, Arabic right-to-left, dark mode, a 375 pixel phone and reduced motion. The database is reset before every test.

```bash
npm ci
npm run build
npx playwright install chromium   # or point PLAYWRIGHT_CHROMIUM_EXECUTABLE at a Chromium you already have
npm run test:e2e
```

`composer serve` starts the same workbench at `/admin` if you want to click around. Add `?locale=ar`, `fr`, `de` or `ja` to a URL to switch language. `UT_SCREENSHOTS=1 npm run test:e2e -- screenshots` retakes the images in this README.

In your own app's tests, the undo is a plain Livewire method on `HoceineEl\UndoToast\Livewire\UndoToast`. Call an action, read the token from the `undo-toast-push` event, and undo it:

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

## Contributing

Contributions are welcome. Please open an issue first for larger changes.

1. Fork [the repository](https://github.com/HoceineEl/filament-undo-toast) and create a branch.
2. Run `composer install` and `npm ci`.
3. After changing `resources/js` or `resources/css`, run `npm run build` to rebuild `dist/`.
4. Add or update tests, then run `vendor/bin/pest --parallel`, `composer analyse`, `composer format` and, for UI changes, `npm run test:e2e`.

To add a language, copy `resources/lang/en/undo-toast.php` into a folder named with Filament's locale code (the folder names in `vendor/filament/actions/resources/lang`), translate it using the same words Filament uses for its own actions, add the code to `TRANSLATION_LOCALES` in `tests/Unit/TranslationsTest.php`, and run the tests. The suite checks keys, placeholders and plural forms for every locale.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for what changed in each release.

## Security vulnerabilities

Please do not open a public issue for a security problem. Report it privately as described in [SECURITY.md](SECURITY.md).

## Credits

- [Hoceine El Idrissi](https://github.com/HoceineEl)
- Built on [Filament](https://filamentphp.com) and [Livewire](https://livewire.laravel.com).
- From the same author: [Quick Action Dock](https://github.com/HoceineEl/filament-quick-action-dock), a floating launcher for the actions people use most, and [Keyboard Shortcuts](https://github.com/HoceineEl/filament-keyboard-shortcuts), a `?` cheat sheet that lists every shortcut on the page, including `mod+z`.

## License

The MIT License. See [LICENSE.md](LICENSE.md).
