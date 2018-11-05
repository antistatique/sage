<?php

namespace App;

use Roots\Sage\Container;

/**
 * Get the sage container.
 *
 * @param string $abstract
 * @param array  $parameters
 * @param Container $container
 * @return Container|mixed
 */
function sage($abstract = null, $parameters = [], Container $container = null)
{
    $container = $container ?: Container::getInstance();
    if (!$abstract) {
        return $container;
    }
    return $container->bound($abstract)
        ? $container->makeWith($abstract, $parameters)
        : $container->makeWith("sage.{$abstract}", $parameters);
}

/**
 * Get / set the specified configuration value.
 *
 * If an array is passed as the key, we will assume you want to set an array of values.
 *
 * @param array|string $key
 * @param mixed $default
 * @return mixed|\Roots\Sage\Config
 * @copyright Taylor Otwell
 * @link https://github.com/laravel/framework/blob/c0970285/src/Illuminate/Foundation/helpers.php#L254-L265
 */
function config($key = null, $default = null)
{
    if (is_null($key)) {
        return sage('config');
    }
    if (is_array($key)) {
        return sage('config')->set($key);
    }
    return sage('config')->get($key, $default);
}

/**
 * @param string $file
 * @param array $data
 * @return string
 */
function template($file, $data = [])
{
    if (!is_admin() && remove_action('wp_head', 'wp_enqueue_scripts', 1)) {
        wp_enqueue_scripts();
    }

    return sage('blade')->render($file, $data);
}

/**
 * Retrieve path to a compiled blade view
 * @param $file
 * @param array $data
 * @return string
 */
function template_path($file, $data = [])
{
    return sage('blade')->compiledPath($file, $data);
}

/**
 * @param $asset
 * @return string
 */
function asset_path($asset)
{
    return sage('assets')->getUri($asset);
}

/**
 * @param string|string[] $templates Possible template files
 * @return array
 */
function filter_templates($templates)
{
    $paths = apply_filters('sage/filter_templates/paths', [
        'views',
        'resources/views'
    ]);
    $paths_pattern = "#^(" . implode('|', $paths) . ")/#";

    return collect($templates)
        ->map(function ($template) use ($paths_pattern) {
            /** Remove .blade.php/.blade/.php from template names */
            $template = preg_replace('#\.(blade\.?)?(php)?$#', '', ltrim($template));

            /** Remove partial $paths from the beginning of template names */
            if (strpos($template, '/')) {
                $template = preg_replace($paths_pattern, '', $template);
            }

            return $template;
        })
        ->flatMap(function ($template) use ($paths) {
            return collect($paths)
                ->flatMap(function ($path) use ($template) {
                    return [
                        "{$path}/{$template}.blade.php",
                        "{$path}/{$template}.php",
                    ];
                })
                ->concat([
                    "{$template}.blade.php",
                    "{$template}.php",
                ]);
        })
        ->filter()
        ->unique()
        ->all();
}

/**
 * @param string|string[] $templates Relative path to possible template files
 * @return string Location of the template
 */
function locate_template($templates)
{
    return \locate_template(filter_templates($templates));
}

/**
 * Determine whether to show the sidebar
 * @return bool
 */
function display_sidebar()
{
    static $display;
    isset($display) || $display = apply_filters('sage/display_sidebar', false);
    return $display;
}

/**
 * change excerpt length
 * These functions ares used by the smart_excerpt() function
 */
function smart_excerpt_length( $length = 0 ) {
	return 55;
}
add_filter( 'excerpt_length', 'smart_excerpt_length', 9999 );

function smart_excerpt_more( $more = '') {
    return ' (...)';
}
add_filter( 'excerpt_more', 'smart_excerpt_more' );

/**
 * custom function for excerpts. With a given post, returns a formatted excerpt.
 * it will have the same behaviour whether it takes a user-defined excerpt or generates one from the content
 * @arg $post a WP_Post object
 */
function smart_excerpt($post = null) {
	if(!$post) return '';
	$excerpt = $post->post_excerpt;
	if (strlen($excerpt) == 0) {
		// custom excerpt is empty, let's generate one
		$excerpt = strip_shortcodes($post->post_content);
		$excerpt = str_replace(array("\r\n", "\r", "\n", "&nbsp;"), "", $excerpt);
		$excerpt = wp_trim_words($excerpt, smart_excerpt_length(), smart_excerpt_more());
	} else {
		// custom excerpt is set, let's trim it
		$excerpt = wp_trim_words($excerpt, smart_excerpt_length(), smart_excerpt_more());
	}
	return $excerpt;
}

/**
 * This fuctions returns an array with menu items to include in the breadcrumbs
 */
function get_breadcrumb() {
	$context = Timber::get_context();
    $post = new Timber\Post();
        $items = $context['menu']->items;
        $crumbs = [];

    foreach ($items as $item) {
        if ($item->current_item_parent
        || $item->current_item_ancestor
        || $item->current) {
        $crumbs[] = $item;
        }
    }

    if (is_single() && get_post_type() == 'post') {
        // add article page to the breadcrumbs
        $page_for_posts = new Timber\Post(get_option('page_for_posts'));
        $page_for_posts->url = get_permalink($page_for_posts);
        $crumbs[] = $page_for_posts;

        // add current post title to the breadcrumbs
        $post->url = get_permalink($page_for_posts);
        $post->current = true;
        $crumbs[] = $post;
    }

	return $crumbs;
}
