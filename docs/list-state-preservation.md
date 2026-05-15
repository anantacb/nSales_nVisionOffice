# Preserve List-Page State Across Detail Round-Trips

## Context

Every list page (Customers, Orders, Items, Modules, Tables, etc.) builds its state — search query, current page, page size, filters, sort order — inside a single `request` ref created by `resources/js/composables/useGridManagement.js`. State is local to the list component; navigating to a detail page (or anywhere else) unmounts the list and that state is lost. Returning to the list (via the detail page's `<BackButton>` or by clicking the same list link in the left menu) re-mounts a fresh component and the user has to redo their search and pagination.

Desired behaviour:

- **Customer list → customer detail → back → list:** restore the prior search/page/filter/sort/page-size.
- **Customer list → customer detail → click "Customers" in left menu:** same — restore prior state.
- **Customer list → customer detail → click "Tables" in left menu:** Tables opens fresh; the Customer snapshot is discarded.

Scope:

- **Restore only the `request` ref** (search, filters, order, pagination, page size). The list re-fetches from the API on mount, so any edits made on the detail page reflect on return.
- **In-memory (Pinia) only** — refreshing the browser starts fresh.
- **Out of scope:** row caching, scroll-position restoration, `sessionStorage` persistence.

## Approach

One new Pinia store holds a single snapshot keyed by route name. `useGridManagement` reads the snapshot when it runs (restore if same route name, wipe and start fresh otherwise) and saves the current `request` on `onBeforeRouteLeave`. That single rule covers every case above without any per-list code changes.

Key design points:

1. **One snapshot slot, not a map.** The store stores `{rememberedRouteName, snapshot}`. When a list mounts, it asks the store for *its* snapshot — if `rememberedRouteName` matches the current route name, the snapshot is returned; if not, the store is cleared and the list starts fresh. The "different list wipes the snapshot" requirement falls out for free.
2. **Restore happens synchronously during the composable call**, not in `onMounted`. List components typically kick off their initial API fetch in their own `onMounted`; if restore ran in `onMounted` too, the order would be racy. Doing it during composable invocation guarantees `request.value` is already the restored value before any consumer reads it.
3. **`onBeforeRouteLeave` saves the snapshot**, keyed by `from.name`. Vue Router's composable hook fires for the entire active route-component tree, which includes list components rendered inside route views (e.g. `CustomerList.vue` rendered by `views/customer/Customers.vue`).
4. **No changes to list components, route definitions, BackButton, or the left menu.** All new behaviour lives in two files.

## Files

### New: `resources/js/stores/listStateStore.js`

```js
import {defineStore} from 'pinia';
import {ref} from 'vue';

export const useListStateStore = defineStore('listState', () => {
    const rememberedRouteName = ref(null);
    const snapshot = ref(null);

    function save(routeName, request) {
        if (!routeName) return;
        rememberedRouteName.value = routeName;
        snapshot.value = JSON.parse(JSON.stringify(request));
    }

    function consume(routeName) {
        if (!routeName || rememberedRouteName.value !== routeName) {
            clear();
            return null;
        }
        return JSON.parse(JSON.stringify(snapshot.value));
    }

    function clear() {
        rememberedRouteName.value = null;
        snapshot.value = null;
    }

    return {save, consume, clear};
});
```

- `save()` is called on every list-page leave; it overwrites the previous snapshot.
- `consume()` is called on every list-page mount; returns the snapshot if the route name matches, otherwise wipes the store and returns `null`.
- Deep-cloning via `JSON.parse(JSON.stringify(...))` is fine — `request` is a small plain object (search columns, filters, order array, pagination object, query string), no Date/Map/Set values.

### Modified: `resources/js/composables/useGridManagement.js`

Add three imports and a small block at the top of the exported function:

```js
import {ref} from 'vue';
import {onBeforeRouteLeave, useRoute} from 'vue-router';
import {useListStateStore} from '@/stores/listStateStore';

export default function useGridManagement() {
    let tableFields = ref([]);
    let bodyHeight = ref('');
    let request = ref({
        search_columns: [],
        filters: [],
        order: {},
        pagination: {page_no: 1, per_page: 20},
        query: ''
    });

    const route = useRoute();
    const listStateStore = useListStateStore();
    const restored = listStateStore.consume(route?.name);
    if (restored) {
        request.value = restored;
    }
    onBeforeRouteLeave((to, from) => {
        listStateStore.save(from.name, request.value);
    });

    // ... existing setters (setTableFields, resetRequest, setPageNo, etc.) unchanged
}
```

The rest of the file (setters and the return object) is untouched.

## Why this covers every case

| User action | What happens |
|---|---|
| List → detail → BackButton → list | `onBeforeRouteLeave` saves snapshot under list's route name; list re-mounts; `consume()` finds matching name and restores. |
| List → detail → clicks same list in left menu | Same as above — the route name on re-mount equals the saved name, snapshot restored. |
| List A → detail A → clicks List B in left menu | `onBeforeRouteLeave` of List A saved under A's name; List B mounts; `consume(B)` sees mismatch, wipes the store, returns null. B starts fresh. |
| List A → detail A → some non-list route → List A | `consume(A)` still matches the saved name, so state is restored. (Acceptable side-effect; nothing wipes the snapshot until a *different* list mounts.) |
| Browser refresh | Pinia state is in-memory only — every list starts fresh after refresh. |

## Lists not using `useGridManagement`

Any list component that bypasses `useGridManagement` won't get state restoration. Spot-checks during exploration showed all four sampled lists (`CustomerList.vue`, `OrderList.vue`, `ModuleList.vue`, `ItemList.vue`) use the composable. Migrating any stragglers is a one-line `import` + destructure change.

## Verification

1. **Vite build** — `npm run build` exits 0; the new store and modified composable type-check and bundle. The store tree-shakes into the main chunk because list components import the composable, which imports the store.
2. **Manual smoke — restore on back:** open `/customers`, type a search, paginate to page 3, click a row → land on `/customer/{id}`, click BackButton → confirm `/customers` shows the same search and page 3.
3. **Manual smoke — restore via menu:** repeat the above but click "Customers" in the left menu instead of BackButton — same restoration.
4. **Manual smoke — different list wipes:** from `/customer/{id}`, click "Tables" in the left menu — `/tables` should open with default page 1, empty search.
5. **Manual smoke — non-list detour does NOT wipe:** from `/customers` (with state X), navigate to a non-list route (e.g. dashboard), then back to `/customers` — state X should still be there.
6. **DevTools (Pinia)**: open Vue DevTools → Pinia → `listState`. Watch `rememberedRouteName` flip as you navigate and confirm it equals the leaving route's name on every leave.

## Out of scope (defer)

- Scroll-position restoration.
- Caching fetched rows to skip the post-restore API refetch.
- `sessionStorage` persistence across page refresh.
- Migrating any list that doesn't currently use `useGridManagement`.
- Multiple grids on the same route (the single snapshot slot would have last-save-wins behaviour; uncommon today).
