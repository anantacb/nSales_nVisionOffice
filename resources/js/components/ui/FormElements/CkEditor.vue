<script setup>
import { Ckeditor } from '@ckeditor/ckeditor5-vue';
import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Heading,
    Bold,
    Italic,
    Link,
    List,
    BlockQuote,
    Undo,
} from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';

const props = defineProps({
    modelValue: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const editorConfig = {
    plugins: [Essentials, Paragraph, Heading, Bold, Italic, Link, List, BlockQuote, Undo],
    toolbar: ['undo', 'redo', '|', 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote'],
    licenseKey: 'GPL',
};

function onInput(value) {
    emit('update:modelValue', value);
    emit('change', value);
}
</script>

<template>
    <Ckeditor
        :editor="ClassicEditor"
        :model-value="modelValue"
        :config="editorConfig"
        @update:model-value="onInput"
    />
</template>
