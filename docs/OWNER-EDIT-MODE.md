# Owner Edit Mode (Elementor Native)

## Purpose
Owner Edit Mode replaces core mock pages with a stricter Elementor-native structure so a non-technical owner can drag, drop, and edit content directly.

## Core Pages Covered
- `home`
- `company`
- `our-curriculum`
- `faq`
- `contact-us`
- `academies`

## How To Switch Modes
1. In WordPress Admin, open `Tools > Kiddie Mock Seed`.
2. To use drag-and-drop native editing, click `Run Owner Edit Mode Seed`.
3. To return to reference-layout parity mode, click `Run Full Mock Seed`.

The active mode is shown at the top of the tools page as:
- `owner-edit`
- `mock-parity`

## What Becomes Native
In Owner Edit Mode, core pages use Elementor native widgets:
- `heading`
- `text-editor`
- `image`
- `button`
- `icon-list`
- `accordion`
- `shortcode`

Core pages intentionally avoid `HTML` widgets in this mode for easier owner editing.

## Notes
- Owner Edit Mode uses plugin stylesheet: `kiddie-mock-seed/assets/css/owner-edit-mode.css`.
- Frontend body class for this mode: `kms-owner-mode`.
- If you do not see updates immediately, run `Elementor > Tools > Regenerate CSS & Data`, then hard refresh.
