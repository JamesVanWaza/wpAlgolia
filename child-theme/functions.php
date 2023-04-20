<?php

function algolia_post_index_name($defaultName) {
    global $table_prefix;

    return $table_prefix.$defaultName;
}
add_filter('algolia_index_name', 'algolia_post_index_name');

function algolia_post_to_record(WP_Post $post) {
    $tags = array_map(function (WP_Term $term) {
        return $term->name;
    }, wp_get_post_terms($post->ID, 'post_tag'));

    return [
        'objectID' => implode('#', [$post->post_type, $post->ID]),
        'title' => $post->post_title,
        'author' => [
            'id' => $post->post_author,
            'name' => get_user_by('ID', $post->post_author)->display_name,
        ],
        'excerpt' => $post->post_excerpt,
        'content' => strip_tags($post->post_content),
        'tags' => $tags,
        'url' => get_post_permalink($post->ID),
        'custom_field' => get_post_meta($post->id, 'custom_field_name'),
    ];
}
add_filter('post_to_record', 'algolia_post_to_record');

function algolia_update_post($id, WP_Post $post, $update) {
    if (wp_is_post_revision($id) || wp_is_post_autosave($id)) {
        return $post;
    }

    global $algolia;

    $record = (array) apply_filters($post->post_type.'_to_record', $post);

    if (!isset($record['objectID'])) {
      $record['objectID'] = implode('#', [$post->post_type, $post->ID]);
    }

    $index = $algolia->initIndex(
        apply_filters('algolia_index_name', $post->post_type)
    );

    $index->saveObject($record);

    return $post;
}

add_action('save_post', 'algolia_update_post', 10, 3);

