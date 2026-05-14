<script setup>
import {Dataset, DatasetInfo, DatasetItem, DatasetPager, DatasetSearch, DatasetShow} from "vue-dataset";
import {computed, onMounted} from "vue";
import Button from "@/components/ui/Button.vue";
import {useJQueryDatatableTable} from "@/composables/useJQueryDatatableTable";
import Swal from "sweetalert2";
import ApplicationModule from "@/models/Office/ApplicationModule";

import {useNotificationStore} from "@/stores/notificationStore";

const notificationStore = useNotificationStore();

const emit = defineEmits(['assignModuleToApplication', 'editApplicationModule'])
const props = defineProps({
    applicationModules: {
        type: Array,
        required: false,
        default: () => []
    }
});

let {columns, setColumns, onSort, sortBy, performDomActions} = useJQueryDatatableTable();

setColumns([
    {
        name: "Module",
        field: "Name",
        sort: ""
    },
    {
        name: "Always Enabled",
        field: "AlwaysEnabled",

    },
    {
        name: "Title",
        field: "Title",

    },
    {
        name: "SubTitle",
        field: "SubTitle",

    },
    {
        name: "Action",
        field: "Action",
        minWidth: 120
    },
]);

const ApplicationModules = computed(() => {
    return props.applicationModules.map((applicationModule) => {
        return {
            ApplicationId: applicationModule.pivot.ApplicationId,
            ModuleId: applicationModule.Id,
            Name: applicationModule.Name,
            ApplicationModuleId: applicationModule.pivot.Id,
            AlwaysEnabled: applicationModule.pivot.AlwaysEnabled ? 'Yes' : 'No',
            ApplicationVersionStart: applicationModule.pivot.ApplicationVersionStart,
            ApplicationVersionEnd: applicationModule.pivot.ApplicationVersionEnd,
            Title: applicationModule.pivot.Title,
            SubTitle: applicationModule.pivot.SubTitle,
            Description: applicationModule.pivot.Description
        }
    })
});

onMounted(() => {
    performDomActions();
});

function deleteApplicationModule(ApplicationModuleId, index) {
    Swal.fire({
        title: 'Are you sure? Delete Module?',
        html: 'Please type <code class="text-danger">Confirm</code> and press delete.',
        input: 'text',
        inputAttributes: {
            autocapitalize: 'off'
        },
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: 'red',
        showLoaderOnConfirm: true,
        preConfirm: (text) => {
            return text === `Confirm`;
        },
        allowOutsideClick: () => !Swal.isLoading()
    }).then(async (result) => {
        if (result.isConfirmed) {
            let {data, message} = await ApplicationModule.delete(ApplicationModuleId);
            props.applicationModules.splice(index, 1);
            notificationStore.showNotification(message);
        }
    });
}

</script>

<template>
    <BaseBlock content-full title="Modules">
        <template #options>
            <button class="btn btn-sm btn-outline-info" @click="emit('assignModuleToApplication')">
                <i class="fa fa-plus-circle"></i> Assign Module
            </button>
        </template>
        <Dataset
            v-slot="{ ds }"
            :ds-data="ApplicationModules"
            :ds-search-in="['Name']"
            :ds-sortby="sortBy"
        >
            <div :data-page-count="ds.dsPagecount" class="row">
                <div id="datasetLengthApplicationModules" class="datasetLength col-md-8 py-2">
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
                                    :style="th.minWidth ? `min-width:${th.minWidth}px` : ``"
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
                                        <td>{{ row.Name }}</td>
                                        <td>{{ row.AlwaysEnabled }}</td>
                                        <td>{{ row.Title }}</td>
                                        <td>{{ row.SubTitle }}</td>
                                        <td>
                                            <button class="btn rounded-pill btn-alt-warning me-1"
                                                    @click="emit('editApplicationModule', row)">
                                                <i class="fa fa-pen-alt"></i>
                                            </button>
                                            <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                                                    @click="deleteApplicationModule(row.ApplicationModuleId, rowIndex)"
                                            >
                                                <i class="fa fa-trash-alt"></i>
                                            </button>
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
