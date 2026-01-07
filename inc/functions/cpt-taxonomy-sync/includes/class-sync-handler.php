<?php
/**
 * Sync Handler - Handles the actual synchronization between CPTs and Taxonomies
 */

if (!defined('ABSPATH')) {
    exit;
}

class TBP_CTS_Sync_Handler {

    private static $_instance = null;
    private $sync_rules = [];
    private $is_syncing = false; // Prevent infinite loops
    private $hierarchy_cache = []; // Cache hierarchy checks

    public static function instance($sync_rules = []) {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($sync_rules);
        }
        return self::$_instance;
    }

    public function __construct($sync_rules) {
        $this->sync_rules = $sync_rules;
        $this->init_hooks();
    }

    /**
     * Initialize sync hooks
     */
    private function init_hooks() {
        // Post hooks
        add_action('save_post', [$this, 'handle_post_save'], 20, 3);
        add_action('before_delete_post', [$this, 'handle_post_delete'], 10, 2);
        add_action('wp_trash_post', [$this, 'handle_post_trash']);

        // Taxonomy hooks
        add_action('created_term', [$this, 'handle_term_created'], 10, 3);
        add_action('edited_term', [$this, 'handle_term_edited'], 10, 3);
        add_action('pre_delete_term', [$this, 'handle_term_delete'], 10, 2);
    }

    /**
     * Check if a post type is hierarchical
     */
    private function is_post_type_hierarchical($post_type) {
        if (isset($this->hierarchy_cache['post_type'][$post_type])) {
            return $this->hierarchy_cache['post_type'][$post_type];
        }

        $result = is_post_type_hierarchical($post_type);
        $this->hierarchy_cache['post_type'][$post_type] = $result;

        return $result;
    }

    /**
     * Check if a taxonomy is hierarchical
     */
    private function is_taxonomy_hierarchical($taxonomy) {
        if (isset($this->hierarchy_cache['taxonomy'][$taxonomy])) {
            return $this->hierarchy_cache['taxonomy'][$taxonomy];
        }

        $tax_obj = get_taxonomy($taxonomy);
        $result = $tax_obj ? $tax_obj->hierarchical : false;
        $this->hierarchy_cache['taxonomy'][$taxonomy] = $result;

        return $result;
    }

    /**
     * Check if both source and destination support hierarchy
     */
    private function both_support_hierarchy($source_type, $source_name, $dest_type, $dest_name) {
        $source_hierarchical = ($source_type === 'post_type')
            ? $this->is_post_type_hierarchical($source_name)
            : $this->is_taxonomy_hierarchical($source_name);

        $dest_hierarchical = ($dest_type === 'post_type')
            ? $this->is_post_type_hierarchical($dest_name)
            : $this->is_taxonomy_hierarchical($dest_name);

        return $source_hierarchical && $dest_hierarchical;
    }

    /**
     * Get the linked parent ID for hierarchy mirroring
     * Returns the destination parent ID if the source has a parent with a linked destination
     */
    private function get_linked_parent_id($source_type, $source_name, $source_parent_id, $dest_type, $dest_name) {
        if (!$source_parent_id) {
            return 0;
        }

        if ($source_type === 'post_type' && $dest_type === 'taxonomy') {
            // Post parent -> linked term parent
            return (int) get_post_meta($source_parent_id, '_tbp_cts_linked_term_' . $dest_name, true);
        } elseif ($source_type === 'post_type' && $dest_type === 'post_type') {
            // Post parent -> linked post parent
            return (int) get_post_meta($source_parent_id, '_tbp_cts_linked_post_' . $dest_name, true);
        } elseif ($source_type === 'taxonomy' && $dest_type === 'post_type') {
            // Term parent -> linked post parent
            return (int) get_term_meta($source_parent_id, '_tbp_cts_linked_post_' . $dest_name, true);
        } elseif ($source_type === 'taxonomy' && $dest_type === 'taxonomy') {
            // Term parent -> linked term parent
            return (int) get_term_meta($source_parent_id, '_tbp_cts_linked_term_' . $dest_name, true);
        }

        return 0;
    }

    /**
     * Get active rules for a specific source
     */
    private function get_rules_for_source($source_type, $source_name) {
        $matching_rules = [];

        foreach ($this->sync_rules as $id => $rule) {
            if (empty($rule['active'])) continue;

            // Check if this rule applies to the source
            if ($rule['source_type'] === $source_type && $rule['source_name'] === $source_name) {
                $matching_rules[$id] = $rule;
            }

            // If bidirectional, also check reverse direction
            if (!empty($rule['bidirectional']) &&
                $rule['dest_type'] === $source_type &&
                $rule['dest_name'] === $source_name) {
                // Create a reversed rule
                $reversed = $rule;
                $reversed['source_type'] = $rule['dest_type'];
                $reversed['source_name'] = $rule['dest_name'];
                $reversed['dest_type'] = $rule['source_type'];
                $reversed['dest_name'] = $rule['source_name'];
                $matching_rules[$id . '_reverse'] = $reversed;
            }
        }

        return $matching_rules;
    }

    /**
     * Handle post save
     */
    public function handle_post_save($post_id, $post, $update) {
        // Prevent infinite loops
        if ($this->is_syncing) return;

        // Skip autosaves, revisions, and auto-drafts
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if ($post->post_status === 'auto-draft') return;

        // Get rules for this post type
        $rules = $this->get_rules_for_source('post_type', $post->post_type);

        if (empty($rules)) return;

        foreach ($rules as $rule) {
            // Check if we should sync on create/update
            if (!$update && empty($rule['sync_on_create'])) continue;
            if ($update && empty($rule['sync_on_update'])) continue;

            $this->sync_post_to_destination($post, $rule);
        }
    }

    /**
     * Sync a post to its destination
     */
    private function sync_post_to_destination($post, $rule) {
        $this->is_syncing = true;

        if ($rule['dest_type'] === 'taxonomy') {
            $this->sync_post_to_term($post, $rule['dest_name']);
        } elseif ($rule['dest_type'] === 'post_type') {
            $this->sync_post_to_post($post, $rule['dest_name']);
        }

        $this->is_syncing = false;
    }

    /**
     * Sync post to taxonomy term
     */
    private function sync_post_to_term($post, $taxonomy) {
        // Check if term already exists (linked via meta)
        $linked_term_id = get_post_meta($post->ID, '_tbp_cts_linked_term_' . $taxonomy, true);

        $term_data = [
            'name' => $post->post_title,
            'slug' => $post->post_name,
            'description' => $post->post_excerpt,
        ];

        // Handle hierarchy if both support it
        if ($this->both_support_hierarchy('post_type', $post->post_type, 'taxonomy', $taxonomy)) {
            $parent_term_id = $this->get_linked_parent_id(
                'post_type', $post->post_type, $post->post_parent,
                'taxonomy', $taxonomy
            );
            $term_data['parent'] = $parent_term_id;
        }

        // Verify linked term still exists
        if ($linked_term_id) {
            $existing_linked = get_term($linked_term_id, $taxonomy);
            if (!$existing_linked || is_wp_error($existing_linked)) {
                // Term was deleted, clear the meta
                delete_post_meta($post->ID, '_tbp_cts_linked_term_' . $taxonomy);
                $linked_term_id = null;
            }
        }

        if ($linked_term_id) {
            // Update existing term
            $result = wp_update_term($linked_term_id, $taxonomy, $term_data);

            if (is_wp_error($result)) {
                error_log('TBP CTS: Failed to update term - ' . $result->get_error_message());
            }
        } else {
            // Check if term with same slug exists
            $existing = get_term_by('slug', $post->post_name, $taxonomy);

            if ($existing) {
                // Link to existing term
                update_post_meta($post->ID, '_tbp_cts_linked_term_' . $taxonomy, $existing->term_id);
                update_term_meta($existing->term_id, '_tbp_cts_linked_post_' . $post->post_type, $post->ID);

                // Update the term
                wp_update_term($existing->term_id, $taxonomy, $term_data);
            } else {
                // Create new term
                $insert_data = [
                    'slug' => $post->post_name,
                    'description' => $post->post_excerpt,
                ];
                if (isset($term_data['parent'])) {
                    $insert_data['parent'] = $term_data['parent'];
                }

                $result = wp_insert_term($post->post_title, $taxonomy, $insert_data);

                if (!is_wp_error($result)) {
                    // Store link
                    update_post_meta($post->ID, '_tbp_cts_linked_term_' . $taxonomy, $result['term_id']);
                    update_term_meta($result['term_id'], '_tbp_cts_linked_post_' . $post->post_type, $post->ID);
                } else {
                    error_log('TBP CTS: Failed to create term - ' . $result->get_error_message());
                }
            }
        }
    }

    /**
     * Sync post to another post type
     */
    private function sync_post_to_post($source_post, $dest_post_type) {
        // Check if post already exists (linked via meta)
        $linked_post_id = get_post_meta($source_post->ID, '_tbp_cts_linked_post_' . $dest_post_type, true);

        $post_data = [
            'post_title' => $source_post->post_title,
            'post_name' => $source_post->post_name,
            'post_excerpt' => $source_post->post_excerpt,
            'post_content' => $source_post->post_content,
            'post_status' => $source_post->post_status,
            'post_type' => $dest_post_type,
        ];

        // Handle hierarchy if both support it
        if ($this->both_support_hierarchy('post_type', $source_post->post_type, 'post_type', $dest_post_type)) {
            $parent_post_id = $this->get_linked_parent_id(
                'post_type', $source_post->post_type, $source_post->post_parent,
                'post_type', $dest_post_type
            );
            $post_data['post_parent'] = $parent_post_id;
        }

        // Verify linked post still exists
        if ($linked_post_id && !get_post($linked_post_id)) {
            delete_post_meta($source_post->ID, '_tbp_cts_linked_post_' . $dest_post_type);
            $linked_post_id = null;
        }

        if ($linked_post_id && get_post($linked_post_id)) {
            // Update existing post
            $post_data['ID'] = $linked_post_id;
            wp_update_post($post_data);
        } else {
            // Check if post with same slug exists
            $existing = get_page_by_path($source_post->post_name, OBJECT, $dest_post_type);

            if ($existing) {
                // Link to existing post
                update_post_meta($source_post->ID, '_tbp_cts_linked_post_' . $dest_post_type, $existing->ID);
                update_post_meta($existing->ID, '_tbp_cts_linked_post_' . $source_post->post_type, $source_post->ID);

                // Update the post
                $post_data['ID'] = $existing->ID;
                wp_update_post($post_data);
            } else {
                // Create new post
                $new_post_id = wp_insert_post($post_data);

                if (!is_wp_error($new_post_id)) {
                    // Store link
                    update_post_meta($source_post->ID, '_tbp_cts_linked_post_' . $dest_post_type, $new_post_id);
                    update_post_meta($new_post_id, '_tbp_cts_linked_post_' . $source_post->post_type, $source_post->ID);
                }
            }
        }
    }

    /**
     * Handle post delete
     */
    public function handle_post_delete($post_id, $post) {
        if ($this->is_syncing) return;

        $rules = $this->get_rules_for_source('post_type', $post->post_type);

        foreach ($rules as $rule) {
            if (empty($rule['sync_on_delete'])) continue;

            $this->delete_synced_destination($post, $rule);
        }
    }

    /**
     * Handle post trash
     */
    public function handle_post_trash($post_id) {
        $post = get_post($post_id);
        if (!$post) return;

        $this->handle_post_delete($post_id, $post);
    }

    /**
     * Delete synced destination
     */
    private function delete_synced_destination($post, $rule) {
        $this->is_syncing = true;

        if ($rule['dest_type'] === 'taxonomy') {
            $linked_term_id = get_post_meta($post->ID, '_tbp_cts_linked_term_' . $rule['dest_name'], true);
            if ($linked_term_id) {
                wp_delete_term($linked_term_id, $rule['dest_name']);
                delete_post_meta($post->ID, '_tbp_cts_linked_term_' . $rule['dest_name']);
            }
        } elseif ($rule['dest_type'] === 'post_type') {
            $linked_post_id = get_post_meta($post->ID, '_tbp_cts_linked_post_' . $rule['dest_name'], true);
            if ($linked_post_id) {
                wp_delete_post($linked_post_id, true);
                delete_post_meta($post->ID, '_tbp_cts_linked_post_' . $rule['dest_name']);
            }
        }

        $this->is_syncing = false;
    }

    /**
     * Handle term created
     */
    public function handle_term_created($term_id, $tt_id, $taxonomy) {
        if ($this->is_syncing) return;

        $rules = $this->get_rules_for_source('taxonomy', $taxonomy);

        if (empty($rules)) return;

        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) return;

        foreach ($rules as $rule) {
            if (empty($rule['sync_on_create'])) continue;

            $this->sync_term_to_destination($term, $rule);
        }
    }

    /**
     * Handle term edited
     */
    public function handle_term_edited($term_id, $tt_id, $taxonomy) {
        if ($this->is_syncing) return;

        $rules = $this->get_rules_for_source('taxonomy', $taxonomy);

        if (empty($rules)) return;

        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) return;

        foreach ($rules as $rule) {
            if (empty($rule['sync_on_update'])) continue;

            $this->sync_term_to_destination($term, $rule);
        }
    }

    /**
     * Sync term to destination
     */
    private function sync_term_to_destination($term, $rule) {
        $this->is_syncing = true;

        if ($rule['dest_type'] === 'post_type') {
            $this->sync_term_to_post($term, $rule['dest_name']);
        } elseif ($rule['dest_type'] === 'taxonomy') {
            $this->sync_term_to_term($term, $rule['dest_name']);
        }

        $this->is_syncing = false;
    }

    /**
     * Sync term to post
     */
    private function sync_term_to_post($term, $post_type) {
        // Check if post already exists (linked via meta)
        $linked_post_id = get_term_meta($term->term_id, '_tbp_cts_linked_post_' . $post_type, true);

        $post_data = [
            'post_title' => $term->name,
            'post_name' => $term->slug,
            'post_excerpt' => $term->description,
            'post_status' => 'publish',
            'post_type' => $post_type,
        ];

        // Handle hierarchy if both support it
        if ($this->both_support_hierarchy('taxonomy', $term->taxonomy, 'post_type', $post_type)) {
            $parent_post_id = $this->get_linked_parent_id(
                'taxonomy', $term->taxonomy, $term->parent,
                'post_type', $post_type
            );
            $post_data['post_parent'] = $parent_post_id;
        }

        // Verify linked post still exists
        if ($linked_post_id && !get_post($linked_post_id)) {
            delete_term_meta($term->term_id, '_tbp_cts_linked_post_' . $post_type);
            $linked_post_id = null;
        }

        if ($linked_post_id && get_post($linked_post_id)) {
            // Update existing post
            $post_data['ID'] = $linked_post_id;
            wp_update_post($post_data);
        } else {
            // Check if post with same slug exists
            $existing = get_page_by_path($term->slug, OBJECT, $post_type);

            if ($existing) {
                // Link to existing post
                update_term_meta($term->term_id, '_tbp_cts_linked_post_' . $post_type, $existing->ID);
                update_post_meta($existing->ID, '_tbp_cts_linked_term_' . $term->taxonomy, $term->term_id);

                // Update the post
                $post_data['ID'] = $existing->ID;
                wp_update_post($post_data);
            } else {
                // Create new post
                $new_post_id = wp_insert_post($post_data);

                if (!is_wp_error($new_post_id)) {
                    // Store link
                    update_term_meta($term->term_id, '_tbp_cts_linked_post_' . $post_type, $new_post_id);
                    update_post_meta($new_post_id, '_tbp_cts_linked_term_' . $term->taxonomy, $term->term_id);
                }
            }
        }
    }

    /**
     * Sync term to another taxonomy
     */
    private function sync_term_to_term($source_term, $dest_taxonomy) {
        // Check if term already exists (linked via meta)
        $linked_term_id = get_term_meta($source_term->term_id, '_tbp_cts_linked_term_' . $dest_taxonomy, true);

        $term_data = [
            'name' => $source_term->name,
            'slug' => $source_term->slug,
            'description' => $source_term->description,
        ];

        // Handle hierarchy if both support it
        if ($this->both_support_hierarchy('taxonomy', $source_term->taxonomy, 'taxonomy', $dest_taxonomy)) {
            $parent_term_id = $this->get_linked_parent_id(
                'taxonomy', $source_term->taxonomy, $source_term->parent,
                'taxonomy', $dest_taxonomy
            );
            $term_data['parent'] = $parent_term_id;
        }

        // Verify linked term still exists
        if ($linked_term_id) {
            $existing_linked = get_term($linked_term_id, $dest_taxonomy);
            if (!$existing_linked || is_wp_error($existing_linked)) {
                // Term was deleted, clear the meta
                delete_term_meta($source_term->term_id, '_tbp_cts_linked_term_' . $dest_taxonomy);
                $linked_term_id = null;
            } else {
                // Update existing term
                wp_update_term($linked_term_id, $dest_taxonomy, $term_data);
                return;
            }
        }

        // Check if term with same slug exists
        $existing = get_term_by('slug', $source_term->slug, $dest_taxonomy);

        if ($existing) {
            // Link to existing term
            update_term_meta($source_term->term_id, '_tbp_cts_linked_term_' . $dest_taxonomy, $existing->term_id);
            update_term_meta($existing->term_id, '_tbp_cts_linked_term_' . $source_term->taxonomy, $source_term->term_id);

            // Update the term
            wp_update_term($existing->term_id, $dest_taxonomy, $term_data);
        } else {
            // Create new term
            $insert_data = [
                'slug' => $source_term->slug,
                'description' => $source_term->description,
            ];
            if (isset($term_data['parent'])) {
                $insert_data['parent'] = $term_data['parent'];
            }

            $result = wp_insert_term($source_term->name, $dest_taxonomy, $insert_data);

            if (!is_wp_error($result)) {
                // Store link
                update_term_meta($source_term->term_id, '_tbp_cts_linked_term_' . $dest_taxonomy, $result['term_id']);
                update_term_meta($result['term_id'], '_tbp_cts_linked_term_' . $source_term->taxonomy, $source_term->term_id);
            }
        }
    }

    /**
     * Handle term delete
     */
    public function handle_term_delete($term_id, $taxonomy) {
        if ($this->is_syncing) return;

        $rules = $this->get_rules_for_source('taxonomy', $taxonomy);

        $term = get_term($term_id, $taxonomy);
        if (!$term || is_wp_error($term)) return;

        foreach ($rules as $rule) {
            if (empty($rule['sync_on_delete'])) continue;

            $this->delete_synced_term_destination($term, $rule);
        }
    }

    /**
     * Delete synced destination for term
     */
    private function delete_synced_term_destination($term, $rule) {
        $this->is_syncing = true;

        if ($rule['dest_type'] === 'post_type') {
            $linked_post_id = get_term_meta($term->term_id, '_tbp_cts_linked_post_' . $rule['dest_name'], true);
            if ($linked_post_id) {
                wp_delete_post($linked_post_id, true);
            }
        } elseif ($rule['dest_type'] === 'taxonomy') {
            $linked_term_id = get_term_meta($term->term_id, '_tbp_cts_linked_term_' . $rule['dest_name'], true);
            if ($linked_term_id) {
                wp_delete_term($linked_term_id, $rule['dest_name']);
            }
        }

        $this->is_syncing = false;
    }

    /**
     * Public method for manual sync: Post to Term
     */
    public function manual_sync_post_to_term($post, $taxonomy) {
        $this->is_syncing = true;
        $this->sync_post_to_term($post, $taxonomy);
        $this->is_syncing = false;
    }

    /**
     * Public method for manual sync: Post to Post
     */
    public function manual_sync_post_to_post($source_post, $dest_post_type) {
        $this->is_syncing = true;
        $this->sync_post_to_post($source_post, $dest_post_type);
        $this->is_syncing = false;
    }

    /**
     * Public method for manual sync: Term to Post
     */
    public function manual_sync_term_to_post($term, $post_type) {
        $this->is_syncing = true;
        $this->sync_term_to_post($term, $post_type);
        $this->is_syncing = false;
    }

    /**
     * Public method for manual sync: Term to Term
     */
    public function manual_sync_term_to_term($source_term, $dest_taxonomy) {
        $this->is_syncing = true;
        $this->sync_term_to_term($source_term, $dest_taxonomy);
        $this->is_syncing = false;
    }
}
