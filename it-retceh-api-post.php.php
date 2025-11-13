<?php
/*
Plugin Name: It Retceh API Post
Plugin URI: 
Description: Plugin for posting via API with complete WordPress data and author selection
Version: 1.0
Author: Sodikin 
Author URI: https://sodikinnaa.github.io/
License: GPL2
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Add menu to WordPress admin
add_action('admin_menu', 'bb_add_admin_menu');
function bb_add_admin_menu() {
    add_menu_page(
        'Buta Buku API Settings',
        'It Retceh Setting',
        'manage_options',
        'buta-buku-settings',
        'bb_settings_page',
        'dashicons-admin-generic'
    );
}

// Generate random API key
function bb_generate_api_key() {
    $chars = array_merge(range('a', 'z'), range('A', 'Z'), range(0, 9));
    $key = '';
    for ($i = 0; $i < 32; $i++) {
        $key .= $chars[array_rand($chars)];
    }
    return $key;
}

// Create the settings page
function bb_settings_page() {
    // Handle API key generation
    if (isset($_POST['generate_api_key'])) {
        $new_api_key = bb_generate_api_key();
        update_option('bb_api_key', $new_api_key);
    }
    
    $current_api_key = get_option('bb_api_key', '');
    ?>
    <div class="wrap">
        <div class="bb-app-info">
            <h1>Buta Buku API Post</h1>
            <p class="bb-app-description">Plugin for posting via API with complete WordPress data and author selection. This plugin allows you to create posts through an API endpoint while maintaining full WordPress functionality including author selection.</p>
        </div>

        <div class="bb-container">
            <div class="bb-card">
                <h2>API Information</h2>
                <form method="post">
                    <table class="form-table">
                        <tr>
                            <th scope="row">API Key</th>
                            <td>
                                <input type="text" 
                                       class="bb-input"
                                       value="<?php echo esc_attr($current_api_key); ?>" 
                                       readonly
                                />
                                <input type="submit" 
                                       name="generate_api_key" 
                                       class="button button-secondary" 
                                       value="Generate New API Key"
                                />
                                <p class="description">This key is required for API authentication</p>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            
            <div class="bb-card">
                <h2>Default Author Settings</h2>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('buta_buku_options');
                    do_settings_sections('buta_buku_options');
                    ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">Default Author</th>
                            <td>
                                <?php
                                $selected_author = get_option('bb_default_author');
                                if (empty($selected_author)) {
                                    // Get first eligible author
                                    $users = get_users(array(
                                        'role__in' => array('administrator', 'editor', 'author', 'contributor'),
                                        'capability' => array('edit_posts'),
                                        'number' => 1,
                                        'orderby' => 'ID',
                                        'order' => 'ASC'
                                    ));
                                    if (!empty($users)) {
                                        $selected_author = $users[0]->ID;
                                    }
                                }
                                wp_dropdown_users(array(
                                    'name' => 'bb_default_author',
                                    'selected' => $selected_author,
                                    'show_option_none' => 'Select an author',
                                    'option_none_value' => '0',
                                    'class' => 'bb-select',
                                    'role__in' => array('administrator', 'editor', 'author', 'contributor'),
                                    'capability' => array('edit_posts')
                                ));
                                ?>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>

        <div class="bb-video-section">
            <h2>Aplikasi Lainnya</h2>
            <div class="bb-video-container">
                <?php
                // Fetch video links from GitHub
                $links_url = 'https://raw.githubusercontent.com/datadebasa/dewakata/refs/heads/Main/butabuku/links.txt';
                $response = wp_remote_get($links_url);
                
                if (!is_wp_error($response)) {
                    $links = explode("\n", wp_remote_retrieve_body($response));
                    $links = array_filter($links); // Remove empty lines
                    
                    foreach ($links as $link) {
                        $link = trim($link);
                        if (!empty($link)) {
                            ?>
                            <div class="bb-video-wrapper">
                                <iframe src="<?php echo esc_url($link); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                            <?php
                        }
                    }
                } else {
                    echo '<p>Error loading tutorial videos</p>';
                }
                ?>
            </div>
        </div>

        <style>
            .bb-app-info {
                background: #fff;
                padding: 20px;
                margin-bottom: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .bb-app-info h1 {
                margin: 0 0 10px 0;
                color: #23282d;
                font-size: 24px;
            }

            .bb-app-description {
                margin: 0;
                color: #646970;
                font-size: 14px;
                line-height: 1.5;
            }

            .bb-container {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                margin-bottom: 20px;
            }
            
            .bb-card {
                flex: 1 1 300px;
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .bb-input {
                width: 100%;
                max-width: 300px;
                margin-bottom: 10px;
                padding: 8px;
                background: #f0f0f1;
            }

            .bb-select {
                width: 100%;
                max-width: 300px;
                padding: 8px;
            }

            .bb-video-section {
                margin-top: 30px;
            }

            .bb-video-container {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
            }

            .bb-video-wrapper {
                flex: 1 1 300px;
                position: relative;
                padding-bottom: 56.25%; /* 16:9 aspect ratio */
                height: 0;
            }

            .bb-video-wrapper iframe {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 50%;
            }

            @media screen and (max-width: 782px) {
                .bb-card {
                    flex: 1 1 100%;
                }
                
                .bb-video-wrapper {
                    flex: 1 1 100%;
                }

                .form-table td {
                    padding: 15px 10px;
                }

                .form-table input[type="text"],
                .form-table select {
                    max-width: 100%;
                }
            }
        </style>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'bb_register_settings');
