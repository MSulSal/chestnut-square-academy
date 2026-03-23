# Native Parity Mode

## Goal
Keep the strongest visual/layout parity while staying fully editable in Elementor at the widget level.

## How To Run
1. Open `Tools > CSA Site Tools` in WordPress Admin.
2. Click `Run Native Parity Seed`.
3. Keep `Overwrite existing page content` enabled when doing a full reset.

## What This Mode Does
- Seeds core pages with native Elementor widgets (`heading`, `text-editor`, `image`, `button`, containers).
- Avoids HTML-widget-only sections so owners can edit content without code.
- Applies parity helper CSS/JS on the frontend to preserve layout behavior.
- Automatically suppresses parity helper JS inside Elementor editor contexts to protect drag-and-drop editing.

## Tradeoff
Exact clone-level behavior can still vary because Elementor and third-party source implementations differ. This mode is the best balance for real-world no-code owner editing plus close visual parity.
