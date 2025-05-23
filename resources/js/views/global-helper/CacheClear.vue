<script setup>
import {ref} from "vue";
import {useTemplateStore} from "@/stores/templateStore";
import GlobalHelper from '@/models/GlobalHelper'
import {useNotificationStore} from "@/stores/notificationStore";

const templateStore = useTemplateStore();
const notificationStore = useNotificationStore();

let nVisionOfficeCache = ref(true);
let nSalesOfficeCache = ref(true);
let nvmGqlCache = ref(true);
let b2bGqlCache = ref(true);


async function cacheClear() {
    templateStore.pageLoader({mode: "on"});
    let formData = {
        nvisionOfficeCache: nVisionOfficeCache.value,
        nsalesOfficeCache: nSalesOfficeCache.value,
        nvmGqlCache: nvmGqlCache.value,
        b2bGqlCache: b2bGqlCache.value
    };
    let {message} = await GlobalHelper.cacheClear(formData);
    templateStore.pageLoader({mode: "off"});
    notificationStore.showNotification(message);
}

</script>

<template>
    <!-- Page Content -->
    <div class="content">
        <BaseBlock ref="createApplicationRef" content-full title="Cache Clear">
            <template #options>
                <router-link :to="{name:'home'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>
            <form class="space-y-4" @submit.prevent="cacheClear">
                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <div class="form-check">
                            <input id="nVisionOfficeCache" :checked="nVisionOfficeCache"
                                   class="form-check-input" type="checkbox"
                                   @change="nVisionOfficeCache = !nVisionOfficeCache">
                            <label class="form-check-label" for="nVisionOfficeCache">
                                Nvision Office
                            </label>
                        </div>
                        <div class="form-check">
                            <input id="nSalesOfficeCache" :checked="nSalesOfficeCache"
                                   class="form-check-input" type="checkbox"
                                   @change="nSalesOfficeCache = !nSalesOfficeCache">
                            <label class="form-check-label" for="nSalesOfficeCache">
                                Nsales Office
                            </label>
                        </div>
                        <div class="form-check">
                            <input id="nvmGqlCache" :checked="nvmGqlCache"
                                   class="form-check-input" type="checkbox"
                                   @change="nvmGqlCache = !nvmGqlCache">
                            <label class="form-check-label" for="nvmGqlCache">
                                NVM Gql API
                            </label>
                        </div>
                        <div class="form-check">
                            <input id="b2bGqlCache" :checked="b2bGqlCache"
                                   class="form-check-input" type="checkbox"
                                   @change="b2bGqlCache = !b2bGqlCache">
                            <label class="form-check-label" for="b2bGqlCache">
                                B2B Gql API
                            </label>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Apply</button>
            </form>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
