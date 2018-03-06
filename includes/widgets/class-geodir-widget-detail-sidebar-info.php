<?php
/**
* GeoDirectory Detail Sidebar Info Widget
*
* @since 2.0.0
*
* @package GeoDirectory
*/

/**
 * GeoDir_Widget_Detail_Sidebar_Info class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Detail_Sidebar_Info extends WP_Super_Duper {
    
	public $arguments;

    /**
     * Sets up a new Detail Sidebar Info widget instance.
     *
     * @since 2.0.0
     * @access public
     */
    public function __construct() {
        $options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'location-alt',
			'block-category'=> 'common',
			'block-keywords'=> "['detail','geodir','geodirectory']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_detail_sidebar_info', // this us used as the widget id and the shortcode id.
			'name'          => __( 'GD > Detail Sidebar Info', 'geodirectory' ), // the name of the widget.
			'widget_ops'    => array(
				'classname'   	=> 'geodir-detail-sidebar-info', // widget class
				'description' 	=> esc_html__( 'Show listing information on detail page sidebar.', 'geodirectory' ), // widget description
				'geodirectory' 	=> true,
				'gd_show_pages' => array( 'detail', 'preview' ),
			),
			'arguments'     => array(
			)
		);

		parent::__construct( $options );
    }

    /**
	 * The Super block output function.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
        if ( !( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) ) {
            return;
        }
        
        /**
         * Filters the widget title.
         *
         * @since 2.0.0
         *
         * @param string $title    The widget title. Default 'Pages'.
         * @param array  $widget_args An array of the widget's settings.
         * @param mixed  $id_base  The widget ID.
         */
        $title = apply_filters( 'widget_title', empty( $widget_args['title'] ) ? '' : $widget_args['title'], $widget_args, $this->id_base );
        
        ob_start();
        
        do_action( 'geodir_widget_before_detail_sidebar_info' );
        
        geodir_detail_page_more_info();
        
        do_action( 'geodir_widget_after_detail_sidebar_info' );
        
        $content = ob_get_clean();
        
        $content = trim( $content );
        if ( empty( $content ) ) {
            return;
        }
		
		ob_start();
        
        echo $args['before_widget'];
        
        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        echo $content;
        
        echo $args['after_widget'];
		
		$output = ob_get_clean();
		
		return $output;
    }
}


class GeoDir_Widget_Detail_Sidebar_Info_OLD extends WP_Widget {
    
    /**
     * Sets up a new Detail Sidebar Info widget instance.
     *
     * @since 2.0.0
     * @access public
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'geodir-widget gd-widget-detail-sidebar-info',
            'description' => __( 'Show listing information on detail page sidebar.', 'geodirectory' ),
            'customize_selective_refresh' => true,
            'geodirectory' => true,
            'gd_show_pages' => array( 'detail' ),
        );
        parent::__construct( 'detail_sidebar_info', __( 'GD > Detail Sidebar Info', 'geodirectory' ), $widget_ops );
    }

    /**
     * Outputs the content for the current Detail Sidebar Info widget instance.
     *
     * @since 2.0.0
     * @access public
     *
     * @param array $args     Display arguments including 'before_title', 'after_title',
     *                        'before_widget', and 'after_widget'.
     * @param array $instance Settings for the current Detail Rating widget instance.
     */
    public function widget( $args, $instance ) {
        if ( !( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) ) {
            return;
        }
        
        /**
         * Filters the widget title.
         *
         * @since 2.0.0
         *
         * @param string $title    The widget title. Default 'Pages'.
         * @param array  $instance An array of the widget's settings.
         * @param mixed  $id_base  The widget ID.
         */
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
        
        ob_start();
        
        do_action( 'geodir_widget_before_detail_sidebar_info' );
        
        geodir_detail_page_more_info();
        
        do_action( 'geodir_widget_after_detail_sidebar_info' );
        
        $content = ob_get_clean();
        
        $content = trim( $content );
        if ( empty( $content ) ) {
            return;
        }
        
        echo $args['before_widget'];
        
        if ( $title ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        echo $content;
        
        echo $args['after_widget'];
    }

    /**
     * Handles updating settings for the current Detail Sidebar Info widget instance.
     *
     * @since 2.0.0
     * @access public
     *
     * @param array $new_instance New settings for this instance as input by the user via
     *                            WP_Widget::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Updated settings to save.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        
        $instance['title'] = sanitize_text_field( $new_instance['title'] );

        return $instance;
    }
    
    /**
     * Outputs the settings form for the Detail Sidebar Info widget.
     *
     * @since 2.0.0
     * @access public
     *
     * @param array $instance Current settings.
     */
    public function form( $instance ) {
        // Defaults
        $instance = wp_parse_args( (array)$instance, 
            array( 
                'title' => ''
            ) 
        );
        
        $title = sanitize_text_field( $instance['title'] );
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:', 'geodirectory' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        <?php
    }
}