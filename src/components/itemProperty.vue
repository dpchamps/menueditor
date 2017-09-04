<template>
  <th class="content--editable"
      :data-key="itemkey"
      :data-idx="itemidx"
      :data-subkey="itemsubkey"
      @click.stop.prevent="editProp">
    <span v-show="currentProperty.key !== itemkey">
      <slot name="item"></slot>
    </span>
    <input
      @click.stop=""
      @blur="blurProp"
      @keyup.enter="updateProp"
      v-show="currentProperty.key === itemkey"
      v-model="currentPropertyValue">

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
          return (idx) ? this.$parent.$props.item[key][idx][subkey]  : this.$parent.$props.item[key];
        },
        setItemFromProperty(){
          let prop = this.$data.currentProperty;
          if(prop.idx){
            console.log("yayaya", this.$parent.$props.item[prop.key][prop.idx][prop.subkey] );
            this.$parent.$props.item[prop.key][prop.idx][prop.subkey] =  this.$data.currentPropertyValue;
          }else{
            console.log('toieurtpwoierupt');
            this.$parent.$props.item[prop.key] =  this.$data.currentPropertyValue;
          }
        },
        updateProp(){
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
            evt.target.querySelector('input').focus();
          });
        },
        blurProp(){
          if(this.$lodash.isEmpty(this.$data.currentProperty)){
            return;
          }

          this.updateProp();
        }
      }
    }
</script>

<style lang="scss">

</style>
