<?php
/*
Plugin Name: Bulk Comment Deleter
Plugin URI: https://plugins.club/wordpress/bulk-comment-deleter/
Description: A plugin to delete SPAM, Unapproved or ALL comments from the database.
Version: 1.0
Author: plugins.club
Author URI: https://plugins.club
*/

// function to add buttons to comments page
function pluginsclub_comment_deleter_buttons()
{
    if (current_user_can("moderate_comments")) {
        $delete_all_unapproved_url = wp_nonce_url(
            admin_url(
                "edit-comments.php?comment_status=moderated&delete_all_unapproved=1"
            ),
            "delete_all_unapproved_comments",
            "delete_all_unapproved_nonce"
        );
        $delete_all_spam_url = wp_nonce_url(
            admin_url(
                "edit-comments.php?comment_status=spam&delete_all_spam=1"
            ),
            "delete_all_spam_comments",
            "delete_all_spam_nonce"
        );
        $delete_all_url = wp_nonce_url(
            admin_url("edit-comments.php?delete_all=1"),
            "delete_all_comments",
            "delete_all_nonce"
        );
        echo '<div class="alignright actions">';
        echo '&nbsp;<a href="' .
            esc_url($delete_all_unapproved_url) .
            '" onclick="return pluginsclubconfirmDeleteAllUnapproved()" class="button-primary">Delete All Unapproved</a>&nbsp;';
        echo '&nbsp;<a href="' .
            esc_url($delete_all_spam_url) .
            '" onclick="return pluginsclubconfirmDeleteAllSpam()" class="button-primary">Bulk Delete SPAM</a>&nbsp;';
        echo '&nbsp;<a href="' .
            esc_url($delete_all_url) .
            '" onclick="return pluginsclubconfirmDeleteAll()" class="button-primary">Delete ALL</a>&nbsp;';
        echo "</div>";
    }
}
add_action("manage_comments_nav", "pluginsclub_comment_deleter_buttons", 0);

function pluginsclub_comment_deleter_js()
{
    if (current_user_can("moderate_comments")) {
        echo '<script>
function pluginsclubconfirmDeleteAllUnapproved() {
if (confirm("Are you sure you want to delete all Unapproved comments?")) {
return true;
}
return false;
}
function pluginsclubconfirmDeleteAllSpam() {
if (confirm("Are you sure you want to delete all SPAM comments?")) {
return true;
}
return false;
}
function pluginsclubconfirmDeleteAll() {
if (confirm("Are you sure you want to delete ALL comments?")) {
return true;
}
return false;
}
</script>';
    }
}
add_action("admin_footer", "pluginsclub_comment_deleter_js");

// function to handle delete requests
function pluginsclub_comment_deleter_handle_request()
{
    if (!current_user_can("moderate_comments")) {
        wp_die("You do not have sufficient permissions to access this page.");
    }
    if (
        isset($_GET["delete_all_unapproved"]) &&
        $_GET["delete_all_unapproved"] == 1
    ) {
        if (
            !isset($_GET["delete_all_unapproved_nonce"]) ||
            !wp_verify_nonce(
                $_GET["delete_all_unapproved_nonce"],
                "delete_all_unapproved_comments"
            )
        ) {
            wp_die("Invalid nonce, please try again.");
        }
        $comments = get_comments(["status" => "hold"]);
        if (count($comments) < 1) {
            wp_die("No unapproved comments found.");
        }
        foreach ($comments as $comment) {
            wp_delete_comment($comment->comment_ID, true);
        }
        wp_safe_redirect(admin_url("edit-comments.php"));
        exit();
    }
    if (isset($_GET["delete_all_spam"]) && $_GET["delete_all_spam"] == 1) {
        if (
            !isset($_GET["delete_all_spam_nonce"]) ||
            !wp_verify_nonce(
                $_GET["delete_all_spam_nonce"],
                "delete_all_spam_comments"
            )
        ) {
            wp_die("Invalid nonce, please try again.");
        }
        $comments = get_comments(["status" => "spam"]);
        if (count($comments) < 1) {
            wp_die("No spam comments found.");
        }
        foreach ($comments as $comment) {
            wp_delete_comment($comment->comment_ID, true);
        }
        wp_safe_redirect(admin_url("edit-comments.php"));
        exit();
    }
    if (isset($_GET["delete_all"]) && $_GET["delete_all"] == 1) {
        if (
            !isset($_GET["delete_all_nonce"]) ||
            !wp_verify_nonce($_GET["delete_all_nonce"], "delete_all_comments")
        ) {
            wp_die("Invalid nonce, please try again.");
        }
        $comments = get_comments();
        if (count($comments) < 1) {
            wp_die("No comments found.");
        }
        foreach ($comments as $comment) {
            wp_delete_comment($comment->comment_ID, true);
        }
        wp_safe_redirect(admin_url("edit-comments.php"));
        exit();
    }
}
add_action("admin_init", "pluginsclub_comment_deleter_handle_request");
