<?php

namespace alexdemers\OneSpanSign\Models;

/**
 * Class Page
 * @package alexdemers\OneSpanSign\Models
 */
class Page extends Model
{
	/** @var string */
	protected $id = '';

	/** @var int */
	protected $index = 0;

	/** @var int */
	protected $version = 0;

	/** @var float */
	protected $left = 0.0;

	/** @var float */
	protected $width = 0.0;

	/** @var float */
	protected $top = 0.0;

	/** @var float */
	protected $height = 0.0;

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return Page
	 */
	public function withId(string $id): Page
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getIndex(): int
	{
		return $this->index;
	}

	/**
	 * @param int $index
	 * @return Page
	 */
	public function withIndex(int $index): Page
	{
		$this->index = $index;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getVersion(): int
	{
		return $this->version;
	}

	/**
	 * @param int $version
	 * @return Page
	 */
	public function withVersion(int $version): Page
	{
		$this->version = $version;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getLeft(): float
	{
		return $this->left;
	}

	/**
	 * @param float $left
	 * @return Page
	 */
	public function withLeft(float $left): Page
	{
		$this->left = $left;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getWidth(): float
	{
		return $this->width;
	}

	/**
	 * @param float $width
	 * @return Page
	 */
	public function withWidth(float $width): Page
	{
		$this->width = $width;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getTop(): float
	{
		return $this->top;
	}

	/**
	 * @param float $top
	 * @return Page
	 */
	public function withTop(float $top): Page
	{
		$this->top = $top;
		return $this;
	}

	/**
	 * @return float
	 */
	public function getHeight(): float
	{
		return $this->height;
	}

	/**
	 * @param float $height
	 * @return Page
	 */
	public function withHeight(float $height): Page
	{
		$this->height = $height;
		return $this;
	}

}
