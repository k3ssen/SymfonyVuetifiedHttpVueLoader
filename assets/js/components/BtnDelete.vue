<template>
    <v-btn v-bind="$attrs" color="error" rounded>
        <v-icon>mdi-delete</v-icon>
        <slot>Delete</slot>
    </v-btn>
</template>