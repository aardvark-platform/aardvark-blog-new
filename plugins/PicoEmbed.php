<?php
   
/**
 * PicoEmbed - Embed youtube videos in wordpress-like shortcode format
 *
 * 
 * @author  Saad Bouteraa <s4ad@github>
 * @link    http://fb.com/sa4db
 * @license http://opensource.org/licenses/MIT
 * @version 0.1
 */
final class PicoEmbed extends AbstractPicoPlugin
{
    /**
     * This plugin is enabled by default?
     *
     * @see AbstractPicoPlugin::$enabled
     * @var boolean
     */
    protected $enabled = true;

    /**
     * This plugin depends on ...
     *
     * @see AbstractPicoPlugin::$dependsOn
     * @var string[]
     */
    protected $dependsOn = array();



    /**
     * Triggered before Pico renders the page
     * @see    Pico::getTwig()
     * @see    Embed::onPageRendered()
     * @param  Twig_Environment &$twig          twig template engine
     * @param  array            &$twigVariables template variables
     * @param  string           &$templateName  file name of the template
     * @return void
     */
    public function onPageRendering(Twig_Environment &$twig, array &$twigVariables, &$templateName)
    {
        

        // Search for Embed shortcodes allover the content
        preg_match_all( '#\[embed *.*?\]#s', $twigVariables['content'], $matches );
        
        // Make sure we found some shortcodes
        if(count($matches[0])>0){
            
            // Get page content
            $new_content = &$twigVariables['content'];

            // Walk through shortcodes one by one
            foreach($matches[0] as $match){

                // Get youtube like and video ID (Ref:http://stackoverflow.com/questions/3717115/regular-expression-for-youtube-links/3726073#3726073)
                preg_match( '#http(?:s)?\:\/\/(?:www\.)?youtu(?:be\.com/watch\?v=|\.be/)([\w\-]+)(&(amp;)?[\w\?=]*)?#s', $match, $embed_link );
                
                // Make sure we found the link ($embed_link[0]) and the ID ($embed_link[1])
                if(count($embed_link)>1){

                    // Generate embeding code
                    $embed_code = '<iframe width="854" height="480" src="https://www.youtube.com/embed/'.$embed_link[1].'" frameborder="0" allowfullscreen></iframe>' ;
                    
                    // Replace embeding code with the shortcode in the content
                    $new_content = preg_replace('#\[embed *.*?\]#s', $embed_code, $new_content,1);
            }

            }

        }
    }

    
}
