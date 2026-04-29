---
name: expense-tracker-ui-designer
description: Designs and generates modern, production-ready UI for the Expense Tracker, a personal finance app built on CodeIgniter 4 with PHP views, Bootstrap 5 CDN, plain CSS, and vanilla JS. Creates polished fintech-styled pages, components, and layouts consistent with the navy (#1e3a5f) and gold (#f5a623) design language. Use this skill whenever the user asks to design, build, create, redesign, improve, or style any page, screen, section, or component.
disable-model-invocation: false
---

# Expense Tracker UI Designer

You are designing frontend UI for the **Expense Tracker**, a personal finance app built on CodeIgniter 4 (PHP). Output polished, fintech-styled PHP views, plain CSS, and vanilla JavaScript that follow the navy and gold design language.

## Stack Context

| Layer | Technology | Notes |
|-------|-----------|-------|
| **Backend** | CodeIgniter 4 | PHP 8.2+, MVC pattern |
| **Views** | PHP `.php` files | Always extend a shared layout via `$this->extend()` |
| **Markup** | HTML 5 | Semantic tags (`<header>`, `<nav>`, `<main>`, `<section>`, `<footer>`) |
| **Styles** | Plain CSS 3 | `public/assets/css/` — no Tailwind, no SCSS preprocessors |
| **Scripts** | Vanilla JavaScript (ES6+) | `public/assets/js/` — no jQuery, no React/Vue/Angular |
| **UI Framework** | Bootstrap 5.3 CDN | Use utilities, override defaults with `.et-` prefix |
| **Icons** | Lucide Icons CDN | `<i data-lucide="icon-name"></i>` |
| **Charts** | Chart.js CDN | For dashboards and data visualization |
| **Font** | Poppins (Google Fonts CDN) | 400 (body), 500 (UI), 600 (subheadings), 700 (headings) |

## CI4 View Conventions

```php
<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<!-- page content here -->

<?= $this->endSection() ?>
```

- Always use `$this->extend()` and `$this->section()` — never standalone HTML files
- Use `<?= esc($variable) ?>` for all user-supplied output (XSS prevention)
- Use `site_url('path')` or `base_url('path')` for all links and asset paths
- Form actions: `<?= site_url('expense/store') ?>`
- CSRF token in every form: `<?= csrf_field() ?>`

## Pre-Design Checklist

Before generating new UI, always:

1. **Review Existing Conventions**
   - Check `public/assets/css/` for existing color tokens, spacing scales, and component patterns
   - Look for `.et-` prefixed classes already in use
   - Identify established component library (buttons, cards, forms, tables)

2. **Verify Color Palette**
   - Use project's defined primary color (navy `#1e3a5f`)
   - Use project's defined accent color (gold `#f5a623`)
   - Check for semantic colors (success, error, info, warning)

3. **Check Responsive Breakpoints**
   - Confirm mobile breakpoints (< 640px, 640–1024px, ≥ 1024px)
   - Verify sidebar collapse behavior on tablet/mobile

4. **Examine Existing Pages**
   - Look at `app/Views/layouts/` for the shared layout structure
   - Check existing views for established patterns

## Design Language

### Color Palette
- **Primary Background:** `#1e3a5f` (deep navy) — headers, sidebars, hero sections
- **Secondary Background:** `#f7f8fa` (light gray) — page backgrounds, content areas
- **Surface:** `#ffffff` (white) — cards, modals, forms
- **Accent:** `#f5a623` (gold) — buttons, active states, highlights, stat numbers
- **Text Primary:** `#111827` (dark gray) — body text, headings
- **Text Secondary:** `#6b7280` (medium gray) — labels, helper text, muted info
- **Border:** `#e5e7eb` (light gray) — dividers, card borders
- **Success:** `#10b981` (emerald) — confirmations, positive indicators
- **Error:** `#ef4444` (red) — warnings, destructive actions, validation errors
- **Info:** `#3b82f6` (blue) — informational messages
- **Warning:** `#f59e0b` (amber) — caution, pending states

### Typography
- **Font Family:** Poppins (Google Fonts CDN)
- **Body:** 400 weight, 16px base, 1.5 line-height
- **Headings:** 600 weight (h4–h6) or 700 weight (h1–h3)
- **UI Text:** 500 weight (buttons, labels, navigation)
- **Size Scale:** 12px, 14px, 16px, 18px, 20px, 24px, 32px, 40px
- **Currency Display:** Always `font-variant-numeric: tabular-nums`, prefix with "PKR "

### Spacing & Layout
- **Grid Unit:** 8px baseline — multiples only (4px, 8px, 16px, 24px, 32px, 40px, 48px)
- **Card Padding:** 24px
- **Input Height:** 40px
- **Button Padding:** 10px 24px (primary), 8px 22px (secondary)
- **Border Radius:** 12px (cards, large buttons), 8px (inputs, smaller buttons), 4px (very small)

### Component Defaults

**Buttons:**
- Primary (gold, filled): `background: #f5a623`, `color: white`, hover: `#e09517`
- Secondary (navy, outline): `border: 2px solid #1e3a5f`, `color: #1e3a5f`, hover: navy background
- Danger (red, filled): `background: #ef4444`, `color: white`, hover: `#dc2626`

**Cards:**
- White background, `border: 1px solid #e5e7eb`, `border-radius: 12px`, padding: 24px
- Shadow: `0 1px 3px rgba(0,0,0,0.05)`, hover: `0 4px 6px rgba(0,0,0,0.08)`

**Forms:**
- Input height: 40px, padding: 10px 12px, border: `1px solid #e5e7eb`
- Focus: border gold, shadow `0 0 0 3px rgba(245,166,35,0.1)`
- Invalid: border `#ef4444`
- Labels: 500 weight, 14px, margin-bottom: 6px

**Tables:**
- Header background: `#f7f8fa`, border-bottom: `2px solid #e5e7eb`
- Row padding: 12px 16px, border-bottom: `1px solid #e5e7eb`
- Hover: background `#f9fafb`
- Always wrap in `overflow-x: auto` for mobile

### Layout Architecture

**Public Pages (Home, Login, Register):**
- Sticky navy header with logo (gold text) and nav
- Full-width hero with navy background, centered white content, gold CTA
- Light gray content areas with white cards
- Navy footer

**Authenticated Pages (Dashboard, Expenses, Settings):**
- Sticky navy header with logo and user menu
- Left sidebar (280px, navy, gold active state) — collapses to offcanvas on mobile
- Main content area with light gray background

**Responsive Breakpoints:**
- Mobile: < 640px (vertical stack, hamburger nav)
- Tablet: 640px–1024px (sidebar collapses to offcanvas)
- Desktop: ≥ 1024px (sidebar visible, full layout)

## CSS Class Reference

| Class | Purpose |
|-------|---------|
| `.et-container` | Main page wrapper |
| `.et-header` | Sticky navigation bar |
| `.et-sidebar` | Left navigation sidebar |
| `.et-content` | Main content area |
| `.et-card` | Content card (white, bordered, shadowed) |
| `.et-btn-primary` | Gold filled button |
| `.et-btn-secondary` | Navy outline button |
| `.et-btn-danger` | Red filled button |
| `.et-form-group` | Form field wrapper |
| `.et-input` | Text input |
| `.et-select` | Dropdown select |
| `.et-textarea` | Multiline input |
| `.et-table` | Data table |
| `.et-alert` | Alert/notification box |
| `.et-stat-card` | Dashboard stat display |
| `.et-stat-value` | Large currency number (gold, tabular-nums) |
| `.et-stat-label` | Stat label (gray, uppercase) |
| `.et-invalid-feedback` | Validation error text (red, 12px) |

## Anti-Patterns

- Default Bootstrap blue — override all primary colors with navy/gold
- Hardcoded URLs — always use `site_url()` or `base_url()`
- Raw output without `esc()` — always escape user data
- Forms without `<?= csrf_field() ?>` — always include CSRF token
- Arbitrary spacing — multiples of 4px/8px only
- More than one accent color — gold for CTAs only
- Tables without responsive wrapper
- Inline styles — all styles in `public/assets/css/`
- jQuery or heavy JS frameworks — vanilla ES6+ only

## CDN Dependencies (place in layout `<head>`)

```html
<!-- Google Fonts - Poppins -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Bootstrap 5.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Lucide Icons -->
<script src="https://cdn.jsdelivr.net/npm/lucide@latest"></script>

<!-- Chart.js (optional, for dashboards) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
```

Before closing `</body>`:

```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
</script>
```
