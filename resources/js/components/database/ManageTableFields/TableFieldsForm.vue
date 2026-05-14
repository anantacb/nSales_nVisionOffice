<script setup>
import {computed, onMounted, ref} from "vue";
import TableField from "@/models/Office/TableField";
import {useRoute} from "vue-router";
import {useNotificationStore} from "@/stores/notificationStore";
import VueSelect from "vue-select";
import Table from "@/models/Office/Table";
import Swal from "sweetalert2";
import _ from "lodash";

const route = useRoute();
const notificationStore = useNotificationStore();

const props = defineProps({
    table: Object,
    selectCompaniesOptions: Array
});

const emit = defineEmits(['showPreview', 'startLoading', 'endLoading', 'tableDataLoaded', 'noChange']);

onMounted(() => {
    getTableFields();
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

const dataTypesOptions = [
    'int', 'varchar', 'text', 'datetime', 'double',
    'longtext', 'tinytext', 'tinyint', 'blob', 'longblob', 'enum'
];

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
        name: "DataType",
        title: "Data Type",
    },
    {
        name: "Length",
        title: "Length",
    },
    {
        name: "Default",
        title: "Default",
    },
    {
        name: "Nullable",
        title: "Nullable",
    },
    {
        name: "PrimaryKey",
        title: "Primary Key",
    },
    {
        name: "AutoIncrement",
        title: "Auto Increment",
    },
    {
        name: "Unique",
        title: "Unique",
    },
    {
        name: "SortOrder",
        title: "Sort Order",
    },
    {
        name: "company_table_fields",
        title: "Company"
    },
    {
        name: "Action",
        title: "Action"
    }
];

// Get TableFields Data section
let tableFieldsData = ref([]);
let initialTableFieldsData = [];

async function getTableFields() {
    try {
        emit('startLoading');
        let {data} = await TableField.getTableFields(route.params.id);

        let responseTableFields = data;
        // Order By Sort Order
        responseTableFields = _.orderBy(responseTableFields, ['SortOrder'], ['asc'])

        responseTableFields.forEach((tableField) => {
            tableField.companies = tableField.company_table_fields.map((companyTableField) => {
                return companyTableField.company.Id;
            });
            tableField.isGeneralField = _.isEmpty(tableField.companies);
            return tableField;
        });

        tableFieldsData.value = responseTableFields;

        initialTableFieldsData = JSON.parse(JSON.stringify(responseTableFields));
        initialTableFieldsData = _.groupBy(initialTableFieldsData, 'Id');

        emit('endLoading');
    } catch (error) {
        notificationStore.showNotification(error.response.data.message, 'error', 15000);
    }
}

// end

// Adding New Field Section
function addNewField() {
    let sortOrder = tableFieldsData.value[tableFieldsData.value.length - 1].SortOrder + 10;
    tableFieldsData.value.push({
        Id: null,
        TableId: props.table.Id,
        AutoIncrement: false,
        DataType: "varchar",
        DefaultValue: null,
        Length: 250,
        Name: "",
        Nullable: true,
        PrimaryKey: false,
        SortOrder: sortOrder,
        Type: props.table.Type,
        Unique: false,
        companies: [],
        isGeneralField: false
    });
}

// end

// Delete Field Section
let tableFieldIdsToDelete = ref([]);

function deleteField(index) {
    if (tableFieldsData.value[index].Id !== null) {
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
                delete updatedFields.value[tableFieldsData.value[index].Id];
                // Push Id to deleted Fields
                tableFieldIdsToDelete.value.push(tableFieldsData.value[index].Id);
                tableFieldsData.value.splice(index, 1);
            }
        });
    } else {
        tableFieldsData.value.splice(index, 1);
    }
}

// end

/*--------- Update Field Section Start -----------*/

let updatedFields = ref({});

function addInUpdatedSections(tableFieldId, sectionName) {
    if (_.has(updatedFields.value, tableFieldId)) {
        if (!(updatedFields.value[tableFieldId].updatedSections.indexOf(sectionName) > -1)) {
            updatedFields.value[tableFieldId].updatedSections.push(sectionName);
        }
    } else {
        updatedFields.value[tableFieldId] = {
            updatedSections: [sectionName]
        }
    }
}

function removeFromUpdatedSections(tableFieldId, sectionName) {
    if (_.has(updatedFields.value, tableFieldId)) {
        updatedFields.value[tableFieldId].updatedSections.splice(
            updatedFields.value[tableFieldId].updatedSections.indexOf(sectionName), 1
        );

        if (_.isEmpty(updatedFields.value[tableFieldId].updatedSections)) {
            delete updatedFields.value[tableFieldId];
        }
    }
}

