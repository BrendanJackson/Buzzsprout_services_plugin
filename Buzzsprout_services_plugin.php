<?php
/*
Plugin Name: Buzzsprout Services Plugin
Plugin URI: http://www.wpexplorer.com/create-widget-plugin-wordpress/
Description: This plugin adds a buzzsprout services custom widget.
Version: 1.0
*/

define( 'BUZZSPROUT_SERVICES_VERSION', '1.0.0' );

class Buzzsprout_Services_Widget extends WP_Widget
{



//	public $buzzsprout_channel_ID = "";

	// Main constructor
	public function __construct() {
		parent::__construct(
			'buzzsprout_services_widget',
			__( 'Buzzsprout_Services', 'text_domain' ),
			array(
				'customize_selective_refresh' => true,
			)
		);
	}


	public function getServices($buzzsprout_channel_ID )
	{
		$html = file_get_contents("https://www.buzzsprout.com/" . $buzzsprout_channel_ID  );
		//Create a new DOM document
		$dom = new DOMDocument;

		//Parse the HTML. The @ is used to suppress any parsing errors
		//that will be thrown if the $html string isn't valid XHTML.
		@$dom->loadHTML($html);

		$xpath = new DOMXpath($dom);
		$services = $xpath->query('//div[contains(@class, "listen-modal__list")]'); //instance of DOMNodeList
		return $services;
	}


	// The widget form (for the backend )
	public function form( $instance ) {

		// Set widget defaults
		$defaults = array(
			'title'    => '',
			'buzzsprout_channel_ID'    => '',
			'checkbox' => '',
			'buzzsprout_services' => array(),

		);
		
		// Parse current settings with defaults
		extract( wp_parse_args( ( array ) $instance, $defaults ) ); ?>

		<?php // Widget Title ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Widget Title', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php // Widget buzzsprout_channel_ID ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'buzzsprout_channel_ID' ) ); ?>"><?php _e( 'Widget buzzsprout_channel_ID', 'text_domain' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'buzzsprout_channel_ID' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'buzzsprout_channel_ID' ) ); ?>" type="text" value="<?php echo esc_attr( $buzzsprout_channel_ID ); ?>" />
		</p>



		<?php // Checkbox ?>
		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'checkbox' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'checkbox' ) ); ?>" type="checkbox" value="1" <?php checked( '1', $checkbox ); ?> />
			<label for="<?php echo esc_attr( $this->get_field_id( 'checkbox' ) ); ?>"><?php _e( 'Enable Automatic Scrape', 'text_domain' ); ?></label>
		</p>


		<?php // services ?>
		<p>
			<label for="buzzsprout-services">Buzzsprout Services</label>

			<ul style="padding-left: 16px;">
				<?php
					if ($checkbox ):

						$services = $this->getServices( $buzzsprout_channel_ID );
						foreach ($services as $service)
						{
							$nodes = $service->childNodes;
							$i = 0;
							foreach ($nodes as $node)
							{
								if ($node->nodeName == "a"){
									echo "<li>" . $node->nodeValue;;
									?>
									<input class='widefat hidden' name="<?php echo esc_attr( $this->get_field_name( 'buzzsprout_services' ) ) . "[" . $i . "]" ; ?>" type='text' value='<?php echo $node->nodeValue; ?>'  >

									<?php
									echo "</li>";
									$i++;
								}

							}

						}

					else:


						for($i = 0; $i < count($buzzsprout_services); $i++)
						{
							echo "<li>" . $buzzsprout_services[$i];
							?>
							<input class='widefat hidden' name="<?php echo esc_attr( $this->get_field_name( 'buzzsprout_services' ) ) . "[" . $i . "]" ; ?>" type='text' value='<?php echo $buzzsprout_services[$i]; ?>'  >
							<?php
							echo "</li>";

						}

					endif;

				?>
			</ul>

		</p>

	<?php }

	// Update widget settings
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['buzzsprout_services'] = array();
//		echo "<pre>	new_instance";
//		var_dump($new_instance);
//		echo "</pre>";
//		exit();

		$instance['title']    = isset( $new_instance['title'] ) ? wp_strip_all_tags( $new_instance['title'] ) : '';
		$instance['checkbox'] = isset( $new_instance['checkbox'] ) ? 1 : false;
		$instance['buzzsprout_channel_ID'] = isset( $new_instance['buzzsprout_channel_ID'] ) ? wp_strip_all_tags( $new_instance['buzzsprout_channel_ID'] ) : '';
