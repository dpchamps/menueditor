// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'

import router from './router'
import store from './store';
import axios from 'axios';
import apiWrapper from './assets/scripts/ApiWrapper';
const {Vue2Dragula} = require('vue2-dragula');


Vue.prototype.$api = new apiWrapper();
Vue.use(Vue2Dragula);

Vue.prototype.$lodash = require('lodash');
require('font-awesome/scss/font-awesome.scss');
window.EventBus = new Vue();

Vue.config.productionTip = false;
window._ = require('lodash');
/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  template: '<App/>',
  components: { App }
});
