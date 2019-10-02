<template>
    <v-btn v-bind="$attrs" rounded color="accent">
        <v-icon dark>mdi-format-list-bulleted-square</v-icon>
        <slot>Overview</slot>
    </v-btn>
</template>