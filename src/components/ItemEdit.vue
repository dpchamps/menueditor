<template>
  <div class="item-edit-modal">
    <div class="item-editor">

      <table>
        <tr class="title">
          <th>Item Title: </th>
          <item-property itemkey="title">
            <span slot="item">{{item.title}}</span>
          </item-property>
        </tr>
        <tr class="price">
          <th>Price: </th>
          <item-property itemkey="price">
            <template slot="item">
              <span v-show="item.price">${{item.price}}</span>
              <span v-show="!item.price">(add price)</span>
            </template>
          </item-property>
        </tr>
        <tr class="header">
          <th>Section</th>
          <th>
            <select>
              <option v-for="subPageList in subPages" :selected="(subPageList === subPage)">{{subPageList}}</option>
            </select>
          </th>
          <th>
            <select>
              <option v-for="header in headers" :selected="(header === item.header)">{{header}}</option>
            </select>
          </th>
        </tr>
        <tr v-for="(description, idx) in item.descriptions" v-show="description.id !== null">
          <th><a href="#" @click.prevent="removeProp">&times;</a></th>
          <item-property
            itemkey="descriptions"
            :itemidx="idx"
            itemsubkey="text"
            >
            <span slot="item">{{description.text}}</span>
          </item-property>
          <item-property
            itemkey="descriptions"
            :itemidx="idx"
            itemsubkey="price"
            >
            <template slot="item">
              <span v-show="description.price">${{description.price}}</span>
              <span v-show="!description.price">(add price)</span>
            </template>
          </item-property>

        </tr>
        <tr>
          <th>
            <a href="#" v-show="isAddDescription" @click.prevent="closeAddDescription">&times;</a>
          </th>
          <th >
            <span v-show="!isAddDescription" @click="addDescription">(add description)</span>
            <input  class="firstDescriptionField"
                    @blur="blurAddDescription"
                    @keydown.enter="pushNewDescription"
                    v-show="isAddDescription"
                    v-model="newDescription.text">
          </th>
          <th>
            <span v-show="!isAddDescription" @click="addDescription">(add price)</span>
            <input
              @keydown.enter="pushNewDescription"
              v-show="isAddDescription"
              v-model="newDescription.price">
          </th>
          <th>
            <a href="#" v-show="isAddDescription" @click.prevent="pushNewDescription">+</a>
          </th>

        </tr>
      </table>

    </div>
  </div>
</template>
<script type="text/babel">
    import itemProperty from './itemProperty';
    export default {
      props: ['item'],
      components: {
        itemProperty
      },
      data(){
        return{
          isAddDescription: false,
          newDescription:{
            text : '',
            price : ''
          },
          localItem : {}
        }
      },
      computed:{
        headers(){
          return this.$parent.itemListHeaders;
        },
        section(){
          return this.$route.params.section;
        },
        subPage(){
          return this.$route.params.subpage
        },
        subPages(){
          return (this.$store.getters.pages[this.$route.params.page]) ? this.$store.getters.pages[this.$route.params.page].subPages : undefined;
        }
      },
      methods:{
        pushNewDescription(key){
          if(!this.$data.newDescription.text){
            console.warn('A Sub Item Needs a Description');
            return
          }
          let propToPush = this.$lodash.extend({}, this.$data.newDescription, {id: -1});
          this.$parent.$data.currentItem.descriptions.push(propToPush);
          this.closeAddDescription();
          this.addDescription();
        },
        addDescription(){

          this.$data.isAddDescription = true;
          this.$nextTick(()=>{
            this.$el.querySelector('.firstDescriptionField').focus();
          })
        },
        closeAddDescription(){
          this.$data.newDescription.text = "";
          this.$data.newDescription.price = "";
          this.$data.isAddDescription = false;
        },
        removeProp(){

        },
        blurAddDescription(evt){
          if(evt.target.value === ""){
            evt.target.focus();
          }
        }
      },
      watch:{
        '$route'(){

        }
      },
      created(){

      }

    }
</script>

<style lang="scss">
  .content--editable{
    position: relative;
  }
  .content--editable:after{
    content: '✎';
    position: relative;
    width: 20px;
    height: 20px;
  }
  .content--editable span{
    pointer-events: none;
  }
</style>
