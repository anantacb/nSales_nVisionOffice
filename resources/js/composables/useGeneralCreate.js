import {ref} from "vue";
import TableField from "@/models/Office/TableField";
import _ from "lodash";
import Table from "@/models/Office/Table";
import {useCompanyStore} from "@/stores/companyStore";

export default function useGeneralCreate() {
    const ModelObject = ref({});
    const GroupedTableFields = ref({});

    const TableModel = ref({});

    async function getTableDetails(TableName) {
        let {data} = await Table.getDetailsByName(TableName);
        TableModel.value = data;
    }

    const ExceptColumns = ref(['Id', 'InsertTime', 'UpdateTime', 'DeleteTime', 'UUID', 'ImportTime']);

    function setExceptColumns(value) {
        ExceptColumns.value = value;
    }

    const OverriddenProperties = ref({});

    function setOverriddenProperties(value) {
        OverriddenProperties.value = value;
    }

    const NullHeaderText = ref('Info');

    function setNullHeaderText(value) {
        NullHeaderText.value = value;
    }

    const CustomSortKeys = ref(['General', 'Account', 'Sales', 'Contact', 'Misc', 'Info']);

    function setCustomSortKeys(value) {
        CustomSortKeys.value = value;
    }

    async function getCompanyAllTableFields() {
        const companyStore = useCompanyStore();
        let {data} = await TableField.getCompanyAllTableFields(TableModel.value.Id, companyStore.selectedCompany.Id);

        let TableFields = [];

        data.generalTableFields.map((item) => {
            if (!ExceptColumns.value.includes(item.Name)) {
                item.isCompanySpecific = false;
                let formattedTableField = getFormattedTableField(item);
                TableFields.push(formattedTableField);
                ModelObject.value[formattedTableField.Name] = formattedTableField.DefaultValue;
            }
        });
        data.companySpecificTableFields.forEach((item) => {
            item.isCompanySpecific = true;
            let formattedTableField = getFormattedTableField(item);
            TableFields.push(formattedTableField);
            ModelObject.value[formattedTableField.Name] = formattedTableField.DefaultValue;
        });

        const UnFormattedGroupedTableFields = _.groupBy(TableFields, 'GroupName');
        const FormattedGroupedTableFields = {};
        const SortedFormattedTableFields = {};

        Object.keys(UnFormattedGroupedTableFields).map((key) => {
            if (key !== 'null' && key) {
                FormattedGroupedTableFields[key] = UnFormattedGroupedTableFields[key];
            } else {
                if (!FormattedGroupedTableFields.hasOwnProperty('Info')) {
                    FormattedGroupedTableFields.Info = [];
                }
                UnFormattedGroupedTableFields[key].forEach((item) => {
                    FormattedGroupedTableFields.Info.push(item);
                });
            }
        });

        const customSortKeys = CustomSortKeys.value; // Keys to be sorted in a custom order
        const remainingKeys = Object.keys(FormattedGroupedTableFields).filter((key) => !customSortKeys.includes(key));
        customSortKeys.forEach((key) => {
            if (FormattedGroupedTableFields.hasOwnProperty(key)) {
                SortedFormattedTableFields[key] = FormattedGroupedTableFields[key];
            }
        });
        remainingKeys.forEach((key) => {
            SortedFormattedTableFields[key] = FormattedGroupedTableFields[key];
        });

        GroupedTableFields.value = SortedFormattedTableFields;
    }

    function getFormattedTableField(TableField) {
        const OverriddenPropertiesValue = OverriddenProperties.value;

        if (/enum\(.*\)/.test(TableField.DataType)) {
            TableField.SelectOptions = [{
                label: 'Please Select',
                value: ""
            }];
            TableField.DataType.slice(5, TableField.DataType.length - 1).split(',').map((value) => {
                TableField.SelectOptions.push({
                    label: value.slice(1, value.length - 1),
                    value: value.slice(1, value.length - 1)
                });
            });
        }

        if (OverriddenPropertiesValue.hasOwnProperty(TableField.Name)) {
            Object.keys(OverriddenPropertiesValue[TableField.Name]).forEach((key) => {
                TableField[key] = OverriddenPropertiesValue[TableField.Name][key];
            });
        }

        return TableField;
    }

    return {
        ModelObject,
        GroupedTableFields,
        setOverriddenProperties,
        setExceptColumns,
        setCustomSortKeys,
        setNullHeaderText,
        getTableDetails,
        getCompanyAllTableFields
    };
}
