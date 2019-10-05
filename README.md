SymfonyVuetified
=====================

Setup for Symfony 4 application that uses utilizes 
[Vuetify](https://vuetifyjs.com),
a modified version of [http-vue-loader](https://github.com/FranckFreiburger/http-vue-loader)
and the [GeneratorBundle](https://github.com/k3ssen/GeneratorBundle)
for rapid developing a symfony-vue-apps.

### Features:
* Twig as vue-components: example.vue.twig
* `{{ form(form) }}` will render form with vuetify-form-components.
* Datatable services for simple datatable setup and ajax support.
* Optionally fetch page-content through ajax, simply by changing a boolean value.
* Partly overwrite the GeneratorBundle for generating pages with vuetified content.
* Use `v-model` inside your form type class to have this model automatically set for you.

### Getting started

1. Clone this project.
1. `composer install`
1. configure `DATABASE_URL` in your `.env` file.
1. `php bin/console doctrine:database:create --if-not-exists`
1. `php bin/console doctrine:schema:update --force`  
    (or use migrations if you want)
1. `php bin/console doctrine:fixtures:load -n`    
1. `yarn install`  
(use `npm` instead of yarn if you don't have yarn installed)
1. `yarn run build`  
(or `yarn run watch` during development).

After this, you should be able to load the website and see a login form. 
Here, you can log in with `admin` as username and password.

### Usage

1. Generate entity: `php bin/console generator:entity:create`   
Do not forget to update your database scheme: `php bin/console doctrine:schema:update --force`
2. Generate CRUD: `php bin/console generator:crud`

That's it! The files needed are now added to your project. 

### Configuration

In `config/packages/twig.yaml` you'll see two global variables: 
- `load_pages_by_ajax`: this lets you choose whether you want pages to load through ajax or have the page
refreshed. The latter can be helpful during development, since you'll have the developer toolbar for every
page.
- `input_style`: Vuetify has different styles for form-inputs. You can configure the style here.


You can configure loads of stuff for the generator. [Have a look at the generator configuration docs](https://github.com/k3ssen/GeneratorBundle/blob/master/Resources/doc/configuration.md#generatorbundle)
 for that.

### Customize

Very basic stuff might work the way you need it to work right away, but in most cases you'll want to make
 modifications. Simply modify the generated files. It's mostly the same as any symfony project 
 with some abstract classes for generic stuff. How you use it is up to you.
 
**Twig files** 
 
Dealing with twig-files has become somewhat different. 
They still are twig-files like any other project, but you'll need to make sure the rendered output conforms to the 
format needed for vue-components. 
Basically this means any rendered output should be like the code below:
 ```
<template>
    You html and/or vue-components here
</template>

<script>
    module.exports = {
        // vue javascript stuff here
    }
</script>

<style>
    // component-specific style here
</style>
```

The `script` and `style` can be omitted if you don't need them, but you must make sure there's always 
a root `template`.

> The `.vue.twig` extension actually won't make any difference from `.html.twig`. 
It is merely being used to improve autocompletes in IDE.

**limitations**  
Actual .vue files are usually compiled by webpack, but the vue.twig files won't be. You need to
make sure that the javascript being used here is supported by the browsers that you need to support.

The .vue.twig files need to be parsed and split into parts to be passed down to a dynamic vue component. 
This has limitation, such as not being able to import other javascript files.
(there may be ways to achieve this, but it's not recommended).

The vuetify-loaded has a mechanism to recognize which components are used to have them loaded automatically for you.
Again, since twig files aren't compiled through webpack, the vuetify-loader has no way of know which components
you are using. All components that you want to use in your twig files must be globally available 
(or you'll have to use your own tricks to make these components available whenever needed). You can
add global components in `assets/js/libs/GlobalComponents.js`.

> **Note:** most of your javascript/vue should be done in actual javascript and vue files. 
Using vue in twig is merely intended as a bridge between serverside rendered code and your vue application. 

### TODO's:

* Form's may not be fully supported yet. Basic fields, entity fields, subforms and datetime have been implemented
but aren't tested in edge cases yet. 
Date-type is supported, but datetime not yet. 
* Error-catching needs improvement.
* Flash-messages don't work through ajax; an alternative is used in controllers, but a more comprehensive fix 
would be preferable.