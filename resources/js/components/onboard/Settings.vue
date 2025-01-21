<template>
    <h4 class="d-flex justify-content-between">
        {{ step.Title }}
        <div>
            <button class="btn btn-outline-success btn-sm" :disabled="isLoading" @click.prevent="update">Update</button>
        </div>
    </h4>
    <div class="form-wrapper">
        <div class="row mb-3" v-for="setting in settings">
            <label :for="`${setting.Name}`"
                   class="col-sm-3 col-form-label col-form-label-sm label-text text-break">
                {{ setting.Name }}
            </label>
            <div class="col-sm-9">
                <select v-if="setting.DataType === `Boolean`" :id="`${setting.Name}`"
                        v-model="setting.Value"
                        class="form-select form-select-sm">
                    <option value="">Select {{ setting.Name }}</option>
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                </select>

                <select v-else-if="setting.DataType === `Enum`" :id="`${setting.Name}`"
                        v-model="setting.Value"
                        class="form-select form-select-sm">
                    <option value="">Select {{ setting.Name }}</option>
                    <option v-for="option in setting.EnumOptions"
                            :value="option">{{ option }}
                    </option>
                </select>

                <input v-else-if="setting.DataType === `Int32`" :id="`${setting.Name}`"
                       v-model.number="setting.Value"
                       :name="`${setting.Name}`"
                       class="form-control form-control-sm"
                       step="0" type="number"/>

                <input v-else-if="setting.DataType === `Double`" :id="`${setting.Name}`"
                       v-model.number="setting.Value"
                       :name="`${setting.Name}`"
                       class="form-control form-control-sm"
                       step="any" type="number"/>

                <JsonEditorVue v-else-if="setting.DataType === `String` && setting.IsJson"
                               v-model="setting.Value"
                               :class="templateStore.settings.darkMode ? `jse-theme-dark` : ``"
                               :main-menu-bar="false"
                               :status-bar="false"
                               mode="text"
                >
                </JsonEditorVue>

                <input v-else :id="`${setting.Name}`" v-model="setting.Value"
                       :name="`${setting.Name}`"
                       class="form-control form-control-sm"
                       type="text"/>
            </div>
        </div>
    </div>
</template>
<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import _ from "lodash";
import Button from "@/components/ui/Button.vue";
import ModuleSetting from "@/models/Office/ModuleSetting";
import JsonEditorVue from "json-editor-vue";
import {useTemplateStore} from "@/stores/templateStore";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();
const templateStore = useTemplateStore();

const isLoading = ref(false)
const settings = ref([])

onMounted(() => {
    getModuleSettings();
});

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getModuleSettings();
    }
});

const getModuleSettings = async () => {
    try {
        isLoading.value = true

        let {data} = await ModuleSetting.getModuleSettingsByName(companyStore.selectedCompany.Id, props.step.payload.settings);
        settings.value = data

        for (let index in settings.value) {
            if (!settings.value[index]['Value']) {
                break
            }

            if (index == (settings.value.length - 1)) {
                emits("complete", true)
            }
        }

        isLoading.value = false
    } catch (err) {

        isLoading.value = false
    }
}

const update = async () => {
    try {
        isLoading.value = true

        let {data} = await ModuleSetting.updateModuleSettings(companyStore.selectedCompany.Id, settings.value);

        isLoading.value = false

        getModuleSettings()
    } catch (err) {

        isLoading.value = false
    }
}
</script>

<style scoped>
.form-wrapper {
    max-height: 60vh;
    height: 100%;
    overflow: auto;
}
</style>
