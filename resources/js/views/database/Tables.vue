<script setup>
import {onMounted, ref} from 'vue';
import {useTemplateStore} from "@/stores/template";

// Main store
const store = useTemplateStore();

let tables = ref([]);
onMounted(() => {
    axios.get('/api/tables').then(({data}) => {
        tables.value = data;
    });
});

</script>

<template>
    <!-- Hero -->
    <!--    <BasePageHeading title="Main Content" subtitle="Full Width">
            <template #extra>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="javascript:void(0)">Layout</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a class="link-fx" href="javascript:void(0)">Main Content</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">Full Width</li>
                    </ol>
                </nav>
            </template>
        </BasePageHeading>-->
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <BaseBlock class="text-center">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-vcenter">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Version</th>
                        <th>Client Sync</th>
                        <th>Company</th>
                        <th>Disabled</th>
                        <th class="text-center">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="table in tables">
                        <td>
                            {{ table.Name }}
                        </td>
                        <td class="fw-semibold fs-sm">
                            {{ table.Type }}
                        </td>
                        <td class="fs-sm">
                            {{ table.Version }}
                        </td>
                        <td>
                            {{ table.ClientSync }}
                        </td>
                        <td>
                            <span v-for="company in table.companies"
                                  class="fs-xs fw-semibold d-inline-block py-1 px-3 rounded-pill bg-success-light text-success">
                                {{ company }}
                            </span>
                        </td>
                        <td>
                            {{ table.Disabled ? 'Yes' : 'No' }}
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-alt-secondary" type="button">
                                    <i class="fa fa-fw fa-pencil-alt"></i>
                                </button>
                                <button class="btn btn-sm btn-alt-secondary" type="button">
                                    <i class="fa fa-fw fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
