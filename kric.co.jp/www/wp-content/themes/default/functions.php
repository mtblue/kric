<?php
if ( function_exists('register_sidebar') )
    register_sidebars(4,array(
        'before_widget' => '<div id="%1$s" class="widget">',
        'after_widget' => '</div>',
        'before_title' => '<p class="module_title">',
        'after_title' => '</p>',
    ));
	
	
	//ブログアドレス呼び出し
	//呼び出しショートコード[bloginfo arg="template_url"]
function sc_bloginfo($atts, $content = null) {
    extract(shortcode_atts(array(
                                 'arg' => ''
                                 ), $atts));

    return get_bloginfo($arg);
}

add_shortcode('bloginfo', 'sc_bloginfo');
	//ブログアドレス呼び出し end

	//アドセンス呼び出し
function sc_liste($atts, $content = null) {
        extract(shortcode_atts(array(
                "num" => '5',
                "cat" => ''
        ), $atts));
        global $post;
        $myposts = get_posts('numberposts='.$num.'&order=DESC&orderby=post_date&category='.$cat);
        $retour='<ul>';
        foreach($myposts as $post) :
                setup_postdata($post);
             $retour.='<li><a href="'.get_permalink().'">'.the_title("","",false).'</a></li>';
        endforeach;
        $retour.='</ul> ';
        return $retour;
}
 
add_shortcode("list", "sc_liste");
	//アドセンス end




//続きを読むをアンカーリンクさせない
function remove_more_jump_link($link) { 
$offset = strpos($link, '#more-');
if ($offset) {
$end = strpos($link, '"',$offset);
}
if ($end) {
$link = substr_replace($link, '', $offset, $end-$offset);
}
return $link;
}
add_filter('the_content_more_link', 'remove_more_jump_link');
//続きを読むをアンカーリンクさせない　end




//アイキャッチ画像表示
add_theme_support( 'post-thumbnails' );
set_post_thumbnail_size( 165, 125, true ); 
//アイキャッチ画像表示 end

//親ページを取得
function ps_get_root_page( $cur_page ) {
    if ( $cur_page->post_parent == 0 ) {
        $root_page = $cur_page;
    } else {
        $root_page = ps_get_root_page( get_post( $cur_page->post_parent ) );
    }
    return $root_page;
}
//親ページを取得 end

//ページで抜粋を使用可能に
add_post_type_support( 'page', 'excerpt' );




	//ショートコード作成　本文にマージン
	function mg_bottom10() {
    return '<div class="mgBottom10"></div>';
}
add_shortcode('mg10', 'mg_bottom10');

	function mg_bottom20() {
    return '<div class="mgBottom20"></div>';
}
add_shortcode('mg20', 'mg_bottom20');

function mg_bottom30() {
    return '<div class="mgBottom30"></div>';
}
add_shortcode('mg30', 'mg_bottom30');

	//ショートコード作成　ページトップへ
	function pageTop1() {
    return '<div class="pageTopM" align="right"><a href="#top"><img src="/wp-content/themes/default/images/bt_pageTop.jpg" width="134" height="12" alt="このページのトップへ" /></a></div>';
}
add_shortcode('pageTop', 'pageTop1');

	
	//お問合わせボックス
	function contactbox() {
    return '';
}
add_shortcode('contactBox', 'contactbox');	
	
	
	//続きはこちら　で出力されるソースを編集
function my_content_more_link($output, $more_link_text){
  return '<p class="top_datail"><a href="' . get_permalink()
    . '" title="' . get_the_title() . '">'
    . get_the_title() . 'の詳細はこちら</a>'
    . $more_link_text . '</p>';
}
add_filter('the_content_more_link', 'my_content_more_link', 10, 2);

	//地図
	function map1() {
    return '<iframe width="710" height="350" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.co.jp/maps?f=q&amp;source=s_q&amp;hl=ja&amp;geocode=&amp;q=%E5%A4%A7%E9%98%AA%E5%BA%9C%E5%A4%A7%E9%98%AA%E5%B8%82%E8%A5%BF%E5%8C%BA%E7%AB%8B%E5%A3%B2%E5%A0%801-7-12&amp;aq=&amp;sll=36.5626,136.362305&amp;sspn=59.612189,135.263672&amp;brcurrent=3,0x6000e702ed738d69:0x6a6810efe1e0b76,0,0x6000e702ebc4d7c9:0x59fa76d053880d47&amp;ie=UTF8&amp;hq=&amp;hnear=%E5%A4%A7%E9%98%AA%E5%BA%9C%E5%A4%A7%E9%98%AA%E5%B8%82%E8%A5%BF%E5%8C%BA%E7%AB%8B%E5%A3%B2%E5%A0%80%EF%BC%91%E4%B8%81%E7%9B%AE%EF%BC%97%E2%88%92%EF%BC%91%EF%BC%92&amp;t=m&amp;ll=34.685311,135.495157&amp;spn=0.024702,0.06094&amp;z=14&amp;output=embed"></iframe><br /><small><a href="http://maps.google.co.jp/maps?f=q&amp;source=embed&amp;hl=ja&amp;geocode=&amp;q=%E5%A4%A7%E9%98%AA%E5%BA%9C%E5%A4%A7%E9%98%AA%E5%B8%82%E8%A5%BF%E5%8C%BA%E7%AB%8B%E5%A3%B2%E5%A0%801-7-12&amp;aq=&amp;sll=36.5626,136.362305&amp;sspn=59.612189,135.263672&amp;brcurrent=3,0x6000e702ed738d69:0x6a6810efe1e0b76,0,0x6000e702ebc4d7c9:0x59fa76d053880d47&amp;ie=UTF8&amp;hq=&amp;hnear=%E5%A4%A7%E9%98%AA%E5%BA%9C%E5%A4%A7%E9%98%AA%E5%B8%82%E8%A5%BF%E5%8C%BA%E7%AB%8B%E5%A3%B2%E5%A0%80%EF%BC%91%E4%B8%81%E7%9B%AE%EF%BC%97%E2%88%92%EF%BC%91%EF%BC%92&amp;t=m&amp;ll=34.685311,135.495157&amp;spn=0.024702,0.06094&amp;z=14" style="text-align:left" target="_blank">大きな地図で見る</a></small>';
}
add_shortcode('map', 'map1');	



/* ---------- カスタム投稿タイプを追加 ---------- */
add_action( 'init', 'create_post_type' );

function create_post_type() {

  register_post_type(
    'kric-media',
    array(
      'label' => 'KRICメディア',
      'public' => true,
      'has_archive' => true,
      'show_in_rest' => true,
      'menu_position' => 5,
      'supports' => array(
        'title',
        'editor',
        'thumbnail',
        'revisions',
      ),
    )
  );

  register_taxonomy(
    'kric-media',
   array(
      'label' => 'KRICメディア',
      'hierarchical' => true,
      'public' => true,
      'show_in_rest' => true,
    )
  );



}

// 親ページか子ページか条件分岐
function is_subpage() {
global $post; // $post には現在の固定ページの情報があります
if ( is_page() && $post->post_parent ) { // 現在の固定ページが親ページを持つかどうかをチェックします
$parentID = $post->post_parent; // 親ページの ID を取得します
return $parentID; // 親ページの ID を返します
} else { // 親ページを持たないので…
return false; // …false を返します
};
};

function custom_admin_footer() {}
add_filter('admin_footer_text', 'custom_admin_footer');

function login_logo() { echo "<script>(function($){ $(function(){ $('.login h1').html('')})})(jQuery)</script>"; }
add_action('login_head', 'login_logo');
?>