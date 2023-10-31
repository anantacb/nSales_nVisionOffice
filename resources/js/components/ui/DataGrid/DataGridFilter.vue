<script setup>
import _ from "lodash";

const debounceSearch = _.debounce(function (value) {
    emit("update:modelValue", value);
    emit('change', value);
}, 500);

const emit = defineEmits(['update:modelValue', 'change'])

const props = defineProps({
    modelValue: {
        type: String,
        required: true,
        default: () => {
            return "";
        }
    }
});

</script>

<template>
    <div class="data-grid-filter-wrapper">
        <div class="input-group search-input-group">
            <input :value="props.modelValue" aria-describedby="search-addon" aria-label="Search" class="form-control"
                   name="Search" placeholder="Search..." type="text" @input="debounceSearch($event.target.value)">
            <span id="search-addon" class="input-group-text">
                    <i class="fa fa-search"></i>
            </span>
        </div>
    </div>
</template>

<style lang="scss" scoped>
.data-grid-filter-wrapper {
    //align-items: end;
    //justify-content: flex-end;
    margin-bottom: 0.5rem;
    margin-top: 0.5rem;

    .search-input-group {
        width: 250px;
        border-radius: 0;

        input {
            border-right: 0;
            border-radius: .2rem;
            //height: calc(2em + 0.75rem + 2px);

            &:focus {
                border-color: #e9ecef;
            }
        }

        .input-group-text {
            //background: white;
            margin-left: -1px;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    }
}
</style>