function bb_register_settings() {
    register_setting('buta_buku_options', 'bb_default_author');
    register_setting('buta_buku_options', 'bb_api_key');
}

// Verify API key
function bb_verify_api_key($request) {
    $token = $request->get_param('token');
    $stored_api_key = get_option('bb_api_key');
    // Validasi token ada di query
    if (empty($token)) {
        return new WP_Error(
            'missing_token',
            'Token query parameter is missing. Use "?token=<api_key>" in the URL.',
            array('status' => 200, 'token'=>$token)
        );
    }


    // Cocokkan token dengan key tersimpan
    if ($token !== $stored_api_key) {
        return new WP_Error(
            'invalid_token',
            'Invalid API token',
            array('status' => 200)
        );
    }

    // Jika semua valid → kembalikan data validasi
    return true;
}


// Add this new function for WebP conversion
function bb_convert_to_webp($source_path, $unique_id) {
    $image_info = getimagesize($source_path);
    if (!$image_info) return false;

    $image = false;
    
    switch ($image_info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source_path);
            // Handle PNG transparency
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source_path);
            break;
        case 'image/webp':
            // Already WebP, just return the original path
            return $source_path;
    }

    if (!$image) return false;

    // Get WordPress upload directory structure
    $uploads = wp_upload_dir();
    $year_month = date('Y/m');
    $upload_path = $uploads['basedir'] . '/' . $year_month;

    // Create directory if it doesn't exist
    if (!file_exists($upload_path)) {
        wp_mkdir_p($upload_path);
    }

    // Create unique WebP filename
    $webp_filename = $unique_id . '-' . time() . '.webp';
    $webp_path = $upload_path . '/' . $webp_filename;
    
    // Convert to WebP
    imagewebp($image, $webp_path, 80);
    imagedestroy($image);

    // Remove original file
    if ($webp_path !== $source_path) {
        unlink($source_path);
    }

    return $webp_path;
}

// Modify the media_sideload_image filter to convert URL uploads
add_filter('wp_handle_upload', function($file) {
    static $upload_count = 0;
    $upload_count++;
    
    if (strpos($file['type'], 'image/') === 0) {
        $unique_id = uniqid('img_' . $upload_count . '_');
        $converted_path = bb_convert_to_webp($file['file'], $unique_id);
        if ($converted_path) {
            $file['file'] = $converted_path;
            $file['type'] = 'image/webp';
            $file['url'] = str_replace(wp_upload_dir()['basedir'], wp_upload_dir()['baseurl'], $converted_path);
        }
    }
    return $file;
});

