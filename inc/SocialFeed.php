<?php

	class FacebookFeed extends SocialFeed {
		
		var $url = 'https://graph.facebook.com/%s/posts?access_token=178783688861822|59Vu39OlE75gR8xjF9XbROZ636s&limit=%s';
		var $user = "";
		var $type = "facebook";
		
		protected function _feed_root($feed) {
			return $feed['data'];
		}
		
		protected function _map_item($item) {
			$id = explode("_", $item->{'id'});
			$link = 'http://www.facebook.com/' . $this->user . '/posts/' . $id[1];
			$text = $item->{'message'};
			$text_fmt = sprintf('<a href="%s">%s</a>', $link, $text);
			
			if ( $item->{'type'} == 'link' ) {
				$text = $item->{'description'};
				$link = $item->{'link'};
				//$text_fmt = sprintf('<a href="%s">%s</a> %s', $link, $item->{'name'}, $text);
				$text_fmt = $text;
			}
			
			return array(
				'type' => 'facebook',
				'time' => strtotime($item->{'created_time'}),
				'text' => $text,
				'text_formatted' => $text_fmt,
				'link' => $link,
			);
		}
	}
	
	class TwitterFeed extends SocialFeed {
		
		var $url = 'http://twitter.com/statuses/user_timeline.json?screen_name=%s&count=%s';
		var $user = "";
		var $type = "twitter";
		
		protected function _map_item($item) {
			$text = $item->{'text'};
			$text_fmt = $this->_twitter_it($text);
			$link = 'http://twitter.com/' . $this->user . '/statuses/' . $item->{'id_str'};
			
			return array(
				'type' => 'twitter',
				'time' => strtotime($item->{'created_at'}),
				'text' => $text,
				'text_formatted' => $text_fmt,
				'link' => $link,
			);
		}
		
		protected function _twitter_it($text) {  
			$text= preg_replace("/@(\w+)/", '<a href="http://www.twitter.com/$1" target="_blank">@$1</a>', $text);  
			$text= preg_replace("/\#(\w+)/", '<a href="http://search.twitter.com/search?q=$1" target="_blank">#$1</a>',$text);  
			$text= preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", $text);  
			$text= preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" >$3</a>", $text);  
			$text= preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $text);
			return $text;  
		}  
		
	}
	
	class YouTubeFeed extends SocialFeed {
		
		var $url = 'http://gdata.youtube.com/feeds/base/users/%s/uploads?orderby=updated&alt=json&client=ytapi-youtube-rss-redirect&v=2&max-results=%s';
		var $user = "";
		var $type = "youtube";
		
		protected function _feed_root($feed) {
			return $feed['feed']->{'entry'};
		}
		
		protected function _map_item($item) {
			$link = $item->{'link'}[0]->{'href'};
			$text = $item->{'title'}->{'$t'};
			$text_fmt = sprintf('<a href="%s">%s</a>', $link, $text);
			return array(
				'type' => 'youtube',
				'time' => strtotime($item->{'published'}->{'$t'}),
				'text' => $text,
				'text_formatted' => $text_fmt,
				'link' => $link,
			);
		}
	}
	
	class SocialFeed {
		
		var $user = "";
		var $count = 10;
		var $url = "";
		var $type = "feed";
		
		function get() {
			$feed = $this->_readFeed(
				sprintf($this->url, $this->user, $this->count), 
				"__" . $this->type . "_posts__{$this->user}"
			);
			return $this->_process($feed);
		}
		
		protected function _feed_root($feed) {
			return $feed;
		}
		
		protected function _process($feed) {
			$items = array();
			
			if ( $feed ) {
				foreach ( $this->_feed_root($feed) as $item ) {
					$newitem = $this->_map_item($item);
					if ( $newitem['text'] ) {
						array_push($items, $this->_map_item($item));
					}
				}
			}
			return $items;
		}
		
		protected function _map_item($item) {			
			return $item;
		}
		
		protected function _readFeed($url, $key) {
		
            // Just to keep the code below cleaner, create the cache key now
            $cache_key = $key;
			
			      // First we look for a cached result
            if ( false !== $results = get_transient( $cache_key ) )
                return $results;
 
            // Okay, no cache, so let's fetch it
            $result = wp_remote_retrieve_body( wp_remote_get( $url, array( 'sslverify' => false ) ) );
			    
			      // Check to make sure we got some data to work with
            if ( empty($result) ) {
                // Cache the failure for 1 min to avoid hammering server
                set_transient( $cache_key, 0, 60 );
            }
 
            // Parse the data
            $data = json_decode( $result );
 
            // Make sure we were able to parse it
            // If not, cache the failure (like above)
            if ( !isset( $data ) )
                set_transient( $cache_key , 0, 60 );
 
            // Success! Cache the result for an hour.
            $results = (array) $data;
            set_transient( $cache_key, $results, 600 ); // 10 minutes
 
            return $results;
		}
		
	}
	
	class Feed {
		
		var $twitter = "";
		var $facebook = "";
		var $youtube = "";
		var $count = 10;
		
		function json() {
			return $this->_mashFeeds();
		}
		
		protected function _mashFeeds() {
			$twitter = new TwitterFeed;
			$twitter->user = $this->twitter;
			$twitter->count = $this->count;			
			$tw = $twitter->get();
		
			$facebook = new FacebookFeed;
			$facebook->user = $this->facebook;
			$facebook->count = $this->count;			
			$fb = $facebook->get();
				
			$youtube = new YouTubeFeed;
			$youtube->user = $this->youtube;
			$youtube->count = $this->count;			
			$yt = $youtube->get();

			$all = $this->_sort(array_merge($fb, $tw, $yt));			
			$all = $this->_format_posts($all);
			
			return $all;
		}	
		
		protected function _sort($arry) {
			usort($arry, array($this, '_cmp'));
			return $arry;
		}

		protected function _format_posts($arry) {
			$newarry = array();
			
			foreach ( $arry as $item ) {
				$item['ago'] = $this->_ago($item['time']);
        $item['short_link'] = $this->_truncate($item['link'], '40');
        $item['short_text'] = $this->_truncate($item['text'], '140');
				$item['short_formatted'] = $this->_truncate($item['text_formatted'], '140');
				$item['date'] = date("Y.j.n H:m", $item['time']);
				array_push($newarry, $item);
			}
			
			return $newarry;
		}
		
		protected function _cmp($a, $b) {
			return ($a['time'] > $b['time']) ? -1 : 1;
		}
		
		protected function _ago($tm,$rcs = 0) {
		   $cur_tm = time(); $dif = $cur_tm-$tm;
		   $pds = array('second','minute','hour','day','week','month','year','decade');
		   $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
		   for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);

		   $no = floor($no); if($no <> 1) $pds[$v] .='s'; $x=sprintf("%d %s ",$no,$pds[$v]);
		   if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
		   return $x;
		}
		
		
		/** Truncate HTML, close opened tags. UTF-8 aware, and aware of unpaired tags
		 * (which don't need a matching closing tag)
		 *
		 * @param int $max_length Maximum length of the characters of the string
		 * @param string $html
		 * @param string $indicator Suffix to use if string was truncated.
		 * @return string
		 */
		protected function _truncate($html, $max_length, $indicator = '&hellip;' )
		{
			$output_length = 0; // number of counted characters stored so far in $output
			$position = 0;      // character offset within input string after last tag/entity
			$tag_stack = array(); // stack of tags we've encountered but not closed
			$output = '';
			$truncated = false;

			/** these tags don't have matching closing elements, in HTML (in XHTML they
			 * theoretically need a closing /> )
			 * @see http://www.netstrider.com/tutorials/HTMLRef/a_d.html
			 * @see http://www.w3schools.com/tags/default.asp
			 * @see http://stackoverflow.com/questions/3741896/what-do-you-call-tags-that-need-no-ending-tag
			 */
			$unpaired_tags = array( 'doctype', '!doctype',
				'area','base','basefont','bgsound','br','col',
				'embed','frame','hr','img','input','link','meta',
				'param','sound','spacer','wbr');

			// loop through, splitting at HTML entities or tags
			while ($output_length < $max_length
					&& preg_match('{</?([a-z]+)[^>]*>|&#?[a-zA-Z0-9]+;}', $html, $match, PREG_OFFSET_CAPTURE, $position))
			{
				list($tag, $tag_position) = $match[0];

				// get text leading up to the tag, and store it (up to max_length)
				$text = mb_substr($html, $position, $tag_position - $position);
				if ($output_length + strlen($text) > $max_length)
				{
					$output .= mb_substr($text, 0, $max_length - $output_length);
					$truncated = true;
					$output_length = $max_length;
					break;
				}

				// store everything, it wasn't too long
				$output .= $text;
				$output_length += strlen($text);

				if ($tag[0] == '&') // Handle HTML entity by copying straight through
				{
					$output .= $tag;
					$output_length++; // only counted as one character
				}
				else // Handle HTML tag
				{
					$tag_inner = $match[1][0];
					if ($tag[1] == '/') // This is a closing tag.
					{
						$output .= $tag;
						// If input tags aren't balanced, we leave the popped tag
						// on the stack so hopefully we're not introducing more
						// problems.
						if ( end($tag_stack) == $tag_inner )
						{
							array_pop($tag_stack);
						}
					}
					else if ($tag[strlen($tag) - 2] == '/'
							|| in_array(strtolower($tag_inner),$unpaired_tags) )
					{
						// Self-closing or unpaired tag
						$output .= $tag;
					}
					else // Opening tag.
					{
						$output .= $tag;
						$tag_stack[] = $tag_inner; // push tag onto the stack
					}
				}

				// Continue after the tag we just found
				$position = $tag_position + strlen($tag);
			}

			// Print any remaining text after the last tag, if there's room.
			if ($output_length < $max_length && $position < strlen($html))
			{
				$output .= mb_substr($html, $position, $max_length - $output_length);
			}
			
			$truncated = strlen($html)-$position > $max_length - $output_length;

			// add terminator if it was truncated in loop or just above here
			if ( $truncated )
				$output .= $indicator;

			// Close any open tags
			while (!empty($tag_stack))
				$output .= '</'.array_pop($tag_stack).'>';

			return $output;
		}

	}

?>