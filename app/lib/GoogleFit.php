<?php
namespace GoogleFit;

//copie à l'arrache du fichier d'api fitbit app/vendor/thesavior/fitbit/lib/Fitbit/Api.php
class Api
{
    protected $service;

    protected $oauthToken;
    protected $oauthSecret;


    public function __construct($consumer_key, $consumer_secret, $callbackUrl = null, \OAuth\Common\Storage\TokenStorageInterface $storageAdapter = null)
    {
        if ($callbackUrl == null) {
            $uriFactory = new \OAuth\Common\Http\Uri\UriFactory();
            $currentUri = $uriFactory->createFromSuperGlobalArray($_SERVER);
            $currentUri->setQuery('');
            $callbackUrl = $currentUri->getAbsoluteUri();
        }

        $factory = new \OAuth\ServiceFactory();

        $credentials = new \OAuth\Common\Consumer\Credentials(
            $consumer_key,
            $consumer_secret,
            $callbackUrl
        );

        if ($storageAdapter == null)
        {
            $storageAdapter = new \OAuth\Common\Storage\Session();
        }

        $this->service = $factory->createService('google', $credentials, $storageAdapter, ['fitness_activity_read']);
    }

    public function isAuthorized() {
        return $this->service->getStorage()->hasAccessToken($this->service->service());
    }

    /**
    * Authorize the user
    *
    */
    public function initSession() {
        if ($this->isAuthorized())
            return 1;

        if (empty($_SESSION['googlefit_session']))
            $_SESSION['googlefit_session'] = 0;

        if (!isset($_GET['code']) && $_SESSION['googlefit_session'] == 1)
            $_SESSION['googlefit_session'] = 0;

        if ($_SESSION['googlefit_session'] == 0) {

            $url = $this->service->getAuthorizationUri();

            $_SESSION['googlefit_session'] = 1;
            header('Location: ' . $url);
            exit;

        } else if ($_SESSION['googlefit_session'] == 1) {

            $this->service->requestAccessToken($_GET['code']);

            $_SESSION['googlefit_session'] = 2;

            return 1;

        }

        return 0;
    }

    /**
     * Reset session
     *
     * @return void
     */
    public function resetSession()
    {
        // TODO: Need to add clear to the interface for phpoauthlib
        $this->service->getStorage()->clearToken($this->service->service());
        unset($_SESSION["googlefit_session"]);
    }

    protected function verifyToken()
    {
        if(!$this->isAuthorized()) {
            throw new \Exception("You must be authorized to make requests");
        }
    }

    /**
     * Make custom call to any API endpoint
     *
     * @param string $url Endpoint url after '.../1/'
     * @param string $method (OAUTH_HTTP_METHOD_GET, OAUTH_HTTP_METHOD_POST, OAUTH_HTTP_METHOD_PUT, OAUTH_HTTP_METHOD_DELETE)
     * @param array $body Request body
     * @param array $userHeaders Additional custom headers
     * @return Response
     */
    public function req($url, $method = 'GET', $body = null, $userHeaders = array())
    {
        try {
            $response = $this->service->request($url, $method, $body, $userHeaders);
            return $this->parseResponse($response);
        } catch (\OAuth\Common\Token\Exception\ExpiredTokenException $e) {
            $this->resetSession();
            $this->initSession();
            $this->req($url, $method, $body, $userHeaders);
        }
    }

    /**
     * @return value encoded in json as an object
     */
    private function parseResponse($response)
    {
        return json_decode($response, true);
    }
}
