<?php
// UW Filters
//
// These are filters the UW 2014 theme adds and globally uses.

class UW_Filters
{

  private $REPLACE_TEMPLATE_CLASS = array( 'templatestemplate-', '-php' );

  function __construct()
  {
    // Custom UW Filters
    add_filter( 'italics', array( $this, 'italicize') );
    add_filter( 'abbreviation', array( $this, 'abbreviate') );

    // Global filters
    // Allow shortcodes in text widgets and excerpts
    add_filter( 'widget_text', 'do_shortcode' );
    add_filter( 'the_excerpt', 'do_shortcode' );

    // Add a better named template class to the
    add_filter( 'body_class', array( $this, 'better_template_name_body_class' ) );

    // Filters the category widget dropdown menu
    add_filter( 'widget_categories_dropdown_args', array( $this, 'custom_widget_classes' ) );

    // Modify the more text link
    add_filter('excerpt_more', '__return_false' );
    add_filter('the_excerpt', array($this, 'excerpt_more_override'));

    //remove auto-paragraphs from shortcodes and keep it for content
    remove_filter( 'the_content', 'wpautop' );
    add_filter( 'the_content', 'wpautop' , 99);
    add_filter( 'the_content', 'shortcode_unautop',100 );

    // Add PDF filter to media library
    add_filter( 'post_mime_types', array( $this, 'modify_post_mime_types' ) );

    // Multisite filters
    if ( is_multisite() )
    {
      // Add the site title to the body class
      add_filter( 'body_class', array( $this, 'add_site_title_body_class' ) );
    }

    // Custom except ending
    add_filter( 'excerpt_more', array( $this, 'custom_excerpt_more' ) );

  }

  function custom_excerpt_more( $more ) {
    return '...';
  }

  function modify_post_mime_types( $post_mime_types ) {

    // select the mime type, here: 'application/pdf'
    $post_mime_types['application/pdf'] = array( __( 'PDF' ), __( 'Manage PDFs' ), _n_noop( 'PDF <span class="count">(%s)</span>', 'PDFs <span class="count">(%s)</span>' ) );

    return $post_mime_types;

  }

  function add_site_title_body_class( $classes )
  {
    $site = array_filter( explode( '/', get_blog_details( get_current_blog_id() )->path ) );
    array_shift( $site );

    if ( is_multisite() )
        $classes[] = 'site-'. sanitize_html_class( implode( '-', $site ) );

    return $classes;

  }

  function better_template_name_body_class( $classes )
  {
    if ( is_page_template() )
    {
      foreach( $classes as $index=>$class )
      {
        $classes[ $index ] = str_replace( $this->REPLACE_TEMPLATE_CLASS, '', $class );
      }
    }
    return $classes;
  }

  function custom_widget_classes( $args )
  {
    $args['class' ] = 'uw-select uw-select-wp';
    return $args;
  }

  /**
   * Filter that returns the same content but with words from the
   *  Italicized words setting in italics.
   */
  function italicize( $content )
  {

    $words = explode(' ', get_option('italicized_words') );

    if ( 1 == sizeof( $words ) )
    {
      echo $content;
      return;
    }

    $words = array_filter(array_map('trim', $words));

    $regex = '/\b(' . implode("|", $words) . ')\b/i';

    $new_content = preg_replace($regex, "<em>$1</em>", $content);

    if ( in_array('&', $words) )
      $new_content = str_replace( array( ' &#038; ', ' & ', ' &amp; ' ) , ' <em>&</em> ', $new_content);

    echo $new_content;

  }


  /**
   * Returns the abbreviated title if it exists, otherwise the site title
   */
  function abbreviate( $title )
  {
     $abbr = get_option('abbreviation');

     if (! $abbr )
       return $title;

     return $abbr;
  }

   // Adds a more link button to the end of the excerpt
  function excerpt_more_override($excerpt)
  {
    return $excerpt . '<div><a class="more" href="' . get_permalink() . '">Read more</a></div>';
  }
}
