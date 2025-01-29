<?php


namespace alexdemers\OneSpanSign\Models;

/**
 * Class Document
 * @package TagMyDoc\OneSpan\Models
 */
class Document extends Model
{
	const SUPPORTED_FILETYPES = ['pdf', 'doc', 'docx', 'odt', 'txt', 'rtf'];
	const MAX_FILE_SIZE = 1024 * 1024 * 16;

	/** @var string */
	protected $name = '';

	/** @var string */
	protected $id = null;

	/** @var Approval[] */
	protected $approvals = [];

	/** @var Field[] */
	protected $fields = [];

	/** @var string */
	protected $status = '';

	/** @var Page[] */
	protected $pages = [];

	/** @var int */
	protected $size = 0;

	/** @var int */
	protected $index = 0;

	/** @var array */
//	protected $data = [];

	/**
	 * @param string $name
	 * @return self
	 */
	public static function createFromName(?string $name = null): self
	{
		return (new self())->withName($name);
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
	 * @param Approval $approval
	 * @return self
	 */
	public function withApproval(Approval $approval): self
	{
		$this->approvals[] = $approval;
		return $this;
	}

	/**
	 * @param Field $field
	 * @return self
	 */
	public function withField(Field $field): self
	{
		$this->fields[] = $field;
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
	 * @return Document
	 */
	public function withName(?string $name = null): self
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * @return Approval[]
	 */
	public function getApprovals(): array
	{
		return $this->approvals;
	}

	/**
	 * @param array $approvals
	 * @return Document
	 */
	public function withApprovals(array $approvals): Document
	{
		$this->approvals = $approvals;
		return $this;
	}

	/**
	 * @return Field[]
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @param Field[] $fields
	 * @return self
	 */
	public function withFields(array $fields): self
	{
		$this->fields = $fields;
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
	 * @return self
	 */
	public function withStatus(string $status): self
	{
		$this->status = $status;
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
	 * @return self
	 */
	public function withId(string $id): self
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * @return Page[]
	 */
	public function getPages(): array
	{
		return $this->pages;
	}

	/**
	 * @param Page[] $pages
	 * @return Document
	 */
	public function withPages(array $pages): Document
	{
		$this->pages = $pages;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getSize(): int
	{
		return $this->size;
	}

	/**
	 * @param int $size
	 * @return Document
	 */
	public function withSize(int $size): Document
	{
		$this->size = $size;
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
	 * @return Document
	 */
	public function withIndex(int $index): Document
	{
		$this->index = $index;
		return $this;
	}
}
