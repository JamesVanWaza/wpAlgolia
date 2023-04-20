<?php

function algolia_post_index_name($defaultName) {
    global $table_prefix;

    return $table_prefix.$defaultName;
}
add_filter('algolia_index_name', 'algolia_post_index_name');

