<?php

namespace App\Services;

use GuzzleHttp\Client as HttpClient;
use Google\Auth\Credentials\ServiceAccountCredentials;

class FirestoreRestClient
{
    protected string $projectId;
    protected string $database;
    protected string $credentialsPath;
    protected HttpClient $http;

    public function __construct(?string $projectId = null, string $database = '(default)')
    {
        $this->credentialsPath = (string) (config('firebase.projects.app.credentials')
            ?? env('FIREBASE_CREDENTIALS')
            ?? env('GOOGLE_APPLICATION_CREDENTIALS')
        );

        if (!is_file($this->credentialsPath)) {
            throw new \RuntimeException('Firebase credentials file not found at: ' . $this->credentialsPath);
        }

        if ($projectId) {
            $this->projectId = $projectId;
        } else {
            $json = json_decode((string) file_get_contents($this->credentialsPath), true);
            $this->projectId = $json['project_id'] ?? throw new \RuntimeException('project_id missing in credentials');
        }

        $this->database = $database;
        $this->http = new HttpClient([
            'base_uri' => 'https://firestore.googleapis.com/v1/',
            'timeout' => (float) (env('FIREBASE_HTTP_CLIENT_TIMEOUT', 60)),
        ]);
    }

    protected function getAccessToken(): string
    {
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
}
