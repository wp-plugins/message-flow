<?PHP
/*
Plugin Name: Message Flow
Plugin URI: http://JoeAnzalone.com/plugins/message-flow
Description: Provides a shortcode that generates a cover flow-like interface for all podcasts in a given category or feed: [message-flow category="11"]
Version: 1.1.5
Author: Joe Anzalone
Author URI: http://JoeAnzalone.com
License: GPL2
*/
?>
<?PHP

class shmit_message_flow {
	
	
/*
* Gets the excerpt of a specific post ID or object
* @param - $post - object/int - the ID or object of the post to get the excerpt of
* @param - $length - int - the length of the excerpt in words
* @param - $tags - string - the allowed HTML tags. These will not be stripped out
* @param - $extra - string - text to append to the end of the excerpt
http://pippinsplugins.com/a-better-wordpress-excerpt-by-id-function/
*/
function get_excerpt_by_id($post, $length = 10, $tags = '<a><em><strong>', $extra = ' . . .') {
 
	if(is_int($post)) {
		// get the post object of the passed ID
		$post = get_post($post);
	} elseif(!is_object($post)) {
		return false;
	}
 
	if(has_excerpt($post->ID)) {
		$the_excerpt = $post->post_excerpt;
		return apply_filters('the_content', $the_excerpt);
	} else {
		$the_excerpt = $post->post_content;
	}
 
	$the_excerpt = strip_shortcodes( strip_tags($the_excerpt, $tags) );
	$the_excerpt = preg_split('/\b/', $the_excerpt, $length * 2+1);
	$excerpt_waste = array_pop($the_excerpt);
	$the_excerpt = implode($the_excerpt);
	$the_excerpt .= $extra;
 
	return apply_filters('the_content', $the_excerpt);
}
	
	
	function head_scripts(){
		echo '<link rel="stylesheet" href="'.$this->plugin_url.'/mediaelementjs/build/mediaelementplayer.min.css" />';
		echo '<link rel="stylesheet" href="'.$this->plugin_url.'/css/main.css" />';
	}
	
