<?php
/*
Plugin Name: Custom Image Advert
Version: 1.0
Plugin URI: http://www.jamescryer.com
Description: Allows a user to display an advert with a media file
Author: James Cryer
Author URI: http://www.jamescryer.com
*/

class ImageAdvertWidget extends WP_Widget {
    
    public function ImageAdvertWidget() {
        parent::WP_Widget(false, $name = 'Image Advert Widget');
    }

    public function form($instance) {
        $title          = esc_attr($instance['title']);
        $customType     = esc_attr($instance['custom-post-type']);
        $id             = esc_attr($instance['identifier']);
        $media          = esc_attr($instance['media']);

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('custom-post-type'); ?>"><?php _e('Content Type:'); ?>
                <?php $aPostTypes = get_post_types(); ?>
                
                <select class="widefat" id="<?php echo $this->get_field_id('custom-post-type'); ?>" name="<?php echo $this->get_field_name('custom-post-type'); ?>">
                    <?php
                       
                       foreach($aPostTypes as $key => $value) {
                           if(!in_array($key, array('nav_menu_item', 'revision'))) {
                               $obj = get_post_type_object($key);
                               ?><option value="<?php echo $key; ?>" <?php selected($customType, $key); ?>><?php echo $obj->labels->name; ?></option><?php
                           }
                       }
                    ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('identifier'); ?>"><?php _e('Item ID:'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('identifier'); ?>" name="<?php echo $this->get_field_name('identifier'); ?>" type="text" value="<?php echo $id; ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('media'); ?>"><?php _e('Media ID:'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('media'); ?>" name="<?php echo $this->get_field_name('media'); ?>" type="text" value="<?php echo $media; ?>" />
            </label>
        </p>
        <?php
    }

    /**
     * Update a widget values
     *
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
	$instance['title']            = strip_tags($new_instance['title']);
        $instance['custom-post-type'] = strip_tags($new_instance['custom-post-type']);
        $instance['identifier']       = strip_tags($new_instance['identifier']);
        $instance['media']            = strip_tags($new_instance['media']);
        return $instance;
    }

    /**
     * Process the widget and output the content
     * 
     * @param array $args
     * @param array $instance
     * @return string
     */
    public function widget($args, $instance) {
        extract($args);
        $title   = $this->getTitle($instance);
        $content = $this->getContent($instance);
        
        echo sprintf(
            "%s%s%s%s%s%s",
            $before_widget,
                $title ? $before_title : '',
                    $title,
                $title ? $after_title : '',
                $content,
            $after_widget
        );
    }

    /**
     * Returns the HTML for the title of the widget
     * 
     * @param array $instance
     * @return string
     */
    protected function getTitle($instance) {
        return apply_filters('widget_title', $instance['title']);
    }

    /**
     * Generates HTML list of feed items
     *
     * @param array $instance
     * @return string
     */
    protected function getContent($instance) {
        
        $post  = $this->getPostItem($instance['custom-post-type'], $instance['identifier']);
        $media = $this->getMediaItem($instance['media']);
        
        return $this->generateWidgetMarkup($post, $media);
    }
    
    
    /**
     * Returns the post item that will be linked to
     * 
     * @param string $postType
     * @param int $id 
     */
    protected function getPostItem($postType, $id) {
        
        $query = new WP_Query(array(
            'p'         => $id,
            'post_type' => $postType
        ));
        
        if(!$query->have_posts()) {
            return null;
        }
        $posts = $query->get_posts();
        $post  = current($posts);
        
        $aPost = array(
            'title' => get_the_title($post->ID),
            'permalink' => get_permalink($post->ID)
        );
        return $aPost;
    }
    
    /**
     * Returns the media content for a given id
     * 
     * @param int $id 
     */
    protected function getMediaItem($id) {
        $query = new WP_Query(array(
            'p'         => $id,
            'post_type' => 'attachment'
        ));
        
        
        if(!$query->have_posts()) {
            return null;
        }
        $medias = $query->get_posts();
        $media  = current($medias);
        
        $aMedia = array(
            'title' => get_the_title($media->ID),
            'src'   => wp_get_attachment_url($media->ID)
        );
        return $aMedia;
    }
    
    /**
     * Returns the content for the widget
     * 
     * @param array $post
     * @param array $media
     * @return string 
     */
    protected function generateWidgetMarkup($post, $media) {
        
        if($post === null || $media === null) {
            return '';
        }
        
        return sprintf(
           '<div class="image-frame"><a href="%s" title="%s"><img src="%s" alt="%s" /></a></div>',
           $post['permalink'], $post['title'], $media['src'], $media['title']
        );
    }
}
add_action('widgets_init', create_function('', 'return register_widget("ImageAdvertWidget");'));