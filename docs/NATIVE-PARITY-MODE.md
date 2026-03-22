# Native Parity Mode

## Goal
Use fully native Elementor widgets while keeping Kiddie Academy structure/content parity as close as possible.

## How To Run
1. Open `Tools > Kiddie Mock Seed` in WordPress Admin.
2. Click `Run Native Parity Seed`.
3. Keep `Overwrite existing page content` enabled for a full reset pass.

## What This Mode Does
- Seeds the Kiddie page tree using native Elementor widgets (`text-editor`, `heading`, `image`, `button`, plus nested containers).
- Avoids `HTML` widget fallback so content is component-level editable in Elementor.
- Applies a native-parity frontend helper script (`assets/js/native-parity-front.js`) and CSS (`assets/css/native-parity-mode.css`).
- Preserves closer frontend DOM parity by unwrapping Elementor widget wrappers on public pages while keeping widgets fully editable in Elementor editor.
- Auto-disables parity JS in Elementor editor/preview contexts to avoid widget drag/drop conflicts.

## Important Tradeoff
100% clone-level parity is still constrained by Elementor rendering internals and source-site private assets/scripts.
This mode is intended to maximize practical visual/structural parity while preserving full native editing.
