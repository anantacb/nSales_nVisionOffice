<script setup>
import {ref} from "vue";
import {booleanOptions} from "@/data/dropDownOptions";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import Language from "@/models/Office/Language";

const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

let Name = ref('');
let Code = ref('');
let Locale = ref('');
let IsDefault = ref(0);

const createLanguageRef = ref(null);

async function createLanguage() {
    createLanguageRef.value.statusLoading();
    let formData = {
        Name: Name.value,
        Code: Code.value,
        Locale: Locale.value,
        IsDefault: IsDefault.value,
    };

    try {
        let {data, message} = await Language.create(formData);
        await router.push({name: 'languages'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createLanguageRef.value.statusNormal();
    }
}

</script>

<template>
    <div class="content">

        <BaseBlock ref="createLanguageRef" content-full title="Create Language">

            <template #options>
                <router-link :to="{name:'languages'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="createLanguage">

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
                                       type="text"
                                />
                                <InputErrorMessages v-if="errors.Name"
                                                    :errorMessages="errors.Name"></InputErrorMessages>
                            </div>
                        </div>
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="IsDefault">
                                IsDefault<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="IsDefault" v-model="IsDefault"
                                        :options="booleanOptions"
                                        :required="true"
                                        :select-class="errors.IsDefault ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="IsDefault"
                                        @change="resetErrors"/>
                                <InputErrorMessages v-if="errors.IsDefault"
                                                    :errorMessages="errors.IsDefault"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Code">
                                Code<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Code" v-model="Code"
                                       :class="errors.Code ? `is-invalid form-control-sm` : `form-control-sm`"
                                       class="form-control" name="Code"
                                       required
                                       type="text"/>
                                <InputErrorMessages v-if="errors.Code"
                                                    :errorMessages="errors.Code"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Locale">
                                Locale
                            </label>
                            <div class="col-sm-8">
                                <input id="Locale" v-model="Locale"
                                       :class="errors.Locale ? `is-invalid form-control-sm` : `form-control-sm`"
                                       autocomplete="off" class="form-control" name="Locale"
                                       type="text"/>
                                <InputErrorMessages v-if="errors.Locale"
                                                    :errorMessages="errors.Locale"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                </div>

                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>

            </form>

        </BaseBlock>

    </div>
</template>
