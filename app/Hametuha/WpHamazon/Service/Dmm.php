<?php

namespace Hametuha\WpHamazon\Service;


use Hametuha\WpHamazon\Pattern\AbstractService;

/**
 * Dmm Class
 *
 * @package hamazon
 */
class Dmm extends AbstractService {

	public $name = 'dmm';

	public $title = 'DMM';

 	public $endpoint = 'https://api.dmm.com/affiliate/v3/';

 	protected $per_page = 20;

	/**
	 * Get default options
	 *
	 * @return array
	 */
	protected function get_option_names() {
		return [
			[
				'key' => 'dmm_affiliate_id',
				'label' => __( 'Affiliate ID', 'hamazon' ),
				'description' => sprintf( __( 'You can get %1$s <a href="%2$s" target="_blank">here</a>.', 'hamazon' ), __( 'Affiliate ID', 'hamazon' ), 'https://affiliate.dmm.com/account/index/' ),
			],
			[
				'key'   => 'dmm_api_id',
				'label' => __( 'API ID', 'hamazon' ),
				'description' => sprintf( __( 'You can get %1$s <a href="%2$s" target="_blank">here</a>.', 'hamazon' ), __( 'API ID', 'hamazon' ), 'https://affiliate.dmm.com/api/id_confirm/' ),
			],
		];
	}

	public function short_code_setting() {
		return [
			'dmm' => [
				[
					'label' => __( 'Product ID', 'hamazon' ),
					'attr' => 'id',
					'type' => 'text',
				],
				[
					'label' => __( 'Site', 'hamazon' ),
					'attr' => 'site',
					'type' => 'select',
					'options' => [
						'DMM.com' => __( 'General', 'hamazon' ),
						'DMM.R18' => __( 'Adult', 'hamazon' ),
					],
					'default' => 'DMM.com',
				],
				[
					'label' => __( 'Floor', 'hamazon' ),
					'attr' => 'floor',
					'type' => 'text',
				]
			]
		];
	}

	public function short_code_callback( $short_code_name, array $attributes = [], $content = '' ) {
		switch ( $short_code_name ) {
			case 'dmm':
				$item = $this->find_product( $attributes['id'], $attributes['site'], $attributes['floor'] );
				if ( is_wp_error( $item ) ) {
					return $item;
				}
				$out = hamazon_template( 'dmm', 'single', [
					'item'    => $item,
					'content' => $content,
					'price'   => $this->format_price( $item->prices->price ),
				] );
				/**
				 * wp_hamazon_dmm
				 *
				 * Filter output of amazon
				 *
				 * @param string $html
				 * @param \stdClass $item
                 * @param string $content
				 * @return string
				 */
                $out = apply_filters( 'wp_hamazon_dmm', $out, $item, $content );
				return $out;
				break;
			default:
				return '';
				break;
		}
	}

	/**
	 * Detect if this service is valid.
	 *
	 * @return bool
	 */
	public function is_valid() {
		return $this->get_option( 'dmm_affiliate_id' ) && $this->get_option( 'dmm_api_id' );
	}

	/**
	 * Filter data passed to react.
	 *
	 * @param $data
	 * @return array|\stdClass
	 */
	protected function filter_data( $data ) {
		return [
			'options' => array_map( function( $key, $value ) {
				return [
					'key' => $key,
					'label' => $value,
				];
			}, [ 'DMM.com', 'DMM.R18' ], [ __( 'General', 'hamazon' ), __( 'Adult', 'hamazon' ) ] ),
		];
	}

	/**
	 * Get rest arguments.
	 *
	 * @return array
	 */
	public function get_rest_arguments() {
		return [
			'keyword' => [
				'required' => true,
				'description' => __( 'Search keyword.', 'hamazon' ),
				'validate_callback' => function( $var ) {
					return ! empty( $var );
				},
			],
			'page' => [
				'default' => 1,
				'description' => __( 'Specified page.', 'hamazon' ),
				'validate_callback' => function( $var ) {
					return is_numeric( $var ) && ( 0 < $var );
				},
			],
			'site' => [
				'default' => 'DMM.com',
				'required' => true,
				'description' => __( 'Site to search. DMM.com or DMM.R18', 'hamazon' ),
				'validation_callback' => function( $var ) {
					return false !== array_search( $var, [ 'DMM.com', 'DMM.R18' ] );
				}
			],
		];
	}

