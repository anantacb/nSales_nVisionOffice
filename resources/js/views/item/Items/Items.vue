<script setup>
import ItemList from "@/views/item/Items/ItemList.vue";
import {useCompanyStore} from "@/stores/companyStore";

const companyStore = useCompanyStore();

</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock :title="`Products (${companyStore.selectedCompany.Name})`">
            <!-- <template v-slot:options>
                <PopOverButton
                    btnClass=" btn rounded-pill me-1 btn-alt-info"
                    btnText="export"
                    content="TEST"
                    iconClass="fa fa-file-invoice"
                    @click="">
                    >
                </PopOverButton>
            </template> -->
            <ItemList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
