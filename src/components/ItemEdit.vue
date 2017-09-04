<template>
  <div class="item-edit-modal">
    <div class="item-editor">
      <table>
        <tr class="title">
          <th>{{item.title}}</th>
        </tr>
        <tr class="price">
          <th>Price: </th>
          <th>${{item.price}}</th>
        </tr>
        <tr class="header">
          <th>Section</th>
          <th>
            <select>

            </select>
          </th>
          <th>
            <select>
              <option v-for="header in headers" :selected="{selected : header === item.header}">{{header}}</option>
            </select>
          </th>
        </tr>
        <tr v-for="description in item.descriptions" v-show="description.id !== null">
          <th v-show="description.text !== null">Quality: </th>
          <th v-show="description.text !== null">{{description.text}}</th>

          <th v-show="description.price !== null">Price: </th>
          <th v-show="description.price !== null">{{description.price}}</th>
        </tr>
      </table>
    </div>
  </div>
</template>

<script type="text/babel">
    export default {
      props: ['item'],
      computed:{
        headers(){
          return this.$parent.itemListHeaders
        },

      }
    }
</script>

<style lang="scss">

</style>
