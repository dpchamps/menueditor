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
  </div>
</template>

<script type="text/babel">
    export default {
      data(){
        return{
          itemList: []
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
        }
      },
      methods:{

        getItemList(page, subPage){
          this.$api.getItemList(page, subPage).then((response) => {
            this.$data.itemList = [];
            this.$data.itemList = response.data;
          });
        }

      },
      created(){
        if(this.$route.params.subpage){
          var subPage = this.$route.params.subpage,
              page    = this.$route.params.page;
            this.getItemList(page, subPage);
        }
      }
    }
</script>

<style lang="scss">

</style>
