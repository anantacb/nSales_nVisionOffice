import {ref} from "vue";
import TableHelper from "@/models/TableHelper";

export function useCompanyEmailTemplate() {

    const ColumnWiseValueOptions = ref({
        "Type": [
            {value: 'Order', label: 'Order'},
            {value: 'WebShop', label: 'WebShop'},
            {value: 'ReturnOrder', label: 'ReturnOrder'}
        ],
        "Status": [
            {value: 'Open', label: 'Open'},
            {value: 'Sent', label: 'Sent'},
            {value: 'Closed', label: 'Closed'}
        ],
        "DocumentType": [
            {value: 'Order', label: 'Order'},
            {value: 'ReturnOrder', label: 'ReturnOrder'},
            {value: 'DraftOrder', label: 'DraftOrder'}
        ],
        "DocumentStatus": [
            {value: 'Open', label: 'Open'},
            {value: 'Sent', label: 'Sent'},
            {value: 'Pending', label: 'Pending'},
            {value: 'Closed', label: 'Closed'}
        ],
    });

    async function getDistinctColumnValues(TableName, ColumnName, CompanyId) {
        let {data, message} = await TableHelper.getColumnDistinctValues(
            'Company', TableName, ColumnName, CompanyId
        );

        let columnValueOptions = [
            {label: 'Please Select', value: ''},
            ...data.map(column => ({
                label: column,
                value: column
            }))
        ];

        return setDefaultColumnValueOptions(ColumnName, columnValueOptions);
    }

    function setDefaultColumnValueOptions(columnName, columnValueOptions) {
        if (!columnName || !ColumnWiseValueOptions.value?.[columnName]) {
            return columnValueOptions;
        }

        const selectedOptions = ColumnWiseValueOptions.value[columnName];
        const existingValues = new Set(
            columnValueOptions.map(opt => opt.value)
        );

        return [
            ...columnValueOptions,
            ...selectedOptions.filter(
                opt => !existingValues.has(opt.value)
            )
        ];
    }

    return {
        getDistinctColumnValues,
        setDefaultColumnValueOptions
    };
}
