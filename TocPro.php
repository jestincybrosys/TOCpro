<?php
/*
Plugin Name: TocPro
Description: Creates a table of contents for selected pages, posts, and media.
Version: 1.0.0
Author: Jestin Joseph
*/

function generate_table_of_contents($content) {
    if (get_option('enable_table_of_contents') == '1' && (is_single() || is_page())) {
        $pattern = '/<h([2-6])[^>]*>.*?<\/h\1>/i';
        preg_match_all($pattern, $content, $matches);

        if (!empty($matches[0])) {
            $toc = '<div class="tocpro tocpro-set-width">';
    
            $toc .= '<p>' . esc_attr(get_option('tocpro_header_label')) . '</p>';
            // $toc .= '<ol type="' . esc_attr(get_option('tocpro_ol_type')) . '">'; 
            $stack = array();

            foreach ($matches[1] as $index => $level) {
                $id = 'toc-' . sanitize_title_with_dashes(strip_tags($matches[0][$index]));
                $headingText = strip_tags($matches[0][$index]);
                $words = explode(' ', $headingText);
                $shortenedText = implode(' ', array_slice($words, 0, 5));
                $hasMoreContent = count($words) > 5;

                while ($level > end($stack)) {
                    $toc .= '<ol class="custom-ol" type="' . esc_attr(get_option('tocpro_ol_type')) . '">';
                    array_push($stack, $level);
                }

                while ($level < end($stack)) {
                    $toc .= '</ol></li>';
                    array_pop($stack);
                }

                $toc .= '<li><a href="#' . $id . '">' . $shortenedText;
                if ($hasMoreContent) {
                    $toc .= '...';
                }
                $toc .= '</a>';

                $content = str_replace($matches[0][$index], '<h' . $level . ' id="' . $id . '">' . strip_tags($matches[0][$index]) . '</h' . $level . '>', $content);
            }

            while (!empty($stack)) {
                $toc .= '</ol>';
                array_pop($stack);
            }

            $toc .= '</div>';
            $content = $toc . $content;
        }
    }

    return $content;
}

function generate_individual_progress_bar($content) {
    if (get_option('enable_progress_bar') == '1' && (is_single() || is_page())) {
            $progress_bar = '<div class="tocpro-progress-bar"></div>';
            $content = $progress_bar . $content;
        
    }

    return $content;
}


function add_plugin_menu() {
    add_menu_page('TocPro Settings', 'TOCPro', 'manage_options', 'tocpro-settings', 'plugin_settings_page', '', 30);
}

