<template>
  <div class="itemlist">
    <template v-for="header in itemListHeaders">
      <router-link :to="{path: link+header }">{{header}}</router-link>
    </template>
    <ul>
      <ol v-for="item in itemsInHeader">
        <input type="checkbox" > <router-link :to="{path: link+section+'/'+item.id }">{{item.title}}</router-link>
      </ol>
    </ul>
    <item-edit :section="section" :item="(currentItem) ? currentItem : {}" v-show="isOpen"></item-edit>
  </div>
</template>

<script type="text/babel">
  import ItemEdit from './ItemEdit';
    export default {
      components: {
        ItemEdit
      },
      data(){
        return{
          itemList: [],
          currentItem: {}
        }
      },
      computed:{

        itemListHeaders(){
          //return this.$lodash.values( this.$lodash.groupBy(this.$data.itemList, 'header') );
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
        }
      },
      methods:{

        getItemList(page, subPage){
          this.$api.getItemList(page, subPage).then((response) => {
            this.$data.itemList = [];
            this.$data.itemList = response.data;
            this.getCurrentItem();
          });
        },
        getCurrentItem(){
          console.log("getting item", this.$route.params.item);
          if(this.$route.params.item){
            this.currentItem = this.$data.itemList.filter((item) => {
              return item.id === this.$route.params.item;
            })[0];
          }else{
            this.currentItem = {};
          }
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
        },
        'currentItem'(){
          console.log('changed');
        }
      },
      created(){
        this.checkForItemList();
      }
    }
</script>

<style lang="scss">

</style>
