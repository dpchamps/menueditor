<template>
  <div class="dashboard">
    <nav>
      <router-link to="/user"></router-link>
      <router-link to="/backup"></router-link>
      <button @click="logout">Logout</button>
    </nav>
    <pages></pages>
    <hr>
    <item-list></item-list>
  </div>
</template>

<script type="text/babel">
  import ItemList from './ItemList';
  import Pages from './Pages';
    export default {
      components:{
        ItemList, Pages
      },
      methods:{
        logout(){
          this.$api.logout();
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
