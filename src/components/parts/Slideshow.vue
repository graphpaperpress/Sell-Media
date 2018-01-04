<template>

	<div class="slideshow">

		<div class="slideshow-content">

			<div class="slideshow-nav">

				<button
				@click="goToSlide(currentSlide - 1)"
				:disabled="prev.disabled"
				class="slideshow-nav-left">
				<icon
				name="angle-left"
				scale="2"
				></icon>
				</button>

				<button
				@click="goToSlide(currentSlide + 1)"
				:disabled="next.disabled"
				class="slideshow-nav-right">
				<icon
				name="angle-right"
				scale="2"
				></icon>
				</button>

			</div>

			<div
			class="slideshow-image"
			v-for="slide in [currentSlide]"
			>
				<img
					:src="attachments[Math.abs(currentSlide) % attachments.length].sizes.large[0]"
					:alt="slide.alt"
				/>
			</div>

		</div>

		<div class="slideshow-thumbnails">
			<ul>
				<li v-for="(attachment, index) in attachments" @click="goToSlide(index)" :class="{ active: currentSlide === index }">
					<img :src="attachment.sizes.thumbnail[0]" :alt="attachment.alt" />
				</li>
			</ul>
		</div>

		 {{ currentSlide + 1 }} of {{ attachments.length }} 

	</div>

</template>

<script>

	export default {

		props: ['attachments'],

		data: function() {
			return {
				currentSlide: 0,

				prev: {
					label: sell_media.cart_labels.prev,
					disabled: true
				},

				next: {
					label: sell_media.cart_labels.next,
					disabled: false
				}
			}
		},

		mounted: function() {
			let attachment = this.attachments[this.currentSlide]
			this.$emit('attachment', attachment)
		},

		methods: {

			goToSlide: function(slide) {
				this.currentSlide = slide
				let attachment = this.attachments[this.currentSlide]
				this.$emit('attachment', attachment)
				
				// beginning of slides
				if (this.currentSlide > 0){
					this.prev.disabled = false
				} else {
					this.prev.disabled = true
				}

				// end of slides
				if (this.currentSlide < this.attachments.length - 1){
					this.next.disabled = false
				} else {
					this.next.disabled = true
				}
			}
		}
	}
</script>

<style lang="scss" scoped>

	.slideshow-content {
		position: relative;
		overflow: hidden;
	}

	.slideshow-nav button {
		top: calc(50% - 16px);
		position: absolute;
		display: block;
		background: transparent;
		color: rgba(255,255,255,.8);
		transition: all 0.25s ease-in-out;
		padding: .5rem 1rem;
		
		&:hover {
			background: rgba(0,0,0,.5);
			color: #fff;
		}

		&:focus {
			outline: none;
		}

		&.slideshow-nav-left {
			left: -5rem;
		}

		&.slideshow-nav-right {
			right: -5rem;
		}

		svg {
			-webkit-filter: drop-shadow(0 0 3px rgba(0,0,0,.75));
			filter: drop-shadow(0 0 3px rgba(0,0,0,.75));
		}
	}

	.slideshow-content:hover .slideshow-nav button {
		&.slideshow-nav-left {
			left: .5rem;
		}

		&.slideshow-nav-right {
			right: .5rem;
		}
	}

	.slideshow-thumbnails {
		margin: 1rem auto;
		text-align: center;

		ul {
			list-style: none;
			padding: 0;
			margin: 0;

			li {
				display: inline-block;
				margin-right: .5rem;
				width: 40px;
				height: 40px;
				border: 2px solid #555;

				&:last-child {
					margin-right: 0;
				}

				&.active {
					border-color: #fff;
					box-shadow: 0 3px 6px rgba(0,0,0,.5)
				}

				&:hover {
					cursor: pointer;
				}
			}
		}
	}

</style>