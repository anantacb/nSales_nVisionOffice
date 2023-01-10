<script setup>
import {onMounted, onUnmounted, ref} from "vue";
import {useTemplateStore} from "@/stores/template";
import {useAuthStore} from "@/stores/auth";
import User from "@/models/User";
import router from "@/router";

// Grab example data
//import notifications from "@/data/notifications";

// Main Template Store and Router
const templateStore = useTemplateStore();
const authStore = useAuthStore();

// Reactive variables
const baseSearchTerm = ref("");

async function logout() {
    try {
        await User.logout();
        authStore.logout();
        await router.push({name: 'login'});
    } catch (error) {
    }
}

// On form search submit functionality
function onSubmitSearch() {
    //router.push("/backend/pages/generic/search?" + baseSearchTerm.value);
}

// When ESCAPE key is hit close the header search section
function eventHeaderSearch(event) {
    if (event.which === 27) {
        event.preventDefault();
        templateStore.headerSearch({mode: "off"});
    }
}

// Attach ESCAPE key event listener
onMounted(() => {
    document.addEventListener("keydown", eventHeaderSearch);
});

// Remove keydown event listener
onUnmounted(() => {
    document.removeEventListener("keydown", eventHeaderSearch);
});
</script>

<template>
    <!-- Header -->
    <header id="page-header">
        <slot>
            <!-- Header Content -->
            <div class="content-header">
                <slot name="content">
                    <!-- Left Section -->
                    <div class="d-flex align-items-center">
                        <slot name="content-left">
                            <!-- Toggle Sidebar -->
                            <button
                                class="btn btn-sm btn-alt-secondary me-2 d-lg-none"
                                type="button"
                                @click="templateStore.sidebar({ mode: 'toggle' })"
                            >
                                <i class="fa fa-fw fa-bars"></i>
                            </button>
                            <!-- END Toggle Sidebar -->

                            <!-- Toggle Mini Sidebar -->
                            <button
                                class="btn btn-sm btn-alt-secondary me-2 d-none d-lg-inline-block"
                                type="button"
                                @click="templateStore.sidebarMini({ mode: 'toggle' })"
                            >
                                <i class="fa fa-fw fa-ellipsis-v"></i>
                            </button>
                            <!-- END Toggle Mini Sidebar -->

                            <!-- Open Search Section (visible on smaller screens) -->
                            <button
                                class="btn btn-sm btn-alt-secondary d-md-none"
                                type="button"
                                @click="templateStore.headerSearch({ mode: 'on' })"
                            >
                                <i class="fa fa-fw fa-search"></i>
                            </button>
                            <!-- END Open Search Section -->

                            <!-- Search Form (visible on larger screens) -->
                            <form
                                class="d-none d-md-inline-block"
                                @submit.prevent="onSubmitSearch"
                            >
                                <div class="input-group input-group-sm">
                                    <input
                                        id="page-header-search-input2"
                                        v-model="baseSearchTerm"
                                        class="form-control form-control-alt"
                                        name="page-header-search-input2"
                                        placeholder="Search.."
                                        type="text"
                                    />
                                    <span class="input-group-text border-0">
                    <i class="fa fa-fw fa-search"></i>
                  </span>
                                </div>
                            </form>
                            <!-- END Search Form -->
                        </slot>
                    </div>
                    <!-- END Left Section -->

                    <!-- Right Section -->
                    <div class="d-flex align-items-center">
                        <slot name="content-right">
                            <!-- User Dropdown -->
                            <div class="dropdown d-inline-block ms-2">
                                <button
                                    id="page-header-user-dropdown"
                                    aria-expanded="false"
                                    aria-haspopup="true"
                                    class="btn btn-sm btn-alt-secondary d-flex align-items-center"
                                    data-bs-toggle="dropdown"
                                    type="button"
                                >
                                    <img
                                        alt="Header Avatar"
                                        class="rounded-circle"
                                        src="/assets/media/avatars/avatar10.jpg"
                                        style="width: 21px"
                                    />
                                    <span class="d-none d-sm-inline-block ms-2">{{ authStore.user.Name }}</span>
                                    <i
                                        class="fa fa-fw fa-angle-down d-none d-sm-inline-block opacity-50 ms-1 mt-1"
                                    ></i>
                                </button>
                                <div
                                    aria-labelledby="page-header-user-dropdown"
                                    class="dropdown-menu dropdown-menu-md dropdown-menu-end p-0 border-0"
                                >
                                    <div
                                        class="p-3 text-center bg-body-light border-bottom rounded-top"
                                    >
                                        <img
                                            alt="Header Avatar"
                                            class="img-avatar img-avatar48 img-avatar-thumb"
                                            src="/assets/media/avatars/avatar10.jpg"
                                        />
                                        <p class="mt-2 mb-0 fw-medium">{{ authStore.user.Email }}</p>
                                        <p class="mb-0 text-muted fs-sm fw-medium">{{ authStore.user.Initials }}</p>
                                    </div>
                                    <div class="p-2">
                                        <!--                                        <a
                                                                                    class="dropdown-item d-flex align-items-center justify-content-between"
                                                                                    href="javascript:void(0)"
                                                                                >
                                                                                    <span class="fs-sm fw-medium">Inbox</span>
                                                                                    <span class="badge rounded-pill bg-primary ms-2">3</span>
                                                                                </a>
                                                                                <RouterLink
                                                                                    class="dropdown-item d-flex align-items-center justify-content-between"
                                                                                    to=""
                                                                                >
                                                                                    <span class="fs-sm fw-medium">Profile</span>
                                                                                    <span class="badge rounded-pill bg-primary ms-2">1</span>
                                                                                </RouterLink>
                                                                                <a
                                                                                    class="dropdown-item d-flex align-items-center justify-content-between"
                                                                                    href="javascript:void(0)"
                                                                                >
                                                                                    <span class="fs-sm fw-medium">Settings</span>
                                                                                </a>-->
                                    </div>
                                    <div class="dropdown-divider m-0" role="separator"></div>
                                    <div class="p-2">
                                        <!--                                        <RouterLink
                                                                                    class="dropdown-item d-flex align-items-center justify-content-between"
                                                                                    to=""
                                                                                >
                                                                                    <span class="fs-sm fw-medium">Lock Account</span>
                                                                                </RouterLink>-->

                                        <button class="dropdown-item d-flex align-items-center justify-content-between"
                                                type="button"
                                                @click="logout">
                                            <span class="fs-sm fw-medium">Log Out</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <!-- END User Dropdown -->

                            <!-- Notifications Dropdown -->
                            <!--                            <div class="dropdown d-inline-block ms-2">
                                                            <button
                                                                id="page-header-notifications-dropdown"
                                                                aria-expanded="false"
                                                                aria-haspopup="true"
                                                                class="btn btn-sm btn-alt-secondary"
                                                                data-bs-toggle="dropdown"
                                                                type="button"
                                                            >
                                                                <i class="fa fa-fw fa-bell"></i>
                                                                <span v-if="notifications.length > 0" class="text-primary"
                                                                >•</span
                                                                >
                                                            </button>
                                                            <div
                                                                aria-labelledby="page-header-notifications-dropdown"
                                                                class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0 border-0 fs-sm"
                                                            >
                                                                <div
                                                                    class="p-2 bg-body-light border-bottom text-center rounded-top"
                                                                >
                                                                    <h5 class="dropdown-header text-uppercase">
                                                                        Notifications
                                                                    </h5>
                                                                </div>
                                                                <ul class="nav-items mb-0">
                                                                    <li
                                                                        v-for="(notification, index) in notifications"
                                                                        :key="`notification-${index}`"
                                                                    >
                                                                        <a
                                                                            :href="`${notification.href}`"
                                                                            class="text-dark d-flex py-2"
                                                                        >
                                                                            <div class="flex-shrink-0 me-2 ms-3">
                                                                                <i :class="`${notification.icon}`"></i>
                                                                            </div>
                                                                            <div class="flex-grow-1 pe-2">
                                                                                <div class="fw-semibold">
                                                                                    {{ notification.title }}
                                                                                </div>
                                                                                <span class="fw-medium text-muted">
                                                        {{ notification.time }}
                                                      </span>
                                                                            </div>
                                                                        </a>
                                                                    </li>
                                                                    <li v-if="!notifications.length" class="p-2">
                                                                        <div
                                                                            class="alert alert-light d-flex align-items-center space-x-2 mb-0"
                                                                            role="alert"
                                                                        >
                                                                            <i class="fa fa-exclamation-triangle opacity-50"></i>
                                                                            <p class="mb-0">No new ones!</p>
                                                                        </div>
                                                                    </li>
                                                                </ul>
                                                                <div
                                                                    v-if="notifications.length > 0"
                                                                    class="p-2 border-top text-center"
                                                                >
                                                                    <a
                                                                        class="d-inline-block fw-medium"
                                                                        href="javascript:void(0)"
                                                                    >
                                                                        <i class="fa fa-fw fa-arrow-down me-1 opacity-50"></i>
                                                                        Load More..
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>-->
                            <!-- END Notifications Dropdown -->

                            <!-- Toggle Side Overlay -->
                            <button
                                class="btn btn-sm btn-alt-secondary ms-2"
                                type="button"
                                @click="templateStore.sideOverlay({ mode: 'toggle' })"
                            >
                                <i class="fa fa-fw fa-list-ul fa-flip-horizontal"></i>
                            </button>
                            <!-- END Toggle Side Overlay -->
                        </slot>
                    </div>
                    <!-- END Right Section -->
                </slot>
            </div>
            <!-- END Header Content -->

            <!-- Header Search -->
            <div
                id="page-header-search"
                :class="{ show: templateStore.settings.headerSearch }"
                class="overlay-header bg-body-extra-light"
            >
                <div class="content-header">
                    <form class="w-100" @submit.prevent="onSubmitSearch">
                        <div class="input-group">
                            <button
                                class="btn btn-alt-danger"
                                type="button"
                                @click="templateStore.headerSearch({ mode: 'off' })"
                            >
                                <i class="fa fa-fw fa-times-circle"></i>
                            </button>
                            <input
                                id="page-header-search-input"
                                v-model="baseSearchTerm"
                                class="form-control"
                                name="page-header-search-input"
                                placeholder="Search or hit ESC.."
                                type="text"
                            />
                        </div>
                    </form>
                </div>
            </div>
            <!-- END Header Search -->

            <!-- Header Loader -->
            <div
                id="page-header-loader"
                :class="{ show: templateStore.settings.headerLoader }"
                class="overlay-header bg-body-extra-light"
            >
                <div class="content-header">
                    <div class="w-100 text-center">
                        <i class="fa fa-fw fa-circle-notch fa-spin"></i>
                    </div>
                </div>
            </div>
            <!-- END Header Loader -->
        </slot>
    </header>
    <!-- END Header -->
</template>
