<?php

/**
 * Plugin Name:     WP Article Owner Frontend Comment Moderation
 * Plugin URI:      https://www.strainovic-it.ch
 * Description:     The article owner can on frontend approve oder trash unapproved comment.
 * Author:          Goran Strainovic
 * Author URI:      https://www.strainovic-it.ch
 * Text Domain:     wp-article-owner-frontend-comment-moderation
 * Domain Path:     /languages
 * Version:         0.1.0
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
    $comment_status = wp_get_comment_status($id);

    switch ($comment_status) {
        case 'approved':
            if (wp_trash_comment($id)) {
                echo ('trashed');
            }
            break;
        case 'unapproved':
            if (wp_trash_comment($id)) {
                echo ('trashed');
            }
            break;
        case 'trash':
            if (wp_untrash_comment($id)) {
                echo ('untrashed');
            }
            break;
        default:
            echo ('error');
    }
}


function getApprovalLink($comment)
{
    $id = $comment->comment_ID;
    $class = 'waofcm-moderate' . "-report";
    $link = '<a href=?approvecomment=' . $id . '>Freigeben</a>';
    return $link;
}

function getTrashLink($comment)
{
    $id = $comment->comment_ID;
    $class = 'waofcm-moderate' . "-trash";
    $link = '<a href=?delcomment=' . $id . '>Löschen</a>';
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
        echo '<p class="waofcm-moderate-links">' . getApprovalLink($comment) . ' | ' . getTrashLink($comment) . '</p>';
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
            echo '<div class="geodir-review-ratings mb-n2">' . geodir_get_rating_stars($post_rating, $comment->comment_ID) . '</div>';
            echo '<div class="comment-content comment card-body m-0">' . comment_text($comment->comment_ID) . '</div>';
            printModerateLinks($comment);
        }
    }
}

// // register shortcode
add_shortcode('woafcm', 'commentForApproval');
