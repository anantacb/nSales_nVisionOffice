<template>
    <RouterView/>
</template>

<style lang="scss">
// Main Stylesheet
@import "@/assets/scss/main.scss";

// Vue Select Css And Overwritten Scss
@import "vue-select/dist/vue-select.css";
@import "@/assets/scss/vendor/_vue-select.scss";

// All color themes are included and available by default
// Feel free to comment out any of them if you won't use them in your project
//@import "@/assets/scss/oneui/themes/amethyst";
//@import "@/assets/scss/oneui/themes/city";
//@import "@/assets/scss/oneui/themes/flat";
//@import "@/assets/scss/oneui/themes/modern";
//@import "@/assets/scss/oneui/themes/smooth";
</style>
