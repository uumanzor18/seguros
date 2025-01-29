<?php


namespace alexdemers\OneSpanSign\Models;

/**
 * Class Field
 * @package TagMyDoc\OneSpan\Models
 */
class Auth extends Model
{
	const SCHEME_NONE = "NONE";
	const SCHEME_PROVIDER = "PROVIDER";
	const SCHEME_CHALLENGE = "CHALLENGE";
	const SCHEME_SMS = "SMS";
	const SCHEME_SSO = "SSO";
	const SCHEME_KBA = "KBA";
	
	/** @var string */
	protected $scheme = self::SCHEME_NONE;
	
	/** @var AuthChallenge */
	protected $challenges = [];

	public function __construct(?string $scheme = self::SCHEME_NONE)
	{
		$this->withScheme($scheme);
	}

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     * @return Auth
     */
    public function withScheme(string $scheme): Auth
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @return AuthChallenge[]
     */
    public function getChallenges(): array
    {
        return $this->challenges;
    }

    /**
     * @param AuthChallenge[] $challenges
     * @return Auth
     */
    public function withChallenges(array $challenges): Auth
    {
        $this->challenges = $challenges;
        return $this;
    }

}
