<?php
namespace Virgil\Sdk\Client\Card;


use Virgil\Sdk\Client\Card\Mapper\ModelMappersCollectionInterface;
use Virgil\Sdk\Client\Card\Model\ErrorResponseModel;
use Virgil\Sdk\Client\Card\Model\RevokeCardContentModel;
use Virgil\Sdk\Client\Card\Model\SearchCriteria;
use Virgil\Sdk\Client\Card\Model\SignedRequestModel;
use Virgil\Sdk\Client\Http\ClientInterface;
use Virgil\Sdk\Client\Http\ResponseInterface;

class CardsService implements CardsServiceInterface
{
    const DEFAULT_ERROR_MESSAGES = [
        400 => 'Request error',
        401 => 'Authentication error',
        403 => 'Forbidden',
        404 => 'Entity not found',
        405 => 'Method not allowed',
        500 => 'Server error',
    ];

    private $httpClient;
    private $mappers;
    private $params;


    /**
     * CardsService constructor.
     *
     * @param CardServiceParamsInterface      $params
     * @param ClientInterface                 $httpClient
     * @param ModelMappersCollectionInterface $mappers
     */
    public function __construct(
        CardServiceParamsInterface $params,
        ClientInterface $httpClient,
        ModelMappersCollectionInterface $mappers
    ) {
        $this->httpClient = $httpClient;
        $this->mappers = $mappers;
        $this->params = $params;
    }


    /**
     * @inheritdoc
     */
    public function create(SignedRequestModel $model)
    {
        $request = function () use ($model) {
            return $this->httpClient->post(
                $this->params->getCreateEndpoint(),
                $this->mappers->getSignedRequestModelMapper()->toJson($model)
            );
        };

        $response = $this->makeRequest($request);

        return $this->mappers->getSignedResponseModelMapper()->toModel($response->getBody());
    }


    /**
     * @inheritdoc
     */
    public function delete(SignedRequestModel $model)
    {
        $request = function () use ($model) {
            /** @var RevokeCardContentModel $cardContent */
            $cardContent = $model->getCardContent();

            return $this->httpClient->delete(
                $this->params->getDeleteEndpoint($cardContent->getId()),
                $this->mappers->getSignedRequestModelMapper()->toJson($model)
            );
        };

        $this->makeRequest($request);
    }


    /**
     * @inheritdoc
     */
    public function search(SearchCriteria $model)
    {
        $request = function () use ($model) {
            return $this->httpClient->post(
                $this->params->getSearchEndpoint(),
                $this->mappers->getSearchCriteriaRequestMapper()->toJson($model)
            );
        };

        $response = $this->makeRequest($request);

        return $this->mappers->getSearchCriteriaResponseMapper()->toModel($response->getBody());
    }


    /**
     * @inheritdoc
     */
    public function get($id)
    {
        $request = function () use ($id) {
            return $this->httpClient->get($this->params->getGetEndpoint($id));
        };

        $response = $this->makeRequest($request);

        return $this->mappers->getSignedResponseModelMapper()->toModel($response->getBody());
    }


    /**
     * Makes request to http client and gets response object.
     *
     * @param callable $request
     *
     * @throws CardsServiceException
     * @return ResponseInterface
     */
    protected function makeRequest($request)
    {
        /** @var ResponseInterface $result */
        $result = call_user_func($request);

        if (!$result->getHttpStatus()->isSuccess()) {
            /** @var ErrorResponseModel $response */
            $response = $this->mappers->getErrorResponseModelMapper()->toModel($result->getBody());

            $httpStatus = (int)$result->getHttpStatus()->getStatus();
            $serviceErrorMessage = $response->getMessageOrDefault(self::DEFAULT_ERROR_MESSAGES[$httpStatus]);

            throw new CardsServiceException($serviceErrorMessage, $httpStatus);
        }

        return $result;
    }
}
