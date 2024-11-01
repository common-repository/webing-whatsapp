<?php
/**
 * admin.php
 *
 * @package     Webing\WhatsApp\Core
 * @subpackage  Admin
 * @author      Webing <info@webing.co.il>
 * @link        https://www.webing.co.il
 * @version     1.0.0
 */

namespace Webing\WhatsApp\Core;

// Block direct access to the file via url.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class Admin
 *
 * @package Webing\WhatsApp\Core
 */
class Admin {

	/**
	 * @var string
	 */
	const PAGE_SLUG = 'webing-whatsapp';

	/**
	 * @var string
	 */
	const FIELD_PREFIX = 'wgwa_';

	/**
	 * @var array
	 */
	private $options = [];


	/**
	 * Admin constructor.
	 */
	public function __construct() {

		add_action( 'admin_menu', [ $this, 'add_settings_page_to_menu' ] );
		add_action( 'admin_init', [ $this, 'register_plugin_settings' ] );

		$this->options = [
			'phone_code'      => get_option( self::FIELD_PREFIX . 'phone_code' ),
			'phone_number'    => get_option( self::FIELD_PREFIX . 'phone_number' ),
			'message'         => get_option( self::FIELD_PREFIX . 'message' ),
			'button_text'     => get_option( self::FIELD_PREFIX . 'button_text' ),
			'button_position' => get_option( self::FIELD_PREFIX . 'button_position' ),
		];

	}


	/**
	 * Register settings page to options menu.
	 */
	public function add_settings_page_to_menu() {

		add_options_page( __( 'WhatsApp Button', 'webing-whatsapp' ), __( 'WhatsApp Button', 'webing-whatsapp' ), 'manage_options', self::PAGE_SLUG, [
			$this,
			'settings_page_html',
		] );

	}


	/**
	 * Display plugin settings page.
	 */
	public function settings_page_html() {

		if ( ! current_user_can( 'manage_options' ) ) {

			$notice = esc_html__( 'Oops... it\'s seems like you don\'t have the permission to access this page.', 'webing-whatsapp' );

			wp_die( "<div class='error'><p>{$notice}</p></div>" );

		}

		require_once( WGWA_PLUGIN_PATH . 'templates/admin-page.php' );

	}


	/**
	 * Register plugin sections and settings.
	 */
	public function register_plugin_settings() {

		// Register Sections
		add_settings_section( self::PAGE_SLUG . '-message-configuration', __( 'Message Configuration', 'webing-whatsapp' ), null, self::PAGE_SLUG );
		add_settings_section( self::PAGE_SLUG . '-button-design', __( 'Button Design', 'webing-whatsapp' ), null, self::PAGE_SLUG );

		// Register Settings
		register_setting( self::PAGE_SLUG, self::FIELD_PREFIX . 'phone_code' );
		register_setting( self::PAGE_SLUG, self::FIELD_PREFIX . 'phone_number' );
		register_setting( self::PAGE_SLUG, self::FIELD_PREFIX . 'message' );
		register_setting( self::PAGE_SLUG, self::FIELD_PREFIX . 'button_text' );
		register_setting( self::PAGE_SLUG, self::FIELD_PREFIX . 'button_position' );

		// Register Fields
		add_settings_field( self::FIELD_PREFIX . 'phone_number', __( 'Recipient Number', 'webing-whatsapp' ), [
			$this,
			'setting_field_input_html',
		], self::PAGE_SLUG, self::PAGE_SLUG . '-message-configuration', [
			'label_for'      => self::FIELD_PREFIX . 'phone_number',
			'type'           => 'text',
			'max'            => '16',
			'include_prefix' => true,
			'description'    => esc_html__( 'A standard phone number must be entered. For example: 0501234567 or 050-1234567.', 'webing-whatsapp' ),
		] );

		add_settings_field( self::FIELD_PREFIX . 'message', __( 'Message', 'webing-whatsapp' ), [
			$this,
			'setting_field_textarea_html',
		], self::PAGE_SLUG, self::PAGE_SLUG . '-message-configuration', [
			'label_for'   => self::FIELD_PREFIX . 'message',
			'description' => esc_html__( 'Here you can enter the automatic message that will be sent to the number you entered.', 'webing-whatsapp' ),
		] );

		add_settings_field( self::FIELD_PREFIX . 'button_text', __( 'Button Text', 'webing-whatsapp' ), [
			$this,
			'setting_field_input_html',
		], self::PAGE_SLUG, self::PAGE_SLUG . '-button-design', [
			'label_for'   => self::FIELD_PREFIX . 'button_text',
			'type'        => 'text',
			'max'         => '70',
			'description' => esc_html__( 'Here you can insert a text to display next to the WhatsApp icon - for example, an action-oriented sentence.', 'webing-whatsapp' ),
		] );

		add_settings_field( self::FIELD_PREFIX . 'button_position', __( 'Button Position', 'webing-whatsapp' ), [
			$this,
			'setting_field_radio_html',
		], self::PAGE_SLUG, self::PAGE_SLUG . '-button-design', [
			'label'       => __( 'Button Position', 'webing-whatsapp' ),
			'label_for'   => self::FIELD_PREFIX . 'button_position',
			'options'     => [
				'align_left'  => __( 'Left', 'webing-whatsapp' ),
				'align_right' => __( 'Right', 'webing-whatsapp' ),
			],
			'description' => esc_html__( 'Here you can specify where to display the WhatsApp icon - on the right side or on the left side of the site.', 'webing-whatsapp' ),
		] );

	}


