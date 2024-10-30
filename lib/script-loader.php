<?php
class WP_Scripts {
	var $scripts = array();
	var $queue = array();
	var $printed = array();
	var $args = array();

	function WP_Scripts() {
		$this->default_scripts();
	}

	function default_scripts() {
		$libroot = IG_BASE . "/eyecandy/libs";

		$this->add( "prototype", "$libroot/prototype.js", false, "1.5.0");
		$this->add( "scriptaculous-root", "$libroot/scriptaculous/wp-scriptaculous.js", array("prototype"), "1.6.1");
		$this->add( "scriptaculous-builder", "$libroot/scriptaculous/builder.js", array("scriptaculous-root"), "1.6.1");
		$this->add( "scriptaculous-dragdrop", "$libroot/scriptaculous/dragdrop.js", array("scriptaculous-builder"), "1.6.1");
		$this->add( "scriptaculous-effects", "$libroot/scriptaculous/effects.js", array("scriptaculous-root"), "1.6.1");
		$this->add( "scriptaculous-slider", "$libroot/scriptaculous/slider.js", array("scriptaculous-effects"), "1.6.1");
		$this->add( "scriptaculous-controls", "$libroot/scriptaculous/controls.js", array("scriptaculous-root"), "1.6.1");
		$this->add( "scriptaculous", "", array("scriptaculous-dragdrop", "scriptaculous-slider", "scriptaculous-controls"), "1.6.1");
	}

	/**
	 * Prints script tags
	 *
	 * Prints the scripts passed to it or the print queue.  Also prints all necessary dependencies.
	 *
	 * @param mixed handles (optional) Scripts to be printed.  (void) prints queue, (string) prints that script, (array of strings) prints those scripts.
	 * @return array Scripts that have been printed
	 */
	function print_scripts( $handles = false ) {
		// Print the queue if nothing is passed.  If a string is passed, print that script.  If an array is passed, print those scripts.
		$handles = false === $handles ? $this->queue : (array) $handles;
		$handles = $this->all_deps( $handles );
		$this->_print_scripts( $handles );
		return $this->printed;
	}

	/**
	 * Internally used helper function for printing script tags
	 *
	 * @param array handles Hierarchical array of scripts to be printed
	 * @see WP_Scripts::all_deps()
	 */
	function _print_scripts( $handles ) {
		global $wp_db_version;

		foreach( array_keys($handles) as $handle ) {
			if ( !$handles[$handle] )
				return;
			elseif ( is_array($handles[$handle]) )
				$this->_print_scripts( $handles[$handle] );
			if ( !in_array($handle, $this->printed) && isset($this->scripts[$handle]) ) {
				if ( $this->scripts[$handle]->src ) { // Else it defines a group.
					$ver = $this->scripts[$handle]->ver ? $this->scripts[$handle]->ver : $wp_db_version;
					if ( isset($this->args[$handle]) )
						$ver .= '&amp;' . $this->args[$handle];
					$src = 0 === strpos($this->scripts[$handle]->src, 'http://') ? $this->scripts[$handle]->src : get_option( 'siteurl' ) . $this->scripts[$handle]->src;
					$src = add_query_arg('ver', $ver, $src);
					echo "<script type='text/javascript' src='$src'></script>\n";
				}
				$this->printed[] = $handle;
			}
		}
	}


	/**
	 * Determines dependencies of scripts
	 *
	 * Recursively builds hierarchical array of script dependencies.  Does NOT catch infinite loops.
	 *
	 * @param mixed handles Accepts (string) script name or (array of strings) script names
	 * @param bool recursion Used internally when function calls itself
	 * @return array Hierarchical array of dependencies
	 */
	function all_deps( $handles, $recursion = false ) {
		if ( ! $handles = (array) $handles )
			return array();
		$return = array();
		foreach ( $handles as $handle ) {
			$handle = explode('?', $handle);
			if ( isset($handle[1]) )
				$this->args[$handle[0]] = $handle[1];
			$handle = $handle[0];
			if ( is_null($return[$handle]) ) // Prime the return array with $handles
				$return[$handle] = true;
			if ( $this->scripts[$handle]->deps ) {
				if ( false !== $return[$handle] && array_diff($this->scripts[$handle]->deps, array_keys($this->scripts)) )
					$return[$handle] = false; // Script required deps which don't exist
				else
					$return[$handle] = $this->all_deps( $this->scripts[$handle]->deps, true ); // Build the hierarchy
			}
			if ( $recursion && false === $return[$handle] )
				return false; // Cut the branch
		}
		return $return;
	}

