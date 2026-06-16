<?php
/**
 * Plugin Name: AIGround
 * Description: AIGround plugin.
 * Version: 1.0.0
 * Author: Nilambar Sharma
 * Text Domain: aiground
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'aiground_add_admin_menu' );

function aiground_add_admin_menu() {
	add_management_page(
		__( 'AIGround', 'aiground' ),
		__( 'AIGround', 'aiground' ),
		'manage_options',
		'aiground-tools',
		'aiground_tools_page'
	);
}

function aiground_get_providers(): array {
	if ( ! function_exists( 'wp_get_connectors' ) ) {
		return [];
	}

	$connectors = wp_get_connectors();
	if ( ! is_array( $connectors ) ) {
		return [];
	}

	$providers = [];
	foreach ( $connectors as $id => $data ) {
		if ( ! is_array( $data ) || ! isset( $data['type'] ) || 'ai_provider' !== $data['type'] ) {
			continue;
		}
		$name              = ( isset( $data['name'] ) && '' !== $data['name'] ) ? $data['name'] : $id;
		$providers[ $id ]  = $name;
	}

	return $providers;
}

function aiground_tools_page() {
	$providers = aiground_get_providers();
	$nonce     = wp_create_nonce( 'aiground_prompt' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'AIGround', 'aiground' ); ?></h1>

		<style>
			#aiground-form { max-width: 600px; margin-top: 20px; }
			#aiground-form p { margin-bottom: 12px; }
			#aiground-form label { display: block; font-weight: 600; margin-bottom: 4px; }
			#aiground-form select, #aiground-form textarea { width: 100%; box-sizing: border-box; }
			#aiground-form textarea { height: 100px; margin-top: 4px; }
			#aiground-spinner { display: none; vertical-align: middle; float: none; }
			#aiground-output { max-width: 600px; margin-top: 20px; padding: 12px 16px; background: #f0f0f1; border-left: 4px solid #2271b1; white-space: pre-wrap; display: none; }
			#aiground-output.is-error { border-left-color: #d63638; background: #fcf0f1; }
		</style>

		<div id="aiground-form">
			<?php if ( empty( $providers ) ) : ?>
				<p><?php esc_html_e( 'No AI providers configured. Please configure a provider under Settings > Connectors.', 'aiground' ); ?></p>
			<?php else : ?>
				<p>
					<label for="aiground-provider"><?php esc_html_e( 'Provider', 'aiground' ); ?></label>
					<select id="aiground-provider">
						<?php foreach ( $providers as $id => $name ) : ?>
							<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $name ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p>
					<label for="aiground-prompt"><?php esc_html_e( 'Prompt', 'aiground' ); ?></label>
					<textarea id="aiground-prompt"><?php echo esc_textarea( 'What is capital of Nepal?' ); ?></textarea>
				</p>
				<button id="aiground-submit" class="button button-primary"><?php esc_html_e( 'Submit', 'aiground' ); ?></button>
				<span id="aiground-spinner" class="spinner"></span>
			<?php endif; ?>
		</div>

		<div id="aiground-output"></div>
	</div>

	<script>
		(function () {
			var btn = document.getElementById('aiground-submit');
			if (!btn) return;

			btn.addEventListener('click', function () {
				var provider = document.getElementById('aiground-provider').value;
				var prompt   = document.getElementById('aiground-prompt').value.trim();
				var output   = document.getElementById('aiground-output');
				var spinner  = document.getElementById('aiground-spinner');

				if (!prompt) return;

				btn.disabled            = true;
				spinner.style.display   = 'inline-block';
				output.style.display    = 'none';
				output.className        = '';

				var body = new FormData();
				body.append('action',   'aiground_prompt');
				body.append('nonce',    <?php echo wp_json_encode( $nonce ); ?>);
				body.append('provider', provider);
				body.append('prompt',   prompt);

				fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, { method: 'POST', body: body })
					.then(function (r) { return r.json(); })
					.then(function (res) {
						output.style.display = 'block';
						if (res.success) {
							output.className    = '';
							output.textContent  = res.data;
						} else {
							output.className    = 'is-error';
							output.textContent  = res.data || <?php echo wp_json_encode( __( 'An error occurred.', 'aiground' ) ); ?>;
						}
					})
					.catch(function () {
						output.style.display = 'block';
						output.className     = 'is-error';
						output.textContent   = <?php echo wp_json_encode( __( 'Request failed.', 'aiground' ) ); ?>;
					})
					.finally(function () {
						btn.disabled          = false;
						spinner.style.display = 'none';
					});
			});
		}());
	</script>
	<?php
}

add_action( 'wp_ajax_aiground_prompt', 'aiground_handle_prompt' );

function aiground_handle_prompt() {
	check_ajax_referer( 'aiground_prompt', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'Unauthorized.', 'aiground' ) );
	}

	if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
		wp_send_json_error( __( 'WordPress AI client not available.', 'aiground' ) );
	}

	$prompt   = isset( $_POST['prompt'] ) ? sanitize_textarea_field( wp_unslash( $_POST['prompt'] ) ) : '';
	$provider = isset( $_POST['provider'] ) ? sanitize_text_field( wp_unslash( $_POST['provider'] ) ) : '';

	if ( '' === $prompt ) {
		wp_send_json_error( __( 'Prompt is required.', 'aiground' ) );
	}

	$builder = wp_ai_client_prompt( $prompt );

	if ( '' !== $provider ) {
		$builder = $builder->using_provider( $provider );
	}

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

	wp_send_json_success( trim( $result->toText() ) );
}
