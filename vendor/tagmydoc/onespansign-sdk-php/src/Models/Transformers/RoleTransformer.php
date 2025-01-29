<?php


namespace alexdemers\OneSpanSign\Models\Transformers;


use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;
use alexdemers\OneSpanSign\Models\Role;
use alexdemers\OneSpanSign\Models\Signer;

/**
 * Class RoleTransformer
 * @package TagMyDoc\OneSpan\Models\Transformers
 */
class RoleTransformer implements Transformer
{

	/**
	 * register field, array, alias and callback bindings.
	 *
	 * @param ClassBindings $classBindings
	 */
	public function register(ClassBindings $classBindings)
	{
		$classBindings->register(new ArrayBinding('signers', 'signers', Signer::class));
	}

	/**
	 * @return string the full qualified class name that the transformer transforms
	 */
	public function transforms()
	{
		return Role::class;
	}
}
