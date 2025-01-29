<?php

namespace alexdemers\OneSpanSign\Models;

/**
 * Class Sender
 * @package TagMyDoc\OneSpan\Models
 */
class Sender extends Model
{
	const TYPE_REGULAR = 'REGULAR';
	const TYPE_MANAGER = 'MANAGER';

	/** @var string */
	protected $id = null;

	/** @var string */
	protected $email = '';

	/** @var string */
	protected $firstName = '';

	/** @var string */
	protected $lastName = '';

	/** @var string */
	protected $language = 'en';

	/** @var string */
	protected $type = self::TYPE_MANAGER;

	/**
	 * @param string $email
	 * @return Sender
	 */
	public static function createFromEmail(string $email): Sender
	{
		return (new self())->withEmail($email);
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
	 * @return Sender
	 */
	public function withId(string $id): Sender
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	/**
	 * @param string $email
	 * @return Sender
	 */
	public function withEmail(string $email): Sender
	{
		$this->email = $email;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFirstName(): string
	{
		return $this->firstName;
	}

	/**
	 * @param string $firstName
	 * @return Sender
	 */
	public function withFirstName(string $firstName): Sender
	{
		$this->firstName = $firstName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLastName(): string
	{
		return $this->lastName;
	}

	/**
	 * @param string $lastName
	 * @return Sender
	 */
	public function withLastName(string $lastName): Sender
	{
		$this->lastName = $lastName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getLanguage(): string
	{
		return $this->language;
	}

	/**
	 * @param string $language
	 * @return Sender
	 */
	public function withLanguage(string $language): Sender
	{
		$this->language = $language;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @param string $type
	 * @return Sender
	 */
	public function withType(string $type): Sender
	{
		$this->type = $type;
		return $this;
	}

}
