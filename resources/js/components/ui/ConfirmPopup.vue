<template>
    <div>
        <b-modal v-model="modalShow" :title="title" centered no-close-on-backdrop no-close-on-esc hide-header-close>
            <template #modal-title>
                <h4 class="mb-0">{{ title }}</h4>
            </template>
            <p class="my-2" v-html="message"></p>
            <template #modal-footer>
                <div class="">
                    <Button :label="cancelButtonLabel"
                            :title="cancelButtonLabel"
                            classes="btn btn-outline-secondary mr-1"
                            @click="cancel"
                            :disabled="disabled || isLoading"
                    />
                    <Button :label="confirmButtonLabel"
                            :title="confirmButtonLabel"
                            :classes="confirmButtonVariant"
                            @click="proceed"
                            :disabled="disabled || isLoading"
                            :show-loader="true"
                            :is-loading="isLoading"
                    />
                </div>
            </template>
        </b-modal>
    </div>
</template>

<script>
import Button from "./Button";

export default {
    name: "ConfirmPopup",
    components: {
        Button
    },
    props: {
        title: {
            type: String,
            required: true
        },
        message: {
            type: String,
            required: true
        },
        modalState: {
            type: Boolean,
            default: false
        },
        confirmButtonLabel: {
            type: String,
            default: 'Delete'
        },
        confirmButtonVariant: {
            type: String,
            default: 'btn btn-danger'
        },
        cancelButtonLabel: {
            type: String,
            default: 'Cancel'
        },
        isLoading: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            modalShow: this.modalState,
            disabled: false
        }
    },
    watch: {
        modalState() {
            this.disabled = false
            this.modalShow = this.modalState
        }
    },
    methods: {
        cancel() {
            this.disabled = true
            this.$emit('cancel')
        },
        proceed() {
            this.disabled = true
            this.$emit('proceed')
        }
    }
}
</script>

<style scoped>

</style>
