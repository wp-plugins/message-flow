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
						var new_post_permalink = $('canvas', e.element).data('post-permalink');
						var new_podcast_episode_text_content = $(this_message_flow).find('.hidden.podcast-episode-text-content[data-episode-id='+new_podcast_episode_id+']').html();

						$(this_message_flow).find('.current.podcast-episode-text-content').html(new_podcast_episode_text_content);

						
						var new_podcast_episode_title = $('canvas', e.element).data('podcast-episode-title');
						$(this_message_flow).find('.podcast-episode-title').html(new_podcast_episode_title);
						
						// $(this_message_flow).find('.now-playing .post-permalink a').prop('href', new_post_permalink);
						$(this_message_flow).find('.now-playing a.post-permalink').prop('href', new_post_permalink);
						
						
						
						if(new_podcast_episode_url){
							$(this_message_flow).find('.now-playing .download').css({display: 'inline'});
							$(this_message_flow).find('.now-playing .download a').prop('href', new_podcast_episode_url);
							if( $(this_message_flow).find('.podcast_player').length == 0 ){
								//console.log('no player!');
								
								var newPlayer = $('<audio class="podcast_player" src="'+new_podcast_episode_url+'" controls="controls"></audio>');
								$(newPlayer).append('<a href="'+new_podcast_episode_url+'">Click here to listen to the podcast</a>');
								
								$(this_message_flow).find('.now-playing').after(newPlayer);
								$('audio,video').mediaelementplayer();
							}
							
							$(this_message_flow).find('.podcast_player').css({display: 'block'});
							//var player_id = this_message_flow.children('.podcast_player').prop('id');

							
							player = this_message_flow.find('.podcast_player audio')[0].player;
							player.setSrc(new_podcast_episode_url);
							player.play();
						} else {
							$(this_message_flow).find('.now-playing .download').css({display: 'none'});
							$(this_message_flow).find('.podcast_player').remove();
						}
						
					}
				});
				
				e.moveTo('last');
				//e.moveTo('first');
			};
		});
		
	});

})( jQuery );