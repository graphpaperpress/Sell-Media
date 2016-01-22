<?php
/**
 * Cart class.
 *
 * @package Sell Media
 * @author Thad Allender <support@graphpaperpress.com>
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Cart class.
 */
class SellMediaClass {
	/**
	 * Vars.
	 * @var string
	 */
	private $session_id = '', $cookie = false, $itemLimit = 0, $quantityLimit = 99, $items = array(), $attributes = array(), $errors = array();

	/**
	 * Initialize shopping cart
	 *
	 * @param string  $session_id An unique ID for shopping cart session
	 * @param boolean $cookie Store cart items in cookie
	 */
	public function __construct( $session_id = '', $cookie = false ) {
		if ( ! session_id() ) {
			session_start(); }

		$this->session_id = ( ! empty( $session_id )) ? $session_id : str_replace( '.', '_', ((isset( $_SERVER['HTTP_HOST'] )) ? $_SERVER['HTTP_HOST'] : '') ) . '_cart';
		$this->cookie = ($cookie) ? true : false;

		$this->read();
	}

	/**
	 * Get errors
	 *
	 * @return array An array of errors occured
	 */
	public function getErrors() {
		return $this->errors;
	}

	/**
	 * Get last error
	 *
	 * @return string The last error occured
	 */
	public function getLastError() {
		return end( $this->errors );
	}

	/**
	 * Get list of items in cart
	 *
	 * @return array An array of items in the cart
	 */
	public function getItems() {
		return $this->items;
	}

	/**
	 * Set the maximum quantity per item accepted in cart
	 *
	 * @param integer $qty Quantity limit
	 *
	 * @return boolean Result as true/false
	 */
	public function setQuantityLimit( $qty ) {
		if ( ! $this->isInteger( $qty ) ) {
			$this->errors[] = 'Cart::setQuantityLimit($qty): $qty must be integer.';
			return false;
		}

		$this->quantityLimit = $qty;

		return true;
	}

	/**
	 * Set the maximum of item accepted in cart
	 *
	 * @param integer $limit Item limit
	 *
	 * @return boolean Result as true/false
	 */
	public function setItemLimit( $limit ) {
		if ( ! $this->isInteger( $limit ) ) {
			$this->errors[] = 'Cart::setItemLimit($limit): $limit must be integer.';
			return false;
		}

		$this->itemLimit = $limit;

		return true;
	}

	/**
	 * Add an item to cart
	 *
	 * @param integer $id An unique ID for the item
	 * @param integer $qty Quantity of item
	 *
	 * @return boolean Result as true/false
	 */
	public function add( $id, $price = 0, $qty = 1, $attr = array() ) {
		if ( ! $this->isInteger( $qty ) ) {
			$this->errors[] = 'Cart::add($qty): $qty must be integer.';
			return false;
		}

		if ( $this->itemLimit > 0 && count( $this->items ) >= $this->itemLimit ) {
			$this->clear(); }

		$cart_item_id = ( isset( $attr['item_license'] ) && '' !== $attr['item_license'] )? $id . '_' . $attr['item_license'] : $id ;
		$cart_item_id = ( isset( $attr['item_pgroup'] ) && '' !== $attr['item_pgroup'] )? $cart_item_id . '_' . $attr['item_pgroup'] : $cart_item_id;

		// Add product id
		$this->items[ $cart_item_id ]['item_id'] = $id;

		// Add quantity
		$this->items[ $cart_item_id ]['qty'] = (isset( $this->items[ $cart_item_id ]['qty'] )) ? ($this->items[ $cart_item_id ]['qty'] + $qty) : $qty;
		$this->items[ $cart_item_id ]['qty'] = ($this->items[ $cart_item_id ]['qty'] > $this->quantityLimit) ? $this->quantityLimit : $this->items[ $cart_item_id ]['qty'];

		// Add product price
		$this->items[ $cart_item_id ]['price'] = $price;

		if ( isset( $attr['item_license'] ) && '' !== $attr['item_license'] ) {
			$this->items[ $cart_item_id ]['item_license'] = $attr['item_license']; }

		if ( isset( $attr['item_pgroup'] ) && '' !== $attr['item_pgroup'] ) {
			$this->items[ $cart_item_id ]['item_pgroup'] = $attr['item_pgroup']; }

		$this->write();
		return true;
	}

	/**
	 * Add extra attributes to item in cart
	 *
	 * @param integer $id ID of targeted item
	 * @param string  $key Name of the attribute
	 * @param string  $value Value of the attribute
	 *
	 * @return boolean Result as true/false
	 */
	public function setAttribute( $id, $key = '', $value = '' ) {
		if ( ! isset( $this->items[ $id ] ) ) {
			$this->errors[] = 'Cart::setAttribute($id, $key, $value): Item #' . $id . ' does not exist.';
			return false;
		}

		if ( empty( $key ) || empty( $value ) ) {
			$this->errors[] = 'Cart::setAttribute($id, $key, $value): Invalid value for $key or $value.';
			return false;
		}

		$this->attributes[ $id ][ $key ] = $value;
		$this->write();

		return true;
	}

