<template>
  <div class="item-edit-bar">
    <button class="undo" @click="undoChanges" :disabled="!this.$parent.hasChanged"> Undo <i class="fa fa-undo" aria-hidden="true"></i>  </button>
    <button class="save" @click="saveChanges" :disabled="!this.$parent.hasChanged && !canRestore">
      <span v-show="!canRestore"> Save <i class="fa fa-download" aria-hidden="true"></i> </span>
      <span v-show="canRestore"> Restore <i class="fa fa-upload" aria-hidden="true"></i> </span>

    </button>
    <button class="remove" @click="removeItem"> Remove <i class="fa fa-remove" aria-hidden="true"></i> </button>
    <button class="close" @click="closeItem">Close <i class="fa fa-remove" aria-hidden="true"></i></button>
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
          let itemToCommit = this.$lodash.extend({}, this.$parent.localItem);
          itemToCommit.alteration = (itemToCommit.alteration !== 'create') ? 'change' : 'create';

          EventBus.$emit('propsCommitted');
          EventBus.$emit('itemSaveChanges', itemToCommit);
        },
        removeItem(){
          if(confirm("Are you sure you want to delete this item?")) {
            EventBus.$emit('itemRemove', this.$parent.localItem);
          }
        },
        closeItem(){
          EventBus.$emit('itemClose');
        }
      },
      computed:{
        canRestore(){
          let id = this.$parent.localItem.id,
              item = this.$store.getters.getItemFromId(id),
              alteration = (item) ? item.alteration : false;
          return (alteration) ? alteration === 'delete' : false;
        }
      }
    }
</script>

<style lang="scss">
  @import "../../assets/styles/itemEditBar.scss";
</style>
