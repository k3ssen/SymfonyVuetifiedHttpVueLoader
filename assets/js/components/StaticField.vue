<template>
    <div class="row">
        <label class="font-weight-bold col-md-2">
            {{ label }}
        </label>
        <div class="col-md-10">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    export default {
        props: {
            label: {
                type: String,
                default: ""
            },
        },
    }
</script>