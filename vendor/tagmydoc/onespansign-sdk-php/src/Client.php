<?php

namespace alexdemers\OneSpanSign;

use alexdemers\OneSpanSign\Models\Approval;
use alexdemers\OneSpanSign\Models\Callback;
use alexdemers\OneSpanSign\Models\Role;
use alexdemers\OneSpanSign\Models\Document;
use alexdemers\OneSpanSign\Models\Package;
use alexdemers\OneSpanSign\Models\Signer;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Karriere\JsonDecoder\JsonDecoder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use alexdemers\OneSpanSign\Models\Transformers\ApprovalTransformer;
use alexdemers\OneSpanSign\Models\Transformers\AuthTransformer;
use alexdemers\OneSpanSign\Models\Transformers\DocumentTransformer;
use alexdemers\OneSpanSign\Models\Transformers\PackageTransformer;
use alexdemers\OneSpanSign\Models\Transformers\PageTransformer;
use alexdemers\OneSpanSign\Models\Transformers\RoleTransformer;
use alexdemers\OneSpanSign\Models\Transformers\SignerTransformer;

/**
 * Class Client
 * @package TagMyDoc\OneSpan
 */
class Client
{
	const VERSION = 11.27;

	/**
	 * @var \GuzzleHttp\Client
	 */
	protected $client;

	/**
	 * @var string
	 */
	protected $apiKey;

	/**
	 * @var string
	 */
	protected $baseUrl;

	/**
	 * @return string
	 */
	public function getBaseUrl(): string
	{
		return $this->baseUrl;
	}

	/**
	 * Known API urls
	 *
	 * @var array
	 */
	public static $API_URL = [
		'us-1' => 'https://apps.e-signlive.com',
		'us-1-sandbox' => 'https://sandbox.e-signlive.com',
		'us-2' => 'https://apps.esignlive.com',
		'us-2-sandbox' => 'https://sandbox.esignlive.com',
		'ca-1' => 'https://apps.e-signlive.ca',
		'ca-1-sandbox' => 'https://sandbox.e-signlive.ca',
		'au-1' => 'https://apps.esignlive.com.au',
		'eu-1' => 'https://apps.esignlive.eu',
	];

	/**
	 * Client constructor.
	 * @param string|null $api_key
	 * @param string|null $base_url
	 */
	public function __construct(?string $api_key = null, ?string $base_url = null)
	{
		$this->apiKey = $this->setApiKey($api_key);
		$this->baseUrl = $base_url;
		$this->client = new \GuzzleHttp\Client();
	}

	/**
	 * Sets the API key
	 *
	 * @param string|null $api_key
	 * @return $this
	 */
	public function setApiKey(?string $api_key = null): self {
		$this->apiKey = $api_key;
		return $this;
	}

	/**
	 * Sets the API URL
	 *
	 * @param string $server_code  One of the keys of Client::$API_URL
	 * @return $this
	 */
	public function setApiServer(string $server_code): self {
		$this->baseUrl = self::$API_URL[$server_code];
		return $this;
	}

	/**
	 * @param array $fields
	 * @return Package[]
	 * @throws GuzzleException
	 */
	public function listPackages($fields = []): array
	{
		return $this->parseResponse($this->sendRequest('get', 'packages', [
			'query' => $fields
		]), Package::class, true);
	}

	/**
	 * Creates a new document package from scratch.
	 *
	 * @param Package $package
	 * @return Package Package Id
	 * @throws GuzzleException
	 */
	public function createPackage(Package $package): Package
	{
		$response = $this->parseResponse($this->sendRequest('post', 'packages', ['json' => $package]));
		$package->withId($response->id);
		return $package;
	}

	/**
	 * Creates a new document package from scratch.
	 *
	 * @param Package $package
	 * @param Package $template
	 * @return Package Package Id
	 * @throws GuzzleException
	 */
	public function createPackageFromTemplate(Package $package, Package $template): Package
	{
		$response = $this->parseResponse($this->sendRequest('post', "packages/{$template->getId()}/clone", ['json' => $package]));
		$package->withId($response->id);
		return $package;
	}

