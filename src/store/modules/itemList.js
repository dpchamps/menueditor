"use strict";
const lodash = require('lodash');
const state = {
  itemList : []
};
const getters = {
  getItemFromIdx: (state) => (idx) => {
    return state.itemList[idx];
  },
  getIdxFromItem: (state) => (item) => {
    return state.itemList.indexOf(item);
  },
  getItemFromId: (state) => (id) => {
    let match = state.itemList.filter(item =>{
      return item.id === id;
    });
    return (match.length) ? match[0] : undefined;

  },
  getItemList: (state) => {
    return state.itemList;
  },
  getItemListHeaders: (state) => {
    return lodash.uniq( lodash.map( state.itemList, item => item.header ) );
  }
};
const mutations = {
  setItemList(state, itemList){
    state.itemList = itemList.slice(0);
  },

  mergeItemsStagedForChange(state, changedItems){
    changedItems.forEach(item => {
      let _item = lodash.find(state.itemList, {id:item.id}),
          idx = state.itemList.indexOf(_item);

      _item = lodash.assign(item);

      if(idx === -1){
        state.itemList.push(_item);
      }else{
        state.itemList.splice(idx, 1, _item);
      }
    });
  },
  updateItem(state, payload){
    this.$set(state.itemList, payload.idx, payload.item);
  }
};
const actions = {
  setItemInList({ commit, getters }, item){
    let itemToChange = lodash.extend({}, item),
        itemFromList = getters.getItemFromId(itemToChange.id),
        idx = getters.getIdxFromItem(itemFromList);

    commit('updateItem', {
      idx: idx,
      item: itemToChange
    })
  }
};

export default {
  state,
  getters,
  mutations,
  actions
}
