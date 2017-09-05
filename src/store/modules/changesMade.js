"use strict";

var lodash = require('lodash');

const state = {
  items:[

  ]
};

const getters = {
  isChanges:(state)=>{
    return state.items.length > 0;
  },
  getChangesList :(state) =>{
    return state.items;
  }
};

const mutations = {
  pushItem(state, change){
    let changeType = change.type;
    let item = change.item;
    let object = lodash.assign(lodash.find(state.items, {id : item.id})),
        idx = state.items.indexOf(object);

    object = lodash.extend(object, item);
    object.alteration = changeType;

    if(idx !== -1){
      state.items.splice(idx, 1, object);
    }else{
      state.items.push(object);
    }
  },
  emptyList(state){
    state.items = [];
  }
};

const actions = {

};


export default {
  state,
  getters,
  mutations,
  actions
}
