<script setup>
import {basicSetup, EditorView} from "codemirror";
import {javascript} from "@codemirror/lang-javascript";
import {html} from "@codemirror/lang-html";
import {css} from "@codemirror/lang-css";
import {php} from "@codemirror/lang-php"
import {defaultHighlightStyle, syntaxHighlighting} from "@codemirror/language";
import {parseMixed} from "@lezer/common";
import {computed, onMounted, ref} from "vue";

const emit = defineEmits(['editorValueChange']);

const props = defineProps({
    InitialValue: {
        type: String,
        default: ""
    },
    Language: {
        type: String,
        default: "html",
        validator: (value) => ["html", "javascript", "css"].includes(value)
    }
});

let Editor = ref(null);
const editorTextAreaRef = ref(null);

const languageExtension = computed(() => {
    switch (props.Language) {
        case 'html':
            // Configure mixed language parsing for HTML with nested JS and CSS
            return html({
                configureParser: parser => parser.configure({
                    wrap: parseMixed(node => {
                        if (node.name === "ScriptText") {
                            return {parser: javascript().language.parser};
                        }
                        if (node.name === "StyleText") {
                            return {parser: css().language.parser};
                        }
                        if (node.name === "PHPText") {
                            return {parser: php().language.parser};
                        }
                        return null;
                    })
                })
            });
        case 'javascript':
            return javascript();
        case 'css':
            return css();
        default:
            return javascript();
    }
});

function getValue() {
    return Editor.value ? Editor.value.state.doc.toString() : '';
}

function editorSetup() {
    // console.log(editorTextAreaRef.value);
    // console.log(props);
    // console.log('out');


    Editor.value = new EditorView({
        doc: props.InitialValue,
        extensions: [
            basicSetup,
            languageExtension.value,
            syntaxHighlighting(defaultHighlightStyle),
            EditorView.updateListener.of((update) => {
                if (update.docChanged) {
                    emit('editorValueChange', update.state.doc.toString());
                }
            })
        ],
        parent: editorTextAreaRef.value
        // parent: this.$refs.textArea
    });
}

onMounted(async () => {
    editorSetup();
});

</script>

<template>
    <div ref="editorTextAreaRef" class="codemirror-container"></div>
</template>

<style scoped>
.codemirror-container {
    max-height: 65vh;
    overflow-y: auto;
    border: 1px solid #2a384b;
    font-size: 0.875rem;
}
</style>
