<?php

namespace Virgil\SDK\Client;


use Virgil\SDK\BufferInterface;
use Virgil\SDK\Client\Card\Model\SignedRequestMetaModel;
use Virgil\SDK\Client\Card\Model\SignedRequestModel;

abstract class AbstractCardRequest
{
    protected $signatures = [];

    /**
     * Gets the card content.
     * @return mixed
     */
    protected abstract function getCardContent();

    /**
     * Gets the request model.
     * @return SignedRequestModel
     */
    public function getRequestModel()
    {
        return new SignedRequestModel($this->getCardContent(), $this->getCardMeta());
    }

    /**
     * Append signature to request.
     * @param string $signatureId
     * @param BufferInterface $signature
     */
    public function appendSignature($signatureId, BufferInterface $signature)
    {
        $this->signatures[$signatureId] = $signature;
    }

    /**
     * Gets the signatures.
     * @return array
     */
    public function getSignatures()
    {
        return $this->signatures;
    }

    /**
     * Gets card request snapshot.
     * @return string
     */
    public function snapshot()
    {
        return $this->getRequestModel()->getSnapshot();
    }

    /**
     * Gets the card meta.
     * @return SignedRequestMetaModel
     */
    protected function getCardMeta()
    {
        return new SignedRequestMetaModel(
            call_user_func(function ($signatures) {
                /** @var BufferInterface $signature */
                foreach ($signatures as &$signature) {
                    $signature = $signature->toBase64();
                }
                return $signatures;
            }, $this->signatures)
        );
    }
}