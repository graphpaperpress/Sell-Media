<template>

	<div :class="name">
		<p>
			<a @click="prev">Previous</a> || <a @click="next">Next</a>
		</p>
		
		<div
			 v-for="number in [currentNumber]"
			 transition="fade"
			 >
			<img
				:src="attachments[Math.abs(currentNumber) % images.length].sizes.large[0]"
			/>
		</div>
	</div>

</template>

<script>

	export default {

		props: ['attachments'],

		data: function() {
			return {
				name: 'slideshow',
				images: ['http://i.imgur.com/vYdoAKu.jpg', 'http://i.imgur.com/PUD9HQL.jpg', 'http://i.imgur.com/Lfv18Sb.jpg', 'http://i.imgur.com/tmVJtna.jpg', 'http://i.imgur.com/ZfFAkWZ.jpg'],
				currentNumber: 0,
				timer: null
			}
		},

		created: function() {
			console.log(this.attachments);
		},

		methods: {

			next: function() {
				this.currentNumber += 1
			},
			prev: function() {
				this.currentNumber -= 1
			}
		}
	}
</script>

<style lang="scss" scoped>

	.fade-transition {
		transition: all 0.8s ease;
		overflow: hidden;
		visibility: visible;
		opacity: 1;
		position: absolute;
	}
	.fade-enter, .fade-leave {
		opacity: 0;
		visibility: hidden;
	}

</style>