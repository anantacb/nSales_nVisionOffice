<script setup>
import {computed, onUpdated, ref, watch} from "vue";
import DataGridFilter from "@/components/ui/DataGrid/DataGridFilter.vue";
import DataGridPagination from "@/components/ui/DataGrid/DataGridPagination.vue";

const props = defineProps({
    height: {
        type: String,
        default: () => {
            return "auto";
        }
    },
    tableFields: {
        type: [Object, Array]
    },
    tableData: {
        type: [Object, Array]
    },
    expandable: {
        type: Boolean,
        default: () => {
            return false;
        }
    },
    searchable: {
        type: Boolean,
        default: () => {
            return false;
        }
    },
    searchString: {
        type: String,
        required: false,
        default: () => {
            return '';
        }
    },
    pagination: {
        type: Object,
        default: () => {
            return {
                current_items_count: 0,
                current_page_no: 0,
                has_more_pages: false,
                items_per_page: 0,
                last_page_no: 0,
                total: 6
            }
        }
    }
});

const emit = defineEmits(['expand', 'sortBy', 'search', 'paginate']);

let sortBy = ref("");
let sortOrder = ref("");
let gridHeaderHeight = ref("0px");
let expandedElementIndex = ref(0);
let searchText = ref("");

const modifiedTableFields = computed(() => {
    let tableFields = [...props.tableFields];
    if (props.expandable) {
        tableFields = [
            {
                name: "expand",
                title: ` `,
                width: "1.5rem",
                dataClass: "data-grid-item-expand"
            },
            ...tableFields
        ]
    }
    return tableFields;
});

const getContainerStyle = computed(() => {
    let style = {};
    style["height"] = props.height;
    style["--scroll-track-margin-top"] = gridHeaderHeight.value;
    return style;
});

const getGridStyle = computed(() => {
    const style = {};
    style["grid-template-columns"] = "";
    for (let field in modifiedTableFields.value) {
        if (field.width) {
            style["grid-template-columns"] += `${field.width} `;
        } else {
            style["grid-template-columns"] += "minmax(100px, auto) ";
        }
    }
    return style;
});


function getTitle(field) {
    return field.title ? field.title : field.name;
}

function getRowClass(field, data) {
    return (field.dataClass ? field.dataClass : '') + " " + (data.dataClass ? data.dataClass : '');
}

function getRowData(data, field) {
    if (field.formatter) {
        return field.formatter(data[field.name]);
    }
    return data[field.name];
}

function sortByField(field) {
    if (sortBy.value === field.sortField && sortOrder.value !== "desc") {
        sortOrder.value = "desc";
    } else {
        sortOrder.value = "asc";
    }
    sortBy.value = field.sortField;
    emit("sortBy", {field: sortBy.value, order: sortOrder.value});
}

function randKey() {
    return Math.random().toString(36).substr(2, 8);
}

function toggleExpandableElement(index) {
    if (expandedElementIndex.value === index + 1) {
        expandedElementIndex.value = 0;
    } else {
        expandedElementIndex.value = index + 1;
    }
    emit("expand", props.tableData[index]);
}

function searchBy(query) {
    emit("search", query);
}

function goToPage(pageNo) {
    emit("paginate", pageNo);
    expandedElementIndex.value = 0;
}


onUpdated(() => {
    const headItem = document.querySelector(".data-grid-head-item");
    gridHeaderHeight.value = headItem.getBoundingClientRect().height + "px";
});

watch(() => props.searchString, () => {
    searchText.value = props.searchString;
});

</script>

