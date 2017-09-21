<template>
  <div class="commit-changes">
    <button @click="commit">Commit All Changes Made</button>
    <button @click="toss">Toss Out All Changes Made</button>
    <router-link to="/changes">  <button >View All Changes Staged </button></router-link>

    <div class="commit-changes-modal" v-show="committing">
      <div class="commit-changes-dialog">
        <h1>Committing Changes <span class="text-spinner">{{textSpinner}}</span></h1>
        <h2>{{currentAction}}</h2>
      </div>
    </div>
  </div>
</template>

<script type="text/babel">
    export default {
      data(){
        return{
          textSpinner : '.',
          committing: false,
          currentAction: '',
          promiseArray: []
        }
      },
      methods:{
        toss(){
          if(confirm("Delete ALL changes made during session?")){
            this.$store.commit('emptyList');
            window.location.reload();
          }
        },

        commit(){
          if(
            confirm("This is a live update. " +
            "\nThese Changes are permanent, " +
            "if you've made a lot of changes consider backing up the menu first.")){

              let changesList = this.$store.getters.getChangesList;
              let apiCall = null;
              var throt = 0;
              var throttle = this.$lodash.debounce(()=>{
                console.log("throttled", ++throt);
              }, 100);
              changesList.forEach((item,idx) => {
                setTimeout(()=>{
                  switch(item.alteration) {
                    case 'change':
                      console.log("changing item");

                      apiCall = this.$api.change(item);
                      break;
                    case 'delete':
                      apiCall = this.$api.remove(item);
                      break;
                    case 'create':
                      apiCall = this.$api.create(item);
                      break;
                  }
                  this.promiseArray.push(apiCall);
                  if(idx == changesList.length-1){
                    Promise.all(this.promiseArray).then(()=>{
                      window.location.reload();
                    });
                  }
                }, ++throt*50);

              });



          }

        }
      }
    }
</script>

<style lang="scss">
  .commit-changes-modal{
    position: absolute;
    z-index: 99;
    width: 100%;
    height: 100%;
    background-color: rgba(100,100,100, 0.3);
  }
</style>
