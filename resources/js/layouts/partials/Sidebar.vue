<script setup>
import {onMounted, ref, watch} from "vue";
import {useTemplateStore} from "@/stores/templateStore";

import BaseNavigation from "@/components/BaseNavigation.vue";

// SimpleBar, for more info and examples you can check out https://github.com/Grsmto/simplebar/tree/master/packages/simplebar-vue
import SimpleBar from "simplebar";

// Grab menu navigation arrays
import menu from "@/data/menu";

const navigation = menu.main;

// Component properties
defineProps({
    withMiniNav: {
        type: Boolean,
        default: false,
        description: "If the sidebar is in Mini Nav Mode",
    },
});

// Main store
const store = useTemplateStore();

// Dark Mode preference helper for radios
const radioDarkMode = ref();

// Sets default dark mode preferences for radios
function setDarkModeRadioDefault() {
    if (store.settings.darkModeSystem) {
        radioDarkMode.value = "system";
    } else {
        if (store.settings.darkMode) {
            radioDarkMode.value = "dark";
        } else {
            radioDarkMode.value = "light";
        }
    }
}

// When the user sets dark mode preference through the radios
function onDarkModeRadioChange() {
    if (radioDarkMode.value === "system") {
        store.darkModeSystem({mode: "on"});
    } else {
        store.darkModeSystem({mode: "off"});

        if (radioDarkMode.value === "dark") {
            store.darkMode({mode: "on"});
        } else {
            store.darkMode({mode: "off"});
        }
    }
}

// Set dark mode preference radios default and watch for changes to store
setDarkModeRadioDefault();
watch(
    () => store.settings.darkModeSystem,
    () => {
        setDarkModeRadioDefault();
    }
);
watch(
    () => store.settings.darkMode,
    () => {
        setDarkModeRadioDefault();
    }
);

// Init SimpleBar (custom scrolling)
onMounted(() => {
    new SimpleBar(document.getElementById("simplebar-sidebar"));
});
</script>

<template>
    <!-- Sidebar -->
    <!--
      Sidebar Mini Mode - Display Helper classes

      Adding 'smini-hide' class to an element will make it invisible (opacity: 0) when the sidebar is in mini mode
      Adding 'smini-show' class to an element will make it visible (opacity: 1) when the sidebar is in mini mode
      If you would like to disable the transition animation, make sure to also add the 'no-transition' class to your element

      Adding 'smini-hidden' to an element will hide it when the sidebar is in mini mode
      Adding 'smini-visible' to an element will show it (display: inline-block) only when the sidebar is in mini mode
      Adding 'smini-visible-block' to an element will show it (display: block) only when the sidebar is in mini mode
    -->
    <nav
        id="sidebar"
        :class="{ 'with-mini-nav': withMiniNav }"
        aria-label="Main Navigation"
    >
        <slot>
            <!-- Side Header -->
            <div class="content-header">
                <slot name="header">
                    <!-- Logo -->
                    <RouterLink :to="{ name: 'home' }" class="fw-semibold text-dual">
                                <span class="smini-visible">
                                    <img src="/assets/media/favicons/favicon.png" width="20">
                                    <!--                                  <i class="fa fa-circle-notch text-primary"></i>-->
                                </span>
                        <span class="smini-hide fs-5 tracking-wider">
                                  nVision
                                  <span class="fw-normal">Office</span>
                                </span>
                    </RouterLink>
                    <!-- END Logo -->
                </slot>

                <!-- Extra -->
                <div>
                    <slot name="header-extra">
                        <!-- Dark Mode -->
                        <div class="dropdown d-inline-block ms-1">
                            <button
                                id="sidebar-dark-mode-dropdown"
                                aria-expanded="false"
                                aria-haspopup="true"
                                class="btn btn-sm btn-alt-secondary"
                                data-bs-auto-close="outside"
                                data-bs-toggle="dropdown"
                                type="button"
                            >
                                <i v-if="!store.settings.darkMode" class="far fa-moon"></i>
                                <i v-if="store.settings.darkMode" class="fa fa-moon"></i>
                            </button>
                            <div
                                aria-labelledby="sidebar-dark-mode-dropdown"
                                class="dropdown-menu dropdown-menu-end dropdown-menu fs-sm smini-hide border-0"
                                style="min-width: 8rem"
                            >
                                <div class="px-3 py-2 space-y-2">
                                    <div class="form-check">
                                        <input
                                            id="radio-dark-mode-off"
                                            v-model="radioDarkMode"
                                            class="form-check-input"
                                            type="radio"
                                            value="light"
                                            @change="onDarkModeRadioChange"
                                        />
                                        <label
                                            class="form-check-label fw-medium"
                                            for="radio-dark-mode-off"
                                        >Light</label
                                        >
                                    </div>
                                    <div class="form-check">
                                        <input
                                            id="radio-dark-mode-on"
                                            v-model="radioDarkMode"
                                            class="form-check-input"
                                            type="radio"
                                            value="dark"
                                            @change="onDarkModeRadioChange"
                                        />
                                        <label
                                            class="form-check-label fw-medium"
                                            for="radio-dark-mode-on"
                                        >Dark</label
                                        >
                                    </div>
                                    <div class="form-check mb-0">
                                        <input
                                            id="radio-dark-mode-system"
                                            v-model="radioDarkMode"
                                            class="form-check-input"
                                            type="radio"
                                            value="system"
                                            @change="onDarkModeRadioChange"
                                        />
                                        <label
                                            class="form-check-label fw-medium"
                                            for="radio-dark-mode-system"
                                        >System</label
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- END Dark Mode -->
                    </slot>

                    <!-- Close Sidebar, Visible only on mobile screens -->
                    <button
                        class="d-lg-none btn btn-sm btn-alt-secondary ms-1"
                        type="button"
                        @click="store.sidebar({ mode: 'close' })"
                    >
                        <i class="fa fa-fw fa-times"></i>
                    </button>
                    <!-- END Close Sidebar -->
                </div>
                <!-- END Extra -->
            </div>
            <!-- END Side Header -->

            <!-- Sidebar Scrolling -->
            <div id="simplebar-sidebar" class="js-sidebar-scroll">
                <slot name="content">
                    <!-- Side Navigation -->
                    <div class="content-side">
                        <BaseNavigation :nodes="navigation"/>
                    </div>
                    <!-- END Side Navigation -->
                </slot>
            </div>
            <!-- END Sidebar Scrolling -->
        </slot>
    </nav>
    <!-- END Sidebar -->
</template>
