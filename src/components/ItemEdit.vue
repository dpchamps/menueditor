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
        <tr v-for="description, idx in item.descriptions" v-show="description.id !== null">
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
          <th> <a href="#" @click.prevent="addProp">+</a></th>
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

        }
      },
      computed:{
        headers(){
          return this.$parent.itemListHeaders
        },
        section(){
          return this.$route.params.section;
        },
        subPage(){
          return this.$route.params.subpage
        },
        subPages(){
          return this.$store.getters.pages[this.$route.params.page].subPages;
        }
      },
      methods:{

        addProp(){

        },
        removeProp(){

        }

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
