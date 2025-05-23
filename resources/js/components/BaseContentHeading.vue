<script setup>
// Component properties
defineProps({
    title: {
        type: String,
        description: "The title of page heading section",
    },
    subtitle: {
        type: String,
        description: "The subtitle of page heading section",
    },
    iconForTitle: {
        type: String,
        description: "The Icon of page heading section before title",
    },
    iconForSubTitle: {
        type: String,
        description: "The Icon of page heading section before subtitle",
    },
});
</script>

<template>
    <!-- Hero -->
    <div class="content">
        <slot>
            <div
                v-if="title || $slots.extra"
                class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center py-2 text-center text-md-start"
            >
                <div class="flex-grow-1 mb-1 mb-md-0">
                    <h1 v-if="title" class="h3 fw-bold mb-2">
                        <i v-if="iconForSubTitle" :class="iconForTitle"></i>
                        {{ title }}
                    </h1>
                    <h2 v-if="subtitle" class="h6 fw-medium fw-medium text-muted mb-0">
                        <i v-if="iconForSubTitle" :class="iconForSubTitle"></i>
                        {{ subtitle }}
                    </h2>
                </div>
                <div v-if="$slots.extra" class="mt-3 mt-md-0 ms-md-3 space-x-1">
                    <slot name="extra"></slot>
                </div>
            </div>
        </slot>
    </div>
    <!-- END Hero -->
</template>


<style scoped>
.content {
    padding: .7rem 2rem;
}
</style>