	/**
	 * Creates a new package, together with document binaries.
	 *
	 * @param Package $package
	 * @param bool $send_for_signing
	 * @return Package Package Id
	 * @throws GuzzleException
	 */
	public function createPackageWithDocuments(Package $package, bool $send_for_signing = false): Package
	{
		$files = [];

		/**
		 * @var string $path
		 * @var Document $document
		 */
		foreach ($package->getDocuments() as $path => $document) {

			$files[] = [
				'name' => 'file',
				'contents' => fopen($path, 'r'),
				'filename' => pathinfo($path, PATHINFO_BASENAME)
			];

			if (empty($document->getName())) {
				$document->withName(pathinfo($path, PATHINFO_BASENAME));
			}
		}

		$package->withDocuments(array_values($package->getDocuments()));

		$response = $this->parseResponse($this->sendRequest('post', 'packages', [
			'multipart' => array_merge([['name' => 'payload', 'contents' => json_encode($package)]], $files)
		]));

		$package = $this->getPackage($response->id);

		if ($send_for_signing) {
			$package = $this->sendPackageForSigning($package);
		}

		return $package;
	}

    /**
     * Deletes a specified package or template.
     *
     * @param Package $package  The unique package id.
     * @return bool
     * @throws GuzzleException
     */
	public function deletePackage(Package $package): bool
    {
        return $this->sendRequest('delete', "packages/{$package->getId()}")->getStatusCode() === 200;
    }

	/**
	 * Deletes multiple existing documents from a specified package. Documents are identified by the documentID.
	 *
	 * @param Package $package  The unique package id.
	 * @param array $documentIds  List of document ids.
	 * @return bool
	 * @throws GuzzleException
	 */
	public function deleteDocumentsFromPackage(Package $package, array $documentIds): bool
	{
		return $this->sendRequest('delete', "packages/{$package->getId()}/documents", [
			'json' => $documentIds
		])->getStatusCode() === 200;
	}

    /**
     * Returns a URL for the designer view of a package. Status needs to be DRAFT for this to work
     *
     * @param Package $package
     * @param bool $classic_designer
     * @param array $options
     * @param null $base_url
     * @return string
     * @throws GuzzleException
     */
	public function getPackageDesignerUrl(Package $package, $classic_designer = false, $options = [], $base_url = null)
	{
		$token = $this->parseResponse($this->sendRequest('post', 'authenticationTokens/sender', [
			'json' => ['packageId' => $package->getId()]
		]))->value;

		if ($base_url === null) {
			$base_url = $this->baseUrl;
        }

		if ($classic_designer) {
			return sprintf('%s/auth?authenticationToken=%s&target=/designer/%s', $base_url, rawurlencode($token), $package->getId());
		}

		return sprintf('%s/auth?senderAuthenticationToken=%s&target=/a/transaction/%s/designer', $base_url, rawurlencode($token), $package->getId());
	}

	/**
	 * Retrieves Evidence Summary information for a specified package.
	 *
     * @param Package $package The package
     * @param string $filename
     * @return ResponseInterface
     * @throws GuzzleException
	 */
	public function getPackageEvidenceSummary(Package $package, string $filename)
	{
		return $this->sendRequest('get', "packages/{$package->getId()}/evidence/summary", [
		    'sink' => $filename
        ], "application/pdf");
	}

    /**
     * Reorders the roles in a specified package.
     *
     * @param Package $package  The unique package id.
     * @param array $roles  The new role data. If the id field is not set, it will be generated by the system.
     * @return array
     * @throws GuzzleException
     */
	public function updateRolesOfPackage(Package $package, $roles): array
    {
        if ($roles instanceof Collection) {
            $roles = $roles->toArray();
        }

        return $this->parseResponse($this->sendRequest('put', "packages/{$package->getId()}/roles", [
            'json' => $roles
        ]), Role::class, true);
    }

	/**
	 * Uploads a document to a package
	 *
	 * @param Package $package
	 * @param string $path
	 * @param Document $document
	 * @return Document
	 * @throws GuzzleException
	 */
	public function addDocumentToPackage(Package $package, string $path, Document $document): Document
	{
		return $this->parseResponse($this->sendRequest('post', "packages/{$package->getId()}/documents", [
			'multipart' => [
				[
					'name' => 'payload',
					'contents' => json_encode($document)
				],
				[
					'name' => 'file',
					'contents' => fopen($path, 'r'),
					'filename' => pathinfo($path, PATHINFO_BASENAME)
				]
			]
		], 'text/html'), Document::class);
	}

