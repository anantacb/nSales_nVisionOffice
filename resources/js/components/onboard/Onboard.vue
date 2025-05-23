<script setup>
import {computed, defineAsyncComponent, onMounted, ref, watch} from "vue";
import {useCompanyStore} from "@/stores/companyStore";
import {useTemplateStore} from "@/stores/templateStore";
import {useNotificationStore} from "@/stores/notificationStore";
import _ from "lodash";
import Button from "@/components/ui/Button.vue";
import Onboard from "@/models/Company/Onboard";

const ModuleEnable = defineAsyncComponent(() => import('@/components/onboard/ModuleEnable.vue'))
const LanguageAndTranslations = defineAsyncComponent(() => import('@/components/onboard/LanguageAndTranslations.vue'))
const WebshopUser = defineAsyncComponent(() => import('@/components/onboard/WebshopUser.vue'))
const Item = defineAsyncComponent(() => import('@/components/onboard/Item.vue'))
const PageBuilder = defineAsyncComponent(() => import('@/components/onboard/PageBuilder.vue'))
const Settings = defineAsyncComponent(() => import('@/components/onboard/Settings.vue'))
const Theme = defineAsyncComponent(() => import('@/components/onboard/Theme.vue'))
const DocumentApi = defineAsyncComponent(() => import('@/components/onboard/DocumentApi.vue'))
const GitBranch = defineAsyncComponent(() => import('@/components/onboard/GitBranch.vue'))
const Deploy = defineAsyncComponent(() => import('@/components/onboard/Deploy.vue'))
const Default = defineAsyncComponent(() => import('@/components/onboard/Default.vue'))

const companyStore = useCompanyStore();
const templateStore = useTemplateStore();
const notificationStore = useNotificationStore();

const props = defineProps(["application"]);

const isLoading = ref(false)
const onboardingProgress = ref(null)

const getCompanyOnboardStatus = async () => {
    try {
        isLoading.value = true

        let {data} = await Onboard.getCompanyOnboardStatus(companyStore.selectedCompany.Id, props.application);
        onboardingProgress.value = data

        setCurrentState()

        isLoading.value = false
    } catch (err) {
        notificationStore.showNotification(err.response.data.message, "error")
        isLoading.value = false
    }
}

const currentStep = ref("")
const isCurrentStepComplete = ref(false)

const setCurrentState = () => {
    if (onboardingProgress.value) {
        for (let moduleName of Object.keys(onboardingProgress.value)) {
            if (onboardingProgress.value[moduleName]["IsCompleted"]) {
                currentStep.value = moduleName
                isCurrentStepComplete.value = false
                break;
            }
        }

        if(!currentStep.value) {
            currentStep.value = Object.keys(onboardingProgress.value)[0]
            isCurrentStepComplete.value = false
        }
    }
}

const previousStep = computed(() => {
    if (onboardingProgress.value && onboardingProgress.value[currentStep.value]) {
        const steps = Object.keys(onboardingProgress.value)
        if (steps.indexOf(currentStep.value) > 0) {
            return steps[steps.indexOf(currentStep.value) - 1]
        }
    }
    return null
})

const nextStep = computed(() => {
    if (onboardingProgress.value && onboardingProgress.value[currentStep.value]) {
        const steps = Object.keys(onboardingProgress.value)
        if (steps.indexOf(currentStep.value) < (steps.length - 1)) {
            return steps[steps.indexOf(currentStep.value) + 1]
        }
    }
    return null
})

const gotoStep = (step) => {
    currentStep.value = step
    isCurrentStepComplete.value = false
}

const setCurrentStepStatus = async(isComplete) => {
    isCurrentStepComplete.value = isComplete
    if(isCurrentStepComplete.value) {
        onboardingProgress.value[currentStep.value]["IsCompleted"] = 1
        await updateCompanyOnboardStatus()
    }
}

const updateCompanyOnboardStatus = async () => {
    try {
        isLoading.value = true
        await Onboard.updateCompanyOnboardStatus(companyStore.selectedCompany.Id, props.application, onboardingProgress.value);
        isLoading.value = false
    } catch (err) {
        notificationStore.showNotification(err.response.data.message, "error")
        isLoading.value = false
    }
}

