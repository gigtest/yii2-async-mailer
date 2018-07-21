<?php
/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */
namespace YarCode\Yii2\AsyncMailer\Tasks;

use bazilio\async\models\AsyncTask;
use YarCode\Yii2\AsyncMailer\Mailer;
use yii\base\InvalidConfigException;
use yii\mail\MessageInterface;
use SMTPValidateEmail\Validator as SmtpEmailValidator;

class SendMessageTask extends AsyncTask
{
    public static $queueName = 'mailer';

    /** @var MessageInterface */
    public $mailMessage;
    /** @var string */
    public $mailerComponent = 'mailer';

    public function setMailMessage($mailMessage)
    {
        if (!$mailMessage instanceof MessageInterface) {
            throw new \InvalidArgumentException('Message must be an instance of ' . MessageInterface::class);
        }
        $this->mailMessage = $mailMessage;
    }

    public function execute()
    {
        $asyncMailer = \Yii::$app->get($this->mailerComponent);
        if (!$asyncMailer instanceof Mailer) {
            throw new InvalidConfigException('Mailer must be an instance of ' . Mailer::class);
        }

        $to = $this->mailMessage->getTo();
        $email     = array_keys($to)[0];
        $sender    = 'info@gigtest.ru';
        $validator = new SmtpEmailValidator($email, $sender);

        // $validator->debug = true;
        $results   = $validator->validate();
        $emailIsValid = $results[$email];
        echo "email is valid: $emailIsValid\n";

        if ($emailIsValid) {
            return $this->mailMessage->send();
            return $asyncMailer->getSyncMailer()->send($this->mailMessage);
        } else {
            return false;
        }
    }
}
