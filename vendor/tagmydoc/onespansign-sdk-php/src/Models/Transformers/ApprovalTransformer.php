<?php

namespace alexdemers\OneSpanSign\Models\Transformers;

use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Exceptions\InvalidBindingException;
use Karriere\JsonDecoder\Transformer;
use alexdemers\OneSpanSign\Models\Approval;
use alexdemers\OneSpanSign\Models\Field;

/**
 * Class ApprovalTransformer
 * @package TagMyDoc\OneSpan\Models\Transformers
 */
class ApprovalTransformer implements Transformer
{
	/**
	 * register field, array, alias and callback bindings.
	 *
	 * @param ClassBindings $classBindings
	 * @throws InvalidBindingException
	 */
	public function register(ClassBindings $classBindings)
	{
		$classBindings->register(new ArrayBinding('fields', 'fields', Field::class));
	}

	/**
	 * @return string the full qualified class name that the transformer transforms
	 */
	public function transforms()
	{
		return Approval::class;
	}
}
