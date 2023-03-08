<script setup>
import ModuleList from "@/views/module/Modules/ModuleList.vue";</script>

<template>
    <!-- Hero -->
    <!--    <BasePageHeading subtitle="" title="Dashboard">
            <template #extra>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumb-alt">
                        <li class="breadcrumb-item">
                            Dashboard
                        </li>
                    </ol>
                </nav>
            </template>
        </BasePageHeading>-->
    <!-- END Hero -->

    <!-- Page Content -->
    <div class="content">
        <BaseBlock title="Modules">
            <ModuleList/>
        </BaseBlock>
    </div>
    <!-- END Page Content -->
</template>
