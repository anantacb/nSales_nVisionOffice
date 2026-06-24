import {defineStore} from "pinia";

const STORAGE_KEY = "listState:snapshots";

function loadSnapshots() {
    try {
        const raw = sessionStorage.getItem(STORAGE_KEY);
        return raw ? JSON.parse(raw) : {};
    } catch (e) {
        return {};
    }
}

function persistSnapshots(snapshots) {
    try {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify(snapshots));
    } catch (e) {
        // sessionStorage unavailable / quota — keep in-memory state only.
    }
}

function firstPathSegment(path) {
    return (path || "").split("/").filter(Boolean)[0] ?? null;
}

export const useListStateStore = defineStore("listState", {
    state: () => ({
        // Per-list request snapshots keyed by route name (persisted to sessionStorage).
        snapshots: loadSnapshots(),
        // Origin of the in-progress navigation, recorded by the router beforeEach guard.
        previousRoute: null
    }),
    actions: {
        save(routeName, request) {
            if (!routeName) return;
            this.snapshots[routeName] = JSON.parse(JSON.stringify(request));
            persistSnapshots(this.snapshots);
        },
        get(routeName) {
            if (!routeName || !this.snapshots[routeName]) return null;
            return JSON.parse(JSON.stringify(this.snapshots[routeName]));
        },
        clear(routeName) {
            if (!routeName || !(routeName in this.snapshots)) return;
            delete this.snapshots[routeName];
            persistSnapshots(this.snapshots);
        },
        setPreviousRoute(from) {
            this.previousRoute = {
                name: from?.name ?? null,
                hasParam: Object.keys(from?.params ?? {}).length > 0,
                segment: firstPathSegment(from?.path),
                isInitial: !from?.name
            };
        }
    }
});
