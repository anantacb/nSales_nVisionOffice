<script setup>
import {useCompanyStore} from "@/stores/companyStore";
import {onMounted, ref, watch} from "vue";
import ModuleSetting from "@/models/Office/ModuleSetting";
import BaseBlock from "@/components/BaseBlock.vue";
import Swal from "sweetalert2";
import JsonEditorVue from 'json-editor-vue';
import {useTemplateStore} from "@/stores/templateStore";
import {useNotificationStore} from "@/stores/notificationStore";

const companyStore = useCompanyStore();
const templateStore = useTemplateStore();
const notificationStore = useNotificationStore();

watch(() => companyStore.getSelectedCompany, () => {
    updatedSettings.value = [];
    getModuleSettings();
});

onMounted(() => {
    if (companyStore.getSelectedCompany.Id) {
        getModuleSettings();
    }
});

let moduleSettings = ref({});
let moduleSettingsRef = ref(null);
let initialModuleSettings = {};

async function getModuleSettings() {
    moduleSettingsRef.value.statusLoading();
    let {data} = await ModuleSetting.getModuleSettingsByCompany(companyStore.selectedCompany.Id);
    moduleSettings.value = data;
    initialModuleSettings = JSON.parse(JSON.stringify(data));
    moduleSettingsRef.value.statusNormal();
}

let updatedSettings = ref([]);

async function update() {
    if (_.isEmpty(updatedSettings.value)) {
        await Swal.fire({
            position: 'center',
            icon: 'warning',
            title: 'No changes to save.',
            showConfirmButton: false,
            timer: 1500
        });
        return;
    }
    moduleSettingsRef.value.statusLoading();

    let {data, message} = await ModuleSetting.updateModuleSettings(
        companyStore.selectedCompany.Id,
        updatedSettings.value
    );

    notificationStore.showNotification(message);

    updatedSettings.value = [];

    moduleSettingsRef.value.statusNormal();

    await getModuleSettings();
}

function selectValueChanged(key, settingIndex) {
    updateUpdatedSettingsList(key, settingIndex);
}

function jsonValueChanged(key, settingIndex) {
    if (JSON.stringify(initialModuleSettings[key][settingIndex].Value) !== JSON.stringify(JSON.parse(moduleSettings.value[key][settingIndex].Value))) {
        addToUpdatedList(key, settingIndex);
    } else {
        removeFromUpdatedList(key, settingIndex);
    }
}

function updateUpdatedSettingsList(key, settingIndex) {
    if (initialModuleSettings[key][settingIndex].Value !== moduleSettings.value[key][settingIndex].Value) {
        addToUpdatedList(key, settingIndex);
    } else {
        removeFromUpdatedList(key, settingIndex);
    }
}

const inputChanged = _.debounce((key, settingIndex) => {
    updateUpdatedSettingsList(key, settingIndex);
}, 500);

function addToUpdatedList(key, settingIndex) {
    let index = updatedSettings.value.findIndex(item => item.Id === moduleSettings.value[key][settingIndex].Id);
    // Exists in updatedSettings
    if (index !== -1) {
        updatedSettings.value.splice(index, 1);
    }
    updatedSettings.value.push(moduleSettings.value[key][settingIndex]);
}

function removeFromUpdatedList(key, settingIndex) {
    let index = updatedSettings.value.findIndex(item => item.Id === moduleSettings.value[key][settingIndex].Id);
    // Exists in updatedSettings
    if (index !== -1) {
        updatedSettings.value.splice(index, 1);
    }
}
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock ref="moduleSettingsRef" class="row g-0"
                   title="Setting"
        >
            <template #options>
                <button class="btn btn-sm btn-outline-primary me-1" type="button" @click="update">
                    Save
                </button>
            </template>
            <template #content>
                <div class="col-md-2 block-header-default scrollable">
                    <ul
                        class="nav nav-tabs nav-tabs-block flex-md-column"
                        role="tablist"
                    >
                        <li v-for="(settings, key, index) of moduleSettings" class="nav-item d-md-flex flex-md-column">
                            <button
                                :id="`btabs-vertical-home-tab-${key}-${index}`"
                                :key="`tab-button-${companyStore.selectedCompany.Id}-${key}-${index}`"
                                :class="index === 0 ? `active` : ``"
                                :data-bs-target="`#${key}`"
                                aria-controls="btabs-vertical-home"
                                aria-selected="true"
                                class="nav-link text-md-start"
                                data-bs-toggle="tab"
                                role="tab"
                            >
                                {{ key }}
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="tab-content col-md-10 scrollable">
                    <div v-for="(settings, key, index) of moduleSettings"
                         :id="`${key}`"
                         :key="`tab-content-${companyStore.selectedCompany.Id}-${key}-${index}`"
                         :aria-labelledby="`btabs-vertical-home-tab-${key}-${index}`"
                         :class="index === 0 ? `active` : ``"
                         class="block-content tab-pane space-y-1 mb-2"
                         role="tabpanel"
                         tabindex="0"
                    >
                        <div v-for="(setting, settingIndex) in settings" class="row">
                            <label :for="`${key}-${setting.Name}`" class="col-sm-3 col-form-label col-form-label-sm">
                                {{ setting.Name }}
                            </label>
                            <div class="col-sm-9">
                                <select v-if="setting.DataType === `Boolean`" :id="`${key}-${setting.Name}`"
                                        v-model="setting.Value"
                                        class="form-select form-select-sm"
                                        @change="selectValueChanged(key, settingIndex)">
                                    <option value="">Select {{ setting.Name }}</option>
                                    <option value="true">Yes</option>
                                    <option value="false">No</option>
                                </select>

                                <select v-else-if="setting.DataType === `Enum`" :id="`${key}-${setting.Name}`"
                                        v-model="setting.Value"
                                        class="form-select form-select-sm"
                                        @change="selectValueChanged(key, settingIndex)">
                                    <option value="">Select {{ setting.Name }}</option>
                                    <option v-for="option in setting.EnumOptions"
                                            :value="option">{{ option }}
                                    </option>
                                </select>

                                <input v-else-if="setting.DataType === `Int32`" :id="`${key}-${setting.Name}`"
                                       v-model.number="setting.Value"
                                       :name="`${key}-${setting.Name}`"
                                       class="form-control form-control-sm"
                                       step="0" type="number" @input="inputChanged(key, settingIndex)"/>

                                <input v-else-if="setting.DataType === `Double`" :id="`${key}-${setting.Name}`"
                                       v-model.number="setting.Value"
                                       :name="`${key}-${setting.Name}`"
                                       class="form-control form-control-sm"
                                       step="any" type="number" @input="inputChanged(key, settingIndex)"/>

                                <JsonEditorVue v-else-if="setting.DataType === `String` && setting.IsJson"
                                               v-model="setting.Value"
                                               :class="templateStore.settings.darkMode ? `jse-theme-dark` : ``"
                                               :main-menu-bar="false"
                                               :status-bar="false"
                                               mode="text"
                                               @update:modelValue="jsonValueChanged(key, settingIndex)"
                                >
                                </JsonEditorVue>

                                <input v-else :id="`${key}-${setting.Name}`" v-model="setting.Value"
                                       :name="`${key}-${setting.Name}`"
                                       class="form-control form-control-sm"
                                       type="text" @input="inputChanged(key, settingIndex)"/>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>

<style scoped>
.scrollable {
    height: 75vh;
    overflow: auto;
}
</style>
