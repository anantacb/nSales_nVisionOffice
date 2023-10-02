import {computed, ref} from "vue";

export function useJQueryDatatableTable() {
    let columns = ref([]);

    function setColumns(value) {
        columns.value = value;
    }

    function performDomActions() {
        // Remove labels from
        document.querySelectorAll(".datasetLength label").forEach((el) => {
            el.remove();
        });
        // Replace select classes
        let selectLength = document.querySelector(".datasetLength select");
        //selectLength.classList = "";
        selectLength.classList.add("form-select");
        selectLength.style.width = "80px";
    }

    // On sort th click
    function onSort(event, i) {
        let toset;
        const sortEl = columns.value[i];
        if (!event.shiftKey) {
            columns.value.forEach((o) => {
                if (o.field !== sortEl.field) {
                    o.sort = "";
                }
            });
        }
        if (!sortEl.sort) {
            toset = "asc";
        }
        if (sortEl.sort === "desc") {
            toset = event.shiftKey ? "" : "asc";
        }
        if (sortEl.sort === "asc") {
            toset = "desc";
        }
        sortEl.sort = toset;
    }

    // Sort by functionality
    const sortBy = computed(() => {
        return columns.value.reduce((acc, o) => {
            if (o.sort) {
                o.sort === "asc" ? acc.push(o.field) : acc.push("-" + o.field);
            }
            return acc;
        }, []);
    });
    return {columns, setColumns, onSort, sortBy, performDomActions};
}
