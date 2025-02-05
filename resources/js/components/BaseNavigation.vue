<script setup>
import {computed} from "vue";
import {useRoute} from "vue-router";
import {useTemplateStore} from "@/stores/templateStore";
import useCompanyInfos from '@/composables/useCompanyInfos';
import useCheckAccess from "@/composables/useCheckAccess";

let {isModuleEnabled} = useCompanyInfos();
let {hasRoleAccess} = useCheckAccess();

// Main store and Route
const store = useTemplateStore();
const route = useRoute();

// Component properties
const props = defineProps({
    nodes: {
        type: Array,
        description: "The nodes of the navigation",
    },
    subMenu: {
        type: Boolean,
        default: false,
        description: "If true, a submenu will be rendered",
    },
    dark: {
        type: Boolean,
        default: false,
        description: "Dark mode for menu",
    },
    horizontal: {
        type: Boolean,
        default: false,
        description: "Horizontal menu in large screen width",
    },
    horizontalHover: {
        type: Boolean,
        default: false,
        description: "Hover mode for horizontal menu",
    },
    horizontalCenter: {
        type: Boolean,
        default: false,
        description: "Center mode for horizontal menu",
    },
    horizontalJustify: {
        type: Boolean,
        default: false,
        description: "Justify mode for horizontal menu",
    },
    disableClick: {
        type: Boolean,
        default: false,
        description:
            "Disables submenu click on 2+ level when we are in horizontal and hover mode",
    },
});

// Set CSS classes accordingly
const classContainer = computed(() => {
    return {
        "nav-main": !props.subMenu,
        "nav-main-submenu": props.subMenu,
        "nav-main-dark": props.dark,
        "nav-main-horizontal": props.horizontal,
        "nav-main-hover": props.horizontalHover,
        "nav-main-horizontal-center": props.horizontalCenter,
        "nav-main-horizontal-justify": props.horizontalJustify,
    };
});

// Checks if a submenu path is part of the URL path
function subIsActive(paths) {
    const activePaths = Array.isArray(paths) ? paths : [paths];

    return activePaths.some((path) => {
        let path_splits = route.path.split('/');
        if (path_splits[1] === path) {
            return true;
        }
        //return route.path.indexOf(path) === 1; // current path starts with this path string
    });
}

// Main menu toggling and mobile functionality
function linkClicked(e, submenu) {
    if (submenu) {
        // Get closest li element
        let el = e.target.closest("li");

        // Check if we are in a large screen, have horizontal navigation and hover is enabled
        if (
            !(
                window.innerWidth > 991 &&
                ((props.horizontal && props.horizontalHover) || props.disableClick)
            )
        ) {
            if (el.classList.contains("open")) {
                // If submenu is open, close it.
                el.classList.remove("open");
            } else {
                // . else if submenu is closed, close all other (same level) submenus first before open it
                Array.from(el.closest("ul").children).forEach((element) => {
                    element.classList.remove("open");
                });

                el.classList.add("open");
            }
        }
    } else {
        // If we are in mobile, close the sidebar
        if (window.innerWidth < 992) {
            store.sidebar({mode: "close"});
        }
    }
}

</script>

