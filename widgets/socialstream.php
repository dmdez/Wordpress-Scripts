<?php 
    class SocialStream_Widget extends WP_Widget {
    
        function SocialStream_Widget() {
            parent::WP_Widget(false, 'Social Stream');
        }
        
        function form($instance) {
			$html_form = '
                <p><label>Title: <input type="text" class="widefat" name="%s" value="%s" /></label></p>
                <p><label>Facebook: <input type="text" class="widefat" name="%s" value="%s" /></label></p>
                <p><label>Twitter: <input type="text" class="widefat" name="%s" value="%s" /></label></p>
                <p><label>YouTube: <input type="text" class="widefat" name="%s" value="%s" /></label></p>
            ';
            
			printf(
				$html_form, 
				$this->get_field_name('title'), 
				$instance['title'],
        
				$this->get_field_name('ffuser'), 
				$instance['ffuser'],
        
        $this->get_field_name('tfuser'), 
				$instance['tfuser'],
        
        $this->get_field_name('yfuser'), 
				$instance['yfuser']
			);
        }
        
        function update($new_instance, $old_instance) {
            // processes widget options to be saved
			      $instance = $old_instance;
			      $instance['title'] = strip_tags($new_instance['title']);
            $instance['ffuser'] = strip_tags($new_instance['ffuser']);
            $instance['tfuser'] = strip_tags($new_instance['tfuser']);
            $instance['yfuser'] = strip_tags($new_instance['yfuser']);
            return $instance;
        }
        
        function widget($args, $instance) {
            extract($args);
            
            $title = apply_filters('widget_title', $instance['title']);
			      if(!empty($title)) {
				      $title = $before_title.$title.$after_title;
			      }
            
            $feed = new Feed;	
            $feed->twitter = $instance['tfuser'];
            $feed->facebook = $instance['ffuser'];
            $feed->youtube = $instance['yfuser'];
            $feed->count = 15;	
            $items = $feed->json();
            $html = "";
            foreach ( $items as $item ) {
	            $html .= sprintf('<li class="%s">%s <a href="%s" class="link" target="_blank">%s</a> <span class="time">%s ago</span></li>', 
                          $item['type'], 
                          $item['short_text'], 
                          $item['link'], 
                          $item['short_link'], 
                          $item['ago']
                       );
            }
            
            printf('%s%s<ul>%s</ul>%s', $before_widget, $title, $html, $after_widget);
        }
        
    }
    register_widget('SocialStream_Widget');
?>