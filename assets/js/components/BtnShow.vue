<template>
    <v-btn v-bind="$attrs" rounded color="accent">
        <v-icon dark>mdi-details</v-icon>
        <slot>Details</slot>
    </v-btn>
</template>