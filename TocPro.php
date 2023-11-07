<?php
/*
Plugin Name: TocPro
Description: Creates a table of contents for selected pages, posts, and media.
Version: 1.0.0
Author: Jestin Joseph
*/
function generate_table_of_contents($content) {
    if (is_single() || is_page()) {
        $pattern = '/<h([2-6])[^>]*>.*?<\/h\1>/i'; // Regular expression to match headings h2-h6
        preg_match_all($pattern, $content, $matches);

        if (!empty($matches[0])) {
            $toc = '<div class="toc">';
            
            // Add the progress bar HTML
            $toc .= '<div class="toc-progress-bar"></div>';
            
            $toc .= '<h2>Table of Contents</h2><ul>';

            $stack = array(); // Stack to keep track of heading levels

            foreach ($matches[1] as $index => $level) {
                $id = 'toc-' . sanitize_title_with_dashes(strip_tags($matches[0][$index]));

                if ($level == 2) {
                    // Main heading
                    $toc .= '<li><a href="#' . $id . '">' . strip_tags($matches[0][$index]) . '</a></li>';
                } else {
                    // Subheading - determine nesting based on heading level
                    while (count($stack) > 0 && $level <= end($stack)) {
                        array_pop($stack);
                        $toc .= '</ul></li>';
                    }
                    $toc .= '<li><a href="#' . $id . '">' . strip_tags($matches[0][$index]) . '</a>';
                }

                array_push($stack, $level);

                $content = str_replace($matches[0][$index], '<h' . $level . ' id="' . $id . '">' . strip_tags($matches[0][$index]) . '</h' . $level . '>', $content);
            }

            // Close any open subheading lists
            while (count($stack) > 1) {
                array_pop($stack);
                $toc .= '</ul></li>';
            }

            $toc .= '</ul></div>';
            $content = $toc . $content;
        }
    }
    return $content;
}


add_filter('the_content', 'generate_table_of_contents');

function register_toc_styles() {
    wp_enqueue_style('toc-styles', plugin_dir_url(__FILE__) . 'toc-style.css');
}
add_action('wp_enqueue_scripts', 'register_toc_styles');

function register_toc_script() {
    wp_enqueue_script('toc-script', plugin_dir_url(__FILE__) . 'toc-script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'register_toc_script');

function toc_shortcode() {
}
add_shortcode('table_of_contents', 'toc_shortcode');
?>