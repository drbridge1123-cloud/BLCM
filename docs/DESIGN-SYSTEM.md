# BLCM Design System

> Complete visual design reference for the BLCM application.

---

## 1. Color Palette

### CSS Variables (app.css :root)
| Variable | Value | Usage |
|----------|-------|-------|
| `--bg` | `#F0F2F5` | Page background |
| `--card` | `#FFFFFF` | Card background |
| `--card-border` | `#E5E5E0` | Card borders |
| `--card-bg` | `#F5F5F0` | Card inner backgrounds |
| `--sidebar` | `#161616` | Sidebar background |
| `--sidebar-light` | `rgba(255,255,255,.05)` | Sidebar hover |
| `--sidebar-border` | `rgba(255,255,255,.07)` | Sidebar borders |
| `--gold` | `#C9A84C` | Primary brand gold |
| `--gold-hover` | `#B8973F` | Gold hover state |
| `--gold-light` | `#E8D5A0` | Light gold accent |
| `--gold-pale` | `rgba(201,168,76,0.10)` | Gold tint background |
| `--text` | `#0F1B2D` | Primary text (navy) |
| `--text-mid` | `#3D4F63` | Secondary text |
| `--text-light` | `#5A6B82` | Muted text |
| `--off-white` | `#fdfdfb` | Off-white backgrounds |
| `--border` | `#d0cdc5` | General borders |
| `--border-soft` | `#f0ede6` | Subtle borders |
| `--muted` | `#8a8a82` | Muted/label text |

### Tailwind Extended Colors (main.php config)
```javascript
navy:    { DEFAULT: '#0F1B2D', light: '#1A2A40', border: '#243347' }
gold:    { DEFAULT: '#C9A84C', hover: '#B8973F' }
'v2-bg':          '#FFFFFF'
'v2-card':        '#FFFFFF'
'v2-card-border': '#E5E5E0'
'v2-card-bg':     '#F0F2F5'
'v2-text':        '#0F1B2D'
'v2-text-mid':    '#3D4F63'
'v2-text-light':  '#5A6B82'
```

### Status Colors (Tailwind classes from utils.js)
| Status | Background | Text |
|--------|-----------|------|
| prelitigation | `bg-teal-100` | `text-teal-700` |
| collecting | `bg-blue-100` | `text-blue-700` |
| verification | `bg-yellow-100` | `text-yellow-700` |
| completed | `bg-green-100` | `text-green-700` |
| rfd | `bg-purple-100` | `text-purple-700` |
| final_verification | `bg-orange-100` | `text-orange-700` |
| disbursement | `bg-indigo-100` | `text-indigo-700` |
| accounting | `bg-pink-100` | `text-pink-700` |
| closed | `bg-gray-100` | `text-gray-500` |

### Provider Status Colors
| Status | Background | Text |
|--------|-----------|------|
| treating | `bg-gray-100` | `text-gray-600` |
| not_started | `bg-gray-100` | `text-gray-500` |
| requesting | `bg-blue-100` | `text-blue-700` |
| follow_up | `bg-yellow-100` | `text-yellow-700` |
| action_needed | `bg-red-100` | `text-red-700` |
| received_partial | `bg-orange-100` | `text-orange-700` |
| on_hold | `bg-gray-200` | `text-gray-600` |
| no_records | `bg-gray-200` | `text-gray-500` |
| received_complete | `bg-green-100` | `text-green-700` |
| verified | `bg-emerald-100` | `text-emerald-700` |

### Toast Colors
| Type | Background Class |
|------|-----------------|
| success | `bg-green-500` |
| error | `bg-red-500` |
| warning | `bg-yellow-500` |
| info | `bg-blue-500` |

---

## 2. Typography

### Font Families
| Font | Weight | Usage | Class |
|------|--------|-------|-------|
| Libre Franklin | 400-900 | Primary UI font | `font-franklin` |
| IBM Plex Mono | 400, 500 | Case numbers, monospace data | `.sp-col-mono` |
| IBM Plex Sans | 300-600 | Secondary sans-serif | Direct usage |

