<?php
/*
Plugin Name: AS Project Portfolio
Plugin URI: https://github.com/AhmedShaikh0/project-portfolio
Description: Adds a custom post type for managing and displaying project portfolios.
Version: 1.0.0
Author: Ahmed Shaikh
Author URI: https://github.com/AhmedShaikh0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: as-project-portfolio
*/

// Register the "Project" custom post type
function as_pp_register_project_post_type() {
    register_post_type('project', array(
        'labels' => array(
            'name' => __('Projects', 'as-project-portfolio'),
            'singular_name' => __('Project', 'as-project-portfolio'),
            'add_new' => __('Add New Project', 'as-project-portfolio'),
            'add_new_item' => __('Add New Project', 'as-project-portfolio'),
            'edit_item' => __('Edit Project', 'as-project-portfolio'),
            'new_item' => __('New Project', 'as-project-portfolio'),
            'view_item' => __('View Project', 'as-project-portfolio'),
            'search_items' => __('Search Projects', 'as-project-portfolio'),
            'not_found' => __('No projects found', 'as-project-portfolio'),
            'menu_name' => __('Projects', 'as-project-portfolio')
        ),
        'public' => true,
        'menu_position' => 5,
        'supports' => array('title'),
        'has_archive' => true,
        'show_in_menu' => true,
        'rewrite' => array('slug' => 'project'),
    ));

    
}
add_action('init', 'as_pp_register_project_post_type');

// Add meta box
function as_pp_add_meta_boxes() {
    add_meta_box(
        'as_pp_project_details',
        __('Project Details', 'as-project-portfolio'),
        'as_pp_project_meta_box_callback',
        'project'
    );
}
add_action('add_meta_boxes', 'as_pp_add_meta_boxes');

// Render meta box fields
function as_pp_project_meta_box_callback($post) {
    wp_nonce_field('as_pp_save_project_meta_nonce_action', 'as_pp_save_project_meta_nonce_field');

    $description = get_post_meta($post->ID, '_project_description', true);
    $client = get_post_meta($post->ID, '_project_client', true);
    $date = get_post_meta($post->ID, '_project_date', true);
    $url = get_post_meta($post->ID, '_project_url', true);
    ?>
    <p><label><?php esc_html_e('Description:', 'as-project-portfolio'); ?></label><br>
    <textarea name="project_description" rows="4" cols="50"><?php echo esc_textarea($description); ?></textarea></p>

    <p><label><?php esc_html_e('Client Name:', 'as-project-portfolio'); ?></label><br>
    <input type="text" name="project_client" value="<?php echo esc_attr($client); ?>"></p>

    <p><label><?php esc_html_e('Completion Date:', 'as-project-portfolio'); ?></label><br>
    <input type="date" name="project_date" value="<?php echo esc_attr($date); ?>"></p>

    <p><label><?php esc_html_e('Project URL:', 'as-project-portfolio'); ?></label><br>
    <input type="url" name="project_url" value="<?php echo esc_url($url); ?>"></p>
    <?php
}

// Save meta fields securely
function as_pp_save_project_meta($post_id) { 
    // Avoid autosaves
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Verify nonce
    $nonce = isset($_POST['as_pp_save_project_meta_nonce_field']) ? sanitize_text_field(wp_unslash($_POST['as_pp_save_project_meta_nonce_field'])) : '';
    if (!wp_verify_nonce($nonce, 'as_pp_save_project_meta_nonce_action')) {
        return;
    }

    // Check user permissions (optional but recommended)
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save each field if present
    if (isset($_POST['project_description'])) {
        update_post_meta($post_id, '_project_description', sanitize_textarea_field(wp_unslash($_POST['project_description'])));
    }

    if (isset($_POST['project_client'])) {
        update_post_meta($post_id, '_project_client', sanitize_text_field(wp_unslash($_POST['project_client'])));
    }

    if (isset($_POST['project_date'])) {
        update_post_meta($post_id, '_project_date', sanitize_text_field(wp_unslash($_POST['project_date'])));
    }

    if (isset($_POST['project_url'])) {
        update_post_meta($post_id, '_project_url', esc_url_raw(wp_unslash($_POST['project_url'])));
    }
}


add_action('save_post', 'as_pp_save_project_meta');