	function get_posts_from_feed($params){
		$feed_object = fetch_feed($params['feed']);

		$items = $feed_object->data['child']['']['rss'][0]['child']['']['channel'][0]['child']['']['item'];
		foreach($items as $k => $item){

			if(!empty($item['child']['']['enclosure'][0]['attribs']['']['url'])){
				$posts_array[$k]->podcast_episode_url = $item['child']['']['enclosure'][0]['attribs']['']['url'];
			}
			
			if(!empty($item['child']['']['link'][0]['data'])){
				$posts_array[$k]->permalink = $item['child']['']['link'][0]['data'];
			}
			
			if(!empty($item['child']['']['title'][0]['data'])){
				$posts_array[$k]->post_title = $item['child']['']['title'][0]['data'];
			}
			
			if(!empty($item['child']['http://purl.org/rss/1.0/modules/content/']['encoded'][0]['data'])){
				$posts_array[$k]->post_content = $item['child']['http://purl.org/rss/1.0/modules/content/']['encoded'][0]['data'];
			}
			
			if(!empty($item['child']['http://www.itunes.com/dtds/podcast-1.0.dtd']['image'][0]['attribs']['']['href'])){
				$posts_array[$k]->thumbnail = $item['child']['http://www.itunes.com/dtds/podcast-1.0.dtd']['image'][0]['attribs']['']['href'];
			}
			
			$posts_array[$k]->ID = $k;
			$posts_array[$k]->from_external_feed = TRUE;
		}

		if($params['podcasts_only']){
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
	
		if(is_array($params)){
			foreach($params as $k => $v){
				if(strtolower(trim($v)) == 'false'){
					$params[$k] = FALSE;
				}
			}
		}
		$default_params = array(
			'numberposts' => 10,
			'category' => '',
			'podcasts_only' => FALSE,
			'download_link_rel' => NULL,
			'permalink_link_rel' => NULL,
			'show_excerpt' => TRUE,
			'order' => 'DESC',
		);
		
		foreach($default_params as $k => $v){
			if(!isset($params[$k])){
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
				'order' => $params['order'],
			);
		
			if($params['podcasts_only']){
				$get_posts_args['meta_key'] = 'enclosure';
			}
			
			$from_external_feed = FALSE;
			$posts_array = get_posts($get_posts_args);
			$posts_array = array_reverse($posts_array);
		}
		
		$html = '';
		wp_enqueue_script('content-flow', $this->plugin_url . '/contentflow/contentflow.js');
		wp_enqueue_script('mediaelementjs', $this->plugin_url . '/mediaelementjs/build/mediaelement-and-player.min.js', array('jquery'));
		wp_enqueue_script('message-flow-main', $this->plugin_url . '/js/main.js', array('jquery', 'content-flow', 'mediaelementjs'));
		
		$html .= '<div class="message-flow">';
		$html .= '<div class="ContentFlow">
			<div class="loadIndicator"><div class="indicator"></div></div>
            <div class="flow">';
	
		if(empty($posts_array)){
			return NULL;
		}
		foreach($posts_array as $post){
			if(empty($post->from_external_feed)){
				$post->from_external_feed = FALSE;
			}
			$enclosure = get_post_meta($post->ID, 'enclosure', TRUE);
			if(!$params['podcasts_only'] OR !empty($post->podcast_episode_url) OR (!$post->from_external_feed && !empty($enclosure))){
			
				if( !$from_external_feed ){
					$post->permalink = get_permalink($post->ID);
				}					
				$post_permalink = $post->permalink;
			
				if(!empty($post->post_content)){
					//$podcast_episode_text_content = $post->post_content;
					$allowed_tags = '<a><em><strong>';
					$post->post_content = trim( strip_tags($post->post_content, $allowed_tags) );
					if($params['show_excerpt']){
						$excerpt = trim( $this->get_excerpt_by_id($post, 10, $allowed_tags, ' ') );
						$podcast_episode_text_content = $excerpt;
						
						
						if($excerpt != $post->post_content){
							$podcast_episode_text_content .= '<div class="read-more"><a href="'.$post_permalink.'">Continue reading...</a></div>';
						}
					}
				} else {
					$podcast_episode_text_content = NULL;
				}
			
				
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
					if(!empty($post->thumbnail)){
						$thumbnail_src = $post->thumbnail;
					} else {
						$thumbnail_src = NULL;
					}
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
				<img data-episode-id="'.$post->ID.'" data-podcast-episode-title="'.$post->post_title.'" data-podcast-episode-url="'. $podcast_episode_url . '" data-post-permalink="'.$post_permalink.'" class="content" src="'.$thumbnail_src.'"/>
				<div class="caption">'.$post->post_title.'</div>
				</div>';
				

				$podcast_episodes_text_contents[$post->ID] = $podcast_episode_text_content;

			}
		}

		$html .= '</div>
            <div class="globalCaption"></div>
            <div class="scrollbar"><div class="slider"><div class="position"></div></div></div>
			</div>';
		
		if(!empty($params['permalink_link_rel'])){
			$permalink_link_rel = 'rel="'.$params['permalink_link_rel'].'" ';
		} else {
			$permalink_link_rel = NULL;
		}
		
		if(!empty($params['download_link_rel'])){
			$download_link_rel = 'rel="'.$params['download_link_rel'].'" ';
		} else {
			$download_link_rel = NULL;
		}
		
		$html .= '<div class="now-playing">';
		//$html .= '<span class="now-playing-label">Now playing:</span>';
		
		if(!empty($post_permalink)){
			$html .= '<a '.$permalink_link_rel.'class="post-permalink" '.$permalink_link_rel.'href="'.$post_permalink.'">';
		}		
		
		$html .= '<h2 class="podcast-episode-title">'. $post->post_title .'</h2>';
		
		if(!empty($post_permalink)){
			$html .= '</a>';
		}

		
		if(!empty($podcast_episode_url)){
			$html .= '<div class="download">[<a '.$download_link_rel.'href="'.$podcast_episode_url.'">Download as MP3</a>]</div>';
		}
		
		$html .= '</div>';
		
		if(!empty($podcast_episode_url)){
			$html .= '<audio class="podcast_player" src="'. $podcast_episode_url .'" controls="controls">';
			$html .= '<a href="'.$podcast_episode_url.'">Click here to listen to the podcast</a>';
			$html .= '</audio>';
		}
		
		$html .= '<div class="current podcast-episode-text-content">';
		
		if(!empty($podcast_episode_text_content)){
			$html .= $podcast_episode_text_content;
		}
		
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