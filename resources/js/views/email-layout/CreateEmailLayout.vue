<script setup>
import {onMounted, ref} from "vue";
import {booleanOptions} from "@/data/dropDownOptions";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import EmailLayout from "@/models/Office/EmailLayout";
import Language from "@/models/Office/Language";

const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

const createEmailLayoutRef = ref(null);
let LanguageOptions = ref([]);

let Name = ref('');
let LanguageId = ref('');

async function getAllLanguages() {
    const {data} = await Language.getAllLanguages();
    let options = [{label: 'Select Language', value: ''}];
    data.forEach((language) => {
        let option = {label: language.Name, value: language.Id};
        options.push(option);
    });
    LanguageOptions.value = options;
}


async function createEmailLayout() {
    createEmailLayoutRef.value.statusLoading();
    let formData = {
        Name: Name.value,
        LanguageId: LanguageId.value,
    };

    try {
        let {data, message} = await EmailLayout.create(formData);
        await router.push({name: 'email-layouts'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
        createEmailLayoutRef.value.statusNormal();
    }
}

onMounted(async () => {
    createEmailLayoutRef.value.statusLoading();
    await getAllLanguages();
    createEmailLayoutRef.value.statusNormal();
});
</script>

<template>
    <div class="content">

        <BaseBlock ref="createEmailLayoutRef" content-full title="Create Email Layout">

            <template #options>
                <router-link :to="{name:'email-layouts'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>

            <form class="space-y-4" @submit.prevent="createEmailLayout">

                <div class="row">
                    <div class="col-lg-6 space-y-2">
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
                    </div>

                    <div class="col-lg-6 space-y-2">
                        <div class="row">
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Language">
                                Language<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Language" v-model="LanguageId" :options="LanguageOptions"
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

                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>

            </form>

        </BaseBlock>

    </div>
</template>
