<?php


namespace alexdemers\OneSpanSign\Models;


/**
 * Class Signer
 * @package TagMyDoc\OneSpan\Models
 */
class Signer extends Model
{
	const TYPE_ACCOUNT_SENDER = 'ACCOUNT_SENDER';
	const TYPE_EXTERNAL_SENDER = 'EXTERNAL_SENDER';
	const TYPE_EXTERNAL_SIGNER = 'EXTERNAL_SIGNER';
	const TYPE_GROUP_SIGNER = 'GROUP_SIGNER';

	/** @var string */
	protected $id = null;

	/** @var string */
	protected $email = '';

	/** @var string */
	protected $firstName = '';

	/** @var string */
	protected $lastName = '';

	/** @var array */
	protected $delivery = ['provider' => false, 'email' => true, 'download' => false];

	/** @var string */
	protected $language = 'en';

	/** @var string */
	protected $signerType = self::TYPE_EXTERNAL_SIGNER;

	/** Auth */
	protected $auth = null;

	/**
	 * @param string $email
	 * @return Signer
	 */
	public static function createFromEmail(string $email)
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
	 * @return Signer
	 */
	public function withId(string $id): Signer
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
	 * @return Signer
	 */
	public function withEmail(string $email): Signer
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
	 * @return Signer
	 */
	public function withFirstName(string $firstName): Signer
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
	 * @return Signer
	 */
	public function withLastName(string $lastName): Signer
	{
		$this->lastName = $lastName;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getDelivery(): array
	{
		return $this->delivery;
	}

	/**
	 * @param array $delivery
	 * @return Signer
	 */
	public function withDelivery(array $delivery): Signer
	{
		$this->delivery = $delivery;
		return $this;
	}

	/**
	 * @return Signer
	 */
	public function withNoDelivery(): Signer
	{
		$this->delivery = ['email' => false, 'provider' => false, 'download' => false ];
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
	 * @return Signer
	 */
	public function withLanguage(string $language): Signer
	{
		$this->language = $language;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getSignerType(): string
	{
		return $this->signerType;
	}

	/**
	 * @param string $signerType
	 * @return Signer
	 */
	public function withSignerType(string $signerType): Signer
	{
		$this->signerType = $signerType;
		return $this;
	}

    /**
     * @return ?Auth
     */
    public function getAuth(): ?Auth
    {
        return $this->auth;
    }

    /**
     * @param ?Auth $auth
     * @return Signer
     */
    public function withAuth(?Auth $auth): Signer
    {
        $this->auth = $auth;
        return $this;
    }

    /**
     * @param string $phoneNumber
     * @return $this
     */
    public function withSmsAuth(string $phoneNumber): Signer
    {
        $this->auth = new SmsAuthChallenge($phoneNumber);
        return $this;
    }

}
