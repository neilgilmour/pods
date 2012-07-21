<?php
class PodsAPI {

    public $display_errors = false;

    public $pod;

    public $pod_id;

    public $pod_data;

    public $fields;

    public $format = 'php';

    /**
     * Store and retrieve data programatically
     *
     * @param string $dtname (optional) The pod name
     * @param string $format (optional) Format for import/export, "php" or "csv"
     *
     * @license http://www.gnu.org/licenses/gpl-2.0.html
     * @since 1.7.1
     */
    public function __construct ( $pod = null, $format = 'php' ) {
        if ( null !== $pod && 0 < strlen( (string) $pod ) ) {
            $this->format = $format;
            $this->pod_data = $this->load_pod( array( 'name' => $pod ) );
            if ( false !== $this->pod_data && is_array( $this->pod_data ) ) {
                $this->pod = $this->pod_data[ 'name' ];
                $this->pod_id = $this->pod_data[ 'id' ];
                $this->fields = $this->pod_data[ 'fields' ];
            }
            else
                return false;
        }
    }

    /**
     * Save a post and it's meta
     *
     * @param $post_data array All post data to be saved (using wp_insert_post / wp_update_post)
     * @param $post_meta array All meta to be saved (set value to null to delete)
     * @param $strict boolean Whether to delete previously saved meta not in $post_meta
     */
    public function save_post( $post_data, $post_meta, $strict = false ) {
        if ( !is_array( $post_data ) || empty( $post_data ) )
            return pods_error( __( 'Post data is required but is either invalid or empty', 'pods' ), $this );

        if ( !is_array( $post_meta ) )
            $post_meta = array();

        if ( !isset( $post_data[ 'ID' ] ) )
            $post_data[ 'ID' ] = wp_insert_post( $post_data, true );
        else
            wp_update_post( $post_data );

        if ( is_wp_error( $post_data[ 'ID' ] ) )
            return pods_error( $post_data[ 'ID' ]->get_error_message(), $this );

        $this->save_post_meta( $post_data[ 'ID' ], $post_meta, $strict );

        return $post_data[ 'ID' ];
    }

    /**
     * Save a post meta
     *
     * @param $id array Post ID
     * @param $post_meta array All meta to be saved (set value to null to delete)
     * @param $strict boolean Whether to delete previously saved meta not in $post_meta
     */
    public function save_post_meta ( $id, $post_meta, $strict = false ) {
        if ( !is_array( $post_meta ) )
            $post_meta = array();

        $meta = get_post_meta( $id );

        foreach ( $post_meta as $meta_key => $meta_value ) {
            if ( null === $meta_value ) {
                $old_meta_value = '';

                if ( isset( $meta[ $meta_key ] ) )
                    $old_meta_value = $meta[ $meta_key ];

                delete_post_meta( $id, $meta_key, $old_meta_value );
            }
            else
                update_post_meta( $id, $meta_key, $meta_value );
        }

        if ( $strict ) {
            foreach ( $meta as $meta_key => $meta_value ) {
                if ( !isset( $post_meta[ $meta_key ] ) )
                    delete_post_meta( $id, $meta_key, $post_meta[ $meta_key ] );
            }
        }

        return $id;
    }

    /**
     * Save a user and it's meta
     *
     * @param $user_data array All user data to be saved (using wp_insert_user / wp_update_user)
     * @param $user_meta array All meta to be saved (set value to null to delete)
     * @param $strict boolean Whether to delete previously saved meta not in $user_meta
     */
    public function save_user ( $user_data, $user_meta, $strict = false ) {
        if ( !is_array( $user_data ) || empty( $user_data ) )
            return pods_error( __( 'User data is required but is either invalid or empty', 'pods' ), $this );

        if ( !is_array( $user_meta ) )
            $user_meta = array();

        if ( !isset( $user_data[ 'ID' ] ) )
            $user_data[ 'ID' ] = wp_insert_user( $user_data );
        else
            wp_update_user( $user_data );

        if ( is_wp_error( $user_data[ 'ID' ] ) )
            return pods_error( $user_data[ 'ID' ]->get_error_message(), $this );

        $this->save_user_meta( $user_data[ 'ID' ], $user_meta, $strict );

        return $user_data[ 'ID' ];
    }

    /**
     * Save a user meta
     *
     * @param $id array User ID
     * @param $user_meta array All meta to be saved (set value to null to delete)
     * @param $strict boolean Whether to delete previously saved meta not in $user_meta
     */
    public function save_user_meta ( $id, $user_meta, $strict = false ) {
        if ( !is_array( $user_meta ) )
            $user_meta = array();

        $meta = get_user_meta( $id );

        foreach ( $user_meta as $meta_key => $meta_value ) {
            if ( null === $meta_value ) {
                $old_meta_value = '';

                if ( isset( $meta[ $meta_key ] ) )
                    $old_meta_value = $meta[ $meta_key ];

                delete_user_meta( $id, $meta_key, $old_meta_value );
            }
            else
                update_user_meta( $id, $meta_key, $meta_value );
        }

        if ( $strict ) {
            foreach ( $meta as $meta_key => $meta_value ) {
                if ( !isset( $user_meta[ $meta_key ] ) )
                    delete_user_meta( $id, $meta_key, $user_meta[ $meta_key ] );
            }
        }

        return $id;
    }

    /**
     * Save a comment and it's meta
     *
     * @param $comment_data array All comment data to be saved (using wp_insert_comment / wp_update_comment)
     * @param $comment_meta array All meta to be saved (set value to null to delete)
     * @param $strict boolean Whether to delete previously saved meta not in $comment_meta
     */
    public function save_comment ( $comment_data, $comment_meta, $strict = false ) {
        if ( !is_array( $comment_data ) || empty( $comment_data ) )
            return pods_error( __( 'Comment data is required but is either invalid or empty', 'pods' ), $this );

        if ( !is_array( $comment_meta ) )
            $comment_meta = array();

        if ( !isset( $comment_data[ 'comment_ID' ] ) )
            $comment_data[ 'comment_ID' ] = wp_insert_comment( $comment_data );
        else
            wp_update_comment( $comment_data );

        if ( is_wp_error( $comment_data[ 'comment_ID' ] ) )
            return pods_error( $comment_data[ 'comment_ID' ]->get_error_message(), $this );

        $this->save_comment_meta( $comment_data[ 'comment_ID' ], $comment_meta, $strict );

        return $comment_data[ 'comment_ID' ];
    }

    /**
     * Save a comment meta
     *
     * @param $id array Comment ID
     * @param $comment_meta array All meta to be saved (set value to null to delete)
     * @param $strict boolean Whether to delete previously saved meta not in $comment_meta
     */
    public function save_comment_meta ( $id, $comment_meta, $strict = false ) {
        if ( !is_array( $comment_meta ) )
            $comment_meta = array();

        $meta = get_comment_meta( $id );

        foreach ( $comment_meta as $meta_key => $meta_value ) {
            if ( null === $meta_value ) {
                $old_meta_value = '';

                if ( isset( $meta[ $meta_key ] ) )
                    $old_meta_value = $meta[ $meta_key ];

                delete_comment_meta( $id, $meta_key, $old_meta_value );
            }
            else
                update_comment_meta( $id, $meta_key, $meta_value );
        }

        if ( $strict ) {
            foreach ( $meta as $meta_key => $meta_value ) {
                if ( !isset( $comment_meta[ $meta_key ] ) )
                    delete_comment_meta( $id, $meta_key, $comment_meta[ $meta_key ] );
            }
        }

        return $id;
    }

    /**
     * Save a taxonomy's term
     *
     * @param $term_ID int Term ID, leave empty to add
     * @param $term string Term name
     * @param $taxonomy string Term ID, set to 0 to add
     * @param $term_data array All term data to be saved (using wp_insert_term / wp_update_term)
     */
    public function save_term ( $term_ID, $term, $taxonomy, $term_data ) {
        if ( !is_array( $term_data ) || empty( $term_data ) )
            return pods_error( __( 'Taxonomy term data is required but is either invalid or empty', 'pods' ), $this );

        if ( empty( $term_ID ) )
            $term_ID = wp_insert_term( $term, $taxonomy, $term_data );
        else {
            if ( 0 < strlen( $term ) )
                $term_data[ 'term' ] = $term;

            wp_update_term( $term_ID, $taxonomy, $term_data );
        }

        if ( is_wp_error( $term_ID ) )
            return pods_error( $term_ID->get_error_message(), $this );
        elseif ( is_array( $term_ID ) )
            $term_ID = $term_ID[ 'term_id' ];

        return $term_ID;
    }

    /**
     * Add a Pod via the Wizard
     *
     * $params['create_extend'] string Create or Extend a Content Type
     * $params['create_pod_type'] string Pod Type (for Creating)
     * $params['create_name'] string Pod Name (for Creating)
     * $params['create_label_plural'] string Plural Label (for Creating)
     * $params['create_label_singular'] string Singular Label (for Creating)
     * $params['create_storage'] string Storage Type (for Creating Post Types)
     * $params['extend_pod_type'] string Pod Type (for Extending)
     * $params['extend_post_type'] string Post Type (for Extending Post Types)
     * $params['extend_taxonomy'] string Taxonomy (for Extending Taxonomies)
     * $params['extend_storage'] string Storage Type (for Extending Post Types / Users / Comments)
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function add_pod ( $params ) {
        $defaults = array(
            'create_extend' => 'create',
            'create_pod_type' => 'post_type',
            'create_name' => '',
            'create_label_plural' => '',
            'create_label_singular' => '',
            'create_storage' => 'meta',
            'extend_pod_type' => 'post_type',
            'extend_post_type' => 'post',
            'extend_taxonomy' => 'category',
            'extend_storage' => 'meta'
        );

        $params = (object) array_merge( $defaults, (array) $params );

        if ( empty( $params->create_extend ) || !in_array( $params->create_extend, array( 'create', 'extend' ) ) )
            return pods_error( __( 'Please choose whether to Create or Extend a Content Type', $this ) );

        $pod_params = array();

        if ( 'create' == $params->create_extend ) {
            if ( empty( $params->create_name ) )
                return pods_error( 'Please enter a Name for this Pod', $this );

            $pod_params = array(
                'post_name' => $params->create_name,
                'post_title' => ( !empty( $params->create_label_plural ) ? $params->create_label_plural : ucwords( str_replace( '_', ' ', $params->create_name ) ) ),
                'options' => array(
                    'type' => $params->create_pod_type,
                    'storage' => 'table',
                )
            );

            if ( 'post_type' == $pod_params[ 'options' ][ 'type' ] ) {
                $pod_params[ 'storage' ] = $params->create_storage;
                $pod_params[ 'options' ][ 'cpt_singular_label' ] = ( !empty( $params->create_label_singular ) ? $params->create_label_singular : ucwords( str_replace( '_', ' ', $params->create_name ) ) );
                $pod_params[ 'options' ][ 'cpt_public' ] = 1;
                $pod_params[ 'options' ][ 'cpt_show_ui' ] = 1;
            }
            elseif ( 'taxonomy' == $pod_params[ 'options' ][ 'type' ] ) {
                $pod_params[ 'options' ][ 'ct_singular_label' ] = ( !empty( $params->create_label_singular ) ? $params->create_label_singular : ucwords( str_replace( '_', ' ', $params->create_name ) ) );
                $pod_params[ 'options' ][ 'ct_public' ] = 1;
                $pod_params[ 'options' ][ 'ct_show_ui' ] = 1;
            }
            elseif ( 'pod' == $pod_params[ 'options' ][ 'type' ] ) {
                $pod_params[ 'options' ][ 'singular_label' ] = ( !empty( $params->create_label_singular ) ? $params->create_label_singular : ucwords( str_replace( '_', ' ', $params->create_name ) ) );
                $pod_params[ 'options' ][ 'public' ] = 1;
                $pod_params[ 'options' ][ 'show_ui' ] = 1;
            }
        }
        elseif ( 'extend' == $params->create_extend ) {
            $pod_params = array(
                'options' => array(
                    'storage' => 'table',
                )
            );

            if ( 'post_type' == $pod_params[ 'type' ] ) {
                $pod_params[ 'storage' ] = $params->extend_storage;
                $pod_params[ 'post_name' ] = $params->extend_post_type;
            }
            elseif ( 'taxonomy' == $pod_params[ 'type' ] )
                $pod_params[ 'post_name' ] = $params->extend_taxonomy;
            else {
                $pod_params[ 'storage' ] = $params->extend_storage;
                $pod_params[ 'post_name' ] = $params->extend_pod_type;
            }

            $pod_params[ 'post_title' ] = ucwords( str_replace( '_', ' ', $pod_params[ 'post_name' ] ) );
            $pod_params[ 'options' ][ 'object' ] = $pod_params[ 'options' ][ 'type' ] = $pod_params[ 'post_name' ];
        }

        if ( !empty( $pod_params ) )
            return $this->save_pod( $pod_params );

        return false;
    }

    /**
     * Add or edit a Pod
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     * $params['label'] string The Pod label
     * $params['type'] string The Pod type
     * $params['storage'] string The Pod storage
     * $params['options'] array Options
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     *
     * @todo Save new fields with save_field, delete with delete_field (it updates cache etc)
     *
     */
    public function save_pod ( $params ) {
        $pod = $this->load_pod( $params, false );

        $params = (object) pods_sanitize( $params );

        $old_id = $old_name = $old_storage = null;

        $old_fields = array();

        if ( !empty( $pod ) ) {
            if ( isset( $params->id ) && 0 < $params->id )
                $old_id = $params->id;

            $params->id = $pod[ 'id' ];

            $old_name = $pod[ 'name' ];
            $old_storage = $pod[ 'storage' ];
            $old_fields = $pod[ 'fields' ];

            if ( !isset( $params->name ) && empty( $params->name ) )
                $params->name = $pod[ 'name' ];

            if ( $old_name != $params->name && false !== $this->pod_exists( array( 'name' => $params->name ) ) )
                return pods_error( sprintf( __( 'Pod %s already exists, you cannot rename %s to that', 'pods' ), $params->name, $old_name ), $this );

            if ( $old_id != $params->id ) {
                if ( $params->type == $pod[ 'options' ][ 'type' ] && isset( $params->object ) && $params->object == $pod[ 'options' ][ 'object' ] )
                    return pods_error( sprintf( __( 'Pod using %s already exists, you can not reuse an object across multiple pods', 'pods' ), $params->object ), $this );
                else
                    return pods_error( sprintf( __( 'Pod %s already exists', 'pods' ), $params->name ), $this );
            }
        }
        else {
            $pod = array(
                'name' => $params->name,
                'label' => $params->name,
                'description' => '',
                'options' => array(
                    'type' => 'pod',
                    'storage' => 'table'
                ),
                'fields' => array()
            );
        }

        // Setup options
        $options = get_object_vars( $params );

        $exclude = array(
            'id',
            'name',
            'label',
            'description',
            'options',
            'fields'
        );

        foreach ( $exclude as $exclude_field ) {
            if ( isset( $options[ $exclude_field ] ) ) {
                $pod[ $exclude_field ] = $options[ $exclude_field ];

                unset( $options[ $exclude_field ] );
            }
        }

        $pod[ 'options' ] = array_merge( $pod[ 'options' ], $options );

        // Add new pod
        if ( empty( $params->id ) || empty( $pod ) ) {
            $params->name = pods_clean_name( $params->name );
            if ( strlen( $params->name ) < 1 )
                return pods_error( 'Pod name cannot be empty', $this );

            $check = pods_query( "SELECT `id` FROM `@wp_pods` WHERE `name` = '{$params->name}' LIMIT 1", $this );
            if ( !empty( $check ) )
                return pods_error( 'Pod ' . $params->name . ' already exists, you can not add one using the same name', $this );

            $post_data = array(
                'post_name' => $pod[ 'name' ],
                'post_title' => $pod[ 'label' ],
                'post_content' => $pod[ 'description' ],
                'post_type' => '_pods_pod',
                'post_status' => 'publish'
            );

            $params->id = $this->save_post( $post_data, $pod[ 'options' ], true );

            if ( false === $params->id )
                return pods_error( 'Cannot add new Pod', $this );

            if ( 'pod' == $params->type && ( !is_array( $pod[ 'fields' ] ) || empty( $pod[ 'fields' ] ) ) ) {
                $pod[ 'fields' ] = array();

                $pod[ 'fields' ][] = array(
                    'name' => 'name',
                    'label' => 'Name',
                    'type' => 'text',
                    'options' => array( 'required' => '1' )
                );

                $pod[ 'fields' ][] = array(
                    'name' => 'created',
                    'label' => 'Date Created',
                    'type' => 'date'
                );

                $pod[ 'fields' ][] = array(
                    'name' => 'modified',
                    'label' => 'Date Modified',
                    'type' => 'date'
                );

                $pod[ 'fields' ][] = array(
                    'name' => 'author',
                    'label' => 'Author',
                    'type' => 'pick',
                    'pick_object' => 'user'
                );

                $pod[ 'fields' ][] = array(
                    'name' => 'permalink',
                    'label' => 'Permalink',
                    'type' => 'slug',
                    'description' => 'Leave blank to auto-generate from Name'
                );
            }
        }

        // Setup / update tables
        if ( 'table' == $pod[ 'storage' ] && null !== $old_storage && $old_storage != $pod[ 'storage' ] ) {
            $definitions = array( "`id` BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY" );

            foreach ( $pod[ 'fields' ] as $field ) {
                if ( !in_array( $field[ 'type' ], array( 'pick', 'file' ) ) )
                    $definitions[ ] = "`{$field['name']}` " . $this->get_field_definition( $field[ 'type' ] );
            }

            $result = pods_query( "CREATE TABLE `@wp_pods_tbl_{$params->name}` (" . implode( ', ', $definitions ) . ") DEFAULT CHARSET utf8", $this );

            if ( empty( $result ) )
                return pods_error( __( 'Cannot add Database Table for Pod', 'pods' ), $this );

        }
        elseif ( 'table' == $pod[ 'storage' ] && 'table' == $old_storage && null !== $old_name && $old_name != $params->name ) {
            $result = pods_query( "ALTER TABLE `@wp_pods_tbl_{$old_name}` RENAME `@wp_pods_tbl_{$params->name}`", $this );

            if ( empty( $result ) )
                return pods_error( __( 'Cannot update Database Table for Pod', 'pods' ), $this );
        }

        $saved = array();

        if ( !empty( $pod[ 'fields' ] ) ) {
            $weight = 0;

            foreach ( $pod[ 'fields' ] as $field ) {
                if ( !is_array( $field ) )
                    continue;

                $field[ 'pod' ] = $pod;
                $field[ 'weight' ] = $weight;

                $field = $this->save_field( $field );

                if ( !empty( $field ) && 0 < $field[ 'id' ] )
                    $saved[ $field[ 'id' ] ] = true;
                else
                    return pods_error( sprintf( __( 'Cannot save %s field', 'pods' ), $field[ 'name' ] ), $this );

                $weight++;
            }

            foreach ( $old_fields as $field ) {
                if ( !isset( $saved[ $field[ 'id' ] ] ) ) {
                    $this->delete_field( array(
                        'id' => (int) $field[ 'id' ],
                        'name' => $field[ 'name' ],
                        'pod' => $pod
                    ) );
                }
            }
        }

        $this->cache_flush_pods( $pod );

        return $params->id;
    }

