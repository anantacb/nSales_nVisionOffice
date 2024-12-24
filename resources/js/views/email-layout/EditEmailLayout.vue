<script setup>
import {onMounted, ref} from "vue";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import {useRoute} from "vue-router";
import Language from "@/models/Office/Language";
import EmailLayout from "@/models/Office/EmailLayout";

const route = useRoute();
const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

let LanguageOptions = ref([]);
let EmailLayoutModel = ref({});
const updateEmailLayoutRef = ref(null);

async function updateEmailLayout() {
    updateEmailLayoutRef.value.statusLoading();
    let formData = {
        Id: EmailLayoutModel.value.Id,
        Name: EmailLayoutModel.value.Name,
        LanguageId: EmailLayoutModel.value.LanguageId,
        Template: EmailLayoutModel.value.Template,
    };

    try {
        let {data, message} = await EmailLayout.update(formData);
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
    }
    updateEmailLayoutRef.value.statusNormal();
}

async function getEmailLayoutDetails() {
    let {data} = await EmailLayout.details(route.params.id);
    EmailLayoutModel.value = data;
}

async function getAllLanguages() {
    const {data} = await Language.getAllLanguages();
    let options = [{label: 'Select Language', value: ''}];
    data.forEach((language) => {
        let option = {label: language.Name, value: language.Id};
        options.push(option);
    });
    LanguageOptions.value = options;
}

onMounted(async () => {
    updateEmailLayoutRef.value.statusLoading();
    await getEmailLayoutDetails();
    await getAllLanguages()
    updateEmailLayoutRef.value.statusNormal();
});

</script>

<template>
    <div class="content">

        <BaseBlock ref="updateEmailLayoutRef" content-full title="Edit Language">

            <template #options>
                <router-link :to="{name:'email-layouts'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="updateEmailLayout">

                <div class="row">
                    <div class="col-lg-6 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Name">
                                Name<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <input id="Name" v-model="EmailLayoutModel.Name"
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

                    </div>

                    <div class="col-lg-6 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Language">
                                Language<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Language" v-model="EmailLayoutModel.LanguageId" :options="LanguageOptions"
                                        :required="true"
                                        :select-class="errors.CompanyLanguage ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Language"
                                />
                                <InputErrorMessages v-if="errors.LanguageId"
                                                    :errorMessages="errors.LanguageId"></InputErrorMessages>
                            </div>
                        </div>
                    </div>

                </div>

                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Update</button>

            </form>

        </BaseBlock>

    </div>
</template>
