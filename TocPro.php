<?php
/*
Plugin Name: TocPro
Description: Creates a table of contents for selected pages, posts, and media.
Version: 1.0.0
Author: Jestin Joseph
*/

// Function to generate table of contents
function generate_table_of_contents($content) {
    if (get_option('enable_table_of_contents') == '1' && (is_single() || is_page())) {
        $pattern = '/<h([2-6])[^>]*>.*?<\/h\1>/i';
        preg_match_all($pattern, $content, $matches);

        if (!empty($matches[0])) {
            $toc = '<div class="tocpro">';
            if (get_option('enable_progress_bar') == '1') {
                $toc .= '<div class="tocpro-progress-bar"></div>';
            }
            $toc .= '<h2>Table of Contents</h2>';
            $toc .= '<ol>';
            $stack = array();

            foreach ($matches[1] as $index => $level) {
                $id = 'toc-' . sanitize_title_with_dashes(strip_tags($matches[0][$index]));
                $headingText = strip_tags($matches[0][$index]);
                $words = explode(' ', $headingText);
                $shortenedText = implode(' ', array_slice($words, 0, 5));
                $hasMoreContent = count($words) > 5;

                while ($level > end($stack)) {
                    $toc .= '<ol>';
                    array_push($stack, $level);
                }

                while ($level < end($stack)) {
                    $toc .= '</ol>';
                    array_pop($stack);
                }

                $toc .= '<li><a href="#' . $id . '">' . $shortenedText;
                if ($hasMoreContent) {
                    $toc .= '...';
                }
                $toc .= '</a></li>';

                $content = str_replace($matches[0][$index], '<h' . $level . ' id="' . $id . '">' . strip_tags($matches[0][$index]) . '</h' . $level . '>', $content);
            }

            while (!empty($stack)) {
                $toc .= '</ol>';
                array_pop($stack);
            }

            $toc .= '</ol></div>';
            $content = $toc . $content;
        }
    }

    return $content;
}

// Add a menu to enable or disable the table of contents
function add_plugin_menu() {
    add_menu_page('TocPro Settings', 'TOCPro', 'manage_options', 'tocpro-settings', 'plugin_settings_page', '', 30);
}

// Modify the settings page to include color picker fields
function plugin_settings_page() {
    ?>
    <div class="wrap">
        <div class="wrap tocpro-main">
        <h2>TOCPro Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('tocpro-settings');
            do_settings_sections('tocpro-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Enable Table of Contents</th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="enable_table_of_contents" value="1" <?php checked(get_option('enable_table_of_contents'), '1'); ?>>
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Enable Progress bar</th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="enable_progress_bar" value="1" <?php checked(get_option('enable_progress_bar'), '1'); ?>>
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>
                <!-- Add settings for TOC styles using color picker -->
                <tr valign="top">
                    <th scope="row">TOC Text Color</th>
                    <td>
                        <input type="text" class="tocpro-color-picker" name="tocpro_text_color" value="<?php echo esc_attr(get_option('tocpro_text_color')); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">tocpro Background Color</th>
                    <td>
                        <input type="text" class="tocpro-color-picker" name="tocpro_background_color" value="<?php echo esc_attr(get_option('tocpro_background_color')); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Progress Bar Color</th>
                    <td>
                        <input type="text" class="tocpro-color-picker" name="progress_bar_color" value="<?php echo esc_attr(get_option('progress_bar_color')); ?>">
                    </td>
                </tr>
                <!-- Add more style settings as needed -->
            </table>
            <?php submit_button(); ?>
        </form>
        </div>
    </div>
    <style>
        .tocpro-main {
  border: 2px solid rgb(114, 66, 234);
  height: 100%;
  padding: 20px;
  margin-top: 20px;
  border-radius: 14px;
}
                .switch {
            position: relative;
            display: inline-block;
            width: 40px; /* Reduced width */
            height: 20px; /* Reduced height */
        }
        .switch input {
            display: none;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 10px; /* Round slider */
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 16px; /* Reduced height */
            width: 16px; /* Reduced width */
            left: 2px; /* Adjusted position */
            bottom: 2px; /* Adjusted position */
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 50%; /* Round knob */
        }
        input:checked + .slider {
            background-color: #2196F3;
        }
        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }
        input:checked + .slider:before {
            -webkit-transform: translateX(20px); /* Adjusted position */
            -ms-transform: translateX(20px); /* Adjusted position */
            transform: translateX(20px); /* Adjusted position */
        }
        </style>
    <?php
}

// Enqueue WordPress Color Picker scripts and styles
function load_color_picker() {
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');
}
add_action('admin_enqueue_scripts', 'load_color_picker');

// Initialize the Color Picker for the input fields
function init_color_picker() {
    ?>
    <script>
        jQuery(document).ready(function($) {
            $('.tocpro-color-picker').wpColorPicker();
        });
    </script>
    <?php
}
add_action('admin_footer', 'init_color_picker');

// Include styles for the tocpro and progress bar based on user settings
function include_tocpro_styles() {
    ?>
    <style>
        .tocpro {
            color: <?php echo esc_attr(get_option('tocpro_text_color')); ?>;
            background-color: <?php echo esc_attr(get_option('tocpro_background_color')); ?>;
            font-size: <?php echo esc_attr(get_option('tocpro_font_size')); ?>px;
            /* Add more styles as needed */
        }
        .tocpro a{
            color: <?php echo esc_attr(get_option('tocpro_text_color')); ?>;
        }
        .tocpro-progress-bar {
            background-color: <?php echo esc_attr(get_option('progress_bar_color')); ?>;
        }

    </style>
    <?php
}
add_action('wp_head', 'include_tocpro_styles');

// Register the setting and options, including color picker fields
function register_plugin_settings() {
    register_setting('tocpro-settings', 'enable_table_of_contents');
    register_setting('tocpro-settings', 'enable_progress_bar');
    register_setting('tocpro-settings', 'tocpro_text_color');
    register_setting('tocpro-settings', 'tocpro_background_color');
    register_setting('tocpro-settings', 'progress_bar_color');
}
add_action('admin_menu', 'add_plugin_menu');
add_action('admin_init', 'register_plugin_settings');
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
