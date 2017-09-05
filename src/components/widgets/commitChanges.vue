<template>
  <div class="commit-changes">
    <button @click="commit">Commit All Changes Made</button>
    <button @click="toss">Toss Out All Changes Made</button>
    <router-link to="/changes">  <button >View All Changes Staged </button></router-link>
  </div>
</template>

<script type="text/babel">
    export default {
      methods:{
        toss(){
          if(confirm("Delete ALL changes made during session?")){
            this.$store.commit('emptyList');
            window.location.reload();
          }
        },
        commit(){
          let changesList = this.$store.getters.getChangesList;
          changesList.forEach(item => {
            console.log(item.alteration)
          });
        }
      }
    }
</script>

<style lang="scss">

</style>
