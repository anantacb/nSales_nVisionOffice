<script setup>
import {ref} from "vue";
import {useTemplateStore} from "@/stores/templateStore";
import GlobalHelper from '@/models/GlobalHelper'

let successMessage = ref("");
const templateStore = useTemplateStore();

async function cacheClear() {
    templateStore.pageLoader({mode: "on"});
    let {message} = await GlobalHelper.cacheClear();
    successMessage.value = message;
    templateStore.pageLoader({mode: "off"});
}

cacheClear();
</script>

<template>
    <!-- Page Content -->
    <div class="hero">
        <div class="hero-inner text-center">
            <div class="bg-body-extra-light">
                <div class="content content-full overflow-hidden">
                    <div class="py-4">
                        <h1 class="h1 fw-normal">
                            {{ successMessage }}
                        </h1>
                    </div>
                </div>
            </div>
            <div class="content content-full text-muted fs-sm fw-medium">
                <a class="link-fx" href="/">Go Home</a>
            </div>
        </div>
    </div>
    <!-- END Page Content -->
</template>
