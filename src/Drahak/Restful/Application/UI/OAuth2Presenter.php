<?php
namespace Drahak\Restful\Application\UI;

use Drahak\OAuth2\Application\IOAuthPresenter;
use Drahak\OAuth2\Grant\GrantContext;
use Drahak\OAuth2\Grant\GrantType;
use Drahak\OAuth2\Grant\IGrant;
use Drahak\OAuth2\InvalidGrantException;
use Drahak\OAuth2\InvalidStateException;
use Drahak\OAuth2\OAuthException;
use Drahak\OAuth2\Storage\AuthorizationCodes\AuthorizationCodeFacade;
use Drahak\OAuth2\Storage\Clients\IClient;
use Drahak\OAuth2\Storage\Clients\IClientStorage;
use Drahak\OAuth2\Storage\TokenException;
use Drahak\OAuth2\UnauthorizedClientException;
use Drahak\OAuth2\UnsupportedResponseTypeException;
use Nette\Http\Url;

/**
 * OAuth2Presenter
 * @package Drahak\Restful\Application
 * @author Drahomír Hanák
 *
 * @property-read IGrant $grantType
 */
class OAuth2Presenter extends ResourcePresenter implements IOAuthPresenter
{

	/** @var GrantContext */
	private $grantContext;

	/** @var AuthorizationCodeFacade */
	protected $authorizationCode;

	/** @var IClientStorage */
	protected $clientStorage;

	/** @var IClient */
	protected $client;

	/**
	 * Inject OAuth2 presenter dependencies
	 * @param GrantContext $grantContext
	 * @param AuthorizationCodeFacade $authorizationCode
	 * @param IClientStorage $clientStorage
	 */
	public final function injectOauth2(
		GrantContext $grantContext, AuthorizationCodeFacade $authorizationCode,
		IClientStorage $clientStorage)
	{
		$this->grantContext = $grantContext;
		$this->clientStorage = $clientStorage;
		$this->authorizationCode = $authorizationCode;
	}

	/**
	 * On presenter startup
	 */
	protected function startup()
	{
		parent::startup();
		$this->client = $this->clientStorage->getClient(
			$this->getParameter(GrantType::CLIENT_ID_KEY),
			$this->getParameter(GrantType::CLIENT_SECRET_KEY)
		);
	}

	/**
	 * Get grant type
	 * @return IGrant
	 * @throws UnsupportedResponseTypeException
	 */
	public function getGrantType()
	{
		$request = $this->getHttpRequest();
		$grantType = $request->getPost(GrantType::GRANT_TYPE_KEY);
		try {
			return $this->grantContext->getGrantType($grantType);
		} catch (InvalidStateException $e) {
			throw new UnsupportedResponseTypeException('Trying to use unknown grant type ' . $grantType, $e);
		}
	}

	/**
	 * Provide OAuth2 error response (redirect or at least JSON)
	 * @param OAuthException $exception
	 */
	public function oauthError(OAuthException $exception)
	{
		$error = array(
			'error' => $exception->getKey(),
			'error_description' => $exception->getMessage()
		);
		$this->oauthResponse($error, $this->getParameter('redirect_uri'), $exception->getCode());
	}

	/**
	 * Send OAuth response
	 * @param array|\Traversable $data
	 * @param string|null $redirectUrl
	 * @param int $code
	 */
	public function oauthResponse($data, $redirectUrl = NULL, $code = 200)
	{
		if ($data instanceof \Traversable) {
			$data = iterator_to_array($data);
		}
		$data = (array)$data;

		// Redirect, if there is URL
		if ($redirectUrl !== NULL) {
			$url = new Url($redirectUrl);
			if ($this->getParameter('response_type') == 'token') {
				$url->setFragment(http_build_query($data));
			} else {
				$url->appendQuery($data);
			}
			$this->redirectUrl($url);
		}

		// else send JSON response
		foreach ($data as $key => $value) {
			$this->resource->$key = $value;
		}
		$this->sendResource(NULL, $code);
	}


	/**************** OAuth2 presenter methods ****************/

	/**
	 * Issue an authorization code
	 * @param string $responseType
	 * @param string $redirectUrl
	 * @param string|null $scope
	 * @return void
	 *
	 * @throws UnauthorizedClientException
	 * @throws UnsupportedResponseTypeException
	 */
	public function issueAuthorizationCode($responseType, $redirectUrl, $scope = NULL)
	{
		try {
			if ($responseType !== 'code') {
				throw new UnsupportedResponseTypeException;
			}
			if (!$this->client->getId()) {
				throw new UnauthorizedClientException;
			}

			$scope = array_filter(explode(',', str_replace(' ', ',', $scope)));
			$code = $this->authorizationCode->create($this->client, $this->user->getId(), $scope);
			$data = array(
				'code' => $code->getAuthorizationCode()
			);
			$this->oauthResponse($data, $redirectUrl);
		} catch (OAuthException $e) {
			$this->oauthError($e);
		} catch (TokenException $e) {
			$this->oauthError(new InvalidGrantException());
		}
	}

	/**
	 * Issue an access token
	 * @param string|null $grantType
	 * @param string|null $redirectUrl
	 */
	public function issueAccessToken($grantType = NULL, $redirectUrl = NULL)
	{
		try {
			if ($grantType !== NULL) {
				$grantType = $this->grantContext->getGrantType($grantType);
			} else {
				$grantType = $this->getGrantType();
			}

			$response = $grantType->getAccessToken($this->getHttpRequest());
			$this->oauthResponse($response, $redirectUrl);
		} catch (OAuthException $e) {
			$this->oauthError($e);
		} catch (TokenException $e) {
			$this->oauthError(new InvalidGrantException);
		}
	}


}
