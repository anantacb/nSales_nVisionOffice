import {ref} from "vue";

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
        setTableFields, resetRequest, setSearchColumns,
        setPageNo,
        setPerPage,
        setSortBy,
        setSearchQuery,
        setBodyHeight
    }
}