function detectAndUpdateUpdatedSections(index, fieldName) {
    let currentTableFieldData = tableFieldsData.value[index];
    let initialData = initialTableFieldsData[tableFieldsData.value[index].Id][0];
    if (initialData[fieldName] !== currentTableFieldData[fieldName]) {
        addInUpdatedSections(currentTableFieldData.Id, fieldName);
    } else {
        removeFromUpdatedSections(currentTableFieldData.Id, fieldName);
    }
}

// Name Change
const nameChanged = _.debounce((index) => {
    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'Name');
    }
}, 500);

// end

// Type Change
function typeChanged(index) {
    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'Type');
    }
}

// end

// DataType Change
function selectDataTypeFromDropdown(newValue, index) {
    dataTypeChanged(newValue, index);
}

const dataTypeChangedFromInput = _.debounce((newValue, index) => {
    dataTypeChanged(newValue, index);
}, 500);

function dataTypeChanged(newValue, index) {
    tableFieldsData.value[index].DataType = newValue;
    let length;
    switch (newValue) {
        case 'int':
            length = 11;
            break;
        case 'tinyint':
            length = 1;
            break;
        case 'varchar':
            length = 250;
            break;
        default:
            length = null;
            break;
    }
    tableFieldsData.value[index].Length = length;

    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        let currentTableFieldData = tableFieldsData.value[index];
        let initialData = initialTableFieldsData[tableFieldsData.value[index].Id][0];

        if (!(initialData.DataType === currentTableFieldData.DataType) || !(initialData.Length === currentTableFieldData.Length)) {
            // TableField DataType is not in initial state
            tableFieldsData.value[index].isDirty = true;
            addInUpdatedSections(currentTableFieldData.Id, 'DataType');
        } else {
            // TableField DataType is back in initial state
            removeFromUpdatedSections(currentTableFieldData.Id, 'DataType');
        }
    }
}

// end

// Length Change
const lengthChange = _.debounce((index) => {
    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'Length');
    }
}, 500);

// DefaultValue changed
const defaultValueChanged = _.debounce((index) => {
    // Make null if value is empty string
    if (tableFieldsData.value[index].DefaultValue === '') {
        tableFieldsData.value[index].DefaultValue = null;
    }
    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'DefaultValue');
    }
}, 500);

// end

// Nullable Changed
function nullableValueChanged(index) {
    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'Nullable');
    }
}

// end

// PrimaryKey Changed
function primaryKeyValueChanged(index) {
    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'PrimaryKey');
    }
}

// end

// AutoIncrement Changed
function autoIncrementValueChanged(index) {
    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'AutoIncrement');
    }
}

// end

// Unique Changed
function uniqueValueChanged(index) {
    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'Unique');
    }
}

// end

// SortOrder Changed
const sortOrderChanged = _.debounce((index) => {
    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        detectAndUpdateUpdatedSections(index, 'SortOrder');
    }
    // Order By Sort Order
    tableFieldsData.value = _.orderBy(tableFieldsData.value, ['SortOrder'], ['asc']);
}, 500);

// end

// Company Changed
function deselectedCompany(option, index) {
    // Not a new field
    if (tableFieldsData.value[index].Id) {
        // Can not empty the Company List
        if (tableFieldsData.value[index].companies.length === 0) {
            tableFieldsData.value[index].companies.push(option.value);
        }
        companyUpdatedFieldCheck(index);
    }
}

function selectedCompany(option, index) {
    // Not a new Field
    if (tableFieldsData.value[index].Id) {
        companyUpdatedFieldCheck(index);
    }
}

function companyUpdatedFieldCheck(index) {
    let currentTableFieldData = tableFieldsData.value[index];
    let initialData = initialTableFieldsData[tableFieldsData.value[index].Id][0];
    if (JSON.stringify(currentTableFieldData.companies.sort()) !== JSON.stringify(initialData.companies.sort())) {
        addInUpdatedSections(currentTableFieldData.Id, 'Company');
    } else {
        removeFromUpdatedSections(currentTableFieldData.Id, 'Company');
    }
}

// end

/*--------- Update Field Section End -----------*/

// Get SqlPreview Section
async function getSqlPreviews() {
    // Get New Fields
    let newFields = tableFieldsData.value.filter((tableField) => {
        return tableField.Id === null;
    });


    // Get Updated Fields and Updates
    let updatedTableFields = [];
    for (let updatedFieldsKey in updatedFields.value) {
        let field = _.find(tableFieldsData.value, ['Id', parseInt(updatedFieldsKey)]);
        field.updatedSections = updatedFields.value[updatedFieldsKey].updatedSections;
        updatedTableFields.push(field);
    }

    // Check There is any Change
    if (_.isEmpty(newFields) && _.isEmpty(tableFieldIdsToDelete.value) && _.isEmpty(updatedTableFields)) {
        emit('noChange', 'No Change Detected');
        return;
    }

    let formData = {
        newFields: newFields, // New Fields
        tableFieldIdsToDelete: tableFieldIdsToDelete.value, // Deleted Fields
        updatedTableFields: updatedTableFields // Updated Fields With Updated Section Names
    };

    let {data} = await TableField.getTableFieldsOperationPreviews(props.table.Id, formData);

    emit('showPreview', data, formData);
}

