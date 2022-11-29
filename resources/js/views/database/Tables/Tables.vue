<script setup>
import TableList from "@/views/database/Tables/TableList.vue";</script>

<template>
    <!-- Hero -->
    <BasePageHeading subtitle="Tables" title="Database">
        <template #extra>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt">
                    <li aria-current="page" class="breadcrumb-item">Database</li>
                    <li class="breadcrumb-item">
                        <router-link :to="{name: 'tables'}" class="link-fx">Tables</router-link>
                    </li>
                </ol>
            </nav>
        </template>
    </BasePageHeading>
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <BaseBlock class="text-center">
            <TableList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
