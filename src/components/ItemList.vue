<template>
  <div class="itemlist">
    <commit-changes v-show="changes"></commit-changes>
    <template v-for="header in itemListHeaders">
      <router-link :to="{path: link+header }">{{header}}</router-link>
    </template>
    <list-edit-widget></list-edit-widget>

    <ul>
      <li v-for="(item,idx) in itemsInHeader" :class="item.alteration">
        <input type="checkbox" :id="idx" :name="idx" :value="item" v-model="itemCheckList">
        <router-link :to="{path: link+section+'/'+item.id }">{{item.title}}</router-link>
      </li>
    </ul>
    <item-edit :section="section" :item="(currentItem) ? currentItem : {}" v-show="isOpen"></item-edit>
  </div>
</template>

<script type="text/babel">
  import ItemEdit from './ItemEdit';
  import listEditWidget from './widgets/listEditBar';
  import commitChanges from './widgets/commitChanges';
    export default {
      components: {
        ItemEdit,
        listEditWidget,
        commitChanges
      },
      data(){
        return{
          itemList: [],
          itemCheckList : [],
          currentItem: {},
          newItemTemplate: {
            id: -1,
            header: "",
            list_order: -1,
            title: "new item",
            descriptions: [
              {
                id: null,
                price: null,
                text: null
              }
            ]
          }
        }
      },
      computed:{
        itemListHeaders(){
          return this.$lodash.uniq( this.$lodash.map(this.$data.itemList, (item) => { return item.header }))
        },
        section(){
          return this.$route.params.section;
        },
        link(){
          return "/dashboard/"+this.$route.params.page+'/'+this.$route.params.subpage+'/';
        },
        itemsInHeader(){
          var header = this.$route.params.section;
          if(header){
            return this.$lodash.groupBy(this.$data.itemList, 'header')[header];
          }
        },

        isOpen(){
          return !this.$lodash.isEmpty(this.currentItem);
        },
        changes(){
          return this.$store.getters.isChanges;
        }
      },
      methods:{
        mergeItemsStagedForChange(){
          console.log("merging");
          let changes = this.$store.state.changesMade.items;
          changes.forEach(item =>{
            let _item = this.$lodash.find(this.$data.itemList, {id : item.id});
            let idx = this.$data.itemList.indexOf(_item);

            _item = this.$lodash.assign(item);
            if(idx > -1){
              this.$data.itemList.splice(idx, 1, _item);
            }else{
              this.$data.itemList.push(_item);
            }
          });
        },
        getItemList(page, subPage){
          this.$api.getItemList(page, subPage).then((response) => {
            this.$data.itemList = this.$lodash.assign(response.data);
            this.mergeItemsStagedForChange();
            this.getCurrentItem();
          });
        },
        getCurrentItem(){
          console.log("getting current item");
          if(this.$route.params.item){
            this.currentItem = this.$lodash.find(this.itemList, {id : this.$route.params.item})
          }else{
            this.currentItem = {};
          }
        },
        setCurrentItem(item){
          this.currentItem = this.$lodash.extend({}, item);
          let listItem = this.itemList.filter(item =>{
            return item.id === this.currentItem.id;
          })[0];
          let idx = this.itemList.indexOf(listItem);

          this.$set(this.itemList, idx, this.currentItem);

        },
        checkForItemList(){
          if(this.$route.params.subpage){
            var subPage = this.$route.params.subpage,
              page    = this.$route.params.page;
            this.getItemList(page, subPage);
          }
        }
      },
      watch:{
        '$route'(to, from){
          this.checkForItemList();
        }
      },
      created(){
        this.checkForItemList();

        EventBus.$on('itemSaveChanges', (item)=>{

          this.$store.commit('pushItem', {
            item,
            type: (item.alteration === "create") ? "create" : "change"
          });
          this.mergeItemsStagedForChange();
        });
        EventBus.$on('itemRemove', (item)=>{
          this.$store.commit('pushItem', {
            item,
            type: "delete"
          })
        });
        EventBus.$on('selectAll', ()=>{
          for(let i = 0; i < this.itemList.length; i++){
            this.itemCheckList.push(this.itemList[i]);
          }
        });
        EventBus.$on('deSelectAll', ()=>{
          this.itemCheckList = [];
        });
        EventBus.$on('createNewItem', ()=>{
          let newItem =  this.$lodash.cloneDeep(this.newItemTemplate);
          newItem.header = this.section;
          newItem.id = Date.now()+"";
          this.itemList.push( newItem );
          this.$store.commit('pushItem', {
            item: newItem,
            type: "create"
          });
          this.mergeItemsStagedForChange();
        });
        EventBus.$on('deleteSelectedItems', ()=>{
          this.itemCheckList.forEach(itemToDelete =>{
            this.$store.commit('pushItem',{
              item: itemToDelete,
              type: "delete"
            });
          });
          this.mergeItemsStagedForChange();
        });
      }
    }
</script>

<style lang="scss">
  .itemlist ul{
    margin: 0.5em 0;
  }
  .itemlist ul li a{
    font-size: 22px;;
    color: #333;
    text-decoration: none;
  }

  .itemlist li.change a{
    color: mediumspringgreen;
  }
  .itemlist ul li.delete a{
    color: orangered;
    text-decoration: line-through;
  }
  .itemlist li.create a{
    color: dodgerblue;
  }
</style>
