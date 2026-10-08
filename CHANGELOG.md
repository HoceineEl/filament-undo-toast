# Changelog

## 0.2.0 - 2026-10-08

- The toast replaces Filament's own success notification for the same action, so a delete no longer says "Deleted" twice. Notifications customised with `successNotification()` or `successNotificationTitle()` are kept, and Filament's notification still shows when no undo entry is created. Turn it off with `->replaceSuccessNotification(false)`, or decide per action with a closure.
- Undoing a hard delete can restore related rows. Implement `HasUndoableRelations` on the model or use `->restoreRelations([Model::class => [...]])`. Supports `BelongsToMany` (with pivot columns), `HasMany`, `HasOne`, `MorphMany` and `MorphOne`; only missing rows are inserted, in the same transaction.
- Default English model labels are translated from `undo-toast::undo-toast.models.{Model}` or the app's own translation of the label.
- `mod+z` works while a checkbox, radio or button has focus, such as right after selecting rows for a bulk action.
- Toast fixes: the progress line sits inside the rounded corners; stacked toasts keep the front toast's height and tint instead of being clipped (no `clip-path` animation); Undo lines up across an expanded stack; the spinner keeps the accent color; a success stays long enough to read; a failed undo stays until dismissed and is announced as an alert; tapping a stacked toast fans the stack out on touch screens; the stack no longer collapses when the pointer crosses the gap between toasts. Also a stronger dark-mode shadow, Windows High Contrast support, and `will-change` only while a toast moves.
- Testbench workbench app and a Playwright end-to-end suite (`npm run test:e2e`), run in CI.
- 20 more languages, 23 in all: Arabic, Chinese (Simplified and Traditional), Czech, Dutch, English, French, German, Hebrew, Hindi, Indonesian, Italian, Japanese, Korean, Persian, Polish, Portuguese (Portugal and Brazil), Russian, Spanish, Turkish, Ukrainian and Vietnamese. Each uses Filament's own terms for that language, and plurals are correct for every count, including the Russian, Ukrainian, Polish and Czech forms for 21, 22 and 25. A test checks keys, placeholders and plural rendering in every locale.
- Clearer Arabic copy. French uses Filament's "détacher" for detach.
- `SECURITY.md`, Dependabot with a cooldown, GitHub Actions pinned to commit SHAs, and a smaller dist archive.

## 0.1.0 - 2026-10-07

- Undo toasts for `DeleteAction`, `DeleteBulkAction`, `RestoreAction`, `RestoreBulkAction`, `EditAction`, `DetachAction` and `DetachBulkAction`, hooked through Filament's `ActionCalling` and `ActionCalled` events so your `before()` and `after()` callbacks keep working.
- Soft-delete restore, hard-delete re-insert with original keys (`->hardDeletes(false)` turns it off), column-level edit revert, and detach re-attach with pivot data.
- `->undoable()` macro for any action, and custom `UndoStrategy` classes.
- Encrypted, single-use cache entries scoped to the user and panel, undone in a database transaction, with `->authorizeUndoUsing()`.
- Toast stack with a countdown that pauses on hover, focus and hidden tab, `mod+z`, toasts that survive redirects, and a record link after undo.
- Light and dark mode, RTL, and English, French and Arabic translations.
