<script setup>
import {computed, onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notification";
import {useTemplateStore} from "@/stores/template";

// Import all layout partials
import BaseHeader from "@/layouts/partials/Header.vue";
import BaseSidebar from "@/layouts/partials/Sidebar.vue";
import BaseSideOverlay from "@/layouts/partials/SideOverlay.vue";
import BaseFooter from "@/layouts/partials/Footer.vue";
import Notification from "@/components/ui/Notification/Notification.vue";

// Component properties
defineProps({
    sidebarWithMiniNav: {
        type: Boolean,
        default: false,
        description: "If the sidebar is in Mini Nav Mode",
    },
});

// Main templateStore
const templateStore = useTemplateStore();

// Set default color theme
templateStore.setColorTheme({
    theme: templateStore.settings.colorTheme,
});

// Render main classes based on templateStore options
const classContainer = computed(() => {
    return {
        "sidebar-r": templateStore.layout.sidebar && !templateStore.settings.sidebarLeft,
        "sidebar-mini": templateStore.layout.sidebar && templateStore.settings.sidebarMini,
        "sidebar-o": templateStore.layout.sidebar && templateStore.settings.sidebarVisibleDesktop,
        "sidebar-o-xs": templateStore.layout.sidebar && templateStore.settings.sidebarVisibleMobile,
        "sidebar-dark":
            templateStore.layout.sidebar &&
            templateStore.settings.sidebarDark &&
            !templateStore.settings.darkMode,
        "side-overlay-o":
            templateStore.layout.sideOverlay && templateStore.settings.sideOverlayVisible,
        "side-overlay-hover":
            templateStore.layout.sideOverlay && templateStore.settings.sideOverlayHoverable,
        "enable-page-overlay":
            templateStore.layout.sideOverlay && templateStore.settings.pageOverlay,
        "page-header-fixed": templateStore.layout.header && templateStore.settings.headerFixed,
        "page-header-dark":
            templateStore.layout.header &&
            templateStore.settings.headerDark &&
            !templateStore.settings.darkMode,
        "main-content-boxed": templateStore.settings.mainContent === "boxed",
        "main-content-narrow": templateStore.settings.mainContent === "narrow",
        "rtl-support": templateStore.settings.rtlSupport,
        "side-trans-enabled": templateStore.settings.sideTransitions,
        "side-scroll": true,
        "sidebar-dark page-header-dark dark-mode": templateStore.settings.darkMode,
    };
});

// Change dark mode based on dark mode system preference
if (templateStore.settings.darkModeSystem) {
    if (
        window.matchMedia &&
        window.matchMedia("(prefers-color-scheme: dark)").matches
    ) {
        templateStore.darkMode({mode: "on"});
    } else {
        templateStore.darkMode({mode: "off"});
    }
}

window
    .matchMedia("(prefers-color-scheme: dark)")
    .addEventListener("change", (e) => {
        if (templateStore.settings.darkModeSystem) {
            if (e.matches) {
                templateStore.darkMode({mode: "on"});
            } else {
                templateStore.darkMode({mode: "off"});
            }
        }
    });

let notifications = ref([]);

// Remove side transitions on window resizing
onMounted(() => {
    let winResize = false;

    window.addEventListener("resize", () => {
        clearTimeout(winResize);

        templateStore.setSideTransitions({transitions: false});

        winResize = setTimeout(() => {
            templateStore.setSideTransitions({transitions: true});
        }, 500);
    });
});

const notificationStore = useNotificationStore();

</script>

<template>
    <div id="page-container" :class="classContainer">
        <!-- Page Loader -->
        <div id="page-loader" :class="{ show: templateStore.settings.pageLoader }"></div>

        <!-- Page Overlay -->
        <div
            v-if="templateStore.layout.sideOverlay && templateStore.settings.pageOverlay"
            id="page-overlay"
            @click="templateStore.sideOverlay({ mode: 'close' })"
        ></div>
        <!-- END Page Overlay -->

        <!-- Side Overlay -->
        <BaseSideOverlay v-if="templateStore.layout.sideOverlay">
            <template #header>
                <slot name="side-overlay-header"></slot>
            </template>

            <template #content>
                <slot name="side-overlay-content"></slot>
            </template>

            <slot name="side-overlay"></slot>
        </BaseSideOverlay>
        <!-- END Side Overlay -->

        <!-- Sidebar -->
        <BaseSidebar
            v-if="templateStore.layout.sidebar"
            :with-mini-nav="sidebarWithMiniNav"
        >
            <template #header>
                <slot name="sidebar-header"></slot>
            </template>

            <template #header-extra>
                <slot name="sidebar-header-extra"></slot>
            </template>

            <template #content>
                <slot name="sidebar-content"></slot>
            </template>

            <slot name="sidebar"></slot>
        </BaseSidebar>
        <!-- END Sidebar -->

        <!-- Header -->
        <BaseHeader v-if="templateStore.layout.header">
            <template #content-left>
                <slot name="header-content-left"></slot>
            </template>

            <template #content-right>
                <slot name="header-content-right"></slot>
            </template>

            <template #content>
                <slot name="header-content"></slot>
            </template>
            <slot name="header"></slot>
        </BaseHeader>
        <!-- END Header -->

        <!-- Main Container -->
        <div id="main-container">
            <slot name="page-top-content"></slot>

            <div
                class="position-fixed top-0 end-0 p-3 space-y-3"
                style="z-index: 9999"
            >
                <!-- Notifications -->
                <Notification v-for="(notification, index) in notificationStore.notifications"
                              :index="index"
                              :message="notification.message"
                              :timer="notification.timer"
                              :type="notification.type"
                ></Notification>
            </div>
            <RouterView/>
        </div>
        <!-- END Main Container -->

        <!-- Footer -->
        <BaseFooter v-if="templateStore.layout.footer">
            <template #content-left>
                <slot name="footer-content-left"></slot>
            </template>

            <template #content-right>
                <slot name="footer-content-right"></slot>
            </template>
            <slot name="footer"></slot>
        </BaseFooter>
        <!-- END Footer -->
    </div>
</template>
