<?php

namespace alexdemers\OneSpanSign\Models\Transformers;

use Karriere\JsonDecoder\Bindings\ArrayBinding;
use alexdemers\OneSpanSign\Models\Auth;
use alexdemers\OneSpanSign\Models\AuthChallenge;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

/**
 * Class SignerTransformer
 * @package TagMyDoc\OneSpan\Models\Transformers
 */
class AuthTransformer implements Transformer
{

	/**
	 * register field, array, alias and callback bindings.
	 *
	 * @param ClassBindings $classBindings
	 */
	public function register(ClassBindings $classBindings)
	{
		$classBindings->register(new ArrayBinding('challenges', 'challenges', AuthChallenge::class));
	}

	/**
	 * @return string the full qualified class name that the transformer transforms
	 */
	public function transforms()
	{
		return Auth::class;
	}
}
