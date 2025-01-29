<?php

namespace alexdemers\OneSpanSign\Models;

/**
 * Class Role
 * @package TagMyDoc\OneSpan\Models
 */
class Role extends Model
{
	const TYPE_SENDER = 'SENDER';
	const TYPE_SIGNER = 'SIGNER';

	/** @var string */
	protected $type = 'SIGNER';

	/** @var Signer[] */
	protected $signers = [];

	/** @var string */
	protected $name = '';

	/** @var string */
	protected $id = null;

	/** @var int */
	protected $index = 0;

	/**
	 * @param string $name
	 * @return Role
	 */
	public static function createFromName(string $name): self
	{
		return self::create()->withName($name);
	}

	/**
	 * @param string $id
	 * @return Role
	 */
	public static function createFromId(string $id): self
	{
		return self::create()->withId($id);
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Can be one of "SENDER" or "SIGNER"
	 *
	 * @param string $type
	 * @return Role
	 */
	public function withType(string $type): self
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @return Signer[]
	 */
	public function getSigners(): array
	{
		return $this->signers;
	}

	/**
	 * @param array $signers
	 * @return Role
	 */
	public function withSigners(array $signers): self
	{
		$this->signers = $signers;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return Role
	 */
	public function withName(string $name): self
	{
		$this->name = $name;
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
	 * @return Role
	 */
	public function withIndex(int $index): self
	{
		$this->index = $index;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getId(): string
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return Role
	 */
	public function withId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @param Signer $signer
	 * @return $this
	 */
	public function withSigner(Signer $signer)
	{
		$this->signers[] = $signer;
		return $this;
	}
}