<template>
    <div :style="getContainerStyle" class="data-grid-container scrollbar">

        <div class="data-grid-filters">
            <div class="data-grid-extra-filters">
                <slot name="extra-filters"></slot>
            </div>
            <DataGridFilter v-if="props.searchable" v-model="searchText" @change="searchBy"/>
        </div>

        <div :style="getGridStyle" class="data-grid">
            <div
                v-for="field in modifiedTableFields"
                :key="'head-' + field.name"
                :class="field.titleClass ? field.titleClass : ''"
                class="data-grid-item data-grid-head-item"
            >
                <a
                    v-if="field.sortField"
                    class="d-flex sort-link"
                    href="#"
                    @click.prevent="sortByField(field)"
                >
                    <slot :name="'head-' + field.sortField" v-bind:field="field">
                        <span class="mr-1" v-html="getTitle(field)"></span>
                    </slot>
                    <span :class="{ disabled: field.sortField !== sortBy }" class="sort-icon">
                        <img
                            v-if="field.sortField === sortBy && sortOrder === 'desc'"
                            alt=""
                            src="/img/chevron-down.svg"
                        />
                        <img v-else alt="" src="/img/chevron-up.svg"/>
                    </span>
                </a>
                <slot v-else :name="'head-' + field.name" v-bind:field="field">
                    <span class="mr-1" v-html="getTitle(field)"></span>
                </slot>
            </div>

            <template v-if="props.tableData && props.tableData.length > 0">
                <template v-for="(data, index) in props.tableData">
                    <div
                        v-for="field in modifiedTableFields"
                        :key="'body-' + randKey() + index + field.name"
                        :class="getRowClass(field, data)"
                        class="data-grid-item"
                    >
                        <div v-if="field.name === 'expand'">
                            <button class="btn-expand" @click.prevent="toggleExpandableElement(index)">
                                <slot
                                    :name="'body-' + field.name"
                                    v-bind:data="data"
                                    v-bind:index="index"
                                >
                                    <img v-if="expandedElementIndex === index + 1" alt="expand"
                                         src="/img/chevron-down.svg" title="expand"
                                         width="20"/>
                                    <img v-else alt="expand" src="/img/chevron-right.svg" title="expand" width="20"/>
                                </slot>
                            </button>
                        </div>
                        <slot
                            v-else
                            :name="'body-' + field.name"
                            v-bind:data="data"
                            v-bind:index="index"
                        >
                            <div v-html="getRowData(data, field)"></div>
                        </slot>
                    </div>
                    <transition name="fade">
                        <div v-if="expandedElementIndex === index+1" class="expandable-element px-4 py-3">
                            <slot
                                :name="'body-expandable'"
                                v-bind:data="data"
                                v-bind:index="index"
                            ></slot>
                        </div>
                    </transition>
                </template>
            </template>
            <template v-else>
                <div class="data-grid-item text-center grid-full-width">
                    <slot name="body-nocontent"> No Data Available</slot>
                </div>
            </template>
        </div>

        <DataGridPagination v-if="props.pagination" :pagination="props.pagination" @paginate="goToPage"/>
    </div>
</template>

<style lang="scss" scoped>
.data-grid-container {
    width: 100%;
    overflow: auto;
}

.dark-mode {
    .data-grid {
        .data-grid-head-item {
            background-color: white;

            span {
                color: black;
            }
        }
    }
}

.data-grid {
    border-collapse: collapse;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 15px;
    position: relative;
    display: grid;
    // grid-template-columns: repeat(6, minmax(100px, auto));
    grid-auto-rows: auto;

    .data-grid-item {
        //background: #fff;
        border-bottom: 1px solid #bec8d5;
        display: flex;
        padding: 0.65rem 1rem;
        align-items: center;
    }

    .data-grid-item.data-grid-item-expand {
        padding: 0 0.5rem;
    }

    .data-grid-head-item {
        //position: relative;
        position: sticky;
        top: 0;
        z-index: 5;
        align-items: flex-end;
        background-color: #1f2937;

        span {
            color: white;
        }

        .sort-link {
            text-decoration: none;
            color: #29235c;
        }

        .sort-icon {
            cursor: pointer;
            display: flex;
        }

        .sort-icon img {
            width: 1.2rem;
        }

        .sort-icon.disabled {
            opacity: 0;
            pointer-events: unset;
        }
    }

    .expandable-element {
        display: grid;
        grid-column: 1/-1;
    }

    .btn-expand {
        padding: 0;
        border: 0;
        background: transparent;
        display: inline-block;
    }
}

.scrollbar {
    &::-webkit-scrollbar {
        width: 10px;
        height: 10px;
    }

    &::-webkit-scrollbar-track {
        background-color: transparent;
        margin-top: var(--scroll-track-margin-top);
    }

    &::-webkit-scrollbar-corner {
        background-color: transparent;
    }

    &::-webkit-scrollbar-thumb {
        border-radius: 6px;
        // -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.9);
        background-color: #999;
    }
}

.data-grid-body.scrollbar:hover {
    padding-right: 0;
    overflow-y: scroll !important;
}

.grid-full-width {
    grid-column: 1/-1;
}

.data-grid-filters {
    display: flex;
    justify-content: space-between;
}

.data-grid-extra-filters {
    margin-bottom: 0.5rem;
    margin-top: 0.5rem;
}
</style>
