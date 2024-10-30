<?php 
/**
 * miscellaneous utility functions
 */

/**
 * os.path.join() :P
 * 
 * @return string
 */
function ig_p_join(){
	$pieces = func_get_args();
	$pieces = array_map(create_function('$s', 'return rtrim($s, DIRECTORY_SEPARATOR);'), $pieces);
	return join(DIRECTORY_SEPARATOR, $pieces);
}

/**
 * return the file extension for the given filename
 * 
 * @param string $filename 
 * @return string
 */
function file_ext($filename){
	return substr($filename, strrpos($filename, ".") + 1);
}

/**
 * implement is_front_page for older WP versions
 */
if(!function_exists("is_front_page")) {
	/**
	 * is_front_page() - Is it the front of the site, whether blog view or a WP Page?
	 *
	 * @since 2.5
	 * @uses is_home
	 * @uses get_option
	 *
	 * @return bool True if front of site
	 */
	function is_front_page () {
		// most likely case
		if ( 'posts' == get_option('show_on_front') && is_home() )
			return true;
		elseif ( 'page' == get_option('show_on_front') && get_option('page_on_front') && is_page(get_option('page_on_front')) )
			return true;
		else
			return false;
	}
}
/**
 * implement is_singular for wp 2.0
 */
if(!function_exists("is_singular")) {
	function is_singular() {
		return is_single() || is_page() || is_attachment();
	}
}
?>
