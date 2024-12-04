<template>
    <h4 class="">Enable Modules</h4>
    <div class="card mb-2" v-if="modules" v-for="(module, moduleName) in modules">
        <div class="card-body p-3 d-flex justify-content-between">
            <div>{{ moduleName }}</div>
            <div>
                <small class="text-success" v-if="module.Installed === true">
                    <i class="fa fa-check"></i> Active
                </small>
                <small class="text-muted" v-else>
                    <i class="fa fa-circle-notch fa-spin rounded-circle p-1"></i>
                    Activating
                </small>
            </div>
        </div>
    </div>
</template>
<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import Module from "@/models/Office/Module";
import _ from "lodash";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

const modules = ref(null);
async function getCompanyModules() {
    let {data} = await Module.getActivatedAndAvailableModulesByCompany(companyStore.selectedCompany.Id);

    if (props.step.payload.modules && props.step.payload.modules.length > 0) {
        const temp = {}
        for(let module of data.installedModules) {
            if(props.step.payload.modules.includes(module.Name)) {
                temp[module.Name] = module
                temp[module.Name]["Installed"] = true
            }
        }
        for(let module of data.availableModules) {
            if(props.step.payload.modules.includes(module.Name)) {
                temp[module.Name] = module
                temp[module.Name]["Installed"] = false
                setTimeout(() => activateModule(module), 10000)
            }
        }

        modules.value = temp
    }
}

async function activateModule(module) {
    module["InstallSubModules"] = true
    module["CreateTables"] = true

    await Module.activateModule(companyStore.selectedCompany.Id, module);
    modules.value[module.Name]["Installed"] = true
}

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getCompanyModules();
    }
});

watch(modules, (modifiedVal) => {
    if(!Object.values(modifiedVal).map(module => module.Installed).includes(false)) {
        emits("complete", true)
    }
})

onMounted(() => {
    if (companyStore.getSelectedCompany.Id) {
        getCompanyModules();
    }
});
</script>