// Add custom submenu
function as_pp_add_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=project',
        __('Project Dashboard', 'as-project-portfolio'),
        __('Project Dashboard', 'as-project-portfolio'),
        'manage_options',
        'project-dashboard',
        'as_pp_render_admin_page'
    );
}
add_action('admin_menu', 'as_pp_add_admin_menu');

// Render admin dashboard
function as_pp_render_admin_page() {
    $projects = get_posts(array('post_type' => 'project', 'numberposts' => -1));
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('All Projects', 'as-project-portfolio'); ?></h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Title', 'as-project-portfolio'); ?></th>
                    <th><?php esc_html_e('Client', 'as-project-portfolio'); ?></th>
                    <th><?php esc_html_e('Completion Date', 'as-project-portfolio'); ?></th>
                    <th><?php esc_html_e('Actions', 'as-project-portfolio'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo esc_html($project->post_title); ?></td>
                        <td><?php echo esc_html(get_post_meta($project->ID, '_project_client', true)); ?></td>
                        <td><?php echo esc_html(get_post_meta($project->ID, '_project_date', true)); ?></td>
                        <td>
                            <a href="<?php echo esc_url(get_edit_post_link($project->ID)); ?>"><?php esc_html_e('Edit', 'as-project-portfolio'); ?></a> |
                            <a href="<?php echo esc_url(get_delete_post_link($project->ID)); ?>" onclick="return confirm('<?php esc_attr_e('Are you sure?', 'as-project-portfolio'); ?>')"><?php esc_html_e('Delete', 'as-project-portfolio'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><a class="button-primary" href="post-new.php?post_type=project"><?php esc_html_e('Add New Project', 'as-project-portfolio'); ?></a></p>
    </div>
    <?php
}

// Shortcode to display projects
function as_pp_project_shortcode() {
    $output = '';
    $projects = get_posts(array('post_type' => 'project', 'numberposts' => -1));

    foreach ($projects as $project) {
        $title = esc_html($project->post_title);
        $url = esc_url(get_post_meta($project->ID, '_project_url', true));
        $desc = esc_html(get_post_meta($project->ID, '_project_description', true));
        $client = esc_html(get_post_meta($project->ID, '_project_client', true));
        $date = esc_html(get_post_meta($project->ID, '_project_date', true));

        $output .= "<div class='project-item'>";
        $output .= "<h2><a href='{$url}' target='_blank'>{$title}</a></h2>";
        $output .= "<p><strong>" . esc_html__('Description:', 'as-project-portfolio') . "</strong> {$desc}</p>";
        $output .= "<p><strong>" . esc_html__('Client:', 'as-project-portfolio') . "</strong> {$client}</p>";
        $output .= "<p><strong>" . esc_html__('Completion Date:', 'as-project-portfolio') . "</strong> {$date}</p>";
        $output .= "</div><hr>";
    }

    return $output;
}
add_shortcode('project_portfolio', 'as_pp_project_shortcode');

// Append fields on single page
function as_pp_display_project_fields_on_single($content) {
    if (is_singular('project') && in_the_loop() && is_main_query()) {
        $post_id = get_the_ID();
        $description = get_post_meta($post_id, '_project_description', true);
        $client = get_post_meta($post_id, '_project_client', true);
        $date = get_post_meta($post_id, '_project_date', true);
        $url = get_post_meta($post_id, '_project_url', true);

        $meta_content = '<div class="project-details">';
        $meta_content .= '<p><strong>' . esc_html__('Description:', 'as-project-portfolio') . '</strong> ' . esc_html($description) . '</p>';
        $meta_content .= '<p><strong>' . esc_html__('Client:', 'as-project-portfolio') . '</strong> ' . esc_html($client) . '</p>';
        $meta_content .= '<p><strong>' . esc_html__('Completion Date:', 'as-project-portfolio') . '</strong> ' . esc_html($date) . '</p>';
        $meta_content .= '<p><strong>' . esc_html__('Project URL:', 'as-project-portfolio') . '</strong> <a href="' . esc_url($url) . '" target="_blank">' . esc_html($url) . '</a></p>';
        $meta_content .= '</div>';

        return $content . $meta_content;
    }

    return $content;
}
add_filter('the_content', 'as_pp_display_project_fields_on_single');
?>
