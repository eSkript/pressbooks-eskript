<?php
/**
 * User rights management and shibboleth integration.
 *
 * @package     PressbooksEskriptPackage
 * @author      Stephan J. Müller
 * @copyright   2017 Stephan J. Müller
 * @license     GPL-2.0+
 */

/**
 * Add admin menu entry to allow users from certain organizations to subscribe.
 */

add_action( 'admin_init', function() {
	register_setting(
		'privacy_settings',
		'shibboleth_subscriber',
		'absint' // input sanitizer
	);
	add_settings_field(
		'shibboleth_subscriber',
		__( 'Who can sign up as a subscriber?', 'ethskript' ),
		'eskript_shibboleth_subscriber_callback',
		'privacy_settings',
		'privacy_settings_section'
	);
}, 11);

function eskript_shibboleth_subscriber_callback( $args ) {
	$sel = get_option( 'shibboleth_subscriber' );
	echo '<select name="shibboleth_subscriber" class="shibboleth_subscriber">';
	echo '<option value="0"' . ($sel == 0 ? ' selected = "selected"' : '') . '>' . __( 'Nobody', 'pressbooks' ) . '</option>';
	echo '<option value="1"' . ($sel == 1 ? ' selected = "selected"' : '') . '>' . __( 'ETH Users', 'pressbooks' ) . '</option>';
	echo '<option value="2"' . ($sel == 2 ? ' selected = "selected"' : '') . '>' . __( 'ETH and UZH Users', 'pressbooks' ) . '</option>';
	echo '<option value="3"' . ($sel == 3 ? ' selected = "selected"' : '') . '>' . __( 'SWITCHaai Users', 'pressbooks' ) . '</option>';
	echo '<option value="4"' . ($sel == 4 ? ' selected = "selected"' : '') . '>' . __( 'UZH Users', 'pressbooks' ) . '</option>';
	echo '<option value="5"' . ($sel == 5 ? ' selected = "selected"' : '') . '>' . __( 'FHNW Users', 'pressbooks' ) . '</option>';
	echo '<option value="6"' . ($sel == 6 ? ' selected = "selected"' : '') . '>' . __( 'ZHAW Users', 'pressbooks' ) . '</option>';
	echo '</select>';
}

/**
 * Update shibboleth_home_orgs on every login.
 *
 * NOTE: 'shibboleth_update_user_data' hook added myself; not in original shibboleth source.
 */
add_action( 'shibboleth_update_user_data', 'eskript_shibboleth_set_home_orgs' );
function eskript_shibboleth_set_home_orgs( $user_id ) {
	if ( isset( $_SERVER['homeOrganization'] ) ) {
		$orgs = explode( ';', $_SERVER['homeOrganization'] );
		update_usermeta( $user_id, 'shibboleth_home_orgs', $orgs );
	}
}

/**
 * Give users of selected organizations subscriber privileges automatically.
 *
 * @author Stephan Müller
 */
add_filter( 'user_has_cap', 'escript_give_permissions', 10, 4 );
function escript_give_permissions( $allcaps, $cap, $args, $user ) {
	if ( empty( $allcaps['read'] ) && in_array( 'read', $cap ) ) {
		$orgs = get_user_meta( $user->ID, 'shibboleth_home_orgs', true );
		if ( empty( $orgs ) ) {
			$orgs = array();
		}
		$mode = get_option( 'shibboleth_subscriber' );
		$eth = in_array( 'ethz.ch', $orgs );
		$uzh = in_array( 'uzh.ch', $orgs );
		$uzh = in_array( 'uzh.ch', $orgs );
		$fhnw = in_array( 'fhnw.ch', $orgs );
		$zhaw = in_array( 'zhaw.ch', $orgs );
		$grant = false;
		if ( $mode == 1 ) {
			$grant = $eth;
		} elseif ( $mode == 2 ) {
			$grant = $eth | $uzh;
		} elseif ( $mode == 3 ) {
			$grant = ! empty( get_usermeta( $user->ID, 'shibboleth_account' ) );
		} elseif ( $mode == 4 ) {
			$grant = $uzh;
		} elseif ( $mode == 5 ) {
			$grant = $fhnw;
		} elseif ( $mode == 6 ) {
			$grant = $zhaw;
		}
		if ( $grant ) {
			$user->add_role( 'subscriber' );
			$role = get_role( 'subscriber' );
			$allcaps = array_merge( $allcaps, $role->capabilities );
		}
	}
	return $allcaps;
}
