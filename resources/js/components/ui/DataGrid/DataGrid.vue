<template>
    <div :style="getContainerStyle" class="data-grid-container scrollbar">
        <DataGridFilter v-if="searchable" @search="searchBy"/>
        <div :style="getGridStyle" class="data-grid">
            <div
                v-for="field in modifiedTablefields"
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

            <template v-if="tabledata && tabledata.length > 0">
                <template v-for="(data, index) in tabledata">
                    <div
                        v-for="field in modifiedTablefields"
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
                                    <img v-if="expandedElementIndex === index+1" alt="expand"
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
        <DataGridPagination v-if="pagination" :pagination="pagination" @paginate="goToPage"/>
    </div>
</template>

<script>
import DataGridFilter from "@/components/ui/DataGrid/DataGridFilter.vue";
import DataGridPagination from "./DataGridPagination.vue";

export default {
    components: {
        DataGridFilter,
        DataGridPagination
    },
    props: {
        height: {
            type: String,
            default: "auto"
        },
        tablefields: [Object, Array],
        tabledata: [Object, Array],
        expandable: {
            type: Boolean,
            default: false
        },
        searchable: {
            type: Boolean,
            default: false
        },
        pagination: {
            type: Object,
            default: function () {
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
    },
    data() {
        return {
            sortBy: "",
            sortOrder: "",
            gridheaderheight: "0px",
            expandedElementIndex: 0
        };
    },
    computed: {
        modifiedTablefields() {
            let tablefields = [...this.tablefields]
            if (this.expandable) {
                tablefields = [
                    {
                        name: "expand",
                        title: ` `,
                        width: "1.5rem",
                        dataClass: "data-grid-item-expand"
                    },
                    ...tablefields
                ]
            }
            return tablefields
        },
        getContainerStyle() {
            let style = {};
            style["height"] = this.height;
            style["--scroll-track-margin-top"] = this.gridheaderheight;
            return style;
        },
        getGridStyle() {
            const style = {};
            style["grid-template-columns"] = "";
            for (let field of this.modifiedTablefields) {
                if (field.width) {
                    style["grid-template-columns"] += `${field.width} `;
                } else {
                    style["grid-template-columns"] += "minmax(100px, auto) ";
                }
            }
            return style;
        },
    },
    methods: {
        getTitle(field) {
            return field.title ? field.title : field.name;
        },
        getRowClass(field, data) {
            return (field.dataClass ? field.dataClass : '') + " " + (data.dataClass ? data.dataClass : '');
        },
        getRowData(data, field) {
            if (field.formatter) {
                return field.formatter(data[field.name]);
            }
            return data[field.name];
        },
        sortByField(field) {
            if (this.sortBy === field.sortField && this.sortOrder !== "desc") {
                this.sortOrder = "desc";
            } else {
                this.sortOrder = "asc";
            }
            this.sortBy = field.sortField;
            this.$emit("sortBy", {field: this.sortBy, order: this.sortOrder});
        },
        randKey() {
            return Math.random().toString(36).substr(2, 8);
        },
        toggleExpandableElement(index) {
            if (this.expandedElementIndex === index + 1) {
                this.expandedElementIndex = 0
            } else {
                this.expandedElementIndex = index + 1
            }
            this.$emit("expand", this.tabledata[index])
        },
        searchBy(query) {
            this.$emit("search", query)
        },
        goToPage(pageNo) {
            this.$emit("paginate", pageNo)
            this.expandedElementIndex = 0
        }
    },
    updated() {
        const headelm = document.querySelector(".data-grid-head-item");
        this.gridheaderheight = headelm.getBoundingClientRect().height + "px";
    },
};
</script>

<style lang="scss" scoped>
.data-grid-container {
    width: 100%;
    overflow: auto;
}

.data-grid {
    border-collapse: collapse;
    font-size: 0.9rem;
    line-height: 1.5;
    margin-bottom: 0;
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
</style>
