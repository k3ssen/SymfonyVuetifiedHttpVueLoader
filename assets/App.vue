<template>
    <v-app id="inspire">
        <v-navigation-drawer v-model="drawer" app clipped>
            <v-list dense>
                <slot name="menu-items"></slot>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar app color="primary" dark clipped-left>
            <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
            <v-toolbar-title>Application</v-toolbar-title>
            <div class="flex-grow-1"></div>
            <v-btn href="/logout" fab large depressed color="primary" no-ajax>
                <v-icon>mdi-logout-variant</v-icon>
            </v-btn>
        </v-app-bar>

        <v-content>
            <v-container>
                <slot></slot>
                <server-side-page></server-side-page>
            </v-container>
        </v-content>
    </v-app>
</template>

<script>
    import ServerSidePage from "./components/ServerSidePage";
    export default {
        components: {ServerSidePage},
        data: () => ({
            drawer: null,
        }),
    }
</script>