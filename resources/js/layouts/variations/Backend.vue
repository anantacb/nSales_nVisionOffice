<script setup>
import {useTemplateStore} from "@/stores/templateStore";
import {useCompanyStore} from "@/stores/companyStore";

import BaseLayout from "@/layouts/BaseLayout.vue";
import {onBeforeMount, onServerPrefetch} from "vue";

// Main store
const templateStore = useTemplateStore();

// Set default elements for this layout
templateStore.setLayout({
    header: true,
    sidebar: true,
    sideOverlay: true,
    footer: true,
});

// Set various template options for this layout variation
templateStore.headerStyle({mode: "dark"});
templateStore.mainContent({mode: "full"});
templateStore.darkMode({mode: "on"});
</script>

<template>
    <BaseLayout/>
</template>
