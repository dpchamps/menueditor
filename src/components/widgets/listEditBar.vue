<template>
  <div class="list-edit-widget">
    <button @click="addItemToList">Add Item</button>
    <button @click="selectAllItems">Select All Items</button>
    <button @click="deSelectAllItems">Deselect All Items</button>
    <button @click="swapItems" :disabled="!twoItemsSelected">Swap Items</button>
    <button @click="deleteSelectedItems">Delete Selected</button>
  </div>
</template>

<script type="text/babel">
    export default {
      computed:{
        twoItemsSelected(){
          return this.$parent.$data.itemCheckList.length === 2;
        }
      },
      methods: {
        addItemToList(){
          EventBus.$emit('createNewItem');
        },
        selectAllItems(){
          EventBus.$emit('selectAll');
        },
        deSelectAllItems(){
          EventBus.$emit('deSelectAll');
        },
        deleteSelectedItems(){
          EventBus.$emit('deleteSelectedItems');
        },
        swapItems(){
          EventBus.$emit('swapItems');
        }
      }
    }
</script>

<style lang="scss">

</style>
