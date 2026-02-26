<?php
/**
 * Theme functions and definitions.
 */
function cargotrucking_child_enqueue_styles() {
wp_enqueue_style( 'cargo-trucking-child-style',
get_stylesheet_directory_uri() . '/style.css',
array(),
wp_get_theme()->get('Version')
);
}

add_action( 'wp_enqueue_scripts', 'cargotrucking_child_enqueue_styles', 11 );