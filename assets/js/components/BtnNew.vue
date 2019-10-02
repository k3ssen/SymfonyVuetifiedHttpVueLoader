<template>
    <v-btn v-bind="$attrs" color="success" rounded>
        <v-icon>mdi-plus</v-icon>
        <slot>New</slot>
    </v-btn>
</template>