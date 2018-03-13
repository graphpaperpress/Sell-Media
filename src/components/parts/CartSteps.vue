<template>

	<ul class="steps is-narrow is-medium is-centered has-content-centered">
	  <li class="steps-segment">
	    <a href="#" class="has-text-dark">
	      <span class="steps-marker">
	        <span class="icon">
	          <span class="dashicons dashicons-cart"></span>
	        </span>
	      </span>
	      <div class="steps-content">
	        <p class="heading">Cart</p>
	      </div>
	    </a>
	  </li>
	  <li class="steps-segment">
	    <a href="#" class="has-text-dark">
	      <span class="steps-marker">
	        <span class="icon">
	          <span class="dashicons dashicons-businessman"></span>
	        </span>
	      </span>
	      <div class="steps-content">
	        <p class="heading">Info</p>
	      </div>
	    </a>
	  </li>
	  <li class="steps-segment is-active has-gaps">
	    <span class="steps-marker">
	      <span class="icon">
	        <span class="dashicons dashicons-tickets-alt"></span>
	      </span>
	    </span>
	    <div class="steps-content">
	      <p class="heading">Payment</p>
	    </div>
	  </li>
	  <li class="steps-segment">
	    <span class="steps-marker is-hollow">
	      <span class="icon">
	        <span class="dashicons dashicons-yes"></span>
	      </span>
	    </span>
	    <div class="steps-content">
	      <p class="heading">Confirmation</p>
	    </div>
	  </li>
	</ul>
</template>

<script>

export default {

  data: function() {
    return {

    }
  }
}

</script>

<style lang="scss" scoped>

	.steps {
	    margin: 3rem 0 6rem;
	}

</style>
