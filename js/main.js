(function($) {

	$('audio,video').mediaelementplayer();
	

	$(document).ready(function(){
	
		$('.message-flow .download a').click(function(e){
				e.preventDefault();
				window.open( $(this).attr('href') );
				return false;
			});
	
	
		function wait_to_be_set(variable, callback){
			var interval_id = window.setInterval(function(){
				if(variable.length == 0){
					//console.log('same old, same old');
				} else {
					callback();
					clearInterval(interval_id);
				}
			}, 500);
		};
		
		wait_to_be_set(ContentFlowGlobal.Flows, function(){
			for(var key in ContentFlowGlobal.Flows){
			var e = ContentFlowGlobal.Flows[key];
				//alert('Hi!');
			
				//ContentFlowGlobal.Flows[1].setConfig({circularFlow: false});
				e.setConfig({
					startItem: 'end',
					onclickActiveItem: function(e){
						var this_message_flow = $(e.element).parents('.message-flow');
						var new_podcast_episode_url = $('canvas', e.element).data('podcast-episode-url');
						var new_podcast_episode_id = $('canvas', e.element).data('episode-id');
						var new_podcast_episode_text_content = $(this_message_flow).find('.hidden.podcast-episode-text-content[data-episode-id='+new_podcast_episode_id+']').html();

						$(this_message_flow).find('.current.podcast-episode-text-content').html(new_podcast_episode_text_content);
						$(this_message_flow).find('.now-playing .download a').prop('href', new_podcast_episode_url);
						
						var new_podcast_episode_title = $('canvas', e.element).data('podcast-episode-title');
						$(this_message_flow).find('.podcast-episode-title').html(new_podcast_episode_title);
						
						//var player_id = this_message_flow.children('.podcast_player').prop('id');
						player = this_message_flow.find('.podcast_player audio')[0].player;
						player.setSrc(new_podcast_episode_url);
						player.play();
					}
				});
				
				e.moveTo('last');
			};
		});
		
	});

})( jQuery );