    /**
     * Add or edit a field within a Pod
     *
     * $params['id'] int The field ID
     * $params['pod_id'] int The Pod ID
     * $params['pod'] string The Pod name
     * $params['name'] string The field name
     * $params['label'] string The field label
     * $params['type'] string The field type ("txt", "desc", "pick", etc)
     * $params['pick_object'] string The related PICK object name
     * $params['pick_val'] string The related PICK object value
     * $params['sister_field_id'] int (optional) The related field ID
     * $params['weight'] int The field weight
     * $params['options'] array The field options
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function save_field ( $params ) {
        $field_columns = array(
            'name' => '',
            'label' => '',
            'pod' => $params->id,
            'weight' => 0,
            'type' => 'text',
            'options' => array()
        );

        $params = (object) $params;

        if ( isset( $params->pod_id ) ) {
            $params->pod_id = pods_absint( $params->pod_id );
        }
        $pod = null;
        $save_pod = false;
        if ( !isset( $params->pod_id ) || empty( $params->pod_id ) ) {
            if ( isset( $params->pod ) ) {
                $pod = $params->pod;
                if ( !is_array( $pod ) )
                    $pod = $this->load_pod( array( 'name' => $pod ) );
                else
                    $save_pod = true;

                if ( empty( $pod ) )
                    return pods_error( 'Pod ID or name is required', $this );
                else {
                    $params->pod_id = $pod[ 'id' ];
                    $params->pod = $pod[ 'name' ];
                }
            }
            else
                return pods_error( 'Pod ID or name is required', $this );
        }
        elseif ( !isset( $params->pod ) ) {
            $pod = $this->load_pod( array( 'id' => $params->pod_id ) );
            if ( empty( $pod ) )
                return pods_error( 'Pod not found', $this );
            else {
                $params->pod_id = $pod[ 'id' ];
                $params->pod = $pod[ 'name' ];
            }
        }
        if ( !isset( $pod ) )
            $pod = $this->load_pod( array( 'id' => $params->pod_id ) );

        $params->name = pods_clean_name( $params->name );
        if ( empty( $params->name ) )
            return pods_error( 'Pod field name is required', $this );

        $defaults = array(
            'id' => 0,
            'pod_id' => 0,
            'name' => '',
            'label' => '',
            'type' => '',
            'pick_object' => '',
            'pick_val' => '',
            'sister_field_id' => '',
            'weight' => 0,
            'options' => array()
        );
        $params = (object) array_merge( $defaults, (array) $params );

        $tableless_field_types = $this->do_hook( 'tableless_field_types', array( 'pick', 'file' ) );

        // Add new field
        if ( !isset( $params->id ) || empty( $params->id ) ) {
            if ( in_array( $params->name, array( 'id', 'created', 'modified', 'author' ) ) ) // 'created', 'modified', 'author'
                return pods_error( "$params->name is reserved for internal Pods usage, please try a different name", $this );

            $sql = "SELECT `id` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} AND `name` = '{$params->name}' LIMIT 1";
            $result = pods_query( $sql, $this );
            if ( !empty( $result ) )
                return pods_error( "Pod field {$params->name} already exists", $this );

            if ( 'slug' == $params->type ) {
            	// @todo use WP_Query and menu_order
                $sql = "SELECT `id` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} AND `type` = 'slug' LIMIT 1";
                $result = pods_query( $sql, $this );
                if ( !empty( $result ) )
                    return pods_error( 'This pod already has a permalink field', $this );
            }

            // Sink the new field to the bottom of the list
            if ( !isset( $params->weight ) ) {
            	// @todo use WP_Query and menu_order
                $params->weight = 0;
                $result = pods_query( "SELECT `weight` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} ORDER BY `weight` DESC LIMIT 1", $this );
                if ( !empty( $result ) )
                    $params->weight = pods_absint( $result[ 0 ]->weight ) + 1;
            }

            $params->sister_field_id = pods_absint( $params->sister_field_id );
            $params->weight = pods_absint( $params->weight );

            if ( !isset( $params->options ) || empty( $params->options ) ) {
                $options = get_object_vars( $params );
                $exclude = array(
                    'id',
                    'pod_id',
                    'pod',
                    'name',
                    'label',
                    'type',
                    'pick_object',
                    'pick_val',
                    'sister_field_id',
                    'weight',
                    'options'
                );
                foreach ( $exclude as $exclude_field ) {
                    if ( isset( $options[ $exclude_field ] ) )
                        unset( $options[ $exclude_field ] );
                }
                $params->options = '';
                if ( !empty( $options ) )
                    $params->options = $options;
            }
            if ( !empty( $params->options ) ) {
                $params->options = pods_sanitize( str_replace( '@wp_', '{prefix}', json_encode( $params->options ) ) );
            }

            // @todo Use wp_insert_post and set post_parent to current pod ID
            $field_id = pods_query( "INSERT INTO `@wp_pods_fields` (`pod_id`, `name`, `label`, `type`, `pick_object`, `pick_val`, `sister_field_id`, `weight`, `options`) VALUES ('{$params->pod_id}', '{$params->name}', '{$params->label}', '{$params->type}', '{$params->pick_object}', '{$params->pick_val}', {$params->sister_field_id}, {$params->weight}, '{$params->options}')", 'Cannot add new field' );
            if ( empty( $field_id ) )
                return pods_error( "Cannot add new field {$params->name}", $this );

            if ( 'table' == $pod[ 'storage' ] && !in_array( $params->type, $tableless_field_types ) ) {
                $dbtype = $this->get_field_definition( $params->type );
                pods_query( "ALTER TABLE `@wp_pods_tbl_{$params->pod}` ADD COLUMN `{$params->name}` {$dbtype}", 'Cannot create new field' );
            }
            elseif ( 0 < $params->sister_field_id ) {
            	// Use update_post_meta on the sister field to set it's sister to current field
                pods_query( "UPDATE `@wp_pods_fields` SET `sister_field_id` = '{$field_id}' WHERE `id` = {$params->sister_field_id} LIMIT 1", 'Cannot update sister field' );
            }

            $params->id = $field_id;
        }
        // Edit existing field
        else {
            $params->id = pods_absint( $params->id );
            if ( 'id' == $params->name ) {
                return pods_error( "$params->name is not editable", $this );
            }

        	// @todo use WP_Query
            $sql = "SELECT `id` FROM `@wp_pods_fields` WHERE `pod_id` = {$params->pod_id} AND `id` != {$params->id} AND name = '{$params->name}' LIMIT 1";
            $check = pods_query( $sql, $this );
            if ( !empty( $check ) )
                return pods_error( "field {$params->name} already exists", $this );

        	// @todo use WP_Query
            $sql = "SELECT * FROM `@wp_pods_fields` WHERE `id` = {$params->id} LIMIT 1";
            $result = pods_query( $sql, $this );
            if ( empty( $result ) )
                return pods_error( "field {$params->name} not found, cannot edit", $this );

            $old_type = $result[ 0 ]->type;
            $old_name = $result[ 0 ]->name;

            $dbtype = $this->get_field_definition( $params->type );
            $params->pick_val = ( 'pick' != $params->type || empty( $params->pick_val ) ) ? '' : "$params->pick_val";
            $params->sister_field_id = pods_absint( $params->sister_field_id );
            $params->weight = pods_absint( $params->weight );

            if ( $params->type != $old_type ) {
                if ( in_array( $params->type, $tableless_field_types ) ) {
                    if ( 'table' == $pod[ 'storage' ] && !in_array( $old_type, $tableless_field_types ) ) {
                        pods_query( "ALTER TABLE `@wp_pods_tbl_$params->pod` DROP COLUMN `$old_name`" );
                    }
                }
                elseif ( in_array( $old_type, $tableless_field_types ) ) {
                    if ( 'table' == $pod[ 'storage' ] )
                        pods_query( "ALTER TABLE `@wp_pods_tbl_$params->pod` ADD COLUMN `$params->name` $dbtype", 'Cannot create field' );

                	// Use update_post_meta on the sister field to set it's sister to current field
                    pods_query( "UPDATE @wp_pods_fields SET sister_field_id = NULL WHERE sister_field_id = $params->id" );
                    pods_query( "DELETE FROM @wp_pods_rel WHERE field_id = $params->id" );
                }
                else {
                    pods_query( "ALTER TABLE `@wp_pods_tbl_$params->pod` CHANGE `$old_name` `$params->name` $dbtype" );
                }
            }
            elseif ( 'table' == $pod[ 'storage' ] && $params->name != $old_name && !in_array( $params->type, $tableless_field_types ) ) {
                pods_query( "ALTER TABLE `@wp_pods_tbl_$params->pod` CHANGE `$old_name` `$params->name` $dbtype" );
            }
            if ( !isset( $params->options ) || empty( $params->options ) ) {
                $options = get_object_vars( $params );
                $exclude = array(
                    'id',
                    'pod_id',
                    'pod',
                    'name',
                    'label',
                    'type',
                    'pick_object',
                    'pick_val',
                    'sister_field_id',
                    'weight',
                    'options'
                );
                foreach ( $exclude as $exclude_field ) {
                    if ( isset( $options[ $exclude_field ] ) )
                        unset( $options[ $exclude_field ] );
                }
                $params->options = '';
                if ( !empty( $options ) )
                    $params->options = $options;
            }
            if ( !empty( $params->options ) ) {
                $params->options = pods_sanitize( str_replace( '@wp_', '{prefix}', json_encode( $params->options ) ) );
            }
            pods_query( "UPDATE `@wp_pods_fields` SET `name` = '{$params->name}', `label` = '{$params->label}', `type` = '{$params->type}', `pick_object` = '{$params->pick_object}', `pick_val` = '{$params->pick_val}', `sister_field_id` = {$params->sister_field_id}, `weight` = {$params->weight}, `options` = '{$params->options}' WHERE `id` = {$params->id} LIMIT 1", 'Cannot edit field' );
        }

        // @todo Maybe save fields at the same time, run add/edit before to setup?

        if ( !$save_pod )
            $this->cache_flush_pods( $pod );

        return $params->id;
    }

    /**
     * Add or Edit a Pods Object
     *
     * $params['id'] int The Object ID
     * $params['name'] string The Object name
     * $params['type'] string The Object type
     * $params['options'] Associative array of Object options
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function save_object ( $params ) {
        $params = (object) $params;

        if ( !isset( $params->name ) || empty( $params->name ) )
            return pods_error( 'Name must be given to save an Object', $this );

        if ( !isset( $params->type ) || empty( $params->type ) )
            return pods_error( 'Type must be given to save an Object', $this );

        if ( !isset( $params->options ) || empty( $params->options ) ) {
            $options = get_object_vars( $params );
            $exclude = array( 'id', 'name', 'type', 'options' );

            foreach ( $exclude as $exclude_field ) {
                if ( isset( $options[ $exclude_field ] ) )
                    unset( $options[ $exclude_field ] );
            }
            $params->options = '';

            if ( !empty( $options ) )
                $params->options = $options;
        }

        if ( !empty( $params->options ) )
            $params->options = str_replace( '@wp_', '{prefix}', json_encode( $params->options ) );

        $params = pods_sanitize( $params );

        if ( isset( $params->id ) && !empty( $params->id ) ) {
            $params->id = pods_absint( $params->id );

            $result = pods_query( "UPDATE `@wp_pods_objects` SET `name` = '{$params->name}', `type` = '{$params->type}', `options` = '{$params->options}' WHERE `id` = " . pods_absint( $params->id ) );

            if ( empty( $result ) )
                return pods_error( ucwords( $params->type ) . ' Object not saved', $this );
        }
        else {
            $sql = "SELECT id FROM `@wp_pods_objects` WHERE `name` = '{$params->name}' LIMIT 1";
            $check = pods_query( $sql, $this );

            if ( !empty( $check ) )
                return pods_error( ucwords( $params->type ) . " Object {$params->name} already exists", $this );

            $object_id = pods_query( "INSERT INTO `@wp_pods_objects` (`name`, `type`, `options`) VALUES ('{$params->name}', '{$params->type}', '{$params->options}')" );

            if ( empty( $object_id ) )
                return pods_error( ucwords( $params->type ) . ' Object not saved', $this );

            $params->id = $object_id;
        }

        delete_transient( 'pods_object_' . $params->type );
        delete_transient( 'pods_object_' . $params->type . '_' . $params->name );

        return $params->id;
    }

    /**
     * Add or edit a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     * $params['code'] string The template code
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function save_template ( $params ) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->save_object( $params );
    }

    /**
     * Add or edit a Pod Page
     *
     * $params['id'] int The page ID
     * $params['uri'] string The page URI
     * $params['phpcode'] string The page code
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function save_page ( $params ) {
        $params = (object) $params;
        if ( !isset( $params->name ) ) {
            $params->name = $params->uri;
            unset( $params->uri );
        }
        $params->name = trim( $params->name, '/' );
        $params->type = 'page';
        return $this->save_object( $params );
    }

    /**
     * Add or edit a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     * $params['helper_type'] string The helper type ("pre_save", "display", etc)
     * $params['phpcode'] string The helper code
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function save_helper ( $params ) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->save_object( $params );
    }

    /**
     * Save the entire role structure
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function save_roles ( $params ) {
        $params = pods_sanitize( $params );
        $roles = array();
        foreach ( $params as $key => $val ) {
            if ( 'action' != $key ) {
                $tmp = empty( $val ) ? array() : explode( ',', $val );
                $roles[ $key ] = $tmp;
            }
        }
        delete_option( 'pods_roles' );
        add_option( 'pods_roles', serialize( $roles ) );
    }

    /**
     * Add or edit a single pod item
     *
     * $params['pod'] string The Pod name (pod or pod_id is required)
     * $params['pod_id'] string The Pod ID (pod or pod_id is required)
     * $params['id'] int The item ID
     * $params['data'] array (optional) Associative array of field names + values
     * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
     *
     * @param array $params An associative array of parameters
     *
     * @return int The item ID
     * @since 1.7.9
     */
    public function save_pod_item ( $params ) {
        $params = (object) str_replace( '@wp_', '{prefix}', pods_sanitize( $params ) );

        // @deprecated 2.0.0
        if ( isset( $params->datatype ) ) {
            pods_deprecated( '$params->pod instead of $params->datatype', '2.0.0' );
            $params->pod = $params->datatype;
            unset( $params->datatype );

            if ( isset( $params->pod_id ) ) {
                pods_deprecated( '$params->id instead of $params->pod_id', '2.0.0' );
                $params->id = $params->pod_id;
                unset( $params->pod_id );
            }

            if ( isset( $params->data ) && !empty( $params->data ) && is_array( $params->data ) ) {
                pods_deprecated( 'PodsAPI::save_pod_items', '2.0.0' );
                return $this->save_pod_items( $params, $params->data );
            }
        }

        // @deprecated 2.0.0
        if ( isset( $params->tbl_row_id ) ) {
            pods_deprecated( '$params->id instead of $params->tbl_row_id', '2.0.0' );
            $params->id = $params->tbl_row_id;
            unset( $params->tbl_row_id );
        }

        // @deprecated 2.0.0
        if ( isset( $params->columns ) ) {
            pods_deprecated( '$params->data instead of $params->columns', '2.0.0' );
            $params->data = $params->columns;
            unset( $params->columns );
        }

        if ( !isset( $params->pod ) )
            $params->pod = false;
        if ( isset( $params->pod_id ) )
            $params->pod_id = pods_absint( $params->pod_id );
        else
            $params->pod_id = 0;

        if ( isset( $params->id ) )
            $params->id = pods_absint( $params->id );
        else
            $params->id = 0;

        // Support for bulk edit
        if ( isset( $params->id ) && !empty( $params->id ) && is_array( $params->id ) ) {
            $ids = array();
            $new_params = $params;
            foreach ( $params->id as $id ) {
                $new_params->id = $id;
                $ids[] = $this->save_pod_item( $new_params );
            }
            return $ids;
        }

        // Allow Helpers to know what's going on, are we adding or saving?
        $is_new_item = false;
        if ( empty( $params->id ) ) {
            $is_new_item = true;
        }

        // Allow Helpers to bypass subsequent helpers in recursive save_pod_item calls
        $bypass_helpers = false;
        if ( isset( $params->bypass_helpers ) && false !== $params->bypass_helpers ) {
            $bypass_helpers = true;
        }

        // Get array of Pods
        if ( empty( $this->pod_data ) || ( $this->pod != $params->pod && $this->pod_id != $params->pod_id ) )
            $this->pod_data = $this->load_pod( array( 'id' => $params->pod_id, 'name' => $params->pod ) );
        if ( false === $this->pod_data )
            return pods_error( "Pod not found", $this );
        $this->pod = $params->pod = $this->pod_data[ 'name' ];
        $this->pod_id = $params->pod_id = $this->pod_data[ 'id' ];
        $this->fields = $this->pod_data[ 'fields' ];

        $fields =& $this->fields; // easy to use variable for helpers

        $fields_active = array();

        // Find the active fields (loop through $params->data to retain order)
        if ( !empty( $params->data ) && is_array( $params->data ) ) {
            foreach ( $params->data as $field => $value ) {
                if ( isset( $fields[ $field ] ) ) {
                    $fields[ $field ][ 'value' ] = $value;
                    $fields_active[] = $field;
                }
            }
            unset( $params->data );
        }

        $columns =& $fields; // @deprecated 2.0.0
        $active_columns =& $fields_active; // @deprecated 2.0.0

        $pre_save_helpers = $post_save_helpers = array();

        if ( false === $bypass_helpers ) {
            // Plugin hook
            $this->do_hook( 'pre_save_pod_item', $params, $fields );
            $this->do_hook( "pre_save_pod_item_{$params->pod}", $params );
            if ( false !== $is_new_item ) {
                $this->do_hook( 'pre_create_pod_item', $params );
                $this->do_hook( "pre_create_pod_item_{$params->pod}", $params );
            }
            else {
                $this->do_hook( 'pre_edit_pod_item', $params );
                $this->do_hook( "pre_edit_pod_item_{$params->pod}", $params );
            }

            // Call any pre-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) {
                if ( !empty( $this->pod_data[ 'options' ] ) && is_array( $this->pod_data[ 'options' ] ) ) {
                    $helpers = array( 'pre_save_helpers', 'post_save_helpers' );
                    foreach ( $helpers as $helper ) {
                        if ( isset( $this->pod_data[ 'options' ][ $helper ] ) && !empty( $this->pod_data[ 'options' ][ $helper ] ) )
                            ${$helper} = explode( ',', $this->pod_data[ 'options' ][ $helper ] );
                    }
                }

                if ( !empty( $pre_save_helpers ) ) {
                    pods_deprecated( 'Pre-save helpers are deprecated, use the action pods_pre_save_pod_item_' . $params->pod . ' instead', '2.0.0' );

                    foreach ( $pre_save_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );
                        if ( false !== $helper )
                            echo eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        $table_data = $rel_fields = $rel_field_ids = array();

        // Loop through each active field, validating and preparing the table data
        foreach ( $fields_active as $field ) {
            $value = $fields[ $field ][ 'value' ];
            $type = $fields[ $field ][ 'type' ];

            // Validate value
            $value = $this->handle_field_validation( $value, $field, $fields, $params );
            if ( false === $value )
                return false;

            // Prepare all table (non-relational) data
            if ( !in_array( $type, array( 'pick', 'file' ) ) )
                $table_data[] = "`{$field}` = '{$value}'";
            // Store relational field data to be looped through later
            else {
                $rel_fields[ $type ][ $field ] = $value;
                $rel_field_ids[] = $fields[ $field ][ 'id' ];
            }
        }

        // @todo: Use REPLACE INTO instead and set defaults on created / modified / author (and check if they exist)
        if ( false !== $is_new_item ) {
            $current_time = current_time( 'mysql' );
            $author = 0;
            if ( is_user_logged_in() ) {
                global $user_ID;
                get_currentuserinfo();
                $author = pods_absint( $user_ID );
            }
            $params->id = pods_query( "INSERT INTO `@wp_pods_tbl_{$params->pod}` (`created`, `modified`, `author`) VALUES ('{$current_time}', '{$current_time}', {$author})", 'Cannot add new table row' );
        }

        // Save the table row
        if ( !empty( $table_data ) ) {
            $table_data = implode( ', ', $table_data );
            pods_query( "UPDATE `@wp_pods_tbl_{$params->pod}` SET {$table_data} WHERE `id` = {$params->id} LIMIT 1" );
        }

        // Save relational field data
        if ( !empty( $rel_fields ) ) {
            // E.g. $rel_fields['pick']['related_events'] = '3,15';
            foreach ( $rel_fields as $type => $data ) {
                foreach ( $data as $field => $values ) {
                    $field_id = pods_absint( $fields[ $field ][ 'id' ] );

                    // Remove existing relationships
                    pods_query( "DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$params->pod_id} AND `field_id` = {$field_id} AND `item_id` = {$params->id}", $this );

                    // Convert values from a comma-separated string into an array
                    if ( !is_array( $values ) )
                        $values = explode( ',', $values );

                    // File relationships
                    if ( 'file' == $type ) {
                        if ( empty( $values ) )
                            continue;
                        $weight = 0;
                        foreach ( $values as $id ) {
                            $id = pods_absint( $id );
                            pods_query( "INSERT INTO `@wp_pods_rel` (`pod_id`, `field_id`, `item_id`, `related_item_id`, `weight`) VALUES ({$params->pod_id}, {$field_id}, {$params->id}, {$id}, {$weight})" );
                            $weight++;
                        }
                    }
                    // Pick relationships
                    elseif ( 'pick' == $type ) {
                        $pick_object = $fields[ $field ][ 'pick_object' ]; // pod, post_type, taxonomy, etc..
                        $pick_val = $fields[ $field ][ 'pick_val' ]; // pod name, post type name, taxonomy name, etc..
                        $related_pod_id = $related_field_id = 0;
                        if ( 'pod' == $pick_object ) {
                            $related_pod = $this->load_pod( array( 'name' => $pick_val ) );
                            if ( false !== $related_pod )
                                $related_pod_id = $related_pod[ 'id' ];
                            if ( 0 < $fields[ $field ][ 'sister_field_id' ] ) {
                                foreach ( $related_pod[ 'fields' ] as $field ) {
                                    if ( 'pick' == $field[ 'type' ] && $fields[ $field ][ 'sister_field_id' ] == $field[ 'id' ] ) {
                                        $related_field_id = $field[ 'id' ];
                                        break;
                                    }
                                }
                            }
                        }

                        // Delete existing sister relationships
                        if ( !empty( $related_field_id ) && !empty( $related_pod_id ) && in_array( $related_field_id, $rel_field_ids ) ) {
                            pods_query( "DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$related_pod_id} AND `field_id` = {$related_field_id} AND `related_pod_id` = {$params->pod_id} AND `related_field_id` = {$field_id} AND `related_item_id` = {$params->id}", $this );
                        }

                        if ( empty( $values ) )
                            continue;

                        // Add relationship values
                        $weight = 0;
                        foreach ( $values as $id ) {
                            if ( !empty( $related_pod_id ) && !empty( $related_field_id ) ) {
                                $related_weight = 0;
                                $result = pods_query( "SELECT `weight` FROM `@wp_pods_rel` WHERE `pod_id` = {$related_pod_id} AND `field_id` = {$related_field_id} ORDER BY `weight` DESC LIMIT 1", $this );
                                if ( !empty( $result ) )
                                    $related_weight = pods_absint( $result[ 0 ]->weight ) + 1;
                                pods_query( "INSERT INTO `@wp_pods_rel` (`pod_id`, `field_id`, `item_id`, `related_pod_id`, `related_field_id`, `related_item_id`, `weight`) VALUES ({$related_pod_id}, {$related_field_id}, {$id}, {$params->pod_id}, {$field_id}, {$params->id}, {$related_weight}", 'Cannot add sister relationship' );
                            }
                            pods_query( "INSERT INTO `@wp_pods_rel` (`pod_id`, `field_id`, `item_id`, `related_pod_id`, `related_field_id`, `related_item_id`, `weight`) VALUES ({$params->pod_id}, {$field_id}, {$params->id}, {$related_pod_id}, {$related_field_id}, {$id}, {$weight})", 'Cannot add relationship' );
                            $weight++;
                        }
                    }
                }
            }
        }

        if ( false === $bypass_helpers ) {
            // Plugin hook
            $this->do_hook( 'post_save_pod_item', $params, $fields );
            $this->do_hook( "post_save_pod_item_{$params->pod}", $params, $fields );
            if ( false !== $is_new_item ) {
                $this->do_hook( 'post_create_pod_item', $params, $fields );
                $this->do_hook( "post_create_pod_item_{$params->pod}", $params, $fields );
            }
            else {
                $this->do_hook( 'post_edit_pod_item', $params, $fields );
                $this->do_hook( "post_edit_pod_item_{$params->pod}", $params, $fields );
            }

            // Call any post-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) {
                if ( !empty( $post_save_helpers ) ) {
                    pods_deprecated( 'Post-save helpers are deprecated, use the action pods_post_save_pod_item_' . $params->pod . ' instead', '2.0.0' );

                    foreach ( $post_save_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );
                        if ( false !== $helper && ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) )
                            echo eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        wp_cache_delete( $params->id, 'pods_items_' . $params->pod );

        // Success! Return the id
        return $params->id;
    }

    /**
     * Add or edit a single pod item
     *
     * $params['pod'] string The Pod name (pod or pod_id is required)
     * $params['pod_id'] string The Pod ID (pod or pod_id is required)
     * $params['id'] int The item ID
     * $params['data'] array (optional) Associative array of field names + values
     * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
     *
     * @param array $params An associative array of parameters
     *
     * @return int The item ID
     * @since 1.7.9
     */
    public function save_pod_items ( $params, $data ) {
        $params = (object) $params;

        $ids = array();

        foreach ( $data as $fields ) {
            $params->data = $fields;
            $ids[] = $this->save_pod_item( $params );
        }

        return $ids;
    }

    /**
     * Duplicate a pod item
     *
     * $params['pod'] string The Pod name
     * $params['id'] int The item's ID from the wp_pods_tbl_* table
     *
     * @param array $params An associative array of parameters
     *
     * @return int The table row ID
     * @since 1.12
     */
    public function duplicate_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        $id = false;
        $fields = $this->fields;
        if ( empty( $fields ) || $this->pod != $params->pod ) {
            $pod = $this->load_pod( array( 'name' => $params->pod ) );
            $fields = $pod[ 'fields' ];
            if ( null === $this->pod ) {
                $this->pod = $pod[ 'name' ];
                $this->pod_id = $pod[ 'id' ];
                $this->fields = $pod[ 'fields' ];
            }
        }
        $pod = pods( $params->pod, $params->id );
        $data = $pod->data();
        if ( !empty( $data ) ) {
            $params = array(
                'pod' => $params->pod,
                'data' => array()
            );
            foreach ( $fields as $field ) {
                $field = $field[ 'name' ];
                if ( 'pick' == $field[ 'coltype' ] ) {
                    $field = $field . '.id';
                    if ( 'wp_taxonomy' == $field[ 'pickval' ] )
                        $field = $field . '.term_id';
                }
                if ( 'file' == $field[ 'coltype' ] )
                    $field = $field . '.ID';
                $value = $pod->field( $field );
                if ( 0 < strlen( $value ) )
                    $params[ 'data' ][ $field[ 'name' ] ] = $value;
            }
            $params = $this->do_hook( 'duplicate_pod_item', $params, $pod->pod, $pod->field( 'id' ) );
            $id = $this->save_pod_item( $params );
        }
        return $id;
    }

    /**
     * Export a pod item
     *
     * $params['pod'] string The Pod name
     * $params['id'] int The item's ID from the wp_pods_tbl_* table
     * $params['fields'] array The fields to export
     *
     * @param array $params An associative array of parameters
     *
     * @return int The table row ID
     * @since 1.12
     */
    public function export_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        $data = false;
        $fields = $this->fields;
        if ( empty( $fields ) || $this->pod != $params->pod ) {
            $pod = $this->load_pod( array( 'name' => $params->pod ) );
            $fields = $pod[ 'fields' ];
            if ( null === $this->pod ) {
                $this->pod = $pod[ 'name' ];
                $this->pod_id = $pod[ 'id' ];
                $this->fields = $pod[ 'fields' ];
            }
        }
        $pod = pods( $params->pod, $params->id );
        $data = $pod->data();
        if ( !empty( $data ) ) {
            $data = array();
            foreach ( $fields as $field ) {
                $data[ $field[ 'name' ] ] = $pod->field( $field[ 'name' ] );
            }
            $data = $this->do_hook( 'export_pod_item', $data, $pod->pod, $pod->field( 'id' ) );
        }
        return $data;
    }

    /**
     * Reorder a Pod
     *
     * $params['pod'] string The Pod name
     * $params['field'] string The field name of the field to reorder
     * $params['order'] array The key => value array of items to reorder (key should be an integer)
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.9.0
     */
    public function reorder_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        // @deprecated 2.0.0
        if ( isset( $params->datatype ) ) {
            pods_deprecated( '$params->pod instead of $params->datatype', '2.0.0' );
            $params->pod = $params->datatype;
            unset( $params->datatype );
        }

        if ( !is_array( $params->order ) )
            $params->order = explode( ',', $params->order );
        foreach ( $params->order as $order => $id ) {
            pods_query( "UPDATE `@wp_pods_tbl_{$params->pod}` SET `{$params->field}` = " . pods_absint( $order ) . " WHERE `id` = " . pods_absint( $id ) . " LIMIT 1" );
        }

        return true;
    }

