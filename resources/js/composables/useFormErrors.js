import {ref} from "vue";

export function useFormErrors() {

    let errors = ref({});

    function setErrors(errs) {
        errors.value = errs;
    }

    function resetErrors() {
        errors.value = {};
    }

    return {errors, setErrors, resetErrors};
}
