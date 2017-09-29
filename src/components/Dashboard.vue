<template>
  <div class="dashboard">
    <nav>
      <router-link to="/user"></router-link>
      <router-link to="/backup"></router-link>
      <logout></logout>
    </nav>
    <pages></pages>
    <hr>
    <item-list></item-list>
  </div>
</template>

<script type="text/babel">
  import ItemList from './ItemList';
  import Logout   from './widgets/Logout';

  import Pages from './Pages';
    export default {
      components:{
        ItemList, Pages, Logout
      },
      methods:{
        logout(){
          this.$api.logout().then(()=>{
            this.
            this.router.push('/');
          });
        }
      },
      watch:{
        '$route' (to, from, next) {
        }
      },
      data(){
        return{
          propertiesNotCommitted : false
        }
      },
      created(){
        EventBus.$on('propsNotCommitted', () =>{
          this.propertiesNotCommitted = true;
        });
        EventBus.$on('propsCommitted', () =>{
          this.propertiesNotCommitted = false;
        })
      },
      beforeRouteUpdate (to, from, next) {

        if (this.propertiesNotCommitted) {
            if (confirm("You have made changes to the item but haven't saved them.\n Do you still want to continue?")) {
              this.propertiesNotCommitted = false;
              next();
            }
        } else {
            next();
        }

      }
    }
</script>

<style lang="scss">
@import '../assets/styles/dashboard.scss';
</style>