<template>
    <ul :class="classContainer">
        <template v-for="(node, index) in nodes" :key="`node-${index}`">
            <li v-if="hasRoleAccess(node.roles)"
                :class="{'nav-main-heading': node.heading, 'nav-main-item': !node.heading, open: node.sub && node.subActivePaths ? subIsActive(node.subActivePaths) : false}">
                <!-- Heading -->
                {{ node.heading ? node.name : "" }}

                <!-- Normal Link -->
                <div
                    v-if="node.moduleSpecific && isModuleEnabled(node.moduleName) && (!node.heading && !node.sub) && hasRoleAccess(node.roles)">
                    <a v-if="node.directLink"
                       :href="node.to"
                       :target="node.targetBlank ? `_blank` : ``"
                       class="nav-main-link">
                        <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                        <span v-if="node.name" class="nav-main-link-name">{{ node.name }}</span>
                    </a>
                    <RouterLink v-else
                                :active-class="node.to && node.to !== '#' ? 'active' : ''"
                                :to="node.to && node.to !== '#' ? { name: node.to } : '#'"
                                class="nav-main-link"
                    >
                        <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                        <span v-if="node.name" class="nav-main-link-name">
                        {{ node.name }}
                    </span>
                        <span v-if="node.badge"
                              :class="node['badge-variant'] ? `bg-${node['badge-variant']}` : 'bg-primary'"
                              class="nav-main-link-badge badge rounded-pill">
                        {{ node.badge }}
                    </span>
                    </RouterLink>
                </div>
                <!-- END Normal Link -->

                <!-- Submenu Link -->
                <a v-else-if="node.moduleSpecific && isModuleEnabled(node.moduleName) && (!node.heading && node.sub) && hasRoleAccess(node.roles)"
                   class="nav-main-link nav-main-link-submenu"
                   href="#"
                   @click.prevent="linkClicked($event, true)">
                    <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                    <span v-if="node.name" class="nav-main-link-name">{{ node.name }}</span>
                    <span v-if="node.badge"
                          :class="node['badge-variant'] ? `bg-${node['badge-variant']}` : 'bg-primary'"
                          class="nav-main-link-badge badge rounded-pill">{{ node.badge }}</span>
                </a>
                <!-- END Submenu Link -->

                <!-- Normal Link -->
                <div v-else-if="!node.moduleSpecific && (!node.heading && !node.sub) && hasRoleAccess(node.roles)"
                     @click="linkClicked($event)">
                    <a v-if="node.directLink"
                       :href="node.to"
                       :target="node.targetBlank ? `_blank` : ``"
                       class="nav-main-link">
                        <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                        <span v-if="node.name" class="nav-main-link-name">{{ node.name }}</span>
                    </a>
                    <RouterLink v-else
                                :active-class="node.to && node.to !== '#' ? 'active' : ''"
                                :to="node.to && node.to !== '#' ? { name: node.to } : '#'"
                                class="nav-main-link"
                    >
                        <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                        <span v-if="node.name" class="nav-main-link-name">{{ node.name }}</span>
                        <span
                            v-if="node.badge"
                            :class="node['badge-variant'] ? `bg-${node['badge-variant']}` : 'bg-primary'"
                            class="nav-main-link-badge badge rounded-pill">
                                    {{ node.badge }}
                                </span>
                    </RouterLink>
                </div>
                <!-- END Normal Link -->

                <!-- Submenu Link -->
                <a v-else-if="!node.moduleSpecific && (!node.heading && node.sub) && hasRoleAccess(node.roles)"
                   class="nav-main-link nav-main-link-submenu"
                   href="#"
                   @click.prevent="linkClicked($event, true)">
                    <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                    <span v-if="node.name" class="nav-main-link-name">{{ node.name }}</span>
                    <span v-if="node.badge"
                          :class="node['badge-variant'] ? `bg-${node['badge-variant']}` : 'bg-primary'"
                          class="nav-main-link-badge badge rounded-pill">{{ node.badge }}</span>
                </a>
                <!-- END Submenu Link -->

                <BaseNavigation
                    v-if="node.sub"
                    :disable-click="props.horizontal && props.horizontalHover"
                    :nodes="node.sub"
                    sub-menu
                />
            </li>
        </template>

        <!--        <li v-for="(node, index) in nodes"
                    :key="`node-${index}`"
                    :class="{'nav-main-heading': node.heading, 'nav-main-item': !node.heading, open: node.sub && node.subActivePaths ? subIsActive(node.subActivePaths) : false}">
                    &lt;!&ndash; Heading &ndash;&gt;
                    &lt;!&ndash;            {{ node.heading ? node.name : "" }}&ndash;&gt;
                    <template v-if="hasRoleAccess(node.roles)">
                        {{ node.heading ? node.name : "" }}
                    </template>


                    &lt;!&ndash; Normal Link &ndash;&gt;
                    <div
                        v-if="node.moduleSpecific && isModuleEnabled(node.moduleName) && (!node.heading && !node.sub) && hasRoleAccess(node.roles)">
                        <a v-if="node.directLink"
                           :href="node.to"
                           :target="node.targetBlank ? `_blank` : ``"
                           class="nav-main-link">
                            <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                            <span v-if="node.name" class="nav-main-link-name">{{ node.name }}</span>
                        </a>
                        <RouterLink v-else
                                    :active-class="node.to && node.to !== '#' ? 'active' : ''"
                                    :to="node.to && node.to !== '#' ? { name: node.to } : '#'"
                                    class="nav-main-link"
                        >
                            <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                            <span v-if="node.name" class="nav-main-link-name">
                                {{ node.name }}
                            </span>
                            <span v-if="node.badge"
                                  :class="node['badge-variant'] ? `bg-${node['badge-variant']}` : 'bg-primary'"
                                  class="nav-main-link-badge badge rounded-pill">
                                {{ node.badge }}
                            </span>
                        </RouterLink>
                    </div>
                    &lt;!&ndash; END Normal Link &ndash;&gt;

                    &lt;!&ndash; Submenu Link &ndash;&gt;
                    <a v-else-if="node.moduleSpecific && isModuleEnabled(node.moduleName) && (!node.heading && node.sub) && hasRoleAccess(node.roles)"
                       class="nav-main-link nav-main-link-submenu"
                       href="#"
                       @click.prevent="linkClicked($event, true)">
                        <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                        <span v-if="node.name" class="nav-main-link-name">{{ node.name }}</span>
                        <span v-if="node.badge"
                              :class="node['badge-variant'] ? `bg-${node['badge-variant']}` : 'bg-primary'"
                              class="nav-main-link-badge badge rounded-pill">{{ node.badge }}</span>
                    </a>
                    &lt;!&ndash; END Submenu Link &ndash;&gt;

                    &lt;!&ndash; Normal Link &ndash;&gt;
                    <div v-else-if="!node.moduleSpecific && (!node.heading && !node.sub) && hasRoleAccess(node.roles)"
                         @click="linkClicked($event)">
                        <a v-if="node.directLink"
                           :href="node.to"
                           :target="node.targetBlank ? `_blank` : ``"
                           class="nav-main-link">
                            <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                            <span v-if="node.name" class="nav-main-link-name">{{ node.name }}</span>
                        </a>
                        <RouterLink v-else
                                    :active-class="node.to && node.to !== '#' ? 'active' : ''"
                                    :to="node.to && node.to !== '#' ? { name: node.to } : '#'"
                                    class="nav-main-link"
                        >
                            <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                            <span v-if="node.name" class="nav-main-link-name">{{ node.name }}</span>
                            <span
                                v-if="node.badge"
                                :class="node['badge-variant'] ? `bg-${node['badge-variant']}` : 'bg-primary'"
                                class="nav-main-link-badge badge rounded-pill">
                                            {{ node.badge }}
                                        </span>
                        </RouterLink>
                    </div>
                    &lt;!&ndash; END Normal Link &ndash;&gt;

                    &lt;!&ndash; Submenu Link &ndash;&gt;
                    <a v-else-if="!node.moduleSpecific && (!node.heading && node.sub) && hasRoleAccess(node.roles)"
                       class="nav-main-link nav-main-link-submenu"
                       href="#"
                       @click.prevent="linkClicked($event, true)">
                        <i v-if="node.icon" :class="`nav-main-link-icon ${node.icon}`"></i>
                        <span v-if="node.name" class="nav-main-link-name">{{ node.name }}</span>
                        <span v-if="node.badge"
                              :class="node['badge-variant'] ? `bg-${node['badge-variant']}` : 'bg-primary'"
                              class="nav-main-link-badge badge rounded-pill">{{ node.badge }}</span>
                    </a>
                    &lt;!&ndash; END Submenu Link &ndash;&gt;

                    <BaseNavigation
                        v-if="node.sub"
                        :disable-click="props.horizontal && props.horizontalHover"
                        :nodes="node.sub"
                        sub-menu
                    />
                </li>-->
    </ul>
</template>
