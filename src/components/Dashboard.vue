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
      created(){
      }
    }
</script>

<style lang="scss">

</style>
