<template>
  <div class="list-edit-widget">
    <button @click="addItemToList">Add Item</button>
    <button @click="selectAllItems">Select All Items</button>
    <button @click="deSelectAllItems">Deselect All Items</button>
    <button @click="deleteSelectedItems">Delete Selected</button>
  </div>
</template>

<script type="text/babel">
    export default {
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
        }
      }
    }
</script>

<style lang="scss">

</style>
