<template>

	<div :class="name">
		<p>
			<a @click="prev">{{ prev_label }}</a> | <a @click="next">{{ next_label }}</a>
		</p>
		
		<div
			 v-for="slide in [currentSlide]"
			 transition="fade"
			 >
			<img
				:src="attachments[Math.abs(currentSlide) % attachments.length].sizes.large[0]" :alt="slide.alt"
			/>
		</div>

<!-- 			<div v-for="attachment in attachments" :key="attachment.id" transition="fade">
				<img :src="attachment.sizes.large[0]" :width="attachment.sizes.large[1]" :height="attachment.sizes.large[2]" :alt="attachment.alt" />
			</div> -->
	</div>

</template>

<script>

	export default {

		props: ['attachments'],

		data: function() {
			return {
				name: 'slideshow',
				currentSlide: 0,
				prev_label: sell_media.cart_labels.prev,
				next_label: sell_media.cart_labels.next,
			}
		},

		methods: {

			next: function() {
				this.currentSlide += 1
			},
			prev: function() {
				this.currentSlide -= 1
			}
		}
	}
</script>

<style lang="scss" scoped>

	.fade {
		transition: all 0.8s ease;
		overflow: hidden;
		visibility: visible;
		opacity: 1;
		position: absolute;
	}
	.fade-enter,
	.fade-leave {
		opacity: 0;
		visibility: hidden;
	}

</style>