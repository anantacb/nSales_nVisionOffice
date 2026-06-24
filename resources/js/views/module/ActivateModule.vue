<script setup>
import Draggable from "vuedraggable";
import {onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import {useTemplateStore} from "@/stores/templateStore";
import {useNotificationStore} from "@/stores/notificationStore";
import Module from "@/models/Office/Module";
import Swal from "sweetalert2";
import _ from "lodash";

const companyStore = useCompanyStore();
const templateStore = useTemplateStore();
const notificationStore = useNotificationStore();

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getCompanyModules();
    }
});

onMounted(() => {
    if (companyStore.getSelectedCompany.Id) {
        getCompanyModules();
    }
});

let installedModules = ref([]);
let availableModules = ref([]);

let activateModuleRef = ref(null);

async function getCompanyModules() {
    let {data} = await Module.getActivatedAndAvailableModulesByCompany(companyStore.selectedCompany.Id);
    installedModules.value = data.installedModules;
    availableModules.value = data.availableModules;
}

async function installModule(event) {
    let module = installedModules.value[event.newIndex];
    if (module.HasSubModules) {
        // Has Sub Modules
        // Ask if it wants to install all submodules
        Swal.fire({
            title: 'Do you want to install all Sub Moules?',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: `No`,
        }).then(async (subModuleResult) => {
            if (subModuleResult.isConfirmed) {
                // Want to install Sub Modules
                if (module.HasSubModuleTables || module.HasTables) {
                    Swal.fire({
                        title: 'Do you want to create tables?',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        denyButtonText: `No`,
                    }).then(async (tableResult) => {
                        if (tableResult.isConfirmed) {
                            module.InstallSubModules = true;
                            module.CreateTables = true;
                            await activateModule(module);
                        } else if (tableResult.isDenied) {
                            module.InstallSubModules = true;
                            module.CreateTables = false;
                            await activateModule(module);
                        } else if (tableResult.isDismissed) {
                            resetModules(event.newIndex, 'installed');
                        }
                    });
                } else {
                    module.InstallSubModules = true;
                    module.CreateTables = false;
                    await activateModule(module);
                }
            } else if (subModuleResult.isDenied) {
                // Don't Want to install Sub Modules
                if (module.HasTables) {
                    Swal.fire({
                        title: 'Do you want to create tables?',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        denyButtonText: `No`,
                    }).then(async (tableResult) => {
                        if (tableResult.isConfirmed) {
                            module.InstallSubModules = false;
                            module.CreateTables = true;
                            await activateModule(module);
                        } else if (tableResult.isDenied) {
                            module.InstallSubModules = false;
                            module.CreateTables = false;
                            await activateModule(module);
                        } else if (tableResult.isDismissed) {
                            resetModules(event.newIndex, 'installed');
                        }
                    });
                } else {
                    module.InstallSubModules = false;
                    module.CreateTables = false;
                    await activateModule(module);
                }
            } else if (subModuleResult.isDismissed) {
                resetModules(event.newIndex, 'installed');
            }
        });
    } else {
        // Does not Have Sub Modules
        if (module.HasTables) {
            // Ask if it wants to create tables
            Swal.fire({
                title: 'Do you want to create tables?',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Yes',
                denyButtonText: `No`,
            }).then(async (tableResult) => {
                if (tableResult.isConfirmed) {
                    module.InstallSubModules = false;
                    module.CreateTables = true;
                    await activateModule(module);
                } else if (tableResult.isDenied) {
                    module.InstallSubModules = false;
                    module.CreateTables = false;
                    await activateModule(module);
                } else if (tableResult.isDismissed) {
                    resetModules(event.newIndex, 'installed');
                }
            });
        } else {
            module.InstallSubModules = false;
            module.CreateTables = false;
            await activateModule(module);
        }
    }
}

async function removeModule(event) {
    let module = availableModules.value[event.newIndex];

    if (module.HasSubModules) {
        // Has Sub Modules
        // Ask if it wants to uninstall all submodules
        Swal.fire({
            title: 'Do you want to uninstall all Sub Moules?',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'Yes',
            denyButtonText: `No`,
        }).then(async (subModuleResult) => {
            if (subModuleResult.isConfirmed) {
                // Want to uninstall Sub Modules
                if (module.HasSubModuleTables || module.HasTables) {
                    Swal.fire({
                        title: 'Do you want to delete tables?',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        denyButtonText: `No`,
                    }).then(async (tableResult) => {
                        if (tableResult.isConfirmed) {
                            module.UnInstallSubModules = true;
                            module.DeleteTables = true;
                            await deactivateModule(module);
                        } else if (tableResult.isDenied) {
                            module.UnInstallSubModules = true;
                            module.deleteTables = false;
                            await deactivateModule(module);
                        } else if (tableResult.isDismissed) {
                            resetModules(event.newIndex, 'installed');
                        }
                    });
                } else {
                    module.UnInstallSubModules = true;
                    module.DeleteTables = false;
                    await deactivateModule(module);
                }
            } else if (subModuleResult.isDenied) {
                // Don't Want to uninstall Sub Modules
                if (module.HasTables) {
                    Swal.fire({
                        title: 'Do you want to delete tables?',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                        denyButtonText: `No`,
                    }).then(async (tableResult) => {
                        if (tableResult.isConfirmed) {
                            module.UnInstallSubModules = false;
                            module.DeleteTables = true;
                            await deactivateModule(module);
                        } else if (tableResult.isDenied) {
                            module.UnInstallSubModules = false;
                            module.DeleteTables = false;
                            await deactivateModule(module);
                        } else if (tableResult.isDismissed) {
                            resetModules(event.newIndex, 'available');
                        }
                    });
                } else {
                    module.UnInstallSubModules = false;
                    module.DeleteTables = false;
                    await deactivateModule(module);
                }
            } else if (subModuleResult.isDismissed) {
                resetModules(event.newIndex, 'available');
            }
        });
    } else {
        // Does not Have Sub Modules
        if (module.HasTables) {
            // Ask if it wants to delete tables
            Swal.fire({
                title: 'Do you want to delete tables?',
                showDenyButton: true,
                showCancelButton: true,
                confirmButtonText: 'Yes',
                denyButtonText: `No`,
            }).then(async (tableResult) => {
                if (tableResult.isConfirmed) {
                    module.UnInstallSubModules = false;
                    module.DeleteTables = true;
                    await deactivateModule(module);
                } else if (tableResult.isDenied) {
                    module.UnInstallSubModules = false;
                    module.DeleteTables = false;
                    await deactivateModule(module);
                } else if (tableResult.isDismissed) {
                    resetModules(event.newIndex, 'available');
                }
            });
        } else {
            module.UnInstallSubModules = false;
            module.DeleteTables = false;
            await deactivateModule(module);
        }
    }
}

