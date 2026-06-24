import {ref, watch} from "vue";
import {useRoute} from "vue-router";
import {useListStateStore} from "@/stores/listStateStore";

function firstPathSegment(path) {
    return (path || "").split("/").filter(Boolean)[0] ?? null;
}

export default function useGridManagement() {
    let tableFields = ref([]);
    let bodyHeight = ref("");
    let isLoading = ref(false);
    let request = ref({
        search_columns: [],
        //relations: [],
        filters: [],
        order: {},
        pagination: {"page_no": 1, "per_page": 20},
        query: ""
    });

    const route = useRoute();
    const listStateStore = useListStateStore();

    // Restore this list's saved state only when returning from one of its detail/edit
    // pages (a route under the same path segment that carries an :id param), or on a hard
    // reload of the list itself. Any other entry (menu click, cross-list nav) starts fresh.
    const prev = listStateStore.previousRoute;
    const backFromDetail = prev && prev.hasParam && prev.segment === firstPathSegment(route?.path);
    const refreshReload = prev && prev.isInitial;
    const saved = listStateStore.get(route?.name);
    if ((backFromDetail || refreshReload) && saved) {
        request.value = saved;
    } else {
        listStateStore.clear(route?.name);
    }

    // Keep this list's snapshot current on every change — reliable regardless of how
    // deeply nested the grid component is in the route's component tree.
    watch(request, () => {
        listStateStore.save(route?.name, request.value);
    }, {deep: true});

    function setTableFields(value) {
        tableFields.value = value;
    }

    function resetRequest() {
        request.value.filters = [];
        request.value.order = {};
        request.value.pagination = {"page_no": 1, "per_page": 20};
        request.value.query = "";
    }

    function setPageNo(value) {
        request.value.pagination.page_no = value;
    }

    function setPerPage(value) {
        request.value.pagination.per_page = value;
        request.value.pagination.page_no = 1;
    }

    function setSortBy(field, order) {
        if (!field || !order) {
            request.value.order = {};
            return;
        }
        request.value.order = [
            {"column": field, "sort": order}
        ];
    }

    function setSearchQuery(value) {
        request.value.query = value;
    }

    function setSearchColumns(value) {
        request.value.search_columns = value;
    }

    function setBodyHeight(value) {
        bodyHeight.value = value;
    }

    // Wraps an async data fetch so the grid loading flag is toggled on/off,
    // resetting even if the fetch throws.
    async function withLoading(fn) {
        isLoading.value = true;
        try {
            return await fn();
        } finally {
            isLoading.value = false;
        }
    }

    return {
        tableFields,
        bodyHeight,
        isLoading,
        request,
        setTableFields,
        resetRequest,
        setSearchColumns,
        setPageNo,
        setPerPage,
        setSortBy,
        setSearchQuery,
        setBodyHeight,
        withLoading
    }
}