const componentToRender = computed(() => {
    if (currentStep.value === "ModuleEnable") {
        return ModuleEnable
    } else if (currentStep.value === "LanguageAndTranslations") {
        return LanguageAndTranslations
    } else if (currentStep.value === "CreateUser") {
        return WebshopUser
    } else if (currentStep.value === "Item") {
        return Item
    } else if (currentStep.value === "PageBuilder") {
        return PageBuilder
    } else if (currentStep.value === "Settings") {
        return Settings
    } else if (currentStep.value === "Theme") {
        return Theme
    } else if (currentStep.value === "DocumentApi") {
        return DocumentApi
    } else if (currentStep.value === "GitBranch") {
        return GitBranch
    } else if (currentStep.value === "Deploy") {
        return Deploy
    }
    return Default
})

watch(() => companyStore.getSelectedCompany, (newSelectedCompany) => {
    if (!_.isEmpty(newSelectedCompany)) {
        getCompanyOnboardStatus()
    }
});

onMounted(() => {
    if (companyStore.getSelectedCompany.Id) {
        getCompanyOnboardStatus()
    }
});




</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock
            ref="activateModuleRef"
            title=""
        >
            <template v-slot:content>
                <div class="row h-80vh" v-if="onboardingProgress">
                    <div class="col-3 p-0 bg-body-light">
                        <div class="d-flex flex-column h-100 max-h-80vh">
                            <div class="multistep px-4 pt-4 flex-grow-1 overflow-auto">
                                <div class="multistep-item" v-for="(step, stepName, index) in onboardingProgress">
                                    <div class="multistep-header" role="tab">
                                        <button type="button" class="btn btn-primary step-btn"
                                                :class="{'btn-success': step.IsCompleted, 'progress-outline': stepName === currentStep && !step.IsCompleted}"
                                        >
                                            {{ index + 1 }}
                                        </button>
                                        <div>
                                            <h6 class="step-title mb-1">{{ step.Title }}</h6>
                                            <small class="mb-0">
                                                <span class="text-success" v-if="step.IsCompleted">
                                                    <i class="fa fa-check"></i>
                                                    Completed
                                                </span>
                                                <span v-else-if="stepName === currentStep">
                                                    <i class="fa fa-circle-notch fa-spin rounded-circle p-1"></i>
                                                    Running
                                                </span>
                                                <span v-else>
                                                    No status
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
<!--                            <div class="py-3 px-4">-->
<!--                                <button class="btn btn-outline-success w-100">Publish</button>-->
<!--                            </div>-->
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="p-4 d-flex flex-column max-h-80vh h-100 overflow-auto">
                            <div class="flex-grow-1">
                                <component :is="componentToRender"
                                           :name="currentStep"
                                           :step="onboardingProgress[currentStep]"
                                           @complete="setCurrentStepStatus"
                                >
                                </component>
                            </div>
                            <div class="d-flex justify-content-center">
                                <div>
                                    <button class="btn btn-light me-2" v-if="previousStep"
                                            @click.prevent="gotoStep(previousStep)">Previous
                                    </button>
                                    <button class="btn btn-light" v-if="nextStep" @click.prevent="gotoStep(nextStep)"
                                            :disabled="!isCurrentStepComplete">
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>

<style lang="scss" scoped>
.max-h-80vh {
    max-height: 80vh;
}

.h-80vh {
    height: 80vh;
}

.progress-outline {
    outline: 2px solid var(--bs-primary);
    border: 2px solid white;
}

.multistep {
    .multistep-item {
        margin-bottom: 0.75rem;
        position: relative;

        &:not(:last-child):after {
            content: "";
            height: 20px;
            width: 1px;
            border-left: 1px solid var(--bs-secondary);
            margin-bottom: 0.75rem;
            margin-left: 0.5rem;
            position: absolute;
            top: 30px;
        }

        .multistep-header {
            display: flex;
            margin-bottom: 1rem;
            align-items: start;

            .step-title {
                margin-bottom: 0;
                font-weight: 600;
            }

            .step-btn {
                border-radius: 100%;
                width: 1.25rem;
                height: 1.25rem;
                line-height: 0.75rem;
                font-weight: bold;
                margin-right: 0.75rem;
                font-size: 0.75rem;
                padding: 0;
            }
        }

        .multistep-body {
            margin-left: 0.75rem;
            padding: 1.25rem;
            border-left: 1px solid #eee;
        }

    }
}
</style>
