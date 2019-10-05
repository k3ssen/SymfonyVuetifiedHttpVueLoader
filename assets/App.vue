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
                <template v-for="(messages, type) in flashMessages">
                    <v-alert v-for="message in messages" :type="type">{{ message }}</v-alert>
                </template>
                <slot></slot>
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
            flashMessages: [],
        }),
        mounted() {
            if (this.ajaxLoadPages) {
                const vm = this;
                document.getElementById('app').addEventListener('click', (event) => {
                    let el = event.target;
                    while (el && el.tagName !== 'A') el = el.parentNode;
                    if (el && !el.hasAttribute('no-ajax')) {
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
                this.loadRequestResponse(response);
            },
            async loadPageUrl(href) {
                this.isLoading = true;
                const response = await fetch(href + '.vue', {
                    headers: {'Content-Type': 'application/json'},
                });
                this.loadRequestResponse(response, href);
            },
            async loadRequestResponse(response, prevHref) {
                this.flashMessages = [];
                const contentType = response.headers.get("content-type");
                if (response.redirected) {
                    const url = response.url;
                    if (url !== prevHref) { // check if url is not the same as previous url to prevent infinite loop.
                        window.history.pushState(url, url, url);
                        this.loadPageUrl(url);
                        return;
                    }
                }
                if (response.ok && (contentType && contentType.indexOf("application/json") !== -1)) {
                    const pageResultJson = await response.json();
                    this.loadPageContent(pageResultJson);
                } else { // If we do not get a json response, then replace the entire document.
                    document.open();
                    document.write(await response.text());
                    document.close();
                }
                this.isLoading = false;
            },
            async loadPageContent(pageView) {
                this.flashMessages = pageView.flashMessages || [];
                const appPageComponent = await httpVueLoader(pageView.body, "app-page", JSON.parse(pageView.vueData));
                this.pageContentComponent = Vue.component('app-page', appPageComponent);
            }
        },
    }
</script>