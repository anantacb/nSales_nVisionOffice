<template>
    <h4 class="d-flex justify-content-between">
        Enable Modules
        <button class="btn btn-sm btn-outline-success"
                v-if="notInstalledModules && notInstalledModules.length > 0"
                :disabled="isLoading"
                @click.prevent="activateNotInstalledModules"
        >
            Activate Modules
        </button>
    </h4>
    <div class="card mb-2" v-if="modules" v-for="(module, moduleName) in modules">
        <div class="card-body p-3 d-flex justify-content-between">
            <div>{{ moduleName }}</div>
            <div>
                <small class="text-success" v-if="module.Installed === true">
                    <i class="fa fa-check"></i> Active
                </small>
                <small class="text-muted" v-else-if="isLoading">
                    <i class="fa fa-circle-notch fa-spin rounded-circle p-1"></i>
                    Activating
                </small>
                <small class="text-danger" v-else>
                    Not active
                </small>
            </div>
        </div>
    </div>
</template>
<script setup>
import {computed, defineProps, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import Module from "@/models/Office/Module";
import _ from "lodash";
import Button from "@/components/ui/Button.vue";

const props = defineProps(['name', 'step']);
const emits = defineEmits(['complete']);

const companyStore = useCompanyStore();

const modules = ref(null);

const isLoading = ref(false)
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
            }
        }

        modules.value = temp
    }
}

const notInstalledModules = computed(() => {
    if(modules.value) {
        return Object.values(modules.value).filter(module => !module.Installed)
    }
    return []
})

const activateNotInstalledModules = () => {
    isLoading.value = true
    const promises = []
    for (let module of notInstalledModules.value) {
        promises.push(activateModule(module))
    }

    Promise.all(promises).then(() => {
        emits("complete", true)
    })
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

watch(notInstalledModules, (modifiedVal) => {
    if(!modifiedVal || !modifiedVal.length) {
        emits("complete", true)
    }
})

onMounted(() => {
    if (companyStore.getSelectedCompany.Id) {
        getCompanyModules();
    }
});
</script>
