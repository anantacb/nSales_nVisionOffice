<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import CodeMirrorEditor from "@/components/ui/FormElements/CodeMirrorEditor.vue";
import {useNotificationStore} from "@/stores/notificationStore";
import JsonEditorVue from "json-editor-vue";
import {useTemplateStore} from "@/stores/templateStore";
import {useCompanyStore} from "@/stores/companyStore";
import EmailLayout from "@/models/Office/EmailLayout";
import EmailTemplate from "@/models/Office/EmailTemplate";
import CompanyEmailLayout from "@/models/Company/CompanyEmailLayout";

const emit = defineEmits(['setTemplate', 'setNewErrors']);
const companyStore = useCompanyStore();
const notificationStore = useNotificationStore();
const templateStore = useTemplateStore();
const tabsRef = ref(null);
const previewTemplate = ref('');
const previewSubject = ref('');

const props = defineProps({
    AppType: {
        type: String,
        required: true,
        // default: 'office',
        validator: function (value) {
            return ['office', 'company'].includes(value)
        }
    },
    PageType: {
        type: String,
        required: true,
        // default: 'template',
        validator: function (value) {
            return ['template', 'layout'].includes(value)
        }
    },
    Subject: {
        type: String,
        default: ""
    },
    Template: {
        type: String,
        default: ""
    },
    TemplateObject: {
        type: Object,
        default: () => ({})
    },
    LanguageId: {
        type: [Number, String],
        required: true,
        // default: ''
    },
    LayoutId: {
        type: [Number, String],
        default: ''
    },
    errors: {
        type: Object,
        default: {}
    },
});

function resetPreview() {
    previewSubject.value = '';
    previewTemplate.value = '';
}

async function getDataForPreview() {
    tabsRef.value.statusLoading();
    resetPreview();

    const formData = {
        LanguageId: props.LanguageId,
        Template: props.Template,
        TemplateObject: props.TemplateObject,
        LayoutId: props.PageType === 'template' ? props.LayoutId : undefined,
        Subject: props.PageType === 'template' ? props.Subject : undefined,
    };

    let model;
    if (props.PageType === 'template') {
        model = props.AppType === 'company' ? 'CompanyEmailTemplate' : EmailTemplate;
    } else if (props.PageType === 'layout') {
        model = props.AppType === 'company' ? CompanyEmailLayout : EmailLayout;
    }

    try {
        let {data} = await (props.AppType === 'company'
            ? model.getDataForPreview(companyStore.selectedCompany.Id, formData)
            : model.getDataForPreview(formData));

        previewTemplate.value = data.template;
        previewSubject.value = data.subject;

        if (!previewTemplate.value) {
            notificationStore.showNotification("Unable to preview", "error");
        }

    } catch (error) {
        if (error.status === 422) {
            emit('setNewErrors', error.response.data.errors);
        }
    } finally {
        tabsRef.value.statusNormal();
    }
}

onMounted(async () => {
    // console.log(props.LanguageId);
});
</script>

<template>
    <div class="row">
        <div class="col-lg-12">
            <BaseBlock ref="tabsRef">
                <template #content>
                    <ul class="nav nav-tabs nav-tabs-block" role="tablist">
                        <li class="nav-item">
                            <a
                                id="btabs-static-edit-tab"
                                aria-controls="btabs-static-edit"
                                aria-selected="true"
                                class="nav-link active"
                                data-bs-toggle="tab"
                                href="#btabs-static-edit"
                                role="tab"
                            >
                                Edit
                            </a>
                        </li>
                        <li class="nav-item">
                            <a
                                id="btabs-static-preview-tab"
                                aria-controls="btabs-static-preview"
                                aria-selected="false"
                                class="nav-link"
                                data-bs-toggle="tab"
                                href="#btabs-static-preview"
                                role="tab"
                                @click="getDataForPreview"
                            >
                                Preview
                            </a>
                        </li>
                    </ul>
                    <div class="block-content tab-content pb-3">
                        <div
                            id="btabs-static-edit"
                            aria-labelledby="btabs-static-edit-tab"
                            class="tab-pane active"
                            role="tabpanel"
                            tabindex="0"
                        >
                            <div class="row">
                                <div class="col-9">
                                    <label class="col-form-label col-form-label-sm" for="Template">
                                        Template<span class="text-danger">*</span>
                                    </label>
                                    <div class="col-lg-12 space-y-2">

                                        <CodeMirrorEditor
                                            :InitialValue="Template"
                                            :select-class="errors.Template ? `is-invalid form-select-sm` : `form-select-sm`"
                                            @editorValueChange="emit('setTemplate', $event)"
                                        />

                                        <InputErrorMessages v-if="errors.Template"
                                                            :errorMessages="errors.Template"></InputErrorMessages>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="col-form-label col-form-label-sm" for="sampleData">
                                        Sample Data
                                    </label>
                                    <div>

                                        <JsonEditorVue
                                            :class="templateStore.settings.darkMode ? `jse-theme-dark` : ``"
                                            :main-menu-bar="false"
                                            :modelValue="TemplateObject"
                                            :read-only="true"
                                            :status-bar="false"
                                            mode="text"
                                        >
                                        </JsonEditorVue>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div
                            id="btabs-static-preview"
                            aria-labelledby="btabs-static-preview-tab"
                            class="tab-pane"
                            role="tabpanel"
                            tabindex="0"
                        >
                            <div class="row fs-sm">
                                <div v-if="PageType==='template'" class="col-12 mb-2">
                                    <span>Subject: </span>
                                    <span v-html="previewSubject"></span>
                                </div>

                                <div class="col-12">
                                    <span>Body: </span>
                                    <iframe :srcdoc="previewTemplate" class="preview-iframe"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </BaseBlock>

        </div>
    </div>
</template>

<style scoped>
.preview-iframe {
    border: 1px solid #2a384b;;
    width: 100%;
    height: 65vh;
}

</style>
