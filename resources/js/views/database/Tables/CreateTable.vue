<script setup>
import CreateTableForm from "@/views/database/Tables/CreateTableForm.vue";

</script>

<template>
    <!-- Hero -->
    <BasePageHeading subtitle="" title="Create Table">
        <template #extra>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li aria-current="page" class="breadcrumb-item">Database</li>
                    <li class="breadcrumb-item">
                        <router-link :to="{name: 'create-table'}" class="link-fx">Create Table</router-link>
                    </li>
                </ol>
            </nav>
        </template>
    </BasePageHeading>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <BaseBlock content-full title="Create Table">
            <CreateTableForm/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
