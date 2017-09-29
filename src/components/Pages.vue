<template>
  <div class="pages-editable">
    <h1>Site Pages</h1>
    <div class="page-link-container" v-for="_page in pages">
      <router-link class="page-link" :class="{selected : page===_page.title}" :to="{path: '/dashboard/'+_page.title  }">
          <i class="fa fa-pencil" aria-hidden="true"></i> {{_page.title}}
      </router-link>
    </div>
    <div class="dropdown-group">
      <div v-show="page" class="sub-pages-editable subpage-select-group">
        <select @change="subPageSelect">
          <option class="default" disabled selected=true>-Select Section - </option>
          <template v-for="subpage in subPages">
            <option :data-id="subpage.title" :value="'/dashboard/'+page+'/'+subpage.title">
              {{subpage.title}}
            </option>
          </template>
        </select>
      </div>
      <div v-show="siteSection" class="section-select-group" >
        <select @change="headerSelect">
          <option class="default" disabled selected="1">- Select Sub Section -</option>
          <template v-for="header in itemListHeaders">
            <option :data-id="header" :value="link+header">
              {{header}}
            </option>
          </template>
        </select>
      </div>
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
        },
        itemListHeaders(){
          return this.$store.getters.getItemListHeaders;
        },
        siteSection(){
          return this.$route.params.subpage;
        },
        link(){
          return "/dashboard/"+this.urlFragment;
        },
        urlFragment(){
          return this.$route.params.page+'/'+this.$route.params.subpage+'/';
        }
      },
      methods:{
        getPages(){
          this.$api.getPages().then((response) => {
            response.data.forEach(page =>{

              this.$store.commit('cache', {
                'page' :page.title
              });


            });

            this.$data.pages = response.data;

          });
        },
        getSubpages(){
          if(this.page){
            this.$api.getSubPages(this.page)
              .then((response) =>{
                let subPageArray = response.data.map((sub) => {return sub.title});
                this.$store.commit('cache', {
                  'page' : this.page,
                  'key'  : 'subPages',
                  'value': subPageArray
                });
                this.$store.commit('setSubPages', subPageArray);
                this.$data.subPages = [];
                this.$data.subPages = response.data;
              })
              .catch(()=>{
                this.$data.subPages = [];
              });
          }
        },
        getContent(){
          this.getPages();
          this.getSubpages();
          this.$store.commit('setCurrentPage', this.$route.params.page);
        },
        subPageSelect(evt){
          this.$el.querySelector('.section-select-group select option').selected = true;
          this.$router.replace(evt.target.value);
        },
        headerSelect(evt){
          this.$router.replace(evt.target.value)
        },
        setDropDowns(){
          if(this.$route.params.subpage){
           Array.prototype.slice.call(this.$el.querySelectorAll('.subpage-select-group select option'), 0).filter(option =>{
              return option.dataset.id === this.$route.params.subpage;
            })[0].selected = true;
          }
          if(this.$route.params.section){
            Array.prototype.slice.call(this.$el.querySelectorAll('.section-select-group select option'), 0).filter(option =>{
              return option.dataset.id === this.$route.params.section;
            })[0].selected = true;
          }
        }
      },
      watch:{
        '$route' (to, from, next){
          this.getContent();
        }
      },
      created(){
        this.getContent();
        EventBus.$on('itemListLoad', () =>{
          this.$nextTick(()=>{
            this.setDropDowns();
          });
        });
      }
    }
</script>

<style lang="scss">

</style>