### Text Sizes (commonly used)
| Size | Usage |
|------|-------|
| `9.5px` | Form labels (uppercase) |
| `10px` | Metadata, timestamps |
| `11px` | Section headers, eyebrow text |
| `12px` | Table cells, small UI text |
| `13px` | Regular body text in tables |
| `14px` | Input fields, button text |
| `15px` | Modal titles, card headers |
| `16px-18px` | Page section headers |
| `22px` | Stat card values |

---

## 3. sp-design-system.css Classes (Complete)

### Card Container
| Class | Description |
|-------|-------------|
| `.sp-card` | White card with border, rounded corners, gold 3px top border |
| `.sp-card-gold-top` | (Built into `.sp-card`) Gold border-top |

### Headers
| Class | Description |
|-------|-------------|
| `.sp-eyebrow` | Uppercase 10px muted label text (letter-spacing .08em) |
| `.sp-card-header` | Card header area with padding 20px 24px, flex between |

### Stat Cards
| Class | Description |
|-------|-------------|
| `.sp-stats-group` | Flex container with gap 16px for stat cards |
| `.sp-stat-card` | Individual stat card with left gold border, padding |
| `.sp-stat-value` | Large number value (22px, bold, navy) |
| `.sp-stat-label` | Small muted label below value (11px, uppercase) |

### Toolbar & Tabs
| Class | Description |
|-------|-------------|
| `.sp-toolbar` | Flex container between tabs and search/actions (sticky) |
| `.sp-tabs` | Flex container for tab buttons (gap 2px) |
| `.sp-tab` | Tab button (12px, 500 weight, muted color) |
| `.sp-tab.active` | Active tab (navy text, gold bottom border, bold) |

### Search & Select
| Class | Description |
|-------|-------------|
| `.sp-search` | Text input with border, rounded 6px, 13px text, padding 7px 12px |
| `.sp-select` | Select dropdown, same styling as `.sp-search` |

