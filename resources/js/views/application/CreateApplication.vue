<script setup>
import {ref} from "vue";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import Application from "@/models/Office/Application";

const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

let Name = ref('');
let Platform = ref('');
let OperatingSystem = ref('');

const createApplicationRef = ref(null);

async function createApplication() {
    createApplicationRef.value.statusLoading();
    let formData = {
        Name: Name.value,
        Platform: Platform.value,
        OperatingSystem: OperatingSystem.value,
    };

    try {
        let {data, message} = await Application.create(formData);
        await router.push({name: 'applications'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
    }
    createApplicationRef.value.statusNormal();
}

</script>

<template>
    <div class="content">
        <BaseBlock ref="createApplicationRef" content-full title="Create Application">
            <template #options>
                <router-link :to="{name:'applications'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>
            <form class="space-y-4" @submit.prevent="createApplication">
                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="Name"
                                       :class="errors.Name ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" name="Name"
                                       placeholder="Name"
                                       required
                                       type="text"/>
                                <InputErrorMessages v-if="errors.Name"
                                                    :errorMessages="errors.Name"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Platform">
                                Platform<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Platform" v-model="Platform"
                                       :class="errors.Platform ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" name="Platform"
                                       placeholder="Platform"
                                       required
                                       type="text"/>
                                <InputErrorMessages v-if="errors.Platform"
                                                    :errorMessages="errors.Platform"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="OperatingSystem">
                                Operating System<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="OperatingSystem" v-model="OperatingSystem"
                                       :class="errors.OperatingSystem ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" name="OperatingSystem"
                                       placeholder="OperatingSystem"
                                       required
                                       type="text"/>
                                <InputErrorMessages v-if="errors.OperatingSystem"
                                                    :errorMessages="errors.OperatingSystem"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>
            </form>
        </BaseBlock>
    </div>
</template>
