<template>
  <button class="logout" @click="action">Log Out</button>
</template>

<script type="text/babel">
    export default {
      methods:{
        action(){
          if(this.$store.state.credentials.token){
            this.$api.logout().then(()=>{
              this.$router.push('/');
              this.$store.commit('clearToken');
            });
          }else{
            this.router.push('/');
          }
        }
      }
    }
</script>

<style lang="scss">

</style>
