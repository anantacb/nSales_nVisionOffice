<script setup>
const props = defineProps({
    pagination: {
        type: Object
    }
});
const emit = defineEmits(['paginate']);

function paginate(pageNo) {
    if (pageNo > 0 && pageNo <= props.pagination.last_page_no) {
        emit("paginate", pageNo)
    }
}
</script>

<template>
    <div v-if="props.pagination.total && props.pagination.last_page_no > 1" class="data-grid-pagination-wrapper">
        <div>
            <small class="text-muted">Row per page: <b>{{ props.pagination.items_per_page }}</b></small>
        </div>
        <div class="pagination-items">
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
