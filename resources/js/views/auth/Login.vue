<script setup>
import {computed, reactive, ref} from "vue";
import {useRouter} from "vue-router";
import {useTemplateStore} from "@/stores/templateStore";

import User from "@/models/Office/User";

// Vuelidate, for more info and examples you can check out https://github.com/vuelidate/vuelidate
import useVuelidate from "@vuelidate/core";
import {required} from "@vuelidate/validators";
import {useAuthStore} from "@/stores/authStore";

// Main templateStore and Router
const templateStore = useTemplateStore();
const authStore = useAuthStore();

const router = useRouter();

// Input state variables
const state = reactive({
    email: null,
    password: null,
});

const login_error_message = ref("");

// Validation rules
const rules = computed(() => {
    return {
        email: {
            required,
            //minLength: minLength(3),
        },
        password: {
            required,
            //minLength: minLength(5),
        },
    };
});

// Use vuelidate
const v$ = useVuelidate(rules, state);

// On form submission
async function login() {
    const result = await v$.value.$validate();

    if (!result) {
        // notify user form is invalid
        return;
    }

    try {
        let {data} = await User.login(state.email, state.password);
        console.log(data);
        await authStore.setToken(data);
        await router.push({path: router.currentRoute.value.query.redirect_to ? router.currentRoute.value.query.redirect_to : '/'});
    } catch (exception) {
        console.log(exception.response, exception);
        login_error_message.value = exception.response.data.message;
    }
}
</script>

<template>
    <!-- Page Content -->
    <div class="hero-static d-flex align-items-center">
        <div class="content">
            <div class="row justify-content-center push">
                <div class="col-md-8 col-lg-6 col-xl-4">
                    <!-- Sign In Block -->
                    <BaseBlock class="mb-0" title="Log In">
                        <template #options>
                            <RouterLink
                                class="btn-block-option fs-sm"
                                to=""
                            >Forgot Password?
                            </RouterLink>
                            <RouterLink
                                class="btn-block-option"
                                to="">
                                <i class="fa fa-user-plus"></i>
                            </RouterLink>
                        </template>

                        <div class="p-sm-3 px-lg-4 px-xxl-5 py-lg-5">
                            <h1 class="h2 mb-1">nVision Office</h1>
                            <p class="fw-medium text-muted">Welcome, please login.</p>

                            <div v-if="login_error_message" class="alert alert-danger alert-dismissible" role="alert">
                                <p class="mb-0">
                                    {{ login_error_message }}
                                </p>
                                <button
                                    aria-label="Close"
                                    class="btn-close"
                                    data-bs-dismiss="alert"
                                    type="button"
                                ></button>
                            </div>

                            <!-- Sign In Form -->
                            <form @submit.prevent="login">
                                <div class="py-3">
                                    <div class="mb-4">
                                        <input
                                            id="login-username"
                                            v-model="state.email"
                                            :class="{'is-invalid': v$.email.$errors.length}"
                                            class="form-control form-control-alt form-control-lg"
                                            name="login-username"
                                            placeholder="Email"
                                            type="text"
                                            @blur="v$.email.$touch"
                                        />
                                        <InputErrorMessages v-if="v$.email.$errors.length"
                                                            :error-messages="[`Please enter your Email`]"></InputErrorMessages>
                                    </div>
                                    <div class="mb-4">
                                        <input
                                            id="login-password"
                                            v-model="state.password"
                                            :class="{'is-invalid': v$.password.$errors.length}"
                                            class="form-control form-control-alt form-control-lg"
                                            name="login-password"
                                            placeholder="Password"
                                            type="password"
                                            @blur="v$.password.$touch"
                                        />
                                        <InputErrorMessages v-if="v$.password.$errors.length"
                                                            :error-messages="[`Please enter your Password`]"></InputErrorMessages>
                                    </div>
                                    <div class="mb-4">
                                        <div class="form-check">
                                            <input
                                                id="login-remember"
                                                class="form-check-input"
                                                name="login-remember"
                                                type="checkbox"
                                                value=""
                                            />
                                            <label class="form-check-label" for="login-remember"
                                            >Remember Me</label
                                            >
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col-md-6 col-xl-5">
                                        <button class="btn w-100 btn-alt-primary" type="submit">
                                            <i class="fa fa-fw fa-sign-in-alt me-1 opacity-50"></i>
                                            Log In
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <!-- END Sign In Form -->
                        </div>
                    </BaseBlock>
                    <!-- END Sign In Block -->
                </div>
            </div>
            <div class="fs-sm text-muted text-center">
                <strong>{{ templateStore.app.name + " " + templateStore.app.version }}</strong> &copy;
                {{ templateStore.app.copyright }}
            </div>
        </div>
    </div>
    <!-- END Page Content -->
</template>
