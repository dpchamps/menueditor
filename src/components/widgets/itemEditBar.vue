<template>
  <div class="item-edit-bar">
    <button class="undo" @click="undoChanges" disabled> Undo <i class="fa fa-undo" aria-hidden="true"></i>  </button>
    <button class="save" @click="saveChanges"> Save <i class="fa fa-download" aria-hidden="true"></i>  </button>
    <button class="remove" @click="removeItem"> Remove <i class="fa fa-remove" aria-hidden="true"></i> </button>
    <router-link class="close" :to="backLink"><button><i class="fa fa-remove" aria-hidden="true"></i></button></router-link>
  </div>
</template>

<script type="text/babel">
    export default {
      methods:{
        undoChanges(){
          EventBus.$emit('propsCommitted');
            EventBus.$emit('itemUndoChanges');
        },
        saveChanges(){
          EventBus.$emit('propsCommitted');
          EventBus.$emit('itemSaveChanges', this.$parent.localItem);
        },
        removeItem(){
          if(confirm("Are you sure you want to delete this item?")) {
            EventBus.$emit('itemRemove', this.$parent.localItem);
          }
        }
      },
      computed:{
        backLink(){
          let page = this.$route.params.page;
          let subpage =  this.$route.params.subpage;
          let section = this.$route.params.section;
          return "/dashboard/"+page+'/'+subpage+'/'+section;
        }
      }
    }
</script>

<style lang="scss">
  @import "../../assets/styles/itemEditBar.scss";
</style>
