<?php

  require 'inc/SocialFeed.php';

  require_once(TEMPLATEPATH . '/inc/widgets/socialstream.php');

  function feed_socialstream($comment) {	
	  $feed = new Feed;	
    
	  $feed->twitter = "hrblock";
	  $feed->facebook = "hrblock";
	  $feed->youtube = "hrblock";
	  $feed->blog = true;
	  $feed->count = 15;
	
	  $items = $feed->json();
	  foreach ( $items as $item ) {
	  	echo $item['type'] . " - " . $item['ago'] . " - " . $item['short_formatted'] . "<br />";
	  }
	
	  //header('Content-type: application/json');	
	  //echo json_encode($feed->json());
	
  }
  add_feed('socialstream', 'feed_socialstream');

	// query all posts
	// $myposts = get_posts('numberposts=-1&offset=0&orderby=title');
	// $choices = array('' => '');
	// foreach($myposts as $post) {            
    //	  $choices[$post->ID] = $post->post_title;
	// }
	//  foreach($choices AS $key=>$option) :
	//	$chooseFrom .= sprintf('<option value="%s" %s>%s</option>',
	//	  $key,($current_value == $key ) ? ' selected="selected"' : NULL,$option);
	//	endforeach;
    //    
    //    printf('
	//<select id="%s" name="%1$s[text_string]">%2$s</select>
	//%3$s',$value['name'],$chooseFrom,
	//  (!empty ($value['description'])) ? sprintf("<em>%s</em>",$value['description']) : NULL);
	//  }
?>