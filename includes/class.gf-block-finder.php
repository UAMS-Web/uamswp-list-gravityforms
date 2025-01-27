<?php
/**
 * Finds pages that contain a particular Gravity Form.
 */
class Gravity_Forms_Block_Finder {
	/**
	 * ID of the Gravity Form to search for.
	 *
	 * @var int
	 */
	private $form_id;

	/**
	 * @param int $form_id ID of the Gravity Form to search for.
	 */
	public function __construct( $form_id ) {
		$this->form_id = (int) $form_id;
	}

	/**
	 * @return array Pages that contain the form. Array is in this format: $post_id => $post_title
	 */
	public function find() {
		return array_reduce( $this->get_all_page_ids(), function( $pages, $page_id ) {
			if ( in_array( $this->form_id, $this->get_form_ids_in_post_content( $page_id ), true ) ) {
				$pages[ $page_id ] = get_the_title( $page_id );
			}
	
			return $pages;
		}, [] );
	}

	/**
	 * @return array Post IDs for all pages.
	 */
	private function get_all_page_ids() {
		return ( new WP_Query( [
			'post_type'              => 'page',
			'posts_per_page'         => 1000,
			'no_found_rows'          => true,
			'fields'                 => 'ids',
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] ) )->get_posts();
	}

	/**
	 * @param  int $page_id Page ID.
	 *
	 * @return array Gravity Form IDs.
	 */
	private function get_form_ids_in_post_content( $page_id ) {
		$content = get_the_content( $page_id );

		return $this->get_block_ids( $this->get_blocks_from_content( parse_blocks( $content ), 'gravityforms/form' ) );
	}

	/**
	 * @param  string $content   Post content.
	 * @param  string $block The block to search for.
	 *
	 * @return array  The blocks found or empty array if none.
	 */
	private function get_blocks_from_content( $blocks, $type ) {
		$matches = [];

		foreach( $blocks as $block ) {
			if( $block['blockName'] === $type )
				$matches[] = $block;

			if( count( $block['innerBlocks'] ) ) {
				$matches = array_merge(
					$matches,
					get_blocks_from_content( $block['innerBlocks'], $type )
				);
			}
		}

		return $matches;
	}

	/**
	 * Extracts IDs from blocks.
	 *
	 * @param string $blocks The blocks to get the IDs from.
	 *
	 * @return array $ids        The block IDs.
	 */
	private function get_block_ids( array $blocks ) {
		return array_reduce( $blocks, function( $ids, $block ) {
			$id = $this->get_block_id( $block );

			if ( $id ) {
				$ids[] = $id;
			}

			return $ids;
		}, [] );
	}

	/**
	 * Extract the 'id' parameter from a block.
	 *
	 * @param  string $block The block.
	 *
	 * @return int The ID, or 0 if none found.
	 */
	private function get_block_id( $block ) {
		$match_found = preg_match( '/formId":"([^"]*)"/', $block, $form_id );

		return $match_found ? (int) $form_id[1] : 0;
	}
}