    /**
     * Delete all content for a Pod
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.9.0
     */
    public function reset_pod ( $params ) {
        $params = (object) pods_sanitize( $params );

        $pod = $this->load_pod( $params );
        if ( false === $pod )
            return pods_error( 'Pod not found', $this );

        $params->id = $pod[ 'id' ];
        $params->name = $pod[ 'name' ];

        $field_ids = array();
        foreach ( $pod[ 'fields' ] as $field ) {
            $field_ids[] = $field[ 'id' ];
        }
        if ( !empty( $field_ids ) )
            pods_query( "UPDATE `@wp_pods_fields` SET `sister_field_id` = NULL WHERE `sister_field_id` IN (" . implode( ',', $field_ids ) . ")" );

        if ( 'pod' == $pod[ 'type' ] ) {
            pods_query( "TRUNCATE `@wp_pods_tbl_{$params->name}`" );
        }
        pods_query( "DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}" );

        wp_cache_flush(); // only way to reliably clear out cached data across an entire group

        return true;
    }

    /**
     * Drop a Pod and all its content
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_pod ( $params ) {
        $params = (object) pods_sanitize( $params );

        $pod = $this->load_pod( $params );
        if ( false === $pod )
            return pods_error( 'Pod not found', $this );

        $params->id = $pod[ 'id' ];
        $params->name = $pod[ 'name' ];

        $field_ids = array();
        foreach ( $pod[ 'fields' ] as $field ) {
            $field_ids[] = $field[ 'id' ];
        }
        if ( !empty( $field_ids ) )
            pods_query( "UPDATE `@wp_pods_fields` SET `sister_field_id` = NULL WHERE `sister_field_id` IN (" . implode( ',', $field_ids ) . ")" );

        if ( 'pod' == $pod[ 'type' ] || 'table' == $pod[ 'storage' ] ) {
            try {
                pods_query( "DROP TABLE `@wp_pods_tbl_{$params->name}`", false );
            } catch ( Exception $e ) {
                // Allow pod to be deleted if the table doesn't exist
                if ( false === strpos( $e->getMessage(), 'Unknown table' ) )
                    die( $e->getMessage() );
            }

            pods_query( "UPDATE `@wp_pods_fields` SET `pick_val` = '' WHERE `pick_object` = 'pod' AND `pick_val` = '{$params->name}'" );
        }

        pods_query( "DELETE FROM `@wp_pods_rel` WHERE `pod_id` = {$params->id} OR `related_pod_id` = {$params->id}" );
        pods_query( "DELETE FROM `@wp_pods_fields` WHERE `pod_id` = {$params->id}" );
        pods_query( "DELETE FROM `@wp_pods` WHERE `id` = {$params->id} LIMIT 1" );

        $this->cache_flush_pods( $pod );

        return true;
    }

    /**
     * Drop a field within a Pod
     *
     * $params['id'] int The field ID
     * $params['name'] int The field name
     * $params['pod'] string The Pod name
     * $params['pod_id'] string The Pod name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_field ( $params ) {
        $params = (object) pods_sanitize( $params );

        if ( !isset( $params->pod ) )
            $params->pod = '';
        if ( !isset( $params->pod_id ) )
            $params->pod_id = 0;

        $pod = $params->pod;
        if ( !is_array( $pod ) )
            $pod = $this->load_pod( array( 'name' => $pod, 'id' => $params->pod_id ) );
        else
            $save_pod = true;

        if ( empty( $pod ) )
            return pods_error( 'Pod not found', $this );

        $params->pod_id = $pod[ 'id' ];
        $params->pod = $pod[ 'name' ];

        if ( !isset( $params->name ) )
            $params->name = '';
        if ( !isset( $params->id ) )
            $params->id = 0;
        $field = $this->load_field( array( 'name' => $params->name, 'id' => $params->id ) );
        if ( false === $field )
            return pods_error( 'field not found', $this );

        $params->id = $field[ 'id' ];
        $params->name = $field[ 'name' ];

        if ( 'pod' == $pod[ 'type' ] && !in_array( $field[ 'type' ], array( 'file', 'pick' ) ) ) {
            pods_query( "ALTER TABLE `@wp_pods_tbl_{$params->pod}` DROP COLUMN `{$params->name}`" );
        }

        pods_query( "DELETE FROM `@wp_pods_rel` WHERE (`pod_id` = {$params->pod_id} AND `field_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_field_id` = {$params->id})" );
        pods_query( "DELETE FROM `@wp_pods_fields` WHERE `id` = {$params->id} LIMIT 1" );
        pods_query( "UPDATE `@wp_pods_fields` SET `sister_field_id` = NULL WHERE `sister_field_id` = {$params->id}" );

        if ( !$save_pod )
            $this->cache_flush_pods( $pod );

        return true;
    }

    /**
     * Drop a Pod Object
     *
     * $params['id'] int The object ID
     * $params['name'] string The object name
     * $params['type'] string The object type
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function delete_object ( $params ) {
        $object = $this->load_object( $params );

        if ( empty( $object ) )
            return pods_error( ucwords( $params->type ) . ' Object not found', $this );

        $result = pods_query( 'DELETE FROM `@wp_pods_objects` WHERE `id` = ' . (int) $object[ 'id' ] . " LIMIT 1", $this );

        if ( empty( $result ) )
            return pods_error( ucwords( $params->type ) . ' Object not deleted', $this );

        delete_transient( 'pods_object_' . $params->type );
        delete_transient( 'pods_object_' . $params->type . '_' . $params->name );

        return true;
    }

    /**
     * Drop a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_template ( $params ) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->delete_object( $params );
    }

    /**
     * Drop a Pod Page
     *
     * $params['id'] int The page ID
     * $params['uri'] string The page URI
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_page ( $params ) {
        $params = (object) $params;
        if ( !isset( $params->name ) ) {
            $params->name = $params->uri;
            unset( $params->uri );
        }
        $params->name = trim( $params->name, '/' );
        $params->type = 'page';
        return $this->delete_object( $params );
    }

    /**
     * Drop a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_helper ( $params ) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->delete_object( $params );
    }

    /**
     * Drop a single pod item
     *
     * $params['id'] int (optional) The item's ID from the wp_pod_tbl_* table (used with datatype parameter)
     * $params['pod'] string (optional) The datatype name (used with id parameter)
     * $params['pod_id'] int (optional) The datatype ID (used with id parameter)
     * $params['bypass_helpers'] bool Set to true to bypass running pre-save and post-save helpers
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function delete_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        // @deprecated 2.0.0
        if ( isset( $params->datatype_id ) || isset( $params->datatype ) ) {
            if ( isset( $params->tbl_row_id ) ) {
                pods_deprecated( '$params->id instead of $params->tbl_row_id', '2.0.0' );
                $params->id = $params->tbl_row_id;
                unset( $params->tbl_row_id );
            }
            if ( isset( $params->pod_id ) ) {
                pods_deprecated( '$params->id instead of $params->pod_id', '2.0.0' );
                $params->id = $params->pod_id;
                unset( $params->pod_id );
            }
            if ( isset( $params->dataype_id ) ) {
                pods_deprecated( '$params->pod_id instead of $params->dataype_id', '2.0.0' );
                $params->pod_id = $params->dataype_id;
                unset( $params->dataype_id );
            }
            if ( isset( $params->datatype ) ) {
                pods_deprecated( '$params->pod instead of $params->datatype', '2.0.0' );
                $params->pod = $params->datatype;
                unset( $params->datatype );
            }
        }

        $params->id = pods_absint( $params->id );

        if ( !isset( $params->pod ) )
            $params->pod = '';
        if ( !isset( $params->pod_id ) )
            $params->pod_id = 0;
        $pod = $this->load_pod( array( 'name' => $params->pod, 'id' => $params->pod_id ) );
        if ( false === $pod )
            return pods_error( 'Pod not found', $this );

        $params->pod_id = $pod[ 'id' ];
        $params->pod = $pod[ 'name' ];

        // Allow Helpers to bypass subsequent helpers in recursive delete_pod_item calls
        $bypass_helpers = false;
        if ( isset( $params->bypass_helpers ) && false !== $params->bypass_helpers )
            $bypass_helpers = true;

        $pre_delete_helpers = $post_delete_helpers = array();

        if ( false === $bypass_helpers ) {
            // Plugin hook
            $this->do_hook( 'pre_delete_pod_item', $params );
            $this->do_hook( "pre_delete_pod_item_{$params->pod}", $params );

            // Call any pre-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) {
                if ( !empty( $pod[ 'options' ] ) && is_array( $pod[ 'options' ] ) ) {
                    $helpers = array( 'pre_delete_helpers', 'post_delete_helpers' );
                    foreach ( $helpers as $helper ) {
                        if ( isset( $pod[ 'options' ][ $helper ] ) && !empty( $pod[ 'options' ][ $helper ] ) )
                            ${$helper} = explode( ',', $pod[ 'options' ][ $helper ] );
                    }
                }

                if ( !empty( $pre_delete_helpers ) ) {
                    foreach ( $pre_delete_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );
                        if ( false !== $helper )
                            echo eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        if ( 'pod' == $pod[ 'type' ] )
            pods_query( "DELETE FROM `@wp_pods_tbl_{$params->datatype}` WHERE `id` = {$params->id} LIMIT 1" );

        pods_query( "DELETE FROM `@wp_pods_rel` WHERE (`pod_id` = {$params->pod_id} AND `item_id` = {$params->id}) OR (`related_pod_id` = {$params->pod_id} AND `related_item_id` = {$params->id})" );

        if ( false === $bypass_helpers ) {
            // Plugin hook
            $this->do_hook( 'post_delete_pod_item', $params );
            $this->do_hook( "post_delete_pod_item_{$params->pod}", $params );

            // Call any post-save helpers (if not bypassed)
            if ( !defined( 'PODS_DISABLE_EVAL' ) || PODS_DISABLE_EVAL ) {
                if ( !empty( $post_delete_helpers ) ) {
                    foreach ( $post_delete_helpers as $helper ) {
                        $helper = $this->load_helper( array( 'name' => $helper ) );
                        if ( false !== $helper )
                            echo eval( '?>' . $helper[ 'code' ] );
                    }
                }
            }
        }

        wp_cache_delete( $params->id, 'pods_items_' . $params->pod );

        return true;
    }

    /**
     * Check if a Pod exists
     *
     * $params['id'] int The datatype ID
     * $params['name'] string The datatype name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.12
     */
    public function pod_exists ( $params ) {
        $params = (object) pods_sanitize( $params );
        if ( !empty( $params->id ) || !empty( $params->name ) ) {
            $where = empty( $params->id ) ? "name = '{$params->name}'" : "id = {$params->id}";
            $result = pods_query( "SELECT id FROM @wp_pods WHERE {$where} LIMIT 1" );
            if ( !empty( $result ) )
                return true;
        }
        return false;
    }

