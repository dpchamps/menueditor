<template>
  <div class="item-edit-bar">
    <button @click="undoChanges"> Undo  </button>
    <button @click="saveChanges"> Save  </button>
    <button @click="removeItem"> Remove </button>

    <router-link :to="backLink"><button>&times;</button></router-link>
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

</style>
