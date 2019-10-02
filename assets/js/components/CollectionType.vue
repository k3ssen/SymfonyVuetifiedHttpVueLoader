<template>
    <div>
        <slot name="content"></slot>
        <v-card class="my-5 pa-4 form-prototype-widget" v-for="item in items" :key="item" ref="widgets">
            <slot name="prototype"></slot>
            <v-btn v-if="allowDelete" @click="remove(item)">Remove</v-btn>
        </v-card>
        <v-btn v-if="allowAdd" @click="add" class="mb-5">Add</v-btn>
    </div>
</template>

<script>
    export default {
        props: {
            allowAdd: {
                type: Boolean,
                default: true
            },
            allowDelete: {
                type: Boolean,
                default: true
            },
            prototypeName: {
                type: String,
                default: '__name__'
            },
        },
        data: () => ({
            count: 0,
            items: [],
        }),
        methods: {
            add() {
                this.count++;
                this.items.push(this.count);
                const index = this.items.indexOf(this.count);
                const placeholder = this.prototypeName;
                const vm = this;
                this.$nextTick(() => {
                    const el = vm.$refs.widgets[index].$el;
                    el.querySelectorAll('[name*='+placeholder+']').forEach((el) => {
                        el.setAttribute('id', el.id.replace(placeholder, index));
                        const describedBy =  el.getAttribute('aria-describedby');
                        if (describedBy) {
                            el.setAttribute('aria-describedby', describedBy.replace(placeholder, index));
                        }
                        el.setAttribute('name', el.name.replace(placeholder, index));
                    });
                    el.querySelectorAll('[for*='+placeholder+']').forEach((el) => {
                        el.setAttribute('for', el.getAttribute('for').replace(placeholder, index));
                    })
                });
            },
            remove(item) {
                this.count++;
                const index = this.items.indexOf(item);
                if (index !== -1){
                    this.items.splice(index, 1);
                }
            },
        },
    }
</script>