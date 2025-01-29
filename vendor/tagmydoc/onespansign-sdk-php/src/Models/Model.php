<?php


namespace alexdemers\OneSpanSign\Models;

use JsonSerializable;

/**
 * Class Model
 * @package alexdemers\OneSpanSign\Models
 */
abstract class Model implements JsonSerializable
{
	/**
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return array_filter(get_object_vars($this), function ($property) {
			return $property !== null;
		});
	}

}
