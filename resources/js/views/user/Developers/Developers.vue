<script setup>
import DeveloperList from "@/views/user/Developers/DeveloperList.vue";
import {ref} from "vue";

const developerListRef = ref(null);


function loading(status) {
    status ? developerListRef.value.statusLoading() : developerListRef.value.statusNormal()
}
</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock ref="developerListRef" title="Developers">
            <DeveloperList
                @endLoading="loading(false)"
                @startLoading="loading(true)"
            ></DeveloperList>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