	/**
	 * Output input field HTML.
	 *
	 * @param $args
	 */
	public function setting_field_input_html( $args ) {

		$value      = get_option( $args[ 'label_for' ] );
		$max_length = ( isset( $args[ 'max' ] ) ) ? $args[ 'max' ] : 9999;

		echo "<div class='field-wrapper'>";

		if ( isset( $args[ 'include_prefix' ] ) && $args[ 'include_prefix' ] ) {

			$codes      = $this->get_phone_country_code_list();
			$code_value = get_option( self::FIELD_PREFIX . 'phone_code' );

			printf( "<select class='phone_prefix' name='%s' id='%s'>", self::FIELD_PREFIX . 'phone_code', self::FIELD_PREFIX . 'phone_code' );

			foreach ( $codes as $code ) {

				printf( "<option value='%s' %s>+%s</option>", esc_attr( $code ), selected( $code_value, $code, false ), esc_html( $code ) );

			}

			echo "</select>";

		}

		printf( "<input type='%s' class='%s' name='%s' id='%s' value='%s' maxlength='%s'>", $args[ 'type' ], 'regular-text', $args[ 'label_for' ], $args[ 'label_for' ], $value, $max_length );

		echo "</div>";

		if ( isset( $args[ 'description' ] ) && ! empty( $args[ 'description' ] ) ) {
			printf( "<p class='description'>%s</p>", $args[ 'description' ] );
		}

	}


	/**
	 * Output textarea field HTML.
	 *
	 * @param $args
	 */
	public function setting_field_textarea_html( $args ) {

		$value = get_option( $args[ 'label_for' ] );

		printf( "<textarea name='%s' class='%s' rows='10'>%s</textarea>", $args[ 'label_for' ], 'regular-text', $value );

		if ( isset( $args[ 'description' ] ) && ! empty( $args[ 'description' ] ) ) {
			printf( "<p class='description'>%s</p>", $args[ 'description' ] );
		}

	}


	/**
	 * Output radio field HTML.
	 *
	 * @param $args
	 */
	public function setting_field_radio_html( $args ) {

		$value = get_option( $args[ 'label_for' ] );

		echo "<fieldset>";
		echo "	<legend class='screen-reader-text'><span>{$args[ 'label' ]}</span></legend>";

		foreach ( $args[ 'options' ] as $class => $title ) {

			printf( '<label><input type="radio" name="%s" value="%s" %s> <span>%s</span></label><br>', $args[ 'label_for' ], $class, checked( $value, $class, false ), $title );

		}

		echo "</fieldset>";

		if ( isset( $args[ 'description' ] ) && ! empty( $args[ 'description' ] ) ) {
			printf( "<p class='description'>%s</p>", $args[ 'description' ] );
		}

	}


