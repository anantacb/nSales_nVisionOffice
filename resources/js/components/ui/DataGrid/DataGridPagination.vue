<script setup>
const props = defineProps({
    pagination: {
        type: Object
    }
});
const emit = defineEmits(['paginate', 'perPageChange']);

const perPageOptions = [10, 20, 50, 100];

function paginate(pageNo) {
    if (pageNo > 0 && pageNo <= props.pagination.last_page_no) {
        emit("paginate", pageNo)
    }
}

function changePerPage(event) {
    emit("perPageChange", Number(event.target.value));
}
</script>

<template>
    <div v-if="props.pagination.total" class="data-grid-pagination-wrapper">
        <div class="per-page-selector">
            <small class="text-muted">Rows per page:</small>
            <select
                :value="props.pagination.items_per_page"
                class="form-select form-select-sm"
                @change="changePerPage"
            >
                <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }}</option>
            </select>
        </div>
        <div v-if="props.pagination.last_page_no > 1" class="pagination-items">
            <small class="text-muted">Showing
                {{ (props.pagination.current_page_no - 1) * props.pagination.items_per_page + 1 }}
                -
                {{ props.pagination.current_page_no * props.pagination.items_per_page }} of
                <b>{{ props.pagination.total }}</b>
            </small>
            <span
                :class="{disabled: props.pagination.current_page_no <= 1}"
                class="pagination-item pagination-item-previous"
                @click="paginate(props.pagination.current_page_no-1)">
                <i class="fa fa-circle-left"></i>
                Previous
            </span>
            <span
                :class="{disabled: props.pagination.current_page_no === props.pagination.last_page_no}"
                class="pagination-item pagination-item-next"
                @click="paginate(props.pagination.current_page_no + 1)">
                Next
                <i class="fa fa-circle-right"></i>
            </span>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.data-grid-pagination-wrapper {
    //background: #fff;
    display: flex;
    padding: 0.65rem 1rem;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
    margin-bottom: 1rem;

    .per-page-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;

        .form-select {
            width: auto;
            min-width: 4.5rem;
        }
    }

    .pagination-items {
        display: flex;
        align-items: center;

        .pagination-item {
            display: inline-flex;
            align-items: center;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;

            i {
                margin: 0 0.5rem;
                font-weight: 400;
                display: inline-block;
                font-size: 90%;
                vertical-align: middle;
            }

            &.disabled {
                color: #ccc;
                cursor: unset;
            }
        }

        .pagination-item-previous {
            margin: 0 1.5rem;
        }
    }
}
</style>
