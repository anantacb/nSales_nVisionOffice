<script setup>
import {defineProps, onMounted, ref, watch} from "vue";
import CodeMirrorEditor from "@/components/ui/FormElements/CodeMirrorEditor.vue";

const emit = defineEmits(['updateEmailTemplate']);
const tabsRef = ref(null);

const props = defineProps({
    PageType: {
        type: String,
        required: true,
        // default: 'template',
        validator: function (value) {
            return ['template', 'layout'].includes(value)
        }
    },
    Template: {
        type: String,
        default: ""
    },
});

const delay = ms => new Promise(res => setTimeout(res, ms));

async function previewTemplate() {
    console.log('previewTemplate');
    tabsRef.value.statusLoading();
    await delay(5000);
    console.log("Waited 5s");
    tabsRef.value.statusNormal();
}

onMounted(async () => {
    // console.log(props.Template);
});
</script>

<template>
    <div class="row">
        <div class="col-lg-12">
            <!-- Block Tabs Default Style -->
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
                                @click="previewTemplate"
                            >
                                Preview
                            </a>
                        </li>
                    </ul>
                    <div class="block-content tab-content ">
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
                                            @editorValueChange="emit('updateEmailTemplate', $event)"
                                        />
                                        <!--                                        @editorValueChange="updateTemplate($event)"-->
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label class="col-form-label col-form-label-sm" for="sampleData">
                                        Sample Data
                                    </label>
                                    <div>
                                        sample object
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
                            <h4 class="fw-normal">Preview Content</h4>
                            <p>...</p>
                        </div>
                    </div>
                </template>
            </BaseBlock>

            <!-- END Block Tabs Default Style -->

        </div>
    </div>
</template>
