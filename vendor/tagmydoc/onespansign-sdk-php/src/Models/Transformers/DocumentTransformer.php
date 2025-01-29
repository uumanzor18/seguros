<?php

namespace alexdemers\OneSpanSign\Models\Transformers;

use alexdemers\OneSpanSign\Models\Page;
use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;
use alexdemers\OneSpanSign\Models\Approval;
use alexdemers\OneSpanSign\Models\Document;
use alexdemers\OneSpanSign\Models\Field;

/**
 * Class DocumentTransformer
 * @package TagMyDoc\OneSpan\Models\Transformers
 */
class DocumentTransformer implements Transformer
{
	/**
	 * register field, array, alias and callback bindings.
	 *
	 * @param ClassBindings $classBindings
	 * @throws \Karriere\JsonDecoder\Exceptions\InvalidBindingException
	 */
	public function register(ClassBindings $classBindings)
	{
		$classBindings->register(new ArrayBinding('approvals', 'approvals', Approval::class));
		$classBindings->register(new ArrayBinding('fields', 'fields', Field::class));
		$classBindings->register(new ArrayBinding('pages', 'pages', Page::class));
	}

	/**
	 * @return string the full qualified class name that the transformer transforms
	 */
	public function transforms()
	{
		return Document::class;
	}
}