// end

defineExpose({
    addNewField,
    getSqlPreviews
});
</script>

<template>
    <div ref="tableDivRef" class="table-responsive">
        <table class="table table-hover">
            <thead>
            <tr>
                <th v-for="column in columns" class="text-center">{{ column.title }}</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(tableField, index) in tableFieldsData" :key="`${tableField.Id}`">
                <td style="width: 250px">
                    <input :id="`tableFieldName${index}`"
                           v-model="tableField.Name"
                           class="form-control form-control-sm"
                           type="text"
                           @input="nameChanged(index)"
                    >
                </td>
                <td style="width: 115px">
                    <Select :id="`tableFieldType${index}`"
                            v-model="tableField.Type"
                            :options="typeOptions"
                            :select-class="`form-select-sm`"
                            @change="typeChanged(index)"
                    >
                    </Select>
                </td>
                <td style="width: 200px;">
                    <div class="input-group">
                        <PopOverButton
                            btnClass="btn"
                            content="enums must be formatted like: enum('A','B','C')."
                            iconClass="si si-info"
                        ></PopOverButton>
                        <input
                            :id="`tableFieldDataType${index}`"
                            v-model="tableField.DataType"
                            aria-label="Select Datatype"
                            class="form-control form-control-sm"
                            placeholder=""
                            type="text"
                            @input="dataTypeChangedFromInput($event.target.value, index)"
                        />
                        <button
                            aria-expanded="false"
                            aria-haspopup="true"
                            class="btn btn-primary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown"
                            type="button"
                        >
                            <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a v-for="dataTypeOption in dataTypesOptions" class="dropdown-item"
                               @click="selectDataTypeFromDropdown(dataTypeOption, index)">
                                {{ dataTypeOption }}
                            </a>
                        </div>
                    </div>
                </td>
                <td>
                    <input v-if="['int', 'varchar', 'tinyint'].includes(tableField.DataType)"
                           :id="`tableFieldLength${index}`"
                           v-model.number="tableField.Length"
                           class="form-control form-control-sm"
                           min="1" type="number" @input="lengthChange(index)">
                </td>
                <td>
                    <input :id="`tableFieldDefaultValue${index}`"
                           v-model="tableField.DefaultValue"
                           class="form-control form-control-sm"
                           type="text"
                           @input="defaultValueChanged(index)"
                    >
                </td>
                <td class="text-center">
                    <input v-model="tableField.Nullable" :name="`tableFieldNullable${index}`"
                           class="form-check-input" type="checkbox" @change="nullableValueChanged(index)">
                </td>
                <td class="text-center">
                    <input v-model="tableField.PrimaryKey" :name="`tableFieldPrimaryKey${index}`"
                           class="form-check-input" type="checkbox" @change="primaryKeyValueChanged(index)">
                </td>
                <td class="text-center">
                    <input v-model="tableField.AutoIncrement" :name="`tableFieldAutoIncrement${index}`"
                           class="form-check-input" type="checkbox" @change="autoIncrementValueChanged(index)">
                </td>
                <td class="text-center">
                    <input v-model="tableField.Unique" :name="`tableFieldUnique${index}`"
                           class="form-check-input" type="checkbox" @change="uniqueValueChanged(index)">
                </td>
                <td style="width: 100px">
                    <input :id="`tableFieldSortOrder${index}`"
                           v-model.number="tableField.SortOrder"
                           class="form-control form-control-sm"
                           type="number"
                           @input="sortOrderChanged(index)"
                    >
                </td>
                <td>
                    <VueSelect
                        v-if="(!tableField.isGeneralField) && (props.table.Database !== `Office`)"
                        v-model="tableField.companies"
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
                <td class="text-center">
                    <button class="btn rounded-pill btn-alt-danger me-1" type="button"
                            @click="deleteField(index)">
                        <i class="fa fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
/*.tableFixHead {
    !*width: 500px;*!
    table-layout: fixed;
    border-collapse: collapse;
}

.tableFixHead tbody {
    display: block;
    width: 100%;
    overflow: auto;
    height: 65vh;
}

.tableFixHead thead tr {
    display: block;
}

.tableFixHead th,
.tableFixHead td {
    padding: 5px 10px;
    width: 200px;
}*/

</style>
