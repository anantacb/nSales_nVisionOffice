import {ref} from "vue";
import moment from "moment/moment";

export function useFormatter() {

    let localesForNumber = ref('en'); // 'en-US'
    let optionsForNumber = ref({
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    function numberFormat(value) {
        return new Intl.NumberFormat(localesForNumber.value, optionsForNumber.value).format(value);
        //return  parseFloat(value).toFixed(2);
    }

    function dateFormat(date, formatter = "DD-MM-YYYY") {
        return moment(date).format(formatter);
    }


    return {numberFormat, dateFormat};
}
