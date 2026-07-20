<?php
/**
 * Admin_Page class.
 *
 * @package Nilambar\AIGround
 */

namespace Nilambar\AIGround\Admin;

use Nilambar\AIGround\Utils\AI_Utils;
use Nilambar\AIGround\Utils\Case_Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin page.
 *
 * @since 1.0.0
 */
class Admin_Page {

	/**
	 * Registers hooks.
	 *
	 * @since 1.0.0
	 */
	public function init(): void {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'wp_ajax_aiground_prompt', [ $this, 'handle_prompt' ] );
	}

	/**
	 * Adds admin menu.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu(): void {
		$hook_suffix = add_management_page(
			__( 'AIGround', 'aiground' ),
			__( 'AIGround', 'aiground' ),
			'manage_options',
			'aiground-tools',
			[ $this, 'render_page' ]
		);

		add_action( "load-{$hook_suffix}", [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Enqueues page assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets(): void {
		$data = $this->get_page_data();

		wp_enqueue_style( 'aiground', plugins_url( 'assets/main.css', AIGROUND_FILE ), [], '1.0.0' );
		wp_enqueue_script( 'aiground', plugins_url( 'assets/main.js', AIGROUND_FILE ), [], '1.0.0', true );

		wp_localize_script(
			'aiground',
			'aigroundData',
			[
				'providerModels' => $data['providers'],
				'testCases'      => $data['test_cases'],
				'nonce'          => $data['nonce'],
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'errorGeneric'   => __( 'An error occurred.', 'aiground' ),
				'requestFailed'  => __( 'Request failed.', 'aiground' ),
			]
		);
	}

	/**
	 * Gets page data.
	 *
	 * @since 1.0.0
	 *
	 * @return array Page data.
	 */
	private function get_page_data(): array {
		static $data = null;

		if ( null !== $data ) {
			return $data;
		}

		$data = [
			'providers'  => AI_Utils::get_providers_with_models(),
			'nonce'      => wp_create_nonce( 'aiground_prompt' ),
			'test_cases' => Case_Utils::get_test_cases(),
		];

		return $data;
	}

	/**
	 * Renders the admin page.
	 *
	 * @since 1.0.0
	 */
	public function render_page(): void {
		$page_data  = $this->get_page_data();
		$providers  = $page_data['providers'];
		$test_cases = $page_data['test_cases'];
		$default_id = array_key_first( $test_cases );
		$default    = $test_cases[ $default_id ];
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<div id="aiground-form">
				<?php if ( empty( $providers ) ) : ?>
					<p><?php esc_html_e( 'No AI providers configured. Please configure a provider under Settings > Connectors.', 'aiground' ); ?></p>
				<?php else : ?>
					<p class="aiground-row">
						<span class="aiground-field">
							<label for="aiground-provider"><?php esc_html_e( 'Provider', 'aiground' ); ?></label>
							<select id="aiground-provider">
								<?php foreach ( $providers as $id => $data ) : ?>
									<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $data['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</span>
						<span class="aiground-field">
							<label for="aiground-model"><?php esc_html_e( 'Model', 'aiground' ); ?></label>
							<select id="aiground-model"></select>
						</span>
					</p>
					<p>
						<label><?php esc_html_e( 'Test Case', 'aiground' ); ?></label>
						<div id="aiground-testcase" class="aiground-pills">
							<?php foreach ( $test_cases as $id => $test_case ) : ?>
								<button type="button" class="aiground-pill<?php echo $id === $default_id ? ' is-active' : ''; ?>" data-testcase="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $test_case['label'] ); ?></button>
							<?php endforeach; ?>
						</div>
					</p>
					<p class="aiground-row">
						<span class="aiground-field">
							<label for="aiground-system-prompt"><?php esc_html_e( 'System Prompt', 'aiground' ); ?></label>
							<textarea id="aiground-system-prompt"><?php echo esc_textarea( $default['system'] ); ?></textarea>
						</span>
						<span class="aiground-field">
							<label for="aiground-prompt"><?php esc_html_e( 'User Prompt', 'aiground' ); ?></label>
							<textarea id="aiground-prompt"><?php echo esc_textarea( $default['user'] ); ?></textarea>
						</span>
					</p>
					<p>
						<label><?php esc_html_e( 'Expected Output', 'aiground' ); ?></label>
						<div id="aiground-expected"><?php echo esc_html( $default['expected'] ); ?></div>
					</p>
					<button id="aiground-submit" class="button button-primary"><?php esc_html_e( 'Submit', 'aiground' ); ?></button>
					<span id="aiground-spinner" class="spinner"></span>
				<?php endif; ?>
			</div>

			<div id="aiground-output"></div>
			<div id="aiground-meta" class="is-empty"></div>
			<div id="aiground-prompt-debug">
				<div class="apd-header">Final Prompt (Debug)</div>
				<div class="apd-row" id="apd-system">
					<div class="apd-label">System</div>
					<div class="apd-value"></div>
				</div>
				<div class="apd-row" id="apd-user">
					<div class="apd-label">User</div>
					<div class="apd-value"></div>
				</div>
				<div class="apd-row" id="apd-raw">
					<div class="apd-label">Raw Response</div>
					<div class="apd-value"></div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Handles the AJAX prompt request.
	 *
	 * @since 1.0.0
	 */
	public function handle_prompt(): void {
		check_ajax_referer( 'aiground_prompt', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized.', 'aiground' ) );
		}

		if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
			wp_send_json_error( __( 'WordPress AI client not available.', 'aiground' ) );
		}

		$prompt   = isset( $_POST['prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt'] ) ) : '';
		$system   = isset( $_POST['system'] ) ? sanitize_textarea_field( wp_unslash( $_POST['system'] ) ) : '';
		$provider = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		$model_id = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( $_POST['model'] ) ) : '';

		if ( '' === $prompt ) {
			wp_send_json_error( __( 'Prompt is required.', 'aiground' ) );
		}

		$builder = AI_Utils::build_prompt( $prompt, $system, $provider, $model_id );

		if ( ! $builder->is_supported_for_text_generation() ) {
			wp_send_json_error( __( 'No AI provider is configured.', 'aiground' ) );
		}

		$result = $builder->generate_text_result();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		if ( ! is_object( $result ) || ! method_exists( $result, 'toText' ) ) {
			wp_send_json_error( __( 'Unexpected response from AI provider.', 'aiground' ) );
		}

		wp_send_json_success(
			[
				'output' => trim( $result->toText() ),
				'meta'   => AI_Utils::extract_meta( $result ),
				'debug'  => [
					'system' => $system,
					'prompt' => $prompt,
					'raw'    => json_decode( wp_json_encode( $result ), true ),
				],
			]
		);
	}
}
