import Vue from 'vue';
import CollectionType from '../components/CollectionType.vue';
import StaticField from '../components/StaticField.vue';
import BtnDelete from '../components/BtnDelete.vue';
import BtnEdit from '../components/BtnEdit.vue';
import BtnOverview from '../components/BtnOverview.vue';
import BtnShow from '../components/BtnShow.vue';
import BtnNew from '../components/BtnNew.vue';
import {
    VDataTable, VTextField, VBtn, VCombobox, VAutocomplete, VCard, VRadioGroup, VRadio, VCardTitle, VCardText, VIcon,
    VCardActions, VDatePicker, VMenu, VAlert,
} from 'vuetify/lib';

const importComponts = {
    VDataTable, VTextField, VBtn, VCombobox, VAutocomplete, VCard, VRadioGroup, VRadio, VCardTitle, VCardText,
    VIcon, VCardActions, VDatePicker, VMenu, VAlert,
    // Custom components
    CollectionType, StaticField, BtnDelete, BtnEdit, BtnOverview, BtnShow, BtnNew
};

for (const i in importComponts) {
    Vue.component(i, importComponts[i]);
}