<?php

class Custom_Meta_Box {

    public function __construct() {
        // Setup Hooks
        $this->setup_hooks();
    }

    public function setup_hooks() {
        // Instance Hooks
        add_action( 'add_meta_boxes', [ $this, 'metabox_callback_func' ] );
        add_action( 'save_post', [ $this, 'save_post_metabox_data' ] );
    }

    /**
     * Add custom meta box function
     *
     * @return void
     */
    public function metabox_callback_func() {
        // Add Metabox Function
        add_meta_box( '_product-addition-infos', __( 'Additional Information', 'text-domain' ), [ $this, 'output_callback_function' ], 'product', 'normal', 'default' );
    }

    /**
     * custom meta box HTML (for form)
     *
     * @param object $post
     * @return void
     */
    public function output_callback_function( $post ) {

        // Get the custom meta data
        $mainColor    = get_post_meta( get_the_ID(), '_maincolor', true );
        $mainMaterial = get_post_meta( get_the_ID(), '_mainmaterial', true );
        $mainFinish   = get_post_meta( get_the_ID(), '_mainfinish', true );
        $boxWeight    = get_post_meta( get_the_ID(), '_boxweight', true );
        $cubes        = get_post_meta( get_the_ID(), '_cubes', true );
        $boxSize      = get_post_meta( get_the_ID(), '_jalalboxsize', true );
        $boxWidth     = get_post_meta( get_the_ID(), '_jalalboxwidth', true );
        $boxHeight    = get_post_meta( get_the_ID(), '_jalalboxheight', true );

        ?>

        <p>
            <label for="main-color">
                <?php esc_html_e( 'Main Color', 'text-domain' ); ?>
            </label>
            <input type="text" class="widefat" value="<?php echo $mainColor; ?>" name="main-color" id="main-color"
                placeholder="Main Color">
        </p>

        <p>
            <label for="main-material">
                <?php esc_html_e( 'Main Material', 'text-domain' ); ?>
            </label>
            <input type="text" class="widefat" value="<?php echo $mainMaterial; ?>" name="main-material" id="main-material"
                placeholder="Main Material">
        </p>

        <p>
            <label for="main-finish">
                <?php esc_html_e( 'Main Finish', 'text-domain' ); ?>
            </label>
            <input type="text" class="widefat" value="<?php echo $mainFinish; ?>" name="main-finish" id="main-finish"
                placeholder="Main Material">
        </p>

        <p>
            <label for="box-weight">
                <?php esc_html_e( 'Box Weight', 'text-domain' ); ?>
            </label>
            <input type="text" class="widefat" value="<?php echo $boxWeight; ?>" name="box-weight" id="box-weight"
                placeholder="Box Weight">
        </p>

        <p>
            <label for="cubes">
                <?php esc_html_e( 'Cubes', 'text-domain' ); ?>
            </label>
            <input type="text" class="widefat" value="<?php echo $cubes; ?>" name="cubes" id="cubes" placeholder="Cubes">
        </p>

        <p>
            <label for="box-size">
                <?php esc_html_e( 'Box Size', 'text-domain' ); ?>
            </label>
            <input type="text" class="widefat" value="<?php echo $boxSize; ?>" name="box-size" id="box-size"
                placeholder="Box Size">
        </p>

        <p>
            <label for="box-width">
                <?php esc_html_e( 'Box Width', 'text-domain' ); ?>
            </label>
            <input type="text" class="widefat" value="<?php echo $boxWidth; ?>" name="box-width" id="box-width"
                placeholder="Box Width">
        </p>

        <p>
            <label for="box-height">
                <?php esc_html_e( 'Box Height', 'text-domain' ); ?>
            </label>
            <input type="text" class="widefat" value="<?php echo $boxHeight; ?>" name="box-height" id="box-height"
                placeholder="Box Height">
        </p>


        <?php
    }

    /**
     * Update metabox form data
     *
     * @param integer $post_id
     * @return void
     */
    public function save_post_metabox_data( $post_id ) {

        $mainColor    = isset( $_POST['main-color'] ) ? $_POST['main-color'] : '';
        $mainMaterial = isset( $_POST['main-material'] ) ? $_POST['main-material'] : '';
        $mainFinish   = isset( $_POST['main-finish'] ) ? $_POST['main-finish'] : '';
        $boxWeight    = isset( $_POST['box-weight'] ) ? $_POST['box-weight'] : '';
        $cubes        = isset( $_POST['cubes'] ) ? $_POST['cubes'] : '';
        $boxSize      = isset( $_POST['box-size'] ) ? $_POST['box-size'] : '';
        $boxWidth     = isset( $_POST['box-width'] ) ? $_POST['box-width'] : '';
        $boxHeight    = isset( $_POST['box-height'] ) ? $_POST['box-height'] : '';

        if ( array_key_exists( 'main-color', $_POST ) ) {
            update_post_meta( $post_id, '_maincolor', $mainColor );
        }

        if ( array_key_exists( 'main-material', $_POST ) ) {
            update_post_meta( $post_id, '_mainmaterial', $mainMaterial );
        }

        if ( array_key_exists( 'main-finish', $_POST ) ) {
            update_post_meta( $post_id, '_mainfinish', $mainFinish );
        }

        if ( array_key_exists( 'box-weight', $_POST ) ) {
            update_post_meta( $post_id, '_boxweight', $boxWidth );
        }

        if ( array_key_exists( 'cubes', $_POST ) ) {
            update_post_meta( $post_id, '_cubes', $cubes );
        }

        if ( array_key_exists( 'box-size', $_POST ) ) {
            update_post_meta( $post_id, '_jalalboxsize', $boxSize );
        }

        if ( array_key_exists( 'box-width', $_POST ) ) {
            update_post_meta( $post_id, '_jalalboxwidth', $boxWidth );
        }

        if ( array_key_exists( 'box-height', $_POST ) ) {
            update_post_meta( $post_id, '_jalalboxheight', $boxHeight );
        }
    }
}

new Custom_Meta_Box();