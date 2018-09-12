<?php
// see https://codex.wordpress.org/Function_Reference/register_post_type

function create_example() {

  $args = [
    'labels' => [
      'name' => __( 'Examples' ),
      'singular_name' => __( 'example' )
    ],
    'public' => true,
    'has_archive' => true,
    'menu_icon' => 'dashicons-calendar'
  ];

  register_post_type( 'example',
    $args
    );
}
add_action( 'init', 'create_example' );