	/**
	 * Handle Amazon search request
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \SimpleXMLElement|\WP_Error|\WP_REST_Response
	 */
	public function handle_rest_request( \WP_REST_Request $request ) {
		$search_result = $this->make_request( 'ItemList', [
			'site' => $request['site'],
			'keyword' => $request['keyword'],
			'offset' => 1 + ( ( max( 1, $request['page'])  - 1 ) * $this->per_page ),
		] );
		if ( is_wp_error( $search_result ) ) {
			$search_result->add_data( [
				'response' => 500,
			] );
			return $search_result;
		}
		$site = $request['site'];
		$site = 'DMM.R18' === $request['site'] ? $request['site'] : 'DMM.com';
		return new \WP_REST_Response( [
			'total_page' => ceil( $search_result->result->total_count / $this->per_page ),
			'total_result' => (int) $search_result->result->total_count,
			'items' => array_map( function( $item ) use ( $site ) {
				return [
					'title' => $item->title,
                    'site'  => $site,
					'category' => $item->category_name,
					'asin' => $item->content_id,
					'floor' => $item->floor_code,
					'service' => $item->service_code,
					'price' => $this->format_price( $item->prices->price ),
					'attributes' => $item->iteminfo,
					'image' => isset( $item->imageURL->small ) ? $item->imageURL->small : hamazon_no_image(),
					'url' => $item->affiliateURL,
				];
			}, $search_result->result->items ),
		] );
	}

	/**
     * Format price
     *
	 * @param string $price
	 * @return string
	 */
	protected function format_price( $price ) {
		return preg_replace_callback( '#(\d+)(〜?)#u', function( $matches ) {
			return '¥' . number_format( $matches[1] ) . $matches[2];
		}, $price );
    }


	/**
	 * Get request from DMM
	 *
	 * @param string $api
	 * @param array $args
	 * @return array|mixed|object|\WP_Error
	 */
	public function make_request( $api, $args = [] ) {
		$params = array_merge([
			'api_id' => $this->get_option( 'dmm_api_id' ),
			'affiliate_id' => $this->get_option( 'dmm_affiliate_id' ),
		], $args);
		$url = add_query_arg( $params, $this->endpoint . $api );
		// Make Request
		$default_time_out = apply_filters( 'hamazon_default_timeout', 10, $params, 'dmm' );
		$response = wp_remote_get( $url, [
			'timeout' => $default_time_out,
		] );
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$result = json_decode( $response['body'] );
		if ( ! $result ) {
			return new \WP_Error( 500, __( 'DMM API returns bad response.', 'hamazon' ) );
		}
		return $result;
	}

	/**
	 * Get item information
	 *
	 * @param string $id
	 * @param string $site
	 * @param string $floor
	 * @param bool $cache
	 * @return array|mixed|object|\WP_Error
	 */
	public function find_product( $id, $site = 'DMM.com', $floor = '', $cache = true ) {
		$args = [
			'site'  => $site,
//			'floor' => $floor,
			'cid'   => $id,
			'hits'  => 1,
		];
		$key = "hamazon_dmm_{$id}";
		if ( $cache && ( false !== ( $transient = get_transient( $key ) ) ) ) {
			return $transient;
		}
		$result = $this->make_request( 'ItemList', $args );
		if ( is_wp_error( $result ) ) {
			return $result;
		} elseif ( ! $result->result->items ) {
		    return new \WP_Error( '404', __( 'Sorry, but item not found.' ) );
		} else {
			$item = $result->result->items[0];
			set_transient( $key, $item, 60 * 60 * 24 );
			return $item;
		}
	}

	/**
     * Get attribute CSV
     *
	 * @param array  $attributes
	 * @param string $glue
	 * @return string
	 */
    private function attribute_to_csv( $attributes, $glue = ', ' ) {
	    return implode( $glue, array_map( function( $attribute ) {
	        return $attribute->name;
        }, $attributes ) );
    }

}
