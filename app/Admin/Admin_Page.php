<?php
/**
 * Admin_Page class.
 *
 * @package Nilambar\Promptbench
 */

namespace Nilambar\Promptbench\Admin;

use Nilambar\Promptbench\Utils\AI_Utils;
use Nilambar\Promptbench\Utils\Case_Utils;

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
		add_action( 'wp_ajax_promptbench_prompt', [ $this, 'handle_prompt' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( PROMPTBENCH_FILE ), [ $this, 'add_action_links' ] );
	}

	/**
	 * Adds plugin action links.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Existing plugin action links.
	 * @return array Modified plugin action links.
	 */
	public function add_action_links( array $links ): array {
		$url = add_query_arg( 'page', 'promptbench-tools', admin_url( 'tools.php' ) );

		$link = sprintf(
			'<a href="%1$s">%2$s</a>',
			esc_url( $url ),
			esc_html_x( 'Promptbench', 'page title', 'promptbench' )
		);

		array_unshift( $links, $link );

		return $links;
	}

	/**
	 * Adds admin menu.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu(): void {
		$hook_suffix = add_management_page(
			_x( 'Promptbench', 'page title', 'promptbench' ),
			_x( 'Promptbench', 'menu title', 'promptbench' ),
			'manage_options',
			'promptbench-tools',
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

		wp_enqueue_style( 'promptbench', plugins_url( 'build/main.css', PROMPTBENCH_FILE ), [], '1.0.0' );
		wp_enqueue_script( 'promptbench', plugins_url( 'build/main.js', PROMPTBENCH_FILE ), [], '1.0.0', true );

		wp_localize_script(
			'promptbench',
			'promptbenchData',
			[
				'providerModels' => $data['providers'],
				'testCases'      => $data['test_cases'],
				'nonce'          => $data['nonce'],
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'errorGeneric'   => __( 'An error occurred.', 'promptbench' ),
				'requestFailed'  => __( 'Request failed.', 'promptbench' ),
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
			'nonce'      => wp_create_nonce( 'promptbench_prompt' ),
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

			<div id="promptbench-form">
				<?php if ( empty( $providers ) ) : ?>
					<p><?php esc_html_e( 'No AI providers configured. Please configure a provider under Settings > Connectors.', 'promptbench' ); ?></p>
				<?php else : ?>
					<p class="promptbench-row">
						<span class="promptbench-field">
							<label for="promptbench-provider"><?php esc_html_e( 'Provider', 'promptbench' ); ?></label>
							<select id="promptbench-provider">
								<?php foreach ( $providers as $id => $data ) : ?>
									<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $data['name'] ); ?></option>
								<?php endforeach; ?>
							</select>
						</span>
						<span class="promptbench-field">
							<label for="promptbench-model"><?php esc_html_e( 'Model', 'promptbench' ); ?></label>
							<select id="promptbench-model"></select>
						</span>
					</p>
					<p>
						<label><?php esc_html_e( 'Test Case', 'promptbench' ); ?></label>
						<div id="promptbench-testcase" class="promptbench-pills">
							<?php foreach ( $test_cases as $id => $test_case ) : ?>
								<button type="button" class="promptbench-pill<?php echo $id === $default_id ? ' is-active' : ''; ?>" data-testcase="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $test_case['label'] ); ?></button>
							<?php endforeach; ?>
						</div>
					</p>
					<p class="promptbench-row">
						<span class="promptbench-field">
							<label for="promptbench-system-prompt"><?php esc_html_e( 'System Prompt', 'promptbench' ); ?></label>
							<textarea id="promptbench-system-prompt"><?php echo esc_textarea( $default['system'] ); ?></textarea>
						</span>
						<span class="promptbench-field">
							<label for="promptbench-prompt"><?php esc_html_e( 'User Prompt', 'promptbench' ); ?></label>
							<textarea id="promptbench-prompt"><?php echo esc_textarea( $default['user'] ); ?></textarea>
						</span>
					</p>
					<div id="promptbench-expected-wrap"<?php echo '' === $default['expected'] ? ' style="display:none;"' : ''; ?>>
						<label><?php esc_html_e( 'Expected Output', 'promptbench' ); ?></label>
						<div id="promptbench-expected"><?php echo esc_html( $default['expected'] ); ?></div>
					</div>
					<button id="promptbench-submit" class="button button-primary"><?php esc_html_e( 'Submit', 'promptbench' ); ?></button>
					<span id="promptbench-spinner" class="spinner"></span>
				<?php endif; ?>
			</div>

			<div id="promptbench-output"></div>
			<div id="promptbench-meta" class="is-empty"></div>
			<div id="promptbench-prompt-debug">
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
		check_ajax_referer( 'promptbench_prompt', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Unauthorized.', 'promptbench' ) );
		}

		if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
			wp_send_json_error( __( 'WordPress AI client not available.', 'promptbench' ) );
		}

		$prompt      = isset( $_POST['prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt'] ) ) : '';
		$system      = isset( $_POST['system'] ) ? sanitize_textarea_field( wp_unslash( $_POST['system'] ) ) : '';
		$provider    = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';
		$model_id    = isset( $_POST['model'] ) ? sanitize_text_field( wp_unslash( $_POST['model'] ) ) : '';
		$exact_match = isset( $_POST['exact_match'] ) && '1' === $_POST['exact_match'];

		if ( '' === $prompt ) {
			wp_send_json_error( __( 'Prompt is required.', 'promptbench' ) );
		}

		$builder = AI_Utils::build_prompt( $prompt, $system, $provider, $model_id, $exact_match );

		if ( ! $builder->is_supported_for_text_generation() ) {
			wp_send_json_error( __( 'No AI provider is configured.', 'promptbench' ) );
		}

		$result = $builder->generate_text_result();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		if ( ! is_object( $result ) || ! method_exists( $result, 'toText' ) ) {
			wp_send_json_error( __( 'Unexpected response from AI provider.', 'promptbench' ) );
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
