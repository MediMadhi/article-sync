<?php
/*
Plugin Name: Article Sync by article_id and last modified date
Description: Registers article_id meta and exposes last modified date for Obsidian synchronization
Version: 1.1
Author: Atsushi H.
*/

// REST APIで article_idフィールドにアクセスできる様にする
add_action('rest_api_init', 'register_article_id_meta');
function register_article_id_meta() {
    register_meta('post', 'article_id', array(
        'type' => 'string',
        'description' => 'Unique identifier for syncing with Obsidian',
        'single' => true,
        'show_in_rest' => true,
    ));
}

add_action('rest_api_init', 'register_modified_date_field');
function register_modified_date_field() {
    register_rest_field('post', 'modified', array(
        'get_callback' => function($post_arr) {
            return get_post_field('post_modified', $post_arr['id']);
        },
        'update_callback' => function($value, $post) {
            $post_data = array(
                'ID' => $post->ID,
                'post_modified' => $value,
                'post_modified_gmt' => get_gmt_from_date($value)
            );
            wp_update_post($post_data);
        },
        'schema' => array(
            'description' => 'Modified date of the post.',
            'type' => 'string',
            'format' => 'date-time',
        ),
    ));
}

add_action('save_post', 'debug_save_post', 10, 3);
function debug_save_post($post_id, $post, $update) {
    $article_id = get_post_meta($post_id, 'article_id', true);
    $post_modified = get_post_field('post_modified', $post_id);
    error_log("Saving post {$post_id}, article_id: {$article_id}, last modified: {$post_modified}");
}