<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ heading }}</div>

                    <div class="card-body">
                        {{ greeting }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {ref} from "vue";

export default {
    props: {
        heading: {
            type: String,
            required: true
        }
    },
    setup(props) {
        const greeting = ref('Hello there')
        return {
            greeting
        }
    }
}
</script>
