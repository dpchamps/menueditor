<template>
  <div id="app">
    <router-view></router-view>
  </div>
</template>

<script type="text/babel">
export default {
  name: 'app',
  methods:{
    checkUserCacheData(){
      this.$store.dispatch('checkCache')
        .then( (data) => {
          var userdata = this.$store.getters.getUsernameToken;

          return this.$api.checkLogin(userdata);
        }).catch((error) =>{
        console.log(":(");
        this.$router.push('/login');
      }).then(() =>{
        var currentRoute = this.$router.currentRoute;
        this.$api.setAuthHeaders({
          username : this.$store.state.credentials.username,
          token    : this.$store.state.credentials.token
        });
        if(currentRoute == '/' || currentRoute == '/login'){
          this.$router.push('/dashboard');
        }
      });
    }
  },
  watch: {
    '$route'(to, from, next){
      this.checkUserCacheData();
      if(to.path === '/' || to.path === '/login'){

      }
    }
  },

  created(){
    this.checkUserCacheData();
  }
}
</script>

<style>

</style>
