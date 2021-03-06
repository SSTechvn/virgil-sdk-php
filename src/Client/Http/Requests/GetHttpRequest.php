<?php
namespace Virgil\Sdk\Client\Http\Requests;


use Virgil\Sdk\Client\Http\Constants\RequestMethods;

class GetHttpRequest extends AbstractHttpRequest
{
    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return RequestMethods::HTTP_GET;
    }
}