// Update bb_handle_featured_image function
function bb_handle_featured_image($base64_image, $post_id) {
    if (empty($base64_image)) return false;

    // Get WordPress upload directory
    $uploads = wp_upload_dir();
    $year_month = date('Y/m');
    $upload_path = $uploads['basedir'] . '/' . $year_month;
    
    // Create directory if it doesn't exist
    if (!file_exists($upload_path)) {
        wp_mkdir_p($upload_path);
    }

    // Decode base64 image
    $image_parts = explode(";base64,", $base64_image);
    if (count($image_parts) < 2) return false;

    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];
    $image_base64 = base64_decode($image_parts[1]);

    // Create unique filename
    $unique_id = uniqid('featured_');
    $temp_filename = $unique_id . '-' . time() . '.' . $image_type;
    $temp_file_path = $upload_path . '/' . $temp_filename;
    file_put_contents($temp_file_path, $image_base64);

    // Convert to WebP
    $webp_path = bb_convert_to_webp($temp_file_path, $unique_id);
    if (!$webp_path) return false;

    // Prepare file data for WordPress
    $filename = basename($webp_path);
    $attachment = array(
        'post_mime_type' => 'image/webp',
        'post_title' => sanitize_file_name($filename),
        'post_content' => '',
        'post_status' => 'inherit'
    );

    // Insert attachment
    $attach_id = wp_insert_attachment($attachment, $webp_path, $post_id);
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // Generate attachment metadata
    $attach_data = wp_generate_attachment_metadata($attach_id, $webp_path);
    wp_update_attachment_metadata($attach_id, $attach_data);

    // Set as featured image
    set_post_thumbnail($post_id, $attach_id);

    return $attach_id;
}

