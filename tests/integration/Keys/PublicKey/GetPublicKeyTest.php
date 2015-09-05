<?php

use Virgil\Crypto\VirgilKeyPair;

class GetPublicKeyTest extends PHPUnit_Framework_TestCase {


    public function test_Should_Get_PublicKey() {

        PublicKeyHelper::setupPublicKey();

        $publicKey = PublicKeyHelper::create(
            Constants::VIRGIL_PRIVATE_KEY,
            Constants::VIRGIL_PUBLIC_KEY
        );

        sleep(5);

        $mailClient = new MailinatorHelper(
            Constants::VIRGIL_MAILINATOR_TOKEN
        );

        $messages = $mailClient->fetchInbox(
            Constants::VIRGIL_USER_DATA_VALUE
        );
        $message  = array_pop($messages);
        $messageContent = $mailClient->fetchMail(
            $message['id']
        );

        preg_match(
            '/<b style="font-weight: bold;">([0-9a-z]{6})<\/b>/i',
            $messageContent['parts'][0]['body'],
            $matches
        );

        UserDataHelper::persist(
            $publicKey->userData->get(0)->id->userDataId,
            trim($matches[1])
        );

        $publicKey = PublicKeyHelper::get(
            $publicKey->publicKeyId
        );

        $this->assertEquals(
            Constants::VIRGIL_PUBLIC_KEY,
            $publicKey->publicKey
        );
    }
}
