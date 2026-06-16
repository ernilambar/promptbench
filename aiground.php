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
			#aiground-output { max-width: 600px; margin-top: 20px; padding: 16px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,.08); white-space: pre-wrap; display: none; }
			#aiground-output.is-error { border-color: #d63638; background: #fcf0f1; color: #d63638; }
			#aiground-meta { max-width: 600px; margin-top: 8px; font-size: 12px; color: #50575e; }
			#aiground-meta.is-empty { display: none; }
			#aiground-prompt-debug { max-width: 600px; margin-top: 12px; border: 1px solid #c3c4c7; border-radius: 4px; font-family: monospace; font-size: 12px; display: none; }
			#aiground-prompt-debug .apd-header { padding: 6px 12px; font-size: 11px; font-weight: 600; color: #50575e; border-bottom: 1px solid #c3c4c7; }
			#aiground-prompt-debug .apd-row { padding: 8px 12px; }
			#aiground-prompt-debug .apd-row + .apd-row { border-top: 1px solid #dcdcde; }
			#aiground-prompt-debug .apd-label { font-size: 10px; font-weight: 700; color: #8c8f94; text-transform: uppercase; margin-bottom: 4px; }
			#aiground-prompt-debug .apd-value { color: #1d2327; white-space: pre-wrap; word-break: break-word; }
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
				<?php
				$countries        = [ 'France', 'Japan', 'Brazil', 'Germany', 'Australia', 'Canada', 'India', 'Italy', 'Mexico', 'Spain', 'Argentina', 'Egypt', 'Nigeria', 'South Korea', 'Turkey', 'Indonesia', 'Saudi Arabia', 'Thailand', 'Poland', 'Netherlands' ];
				$random_country   = $countries[ array_rand( $countries ) ];
				?>
				<p>
					<label for="aiground-prompt"><?php esc_html_e( 'Prompt', 'aiground' ); ?></label>
					<textarea id="aiground-prompt"><?php echo esc_textarea( "What is capital of {$random_country}? Tell me its best attraction in one sentence." ); ?></textarea>
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
		</div>
	</div>

	<script>
		(function () {
			var btn      = document.getElementById('aiground-submit');
			var select   = document.getElementById('aiground-provider');
			if (!btn) return;

			var storageKey = 'aiground_provider';
			var saved = localStorage.getItem(storageKey);
			if (saved && select.querySelector('option[value="' + saved + '"]')) {
				select.value = saved;
			}
			select.addEventListener('change', function () {
				localStorage.setItem(storageKey, select.value);
			});

			function buildMetaLines(meta) {
				if (!meta) return [];
				var lines = [];

				if (meta.provider) {
					var p    = meta.provider;
					var name = p.name || p.id || '';
					var type = p.type || '';
					lines.push('Provider: ' + (name && type ? name + ' · ' + type : name || p.id || ''));
				}

				if (meta.model) {
					var m    = meta.model;
					var mId  = m.id || '';
					var mName = m.name || '';
					if (mName && mId && mName !== mId) {
						lines.push('Model: ' + mName + ' (' + mId + ')');
					} else {
						lines.push('Model: ' + (mName || mId));
					}
				}

				if (meta.token_usage) {
					var t = meta.token_usage, parts = [];
					if (t.promptTokens     != null) parts.push('Prompt: '     + t.promptTokens);
					if (t.completionTokens != null) parts.push('Completion: ' + t.completionTokens);
					if (t.totalTokens      != null) parts.push('Total: '      + t.totalTokens);
					if (t.thoughtTokens    != null) parts.push('Thought: '    + t.thoughtTokens);
					if (parts.length) lines.push('Tokens — ' + parts.join(' · '));
				}

				return lines.filter(Boolean);
			}

			btn.addEventListener('click', function () {
				var provider = document.getElementById('aiground-provider').value;
				var prompt   = document.getElementById('aiground-prompt').value.trim();
				var output      = document.getElementById('aiground-output');
				var metaEl      = document.getElementById('aiground-meta');
				var promptDebug = document.getElementById('aiground-prompt-debug');
				var spinner     = document.getElementById('aiground-spinner');

				if (!prompt) return;

				btn.disabled            = true;
				spinner.style.display   = 'inline-block';
				output.style.display    = 'none';
				output.className        = '';
				metaEl.className        = 'is-empty';
				metaEl.innerHTML        = '';
				promptDebug.style.display = 'none';

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
							output.className   = '';
							output.textContent = res.data.output;
							var lines = buildMetaLines(res.data.meta);
							if (lines.length) {
								lines.forEach(function (line) {
									var div = document.createElement('div');
									div.textContent = line;
									metaEl.appendChild(div);
								});
								metaEl.className = '';
							}
							if (res.data.debug) {
								document.querySelector('#apd-system .apd-value').textContent = res.data.debug.system || '';
								document.querySelector('#apd-user .apd-value').textContent   = res.data.debug.prompt || '';
								promptDebug.style.display = 'block';
							}
						} else {
							output.className   = 'is-error';
							output.textContent = res.data || <?php echo wp_json_encode( __( 'An error occurred.', 'aiground' ) ); ?>;
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

	$system  = 'Be concise. No markdown, no code fences, no wrapping.';
	$builder = wp_ai_client_prompt( $prompt )
		->using_system_instruction( $system );

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

	wp_send_json_success(
		[
			'output' => trim( $result->toText() ),
			'meta'   => aiground_extract_meta( $result ),
			'debug'  => [
				'system' => $system,
				'prompt' => $prompt,
			],
		]
	);
}

function aiground_extract_meta( object $result ): array {
	$meta = [];

	if ( method_exists( $result, 'getProviderMetadata' ) ) {
		$provider = $result->getProviderMetadata();
		if ( $provider ) {
			$data = [];
			if ( method_exists( $provider, 'getId' ) ) {
				$data['id'] = $provider->getId();
			}
			if ( method_exists( $provider, 'getName' ) ) {
				$data['name'] = $provider->getName();
			}
			if ( method_exists( $provider, 'getType' ) ) {
				$data['type'] = $provider->getType();
			}
			$meta['provider'] = $data;
		}
	}

	if ( method_exists( $result, 'getModelMetadata' ) ) {
		$model = $result->getModelMetadata();
		if ( $model ) {
			$data = [];
			if ( method_exists( $model, 'getId' ) ) {
				$data['id'] = $model->getId();
			}
			if ( method_exists( $model, 'getName' ) ) {
				$data['name'] = $model->getName();
			}
			$meta['model'] = $data;
		}
	}

	if ( method_exists( $result, 'getTokenUsage' ) ) {
		$usage = $result->getTokenUsage();
		if ( $usage ) {
			$data = [];
			if ( method_exists( $usage, 'getPromptTokens' ) ) {
				$data['promptTokens'] = $usage->getPromptTokens();
			}
			if ( method_exists( $usage, 'getCompletionTokens' ) ) {
				$data['completionTokens'] = $usage->getCompletionTokens();
			}
			if ( method_exists( $usage, 'getTotalTokens' ) ) {
				$data['totalTokens'] = $usage->getTotalTokens();
			}
			if ( method_exists( $usage, 'getThoughtTokens' ) ) {
				$data['thoughtTokens'] = $usage->getThoughtTokens();
			}
			$meta['token_usage'] = $data;
		}
	}

	return $meta;
}
