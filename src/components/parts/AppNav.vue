<template>

	<nav id="site-navigation" v-bind:class="{open: isActive}">

		<ul>
			<router-link :to="{ name: 'home'}" class="site-name"> {{ site_name }} </router-link>
			<li v-for="(menu, index) in menus" :key="index" v-if="menu.type != 'custom'">
				 <router-link :to="{ name: 'page', params: { name: getUrlName( menu.url ) }}"> {{ menu.title }} </router-link>
			</li>

		</ul>

	</nav>

</template>

<script>
export default {

  mounted: function() {
    this.getMenu()
  },
  data() {
    return {

      menus: [],
      site_name: sell_media.site_name,
      isActive: false

    }
  },
  methods: {

    getMenu: function() {
      this.$http.get( 'wp-api-menus/v2/menu-locations/primary-menu' )
        .then(function(response){
          this.menus = response.data
        } )
        .catch(function(response) {
          console.log(response)
        } )

    },
    getUrlName: function( url ) {

      const array = url.split( '/' )
      return array[ array.length - 2 ]
    },
    toggleMenu: function() {
      //console.log("Clicked" + this.isActive)
      this.isActive = ! this.isActive
    }

  }
}
</script>
