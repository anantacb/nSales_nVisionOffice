<script setup>
import {ref, onMounted} from "vue";
import CKEditor from "@ckeditor/ckeditor5-vue";
// You can import one of the following CKEditor variation (only one at a time)
import ClassicEditor from "@ckeditor/ckeditor5-build-classic";
// import InlineEditor from '@ckeditor/ckeditor5-build-inline'
// import BalloonEditor from '@ckeditor/ckeditor5-build-balloon'
// import BalloonBlockEditor from '@ckeditor/ckeditor5-build-balloon-block'

const ckeditor = CKEditor.component;

const props = defineProps({
    modelValue: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);
const editorData = ref(props.modelValue);
const editorConfig = ref({});

onMounted(() => {
    editorData.value = props.modelValue;
});

function onEditorReady(editor) {
    editor.model.document.on('change:data', () => {
        const data = editor.getData();
        console.log('Editor data changed:', data);
        emit('update:modelValue', data);
        emit('change', data);
    });
}
</script>

<template>
    <ckeditor
        v-model="editorData"
        :config="editorConfig"
        :editor="ClassicEditor"
        @ready="onEditorReady"
    />
</template>
