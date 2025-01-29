<?php


namespace alexdemers\OneSpanSign\Models;

/**
 * Class Callback
 * @package alexdemers\OneSpanSign\Models
 */
class Callback extends Model
{
	/** @var string */
	protected $key = '';

	/** @var string */
	protected $url = '';

	/** @var string[] */
	protected $events = [];

	/**
	 * @return string
	 */
	public function getKey(): string
	{
		return $this->key;
	}

	/**
	 * @param string $key
	 * @return Callback
	 */
	public function withKey(string $key): self
	{
		$this->key = $key;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * @param string $url
	 * @return Callback
	 */
	public function withUrl(string $url): self
	{
		$this->url = $url;
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getEvents(): array
	{
		return $this->events;
	}

	/**
	 * @param string[] $events
	 * @return Callback
	 */
	public function withEvents(array $events = []): self
	{
		$this->events = $events;
		return $this;
	}

}
