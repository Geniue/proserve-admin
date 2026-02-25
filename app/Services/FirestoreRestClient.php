<?php

namespace App\Services;

use GuzzleHttp\Client as HttpClient;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FirestoreRestClient
{
    protected ?string $projectId = null;
    protected string $database;
    protected ?string $credentialsPath = null;
    protected HttpClient $http;

    public function __construct(?string $projectId = null, string $database = '(default)')
    {
        $path = (string) (config('firebase.projects.app.credentials')
            ?? env('FIREBASE_CREDENTIALS')
            ?? env('GOOGLE_APPLICATION_CREDENTIALS')
        );
        
        // Ensure path is absolute (for web server context)
        if ($path !== '' && !str_starts_with($path, '/') && !preg_match('/^[A-Z]:/i', $path)) {
            $path = base_path($path);
        }
        $this->credentialsPath = $path !== '' ? $path : null;

        // Defer validation to when a request is actually made; avoid throwing during bootstrap
        if ($projectId) {
            $this->projectId = $projectId;
        } else {
            $this->projectId = env('FIREBASE_PROJECT_ID') ?: null;
            if (!$this->projectId && $this->credentialsPath && is_file($this->credentialsPath)) {
                $json = json_decode((string) file_get_contents($this->credentialsPath), true);
                $this->projectId = $json['project_id'] ?? null;
            }
        }

        $this->database = $database;
        $this->http = new HttpClient([
            'base_uri' => 'https://firestore.googleapis.com/v1/',
            'timeout' => (float) (env('FIREBASE_HTTP_CLIENT_TIMEOUT', 60)),
        ]);
    }

    protected function getAccessToken(): string
    {
        if (!$this->credentialsPath || !is_file($this->credentialsPath)) {
            throw new \RuntimeException('Firebase credentials file not found at: ' . (string) ($this->credentialsPath ?? ''));
        }
        $scopes = ['https://www.googleapis.com/auth/datastore'];
        $creds = new ServiceAccountCredentials($scopes, $this->credentialsPath);
        $token = $creds->fetchAuthToken();
        if (!isset($token['access_token'])) {
            throw new \RuntimeException('Failed to fetch access token for Firestore REST');
        }
        return $token['access_token'];
    }

    public function listDocuments(string $collection, int $pageSize = 50, ?string $pageToken = null): array
    {
        if (!$this->projectId) {
            // Attempt final lazy resolution from credentials file
            if ($this->credentialsPath && is_file($this->credentialsPath)) {
                $json = json_decode((string) file_get_contents($this->credentialsPath), true);
                $this->projectId = $json['project_id'] ?? null;
            }
        }
        if (!$this->projectId) {
            throw new \RuntimeException('Firebase project_id not configured. Set FIREBASE_PROJECT_ID or provide credentials with project_id.');
        }
        $accessToken = $this->getAccessToken();
        $parent = sprintf('projects/%s/databases/%s/documents', $this->projectId, $this->database);
        $path = sprintf('%s/%s', $parent, $collection);

        $query = ['pageSize' => $pageSize];
        if ($pageToken) {
            $query['pageToken'] = $pageToken;
        }

        $resp = $this->http->get($path, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'query' => $query,
        ]);

        $data = json_decode((string) $resp->getBody(), true) ?: [];
        $documents = $data['documents'] ?? [];
        $nextPageToken = $data['nextPageToken'] ?? null;
        return [$documents, $nextPageToken];
    }

    public static function decodeFirestoreValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        if (array_key_exists('stringValue', $value)) return $value['stringValue'];
        if (array_key_exists('integerValue', $value)) return (int) $value['integerValue'];
        if (array_key_exists('doubleValue', $value)) return (float) $value['doubleValue'];
        if (array_key_exists('booleanValue', $value)) return (bool) $value['booleanValue'];
        if (array_key_exists('nullValue', $value)) return null;
        if (array_key_exists('timestampValue', $value)) return $value['timestampValue'];
        if (array_key_exists('referenceValue', $value)) return $value['referenceValue'];
        if (array_key_exists('geoPointValue', $value)) return $value['geoPointValue'];
        if (array_key_exists('arrayValue', $value)) {
            $arr = $value['arrayValue']['values'] ?? [];
            return array_map([self::class, 'decodeFirestoreValue'], $arr);
        }
        if (array_key_exists('mapValue', $value)) {
            $fields = $value['mapValue']['fields'] ?? [];
            $out = [];
            foreach ($fields as $k => $v) {
                $out[$k] = self::decodeFirestoreValue($v);
            }
            return $out;
        }
        // Unknown type, return as-is
        return $value;
    }

    public static function decodeDocument(array $document): array
    {
        $fields = $document['fields'] ?? [];
        $out = [];
        foreach ($fields as $k => $v) {
            $out[$k] = self::decodeFirestoreValue($v);
        }
        return $out;
    }

    /**
     * Encode a PHP value to Firestore format
     */
    public static function encodeFirestoreValue(mixed $value): array
    {
        if (is_null($value)) {
            return ['nullValue' => null];
        }
        if (is_bool($value)) {
            return ['booleanValue' => $value];
        }
        if (is_int($value)) {
            return ['integerValue' => (string) $value];
        }
        if (is_float($value)) {
            return ['doubleValue' => $value];
        }
        if (is_string($value)) {
            return ['stringValue' => $value];
        }
        if (is_array($value)) {
            // Check if associative array (map) or sequential array
            if (array_keys($value) !== range(0, count($value) - 1)) {
                // Associative array -> mapValue
                $fields = [];
                foreach ($value as $k => $v) {
                    $fields[$k] = self::encodeFirestoreValue($v);
                }
                return ['mapValue' => ['fields' => $fields]];
            } else {
                // Sequential array -> arrayValue
                $values = array_map([self::class, 'encodeFirestoreValue'], $value);
                return ['arrayValue' => ['values' => $values]];
            }
        }
        // Default to string representation
        return ['stringValue' => (string) $value];
    }

    /**
     * Set (create or update) a document in Firestore
     */
    public function setDocument(string $collection, string $documentId, array $data, bool $merge = true): array
    {
        $this->ensureProjectId();
        $accessToken = $this->getAccessToken();
        
        $documentPath = sprintf(
            'projects/%s/databases/%s/documents/%s/%s',
            $this->projectId,
            $this->database,
            $collection,
            $documentId
        );

        // Encode data to Firestore format
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[$key] = self::encodeFirestoreValue($value);
        }

        $body = [
            'fields' => $fields,
        ];

        $queryString = '';
        if ($merge) {
            // Build updateMask query string manually for proper formatting
            $masks = [];
            foreach (array_keys($data) as $field) {
                $masks[] = 'updateMask.fieldPaths=' . urlencode($field);
            }
            $queryString = implode('&', $masks);
        }

        $url = $documentPath . ($queryString ? '?' . $queryString : '');

        $resp = $this->http->patch($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ],
            'json' => $body,
        ]);

        return json_decode((string) $resp->getBody(), true) ?: [];
    }

    /**
     * Delete a document from Firestore
     */
    public function deleteDocument(string $collection, string $documentId): void
    {
        $this->ensureProjectId();
        $accessToken = $this->getAccessToken();
        
        $documentPath = sprintf(
            'projects/%s/databases/%s/documents/%s/%s',
            $this->projectId,
            $this->database,
            $collection,
            $documentId
        );

        $this->http->delete($documentPath, [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
        ]);
    }

    /**
     * Ensure project ID is available
     */
    protected function ensureProjectId(): void
    {
        if (!$this->projectId) {
            if ($this->credentialsPath && is_file($this->credentialsPath)) {
                $json = json_decode((string) file_get_contents($this->credentialsPath), true);
                $this->projectId = $json['project_id'] ?? null;
            }
        }
        if (!$this->projectId) {
            throw new \RuntimeException('Firebase project_id not configured. Set FIREBASE_PROJECT_ID or provide credentials with project_id.');
        }
    }
}
