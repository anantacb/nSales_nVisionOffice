<script setup>
import {Dataset, DatasetInfo, DatasetItem, DatasetPager, DatasetSearch, DatasetShow} from "vue-dataset";
import {computed, onMounted} from "vue";
import Button from "@/components/ui/Button.vue";
import {useJQueryDatatableTable} from "@/composables/useJQueryDatatableTable";

let {columns, setColumns, onSort, sortBy, performDomActions} = useJQueryDatatableTable();

const emit = defineEmits(['assignToCompany'])
const props = defineProps({
    companyUsers: {
        type: Array,
        required: false,
        default: () => []
    }
});

setColumns([
    {
        name: "Company Name",
        field: "CompanyName",
        sort: "",
    },
    {
        name: "Initials",
        field: "Initials",
        sort: "",
    },
    {
        name: "Licence Type",
        field: "LicenceType",
        sort: "",
    },
    {
        name: "Roles",
        field: "Roles",
        sort: "",
    }
]);

const CompanyUsers = computed(() => {
    return props.companyUsers.map((companyUser) => {
        return {
            LicenceType: companyUser.LicenceType,
            Initials: companyUser.Initials,
            CompanyName: companyUser.company.CompanyName,
            Roles: companyUser.roles.map(role => role.Name)
        }
    })
});

onMounted(() => {
    performDomActions()
});

</script>

<template>
    <BaseBlock content-full title="Company User">
        <template #options>
            <button class="btn btn-sm btn-outline-info" @click="emit('assignToCompany')">
                <i class="fa fa-plus-circle"></i> Assign To Company
            </button>
        </template>
        <Dataset
            v-slot="{ ds }"
            :ds-data="CompanyUsers"
            :ds-search-in="['CompanyName', 'Name']"
            :ds-sortby="sortBy"
        >
            <div :data-page-count="ds.dsPagecount" class="row">
                <div id="datasetLength" class="col-md-8 py-2">
                    <DatasetShow/>
                </div>
                <div class="col-md-4 py-2">
                    <DatasetSearch ds-search-placeholder="Search..."/>
                </div>
            </div>
            <hr/>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                            <tr>
                                <th scope="col">ID</th>
                                <th
                                    v-for="(th, index) in columns"
                                    :key="th.field"
                                    :class="['sort', th.sort]"
                                    @click="onSort($event, index)"
                                >
                                    {{ th.name }} <i class="gg-select float-end"></i>
                                </th>
                            </tr>
                            </thead>
                            <DatasetItem class="fs-sm" tag="tbody">
                                <template #default="{ row, rowIndex }">
                                    <tr>
                                        <th scope="row">{{ rowIndex + 1 }}</th>
                                        <td>{{ row.CompanyName }}</td>
                                        <td>{{ row.Initials }}</td>
                                        <td>{{ row.LicenceType }}</td>
                                        <td><span v-for="role in row.Roles"
                                                  :class="role === `Developer` ? `bg-danger-light text-danger` : (role === 'Administrator' ? `bg-success-light text-success`: `bg-info-light text-info`)"
                                                  class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill"
                                        >{{ role }}</span>
                                        </td>
                                    </tr>
                                </template>
                            </DatasetItem>
                        </table>
                    </div>
                </div>
            </div>
            <div
                class="d-flex flex-md-row flex-column justify-content-between align-items-center"
            >
                <DatasetInfo class="py-3 fs-sm"/>
                <DatasetPager class="flex-wrap py-3 fs-sm"/>
            </div>
        </Dataset>
    </BaseBlock>
</template>

<style lang="scss" scoped>
.gg-select {
    box-sizing: border-box;
    position: relative;
    display: block;
    transform: scale(1);
    width: 22px;
    height: 22px;
}

.gg-select::after,
.gg-select::before {
    content: "";
    display: block;
    box-sizing: border-box;
    position: absolute;
    width: 8px;
    height: 8px;
    left: 7px;
    transform: rotate(-45deg);
}

.gg-select::before {
    border-left: 2px solid;
    border-bottom: 2px solid;
    bottom: 4px;
    opacity: 0.3;
}

.gg-select::after {
    border-right: 2px solid;
    border-top: 2px solid;
    top: 4px;
    opacity: 0.3;
}

th.sort {
    cursor: pointer;
    user-select: none;

    &.asc {
        .gg-select::after {
            opacity: 1;
        }
    }

    &.desc {
        .gg-select::before {
            opacity: 1;
        }
    }
}
</style>
