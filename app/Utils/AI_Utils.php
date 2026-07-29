<?php
/**
 * AI_Utils class.
 *
 * @package Nilambar\Promptbench
 */

namespace Nilambar\Promptbench\Utils;

use Throwable;
use WordPress\AiClient\AiClient;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI helper utilities.
 *
 * @since 1.0.0
 */
class AI_Utils {

	/**
	 * Gets AI providers with their available models.
	 *
	 * @since 1.0.0
	 *
	 * @return array Providers keyed by provider ID.
	 */
	public static function get_providers_with_models(): array {
		if ( ! function_exists( 'wp_get_connectors' ) ) {
			return [];
		}

		$connectors = wp_get_connectors();
		if ( ! is_array( $connectors ) ) {
			return [];
		}

		$data = [];

		foreach ( $connectors as $id => $connector_data ) {
			if ( ! is_array( $connector_data ) || ! isset( $connector_data['type'] ) || 'ai_provider' !== $connector_data['type'] ) {
				continue;
			}

			$name   = ( isset( $connector_data['name'] ) && '' !== $connector_data['name'] ) ? $connector_data['name'] : $id;
			$models = [];

			if ( class_exists( '\WordPress\AiClient\AiClient' ) ) {
				try {
					$registry       = AiClient::defaultRegistry();
					$provider_class = $registry->getProviderClassName( $id );
					foreach ( $provider_class::modelMetadataDirectory()->listModelMetadata() as $model ) {
						$models[] = [
							'id'   => $model->getId(),
							'name' => $model->getName(),
						];
					}
				} catch ( Throwable $e ) {
					continue;
				}
			}

			$data[ $id ] = [
				'name'   => $name,
				'models' => $models,
			];
		}

		// Registry fallback: pick up providers that registered directly without a Connectors entry.
		if ( class_exists( '\WordPress\AiClient\AiClient' ) ) {
			try {
				$registry = AiClient::defaultRegistry();
				foreach ( $registry->getRegisteredProviderIds() as $id ) {
					if ( isset( $data[ $id ] ) || ! $registry->isProviderConfigured( $id ) ) {
						continue;
					}
					try {
						$provider_class = $registry->getProviderClassName( $id );
						$models         = [];
						foreach ( $provider_class::modelMetadataDirectory()->listModelMetadata() as $model ) {
							$models[] = [
								'id'   => $model->getId(),
								'name' => $model->getName(),
							];
						}
						$data[ $id ] = [
							'name'   => $provider_class::metadata()->getName(),
							'models' => $models,
						];
					} catch ( Throwable $e ) {
						continue;
					}
				}
			} catch ( Throwable $e ) {
				unset( $e );
			}
		}

		return $data;
	}

	/**
	 * Builds an AI prompt builder for the given inputs.
	 *
	 * @since 1.0.0
	 *
	 * @param string $prompt      User prompt.
	 * @param string $system      System instruction.
	 * @param string $provider    Provider ID.
	 * @param string $model_id    Model ID.
	 * @param bool   $exact_match Whether the test case requires an exact-match output.
	 * @return object Prompt builder.
	 */
	public static function build_prompt( string $prompt, string $system, string $provider, string $model_id, bool $exact_match = false ) {
		$builder = wp_ai_client_prompt( $prompt );
		$builder = $builder->using_temperature( $exact_match ? 0.0 : 0.2 );

		if ( '' !== $system ) {
			$builder = $builder->using_system_instruction( $system );
		}

		if ( '' !== $model_id && '' !== $provider && class_exists( '\WordPress\AiClient\AiClient' ) ) {
			try {
				$registry = AiClient::defaultRegistry();
				$model    = $registry->getProviderModel( $provider, $model_id );
				$builder  = $builder->using_model( $model );
			} catch ( Throwable $e ) {
				if ( '' !== $provider ) {
					$builder = $builder->using_provider( $provider );
				}
			}
		} elseif ( '' !== $provider ) {
			$builder = $builder->using_provider( $provider );
		}

		return $builder;
	}

	/**
	 * Extracts metadata from an AI result.
	 *
	 * @since 1.0.0
	 *
	 * @param object $result AI result object.
	 * @return array Extracted metadata.
	 */
	public static function extract_meta( object $result ): array {
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
}