    /**
     * Load a Pod and all of its fields
     *
     * $params['id'] int The Pod ID
     * $params['name'] string The Pod name
     *
     * @param array $params An associative array of parameters or pod name as a string
     *
     * @since 1.7.9
     */
    public function load_pod ( $params, $strict = true ) {
        if ( !is_array( $params ) && !is_object( $params ) )
            $params = array( 'name' => $params );

        $params = (object) pods_sanitize( $params );

        if ( isset( $params->post_name ) ) {
            $pod = get_transient( 'pods_pod_' . $params->post_name );

            if ( false !== $pod )
                return $pod;

            $_pod = get_object_vars( $params );
        }
        else {
            if ( ( !isset( $params->id ) || empty( $params->id ) ) && ( !isset( $params->name ) || empty( $params->name ) ) )
                return pods_error( 'Either Pod ID or Name are required', $this );

            if ( isset( $params->name ) ) {
                $pod = get_transient( 'pods_pod_' . $params->name );

                if ( false !== $pod )
                    return $pod;
            }

            $pod = get_posts( array(
                'name' => $params->name,
                'post_type' => '_pods_pod',
                'posts_per_page' => 1
            ) );

            if ( empty( $pod) ) {
                if ( $strict )
                    return pods_error( __( 'Pod not found', 'pods' ), $this );

                return false;
            }

            $_pod = get_object_vars( $pod[ 0 ] );
        }

        $pod = array(
            'id' => $_pod[ 'id' ],
            'name' => $_pod[ 'post_name' ],
            'label' => $_pod[ 'post_title' ],
            'description' => $_pod[ 'post_content' ]
        );

        // @todo update with a method to put all options in
        $defaults = array(
            'is_toplevel' => 1,
            'type' => 'post_type',
            'storage' => 'meta'
        );

        $pod[ 'options' ] = get_post_meta( $pod[ 'id' ] );

        $pod[ 'options' ] = array_merge( $defaults, $pod[ 'options' ] );

        $pod[ 'type' ] = $pod[ 'options' ][ 'type' ];
        $pod[ 'storage' ] = $pod[ 'options' ][ 'storage' ];

        unset( $pod[ 'options' ][ 'type' ] );
        unset( $pod[ 'options' ][ 'storage' ] );

        $pod[ 'fields' ] = array();

        $fields = get_posts( array(
            'name' => $params->name,
            'post_type' => '_pods_pod',
            'posts_per_page' => 1,
            'post_parent' => $pod[ 'id' ],
            'orderby' => 'menu_order',
            'order' => 'ASC'
        ) );

        if ( !empty( $pod[ 'fields' ] ) ) {
            foreach ( $fields as $field ) {
                $field = $this->load_field( $field );

                $pod[ 'fields' ][ $field[ 'post_name' ] ] = $field;
            }
        }

        set_transient( 'pods_pod_' . $pod[ 'name' ], $pod );

        return $pod;
    }