	/**
	 * Retrieve list of phone country codes.
	 *
	 * @return array
	 */
	public function get_phone_country_code_list() {

		return [
			0   => '972',
			1   => '93',
			2   => '355',
			3   => '213',
			4   => '684',
			5   => '376',
			6   => '244',
			7   => '809',
			8   => '268',
			9   => '54',
			10  => '374',
			11  => '297',
			12  => '247',
			13  => '61',
			14  => '672',
			15  => '43',
			16  => '994',
			17  => '242',
			18  => '246',
			19  => '973',
			20  => '880',
			21  => '375',
			22  => '32',
			23  => '501',
			24  => '229',
			26  => '975',
			27  => '284',
			28  => '591',
			29  => '387',
			30  => '267',
			31  => '55',
			33  => '673',
			34  => '359',
			35  => '226',
			36  => '257',
			37  => '855',
			38  => '237',
			39  => '1',
			40  => '238',
			42  => '345',
			44  => '236',
			45  => '235',
			46  => '56',
			47  => '86',
			48  => '886',
			49  => '57',
			50  => '269',
			52  => '682',
			53  => '506',
			54  => '385',
			55  => '53',
			56  => '357',
			57  => '420',
			58  => '45',
			60  => '767',
			62  => '253',
			63  => '593',
			64  => '20',
			65  => '503',
			66  => '240',
			67  => '291',
			68  => '372',
			69  => '251',
			70  => '500',
			71  => '298',
			72  => '679',
			73  => '358',
			74  => '33',
			75  => '596',
			76  => '594',
			77  => '241',
			78  => '220',
			79  => '995',
			80  => '49',
			81  => '233',
			82  => '350',
			83  => '30',
			84  => '299',
			85  => '473',
			86  => '671',
			87  => '502',
			88  => '224',
			89  => '245',
			90  => '592',
			91  => '509',
			92  => '504',
			93  => '852',
			94  => '36',
			95  => '354',
			96  => '91',
			97  => '62',
			98  => '98',
			99  => '964',
			100 => '353',
			101 => '39',
			102 => '225',
			103 => '876',
			104 => '81',
			105 => '962',
			106 => '7',
			107 => '254',
			109 => '686',
			110 => '82',
			111 => '850',
			112 => '965',
			113 => '996',
			114 => '371',
			115 => '856',
			116 => '961',
			117 => '266',
			118 => '231',
			119 => '370',
			120 => '218',
			121 => '423',
			122 => '352',
			123 => '853',
			124 => '389',
			125 => '261',
			126 => '265',
			127 => '60',
			128 => '960',
			129 => '223',
			130 => '356',
			131 => '692',
			133 => '222',
			134 => '230',
			136 => '52',
			137 => '691',
			138 => '373',
			140 => '976',
			142 => '212',
			143 => '258',
			144 => '95',
			145 => '264',
			146 => '674',
			147 => '977',
			148 => '31',
			149 => '599',
			150 => '869',
			151 => '687',
			152 => '64',
			153 => '505',
			154 => '227',
			155 => '234',
			156 => '683',
			158 => '1 670',
			159 => '47',
			160 => '968',
			161 => '92',
			162 => '680',
			163 => '507',
			164 => '675',
			165 => '595',
			166 => '51',
			167 => '63',
			168 => '48',
			169 => '351',
			170 => '1 787',
			171 => '974',
			172 => '262',
			173 => '40',
			175 => '250',
			176 => '670',
			177 => '378',
			178 => '239',
			179 => '966',
			180 => '221',
			181 => '381',
			182 => '248',
			183 => '232',
			184 => '65',
			185 => '421',
			186 => '386',
			187 => '677',
			188 => '252',
			189 => '27',
			190 => '34',
			191 => '94',
			192 => '290',
			194 => '508',
			195 => '249',
			196 => '597',
			198 => '46',
			199 => '41',
			200 => '963',
			201 => '689',
			204 => '255',
			205 => '66',
			206 => '228',
			207 => '690',
			208 => '676',
			209 => '1 868',
			210 => '216',
			211 => '90',
			212 => '993',
			213 => '688',
			214 => '256',
			215 => '380',
			216 => '971',
			217 => '44',
			218 => '598',
			221 => '678',
			223 => '58',
			224 => '84',
			225 => '1 340',
			226 => '681',
			227 => '685',
			229 => '967',
			231 => '243',
			232 => '260',
			233 => '263',
		];

	}


	/**
	 * Retrieves plugin options.
	 *
	 * @param null $field
	 *
	 * @return array|bool|mixed
	 */
	public function get_settings( $field = null ) {

		if ( is_null( $field ) ) {
			return $this->options;
		}

		return ( isset( $this->options[ $field ] ) ) ? $this->options[ $field ] : false;

	}

}