/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import './bootstrap';

import {createApp} from 'vue';
import {createPinia} from "pinia";
import App from "./App.vue";

import router from "./router";
import VueLazyload from 'vue-lazyload';

/**
 * Next, we will create a fresh Vue application instance. You may then begin
 * registering components with the application instance, so they are ready
 * to use in your application's views. An example is included for you.
 */
// Template components
import BaseBlock from "@/components/BaseBlock.vue";
import BaseBackground from "@/components/BaseBackground.vue";
import BasePageHeading from "@/components/BasePageHeading.vue";
import BaseContentHeading from "@/components/BaseContentHeading.vue";
import BackButton from "@/components/ui/BackButton.vue";
import ActionButton from "@/components/ui/ActionButton.vue";

import InputErrorMessages from "@/components/ui/FormElements/InputErrorMessages.vue";
import Select from "@/components/ui/FormElements/Select.vue";
import PopOverButton from "@/components/ui/PopOvers/PopOverButton.vue";
import DataGrid from "@/components/ui/DataGrid/DataGrid.vue";


// Template directives
import clickRipple from "@/directives/clickRipple";
// Bootstrap framework
import * as bootstrap from "bootstrap";

window.bootstrap = bootstrap;

// Craft new application
const app = createApp(App);

// Register global components
app.component("BaseBlock", BaseBlock);
app.component("BaseBackground", BaseBackground);
app.component("BasePageHeading", BasePageHeading);
app.component("BaseContentHeading", BaseContentHeading);
app.component("BackButton", BackButton);
app.component("ActionButton", ActionButton);

app.component("DataGrid", DataGrid);
app.component("InputErrorMessages", InputErrorMessages);
app.component("Select", Select);
app.component("PopOverButton", PopOverButton);

// Register VueLazyLoad
app.use(VueLazyload, {
    preLoad: 1.3,
    error: '/assets/media/images/noImage.png',
    loading: '/assets/media/images/loading.gif',
    attempt: 1
});

// Register global directives
app.directive("click-ripple", clickRipple);

// Use Pinia and Vue Router
app.use(createPinia());
app.use(router);

// .and finally mount it!
app.mount('#app');
