<script setup>
import {onMounted, ref} from "vue";
import router from "@/router";
import {useNotificationStore} from "@/stores/notificationStore";
import {useFormErrors} from "@/composables/useFormErrors";
import ModulePackage from "@/models/Office/ModulePackage";
import TableHelper from "@/models/TableHelper";

const notificationStore = useNotificationStore();
let {errors, setErrors, resetErrors} = useFormErrors();

let Name = ref('');
let Type = ref('CompanySetup');

const createModulePackageRef = ref(null);

let TypeOptions = ref([]);

async function getTypes() {
    let {data: TypeData} = await TableHelper.getEnumValues('Office', `ModulePackage`, 'Type');
    TypeOptions.value = TypeData.map((item) => {
        return {
            label: item,
            value: item,
        }
    });
}

async function createModulePackage() {
    createModulePackageRef.value.statusLoading();
    let formData = {
        Name: Name.value,
        Type: Type.value
    };

    try {
        let {data, message} = await ModulePackage.create(formData);
        await router.push({name: 'module-packages'});
        notificationStore.showNotification(message);
    } catch (error) {
        setErrors(error.response.data.errors);
    }
    createModulePackageRef.value.statusNormal();
}

onMounted(async () => {
    createModulePackageRef.value.statusLoading();
    await getTypes();
    createModulePackageRef.value.statusNormal();
});


</script>

<template>
    <div class="content">
        <BaseBlock ref="createModulePackageRef" content-full title="Create ModulePackage">
            <template #options>
                <router-link :to="{name:'module-packages'}" class="btn btn-sm btn-outline-info">
                    <i class="far fa-fw fa-arrow-alt-circle-left"></i> Back
                </router-link>
            </template>
            <form class="space-y-4" @submit.prevent="createModulePackage">
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
                            <label class="col-sm-4 col-form-label col-form-label-sm" for="Type">
                                Type<span class="text-danger">*</span>
                            </label>
                            <div class="col-sm-8">
                                <Select id="Type" v-model="Type" :options="TypeOptions"
                                        :required="true"
                                        :select-class="errors.Type ? `is-invalid form-select-sm` : `form-select-sm`"
                                        name="Type"
                                />
                                <InputErrorMessages v-if="errors.Type"
                                                    :errorMessages="errors.Type"></InputErrorMessages>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="btn btn-outline-primary btn-sm col-2" type="submit">Save</button>
            </form>
        </BaseBlock>
    </div>
</template>
