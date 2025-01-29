<?php


namespace alexdemers\OneSpanSign\Models;

/**
 * Class Field
 * @package TagMyDoc\OneSpan\Models
 */
class AuthChallenge extends Model
{
    /** @var ?string */
    protected $question = null;

    /** @var ?string */
    protected $answer = null;

    /** @var boolean */
    protected $maskInput = false;

    /**
     * @return string|null
     */
    public function getQuestion(): ?string
    {
        return $this->question;
    }

    /**
     * @param string|null $question
     * @return AuthChallenge
     */
    public function withQuestion(?string $question): AuthChallenge
    {
        $this->question = $question;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    /**
     * @param string|null $answer
     * @return AuthChallenge
     */
    public function withAnswer(?string $answer): AuthChallenge
    {
        $this->answer = $answer;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMaskInput(): bool
    {
        return $this->maskInput;
    }

    /**
     * @param bool $maskInput
     * @return AuthChallenge
     */
    public function withMaskInput(bool $maskInput): AuthChallenge
    {
        $this->maskInput = $maskInput;
        return $this;
    }

}