### Buttons
| Class | Description |
|-------|-------------|
| `.sp-new-btn` | Gold background button (#C9A84C), white text, rounded 6px, 11px uppercase |
| `.sp-new-btn:hover` | Darker gold (#B8973F) |
| `.sp-new-btn-navy` | Navy background button (#0F1B2D), white text |

### Table
| Class | Description |
|-------|-------------|
| `.sp-table` | Full-width table, border-collapse, border-spacing 0 |
| `.sp-table-compact` | Compact row height variant |
| `.sp-table th` | Gold background header (#C9A84C), white text, 10px uppercase, padding 10px 14px |
| `.sp-table td` | Table cell, 13px, padding 10px 14px, border-bottom |
| `.sp-table tr:hover` | Row hover background (#FAFAF8) |

### Column Helpers
| Class | Description |
|-------|-------------|
| `.sp-col-case-number` | Monospace, 13px, bold, navy |
| `.sp-col-client` | 13px, 600 weight, navy |
| `.sp-col-mono` | IBM Plex Mono, 12px |
| `.sp-col-commission-value` | Monospace, right-aligned, tabular-nums |
| `.sp-col-commission-amount` | Monospace, bold, navy |
| `.sp-col-date` | 12px, muted color |
| `.sp-col-deadline` | 12px with color states |
| `.sp-col-deadline-badge` | Small badge for deadline status |
| `.sp-col-duration` | Duration display |
| `.sp-col-duration-value` | Bold duration number |

### D-N-T-S Progress Dots
| Class | Description |
|-------|-------------|
| `.sp-dots-group` | Flex container for progress dots (gap 6px) |
| `.sp-dot` | Circle dot (20px, gray border, centered) |
| `.sp-dot-label` | Letter label inside dot (9px, bold) |
| `.sp-dot.done` | Completed state: gold background, white text |
| `.sp-dot.active` | Active state: gold border, gold text |

### Stage & Phase Badges
| Class | Description |
|-------|-------------|
| `.sp-stage` | Pill badge with colored background per stage |
| `.sp-phase` | Phase indicator badge |

### Status Badge
| Class | Description |
|-------|-------------|
| `.sp-status` | Small status pill (10px, uppercase, rounded-full) |

### Checkbox
| Class | Description |
|-------|-------------|
| `.sp-checkbox` | Checkbox wrapper (flex, gap 8px, cursor pointer) |
| `.sp-checkbox-box` | Checkbox visual box (18px, border, rounded 4px) |
| `.sp-checkbox-box.checked` | Checked state: gold background, white checkmark |

### Action Buttons
| Class | Description |
|-------|-------------|
| `.sp-actions` | Actions cell container (flex, gap 4px) |
| `.sp-action-btn` | Base action button (12px, padding 4px 10px, rounded) |
| `.sp-action-btn-edit` | Blue edit button |
| `.sp-action-btn-delete` | Red delete button |
| `.sp-action-btn-view` | Navy view button |

### Pagination
| Class | Description |
|-------|-------------|
| `.sp-pagination` | Pagination container (flex, centered, gap 4px) |
| `.sp-page-btn` | Page number button (32px, rounded 6px, border) |
| `.sp-page-btn.active` | Active page: gold background, white text |
| `.sp-page-btn:hover` | Hover: light gold background |
| `.sp-page-btn:disabled` | Disabled state: reduced opacity |

### Staff Pills
| Class | Description |
|-------|-------------|
| `.sp-staff-pills` | Flex container for staff filter pills |
| `.sp-staff-pill` | Individual pill (12px, rounded-full, border) |
| `.sp-staff-pill.active` | Active: navy background, white text |

### Loading & Empty States
| Class | Description |
|-------|-------------|
| `.sp-loading` | Loading text (center, muted, padding 40px) |
| `.sp-empty` | Empty state text (center, muted, padding 40px) |

### Gold Header Override
Overrides Tailwind table headers with gold background for tables using `<table>` inside `.sp-card`.

---

## 4. app.css Classes (Complete by Section)

### Layout (lines 27-107)
| Class | Description |
|-------|-------------|
| `.sidebar` | Fixed left sidebar (width 220px, height 100vh, z-30) |
| `.sidebar.collapsed` | Collapsed state (width 56px) |
| `.sidebar-text` | Text that hides when collapsed |
| `.main-content` | Main area with margin-left 220px, transition |
| `.main-content.expanded` | When sidebar collapsed: margin-left 56px |
| `.header-bar` | Top header bar |
| `.v2-page-title` | Page title (18px, 800 weight, navy) |

### Sidebar Navigation (lines 80-107)
| Class | Description |
|-------|-------------|
| `.sb-nav` | Navigation container (padding 8px) |
| `.sb-item` | Nav item link (flex, gap 10px, padding 8px, rounded 8px) |
| `.sb-item.active` | Active state: gold-pale background, gold text |
| `.sb-item:hover` | Hover: sidebar-light background |
| `.sb-icon` | SVG icon (18px, stroke, no fill) |
| `.sb-label` | Nav item text label |
| `.sb-section-label` | Section divider label (9px, uppercase, muted) |
| `.sb-badge` | Badge inside nav item |
| `.sb-badge-red` | Red notification badge |

### Buttons (lines 110-220)
| Class | Description |
|-------|-------------|
| `.btn-icon` | Icon-only button (32px square, rounded, transparent) |
| `.v2-btn` | Base button (13px, 600 weight, padding 8px 16px, rounded 6px) |
| `.v2-btn-primary` | Gold background, navy text |
| `.v2-btn-danger` | Red background, white text |
| `.v2-btn-sm` | Small variant (12px, padding 6px 12px) |
| `.v2-btn-ghost` | Transparent with border |

### Badges (lines 223-320)
| Class | Description |
|-------|-------------|
| `.badge-status-*` | Per-status colored badges |
| `.difficulty-easy/medium/hard` | Difficulty level badges |
| `.escalation-tier-*` | Escalation tier indicators |

### Modal Styles (lines 565-763)
| Class | Description |
|-------|-------------|
| `.modal-v2-overlay` | Fixed overlay with backdrop |
| `.modal-v2-container` | Modal dialog container |
| `.modal-v2-header` | Navy header with title |
| `.modal-v2-body` | Scrollable body area |
| `.modal-v2-footer` | Footer with action buttons |
| `.modal-dark-*` | Dark-themed modal variant |

### Form Styles (lines 766-874)
| Class | Description |
|-------|-------------|
| `.form-v2-group` | Form field group with label + input |
| `.form-v2-label` | Form label (9.5px, uppercase, muted) |
| `.form-v2-input` | Text input styling |
| `.form-v2-select` | Select dropdown styling |
| `.form-v2-textarea` | Textarea styling |
| `.form-v2-accordion` | Collapsible form section |

### Panels (lines 927-1022)
| Class | Description |
|-------|-------------|
| `.collapsible-panel` | Panel with expand/collapse |
| `.collapsible-panel-header` | Panel header (clickable) |
| `.collapsible-panel-body` | Panel content area |

### Case Detail Page (lines 1037-3277)
Specialized styles for:
- Case header with hero section
- Pipeline stage indicators
- Provider cards and status
- Activity timeline
- Document management
- Cost/payment tables
- MBR (Medical Balance Report) tables
- Negotiation interface
- Disbursement calculations

---

## 5. Common Inline Style Patterns

These styles are used across 29+ modal files and 100+ form instances. They should eventually be extracted to CSS classes.

### Modal Overlay
```css
background: rgba(0,0,0,.45);
```

### Modal Dialog Container
```css
background: #fff;
border-radius: 12px;
box-shadow: 0 24px 64px rgba(0,0,0,.22);
width: 100%;
max-width: 600px;        /* Standard */
/* max-width: 440px;     /* Small */
/* max-width: 800px;     /* Large */
/* max-width: 95vw;      /* Extra large (preview) */
max-height: 90vh;
overflow: hidden;
display: flex;
flex-direction: column;
```

### Modal Header (Navy)
```css
background: #0F1B2D;
padding: 18px 24px;
display: flex;
align-items: center;
justify-content: space-between;
flex-shrink: 0;
```

### Modal Title
```css
font-size: 15px;
font-weight: 700;
color: #fff;
margin: 0;
```

### Modal Close Button
```css
background: none;
border: none;
color: rgba(255,255,255,.4);
cursor: pointer;
font-size: 20px;
```

### Modal Body
```css
padding: 24px;
overflow-y: auto;
display: flex;
flex-direction: column;
gap: 16px;
```

### Modal Footer
```css
padding: 16px 24px;
border-top: 1px solid #eee;
display: flex;
justify-content: flex-end;
gap: 8px;
flex-shrink: 0;
```

### Form Label (500+ occurrences)
```css
display: block;
font-size: 9.5px;
font-weight: 700;
color: #8a8a82;
text-transform: uppercase;
letter-spacing: .08em;
margin-bottom: 5px;
```

### Form Two-Column Grid
```css
display: grid;
grid-template-columns: 1fr 1fr;
gap: 16px;
```

### Form Section Divider
```css
font-size: 11px;
font-weight: 700;
color: #0F1B2D;
text-transform: uppercase;
letter-spacing: .05em;
padding-bottom: 8px;
border-bottom: 2px solid #C9A84C;
margin-bottom: 12px;
```

### Currency Input Prefix ($)
```css
/* Wrapper */
position: relative;

/* $ sign */
position: absolute;
left: 10px;
top: 50%;
transform: translateY(-50%);
color: #8a8a82;
font-size: 13px;

/* Input */
padding-left: 24px;
```

---

## 6. Responsive Behavior

- Sidebar collapses to 56px width on toggle
- Main content adjusts margin-left accordingly
- "BRIDGE LAW & ASSOCIATES" badge hidden on small screens (`hidden sm:inline-flex`)
- Tables overflow horizontally on small screens
- Modals max-height 90vh with scroll
- No explicit mobile breakpoints (desktop-first application)