function plugin_settings_page() {
    ?>
    <div class="wrap">

        <div class="wrap tocpro-main">

        <div class="tocpro-head"><h2>TOCPro Settings</h2><p>Globel Settings</p></div>
        <?php
            if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
                echo '<div class="toast">
                <div class="toast-content">
                <img width="20px" src="' . plugins_url('assets/accept.svg', __FILE__) . '" alt="Icon" />
                <div class="message">
                    <span class="message-text text-1">Success</span>
                    <span class="message-text text-2">Your changes have been saved</span>
                </div>
                </div>
                <img class="toast-close" width="10px" src="' . plugins_url('assets/close.svg', __FILE__) . '" alt="Icon" />

                <div class="progress"></div>
            </div>';
            }
        ?>
    <div class="position-div">
        <header class="tacpro-div-head">
            <a class="tacpro-link active" href="javascript:void(0);"  onclick="showTable('genaral',this)" ><img width="20px" src="<?php echo plugins_url('assets/settings-gears_60473.svg', __FILE__); ?>" alt="Icon" /> <span class="tocpro-hide-mob"> Genaral</span></a>
            <a class="tacpro-link" href="javascript:void(0);" onclick="showTable('style',this)"><img width="20px" src="<?php echo plugins_url('assets/brush_8313131.svg', __FILE__); ?>" alt="Icon" /><span class="tocpro-hide-mob"> Style</span>  </a>
            <a class="tacpro-link" href="javascript:void(0);" onclick="showTable('progressbar',this)" ><img width="20px" src="<?php echo plugins_url('assets/load-bar_40471.svg', __FILE__); ?>" alt="Icon" /><span class="tocpro-hide-mob"> Progressbar</span> </a>
            <a class="tacpro-link" href="javascript:void(0);" onclick="showTable('autoinsert',this)"><img width="20px" src="<?php echo plugins_url('assets/browser_493223.svg', __FILE__); ?>" alt="Icon" /><span class="tocpro-hide-mob"> Auto Insert</span></a>
        </header>
        <section class="tacpro-div-section">
        <form method="post" action="options.php">
            <?php
            settings_fields('tocpro-settings');
            do_settings_sections('tocpro-settings');
            ?>
                <div id="genaral" class="table-container" style="display: block;">
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
                    <th scope="row">Header label</th>
                    <td>
                        <input type="text" class="" name="tocpro_header_label" value="<?php echo esc_attr(get_option('tocpro_header_label')); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Table Text Color</th>
                    <td>
                        <input type="text" class="tocpro-color-picker" name="tocpro_text_color" value="<?php echo esc_attr(get_option('tocpro_text_color')); ?>">
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Table Background Color</th>
                    <td>
                        <input type="text" class="tocpro-color-picker" name="tocpro_background_color" value="<?php echo esc_attr(get_option('tocpro_background_color')); ?>">
                    </td>
                </tr>
            </table>
            </div>
            <div id="style" class="table-container">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Width</th>
                    <td>
                        <select name="tocpro_width">
                            <option value="auto" <?php selected(get_option('tocpro_width'), 'Auto'); ?>>Auto</option>
                            <option value="100%" <?php selected(get_option('tocpro_width'), '100%'); ?>>100%</option>
                            
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Min Widtgh</th>
                    <td>
                        <div class="input-with-px">
                            <input type="text" class="min_width_field"  name="tocpro_min_width" value="<?php echo esc_attr(get_option('tocpro_min_width')); ?>">
                        </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Max width</th>
                    <td>
                    <div class="input-with-px">
                        <input type="text" class="" name="tocpro_max_width" value="<?php echo esc_attr(get_option('tocpro_max_width')); ?>">
                </div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Gap from Top (in pixels)</th>
                    <td>
                    <div class="input-with-px">
                        <input type="text" name="gap_from_top" value="<?php echo esc_attr(get_option('gap_from_top', 20)); ?>">
                        </div>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">TOC List Type</th>
                    <td>
                    <select name="tocpro_ol_type">
                    <?php
                    $ol_types = array(
                        '1' => 'Decimal',
                        'a' => 'Lower Alpha',
                        'A' => 'Upper Alpha',
                        'i' => 'Lower Roman',
                        'I' => 'Upper Roman',
                        'circle' => 'Circle',
                        'disc' => 'Filled Circle',
                        'square' => 'Filled Square',
                        'lower-greek' => 'Lower Greek',
                        'upper-greek' => 'Upper Greek',
                        'armenian' => 'Armenian',
                        'cjk-ideographic' => 'CJK Ideographic',
                        'georgian' => 'Georgian',
                        'hebrew' => 'Hebrew',
                        'hiragana' => 'Hiragana',
                        'katakana' => 'Katakana',
                        'decimal-leading-zero' => 'Decimal Leading Zero',
                        'lower-latin' => 'Lower Latin',
                        'upper-latin' => 'Upper Latin',
                        'lower-armenian' => 'Lower Armenian',
                        'upper-armenian' => 'Upper Armenian',
                        'lower-hebrew' => 'Lower Hebrew',
                        'upper-hebrew' => 'Upper Hebrew',
                        'lower-hiragana' => 'Lower Hiragana',
                        'upper-hiragana' => 'Upper Hiragana',
                        'lower-katakana' => 'Lower Katakana',
                        'upper-katakana' => 'Upper Katakana',
                        'decimal-leading-zero' => 'Decimal Leading Zero',
                        'lower-latin' => 'Lower Latin',
                        'upper-latin' => 'Upper Latin',
                        'lower-georgian' => 'Lower Georgian',
                        'upper-georgian' => 'Upper Georgian',
                        'lower-cjk-ideographic' => 'Lower CJK Ideographic',
                        'upper-cjk-ideographic' => 'Upper CJK Ideographic',
                        'malayalam' => 'Malayalam',
                        // Add more options as needed
                    );

                    $selected_type = get_option('tocpro_ol_type'); // Get the selected counter type from your settings

                    foreach ($ol_types as $value => $label) {
                        $selected = selected($selected_type, $value, false);
                        echo "<option value='$value' $selected>$label</option>";
                    }
                    ?>
                </select>
                    </td>
                </tr>
            </table>
            </div>

            <div id="progressbar" class="table-container">
            <table class="form-table">
   
                <tr valign="top">
                    <th scope="row">Enable Progress bar</th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="enable_progress_bar" value="1" <?php checked(get_option('enable_progress_bar'), '1'); ?>>
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Progress Bar Color</th>
                    <td>
                        <input type="text" class="tocpro-color-picker" name="progress_bar_color" value="<?php echo esc_attr(get_option('progress_bar_color')); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Width</th>
                    <td>
                    <div class="input-with-px">
                    <input type="text" class="tocpro-progress-bar-width" name="progress_bar_width" value="<?php echo esc_attr(get_option('progress_bar_width')); ?>">
                    </div>
                    </td>
                </tr>
                </table>

            </div> 
            <div id="autoinsert" class="table-container">
            <table class="form-table">
   
                <tr valign="top">
                    <th scope="row">Enable Progress bar</th>
                    <td>
                        <label class="switch">
                            <input type="checkbox" name="enable_progress_bar" value="1" <?php checked(get_option('enable_progress_bar'), '1'); ?>>
                            <span class="slider round"></span>
                        </label>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Progress Bar Color</th>
                    <td>
                        <input type="text" class="tocpro-color-picker" name="progress_bar_color" value="<?php echo esc_attr(get_option('progress_bar_color')); ?>">
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Width</th>
                    <td>
                    <div class="input-with-px">
                    <input type="text" class="tocpro-progress-bar-width" name="progress_bar_width" value="<?php echo esc_attr(get_option('progress_bar_width')); ?>">
                    </div>
                    </td>
                </tr>
                </table>

            </div>
            <?php submit_button('Save changes','toast-btn', false); ?>
        </form>
        </section>
    </div>
        </div>
    </div>
    <script>
        var activeLink = document.querySelector('.tacpro-link.active');

        function showTable(tableId, element) {
            // Hide all tables
            var tables = document.querySelectorAll('.table-container');
            tables.forEach(function(table) {
                table.style.display = 'none';
            });

            // Remove "active" class from all menu links
            var menuLinks = document.querySelectorAll('.tacpro-link');
            menuLinks.forEach(function(link) {
                link.classList.remove('active');
            });

            // Show the selected table
            var selectedTable = document.getElementById(tableId);
            if (selectedTable) {
                selectedTable.style.display = 'block';
            }

            // Add "active" class to the clicked menu link
            element.classList.add('active');
        }

            var toast = document.querySelector(".toast");
            var btn = document.querySelector(".toast-btn");
            var close = document.querySelector(".toast-close");
            var progress = document.querySelector(".progress");

            btn.addEventListener("click", () =>{
            toast.classList.add("active");
            progress.classList.add("active");

            setTimeout(() =>{
                toast.classList.remove("active");
            }, 2000)

            setTimeout(() =>{
                progress.classList.remove("active");
            }, 2300)
            })

            close.addEventListener("click", () =>{
            toast.classList.remove("active");

            setTimeout(() =>{
                progress.classList.remove("active");
            }, 300)
            })
        </script>

    <?php
}

