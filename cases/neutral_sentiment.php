<?php
/**
 * Test case: Neutral Sentiment.
 *
 * @package Nilambar\Promptbench
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'test_id'        => 140,
	'label'          => __( 'Neutral Sentiment', 'promptbench' ),
	'system'         => 'Classify the sentiment of the customer message below into exactly one of these three labels: "POSITIVE", "NEGATIVE", "NONE".'
		. "\n\n" . 'Use "NONE" only if the message expresses no sentiment at all — i.e., it is purely factual or neutral. Output only the single label, no punctuation, no other text.',
	'user'           => 'My order number is 4471 and it was placed on Tuesday.',
	'expected'       => 'Format: a single bare token, no punctuation, no other text.'
		. "\n" . 'Exact value expected: NONE (the message is purely factual with no sentiment; a model biased toward forcing a POSITIVE/NEGATIVE label instead of admitting neither applies will fail this case).',
	'exact_match'    => true,
	'expected_value' => 'NONE',
];
