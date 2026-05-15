import {ref} from "vue";
import {onBeforeRouteLeave, useRoute} from "vue-router";
import {useListStateStore} from "@/stores/listStateStore";

export default function useGridManagement() {
    let tableFields = ref([]);
    let bodyHeight = ref("");
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
    const restored = listStateStore.consume(route?.name);
    if (restored) {
        request.value = restored;
    }
    onBeforeRouteLeave((to, from) => {
        listStateStore.save(from.name, request.value);
    });

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
        request.value.search_columns = value;
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

    return {
        tableFields,
        bodyHeight,
        request,
        setTableFields,
        resetRequest,
        setSearchColumns,
        setPageNo,
        setPerPage,
        setSortBy,
        setSearchQuery,
        setBodyHeight
    }
}
