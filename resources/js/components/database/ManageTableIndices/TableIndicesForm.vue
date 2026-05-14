<script setup>
import {computed, onMounted, ref} from "vue";
import TableIndex from "@/models/Office/TableIndex";
import {useRoute} from "vue-router";
import {useNotificationStore} from "@/stores/notificationStore";
import VueSelect from "vue-select";
import Table from "@/models/Office/Table";
import Swal from "sweetalert2";
import TableField from "@/models/Office/TableField";
import _ from "lodash";

const route = useRoute();
const notificationStore = useNotificationStore();

const props = defineProps({
    table: Object,
    selectCompaniesOptions: Array
});

const emit = defineEmits(['showPreview', 'startLoading', 'endLoading', 'tableDataLoaded', 'noChange']);

onMounted(async () => {
    await getTableIndices();
    await getGeneralTableFields();
});

const typeOptions = computed(() => {
    let typeOptions = [
        {
            label: "Server",
            value: "Server",
        },
        {
            label: "Client",
            value: "Client",
        },
        {
            label: "Both",
            value: "Both",
        }
    ];
    if (props.table.Type === 'Server') {
        return [
            typeOptions[0]
        ];
    } else if (props.table.Type === 'Client') {
        return [
            typeOptions[1]
        ]
    } else if (props.table.Type === 'Both') {
        return typeOptions;
    }
});

let columns = [
    {
        name: "Name",
        title: "Name",
    },
    {
        name: "Type",
        title: "Type",
    },
    {
        name: "Unique",
        title: "Unique",
    },
    {
        name: "company_table_fields",
        title: "Companies"
    },
    {
        name: "Columns",
        title: "Columns",
    },
    {
        name: "Action",
        title: "Action"
    }
];

// Get TableFields Data section
let tableIndicesData = ref([]);
let initialTableIndicesData = [];

async function getTableIndices() {
    try {
        emit('startLoading');
        let {data} = await TableIndex.getTableIndices(route.params.id);

        data.forEach((tableIndex) => {
            tableIndex.companies = tableIndex.company_table_indices.map((companyTableIndex) => {
                return companyTableIndex.company.Id;
            });
            tableIndex.columns = tableIndex.ColumnNames.split(',');
            tableIndex.isGeneralField = _.isEmpty(tableIndex.companies);
            return tableIndex;
        });

        tableIndicesData.value = data;
        initialTableIndicesData = JSON.parse(JSON.stringify(tableIndicesData.value));
        initialTableIndicesData = _.groupBy(initialTableIndicesData, 'Id');
        emit('endLoading');
    } catch (error) {
        notificationStore.showNotification(error.response.data.message, 'error', 15000);
    }
}

let generalTableFieldOptions = ref([]);

async function getGeneralTableFields() {
    try {
        emit('startLoading');
        let {data} = await TableField.getGeneralTableFields(route.params.id);
        generalTableFieldOptions.value = data.map((tableField) => {
            return {label: tableField.Name, value: tableField.Name}
        });
        emit('endLoading');
    } catch (error) {
        notificationStore.showNotification(error.response.data.message, 'error', 15000);
    }
}


// Adding New Field Section
function addNewIndex() {
    tableIndicesData.value.push({
        Id: null,
        TableId: props.table.Id,
        Name: `IDX_${props.table.Name}_`,
        Type: props.table.Type,
        Unique: false,
        ColumnNames: "",
        companies: [],
        columns: [],
        isGeneralField: false
    });
}

// end

// Delete Field Section
let tableIndexIdsToDelete = ref([]);

function deleteIndex(index) {
    if (tableIndicesData.value[index].Id !== null) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Can cause data loss. You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Delete!'
        }).then((result) => {
            if (result.isConfirmed) {
                // Delete From Updated Fields entry IF EXISTS
                delete updatedFields.value[tableIndicesData.value[index].Id];
                // Push Id to deleted Fields
                tableIndexIdsToDelete.value.push(tableIndicesData.value[index].Id);
                tableIndicesData.value.splice(index, 1);
            }
        });
    } else {
        tableIndicesData.value.splice(index, 1);
    }
}

// end

/*--------- Update Field Section Start -----------*/

let updatedFields = ref({});

function addInUpdatedSections(tableIndexId, sectionName) {
    if (_.has(updatedFields.value, tableIndexId)) {
        if (!(updatedFields.value[tableIndexId].updatedSections.indexOf(sectionName) > -1)) {
            updatedFields.value[tableIndexId].updatedSections.push(sectionName);
        }
    } else {
        updatedFields.value[tableIndexId] = {
            updatedSections: [sectionName]
        }
    }
}

function removeFromUpdatedSections(tableIndexId, sectionName) {
    if (_.has(updatedFields.value, tableIndexId)) {
        updatedFields.value[tableIndexId].updatedSections.splice(
            updatedFields.value[tableIndexId].updatedSections.indexOf(sectionName), 1
        );
        if (_.isEmpty(updatedFields.value[tableIndexId].updatedSections)) {
            delete updatedFields.value[tableIndexId];
        }
    }
}

function detectAndUpdateUpdatedSections(index, fieldName) {
    let currentTableIndexData = tableIndicesData.value[index];
    let initialData = initialTableIndicesData[tableIndicesData.value[index].Id][0];
    if (initialData[fieldName] !== currentTableIndexData[fieldName]) {
        addInUpdatedSections(currentTableIndexData.Id, fieldName);
    } else {
        removeFromUpdatedSections(currentTableIndexData.Id, fieldName);
    }
}

// Name Change
const nameChanged = _.debounce((index) => {
    // Not a new Field
    if (tableIndicesData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'Name');
    }
}, 500);
// end

// Type Change
function typeChanged(index) {
    // Not a new Field
    if (tableIndicesData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'Type');
    }
}

