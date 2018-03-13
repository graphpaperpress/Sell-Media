<template>

	<div>

		<div class="tabs">
			<ul>
				<li v-for="(tab, index) in tabs" :key="index" :class="{ 'is-active': tab.isActive }">
					<a :href="tab.href" @click="selectTab(tab)">{{ tab.name }}</a>
				</li>
			</ul>
		</div>

		<div class="tabs-details">
			<slot></slot>
		</div>

	</div>

</template>

<script>

export default {

  data: function() {
    return {
      tabs: []
    }
  },

  created() {
    this.tabs = this.$children
    // console.log(this.$children)
  },

	    methods: {
    selectTab(selectedTab) {
      this.tabs.forEach(tab => {
        tab.isActive = (tab === selectedTab)
      })
    }
  }
}
</script>
