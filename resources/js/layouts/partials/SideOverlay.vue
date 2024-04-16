<script setup>
import {onMounted, onUnmounted} from "vue";
import {useTemplateStore} from "@/stores/templateStore";

// SimpleBar, for more info and examples you can check out https://github.com/Grsmto/simplebar/tree/master/packages/simplebar-vue
import SimpleBar from "simplebar";

// Main store
const store = useTemplateStore();

// Close side overlay on ESCAPE key down
function eventSideOverlay(event) {
    if (event.which === 27) {
        event.preventDefault();
        store.sideOverlay({mode: "close"});
    }
}

// Init SimpleBar (custom scrolling) and attach ESCAPE key event listener
onMounted(() => {
    new SimpleBar(document.getElementById("side-overlay"));
    document.addEventListener("keydown", eventSideOverlay);
});

// Remove keydown event listener
onUnmounted(() => {
    document.removeEventListener("keydown", eventSideOverlay);
});
</script>

<template>
    <!-- Side Overlay-->
    <aside id="side-overlay">
        <slot>
            <!-- Side Header -->
            <div class="content-header border-bottom">
                <slot name="header">
                </slot>

                <!-- Close Side Overlay -->
                <button
                    class="ms-auto btn btn-sm btn-alt-danger"
                    type="button"
                    @click="store.sideOverlay({ mode: 'close' })"
                >
                    <i class="fa fa-fw fa-times"></i>
                </button>
                <!-- END Close Side Overlay -->
            </div>
            <!-- END Side Header -->

            <slot name="content">
                <!-- Side Content -->
                <div class="content-side">
                </div>
                <!-- END Side Content -->
            </slot>
        </slot>
    </aside>
    <!-- END Side Overlay -->
</template>
