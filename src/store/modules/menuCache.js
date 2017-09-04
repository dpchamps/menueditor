"use strict";

const state = {
  pages: {}
};

const getters = {
  headers: (state, page) => {
    if(state.pages[page]){
      return state.pages[page].headers;
    }
  },
  pages: (state) => {
    return state.pages;
  }

};

const mutations = {
  /*
  Where item = {
    page,
    key,
    value
  }
   */
  cache(state, item){
    state.pages[item.page] = state.pages[item.page]  || {};
    let _cacheItem = state.pages[item.page];
    item.key = (typeof  item.key !== 'undefined') ? item.key : null;
    item.value = (typeof item.value !== 'undefined') ? item.value : null;

    if(item.key !== null && item.value !== null){
      _cacheItem[item.key] = item.value;
    }

    state.pages[item.page] = _cacheItem;
  }

};

const actions = {
  cacheArray({commit}, item){
    item.array.forEach(item => {
      commit('cache', {
        'page' : item.page,
        'key'  : item.key,
        'value': item.value
      });
    });
  }
};

export default{
  state,
  getters,
  mutations,
  actions
}

