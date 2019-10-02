<template>
    <v-btn v-bind="$attrs" color="warning" rounded>
        <v-icon>mdi-pencil</v-icon>
        <slot>Edit</slot>
    </v-btn>
</template>