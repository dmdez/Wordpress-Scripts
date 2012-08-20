<?php

    add_action( 'init', 'create_post_type' );
    add_action( 'admin_init', 'create_banner_url_field' );
    add_action ('save_post', 'save_banner_url');
    
    function create_banner_url_field() {
                add_meta_box('banner_url', 'Banner Url', 'banner_url_field', 'banner');
    }
    
    function banner_url_field() {
                global $post;
                $custom = get_post_custom($post->ID);
                $meta_url = $custom["banner_url"][0];      
                
                $myposts = get_posts('numberposts=-1&offset=0&orderby=title&order=ASC&post_type=any');
                
                echo '<ul>';
                echo '<li><label>Link to</label> <select name="banner_url"><option>--</option>';
                foreach($myposts as $_post) {
                        $permalink = get_permalink($_post->ID);
                        $selected = "";
                        if ( $permalink == $meta_url ) {
                        $selected = 'selected="selected"';
                        }
                        echo sprintf('<option value="%s" %s>%s - %s</option>', $permalink, $selected, $_post->post_type, $_post->post_title);
                }
                echo '</select></label>';
                echo '</ul>';
    }
    
    function save_banner_url(){ 
                global $post;
                update_post_meta($post->ID, "banner_url", $_POST["banner_url"] );
    }
    
    function create_post_type() {
            register_post_type( 'banner',
                   array(
                        'labels' => array(
                                        'name'               => __('Banners' ),
                                        'singular_name'      => __('Banner' ),
                                        'add_new'            => __('Add New', 'Banner'),
                                        'add_new_item'       => __('Add New Banner'),
                                        'edit_item'          => __('Edit Banner'),
                                        'new_item'           => __('New Banner'),
                                        'view_item'          => __('View Banner'),
                                        'search_items'       => __('Search Banners'),
                                        'not_found'          => __('No Banners found'),
                                        'not_found_in_trash' => __('No Banners found in Trash'),
                                ),
                                'public' => true,
                                'has_archive' => false,
                                'capability_type' => 'post',
                                'hierarchical' => false,
                                'taxonomies' => array( ),
                                'show_in_nav_menus' => false,
                                'supports' => array('title', 'thumbnail', ),
                        )
            );
    }


?>
 