function load_color_picker() {
    wp_enqueue_script('wp-color-picker');
    wp_enqueue_style('wp-color-picker');
}
add_action('admin_enqueue_scripts', 'load_color_picker');

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

function include_tocpro_styles() {
    ?>
    <?php
$minWidth = get_option('tocpro_min_width');
if (empty($minWidth)) {
    $minWidth = 'unset';
}
$selected_type = get_option('tocpro_ol_type'); // Get the selected counter type from your settings

// Define an array to map the counter styles to their CSS values
$counter_styles = array(
    '1' => 'decimal',
    'a' => 'lower-alpha',
    'A' => 'upper-alpha',
    'i' => 'lower-roman',
    'I' => 'upper-roman',
    'circle' => 'circle',
    'disc' => 'disc',
    'square' => 'square',
    'lower-greek' => 'lower-greek',
    'upper-greek' => 'upper-greek',
    'armenian' => 'armenian',
    'cjk-ideographic' => 'cjk-ideographic',
    'georgian' => 'georgian',
    'hebrew' => 'hebrew',
    'hiragana' => 'hiragana',
    'katakana' => 'katakana',
    'decimal-leading-zero' => 'decimal-leading-zero',
    'lower-latin' => 'lower-latin',
    'upper-latin' => 'upper-latin',
    'lower-armenian' => 'lower-armenian',
    'upper-armenian' => 'upper-armenian',
    'lower-hebrew' => 'lower-hebrew',
    'upper-hebrew' => 'upper-hebrew',
    'lower-hiragana' => 'lower-hiragana',
    'upper-hiragana' => 'upper-hiragana',
    'lower-katakana' => 'lower-katakana',
    'upper-katakana' => 'upper-katakana',
    'decimal-leading-zero' => 'decimal-leading-zero',
    'lower-latin' => 'lower-latin',
    'upper-latin' => 'upper-latin',
    'lower-georgian' => 'lower-georgian',
    'upper-georgian' => 'upper-georgian',
    'lower-cjk-ideographic' => 'lower-cjk-ideographic',
    'upper-cjk-ideographic' => 'upper-cjk-ideographic',
    'malayalam' => 'malayalam',
    // Add more styles as needed
);

$counter_style = isset($counter_styles[$selected_type]) ? $counter_styles[$selected_type] : 'decimal';

?>


    <style>
        .tocpro {
            color: <?php echo esc_attr(get_option('tocpro_text_color')); ?>;
            background-color: <?php echo esc_attr(get_option('tocpro_background_color')); ?>;
            font-size: <?php echo esc_attr(get_option('tocpro_font_size')); ?>px;
        }
        .tocpro a{
            color: <?php echo esc_attr(get_option('tocpro_text_color')); ?>;
        }
        .tocpro-progress-bar {
            background-color: <?php echo esc_attr(get_option('progress_bar_color')); ?>;
        }
        .tocpro-set-width{
            width:<?php echo esc_attr(get_option('tocpro_width')); ?>;
            min-width: <?php echo esc_attr($minWidth); ?>px !important;
            max-width: <?php echo esc_attr(get_option('tocpro_max_width')); ?>px !important;
        }      
        .custom-ol a:before {
            content: counters(item, '.' , <?php echo $counter_style; ?>) '. ';
        }
        .tocpro-progress-bar {
            height:<?php echo esc_attr(get_option('progress_bar_width')); ?>px;
        }
    </style>

<script>
    var offset = parseInt(<?php $gap_from_top = get_option('gap_from_top', 20); echo $gap_from_top; ?>);
</script>


    <?php
}
add_action('wp_head', 'include_tocpro_styles');

