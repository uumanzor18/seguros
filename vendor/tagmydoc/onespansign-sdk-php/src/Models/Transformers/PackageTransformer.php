<?php


namespace alexdemers\OneSpanSign\Models\Transformers;


use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;
use alexdemers\OneSpanSign\Models\Document;
use alexdemers\OneSpanSign\Models\Package;
use alexdemers\OneSpanSign\Models\Role;

/**
 * Class PackageTransformer
 * @package TagMyDoc\OneSpan\Models\Transformers
 */
class PackageTransformer implements Transformer
{
	/**
	 * register field, array, alias and callback bindings.
	 *
	 * @param ClassBindings $classBindings
	 */
	public function register(ClassBindings $classBindings)
	{
		$classBindings->register(new ArrayBinding('documents', 'documents', Document::class));
		$classBindings->register(new ArrayBinding('roles', 'roles', Role::class));
	}

	/**
	 * @return string the full qualified class name that the transformer transforms
	 */
	public function transforms()
	{
		return Package::class;
	}
}
