# Frontend dependency upgrade — Vue 3.2 → 3.5 + all latest packages

## Context

`package.json` has drifted ~3 years: Vue 3.2.37 (latest 3.5.x), Vite 3 (latest 6/7), CKEditor 35 (latest 45+; classic build *removed* in 5.50+), Bootstrap 5.2.3 (latest 5.3.x), Chart.js 3 / FullCalendar 5 / TipTap beta / dropzone — **all installed but never imported** — plus `laravel-mix` and `vue-loader` from a pre-Vite era. A stale `webpack.mix.js` still sits next to `vite.config.js`.

Purpose of this work: bring every active package to current, drop dead weight, and migrate CKEditor off the deprecated build path — without touching application logic. Audit confirms the codebase is well-positioned: 96.6% of Vue components already use `<script setup>`, no removed/deprecated Vue APIs anywhere, Pinia/Router/Bootstrap usage is all standard.

The companion Laravel 13 + PHP 8.4 upgrade lives in `docs/laravel-latest-migration.md` and is **independent** of this plan; do not bundle.

## Decisions (already confirmed with user)

| Decision | Choice |
|---|---|
| Unused packages (chart.js, vue-chart-3, FullCalendar, TipTap, dropzone, laravel-mix, vue-loader) | **Remove** |
| `webpack.mix.js` | **Delete** |
| CKEditor migration | **Include** — migrate to unified `ckeditor5` package |
| Vite target | **Vite 6** (Node floor 18+; Dockerfile already on `node:20-alpine`) |
| Pinia | Hold at **2.3.x** (do not jump to 3) |
| ESLint / Prettier | **Skip** — out of scope for this upgrade |
| Sass | Stay on `sass` (defer `sass-embedded` evaluation post-upgrade) |

## Strategy: 7 commits, each independently verifiable

Each phase is one commit so any regression bisects cleanly.

---

### Phase 0 — Prune dead weight (zero risk)

**Remove from `package.json`:**
- `laravel-mix`, `vue-loader` (webpack-era; Vite uses `@vitejs/plugin-vue`)
- `chart.js`, `vue-chart-3` (no imports anywhere)
- `@fullcalendar/core`, `@fullcalendar/daygrid`, `@fullcalendar/interaction`, `@fullcalendar/list`, `@fullcalendar/timegrid`, `@fullcalendar/vue3` (no imports)
- `@tiptap/starter-kit`, `@tiptap/vue-3` (no imports)
- `dropzone` (only `_dropzone.scss` references it; no JS usage)
- All four `@ckeditor/ckeditor5-build-*` (replaced in Phase 5)
- Duplicate `@popperjs/core` from `dependencies` (keep in `devDependencies`)

**Remove from `package.json` scripts** (mix-era, unused): `development`, `watch`, `watch-poll`, `hot`, `prod`, `production`.

**Delete file:** `webpack.mix.js`.

**Verify:** `npm install`, `npm run build`, `npm run dev` all succeed; SPA loads at `https://nvisionoffice.test`.

**Commit:** `chore(deps): remove unused packages and webpack.mix.js`

---

### Phase 1 — Build toolchain (Vite 6)

**Bump:**
- `vite` → `^6.0.0`
- `@vitejs/plugin-vue` → `^5.2.0`
- `laravel-vite-plugin` → `^1.0.0`
- `postcss` → `^8.4.49`
- `sass` → `^1.80.0`
- `@popperjs/core` → `^2.11.8` (devDeps only after Phase 0)

**Files:** `vite.config.js` — verify `valetTls: 'nvisionoffice.test'` still resolves under `laravel-vite-plugin@1` (it does; option preserved). The `define: { __VUE_PROD_HYDRATION_MISMATCH_DETAILS__ }` and the `vue: 'vue/dist/vue.esm-bundler.js'` alias stay.

**Verify:** `npm run build` succeeds; `npm run dev` HMR works; load one Blade route + one SPA view; check console clean.

**Commit:** `chore(deps): upgrade Vite to 6 and laravel-vite-plugin to 1`

---

### Phase 2 — Vue core + ecosystem

**Bump:**
- `vue` → `^3.5.13`
- `vue-router` → `^4.5.0`
- `pinia` → `^2.3.0`

**Files:** none expected. Pinia 2.3 keeps options syntax (`defineStore('name', { state, actions, getters })`) — all 4 stores remain valid: `authStore`, `companyStore`, `notificationStore`, `templateStore`.

**Verify:** login (JWT), navigate ≥3 routes, switch company (exercises `companyStore` + `CompanyId` propagation), trigger one notification.

