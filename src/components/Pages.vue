<template>
  <div class="pages-editable">
    <template v-for="page in pages">
      <router-link :to="{path: '/dashboard/'+page.title  }">{{page.title}}</router-link>
    </template>
    <div class="sub-pages-editable">
      <template v-for="subpage in subPages">
        <router-link :to="{path: '/dashboard/'+page+'/'+subpage.title  }">{{subpage.title}}</router-link>
      </template>
    </div>
  </div>
</template>

<script type="text/babel">
    export default {
      data(){
        return{
          pages: [],
          subPages: [],
          currentPage: ''
        }
      },
      computed:{
        page(){
          return this.$route.params.page;
        }
      },
      methods:{
        getPages(){
          this.$api.getPages().then((response) => {
            this.$data.pages = response.data;
          })
        },
        getSubpages(){
          if(this.page){
            this.$api.getSubPages(this.page)
              .then((response) =>{
                this.$data.subPages = [];
                this.$data.subPages = response.data;

              })
              .catch(()=>{
                console.log('emptying array');
                this.$data.subPages = [];
              });

          }
        },
        getContent(){
          this.getPages();
          this.getSubpages();
        }
      },
      watch:{
        '$route' (to, from, next){
          this.getContent();
        }
      },
      created(){
        this.getContent();
      }
    }
</script>

<style lang="scss">

</style>
