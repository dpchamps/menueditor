<template>
  <div class="login-container">
    <form class="login" v-on:submit.prevent>
      <div v-show="error">
        <p>{{error}}</p>
      </div>
      <div class="group">
        <label for="username">username</label>
        <input id="username" name="username" type="text" v-model="username">
      </div>
      <div class="group">
        <label for="password">password</label>
        <input id="password" type="password" v-model="password">
      </div>
      <button type="submit" @click="checkLogin">Login</button>
    </form>
  </div>
</template>

<script type="text/babel">
    export default {
      data(){
        return{
          error: "",
          username: "",
          password: ""
        }
      },
      methods:{
        checkLogin(){
          var credentials = {
            username : this.$data.username,
            password : this.$data.password
          };
          this.$api.login(credentials).then((response)=>{
            console.log(response.data);
            this.$store.dispatch('updateAndCache', response.data);
            this.$router.push('/');
          }).catch(()=> {
            this.$data.error = "Wrong Login Information";
          });

        }
      }
    }
</script>

<style lang="scss" scoped>
@import "../assets/styles/login.scss";
</style>