// Register REST API endpoint
add_action('rest_api_init', function () {
    register_rest_route('buta-buku/v1', '/create-post', array(
        'methods' => 'POST',
        'callback' => 'bb_create_post',
        'permission_callback' => 'bb_verify_api_key'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('buta-buku/v1', '/validasi-plugin', array(
        'methods' => 'GET',
        'callback' => 'bb_validate_plugin',
        // 'permission_callback' => '__return_true',
        'permission_callback' => 'bb_verify_api_key'
    ));
});

add_action('rest_api_init', function () {
    register_rest_route('buta-buku/v1', '/check-token', array(
        'methods' => 'GET',
        'callback' => function() {
            return new WP_REST_Response(array(
                'status' => 'valid',
                'message' => 'API key is valid and reusable',
                'timestamp' => current_time('mysql')
            ), 200);
        },
        'permission_callback' => 'bb_verify_api_key'
    ));
});


function bb_validate_plugin($request) {
    $response = array(
        'status' => 'active',
        'message' => 'Plugin is active and working correctly',
        'plugin_name' => 'Buta Buku',
        'version' => '1.0.0'
    );
    
    return new WP_REST_Response($response, 200);
}

function bb_create_post($request) {
    // Get parameters from request
    $params = $request->get_params();
    
    // Required fields validation
    $required_fields = array('title', 'content', 'status');
    foreach ($required_fields as $field) {
        if (!isset($params[$field])) {
            return new WP_Error('missing_field', 'Missing required field: ' . $field, array('status' => 400));
        }
    }

    // Handle post date
    $post_date = isset($params['post_date']) ? $params['post_date'] : current_time('mysql');
    $post_date_gmt = isset($params['post_date_gmt']) ? $params['post_date_gmt'] : get_gmt_from_date($post_date);

    // Get author ID (from request, or default from settings, or fallback to admin)
    $author_id = isset($params['author']) ? $params['author'] : get_option('bb_default_author', 1);

    // Handle categories by name/string
    $category_ids = array();
    if (isset($params['categories']) && is_array($params['categories'])) {
        foreach ($params['categories'] as $category_name) {
            $category = get_term_by('name', $category_name, 'category');
            if ($category) {
                $category_ids[] = $category->term_id;
            } else {
                // Create new category if it doesn't exist
                $new_cat = wp_insert_term($category_name, 'category');
                if (!is_wp_error($new_cat)) {
                    $category_ids[] = $new_cat['term_id'];
                }
            }
        }
    }

    // Prepare post data
    $post_data = array(
        'post_title'    => sanitize_text_field($params['title']),
        'post_content'  => wp_kses_post($params['content']),
        'post_status'   => $params['status'],
        'post_author'   => $author_id,
        'post_type'     => isset($params['post_type']) ? $params['post_type'] : 'post',
        'post_excerpt'  => isset($params['excerpt']) ? sanitize_text_field($params['excerpt']) : '',
        'post_category' => $category_ids,
        'post_date'     => $post_date,
        'post_date_gmt' => $post_date_gmt,
    );

    // Insert the post
    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        return new WP_Error('post_creation_failed', $post_id->get_error_message(), array('status' => 500));
    }

    // Handle featured image (supports both URL and base64)
    $featured_image_id = null;
    $featured_image_url = '';

    if (isset($_FILES['featured_image'])) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $upload = media_handle_upload('featured_image', $post_id);
        
        if (!is_wp_error($upload)) {
            set_post_thumbnail($post_id, $upload);
            $featured_image_id = $upload;
            $featured_image_url = wp_get_attachment_url($upload);
        }
    }

    // Handle tags
    if (isset($params['tags'])) {
        wp_set_post_tags($post_id, $params['tags']);
    }

    // Handle custom fields
    if (isset($params['meta_fields']) && is_array($params['meta_fields'])) {
        foreach ($params['meta_fields'] as $key => $value) {
            update_post_meta($post_id, sanitize_text_field($key), sanitize_text_field($value));
        }
    }

    // Get author info
    $author = get_user_by('ID', $author_id);
    
    // Get featured image info
    $featured_image_sizes = array();
    if ($featured_image_id) {
        $featured_image_sizes = array(
            'thumbnail' => wp_get_attachment_image_src($featured_image_id, 'thumbnail'),
            'medium' => wp_get_attachment_image_src($featured_image_id, 'medium'),
            'large' => wp_get_attachment_image_src($featured_image_id, 'large'),
            'full' => wp_get_attachment_image_src($featured_image_id, 'full')
        );
    }

    // Return success response
    return array(
        'success' => true,
        'post_id' => $post_id,
        'message' => 'Post created successfully',
        'post_url' => get_permalink($post_id),
        'post_date' => $post_date,
        'post_date_gmt' => $post_date_gmt,
        'author' => array(
            'id' => $author_id,
            'name' => $author->display_name,
            'email' => $author->user_email
        ),
        'featured_image' => array(
            'id' => $featured_image_id,
            'url' => $featured_image_url,
            'sizes' => $featured_image_sizes
        )
    );
}

// Example usage:
/*
POST /wp-json/buta-buku/v1/create-post
Headers:
X-API-Key: your_generated_api_key

Body (multipart/form-data):
- title: "Post Title"
- content: "Post content here"
- status: "publish"
- author: 1 (Optional - will use default if not provided)
- post_type: "post"
- excerpt: "Post excerpt"
- categories[]: "Technology"
- categories[]: "News"
- tags[]: "tag1"
- tags[]: "tag2"
- featured_image: (file upload)
- meta_fields[custom_field1]: "value1"
- meta_fields[custom_field2]: "value2"
- post_date: "2024-01-20 12:00:00"
- post_date_gmt: "2024-01-20 12:00:00"
*/