**Commit:** `chore(deps): upgrade Vue 3.5, Vue Router 4.5, Pinia 2.3`

---

### Phase 3 — Validation + small-radius utilities

**Bump:**
- `@vuelidate/core` → `^2.0.3` (alpha → stable)
- `@vuelidate/validators` → `^2.0.4`
- `axios` → `^1.7.9`
- `sweetalert2` → `^11.14.5`
- `slugify` → `^1.6.6`, `@sindresorhus/slugify` → `^2.2.1`
- `moment` → `^2.30.1`
- `nprogress` → `^0.2.0` (already current)
- `lodash` → `^4.17.21` (already current)

**Files:** `resources/js/views/auth/Login.vue` — verify `useVuelidate` import path; alpha → stable changed nothing structurally but re-run validation flow.

**Verify:** Login validation errors render; an axios POST round-trips; one SweetAlert toast.

**Commit:** `chore(deps): upgrade vuelidate, axios, sweetalert2, slugify, moment`

---

### Phase 4 — Bootstrap 5.3 + UI primitives

**Bump:**
- `bootstrap` → `^5.3.3`
- `@fortawesome/fontawesome-free` → `^6.7.2`
- `simplebar` → `^6.3.0` (note v6 — verify imports in any consumer)

**Files:**
- `resources/js/app.js` — `import * as bootstrap from "bootstrap"` unchanged.
- `resources/js/Modal.vue` (or wherever `import {Modal} from 'bootstrap'` lives) — unchanged.
- `resources/sass/app.scss` — Bootstrap 5.3 adds CSS custom properties but is backward-compatible at the SCSS variable level. Watch for deprecation warnings; address only if build fails.

**Verify:** modal open/close, dropdown, tooltip, offcanvas, simplebar scroll on a long page, FA icons render across SPA.

**Commit:** `chore(deps): upgrade Bootstrap 5.3, Font Awesome 6.7, Simplebar 6`

---

### Phase 5 — CKEditor unified package migration

**Remove** (already done in Phase 0): `@ckeditor/ckeditor5-build-classic|inline|balloon|balloon-block`.

**Add:**
- `ckeditor5` → `^44.0.0` (unified package containing all open-source plugins)
- `@ckeditor/ckeditor5-vue` → `^7.3.0` (peer-depends on Vue 3.5)

**Files:** rewrite `resources/js/components/ui/FormElements/CkEditor.vue`:

```vue
<script setup>
import { ref } from 'vue';
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import {
  ClassicEditor, Essentials, Paragraph, Heading,
  Bold, Italic, Link, List, BlockQuote, Undo,
} from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';

const props = defineProps({ modelValue: { type: String, default: '' } });
const emit = defineEmits(['update:modelValue']);

const editorConfig = {
  plugins: [Essentials, Paragraph, Heading, Bold, Italic, Link, List, BlockQuote, Undo],
  toolbar: ['undo','redo','|','heading','|','bold','italic','link','bulletedList','numberedList','blockQuote'],
  licenseKey: 'GPL', // required v44+ for self-hosted open-source use
};
</script>

<template>
  <Ckeditor
    :editor="ClassicEditor"
    :model-value="modelValue"
    :config="editorConfig"
    @update:model-value="emit('update:modelValue', $event)"
  />
</template>
```

Match the existing component's prop/emit shape — read the current file before rewriting and preserve any custom props (height, readonly, etc.).

**Verify:** every page that mounts `<CkEditor>` shows the toolbar, accepts typing, two-way binds via `v-model`, and submits on form save.

**Commit:** `refactor(ckeditor): migrate to unified ckeditor5 package`

---

### Phase 6 — Vue plugin ecosystem (highest churn)

**Bump (with verification per package):**

| Package | Target | Risk note |
|---|---|---|
| `@ckpack/vue-color` | `^1.5.0` | low |
| `@vueform/slider` | `^2.1.10` | low |
| `@chenfengyuan/vue-countdown` | `^2.1.2` | low |
| `@highlightjs/vue-plugin` | `^2.1.2` | low |
| `highlight.js` | `^11.10.0` | low |
| `json-editor-vue` | `^0.18.0` | **API changed** — verify `mode` prop and content shape (`{ json }` vs `{ text }`) across 8 consumers |
| `vanilla-jsoneditor` | `^2.0.0` | **API changed** — same concern as above |
| `vue-cropperjs` | latest | low |
| `vue-dataset` | latest | check breaking-changes log |
| `vue-easy-lightbox` | `^1.19.0` | low |
| `vue-flatpickr-component` | `^11.0.5` | low |
| `vue-lazyload` | `^3.0.0` | already current |
| `vue-select` | `^4.0.0-beta.6` | **still beta** — flag risk; consider switching to `vue3-select-component` if blocking |
| `vue-star-rating` | latest | low |
| `vue3-cookies` | `^1.0.6` | already current |
| `vuedraggable` | `^4.1.0` | already current |
| `@codemirror/*` (lang-css/html/javascript/php, language) | minor latest | low |
| `codemirror` | latest | low |
| `@lezer/common` | latest | low |

