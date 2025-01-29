<?php


namespace alexdemers\OneSpanSign\Models;

/**
 * Class Field
 * @package TagMyDoc\OneSpan\Models
 */
class SmsAuthChallenge extends AuthChallenge
{
    /**
     * @param string $phoneNumber
     */
    public function __construct(string $phoneNumber)
    {
        $this->withQuestion($phoneNumber);
    }
}
