<?php

if (!(defined('WP_CLI') && WP_CLI)) {
    return;
}

class Algolia_Command {
    public function reindex_post($args, $assoc_args) {
        global $algolia;
        global $table_prefix;
    
        $type = isset($assoc_args['type']) ? $assoc_args['type'] : 'post';
    
        $indexName = $table_prefix.$type;

        $index = $algolia->initIndex(
            apply_filters('algolia_index_name', $indexName, $type)
        );

        $index->clearObjects()->wait();
    
        $paged = 1;
        $count = 0;
    
        do {
            $posts = new WP_Query([
                'posts_per_page' => 100,
                'paged' => $paged,
                'post_type' => $type,
                'post_status' => 'publish',
            ]);
    
            if (!$posts->have_posts()) {
                break;
            }
    
            $records = [];
    
            foreach ($posts->posts as $post) {
                if (!empty($assoc_args['verbose'])) {
                    WP_CLI::line('Indexing ['.$post->post_title.']');
                }
                $record = (array) apply_filters($type.'_to_record', $post);
    
                if (! isset($record['objectID'])) {
                    $record['objectID'] = implode('#', [$post->post_type, $post->ID]);
                }
    
                $records[] = $record;
                $count++;
            }
    
            $index->saveObjects($records);
    
            $paged++;
    
        } while (true);
    
        WP_CLI::success("$count $type entries indexed in Algolia");
    }  
    
    public function copy_config($args, $assoc_args) {
        global $algolia;
    
        $srcIndexName = $assoc_args['from'];
        $destIndexName = $assoc_args['to'];
    
        if (!$srcIndexName || !$destIndexName) {
            throw new InvalidArgumentException('--from and --to arguments are required');
        }
    
        $scope = [];
    
        if (isset($assoc_args['settings']) && $assoc_args['settings']) {
            $scope[] = 'settings';
        }
        if (isset($assoc_args['synonyms']) && $assoc_args['synonyms']) {
            $scope[] = 'synonyms';
        }
        if (isset($assoc_args['rules']) && $assoc_args['rules']) {
            $scope[] = 'rules';
        }
    
        if (!empty($scope)) {
            $algolia->copyIndex($srcIndexName, $destIndexName, ['scope' => $scope]);
            WP_CLI::success('Copied '.implode(', ', $scope)." from $srcIndexName to $destIndexName");
        } else {
            WP_CLI::warning('Nothing to copy, use --settings, --synonyms or --rules.');
        }
    }
    
}
  

WP_CLI::add_command('algolia', 'Algolia_Command');
