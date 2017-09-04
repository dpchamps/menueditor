"use strict";

import axios from 'axios';
const root = 'http://menueditor.localhost/api';

var Api = function(){
  this.root = root;
  this.url ={
    login: this.root+"/login",
    checkLogin: this.root+"/check_login",
    logout: this.root+'/logout',
    pages: this.root+'/pages'
  };
  this.config = {}
};

Api.prototype.$axios = axios;
Api.prototype.setAuthHeaders = function(credentials){
  this.config.auth = {
    username : credentials.username,
    password    : credentials.token
  }
};
Api.prototype.login = function(credentials){
  return this.$axios.get(this.url.login, {
    auth: {
      username: credentials.username,
      password: credentials.password
    }
  });
};
Api.prototype.checkLogin = function(credentials){
  return this.$axios.get(this.url.checkLogin, {
    auth: {
      username: credentials.username,
      password: credentials.token
    }
  });
};
Api.prototype.logout = function(){
  return this.$axios.get(this.url.logout, this.config);
};
Api.prototype.getPages = function(){
  return this.$axios.get(this.url.pages, this.config);
};

Api.prototype.getSubPages = function(page){
  return this.$axios.get(this.url.pages+'/'+page.toLowerCase(), this.config);
};

Api.prototype.getItemList = function(page, subPage){
  page = page.toLowerCase();
  subPage = subPage.toLowerCase();
  return this.$axios.get(this.url.pages+'/'+page+'/'+subPage, this.config);
};

export default Api;