// end

// Unique Changed
function uniqueValueChanged(index) {
    // Not a new Field
    if (tableIndicesData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'Unique');
    }
}

// end

// Company Changed
function deselectedCompany(option, index) {
    // Not a new field
    if (tableIndicesData.value[index].Id) {
        // Can not empty the Company List
        if (tableIndicesData.value[index].companies.length === 0) {
            tableIndicesData.value[index].companies.push(option.value);
        }
        companyUpdatedFieldCheck(index);
    }
}

function selectedCompany(option, index) {
    // Not a new Field
    if (tableIndicesData.value[index].Id) {
        companyUpdatedFieldCheck(index);
    }
}

function companyUpdatedFieldCheck(index) {
    let currentTableIndexData = tableIndicesData.value[index];
    let initialData = initialTableIndicesData[tableIndicesData.value[index].Id][0];
    if (JSON.stringify(currentTableIndexData.companies.sort()) !== JSON.stringify(initialData.companies.sort())) {
        addInUpdatedSections(currentTableIndexData.Id, 'Company');
    } else {
        removeFromUpdatedSections(currentTableIndexData.Id, 'Company');
    }
}

// end

// Column Changed
function deselectedColumn(option, index) {
    // Not a new field
    if (tableIndicesData.value[index].Id) {
        // Can not empty the Company List
        if (tableIndicesData.value[index].companies.length === 0) {
            tableIndicesData.value[index].companies.push(option.value);
        }
        columnUpdatedFieldCheck(index);
    }
}

function selectedColumn(option, index) {
    // Not a new Field
    if (tableIndicesData.value[index].Id) {
        columnUpdatedFieldCheck(index);
    }
}

function columnUpdatedFieldCheck(index) {
    let currentTableIndexData = tableIndicesData.value[index];
    let initialData = initialTableIndicesData[tableIndicesData.value[index].Id][0];
    if (JSON.stringify(currentTableIndexData.columns.sort()) !== JSON.stringify(initialData.columns.sort())) {
        addInUpdatedSections(currentTableIndexData.Id, 'Column');
    } else {
        removeFromUpdatedSections(currentTableIndexData.Id, 'Column');
    }
}

// end

/*--------- Update Index Section End -----------*/

// Get SqlPreview Section
async function getSqlPreviews() {
    // Get New Fields
    let newIndices = tableIndicesData.value.filter((tableIndex) => {
        return tableIndex.Id === null;
    });


    // Get Updated Fields and Updates
    let updatedTableIndices = [];
    for (let updatedFieldsKey in updatedFields.value) {
        let field = _.find(tableIndicesData.value, ['Id', parseInt(updatedFieldsKey)]);
        field.updatedSections = updatedFields.value[updatedFieldsKey].updatedSections;
        updatedTableIndices.push(field);
    }

    // Check There is any Change
    if (_.isEmpty(newIndices) && _.isEmpty(tableIndexIdsToDelete.value) && _.isEmpty(updatedTableIndices)) {
        emit('noChange', 'No Change Detected');
        return;
    }

    let formData = {
        newIndices: newIndices, // New Fields
        tableIndexIdsToDelete: tableIndexIdsToDelete.value, // Deleted Fields
        updatedTableIndices: updatedTableIndices // Updated Indices With Updated Section Names
    };

    let {data} = await TableIndex.getTableIndicesOperationPreviews(props.table.Id, formData);

    emit('showPreview', data, formData);
}

// end

defineExpose({
    addNewIndex,
    getSqlPreviews
});
</script>

<template>
    <div ref="tableDivRef" class="table-responsive">
        <p class="text-info">
            Suggestion: Name like IDX_{{ table.Name }}_Column1_...
        </p>
        <table class="table table-hover">
            <thead>
            <tr>
                <th v-for="column in columns" class="text-center">{{ column.title }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(tableIndex, index) in tableIndicesData" :key="`${tableIndex.Id}`">
                <td style="width: 250px">
                    <input :id="`tableIndexName${index}`"
                           v-model="tableIndex.Name"
                           class="form-control form-control-sm"
                           type="text"
                           @input="nameChanged(index)"
                    >
                </td>

                <td style="width: 115px">
                    <Select :id="`tableIndexType${index}`"
                            v-model="tableIndex.Type"
                            :options="typeOptions"
                            :select-class="`form-select-sm`"
                            @change="typeChanged(index)"
                    >
                    </Select>
                </td>

                <td class="text-center">
                    <input v-model="tableIndex.Unique" :name="`tableIndexUnique${index}`"
                           class="form-check-input" type="checkbox" @change="uniqueValueChanged(index)">
                </td>

                <td>
                    <VueSelect
                        v-if="(!tableIndex.isGeneralField) && (props.table.Database !== `Office`)"
                        v-model="tableIndex.companies"
                        :clearable="false"
                        :input-required="false"
                        :loading="false"
                        :options="selectCompaniesOptions"
                        :reduce="company => company.value"
                        :searchable="true"
                        multiple
                        placeholder="Select Companies"
                        @option:selected="selectedCompany($event, index)"
                        @option:deselected="deselectedCompany($event, index)"
                    >
                    </VueSelect>
                </td>

                <td>
                    <VueSelect
                        v-model="tableIndex.columns"
                        :clearable="false"
                        :input-required="false"
                        :loading="false"
                        :options="generalTableFieldOptions"
                        :reduce="field => field.value"
                        :searchable="true"
                        multiple
                        placeholder="Select Columns"
                        @option:selected="selectedColumn($event, index)"
                        @option:deselected="deselectedColumn($event, index)"
                    >
                    </VueSelect>
                </td>

                <td class="text-center">
                    <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                            @click="deleteIndex(index)">
                        <i class="fa fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
</style>