	/**
	 * Adds script
	 *
	 * Adds the script only if no script of that name already exists
	 *
	 * @param string handle Script name
	 * @param string src Script url
	 * @param array deps (optional) Array of script names on which this script depends
	 * @param string ver (optional) Script version (used for cache busting)
	 * @return array Hierarchical array of dependencies
	 */
	function add( $handle, $src, $deps = array(), $ver = false ) {
		if ( isset($this->scripts[$handle]) )
			return false;
		$this->scripts[$handle] = new _WP_Script( $handle, $src, $deps, $ver );
		return true;
	}

	function remove( $handles ) {
		foreach ( (array) $handles as $handle )
			unset($this->scripts[$handle]);
	}

	function enqueue( $handles ) {
		foreach ( (array) $handles as $handle ) {
			$handle = explode('?', $handle);
			if ( !in_array($handle[0], $this->queue) && isset($this->scripts[$handle[0]]) ) {
				$this->queue[] = $handle[0];
				if ( isset($handle[1]) )
					$this->args[$handle[0]] = $handle[1];
			}
		}
	}

	function dequeue( $handles ) {
		foreach ( (array) $handles as $handle )
			unset( $this->queue[$handle] );
	}

	function query( $handle, $list = 'scripts' ) { // scripts, queue, or printed
		switch ( $list ) :
		case 'scripts':
			if ( isset($this->scripts[$handle]) )
				return $this->scripts[$handle];
			break;
		default:
			if ( in_array($handle, $this->$list) )
				return true;
			break;
		endswitch;
		return false;
	}

}

class _WP_Script {
	var $handle;
	var $src;
	var $deps = array();
	var $ver = false;
	var $args = false;

	function _WP_Script() {
		@list($this->handle, $this->src, $this->deps, $this->ver) = func_get_args();
		if ( !is_array($this->deps) )
			$this->deps = array();
		if ( !$this->ver )
			$this->ver = false;
	}
}

/**
 * Prints script tags in document head
 *
 * Called by admin-header.php and by wp_head hook. Since it is called by wp_head on every page load,
 * the function does not instantiate the WP_Scripts object unless script names are explicitly passed.
 * Does make use of already instantiated $wp_scripts if present.
 * Use provided wp_print_scripts hook to register/enqueue new scripts.
 *
 * @see WP_Scripts::print_scripts()
 */
function wp_print_scripts( $handles = false ) {
	do_action( 'wp_print_scripts' );
	if ( '' === $handles ) // for wp_head
		$handles = false;

	global $wp_scripts;
	if ( !is_a($wp_scripts, 'WP_Scripts') ) {
		if ( !$handles )
			return array(); // No need to instantiate if nothing's there.
		else
			$wp_scripts = new WP_Scripts();
	}

	return $wp_scripts->print_scripts( $handles );
}

function wp_register_script( $handle, $src, $deps = array(), $ver = false ) {
	global $wp_scripts;
	if ( !is_a($wp_scripts, 'WP_Scripts') )
		$wp_scripts = new WP_Scripts();

	$wp_scripts->add( $handle, $src, $deps, $ver );
}

function wp_deregister_script( $handle ) {
	global $wp_scripts;
	if ( !is_a($wp_scripts, 'WP_Scripts') )
		$wp_scripts = new WP_Scripts();

	$wp_scripts->remove( $handle );
}

/**
 * Equeues script
 *
 * Registers the script if src provided (does NOT overwrite) and enqueues.
 *
 * @see WP_Script::add(), WP_Script::enqueue()
*/
function wp_enqueue_script( $handle, $src = false, $deps = array(), $ver = false ) {
	global $wp_scripts;
	if ( !is_a($wp_scripts, 'WP_Scripts') )
		$wp_scripts = new WP_Scripts();

	if ( $src ) {
		$_handle = explode('?', $handle);
		$wp_scripts->add( $_handle[0], $src, $deps, $ver );
	}
	$wp_scripts->enqueue( $handle );
}

add_action("wp_head", "wp_print_scripts");
?>
