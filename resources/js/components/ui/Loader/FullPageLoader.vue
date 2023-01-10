<template>
    <div class="ui-loader position-fixed" v-if="isLoading">
        <i class="fa fa-circle-o-notch fa-spin spinner ui-loader-md"></i>
    </div>
</template>

<script>
export default {
    name: "FullPageLoader",
    computed:{
        isLoading() {
            return this.$store.getters["loaderModule/getLoadingStatus"]
        }
    }
}
</script>

<style scoped>
.ui-loader {
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.6);
    z-index: 99;
}
.ui-loader .spinner {
    color: #6c757d;
}
.ui-loader-lg {
    font-size: 6rem;
}
.ui-loader-md {
    font-size: 4rem;
}
.ui-loader-sm {
    font-size: 2rem;
}
.ui-loader-xs {
    font-size: 1rem;
}
</style>