async function activateModule(module) {
    activateModuleRef.value.statusLoading();
    let data = await Module.activateModule(companyStore.selectedCompany.Id, module);
    notificationStore.showNotification(data.message);
    await getCompanyModules();
    activateModuleRef.value.statusNormal();
}

async function deactivateModule(module) {
    activateModuleRef.value.statusLoading();
    let data = await Module.deactivateModule(companyStore.selectedCompany.Id, module);
    notificationStore.showNotification(data.message);
    await getCompanyModules();
    activateModuleRef.value.statusNormal();
}

function resetModules(newIndex, removeFrom = 'available') {
    let module;
    if (removeFrom === 'installed') {
        module = installedModules.value[newIndex];
        availableModules.value.push(module);
        installedModules.value.splice(newIndex, 1);
    } else {
        module = availableModules.value[newIndex];
        installedModules.value.push(module);
        availableModules.value.splice(newIndex, 1);
    }
    installedModules.value = _.sortBy(installedModules.value, `Name`);
    availableModules.value = _.sortBy(availableModules.value, `Name`);
}
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock
            ref="activateModuleRef"
            :title="`Activate Module for ${companyStore.selectedCompany.Name}`"
            content-full
        >
            <div class="row">
                <div class="col-md-6">
                    <h4 class="text-center">Available Modules</h4>
                    <Draggable
                        :list="availableModules"
                        :sort="false"
                        class="module-chip-list scrollable-65vh"
                        group="modules"
                        itemKey="Id"
                        @add="removeModule"
                    >
                        <template #item="{ element, index }">
                            <div :class="{'module-chip--sub' : element.HasSubModules}" class="module-chip">
                                {{ element.Name }}
                            </div>
                        </template>
                    </Draggable>
                </div>

                <div class="col-md-6">
                    <h4 class="text-center">Installed Modules</h4>
                    <Draggable
                        :list="installedModules"
                        :sort="false"
                        class="module-chip-list scrollable-65vh"
                        group="modules"
                        itemKey="Id"
                        @add="installModule"
                    >
                        <template #item="{ element, index }">
                            <div :class="{'module-chip--sub' : element.HasSubModules}" class="module-chip">
                                {{ element.Name }}
                            </div>
                        </template>
                    </Draggable>
                </div>

            </div>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>

<style scoped>
.module-chip-list {
    display: flex;
    flex-wrap: wrap;
    align-content: flex-start;
    gap: 0.5rem;
    padding: 0.75rem;
    min-height: 6rem;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
}

.module-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.8rem;
    font-size: 0.8rem;
    line-height: 1.2;
    border-radius: 50rem;
    border: 1px solid #cbd5e1;
    background-color: #f8fafc;
    color: #334155;
    cursor: grab;
    user-select: none;
    transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
}

.module-chip:hover {
    border-color: var(--bs-primary, #0d6efd);
    background-color: #eef2ff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}

.module-chip:active {
    cursor: grabbing;
}

/* Modules that carry sub-modules get an info accent. */
.module-chip--sub {
    border-color: #38bdf8;
    background-color: #e0f2fe;
    color: #0369a1;
}

/* Dark mode — the theme doesn't adapt these surfaces automatically. */
.dark-mode .module-chip-list {
    border-color: rgba(255, 255, 255, 0.12);
}

.dark-mode .module-chip {
    border-color: rgba(255, 255, 255, 0.18);
    background-color: rgba(255, 255, 255, 0.06);
    color: #e9ecef;
}

.dark-mode .module-chip:hover {
    border-color: var(--bs-primary, #0d6efd);
    background-color: rgba(13, 110, 253, 0.22);
}

.dark-mode .module-chip--sub {
    border-color: #0c91cf;
    background-color: rgba(12, 145, 207, 0.22);
    color: #7dd3fc;
}
</style>
