<?PHP
/*
Plugin Name: Message Flow
Plugin URI: http://JoeAnzalone.com/plugins/message-flow
Description: Provides a shortcode that generates a cover flow-like interface for all podcasts in a given category or feed: [message-flow category="11"]
Version: 1.1
Author: Joe Anzalone
Author URI: http://JoeAnzalone.com
License: GPL2
*/
?>
<?PHP
	
class shmit_message_flow {
	
	function head_scripts(){
		echo '<link rel="stylesheet" href="'.$this->plugin_url.'/mediaelementjs/build/mediaelementplayer.min.css" />';
		echo '<link rel="stylesheet" href="'.$this->plugin_url.'/css/main.css" />';
	}
	
	function get_posts_from_feed($params){
		$feed_object = fetch_feed($params['feed']);

		$items = $feed_object->data['child']['']['rss'][0]['child']['']['channel'][0]['child']['']['item'];
		foreach($items as $k => $item){

			$posts_array[$k]->podcast_episode_url = $item['child']['']['enclosure'][0]['attribs']['']['url'];
			$posts_array[$k]->post_title = $item['child']['']['title'][0]['data'];
			$posts_array[$k]->post_content = $item['child']['http://purl.org/rss/1.0/modules/content/']['encoded'][0]['data'];
			
			$posts_array[$k]->thumbnail = $item['child']['http://www.itunes.com/dtds/podcast-1.0.dtd']['image'][0]['attribs']['']['href'];
			
			$posts_array[$k]->ID = $k;
			$posts_array[$k]->from_external_feed = TRUE;
		}

		if(!$params['allow_silent']){
			foreach($posts_array as $i => $post){
				if(empty($posts_array[$i]->podcast_episode_url)){
					unset($posts_array[$i]);
				}
			}
		}

		$posts_array = array_splice($posts_array, 0, $params['numberposts']);
		
		$posts_array = array_reverse($posts_array);
		return $posts_array;
	}
	
	function shortcode($params){
		
		$default_params = array(
			'numberposts' => 10,
			'category' => '',
			'allow_silent' => FALSE,
			'download_link_rel' => NULL,
		);
		
		foreach($default_params as $k => $v){
			if(empty($params[$k])){
				$params[$k] = $v;
			}
		}
	
		if(!empty($params['feed'])){
			$from_external_feed = TRUE;
			$posts_array = $this->get_posts_from_feed($params);
		} else {
		
			$get_posts_args = array(
				'numberposts' => $params['numberposts'],
				'category' => $params['category'],
				'orderby' => 'post_date',
				'order' => 'ASC',
			);
		
			if(!$params['allow_silent']){
				$get_posts_args['meta_key'] = 'enclosure';
			}
			
			$from_external_feed = FALSE;
			$posts_array = get_posts($get_posts_args);
		}
		
		$html = '';
		wp_enqueue_script('content-flow', $this->plugin_url . '/contentflow/contentflow.js');
		wp_enqueue_script('mediaelementjs', $this->plugin_url . '/mediaelementjs/build/mediaelement-and-player.min.js', array('jquery'));
		wp_enqueue_script('message-flow-main', $this->plugin_url . '/js/main.js', array('jquery', 'content-flow', 'mediaelementjs'));
		
		$html .= '<div class="message-flow">';
		$html .= '<div class="ContentFlow">
			<div class="loadIndicator"><div class="indicator"></div></div>
            <div class="flow">';
	
		foreach($posts_array as $post){
			$enclosure = get_post_meta($post->ID, 'enclosure', TRUE);
			if(!empty($post->podcast_episode_url) OR (!$post->from_external_feed && !empty($enclosure))){
				$podcast_episode_text_content = $post->post_content;
				
				if(!empty($post->podcast_episode_url)){
					$podcast_episode_url = $post->podcast_episode_url;
				} else {
					$enclosure_matches = preg_split('#\r\n|\r|\n#', $enclosure, 2);
					$podcast_episode_url = $enclosure_matches[0];
				}
				
				if(!$from_external_feed){
					$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'small', FALSE );
					$thumbnail_src = $thumbnail[0];
				} else {
					$thumbnail_src = $post->thumbnail;
				}
				
				if(empty($thumbnail_src)){				
					$powerpress_feed_options = get_option('powerpress_feed'); // Get the main feed settings
					
					if(!empty($params['fallback_image'])){
						$thumbnail_src = $params['fallback_image'];
					} elseif(!empty($powerpress_feed_options['rss2_image'])){
						$thumbnail_src = $powerpress_feed_options['rss2_image'];
					} elseif(!empty($powerpress_feed_options['itunes_image'])) {
						$thumbnail_src = $powerpress_feed_options['itunes_image'];
					} elseif(is_file(get_stylesheet_directory() . '/unknown-album_165.png')) {
						$thumbnail_src =  get_stylesheet_directory_uri() . '/unknown-album_165.png';
					} else {
						$thumbnail_src =  $this->plugin_url . '/images/unknown-album_165.png';
					}
				}
				
				$html .= '<div class="item">
				<img data-episode-id="'.$post->ID.'" data-podcast-episode-title="'.$post->post_title.'" data-podcast-episode-url="'. $podcast_episode_url . '" class="content" src="'.$thumbnail_src.'"/>
				<div class="caption">'.$post->post_title.'</div>
				</div>';
				
				$podcast_episodes_text_contents[$post->ID] = $podcast_episode_text_content;
			}
		}

		$html .= '</div>
            <div class="globalCaption"></div>
            <div class="scrollbar"><div class="slider"><div class="position"></div></div></div>
			</div>';
		
		if(!empty($params['download_link_rel'])){
			$download_link_rel = 'rel="'.$params['download_link_rel'].'" ';
		}
		
		$html .= '<div class="now-playing">
		Now playing:
		<h2 class="podcast-episode-title">'. $post->post_title .'</h2>
		<div class="download">[<a '.$download_link_rel.'href="'.$podcast_episode_url.'">Download as MP3</a>]</div>
		</div>';
		
		if(!empty($podcast_episode_url)){
			$html .= '<audio class="podcast_player" src="'. $podcast_episode_url .'" controls="controls">';
			$html .= '<a href="'.$podcast_episode_url.'">Click here to listen to the podcast</a>';
			$html .= '</audio>';
		}
		
		$html .= '<div class="current podcast-episode-text-content">';
		$html .= $podcast_episode_text_content;
		
		$html .= '</div>';
		
		if(!empty($podcast_episodes_text_contents)){
			foreach($podcast_episodes_text_contents as $k => $content){
				$html .= '<div data-episode-id="'.$k.'" class="hidden podcast-episode-text-content">';
				$html .= $content;
				$html .= '</div>';
			}
		}
		
		$html .= '</div>';
		
		if(empty($posts_array)){
			$html = '';
		}
		
		return $html;
	}

	function __construct(){
		$this->plugin_url = plugins_url(NULL, __FILE__);
		
		add_shortcode( 'message-flow', array($this,'shortcode') );
		
		add_action('wp_head', array($this,'head_scripts'));
		
	}

}

new shmit_message_flow;

?>