function register_plugin_settings() {
    register_setting('tocpro-settings', 'enable_table_of_contents');
    register_setting('tocpro-settings', 'enable_progress_bar');
    register_setting('tocpro-settings', 'tocpro_text_color');
    register_setting('tocpro-settings', 'tocpro_background_color');
    register_setting('tocpro-settings', 'progress_bar_color');
    register_setting('tocpro-settings', 'progress_bar_width');
    register_setting('tocpro-settings', 'tocpro_width');
    register_setting('tocpro-settings', 'tocpro_min_width');
    register_setting('tocpro-settings', 'tocpro_max_width');
    register_setting('tocpro-settings', 'gap_from_top');
    register_setting('tocpro-settings', 'tocpro_ol_type'); 
    register_setting('tocpro-settings', 'tocpro_header_label');
}
add_action('admin_menu', 'add_plugin_menu');
add_action('admin_init', 'register_plugin_settings');
add_filter('the_content', 'generate_table_of_contents'); // Add table of contents
add_filter('the_content', 'generate_individual_progress_bar'); // Add individual progress bar

function register_toc_styles() {
    wp_enqueue_style('toc-admin-styles', plugin_dir_url(__FILE__) . 'toc-admin-style.css');

    wp_enqueue_script('toc-script', plugin_dir_url(__FILE__) . 'toc-script.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'register_toc_styles');

function register_toc_script() {
    wp_enqueue_style('toc-styles', plugin_dir_url(__FILE__) . 'toc-style.css');

    wp_enqueue_script('toc-script', plugin_dir_url(__FILE__) . 'toc-script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'register_toc_script');

function toc_shortcode() {
}
add_shortcode('table_of_contents', 'toc_shortcode');

