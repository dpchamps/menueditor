<template>
  <th class="content--editable"
      :data-key="itemkey"
      :data-idx="itemidx"
      :data-subkey="itemsubkey"
      @click.stop.prevent="editProp">

    <span v-show="currentProperty.key !== itemkey">
      <slot name="item"></slot>
    </span>
    <textarea
      @click.stop=""
      @blur="blurProp"
      @keyup.enter="updateProp"
      v-show="currentProperty.key === itemkey"
      v-model="currentPropertyValue">
    </textarea>
  </th>
</template>

<script type="text/babel">
    export default {
      data(){
        return {
          localPropertyValue : "",
          currentPropertyValue : "",
          currentProperty : {

          }
        }

      },
      props:['itemkey', 'itemidx', 'itemsubkey'],

      methods: {
        getPropertyFromItem(key, idx, subkey){
          return (idx) ? this.$parent.$data.localItem[key][idx][subkey]  : this.$parent.$data.localItem[key];
        },
        removeSpecialChars(s){
          const specialChars = ["\\$","\\.00"];
          let regEx = new RegExp(specialChars.join('|'), "g");

          return s.replace(regEx, '');
        },
        setItemFromProperty(){
          let prop = this.$data.currentProperty;
          if(prop.idx){
            this.$parent.$data.localItem[prop.key][prop.idx][prop.subkey] =  this.$data.currentPropertyValue;
          }else{
            this.$parent.$data.localItem[prop.key] =  this.$data.currentPropertyValue;
          }
        },
        updateProp(){
          this.currentPropertyValue = this.removeSpecialChars(this.currentPropertyValue);

          this.setItemFromProperty();

          this.$data.currentProperty = {};
          this.$data.currentPropertyValue = "";
        },
        editProp(evt){

          let key = evt.target.dataset.key;
          let idx = evt.target.dataset.idx;
          let subkey = evt.target.dataset.subkey;

          let prop = this.getPropertyFromItem(key, idx, subkey);

          this.$data.currentPropertyValue = prop;

          this.$data.currentProperty = {
            key,
            idx,
            subkey
          };

          this.$nextTick(()=>{
            evt.target.querySelector('textarea').focus();
          });
        },
        blurProp(){
          if(this.$lodash.isEmpty(this.$data.currentProperty)){
            return;
          }

          this.updateProp();
        },
        removeProp(){
        }
      }
    }
</script>

<style lang="scss">

</style>