**Files (consumers to smoke-test):**
- `resources/js/views/translation/{Edit,Create}Translation.vue`
- `resources/js/views/company-translation/{Edit,Create}CompanyTranslation.vue`
- `resources/js/components/email/TemplateAndPreview.vue`
- `resources/js/views/setting/UpdateSetting.vue`
- `resources/js/components/onboard/Settings.vue`
- `resources/js/App.vue` (vanilla-jsoneditor CSS import)
- `resources/js/components/ui/FormElements/CodeMirrorEditor.vue`
- `resources/js/views/module/ActivateModule.vue` (vuedraggable)
- 11 vue-select consumers under `views/database/**`, `views/user/**`, `components/onboard/WebshopUser.vue`

**Verify (smoke matrix — full SPA walkthrough):**

| Area | Check |
|---|---|
| Auth | Login + logout |
| Routing | Deep-link, guard redirect |
| Pinia | Auth, company, notification, template store each exercised |
| Bootstrap | Modal, dropdown, tooltip, offcanvas, simplebar |
| Forms | CkEditor (Phase 5), CodeMirror, vue-select dropdowns, flatpickr, slider, color picker, cropper, star rating |
| JSON editors | Open Translation editor, Settings JSON, Email template — verify save round-trips |
| Drag-drop | Module activate page reorders |
| Misc | Lightbox open, lazy-loaded images, countdown, hljs code block, SweetAlert toast |
| Build | `npm run build` clean; preview the built bundle; no console errors |

**Commit:** `chore(deps): upgrade remaining Vue plugin ecosystem`

---

## Critical files to modify

| Action | Path | Phase |
|---|---|---|
| Modify | `package.json` | 0–6 |
| Re-resolve | `package-lock.json` | 0–6 |
| Delete | `webpack.mix.js` | 0 |
| Modify | `vite.config.js` (verify only) | 1 |
| Modify | `resources/js/views/auth/Login.vue` (vuelidate import path) | 3 |
| Rewrite | `resources/js/components/ui/FormElements/CkEditor.vue` | 5 |
| Verify | the 8 `json-editor-vue` consumers listed above | 6 |
| Verify (no edit expected) | `resources/js/app.js`, `resources/js/Modal.vue` | 4 |

## Reused existing patterns

- **`<script setup>` is already the dominant style** (171/177 components). New CkEditor.vue follows the same pattern.
- **Vue plugin registration** in `resources/js/app.js` (Pinia, Router, VueLazyload, Bootstrap) — no new global plugins needed for this upgrade; CKEditor's `<Ckeditor>` is a local component import.
- **Path alias `@/`** → `resources/js/*` (in `jsconfig.json` and Vite) — keep using it.
- **Pinia store template** under `resources/js/stores/` — options syntax stays valid through 2.3.

## End-to-end verification (after Phase 6)

1. `rm -rf node_modules package-lock.json && npm install` — clean resolve, no peer-dep errors blocking install.
2. `npm run build` — production bundle into `public/build/` succeeds; no warnings about missing exports.
3. `npm run dev` — Vite 6 dev server boots on `https://nvisionoffice.test`; HMR works on a `<script setup>` edit.
4. Click-through SPA against a real company DB (multi-tenant context required):
   - Login (JWT)
   - Switch company (`companyStore` + `CompanyId` propagation through `SetCompanyDatabaseConnection` middleware)
   - Open one tenant CRUD page (e.g., Customer or Item list)
   - Open the Translation editor (`json-editor-vue`)
   - Open a CkEditor page
   - Open a CodeMirror page
   - Open the Module activate page (`vuedraggable`)
5. `npx vite preview` against the production bundle — no runtime console errors on the routes from step 4.

## Out of scope (explicit non-goals)

- Laravel framework upgrade (lives in `docs/laravel-latest-migration.md`; sequence independently).
- Pinia 3 migration.
- Vite 7 (decided against — Node 20.19+ floor is tight).
- ESLint / Prettier baseline.
- Switching `vue-select` away from its long-running beta (flagged for a separate ticket if its v4-final release keeps slipping).
- TypeScript adoption.
