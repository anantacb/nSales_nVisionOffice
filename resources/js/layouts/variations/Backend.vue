<script setup>
import {useTemplateStore} from "@/stores/templateStore";

import BaseLayout from "@/layouts/BaseLayout.vue";

// Main store
const store = useTemplateStore();

// Set default elements for this layout
store.setLayout({
    header: true,
    sidebar: true,
    sideOverlay: true,
    footer: true,
});

// Set various template options for this layout variation
store.headerStyle({mode: "dark"});
store.mainContent({mode: "full"});
store.darkMode({mode: "on"});
</script>

<template>
    <BaseLayout/>
</template>