	/**
	 * Gets the packages roles
	 *
	 * @param Package $package
	 * @return array
	 * @throws GuzzleException
	 */
	public function getPackageRoles(Package $package): array
	{
		return $this->parseResponse($this->sendRequest('get', "packages/{$package->getId()}/roles"), Role::class, true);
	}

	/**
	 * Adds a new role to an existing package.
	 *
	 * @param Package $package
	 * @param Role $role
	 * @return Role
	 * @throws GuzzleException
	 */
	public function addRoleToPackage(Package $package, Role $role): Role
	{
		return $this->parseResponse($this->sendRequest('post', "packages/{$package->getId()}/roles", [
			'json' => $role
		]), Role::class);
	}

	/**
	 * Updates a new role to an existing package.
	 *
	 * @param Package $package
	 * @param Role $exitingRole
	 * @param Role $newRole
	 * @return Role
	 * @throws GuzzleException
	 */
	public function updateRoleToPackage(Package $package, Role $exitingRole, Role $newRole): Role
	{
		return $this->parseResponse($this->sendRequest('put', "packages/{$package->getId()}/roles/{$exitingRole->getId()}", [
			'json' => $newRole
		]), Role::class);
	}

	/**
	 * Removes a role to an existing package.
	 *
	 * @param Package $package
	 * @param Role $role
	 * @return bool
	 * @throws GuzzleException
	 */
	public function removeRoleFromPackage(Package $package, Role $role): bool
	{
		return $this->sendRequest('delete', "packages/{$package->getId()}/roles/{$role->getId()}")->getStatusCode() === 200;
	}

	/**
	 * @param Package $package
	 * @return bool
	 * @throws GuzzleException
	 */
	public function sendPackageForSigning(Package $package): bool
	{
		return $this->updatePackage(Package::createFromId($package->getId())->withStatus('SENT'));
	}

	/**
	 * Retrieve the URL that a signer can use to sign a package. This can only be done to a package which is not
	 * modifiable (e.g., a sent package). It cannot be done to a package that is in a draft state.
	 *
	 * @param Package $package
	 * @param Role $role
	 * @return string
	 * @throws GuzzleException
	 */
	public function getSigningUrl(Package $package, Role $role): string
	{
		return $this->parseResponse($this->sendRequest('get', "packages/{$package->getId()}/roles/{$role->getId()}/signingUrl"))->url;
	}

	/**
	 * @param Package $package
	 * @param Signer $signer
	 * @return string
	 * @throws GuzzleException
	 */
	private function getSessionToken(Package $package, Signer $signer): string
	{
		return $this->parseResponse($this->sendRequest('post', 'sessions', [
			'query' => [
				'package' => $package->getId(),
				'signer' => $signer->getId()
			]
		]))->sessionToken;
	}

	/**
	 * @return string
	 * @throws GuzzleException
	 */
	public function getAuthenticationToken(): string
	{
		return $this->parseResponse($this->sendRequest('post', 'authenticationTokens'))->value;
	}

	/**
	 * @param string $intended_url
	 * @return string
	 * @throws GuzzleException
	 */
	public function getAuthUrl(string $intended_url): string
	{
		return sprintf('auth?authenticationToken=%s&target=%s', rawurlencode($this->getAuthenticationToken()), rawurlencode($intended_url));
	}

    /**
     * Retrieves a specific document. This function always returns the PDF version of the document, even if the
     * document was originally submitted in a different format.
     *
     * @param Package $package
     * @param Document $document
     * @param string $filename
     * @return ResponseInterface
     * @throws GuzzleException
     */
	public function getDocumentAsPdf(Package $package, Document $document, string $filename): ResponseInterface
	{
		return $this->sendRequest('get', "packages/{$package->getId()}/documents/{$document->getId()}/pdf", [
		    'sink' => $filename
        ]);
	}

	/**
	 * Retrieves a zipped file that contains all documents that were added to the package.
	 *
	 * Can only obtain ZIP once package is COMPLETED
	 *
	 * @param Package $package
	 * @param bool $flatten Flatten the pdf.
	 * @return StreamInterface
	 * @throws GuzzleException
	 */
	public function getZip(Package $package, bool $flatten = false): StreamInterface
	{
		return $this->sendRequest('get', "packages/{$package->getId()}/documents/zip", [
			'query' => [
				'flatten' => var_export($flatten, true)
			]
		])->getBody();
	}

