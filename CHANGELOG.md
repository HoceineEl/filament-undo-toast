# Changelog

## 0.2.0 - 2026-10-08

- The toast now replaces Filament's own success notification for the same action, so a delete no longer says "Deleted" twice. Notifications you customised with `successNotification()` or `successNotificationTitle()` are kept, and Filament's notification still shows whenever no undo entry is created. Turn it off with `->replaceSuccessNotification(false)` or decide per action with a closure.
- Undoing a hard delete can restore related rows: implement `HasUndoableRelations` on the model or use `->restoreRelations([Model::class => [...]])`. Supports `BelongsToMany` (pivot rows with their pivot columns), `HasMany`, `HasOne`, `MorphMany` and `MorphOne`, inserting only rows that are missing, in the same transaction.
- Default English model labels are translated from `undo-toast::undo-toast.models.{Model}` or the app's own translation of the label.
- `mod+z` now works while a checkbox, radio or button has focus, such as right after selecting rows for a bulk action.
- Toast polish from a design review: the progress line sits inside the rounded corners, stacked toasts keep the front toast's height and tint instead of being clipped (no `clip-path` animation), Undo lines up across an expanded stack, the spinner keeps the accent colour while undoing, a success stays long enough to read, a failed undo stays until dismissed and is announced as an alert, tapping a stacked toast fans the stack out on touch screens, the stack no longer collapses when the pointer crosses the gap between toasts, a stronger dark-mode shadow, Windows High Contrast support, and `will-change` only while a toast moves.
- Testbench workbench app and a Playwright end-to-end suite (`npm run test:e2e`), run in CI.
- Translations for 20 more languages, 23 in all: Arabic, Chinese (Simplified and Traditional), Czech, Dutch, English, French, German, Hebrew, Hindi, Indonesian, Italian, Japanese, Korean, Persian, Polish, Portuguese (Portugal and Brazil), Russian, Spanish, Turkish, Ukrainian and Vietnamese. Each reuses Filament's own terms for that language, and plurals are correct for every count, including Russian, Ukrainian, Polish and Czech forms such as 21, 22 and 25. A test checks keys, placeholders and plural rendering in every locale.
- Clearer Arabic copy, and French uses Filament's "détacher" for detach.
- `SECURITY.md`, Dependabot with a cooldown, GitHub Actions pinned to commit SHAs, and a leaner dist archive.

## 0.1.0 - 2026-10-07

- Undo toasts for `DeleteAction`, `DeleteBulkAction`, `RestoreAction`, `RestoreBulkAction`, `EditAction`, `DetachAction` and `DetachBulkAction`, hooked through Filament's `ActionCalling` and `ActionCalled` events so your own `before()` and `after()` callbacks keep working.
- Soft-delete restore, hard-delete re-insert with original keys (`->hardDeletes(false)` to turn off), column-level edit revert, detach re-attach with pivot data.
- `->undoable()` macro for any action, and custom `UndoStrategy` classes.
- Encrypted, one-time cache entries scoped to the user and panel, undone in a database transaction, with `->authorizeUndoUsing()`.
- Toast stack with countdown, pause on hover, focus and hidden tab, `mod+z`, redirect survival, record link after undo.
- Light and dark, RTL, English, French and Arabic.
