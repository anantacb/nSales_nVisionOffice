<template>
    <nav class="d-inline-block" v-if="pagination.last_page_no > 1">
        <ul class="pagination mb-1">
            <li class="page-item"
                :class="{'disabled':pagination.current_page_no <= 1}"
                @click.prevent="gotoPage(pagination.current_page_no-1)">
                <a class="page-link" href="#"><i class="fa fa-chevron-left align-middle mr-1" aria-hidden="true"></i> Previous</a>
            </li>
            <li class="page-item"
                :class="{'disabled':pagination.current_page_no == pagination.last_page_no}"
                @click.prevent="gotoPage(pagination.current_page_no+1)">
                <a class="page-link" href="#">Next <i class="fa fa-chevron-right align-middle ml-1" aria-hidden="true"></i></a>
            </li>
        </ul>
        <div class="text-muted">
            <small>Showing {{ pagination.items_per_page*(pagination.current_page_no-1)+1 }} to {{ pagination.items_per_page*pagination.current_page_no }} of {{ pagination.total }} items</small>
        </div>
    </nav>
</template>

<script>
export default {
    name: "Pagination",
    props: ['pagination'],
    methods: {
        gotoPage(pageNo) {
            if (pageNo >= 1 && pageNo <= this.pagination.last_page_no) {
                this.$emit('gotoPage', pageNo)
            }
        }
    }
}
</script>

<style scoped>

</style>
