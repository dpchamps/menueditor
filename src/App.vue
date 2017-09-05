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
          return this.$api.checkLogin(data);
        }).catch((error) =>{
          return Promise.reject();
        }).then(() =>{
          var currentRoute = this.$router.currentRoute.path;
          this.$api.setAuthHeaders({
            username : this.$store.state.credentials.username,
            token    : this.$store.state.credentials.token
          });
          if(currentRoute == '/' || currentRoute == '/login'){
            this.$router.push('/dashboard');
          }
        }).catch(()=>{
          this.$router.push('/login');
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
    this.$api.$axios.interceptors.response.use(
      (response) => { return response; },
      (error) => {
        switch (error.response.status){
          case 401:
          default:
            this.$router.push('/');
            this.$store.commit('clearToken');
        }
      }
    );
  }
}
</script>

<style>

</style>