//		$instance['buzzsprout_services'] = isset( $new_instance['buzzsprout_services'] ) ? wp_strip_all_tags( $new_instance['buzzsprout_services'] ) : '';
		if ( isset ( $new_instance['buzzsprout_services'] ) )
		{
			foreach ( $new_instance['buzzsprout_services'] as $value )
			{
				if ( '' !== trim( $value ) )
					$instance['buzzsprout_services'][] = $value;
			}
		}
		return $instance;
	}

	// Display the widget
	public function widget( $args, $instance ) {

		extract( $args );

		// Check the widget options
		$title = isset( $instance['title'] ) ? apply_filters( 'widget_title', $instance['title'] ) : '';
		$buzzsprout_channel_ID  = isset( $instance['buzzsprout_channel_ID'] ) ? $instance['buzzsprout_channel_ID'] : '';
		$buzzsprout_services = isset( $instance['buzzsprout_services'] ) ? $instance['buzzsprout_services'] : '';
		$checkbox = ! empty( $instance['checkbox'] ) ? $instance['checkbox'] : false;

		// WordPress core before_widget hook (always include )
		echo $before_widget;

		// Display the widget
		echo '<div class="widget-text wp_widget_plugin_box">';

			// Display widget title if defined
			if ( $title ) {
				echo $before_title . $title . $after_title;
			}

			// Display buzzsprout_channel_ID field
			if ( $buzzsprout_channel_ID ) {
				echo '<p>Buzzsprout Channel: <a href="https://www.buzzsprout.com/' . $buzzsprout_channel_ID . '" >' . $buzzsprout_channel_ID . '</a></p>';
			}

			echo "<ul class='modal__list'> ";


			// Display something if checkbox is true
			if ( $checkbox ) {
//				echo '<p>automatic scrape</p>';
				$services = $this->getServices( $buzzsprout_channel_ID );
				foreach ($services as $service)
				{
					$nodes = $service->childNodes;
					$i = 0;
					foreach ($nodes as $node)
					{
						// <input name=" . esc_attr( $this->get_field_name( 'buzzsprout_services' ) ) . " type='text' value='" . $node->nodeValue . "' disabled >
						if ($node->nodeName == "a"){


							echo "
							<li class='modal__item modal__item--" . strtolower(strtok($node->nodeValue," ")) . "'>
								" . $node->nodeValue . " 
							</li>
							";
							$i++;
						}

					}

				}

			} else {
//				echo '<p>no automatic scrape</p>';
				if ($buzzsprout_services){

					foreach($buzzsprout_services as $buzzsprout_service)
					{
						echo "
							<li class='modal__item modal__item--" . strtolower(strtok($buzzsprout_service,' ')) . "'>
								" . $buzzsprout_service . " 
							</li>
						";
					}

				}
			}

			echo "</ul>";





		echo '</div>';

		// WordPress core after_widget hook (always include )
		echo $after_widget;

	}

}


// Register the widget
function register_buzzsprout_services_widget () {
	register_widget( 'Buzzsprout_Services_Widget' );
}
add_action( 'widgets_init', 'register_buzzsprout_services_widget' );

function load_assets() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'main', $plugin_url . 'assets/css/main.css' );
}
add_action( 'wp_enqueue_scripts', 'load_assets' );