	/**
	 * Send an email notification to the signer(s) linked to a Role.
	 *
	 * @param Package $package
	 * @param Role $role
	 * @return bool
	 * @throws GuzzleException
	 */
	public function sendNotification(Package $package, Role $role): bool
	{
		$this->sendRequest('post', "packages/{$package->getId()}/roles/{$role->getId()}/notifications");
		return true;
	}

	/**
	 * Retrieve information about a single document package.
	 *
	 * @param string|Package $package Can be a Package object with its Id defined, or a string for its id
	 * @return Package
	 * @throws GuzzleException
	 */
	public function getPackage($package): Package
	{
		if (is_string($package)) {
			$package = Package::createFromId($package);
		}

		return $this->parseResponse($this->sendRequest('get', "packages/{$package->getId()}"), Package::class);
	}

	/**
	 * Update the information about a document package.
	 *
	 * @param Package $package
	 * @return bool
	 * @throws GuzzleException
	 */
	public function updatePackage(Package $package): bool
	{
		return $this->sendRequest('put', 'packages/' . $package->getId(), ['json' => $package])->getStatusCode() === 200;
	}

	/**
	 * Updates the approval requests that already exist on a package, or a document.
	 *
	 * @param Package $package
	 * @param Document $document
	 * @param array $approvals
	 * @return bool
	 * @throws GuzzleException
	 */
	public function updateDocumentApprovals(Package $package, Document $document, array $approvals): bool
	{
		return $this->sendRequest('put', "packages/{$package->getId()}/documents/{$document->getId()}/approvals", [
			'json' => $approvals
		])->getStatusCode() === 200;
	}


	/**
	 * @param Callback $callback
	 * @return bool
	 * @throws GuzzleException
	 */
	public function updateCallback(Callback $callback): bool
	{
		return $this->sendRequest('post', 'callback', ['json' => $callback])->getStatusCode() === 200;
	}

	/**
	 * @param string $method
	 * @param string $endpoint Do not include a slash at the beginning
	 * @param array $options
	 * @param string $accept
	 * @return ResponseInterface
	 * @throws GuzzleException
	 */
	public function sendRequest(string $method, string $endpoint, array $options = [], string $accept = 'application/json'): ResponseInterface
	{
		$headers = [
			'Authorization' => 'Basic ' . $this->apiKey,
			'Accept' => sprintf('%s; esl-api-version=%s', $accept, self::VERSION)
		];

		return $this->client->request($method, sprintf('%s/api/%s', $this->baseUrl, $endpoint), array_merge_recursive($options, [
			'headers' => $headers
		]));
	}

	/**
	 * @param ResponseInterface $response
	 * @param string $class
	 * @param bool $is_array
	 * @return mixed
	 */
	protected function parseResponse(ResponseInterface $response, string $class = null, bool $is_array = false)
	{
		if ($response->getBody()->getSize() === 0) {
			return null;
		}

		if ($class !== null) {

			$decoder = new JsonDecoder(false, true);
			$decoder->register(new PackageTransformer());
			$decoder->register(new RoleTransformer());
			$decoder->register(new SignerTransformer());
			$decoder->register(new DocumentTransformer());
			$decoder->register(new ApprovalTransformer());
			$decoder->register(new AuthTransformer());

			if ($is_array) {
				$payload = json_decode($response->getBody()->getContents());
				return $decoder->decodeMultiple(json_encode($payload->results), $class);
			}

			return $decoder->decode($response->getBody()->getContents(), $class);
		}

		$payload = json_decode($response->getBody()->getContents());

		if (json_last_error() === JSON_ERROR_NONE) {
			return $payload;
		}

		return $response;
	}

	/**
	 * @param Package $package
	 * @param Signer $signer
	 * @return string
	 * @throws GuzzleException
	 */
	public function getSessionUrl(Package $package, Signer $signer): string
	{
		return sprintf('%s/%s?sessionToken=%s', $this->baseUrl, 'access', rawurlencode($this->getSessionToken($package->getId(), $signer->getId())));
	}
}