    /**
     * Load Pods and filter by options
     *
     * $params['type'] string/array Pod Type(s) to filter by
     * $params['object'] string/array Pod Object(s) to filter by
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of Pods to return
     * $params['where'] string WHERE clause of query
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function load_pods ( $params = null ) {
        $params = (object) pods_sanitize( $params );

        $order = 'ASC';
        $orderby = 'menu_order';
        $limit = -1;

        $meta_query = array();
        $cache_key = '';

        if ( isset( $params->type ) && !empty( $params->type ) ) {
            if ( !is_array( $params->type ) )
                $params->type = array( trim( $params->type ) );

            sort( $params->type );

            $meta_query[] = array(
                'key' => 'type',
                'value' => $params->type,
                'compare' => 'IN'
            );

            if ( 1 == count( $params->type ) )
                $cache_key .= '_type_' . trim( implode( '', $params->type ) );
        }

        if ( isset( $params->object ) && !empty( $params->object ) ) {
            if ( !is_array( $params->object ) )
                $params->object = array( trim( $params->object ) );

            sort( $params->object );

            $meta_query[] = array(
                'key' => 'object',
                'value' => $params->object,
                'compare' => 'IN'
            );

            if ( 1 == count( $params->object ) )
                $cache_key .= '_object_' . trim( implode( '', $params->object ) );
        }

        if ( isset( $params->options ) && !empty( $params->options ) && is_array( $params->options ) ) {
            foreach ( $params->options as $option => $value ) {
                if ( !is_array( $value ) )
                    $value = array( trim( $value ) );

                sort( $value );

                $meta_query[] = array(
                    'key' => $option,
                    'value' => pods_sanitize( $value ),
                    'compare' => 'IN'
                );
            }

            $cache_key = '';
        }

        if ( isset( $params->where ) && is_array( $params->where ) )
            $meta_query = array_combine( $meta_query, (array) $params->where );

        if ( isset( $params->order ) && !empty( $params->order ) && in_array( strtoupper( $params->order ), array( 'ASC', 'DESC' ) ) )
            $order = strtoupper( $params->order );

        if ( isset( $params->orderby ) && !empty( $params->orderby ) )
            $orderby = strtoupper( $params->orderby );

        if ( isset( $params->limit ) && !empty( $params->limit ) )
            $limit = pods_absint( $params->limit );

        if ( empty( $cache_key ) )
            $cache_key = 'pods';
        else
            $cache_key = 'pods_get' . $cache_key;

        if ( ( 'pods' != $cache_key || empty( $meta_query ) ) && empty( $limit ) && ( empty ( $orderby ) || 'menu_order' == $orderby ) ) {
            $the_pods = get_transient( $cache_key );

            if ( false !== $the_pods )
                return $the_pods;
        }

        $the_pods = array();

        $pods = get_posts( array(
            'post_type' => '_pods_pod',
            'nopaging' => true,
            'posts_per_page' => $limit,
            'order' => $order,
            'orderby' => $orderby,
            'meta_query' => $meta_query
        ) );

        foreach ( $pods as $pod ) {
            $pod = $this->load_pod( $pod );

            $the_pods[ $pod[ 'name' ] ] = $pod;
        }

        if ( ( 'pods' != $cache_key || empty( $meta_query ) ) && empty( $limit ) && ( empty ( $orderby ) || 'menu_order' == $orderby ) )
            set_transient( $cache_key, $the_pods );

        return $the_pods;
    }

    /**
     * Load a field
     *
     * $params['pod_id'] int The Pod ID
     * $params['id'] int The field ID
     * $params['name'] string The field name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function load_field ( $params, $strict = false ) {
        $params = (object) pods_sanitize( $params );

        if ( isset( $params->post_title ) )
            $_field = get_object_vars( $params );
        elseif ( isset( $params->id ) && !empty( $params->id ) )
            $_field = get_post( $dumb = (int) $params->id );
        else {
            if ( ( !isset( $params->name ) || empty( $params->name ) ) && ( !isset( $params->pod_id ) || empty( $params->pod_id ) ) )
                return pods_error( __( 'Either Field Name or Field ID / Pod ID are required', 'pods' ), $this );

            $field = get_posts( array(
                'name' => $params->name,
                'post_type' => '_pods_pod',
                'posts_per_page' => 1,
                'post_parent' => $params->pod_id
            ) );

            if ( empty( $field ) ) {
                if ( $strict )
                    return pods_error( __( 'Field not found', 'pods' ), $this );

                return false;
            }

            $_field = get_object_vars( $field[ 0 ] );
        }

        $defaults = array(
            'type' => 'text'
        );

        $field = array(
            'id' => $_field[ 'ID' ],
            'name' => $_field[ 'post_name' ],
            'label' => $_field[ 'post_title' ],
            'description' => $_field[ 'post_content' ],
            'weight' => $_field[ 'menu_order' ]
        );

        $field[ 'options' ] = get_post_meta( $field[ 'id' ] );

        $field[ 'options' ] = array_merge( $defaults, $field[ 'options' ] );

        $field[ 'type' ] = $field[ 'options' ][ 'type' ];

        unset( $field[ 'options' ][ 'type' ] );

        $field = PodsForm::option_setup( $field, null, true );

        return $field;
    }

    /**
     * Load a Pods Object
     *
     * $params['id'] int The Object ID
     * $params['name'] string The Object name
     * $params['type'] string The Object type
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function load_object ( $params, $strict = false ) {
        $params = (object) pods_sanitize( $params );

        if ( isset( $params->post_name ) ) {
            $object = get_transient( 'pods_object_' . $params->type . '_' . $params->post_name );

            if ( false !== $object )
                return $object;

            $_object = get_object_vars( $params );
        }
        else {
            if ( !isset( $params->type ) || empty( $params->type ) )
                return pods_error( __( 'Object type is required', 'pods' ), $this );

            if ( ( !isset( $params->id ) || empty( $params->id ) ) && ( !isset( $params->name ) || empty( $params->name ) ) )
                return pods_error( __( 'Either Object ID or Name are required', 'pods' ), $this );

            if ( isset( $params->name ) ) {
                $object = get_transient( 'pods_object_' . $params->type . '_' . $params->name );

                if ( false !== $object )
                    return $object;
            }

            global $wpdb;

            $object = $wpdb->get_var( $wpdb->prepare( "SELECT `ID` FROM `{$wpdb->posts}` WHERE `post_title` = %s AND `post_type` = %s LIMIT 1", $params->name, '_pods_object_' . $params->type ) );

            if ( empty( $object ) ) {
                if ( $strict )
                    return pods_error( __( 'Object not found', 'pods' ), $this );

                return false;
            }

            $_object = get_post( $object );
        }

        $object = array(
            'id' => $_object[ 'ID' ],
            'name' => $_object[ 'post_title' ],
            'code' => $_object[ 'post_content' ],
            'type' => str_replace( '_pods_object_', '', $_object[ 'post_type' ] )
        );

        $object[ 'options' ] = get_post_meta( $object[ 'id' ] );

        set_transient( 'pods_object_' . $object[ 'type' ] . '_' . $object[ 'name' ], $object );

        return $object;
    }

    /**
     * Load Multiple Pods Objects
     *
     * $params['type'] string The Object type
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of objects to return
     * $params['where'] string WHERE clause of query
     *
     * @param array $params An associative array of parameters
     */
    public function load_objects ( $params ) {
        $params = (object) pods_sanitize( $params );

        if ( !isset( $params->type ) || empty( $params->type ) )
            return pods_error( __( 'Pods Object type is required', 'pods' ), $this );

        $order = 'ASC';
        $orderby = 'menu_order';
        $limit = -1;

        $meta_query = array();
        $cache_key = '';

        if ( isset( $params->options ) && !empty( $params->options ) && is_array( $params->options ) ) {
            foreach ( $params->options as $option => $value ) {
                if ( !is_array( $value ) )
                    $value = array( trim( $value ) );

                sort( $value );

                $meta_query[ ] = array(
                    'key' => $option,
                    'value' => pods_sanitize( $value ),
                    'compare' => 'IN'
                );
            }

            $cache_key = '';
        }

        if ( isset( $params->where ) && is_array( $params->where ) )
            $meta_query = array_combine( $meta_query, (array) $params->where );

        if ( isset( $params->order ) && !empty( $params->order ) && in_array( strtoupper( $params->order ), array( 'ASC', 'DESC' ) ) )
            $order = strtoupper( $params->order );

        if ( isset( $params->orderby ) && !empty( $params->orderby ) )
            $orderby = strtoupper( $params->orderby );

        if ( isset( $params->limit ) && !empty( $params->limit ) )
            $limit = pods_absint( $params->limit );

        if ( empty( $cache_key ) )
            $cache_key = 'pods_objects_' . $params->type;
        else
            $cache_key = 'pods_objects_' . $params->type . '_get' . $cache_key;

        if ( ( 'pods_objects_' . $params->type != $cache_key || empty( $meta_query ) ) && empty( $limit ) && ( empty ( $orderby ) || 'menu_order' == $orderby ) ) {
            $the_objects = get_transient( $cache_key );

            if ( false !== $the_objects )
                return $the_objects;
        }

        $the_objects = array();

        $objects = get_posts( array(
            'post_type' => '_pods_object_' . $params->type,
            'nopaging' => true,
            'posts_per_page' => $limit,
            'order' => $order,
            'orderby' => $orderby,
            'meta_query' => $meta_query
        ) );

        foreach ( $objects as $object ) {
            $object = $this->load_object( $object );

            $the_objects[ $object[ 'name' ] ] = $object;
        }

        if ( ( 'pods_objects_' . $params->type != $cache_key || empty( $meta_query ) ) && empty( $limit ) && ( empty ( $orderby ) || 'menu_order' == $orderby ) )
            set_transient( $cache_key, $the_objects );

        return $the_objects;
    }

