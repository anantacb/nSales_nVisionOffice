<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import Language from "@/models/Office/Language";
import {booleanOptions} from "@/data/dropDownOptions";

const route = useRoute();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

let LanguageModel = ref({});

const updateLanguageRef = ref(null);

async function updateLanguage() {
    updateLanguageRef.value.statusLoading();
    let formData = {
        Id: LanguageModel.value.Id,
        Name: LanguageModel.value.Name,
        Code: LanguageModel.value.Code,
        Locale: LanguageModel.value.Locale,
        IsDefault: LanguageModel.value.IsDefault
    };

    try {
        let {data, message} = await Language.update(formData);
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
    }
    updateLanguageRef.value.statusNormal();
}

async function getLanguageDetails() {
    let {data} = await Language.details(route.params.id);
    LanguageModel.value = data;
}

onMounted(async () => {
    updateLanguageRef.value.statusLoading();
    await getLanguageDetails();
    updateLanguageRef.value.statusNormal();
});

</script>

<template>
    <div class="content">

        <BaseBlock ref="updateLanguageRef" content-full title="Edit Language">

            <template #options>
                <router-link :to="{name:'languages'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="updateLanguage">

                <div class="row">
                    <div class="col-lg-4 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="LanguageModel.Name"
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
                                <Select id="IsDefault" v-model="LanguageModel.IsDefault"
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
                                <input id="Code" v-model="LanguageModel.Code"
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
                                <input id="Locale" v-model="LanguageModel.Locale"
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
