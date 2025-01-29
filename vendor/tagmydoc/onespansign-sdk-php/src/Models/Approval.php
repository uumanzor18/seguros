<?php


namespace alexdemers\OneSpanSign\Models;


/**
 * Class Approval
 * @package TagMyDoc\OneSpan\Models
 */
class Approval extends Model
{
	/** @var string */
	protected $id = '';

	/** @var Field[] */
	protected $fields = [];

	/** @var string */
	protected $role = '';

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @param string $fields
	 * @return Approval
	 */
	public function withFields(string $fields): self
	{
		$this->fields = $fields;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRole(): string
	{
		return $this->role;
	}

	/**
	 * @param string|Role $role
	 * @return Approval
	 */
	public function withRole($role): self
	{
		$this->role = $role instanceof Role ? $role->getId() : $role;
		return $this;
	}

	/**
	 * @param Field $field
	 * @return $this
	 */
	public function withField(Field $field): self
	{
		$this->fields[] = $field;
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
	 * @return Approval
	 */
	public function withId(?string $id): Approval
	{
		$this->id = $id;
		return $this;
	}

}
