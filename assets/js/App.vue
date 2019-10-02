<template>
    <v-app id="inspire">
        <v-navigation-drawer
                v-model="drawer"
                app
                clipped
        >
            <v-list dense>
                <v-list-item href="/">
                    <v-list-item-action>
                        <v-icon>mdi-view-dashboard</v-icon>
                    </v-list-item-action>
                    <v-list-item-content>
                        <v-list-item-title>Dashboard</v-list-item-title>
                    </v-list-item-content>
                </v-list-item>
                <!-- TODO: add more items -->
            </v-list>
        </v-navigation-drawer>

        <v-app-bar
                app
                color="primary"
                dark
                clipped-left
        >
            <v-app-bar-nav-icon @click.stop="drawer = !drawer"></v-app-bar-nav-icon>
            <v-toolbar-title>Application</v-toolbar-title>
        </v-app-bar>

        <v-content>
            <v-container>
                <slot v-if="!pageContentComponent"></slot>
                <v-progress-circular
                        style="position: fixed; top: 50%; left: 50%; margin-top: -80px; margin-left: -80px;"
                        :size="80"
                        v-if="isLoading"
                        indeterminate
                        color="primary"
                ></v-progress-circular>
                <component v-if="!isLoading" :is="pageContentComponent"></component>
            </v-container>
        </v-content>

<!--        <v-footer app>-->
<!--            <span>&copy; 2019</span>-->
<!--        </v-footer>-->
    </v-app>
</template>

<script>
    import Vue from 'vue';
    import httpVueLoader from './libs/HttpVueLoader';
    export default {
        props: {
            ajaxLoadPages: Boolean,
            pageLoadingText: {
                type: String,
                default: 'Loading...',
            },
        },
        data: () => ({
            drawer: null,
            pageContentComponent: null,
            isLoading: false,
        }),
        mounted() {
            if (this.ajaxLoadPages) {
                const vm = this;
                document.getElementById('app').addEventListener('click', (event) => {
                    let el = event.target;
                    while (el && el.tagName !== 'A') el = el.parentNode;
                    if (el) {
                        window.history.pushState(el.href, el.href, el.href);
                        vm.loadPageUrl(el.href);
                        event.preventDefault();
                    }
                });
                document.getElementById('app').addEventListener('submit', (event) => {
                    const el = event.target;
                    if (el.tagName.toLowerCase() === 'form') {
                        event.preventDefault();
                        window.history.pushState(el.action, el.action, el.action);
                        this.submitForm(el);
                    }
                });
                window.addEventListener('popstate', (event) => {
                    vm.loadPageUrl(event.state);
                });
            }
            this.loadPageContent(pageView);
        },
        methods: {
            async submitForm(formElement) {
                this.isLoading = true;
                const response = await fetch(formElement.action + '.vue', {
                    // Do NOT add headers, since this will affect the body, causing formData to be sent differently.
                    // headers: {'Content-Type': 'application/json'},
                    method: "POST",
                    body: new FormData(formElement),
                });
                if (response.redirected) {
                    const url = response.url;
                    window.history.pushState(url, url, url);
                    this.loadPageUrl(url);
                    return;
                }
                const pageJson = await response.json();
                this.loadPageContent(pageJson);
                this.isLoading = false;
            },
            async loadPageUrl(href) {
                this.isLoading = true;
                const response = await fetch(href + '.vue', {
                    headers: {'Content-Type': 'application/json'},
                });
                const pageResultJson = await response.json();
                this.loadPageContent(pageResultJson);
                this.isLoading = false;
            },
            async loadPageContent(pageView) {
                const appPageComponent = await httpVueLoader(pageView.body, "app-page", JSON.parse(pageView.vueData));
                this.pageContentComponent = Vue.component('app-page', appPageComponent);
            }
        },
    }
</script>