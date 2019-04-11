<?php

/**
 * DISCLAIMER: This is only usable with ACF 5.8 version (which are still in BETA)
 * 02.04.2019
 */

add_action('acf/init', 'as_acf_init');
function as_acf_init() {

	// check function exists
	if( function_exists('acf_register_block') ) {

		// register a testimonial block
		acf_register_block(array(
			'name'				=> 'antistatique',
			'title'				=> __('antistatique'),
			'description'		=> __('A custom antistatique block.'),
			'render_callback'	=> 'as_block_render',
			'category'			=> 'formatting',
			'icon'				=> 'admin-comments',
			'keywords'			=> array( 'testimonial', 'quote' ),
		));
	}
}

function as_block_render( $block ) {
	// ACF fields can be found here:
	$context = Timber::get_context();
	$context['fields'] = $block['data'];
	$output = Timber::compile( 'view.twig', $context );
	echo $output;
}
