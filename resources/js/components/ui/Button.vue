<template>
    <a
        :class="classes"
        @click="_clickHandler"
        :href="href"
        v-if="href"
        :title="title"
    >
        <slot name="icon">
            <i v-if="icon" class="mr-1 fa" :class="icon"></i>
        </slot>
        <slot name="label">
            <span v-if="label">{{ label }}</span>
        </slot>
    </a>
    <button
        class="position-relative"
        :class="classes"
        @click="_clickHandler"
        :type="type"
        :title="title"
        :disabled="disabled"
        v-else
    >
        <div>
            <slot name="icon">
                <i v-if="icon" class="mr-1 fa" :class="icon"></i>
            </slot>
            <slot name="label">
                <span v-if="label">{{ label }}</span>
            </slot>
        </div>
        <div v-if="isLoading && showLoader">
            <slot name="loader">
                <div class="btn-loader">
                    <i class="fa fa-circle-o-notch fa-spin"></i>
                </div>
            </slot>
        </div>
    </button>
</template>

<script>
export default {
    props: {
        type: {
            type: String,
            default: "button",
        },
        label: {
            default: "",
        },
        title: {
            type: String,
            default: "",
        },
        classes: {
            type: String,
            default: "btn btn-info",
        },
        icon: {
            type: String,
            default: "",
        },
        clickHandler: {
            default: undefined,
        },
        to: {
            type: String|Object,
            default: "",
        },
        href: {
            type: String,
            default: "",
        },
        disabled: {
            type: Boolean,
            default: false,
        },
        showLoader: {
            type: Boolean,
            default: false,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },
    methods: {
        _clickHandler() {
            if (this.to !== "") {
                this.$router.push(this.to);
            } else if (this.clickHandler !== undefined) {
                this.clickHandler(event);
            } else if (this.href) {
                window.location.href = this.href;
            }
            this.$emit("click");
        },
    },
};
</script>

<style scoped>
.btn-loader{
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    background: rgba(0,0,0,0.4);
}
</style>
