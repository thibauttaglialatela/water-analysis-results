<?php

declare(strict_types=1);

namespace App\Service;

use DateInterval;
use DateTimeImmutable;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallApiHubeau
{
    private const ENDPOINT = 'v1/qualite_eau_potable/';

    public function __construct(private HttpClientInterface $client, private string $baseApiUrl)
    {
    }

    public function fetchOneCityByName(string $cityName): array
    {
        if ('' === trim($cityName)) {
            throw new \InvalidArgumentException("Le nom de la ville ne peut être vide");
        }

        return $this->request('communes_udi', ['nom_commune' => $cityName]);

    }

    public function findCityNameByInseeCode(string $inseeCode): string
    {
        if ('' === trim($inseeCode)) {
            throw new \InvalidArgumentException("Vous devez fournir le numéro INSEE de la ville recherchée");
        }

        return $this->request('communes_udi', ['code_commune' => $inseeCode, 'size' => 1])['data'][0]["nom_commune"];
    }

    public function getInseeCode(string $cityName): string
    {
        $slugger = new AsciiSlugger();
        $normalizedCityName = $slugger->slug($cityName)->toString();
        $commune = strtoupper($normalizedCityName);
        $cityList = $this->fetchOneCityByName($commune);

        foreach ($cityList['data'] ?? [] as $city) {
            if (isset($city['nom_commune'], $city['code_commune']) && $commune === $city['nom_commune']) {
                return $city['code_commune'];
            }

        }

        throw new \RuntimeException(sprintf(
            '%s n\'existe pas ou aucun code INSEE trouvé.', $cityName
        ));

    }

    public function fetchLast6MonthsResults(string $inseeCode): array
    {
        if ('' === trim($inseeCode)) {
            throw new \InvalidArgumentException('Code INSEE vide');
        }

        $currentDate = new DateTimeImmutable();

        $response = $this->request('resultats_dis', [
            'code_commune' => $inseeCode,
            'date_max_prelevement' => $currentDate->format('Y-m-d'),
            'date_min_prelevement' => $currentDate->sub(new DateInterval('P6M'))->format('Y-m-d'),
            'sort' => 'asc'
        ]);

        if (!array_key_exists('data', $response)) {
            throw new \RuntimeException('Réponse API invalide');
        }

        return $response;

    }

    private function request(string $path, array $query): array
    {
        try {
            $response = $this->client->request(
                'GET',
                $this->baseApiUrl . self::ENDPOINT . $path, [
                    'query' => $query
                ]
            );

            return $response->toArray();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Hubeau API request failed: ' . $e->getMessage());
        }
    }
}