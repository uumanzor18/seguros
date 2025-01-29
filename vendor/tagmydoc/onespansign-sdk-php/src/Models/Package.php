<?php

namespace alexdemers\OneSpanSign\Models;

/**
 * Class Package
 * @package TagMyDoc\OneSpan\Models
 */
class Package extends Model
{
	const TYPE_PACKAGE = 'PACKAGE';
	const TYPE_TEMPLATE = 'TEMPLATE';
	const TYPE_LAYOUT = 'LAYOUT';

	/** @var Document[] */
	protected $documents = [];

	/** @var string */
	protected $emailMessage = '';

	/** @var ?string */
	protected $id = null;

	/** @var string */
	protected $language = 'en';

	/** @var null */
	protected $name = null;

	/** @var Role[] */
	protected $roles = [];

	/** @var string */
	protected $status = null;

	/** @var array */
	protected $data = null;

	/** @var array */
	protected $settings = null;

	/** @var Sender */
	protected $sender = null;

	/** @var string */
	protected $type = self::TYPE_PACKAGE;

	/**
	 * @param string $name
	 * @return Package
	 */
	public static function createFromName(string $name): self
	{
		return self::create()->withName($name);
	}

	/**
	 * @param string $id
	 * @return self
	 */
	public static function createFromId(string $id): self
	{
		return (new self())->withId($id);
	}

	/**
	 * Package constructor.
	 * @param string $name
	 */
	public function __construct(?string $name = null)
	{
		$this->withName($name);
	}

	/**
	 * @return Document[]
	 */
	public function getDocuments(): array
	{
		return $this->documents;
	}

	/**
	 * @param string $path
	 * @param Document $document
	 * @return Package
	 */
	public function withDocument(string $path, Document $document): self
	{
		$this->documents[$path] = $document;
		return $this;
	}

	/**
	 * @param Document[] $documents
	 * @return Package
	 */
	public function withDocuments(array $documents): self
	{
		$this->documents = $documents;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getEmailMessage(): string
	{
		return $this->emailMessage;
	}

	/**
	 * @param string $emailMessage
	 * @return Package
	 */
	public function withEmailMessage(string $emailMessage): self
	{
		$this->emailMessage = $emailMessage;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $id
	 * @return Package
	 */
	public function withId($id): self
	{
		$this->id = $id;
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
	 * @return Package
	 */
	public function withLanguage(string $language): self
	{
		$this->language = $language;
		return $this;
	}

	/**
	 * @return null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name
	 * @return Package
	 */
	public function withName(?string $name = null): self
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return Role[]
	 */
	public function getRoles(): array
	{
		return $this->roles;
	}

	/**
	 * @param Role $role
	 * @return Package
	 */
	public function withRole(Role $role): self
	{
		$this->roles[] = $role;
		return $this;
	}

	/**
	 * @param array $roles
	 * @return Package
	 */
	public function withRoles(array $roles): self
	{
		$this->roles = $roles;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStatus(): string
	{
		return $this->status;
	}

	/**
	 * @param string $status
	 * @return Package
	 */
	public function withStatus(string $status): self
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @return array|null ?array
	 */
	public function getData(): ?array
	{
		return $this->data;
	}

	/**
	 * @param array|null $data
	 * @return Package
	 */
	public function withData(?array $data = null): self
	{
		$this->data = $data;
		return $this;
	}

	/**
	 * @return array|null ?array
	 */
	public function getSettings(): ?array
	{
		return $this->settings;
	}

	/**
	 * @param array|null $settings
	 * @return Package
	 */
	public function withSettings(?array $settings = null): self
	{
		$this->settings = $settings;
		return $this;
	}

	/**
	 * @return Sender[]|null ?array
	 */
	public function getSender(): ?Sender
	{
		return $this->sender;
	}

	/**
	 * @param Sender|null $sender
	 * @return Package
	 */
	public function withSender(?Sender $sender = null): self
	{
		$this->sender = $sender;
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
	 * @return Package
	 */
	public function withType(string $type): self
	{
		$this->type = $type;
		return $this;
	}
}
