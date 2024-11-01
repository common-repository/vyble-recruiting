<?php
function vyble_job_custom_init() {
  $labels = array(
    'name'               => 'Stellen',
    'singular_name'      => 'Stelle',
    'add_new'            => '',
    'add_new_item'       => '',
    'edit_item'          => 'Stelle bearbeiten',
    'new_item'           => '',
    'all_items'          => 'Alle Stellen',
    'view_item'          => 'Stelle ansehen',
    'search_items'       => 'Stelle suchen',
    'not_found'          => 'Keine Stellen gefunden',
    'not_found_in_trash' => 'Keine Stellen gefunden',
    'parent_item_colon'  => '',
    'menu_name'          => 'vyble® Recruiting Stellen'
  );

  $args = array(
    'labels'             => $labels,
    'public'             => true,
    'publicly_queryable' => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
	'show_in_nav_menus'  => true,
    'query_var'          => true,
    'rewrite'            => array( 'slug' => 'job' ),
    'capability_type'    => 'post',
    'has_archive'        => true,
    'hierarchical'       => true,
    'menu_position'      => null,
    'supports'           => array( 'title','page-attributes')
  );

  register_post_type( 'cus_vyblejob', $args );
}
add_action( 'init', 'vyble_job_custom_init' );

// Add the Meta Box 

function vyble_add_job_meta_box() {  
    add_meta_box(  
        'job_meta_box', // $id  
        'Jobdaten', // $title   
        'vyble_show_job_meta_box', // $callback  
        'cus_vyblejob', // $page  
        'normal', // $context  
        'high'); // $priority  
}  
add_action('add_meta_boxes', 'vyble_add_job_meta_box'); 


// Field Array  
$prefix = 'job_';  
$job_meta_fields = array(  
	array(  
        'label' => 'ID bei vyble',  
        'desc'  => '',  
        'id'    => $prefix.'vybleid',  
        'type'  => 'readonly'  
    ),
	array(  
        'label' => 'Status',  
        'desc'  => '',  
        'id'    => $prefix.'status',  
        'type'  => 'readonly'  
    ),
	array(  
        'label' => '',  
        'desc'  => '',  
        'id'    => $prefix.'data',  
        'type'  => 'hiddentextarea'  
    )
);   

function vyble_show_job_meta_box() {  
	global $job_meta_fields;  
	vyble_boxtemplate($job_meta_fields);
}  

function vyble_save_job_meta($post_id) {  
    global $job_meta_fields;  
    vyble_boxsavetemplate($job_meta_fields);  
   
}  
add_action('save_post', 'vyble_save_job_meta');