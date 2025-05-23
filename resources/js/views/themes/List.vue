<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock ref="createApplicationRef" content-full title="Themes">
            <template #options>
                <router-link :to="{name:'home'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <div class="row" v-if="themes.length > 0">
                <div class="col-3 " v-for="theme in themes" :key="'theme-' + theme.Id">
                    <Card :theme="theme"/>
                </div>
            </div>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
<script setup>
import {useNotificationStore} from "@/stores/notificationStore";
import Theme from "@/models/Office/Theme"
import {onMounted, ref} from "vue";
import {useTemplateStore} from "@/stores/templateStore";
import Card from "@/components/theme/card.vue";

const templateStore = useTemplateStore();
const notificationStore = useNotificationStore();

const themes = ref([])

const getThemes = async () => {
    const {data} = await Theme.themes()
    themes.value = data
}

onMounted(async () => {
    await getThemes()
})
</script>

