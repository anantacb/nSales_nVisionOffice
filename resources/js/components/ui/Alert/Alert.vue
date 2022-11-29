<template>
    <div v-if="config.type">
        <div class="alert" :class="`alert-${config.type}`" :style="alertStyle">
            <div class="alert-container">
                <div class="alert-content">
                    <div v-if="config.showIcon">
                        <i class="fa alert-icon"
                           :class="config.icon?`fa-${config.icon}`:`fa-${this.icons[config.type]}`"
                           aria-hidden="true"></i>
                    </div>
                    <div class="mr-auto" v-html="config.message"></div>
                    <div class="alert-close" v-if="config.showClose">
                        <i class="fa fa-times" aria-hidden="true" @click="close"></i></div>
                </div>
                <div class="alert-progress" v-if="config.showProgress" :style="{width: progressWidth+'%'}"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Alert",
    // props: {
    //     type: {
    //         type: String,
    //         required: true
    //     },
    //     message: {
    //         type: String,
    //         required: true
    //     },
    //     align: {
    //         type: Object,
    //         default: () => {
    //             return {right: '1.5rem', top: '1.5rem'}
    //         }
    //     },
    //     width: {
    //         type: String,
    //         default: '300px'
    //     },
    //     autoClose: {
    //         type: Boolean,
    //         default: false
    //     },
    //     timeout: {
    //         type: Number,
    //         default: 5
    //     },
    //     showIcon: {
    //         type: Boolean,
    //         default: false
    //     },
    //     icon: {
    //         type: String,
    //         default: ''
    //     },
    //     showClose: {
    //         type: Boolean,
    //         default: true
    //     },
    //     showProgress: {
    //         type: Boolean,
    //         default: false
    //     },
    // },
    data() {
        return {
            icons: {
                success: 'check',
                danger: 'times',
                warning: 'exclamation',
                info: 'info',
                light: 'lightbulb-o',
            },
            closeAlert: false
        }
    },
    computed: {
        config() {
            return this.$store.getters['alertModule/config']
        },
        alertStyle() {
            return {
                position: 'fixed',
                width: this.config.width,
                ...this.config.align,
            }
        },
        progressWidth() {
            return this.$store.getters['alertModule/getProgressWidth']
        }
    },
    methods: {
        close() {
            this.$store.dispatch('alertModule/close')
        }
    }
}
</script>

<style scoped>

</style>