	/**
	 * Remove an attribute from an item
	 *
	 * @param integer $id ID of targeted item
	 * @param string  $key Name of the attribute
	 */
	public function unsetAttribute( $id, $key ) {
		unset( $this->attributes[ $id ][ $key ] );
	}

	/**
	 * Get item attribute by key
	 *
	 * @param integer $id ID of targeted item
	 * @param string  $key Name of the attribute
	 *
	 * @return string Value of the attribute
	 */
	public function getAttribute( $id, $key ) {
		if ( ! isset( $this->attributes[ $id ][ $key ] ) ) {
			$this->errors[] = 'Cart::getAttribute($id, $key): The attribute does not exist.';
			return false;
		}

		return $this->attributes[ $id ][ $key ];
	}

	/**
	 * Update item quantity
	 *
	 * @param integer $id ID of targeted item
	 * @param integer $qty Quantity
	 *
	 * @return boolean Result as true/false
	 */
	public function update( $id, $qty, $attr = array() ) {
		if ( ! $this->isInteger( $qty ) ) {
			$this->errors[] = 'Cart::update($id, $qty): $qty must be integer.';
			return false;
		}

		$cart_item_id = ( isset( $attr['license'] ) && '' !== $attr['license'] )? $id . '_' . $attr['license'] : $id ;
		$cart_item_id = ( isset( $attr['group'] ) && '' !== $attr['group'] )? $cart_item_id . '_' . $attr['group'] : $cart_item_id;

		if ( $qty < 1 ) {
			return $this->remove( $cart_item_id ); }

		// Add product id
		$this->items[ $cart_item_id ]['item_id'] = $id;

		// Add quantity
		$this->items[ $cart_item_id ]['qty'] = ($qty > $this->quantityLimit) ? $this->quantityLimit : $qty;

		$this->write();

		return true;
	}

	/**
	 * Remove item from cart
	 *
	 * @param integer $id ID of targeted item
	 */
	public function remove( $id ) {
		unset( $this->items[ $id ] );
		unset( $this->attributes[ $id ] );

		$this->write();
	}

	/**
	 * Clear all items in the cart
	 */
	public function clear() {
		$this->items = array();
		$this->attributes = array();
		$this->write();
	}

	/**
	 * Wipe out cart session and cookie
	 */
	public function destroy() {
		unset( $_SESSION[ $this->session_id ] );

		if ( $this->cookie ) {
			setcookie( $this->session_id, '', time() -86400 ); }

		$this->items = array();
		$this->attributes = array();
	}

	/**
	 * Check if a string is integer
	 *
	 * @param string $int String to validate
	 *
	 * @return boolean Result as true/false
	 */
	private function isInteger( $int ) {
		return preg_match( '/^[0-9]+$/', $int );
	}

	/**
	 * Read items from cart session
	 */
	private function read() {
		$listItem = ($this->cookie && isset( $_COOKIE[ $this->session_id ] )) ? $_COOKIE[ $this->session_id ] : (isset( $_SESSION[ $this->session_id ] ) ? $_SESSION[ $this->session_id ] : '');
		$listAttribute = (isset( $_SESSION[ $this->session_id . '_attributes' ] )) ? $_SESSION[ $this->session_id . '_attributes' ] : (($this->cookie && isset( $_COOKIE[ $this->session_id . '_attributes' ] )) ? $_COOKIE[ $this->session_id . '_attributes' ] : '');

		foreach ( $listItem as $id => $item ) {
			if ( empty( $item ) ) {
				continue; }
			$this->items[ $id ] = $item;
		}

		$attributes = @explode( ';', $listAttribute );
		foreach ( $attributes as $attribute ) {
			if ( ! strpos( $attribute, ',' ) ) {
				continue; }

			list($id, $key, $value) = @explode( ',', $attribute );

			$this->attributes[ $id ][ $key ] = $value;
		}
	}

	/**
	 * Write changes to cart session
	 */
	private function write() {
		$_SESSION[ $this->session_id ] = '';
		foreach ( $this->items as $id => $item ) {
			if ( ! $id ) {
				continue; }

			$_SESSION[ $this->session_id ][ $id ] = $item;
		}

		$_SESSION[ $this->session_id . '_attributes' ] = '';
		foreach ( $this->attributes as $id => $attributes ) {
			if ( ! $id ) {
				continue; }

			foreach ( $attributes as $key => $value ) {
				$_SESSION[ $this->session_id . '_attributes' ] .= $id . ',' . $key . ',' . $value . ';'; }
		}

		$_SESSION[ $this->session_id . '_attributes' ] = rtrim( $_SESSION[ $this->session_id . '_attributes' ], ';' );

		if ( $this->cookie ) {
			setcookie( $this->session_id, serialize( $_SESSION[ $this->session_id ] ), time() + 604800 );
			setcookie( $this->session_id . '_attributes', $_SESSION[ $this->session_id . '_attributes' ], time() + 604800 );
		}
	}
}
