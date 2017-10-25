<?php

namespace AppBundle\MailWorkflow\Model;

class EmailRecipientList
{
    /** @var EmailContact[] */
    private $emailList;

    /**
     * @param EmailContact[]|EmailContact $emailList
     */
    public function __construct($emailList)
    {
        if (!is_array($emailList)) {
            $emailList = [$emailList];
        }

        $this->emailList = $emailList;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $emails = [];

        foreach ($this->emailList as $emailContact) {
            if (!$emailContact instanceof EmailContact) {
                continue;
            }

            if ($emailContact->getName()) {
                $emails[$emailContact->getEmail()] = $emailContact->getName();
            } else {
                $emails[] = $emailContact->getEmail();
            }
        }

        return $emails;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode('; ', $this->toArray());
    }
}
