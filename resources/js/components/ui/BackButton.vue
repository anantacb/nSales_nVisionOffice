<template>
    <button class="btn btn-outline-secondary" @click="goBack">
        <div class="d-flex"><img src="/img/chevron-left.svg" alt="back" width="20"> Back</div>
    </button>
</template>

<script>
export default {
    name: "BackButton",
    methods: {
        goBack() {
            return this.$router.go(-1)
        }
    }
}
</script>

<style scoped>

</style>
