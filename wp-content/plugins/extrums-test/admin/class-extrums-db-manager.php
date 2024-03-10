<?php
/**
 *
 * @package Extrums_Test/admin
 * @author  Roman Peniaz <roman.peniaz@gmail.com>
 */
class Extrums_DB_Manager {

    protected $column;
    protected $old_keyword;
    protected $new_keyword;
    protected $posts_ids;

	public function query_posts() {
		global $wpdb;

		$keyword = sanitize_text_field( $_POST['keyword'] ?? '' );

		if ( ! $keyword ) {
			return;
		}

		$seo_table_name = $wpdb->prefix . 'yoast_indexable';
		$seo_table_exists = $wpdb->get_var( "SHOW TABLES LIKE '$seo_table_name'" ) !== null;

		$select_clause = 'p.ID, p.post_title, p.post_content';
		$join_clause = '';
		if ( $seo_table_exists ) {
			$select_clause .= ', y.title, y.description';
			$join_clause = "LEFT JOIN $seo_table_name AS y ON p.ID = y.object_id";
		}

		$query = "SELECT DISTINCT $select_clause
			FROM {$wpdb->prefix}posts AS p
			$join_clause
			WHERE p.post_type = %s
			AND (
				p.post_title LIKE %s
				OR p.post_content LIKE %s";

		if ( $seo_table_exists ) {
			$query .= " OR y.title LIKE %s OR y.description LIKE %s";
		}

		$query .= " )";

		$like_keyword = '%' . $wpdb->esc_like( $keyword ) . '%';
		$args = [
			'post',
			$like_keyword,
			$like_keyword
		];
		if ( $seo_table_exists ) {
			$args[] = $like_keyword;
			$args[] = $like_keyword;
		}

		$query = $wpdb->prepare( $query, $args );
		$posts = $wpdb->get_results( $query );
		return wp_send_json( $posts );
	}

	public function update_posts_data() {
		$column_replace = sanitize_text_field( $_POST['column_replace'] ?? '' );
		$this->old_keyword = sanitize_text_field( $_POST['old_keyword'] ?? '' );
		$this->new_keyword = sanitize_text_field( $_POST['new_keyword'] ?? '' );
		$this->posts_ids = sanitize_text_field( $_POST['posts'] ?? '' );

		if ( ! $column_replace || ! $this->old_keyword || ! $this->new_keyword || ! $this->posts_ids ) {
			return wp_send_json_error();
		}

		$columns_map = [
			'title' => 'post_title',
			'content' => 'post_content',
			'meta-title' => '',
			'meta-description' => '',
		];
		$this->column = $columns_map[ $column_replace ];

		$result = $this->update_posts_table_data();
		return wp_send_json_success( $result );
	}

	public function update_posts_table_data() {
		global $wpdb;

		$select_query = $wpdb->prepare(
			"SELECT ID, {$this->column}
				FROM {$wpdb->prefix}posts
				WHERE ID IN ({$this->posts_ids});"
		);
		$posts = $wpdb->get_results( $select_query );

		$updated_posts_data = [];
		foreach ( $posts as $post ) {
			$new_data = str_replace(
				$this->old_keyword,
				$this->new_keyword,
				strtolower( $post->{$this->column} )
			);

			$update_query = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}posts
					SET {$this->column} = %s
					WHERE ID = {$post->ID};",
				$new_data
			);
			$wpdb->query( $update_query );

			$updated_posts_data[ $post->ID ] = $new_data;
		}

		return wp_send_json_success( $updated_posts_data );
	}
		);
		$posts = $wpdb->get_results( $select_query );

		$updated_posts_data = [];
		foreach ( $posts as $post ) {
			$new_data = str_replace(
				$this->old_keyword,
				$this->new_keyword,
				strtolower( $post->{$this->column} )
			);

			$update_query = $wpdb->prepare(
				"UPDATE {$wpdb->prefix}posts
					SET {$this->column} = %s
					WHERE ID = {$post->ID};",
				$new_data
			);
			$wpdb->query( $update_query );

			$updated_posts_data[ $post->ID ] = $new_data;
		}

		return wp_send_json_success( $updated_posts_data );
	}

}