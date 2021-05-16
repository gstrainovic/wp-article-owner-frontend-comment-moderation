<?php

/**
 * Plugin Name:     WP Article Owner Frontend Comment / GeoDirectory Moderation
 * Plugin URI:      https://www.strainovic-it.ch
 * Description:     The article owner can on frontend approve oder trash unapproved comment or geodirectory rating.
 * Author:          Goran Strainovic
 * Author URI:      https://www.strainovic-it.ch
 * Text Domain:     wp-article-owner-frontend-comment-moderation
 * Domain Path:     /languages
 * Version:         0.1.3
 *
 * @package         WP_Article_Owner_Frontend_Comment_Moderation
 */


function approveComment($id)
{
    $result =  wp_set_comment_status($id, 'approve');
    return $result;
}


function trashComment($id)
{

    function url_origin( $s, $use_forwarded_host = false )
    {
        $ssl      = ( ! empty( $s['HTTPS'] ) && $s['HTTPS'] == 'on' );
        $sp       = strtolower( $s['SERVER_PROTOCOL'] );
        $protocol = substr( $sp, 0, strpos( $sp, '/' ) ) . ( ( $ssl ) ? 's' : '' );
        $port     = $s['SERVER_PORT'];
        $port     = ( ( ! $ssl && $port=='80' ) || ( $ssl && $port=='443' ) ) ? '' : ':'.$port;
        $host     = ( $use_forwarded_host && isset( $s['HTTP_X_FORWARDED_HOST'] ) ) ? $s['HTTP_X_FORWARDED_HOST'] : ( isset( $s['HTTP_HOST'] ) ? $s['HTTP_HOST'] : null );
        $host     = isset( $host ) ? $host : $s['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }
    
    function full_url( $s, $use_forwarded_host = false )
    {
        return url_origin( $s, $use_forwarded_host ) . $s['REQUEST_URI'];
    }
    
    $url = full_url( $_SERVER );

    $newurl = parse_url($url, PHP_URL_SCHEME) . "://" . parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);

    wp_trash_comment($id);
    header("Location: ". $newurl);
    exit();
}


function getApprovalLink($comment)
{
    $id = $comment->comment_ID;
    $class = '<a class="gd_user_action my-1 edit_link btn btn-sm text-white btn-primary"';
    $icon = '<i class="fas fa-check"></i>';
    $link = $class . 'href=?approvecomment=' . $id . '>' . $icon . ' Bewertung freigeben</a>';
    return $link;
}

function getTrashLink($comment)
{
    $id = $comment->comment_ID;
    $class = '<a class="gd_user_action my-1 delete_link btn btn-sm text-white btn-danger"';
    $icon = '<i class="fas fa-trash"></i>';
    $link = $class . 'href=?delcomment=' . $id . '>' . $icon . ' Bewertung löschen</a>';
    return $link;
}

//check if the get variable exists
if (isset($_GET['delcomment'])) {
    trashComment($_GET['delcomment']);
} elseif (isset($_GET['approvecomment'])) {
    approveComment($_GET['approvecomment']);
}


function printModerateLinks($comment)
{
    if (is_single()) {
        echo '<p class="waofcm-moderate-links">' . getApprovalLink($comment) . ' ' . getTrashLink($comment) . '</p>';
    }
}

function commentForApproval()
{

    $comments = get_comments(array(
        'post_id' => get_the_ID(),
        'order' => 'ASC',
        'status' => 'hold'
    ));

    foreach ($comments as $comment) {
        if (get_the_author_meta('ID') == get_current_user_id()) {
            echo '<h2>Bitte Kommentar freigeben oder löschen</h2>';
            echo '<p>Nach der Freigabe können Sie auf das Kommentar auch Antworten</p>';
            $post_rating = geodir_get_comment_rating($comment->comment_ID);
            if ($post_rating > 0) {
                echo '<div class="geodir-review-ratings mb-n2">' . geodir_get_rating_stars($post_rating, $comment->comment_ID) . '</div>';
            }
            echo '<div class="comment-content comment card-body m-0">' . comment_text($comment->comment_ID) . '</div>';
            printModerateLinks($comment);
        }
    }
}

// // register shortcode
add_shortcode('woafcm', 'commentForApproval');