    /**
     * Load a Pod Template
     *
     * $params['id'] int The template ID
     * $params['name'] string The template name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function load_template ( $params ) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->load_object( $params );
    }

    /**
     * Load Multiple Pod Templates
     *
     * $params['where'] string The WHERE clause of query
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of templates to return
     *
     * @param array $params An associative array of parameters
     */
    public function load_templates ( $params ) {
        $params = (object) $params;
        $params->type = 'template';
        return $this->load_objects( $params );
    }

    /**
     * Load a Pod Page
     *
     * $params['id'] int The page ID
     * $params['name'] string The page URI
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function load_page ( $params ) {
        $params = (object) $params;
        if ( !isset( $params->name ) && isset( $params->uri ) ) {
            $params->name = $params->uri;
            unset( $params->uri );
        }
        $params->type = 'page';
        return $this->load_object( $params );
    }

    /**
     * Load Multiple Pod Pages
     *
     * $params['where'] string The WHERE clause of query
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of pages to return
     *
     * @param array $params An associative array of parameters
     */
    public function load_pages ( $params ) {
        $params = (object) $params;
        $params->type = 'page';
        return $this->load_objects( $params );
    }

    /**
     * Load a Pod Helper
     *
     * $params['id'] int The helper ID
     * $params['name'] string The helper name
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.7.9
     */
    public function load_helper ( $params ) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->load_object( $params );
    }

    /**
     * Load Multiple Pod Helpers
     *
     * $params['where'] string The WHERE clause of query
     * $params['options'] array Pod Option(s) key=>value array to filter by
     * $params['orderby'] string ORDER BY clause of query
     * $params['limit'] string Number of pages to return
     *
     * @param array $params An associative array of parameters
     */
    public function load_helpers ( $params ) {
        $params = (object) $params;
        $params->type = 'helper';
        return $this->load_objects( $params );
    }

    /**
     * Load the pod item object
     *
     * $params['pod'] string The datatype name
     * $params['id'] int (optional) The item's ID
     *
     * @param array $params An associative array of parameters
     *
     * @since 2.0.0
     */
    public function load_pod_item ( $params ) {
        $params = (object) pods_sanitize( $params );

        if ( !isset( $params->pod ) || empty( $params->pod ) )
            return pods_error( 'Pod name required', $this );
        if ( !isset( $params->id ) || empty( $params->id ) )
            return pods_error( 'Item ID required', $this );

        $pod = wp_cache_get( $params->id, 'pods_items_' . $params->pod );

        if ( false !== $pod )
            return $pod;

        $pod = pods( $params->pod, $params->id );

        wp_cache_set( $params->id, $pod, 'pods_items_' . $params->pod );

        return $pod;
    }

    /**
     * Load a bi-directional (sister) field
     *
     * $params['pod'] int The Pod name
     * $params['related_pod'] string The related Pod name
     *
     * @param array $params An associative array of parameters
     * @param array $pod Array of Pod data to use (to avoid lookup)
     *
     * @since 1.7.9
     *
     * @todo Implement with load_pod / fields and use AJAX for new admin
     */
    public function load_sister_fields ( $params, $pod = null ) {
        $params = (object) pods_sanitize( $params );

        if ( empty( $pod ) ) {
            $pod = $this->load_pod( array( 'name' => $params->pod ) );

            if ( false === $pod )
                return pods_error( 'Pod not found', $this );
        }

        $params->pod_id = $pod[ 'id' ];
        $params->pod = $pod[ 'name' ];

        $related_pod = $this->load_pod( array( 'name' => $params->related_pod ) );
        if ( false === $pod )
            return pods_error( 'Related Pod not found', $this );

        $params->related_pod_id = $related_pod[ 'id' ];
        $params->related_pod = $related_pod[ 'name' ];

        if ( 'pod' == $related_pod[ 'type' ] ) {
            $sister_fields = array();
            foreach ( $related_pod[ 'fields' ] as $field ) {
                if ( 'pick' == $field[ 'type' ] && $params->pod == $field[ 'pick_val' ] ) {
                    $sister_fields[] = $field;
                }
            }
            return $sister_fields;
        }
        return false;
    }

    /**
     * Retrieve an associative array of table values
     *
     * $params['table'] string The table name (default: "types")
     * $params['columns'] string Comma-separated string of fields (default: "*")
     * $params['orderby'] string MySQL ORDER BY clause (default: "id ASC")
     * $params['where'] string MySQL WHERE clause (default: 1)
     * $params['array_key'] string The key field for the returned associative array (default: "id")
     *
     * @param array $params An associative array of parameters
     *
     * @return array The table data array
     * @since 1.8.5
     */
    public function get_table_data ( $params ) {
        $params = is_array( $params ) ? $params : array();
        $defaults = array(
            'table' => 'types',
            'columns' => '*',
            'orderby' => '`id` ASC',
            'where' => 1,
            'array_key' => 'id'
        );
        $params = (object) array_merge( $defaults, $params );
        $result = pods_query( "SELECT $params->columns FROM `@wp_pods_$params->table` WHERE $params->where ORDER BY $params->orderby", $this );
        $data = array();
        if ( !empty( $result ) ) {
            foreach ( $result as $row ) {
                $data[ $row->{$params->array_key} ] = get_object_vars( $row );
            }
        }
        return $data;
    }

    /**
     * Takes a sql field such as tinyint and returns the pods field type, such as num.
     *
     * @param type $sql_field
     *
     * @return type
     */
    public static function detect_pod_field_from_sql_data_type ( $sql_field ) {
        $sql_field = strtolower( $sql_field );

        $field_to_field_map = array(
            'tinyint' => 'number',
            'smallint' => 'number',
            'mediumint' => 'number',
            'int' => 'number',
            'bigint' => 'number',
            'float' => 'number',
            'double' => 'number',
            'decimal' => 'number',
            'date' => 'date',
            'datetime' => 'date',
            'timestamp' => 'date',
            'time' => 'date',
            'year' => 'date',
            'varchar' => 'text',
            'text' => 'paragraph',
            'mediumtext' => 'paragraph',
            'longtext' => 'paragraph'
        );

        return ( array_key_exists( $sql_field, $field_to_field_map ) ) ? $field_to_field_map[ $sql_field ] : 'paragraph';
    }

    public function get_field_types () {
        $types = array(
            'boolean',
            'date',
            'file',
            'number',
            'paragraph',
            'pick',
            'slug',
            'text'
        );

        $types = $this->do_hook( 'field_types', $types );

        $field_types = get_transient( 'pods_field_types' );

        if ( false === $field_types || count( $types ) != count( $field_types ) ) {
            $field_types = $types;

            foreach ( $field_types as $field_type => $options ) {
                unset( $field_types[ $field_type ] );

                $field_type = $options;

                $options = array(
                    'type' => $field_type,
                    'label' => ucwords( str_replace( '_', ' ', $field_type ) ),
                    'schema' => 'VARCHAR(255)'
                );

                PodsForm::field_loader( $options );

                $class_vars = get_class_vars( PodsForm::$loaded[ $options ] ); // PHP 5.2.x workaround
                $options[ 'type' ] = $field_type = $class_vars[ 'type' ];
                $options[ 'label' ] = $class_vars[ 'label' ];
                $options[ 'schema' ] = PodsForm::$loaded[ $options ]->schema();
                $options[ 'options' ] = PodsForm::options_setup( $options[ 'type' ] );

                $field_types[ $field_type ] = $options;

                set_transient( 'pods_field_types_' . $field_type, $options );
            }

            set_transient( 'pods_field_types', $field_types );
        }

        return $field_types;
    }

    private function get_field_definition ( $type, $options = null ) {
        $definition = PodsForm::field_method( $type, 'schema', $options );

        return $this->do_hook( 'field_definition', $definition, $type, $options );
    }

    private function handle_field_validation ( $value, $field, $fields, $params ) {
        $type = $fields[ $field ][ 'type' ];
        $label = $fields[ $field ][ 'label' ];
        $label = empty( $label ) ? $field : $label;

        // Verify slug fields
        if ( 'slug' == $type ) {
            if ( empty( $value ) && isset( $fields[ 'name' ][ 'value' ] ) )
                $value = $fields[ 'name' ][ 'value' ];
            if ( !empty( $value ) )
                $value = pods_unique_slug( $value, $field, $params->pod, $params->pod_id, $params->id, $this );
        }
        // Verify required fields
        if ( 1 == $fields[ $field ][ 'required' ] ) {
            if ( '' == $value || null == $value )
                return pods_error( "{$label} is empty", $this );
            elseif ( 'num' == $type && !is_numeric( $value ) )
                return pods_error( "{$label} is not numeric", $this );
        }
        // Verify unique fields
        if ( 1 == $fields[ $field ][ 'unique' ] ) {
            if ( !in_array( $type, array( 'pick', 'file' ) ) ) {
                $exclude = '';
                if ( !empty( $params->id ) )
                    $exclude = "AND `id` != {$params->id}";

                // Trigger an error if not unique
                $check = pods_query( "SELECT `id` FROM `@wp_pods_tbl_{$params->pod}` WHERE `{$field}` = '{$value}' {$exclude} LIMIT 1", $this );
                if ( !empty( $check ) )
                    return pods_error( "$label needs to be unique", $this );
            }
            else {
                // handle rel check
            }
        }

        // @todo Run field validation

        $value = $this->do_hook( 'field_validation', $value, $field, $fields );
        return $value;
    }

    /**
     * Export a package
     *
     * $params['pod'] string Pod Type IDs to export
     * $params['template'] string Template IDs to export
     * $params['podpage'] string Pod Page IDs to export
     * $params['helper'] string Helper IDs to export
     *
     * @param array $params An associative array of parameters
     *
     * @since 1.9.0
     */
    public function export_package ( $params ) {
        $export = array(
            'meta' => array(
                'version' => PODS_VERSION,
                'build' => date( 'U' ),
            )
        );

        $pod_ids = $params[ 'pods' ];
        $template_ids = $params[ 'templates' ];
        $page_ids = $params[ 'pages' ];
        $helper_ids = $params[ 'helpers' ];

        if ( !empty( $pod_ids ) ) {
            $pod_ids = explode( ',', $pod_ids );
            foreach ( $pod_ids as $pod_id ) {
                $export[ 'pods' ][ $pod_id ] = $this->load_pod( array( 'id' => $pod_id ) );
            }
        }
        if ( !empty( $template_ids ) ) {
            $template_ids = explode( ',', $template_ids );
            foreach ( $template_ids as $template_id ) {
                $export[ 'templates' ][ $template_id ] = $this->load_template( array( 'id' => $template_id ) );
            }
        }
        if ( !empty( $page_ids ) ) {
            $page_ids = explode( ',', $page_ids );
            foreach ( $page_ids as $page_id ) {
                $export[ 'pod_pages' ][ $page_id ] = $this->load_page( array( 'id' => $page_id ) );
            }
        }
        if ( !empty( $helper_ids ) ) {
            $helper_ids = explode( ',', $helper_ids );
            foreach ( $helper_ids as $helper_id ) {
                $export[ 'helpers' ][ $helper_id ] = $this->load_helper( array( 'id' => $helper_id ) );
            }
        }

        if ( 1 == count( $export ) )
            return false;

        return $export;
    }

    /**
     * Replace an existing package
     *
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     *
     * @since 1.9.8
     */
    public function replace_package ( $data = false ) {
        return $this->import_package( $data, true );
    }

    /**
     * Import a package
     *
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     * @param bool $replace (optional) Replace existing items when found
     *
     * @since 1.9.0
     */
    public function import_package ( $data = false, $replace = false ) {
        $output = false;
        if ( false === $data || isset( $data[ 'action' ] ) ) {
            $data = get_option( 'pods_package' );
            $output = true;
        }
        if ( !is_array( $data ) ) {
            $json_data = @json_decode( $data, true );
            if ( !is_array( $json_data ) )
                $json_data = @json_decode( stripslashes( $data ), true );
            $data = $json_data;
        }
        if ( !is_array( $data ) || empty( $data ) ) {
            return false;
        }

        $found = array();

        if ( isset( $data[ 'pods' ] ) ) {
            $pod_fields = '';
            foreach ( $data[ 'pods' ] as $pod ) {
                $pod = pods_sanitize( $pod );

                $table_fields = array();
                $pod_fields = $pod[ 'fields' ];
                unset( $pod[ 'fields' ] );

                if ( false !== $replace ) {
                    $existing = $this->load_pod( array( 'name' => $pod[ 'name' ] ) );
                    if ( is_array( $existing ) )
                        $this->delete_pod( array( 'id' => $existing[ 'id' ] ) );
                }

                if ( empty( $pod_fields ) )
                    $pod_fields = implode( "`,`", array_keys( $pod ) );
                // Backward-compatibility (before/after helpers)
                $pod_fields = str_replace( 'before_helpers', 'pre_save_helpers', $pod_fields );
                $pod_fields = str_replace( 'after_helpers', 'post_save_helpers', $pod_fields );

                $values = implode( "','", $pod );
                $dt = pods_query( "INSERT INTO @wp_pod_types (`$pod_fields`) VALUES ('$values')" );

                $tupples = array();
                $field_columns = '';
                foreach ( $pod_fields as $fieldval ) {
                    // Escape the values
                    foreach ( $fieldval as $k => $v ) {
                        if ( empty( $v ) )
                            $v = 'null';
                        else
                            $v = pods_sanitize( $v );
                        $fieldval[ $k ] = $v;
                    }

                    // Store all table fields
                    if ( 'pick' != $fieldval[ 'coltype' ] && 'file' != $fieldval[ 'coltype' ] )
                        $table_fields[ $fieldval[ 'name' ] ] = $fieldval[ 'coltype' ];

                    $fieldval[ 'datatype' ] = $dt;
                    if ( empty( $field_columns ) )
                        $field_columns = implode( "`,`", array_keys( $fieldval ) );
                    $tupples[] = implode( "','", $fieldval );
                }
                $tupples = implode( "'),('", $tupples );
                $tupples = str_replace( "'null'", 'null', $tupples );
                pods_query( "INSERT INTO @wp_pod_fields (`$field_columns`) VALUES ('$tupples')" );

                // Create the actual table with any non-PICK fields
                $definitions = array( "id INT unsigned auto_increment primary key" );
                foreach ( $table_fields as $colname => $coltype ) {
                    $definitions[] = "`$colname` " . $this->get_field_definition( $coltype );
                }
                $definitions = implode( ',', $definitions );
                pods_query( "CREATE TABLE @wp_pod_tbl_{$pod['name']} ($definitions)" );
                if ( !isset( $found[ 'pods' ] ) )
                    $found[ 'pods' ] = array();
                $found[ 'pods' ][] = esc_textarea( $pod[ 'name' ] );
            }
        }

        if ( isset( $data[ 'templates' ] ) ) {
            foreach ( $data[ 'templates' ] as $template ) {
                $defaults = array( 'name' => '', 'code' => '' );
                $params = array_merge( $defaults, $template );
                if ( !defined( 'PODS_STRICT_MODE' ) || !PODS_STRICT_MODE )
                    $params = pods_sanitize( $params );
                if ( false !== $replace ) {
                    $existing = $this->load_template( array( 'name' => $params[ 'name' ] ) );
                    if ( is_array( $existing ) )
                        $params[ 'id' ] = $existing[ 'id' ];
                }
                $this->save_template( $params );
                if ( !isset( $found[ 'templates' ] ) )
                    $found[ 'templates' ] = array();
                $found[ 'templates' ][] = esc_textarea( $params[ 'name' ] );
            }
        }

        if ( isset( $data[ 'pod_pages' ] ) ) {
            foreach ( $data[ 'pod_pages' ] as $pod_page ) {
                $defaults = array(
                    'uri' => '',
                    'title' => '',
                    'phpcode' => '',
                    'precode' => '',
                    'page_template' => ''
                );
                $params = array_merge( $defaults, $pod_page );
                if ( !defined( 'PODS_STRICT_MODE' ) || !PODS_STRICT_MODE )
                    $params = pods_sanitize( $params );
                if ( false !== $replace ) {
                    $existing = $this->load_page( array( 'uri' => $params[ 'uri' ] ) );
                    if ( is_array( $existing ) )
                        $params[ 'id' ] = $existing[ 'id' ];
                }
                $this->save_page( $params );
                if ( !isset( $found[ 'pod_pages' ] ) )
                    $found[ 'pod_pages' ] = array();
                $found[ 'pod_pages' ][] = esc_textarea( $params[ 'uri' ] );
            }
        }

        if ( isset( $data[ 'helpers' ] ) ) {
            foreach ( $data[ 'helpers' ] as $helper ) {
                // backwards compatibility
                if ( isset( $helper[ 'helper_type' ] ) ) {
                    if ( 'before' == $helper[ 'helper_type' ] )
                        $helper[ 'helper_type' ] = 'pre_save';
                    if ( 'after' == $helper[ 'helper_type' ] )
                        $helper[ 'helper_type' ] = 'post_save';
                }
                $defaults = array( 'name' => '', 'helper_type' => 'display', 'phpcode' => '' );
                $params = array_merge( $defaults, $helper );
                if ( !defined( 'PODS_STRICT_MODE' ) || !PODS_STRICT_MODE )
                    $params = pods_sanitize( $params );
                if ( false !== $replace ) {
                    $existing = $this->load_helper( array( 'name' => $params[ 'name' ] ) );
                    if ( is_array( $existing ) )
                        $params[ 'id' ] = $existing[ 'id' ];
                }
                $this->save_helper( $params );
                if ( !isset( $found[ 'helpers' ] ) )
                    $found[ 'helpers' ] = array();
                $found[ 'helpers' ][] = esc_textarea( $params[ 'name' ] );
            }
        }

        if ( true === $output ) {
            if ( !empty( $found ) ) {
                echo '<br /><div id="message" class="updated fade">';
                echo '<h3 style="margin-top:10px;">Package Imported:</h3>';
                if ( isset( $found[ 'pods' ] ) ) {
                    echo '<h4>Pod(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'pods' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'templates' ] ) ) {
                    echo '<h4>Template(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'templates' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'pod_pages' ] ) ) {
                    echo '<h4>Pod Page(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'pod_pages' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'helpers' ] ) ) {
                    echo '<h4>Helper(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'helpers' ] ) . '</li></ul>';
                }
                echo '</div>';
            }
            else
                echo '<e><br /><div id="message" class="error fade"><p>Error: Package not imported, try again.</p></div></e>';
        }

        if ( !empty( $found ) )
            return true;
        return false;
    }

    /**
     * Validate a package
     *
     *
     * @param mixed $data (optional) An associative array containing a package, or the json encoded package
     *
     * @since 1.9.0
     */
    public function validate_package ( $data = false, $output = false ) {
        if ( is_array( $data ) && isset( $data[ 'data' ] ) ) {
            $data = $data[ 'data' ];
            $output = true;
        }
        if ( is_array( $data ) )
            $data = esc_textarea( json_encode( $data ) );

        $found = array();
        $warnings = array();

        update_option( 'pods_package', $data );

        $json_data = @json_decode( $data, true );
        if ( !is_array( $json_data ) )
            $json_data = @json_decode( stripslashes( $data ), true );

        if ( !is_array( $json_data ) || empty( $json_data ) ) {
            $warnings[] = "This is not a valid package. Please try again.";
            if ( true === $output ) {
                echo '<e><br /><div id="message" class="error fade"><p>This is not a valid package. Please try again.</p></div></e>';
                return false;
            }
            else
                return $warnings;
        }
        $data = $json_data;

        if ( 0 < strlen( $data[ 'meta' ][ 'version' ] ) && false === strpos( $data[ 'meta' ][ 'version' ], '.' ) && (int) $data[ 'meta' ][ 'version' ] < 1000 ) { // older style
            $data[ 'meta' ][ 'version' ] = implode( '.', str_split( $data[ 'meta' ][ 'version' ] ) );
        }
        elseif ( 0 < strlen( $data[ 'meta' ][ 'version' ] ) && false === strpos( $data[ 'meta' ][ 'version' ], '.' ) ) { // old style
            $data[ 'meta' ][ 'version' ] = pods_version_to_point( $data[ 'meta' ][ 'version' ] );
        }

        if ( isset( $data[ 'meta' ][ 'compatible_from' ] ) ) {
            if ( 0 < strlen( $data[ 'meta' ][ 'compatible_from' ] ) && false === strpos( $data[ 'meta' ][ 'compatible_from' ], '.' ) ) { // old style
                $data[ 'meta' ][ 'compatible_from' ] = pods_version_to_point( $data[ 'meta' ][ 'compatible_from' ] );
            }
            if ( version_compare( PODS_VERSION, $data[ 'meta' ][ 'compatible_from' ], '<' ) ) {
                $compatible_from = explode( '.', $data[ 'meta' ][ 'compatible_from' ] );
                $compatible_from = $compatible_from[ 0 ] . '.' . $compatible_from[ 1 ];
                $pods_version = explode( '.', PODS_VERSION );
                $pods_version = $pods_version[ 0 ] . '.' . $pods_version[ 1 ];
                if ( version_compare( $pods_version, $compatible_from, '<' ) )
                    $warnings[ 'version' ] = 'This package may only compatible with the newer <strong>Pods ' . pods_version_to_point( $data[ 'meta' ][ 'compatible_from' ] ) . '+</strong>, but you are currently running the older <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
        }
        if ( isset( $data[ 'meta' ][ 'compatible_to' ] ) ) {
            if ( 0 < strlen( $data[ 'meta' ][ 'compatible_to' ] ) && false === strpos( $data[ 'meta' ][ 'compatible_to' ], '.' ) ) { // old style
                $data[ 'meta' ][ 'compatible_to' ] = pods_version_to_point( $data[ 'meta' ][ 'compatible_to' ] );
            }
            if ( version_compare( $data[ 'meta' ][ 'compatible_to' ], PODS_VERSION, '<' ) ) {
                $compatible_to = explode( '.', $data[ 'meta' ][ 'compatible_to' ] );
                $compatible_to = $compatible_to[ 0 ] . '.' . $compatible_to[ 1 ];
                $pods_version = explode( '.', PODS_VERSION );
                $pods_version = $pods_version[ 0 ] . '.' . $pods_version[ 1 ];
                if ( version_compare( $compatible_to, $pods_version, '<' ) )
                    $warnings[ 'version' ] = 'This package may only compatible with the older <strong>Pods ' . $data[ 'meta' ][ 'compatible_to' ] . '</strong>, but you are currently running the newer <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
        }
        if ( !isset( $data[ 'meta' ][ 'compatible_from' ] ) && !isset( $data[ 'meta' ][ 'compatible_to' ] ) ) {
            if ( version_compare( PODS_VERSION, $data[ 'meta' ][ 'version' ], '<' ) ) {
                $compatible_from = explode( '.', $data[ 'meta' ][ 'version' ] );
                $compatible_from = $compatible_from[ 0 ] . '.' . $compatible_from[ 1 ];
                $pods_version = explode( '.', PODS_VERSION );
                $pods_version = $pods_version[ 0 ] . '.' . $pods_version[ 1 ];
                if ( version_compare( $pods_version, $compatible_from, '<' ) )
                    $warnings[ 'version' ] = 'This package was built using the newer <strong>Pods ' . $data[ 'meta' ][ 'version' ] . '</strong>, but you are currently running the older <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
            elseif ( version_compare( $data[ 'meta' ][ 'version' ], PODS_VERSION, '<' ) ) {
                $compatible_to = explode( '.', $data[ 'meta' ][ 'version' ] );
                $compatible_to = $compatible_to[ 0 ] . '.' . $compatible_to[ 1 ];
                $pods_version = explode( '.', PODS_VERSION );
                $pods_version = $pods_version[ 0 ] . '.' . $pods_version[ 1 ];
                if ( version_compare( $compatible_to, $pods_version, '<' ) )
                    $warnings[ 'version' ] = 'This package was built using the older <strong>Pods ' . $data[ 'meta' ][ 'version' ] . '</strong>, but you are currently running the newer <strong>Pods ' . PODS_VERSION . '</strong><br />Unless the package author has specified it is compatible, it may not have been tested to work with your installed version of Pods.';
            }
        }

        if ( isset( $data[ 'pods' ] ) ) {
            foreach ( $data[ 'pods' ] as $pod ) {
                $pod = pods_sanitize( $pod );
                $existing = $this->load_pod( array( 'name' => $pod[ 'name' ] ) );
                if ( is_array( $existing ) ) {
                    if ( !isset( $warnings[ 'pods' ] ) )
                        $warnings[ 'pods' ] = array();
                    $warnings[ 'pods' ][] = esc_textarea( $pod[ 'name' ] );
                }
                if ( !isset( $found[ 'pods' ] ) )
                    $found[ 'pods' ] = array();
                $found[ 'pods' ][] = esc_textarea( $pod[ 'name' ] );
            }
        }

        if ( isset( $data[ 'templates' ] ) ) {
            foreach ( $data[ 'templates' ] as $template ) {
                $template = pods_sanitize( $template );
                $existing = $this->load_template( array( 'name' => $template[ 'name' ] ) );
                if ( is_array( $existing ) ) {
                    if ( !isset( $warnings[ 'templates' ] ) )
                        $warnings[ 'templates' ] = array();
                    $warnings[ 'templates' ][] = esc_textarea( $template[ 'name' ] );
                }
                if ( !isset( $found[ 'templates' ] ) )
                    $found[ 'templates' ] = array();
                $found[ 'templates' ][] = esc_textarea( $template[ 'name' ] );
            }
        }

        if ( isset( $data[ 'pod_pages' ] ) ) {
            foreach ( $data[ 'pod_pages' ] as $pod_page ) {
                $pod_page = pods_sanitize( $pod_page );
                $existing = $this->load_page( array( 'uri' => $pod_page[ 'uri' ] ) );
                if ( is_array( $existing ) ) {
                    if ( !isset( $warnings[ 'pod_pages' ] ) )
                        $warnings[ 'pod_pages' ] = array();
                    $warnings[ 'pod_pages' ][] = esc_textarea( $pod_page[ 'uri' ] );
                }
                if ( !isset( $found[ 'pod_pages' ] ) )
                    $found[ 'pod_pages' ] = array();
                $found[ 'pod_pages' ][] = esc_textarea( $pod_page[ 'uri' ] );
            }
        }

        if ( isset( $data[ 'helpers' ] ) ) {
            foreach ( $data[ 'helpers' ] as $helper ) {
                $helper = pods_sanitize( $helper );
                $existing = $this->load_helper( array( 'name' => $helper[ 'name' ] ) );
                if ( is_array( $existing ) ) {
                    if ( !isset( $warnings[ 'helpers' ] ) )
                        $warnings[ 'helpers' ] = array();
                    $warnings[ 'helpers' ][] = esc_textarea( $helper[ 'name' ] );
                }
                if ( !isset( $found[ 'helpers' ] ) )
                    $found[ 'helpers' ] = array();
                $found[ 'helpers' ][] = esc_textarea( $helper[ 'name' ] );
            }
        }

        if ( true === $output ) {
            if ( !empty( $found ) ) {
                echo '<hr />';
                echo '<h3>Package Contents:</h3>';
                if ( isset( $warnings[ 'version' ] ) )
                    echo '<p><em><strong>NOTICE:</strong> ' . $warnings[ 'version' ] . '</em></p>';
                if ( isset( $found[ 'pods' ] ) ) {
                    echo '<h4>Pod(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'pods' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'templates' ] ) ) {
                    echo '<h4>Template(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'templates' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'pod_pages' ] ) ) {
                    echo '<h4>Pod Page(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'pod_pages' ] ) . '</li></ul>';
                }
                if ( isset( $found[ 'helpers' ] ) ) {
                    echo '<h4>Helper(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $found[ 'helpers' ] ) . '</li></ul>';
                }
            }
            if ( 0 < count( $warnings ) && ( !isset( $warnings[ 'version' ] ) || 1 < count( $warnings ) ) ) {
                echo '<hr />';
                echo '<h3 class="red">WARNING: There are portions of this package that already exist</h3>';
                if ( isset( $warnings[ 'pods' ] ) ) {
                    echo '<h4>Pod(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $warnings[ 'pods' ] ) . '</li></ul>';
                }
                if ( isset( $warnings[ 'templates' ] ) ) {
                    echo '<h4>Template(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $warnings[ 'templates' ] ) . '</li></ul>';
                }
                if ( isset( $warnings[ 'pod_pages' ] ) ) {
                    echo '<h4>Pod Page(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $warnings[ 'pod_pages' ] ) . '</li></ul>';
                }
                if ( isset( $warnings[ 'helpers' ] ) ) {
                    echo '<h4>Helper(s)</h4>';
                    echo '<ul class="pretty"><li>' . implode( '</li><li>', $warnings[ 'helpers' ] ) . '</li></ul>';
                }
                echo '<p><input type="button" class="button-primary" style="background:#f39400;border-color:#d56500;" onclick="podsImport(\'replace_package\')" value=" Overwrite the existing package (Step 2 of 2) " />&nbsp;&nbsp;&nbsp;<input type="button" class="button-secondary" onclick="podsImportCancel()" value=" Cancel " /></p>';
                return false;
            }
            elseif ( !empty( $found ) ) {
                echo '<p><input type="button" class="button-primary" onclick="podsImport(\'import_package\')" value=" Import Package (Step 2 of 2) " />&nbsp;&nbsp;&nbsp;<input type="button" class="button-secondary" onclick="podsImportCancel()" value=" Cancel " /></p>';
                return false;
            }
            echo '<e><br /><div id="message" class="error fade"><p>Error: This package is empty, there is nothing to import.</p></div></e>';
            return false;
        }
        if ( 0 < count( $warnings ) )
            return $warnings;
        elseif ( !empty( $found ) )
            return true;
        return false;
    }

    /**
     * Import data
     *
     * @param mixed $data PHP associative array or CSV input
     * @param bool $numeric_mode Use IDs instead of the name field when matching
     *
     * @since 1.7.1
     */
    public function import ( $data, $numeric_mode = false ) {
        global $wpdb;
        if ( 'csv' == $this->format ) {
            $data = $this->csv_to_php( $data );
        }

        pods_query( "SET NAMES utf8" );
        pods_query( "SET CHARACTER SET utf8" );

        // Loop through the array of items
        $ids = array();

        // Test to see if it's an array of arrays
        if ( !is_array( @current( $data ) ) )
            $data = array( $data );

        foreach ( $data as $key => $data_row ) {
            $fields = array();

            // Loop through each field (use $this->fields so only valid fields get parsed)
            foreach ( $this->fields as $field_name => $field_data ) {
                $field_id = $field_data[ 'id' ];
                $type = $field_data[ 'type' ];
                $pickval = $field_data[ 'pickval' ];
                $field_value = $data_row[ $field_name ];

                if ( null != $field_value && false !== $field_value ) {
                    if ( 'pick' == $type || 'file' == $type ) {
                        $field_values = is_array( $field_value ) ? $field_value : array( $field_value );
                        $pick_values = array();
                        foreach ( $field_values as $pick_value ) {
                            if ( 'file' == $type ) {
                                $where = "`guid` = '" . pods_sanitize( $pick_value ) . "'";
                                if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                    $where = "`ID` = " . pods_absint( $pick_value );
                                $result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = 'attachment' AND {$where} ORDER BY `ID`", $this );
                                if ( !empty( $result ) )
                                    $pick_values[ $field_name ] = $result[ 'id' ];
                            }
                            elseif ( 'pick' == $type ) {
                                if ( 'wp_taxonomy' == $pickval ) {
                                    $where = "`name` = '" . pods_sanitize( $pick_value ) . "'";
                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`term_id` = " . pods_absint( $pick_value );
                                    $result = pods_query( "SELECT `term_id` AS `id` FROM `{$wpdb->terms}` WHERE {$where} ORDER BY `term_id`", $this );
                                    if ( !empty( $result ) )
                                        $pick_values[ $field_name ] = $result[ 'id' ];
                                }
                                elseif ( 'wp_page' == $pickval || 'wp_post' == $pickval ) {
                                    $pickval = str_replace( 'wp_', '', $pickval );
                                    $where = "`post_title` = '" . pods_sanitize( $pick_value ) . "'";
                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`ID` = " . pods_absint( $pick_value );
                                    $result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->posts}` WHERE `post_type` = '$pickval' AND {$where} ORDER BY `ID`", $this );
                                    if ( !empty( $result ) )
                                        $pick_values[ $field_name ] = $result[ 'id' ];
                                }
                                elseif ( 'wp_user' == $pickval ) {
                                    $where = "`display_name` = '" . pods_sanitize( $pick_value ) . "'";
                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`ID` = " . pods_absint( $pick_value );
                                    $result = pods_query( "SELECT `ID` AS `id` FROM `{$wpdb->users}` WHERE {$where} ORDER BY `ID`", $this );
                                    if ( !empty( $result ) )
                                        $pick_values[ $field_name ] = $result[ 'id' ];
                                }
                                else {
                                    $where = "`name` = '" . pods_sanitize( $pick_value ) . "'";
                                    if ( 0 < pods_absint( $pick_value ) && false !== $numeric_mode )
                                        $where = "`id` = " . pods_absint( $pick_value );
                                    $result = pods_query( "SELECT `id` FROM `@wp_pods_tbl_{$pickval}` WHERE {$where} ORDER BY `id`", $this );
                                    if ( !empty( $result ) )
                                        $pick_values[ $field_name ] = $result[ 'id' ];
                                }
                            }
                        }
                        $field_value = implode( ',', $pick_values );
                    }
                    $fields[ $field_name ] = pods_sanitize( $field_value );
                }
            }
            if ( !empty( $fields ) ) {
                $params = array(
                    'pod' => $this->pod,
                    'data' => $fields
                );
                $ids[] = $this->save_pod_item( $params );
            }
        }
        return $ids;
    }

    /**
     * Export data
     *
     * @since 1.7.1
     */
    public function export () {
        $data = array();
        $pod = pods( $this->pod, array( 'limit' => -1, 'search' => false, 'pagination' => false ) );
        while ($pod->fetch()) {
            $data[ $pod->field( 'id' ) ] = $this->export_pod_item( $pod->field( 'id' ) );
        }
        return $data;
    }

    /**
     * Convert CSV to a PHP array
     *
     * @param string $data The CSV input
     *
     * @since 1.7.1
     */
    public function csv_to_php ( $data ) {
        $delimiter = ",";
        $expr = "/{$delimiter}(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/";
        $data = str_replace( "\r\n", "\n", $data );
        $data = str_replace( "\r", "\n", $data );
        $lines = explode( "\n", $data );
        $field_names = explode( $delimiter, array_shift( $lines ) );
        $field_names = preg_replace( "/^\"(.*)\"$/s", "$1", $field_names );
        $out = array();
        foreach ( $lines as $line ) {
            // Skip the empty line
            if ( empty( $line ) )
                continue;
            $row = array();
            $fields = preg_split( $expr, trim( $line ) );
            $fields = preg_replace( "/^\"(.*)\"$/s", "$1", $fields );
            foreach ( $field_names as $key => $field ) {
                $row[ $field ] = $fields[ $key ];
            }
            $out[] = $row;
        }
        return $out;
    }

    /**
     * Clear Pod-related cache
     *
     * @param array $pod
     */
    public function cache_flush_pods ( $pod = null ) {
        global $wpdb;

        delete_transient( 'pods_pods' );

        if ( null !== $pod && is_array( $pod ) ) {
            delete_transient( 'pods_pod_' . $pod[ 'name' ] );

            if ( in_array( $pod[ 'type' ], array( 'post_type', 'taxonomy' ) ) )
                delete_transient( 'pods_wp_cpt_ct' );
        }

        $wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_pods_get_%'" );

        wp_cache_flush();
    }

    /**
     * @param object $obj Pod object
     * @param array $fields Fields being submitted in form ( key => settings )
     * @param string $thank_you URL to send to upon success
     *
     * @return mixed
     */
    public function process_form ( $obj = null, $fields = null, $thank_you = null ) {
        $this->display_errors = false;

        $nonce = $pod = $id = $uri = $form = null;

        if ( isset( $_POST[ '_pods_nonce' ] ) )
            $nonce = $_POST[ '_pods_nonce' ];

        if ( is_object( $obj ) ) {
            $pod = $obj->pod;
            $id = $obj->id();
        }
        else {
            if ( isset( $_POST[ '_pods_pod' ] ) )
                $pod = $_POST[ '_pods_pod' ];

            if ( isset( $_POST[ '_pods_id' ] ) )
                $id = $_POST[ '_pods_id' ];
        }

        if ( isset( $_POST[ '_pods_uri' ] ) )
            $uri = $_POST[ '_pods_uri' ];

        if ( !empty( $fields ) ) {
            $fields = array_keys( $fields );
            $form = implode( ',', $fields );
        }
        elseif ( isset( $_POST[ '_pods_form' ] ) ) {
            $form = $_POST[ '_pods_form' ];
            $fields = explode( ',', $form );
        }

        if ( empty( $nonce) || empty( $pod ) || empty( $uri ) || empty( $fields ) )
            return pods_error( __( 'Invalid submission', 'pods' ), $this );

        $action = 'pods_form_' . $pod . '_' . session_id() . '_' . $id . '_' . $uri . '_' . wp_hash( $form );

        if ( wp_verify_nonce( $nonce, $action ) )
            return pods_error( __( 'Access denied, please refresh and try again.', 'pods' ), $this );

        $data = array();

        foreach ( $fields as $field ) {
            $data[ $field ] = '';

            if ( isset( $_POST[ $field ] ) )
                $data[ $field ] = $_POST[ $field ];
        }

        $params = array(
            'pod' => $pod,
            'id' => $id,
            'data' => $data
        );

        $id = $this->save_pod_item( $params );

        if ( 0 < $id && !empty( $thank_you ) )
            echo '<script type="text/javascript">document.location = "' . esc_url( $thank_you ) . '";</script>';

        return $id;
    }

    /**
     * Handle filters / actions for the class
     *
     * @since 2.0.0
     */
    private function do_hook () {
        $args = func_get_args();
        if ( empty( $args ) )
            return false;
        $name = array_shift( $args );
        return pods_do_hook( "api", $name, $args, $this );
    }

    /**
     * Return an array of dummy data for select2 autocomplete input
     */
    public function select2_ajax() {
        return array(
            'results' => array(
                array(
                    'id' => 1,
                    'title' => 'Option 1',
                ),
                array(
                    'id' => 2,
                    'title' => 'Option 2',
                ),
                array(
                    'id' => 3,
                    'title' => 'Option 3',
                )
            ),
        );
    }

    /**
     * Handle methods that have been deprecated
     *
     * @since 2.0.0
     */
    public function __call ( $name, $args ) {
        $name = (string) $name;

        if ( !isset( $this->deprecated ) ) {
            require_once( PODS_DIR . 'deprecated/classes/PodsAPI.php' );
            $this->deprecated = new PodsAPI_Deprecated( $this );
        }

        if ( method_exists( $this->deprecated, $name ) ) {
            $arg_count = count( $args );
            if ( 0 == $arg_count )
                $this->deprecated->{$name}();
            elseif ( 1 == $arg_count )
                $this->deprecated->{$name}( $args[ 0 ] );
            elseif ( 2 == $arg_count )
                $this->deprecated->{$name}( $args[ 0 ], $args[ 1 ] );
            elseif ( 3 == $arg_count )
                $this->deprecated->{$name}( $args[ 0 ], $args[ 1 ], $args[ 2 ] );
            else
                $this->deprecated->{$name}( $args[ 0 ], $args[ 1 ], $args[ 2 ], $args[ 3 ] );
        }
        else
            pods_deprecated( "PodsAPI::{$name}", '2.0.0' );
    }
}
