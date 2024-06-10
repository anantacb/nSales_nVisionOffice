<script setup>
import {booleanOptions} from "@/data/dropDownOptions";
import FlatPicker from "vue-flatpickr-component";
import _ from "lodash";

const props = defineProps({
    GroupedTableFields: {
        type: Object,
        required: true,
        default: () => {
        }
    },
    ModelObject: {
        type: Object,
        required: true,
        default: () => {
        }
    },
    Errors: {
        type: Object,
        required: false,
        default: () => {
        }
    },
    FormType: {
        type: String,
        required: false,
        default: () => {
            return "Save";
        }
    }
});

function formAction() {
    emit('formAction', props.ModelObject);
}

function resetErrors() {
    emit('resetErrors');
}

const emit = defineEmits(['formAction', 'resetErrors']);

</script>

<template>
    <form class="space-y-4" @submit.prevent="formAction">

        <div class="row space-y-2">
            <div v-for="(TableFields, GroupName) of props.GroupedTableFields" class="col-lg-4 space-y-2">
                <h5 class="fw-light text-center">{{ GroupName }}</h5>
                <div v-for="TableField in TableFields" class="row">
                    <label :class="{ 'text-info': TableField.isCompanySpecific }" :for="TableField.Name"
                           class="col-sm-4 col-form-label col-form-label-sm">
                        {{ TableField.LabelTitle ?? TableField.Name }}
                        <span v-if="TableField.InputRequired" class="text-danger">*</span>
                        <PopOverButton v-if="TableField.HasTooltip"
                                       :content="TableField.TooltipText"
                                       btnClass="btn btn-sm pt-0 pb-0 pe-0 float-end"
                                       iconClass="si si-info"
                        ></PopOverButton>
                    </label>
                    <div class="col-sm-8">
                        <input
                            v-if="['varchar','tinytext'].includes(TableField.DataType) && _.isEmpty(TableField.SelectOptions)"
                            :id="TableField.Name"
                            v-model="props.ModelObject[TableField.Name]"
                            :class="{'is-invalid' : props.Errors[TableField.Name]}"
                            :disabled="!!TableField.InputLocked"
                            :name="TableField.Name"
                            :required="!!TableField.InputRequired"
                            autocomplete="off"
                            class="form-control form-control-sm"
                            type="text"
                            @keyup="resetErrors"/>

                        <Select
                            v-else-if="['varchar','tinytext'].includes(TableField.DataType) && _.isEmpty(TableField.SelectOptions)"
                            :id="TableField.Name"
                            id="Disabled"
                            v-model="props.ModelObject[TableField.Name]"
                            :disabled="!!TableField.InputLocked"
                            :name="TableField.Name"
                            :options="TableField.SelectOptions"
                            :required="!!TableField.InputRequired"
                            :select-class="props.Errors[TableField.Name] ? `is-invalid form-select-sm` : `form-select-sm`"
                            name="Disabled"
                        />

                        <input v-else-if="['int'].includes(TableField.DataType)"
                               :id="TableField.Name"
                               v-model="props.ModelObject[TableField.Name]"
                               :class="{'is-invalid' : props.Errors[TableField.Name]}"
                               :disabled="!!TableField.InputLocked"
                               :name="TableField.Name"
                               :required="!!TableField.InputRequired"
                               autocomplete="off"
                               class="form-control form-control-sm"
                               type="number"
                               @keyup="resetErrors"/>

                        <input v-else-if="['double'].includes(TableField.DataType)"
                               :id="TableField.Name"
                               v-model="props.ModelObject[TableField.Name]"
                               :class="{'is-invalid' : props.Errors[TableField.Name]}"
                               :disabled="!!TableField.InputLocked"
                               :name="TableField.Name"
                               :required="!!TableField.InputRequired"
                               autocomplete="off"
                               class="form-control form-control-sm"
                               step="any"
                               type="number"
                               @keyup="resetErrors"/>

                        <textarea
                            v-else-if="['longtext','blob','longblob','text'].includes(TableField.DataType)"
                            :id="TableField.Name"
                            v-model="props.ModelObject[TableField.Name]"
                            :class="{'is-invalid' : props.Errors[TableField.Name]}"
                            :disabled="!!TableField.InputLocked"
                            :name="TableField.Name"
                            :required="!!TableField.InputRequired"
                            class="form-control form-control-sm"
                            rows="3"
                        >
                        </textarea>

                        <FlatPicker
                            v-else-if="['timestamp','datetime'].includes(TableField.DataType)"
                            :id="TableField.Name"
                            v-model="props.ModelObject[TableField.Name]"
                            :class="{'is-invalid' : props.Errors[TableField.Name]}"
                            :disabled="!!TableField.InputLocked"
                            :name="TableField.Name"
                            :required="!!TableField.InputRequired"
                            class="form-control form-control-sm"
                            placeholder="Y-m-d"
                        />

                        <Select
                            v-else-if="['tinyint','bool'].includes(TableField.DataType)"
                            :id="TableField.Name"
                            id="Disabled"
                            v-model="props.ModelObject[TableField.Name]"
                            :disabled="!!TableField.InputLocked"
                            :name="TableField.Name"
                            :options="booleanOptions"
                            :required="!!TableField.InputRequired"
                            :select-class="props.Errors[TableField.Name] ? `is-invalid form-select-sm` : `form-select-sm`"
                            name="Disabled"
                        />

                        <Select
                            v-else
                            :id="TableField.Name"
                            id="Disabled"
                            v-model="props.ModelObject[TableField.Name]"
                            :disabled="!!TableField.InputLocked"
                            :name="TableField.Name"
                            :options="TableField.SelectOptions"
                            :required="!!TableField.InputRequired"
                            :select-class="props.Errors[TableField.Name] ? `is-invalid form-select-sm` : `form-select-sm`"
                            name="Disabled"
                        />

                        <InputErrorMessages v-if="props.Errors[TableField.Name]"
                                            :errorMessages="props.Errors[TableField.Name]"></InputErrorMessages>
                    </div>
                </div>
            </div>
        </div>

        <button class="btn btn-outline-primary btn-sm col-2" type="submit">{{ props.FormType }}</button>

    </form>
</template>

<style scoped>

</style>
