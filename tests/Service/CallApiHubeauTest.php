<?php

declare(strict_types=1);

use App\Service\CallApiHubeau;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class CallApiHubeauTest extends TestCase
{
    public function testGetInseeCodeReturnCode(): void
    {
        $mockResponse = new MockResponse(
            json_encode([
                'data' => [
                    [
                        'nom_commune' => 'PAU',
                        'code_commune' => '64445',
                    ]
                ]
            ]),
            [
                'response_headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]
        );

        $httpClient = new MockHttpClient($mockResponse);

        $service = new CallApiHubeau(
            $httpClient,
            'https://test-api.fr/'
        );

        $result = $service->getInseeCode('Pau');

        $this->assertSame('64445', $result);
    }

    public function testUnknowCity(): void
    {
        
        $mockResponse = new MockResponse(
            json_encode(['data' => []])
        );

        $httpClient = new MockHttpClient($mockResponse);

        $service = new CallApiHubeau(
            $httpClient,
            'https://test-api.fr/'
        );

        //except exception
        $this->expectException(RuntimeException::class);
        $service->getInseeCode('toto');

    }

    public function testLast6MonthsData(): void
    {
        $mockResponse = new MockResponse(
            json_encode([
                'data' => [
                    ['parameter' => 'Chlore libre',
                    'value' => 0.23,
                    'unit' => "mg/L",
                    'date' => '2025-11-11' ]
                ]
                ]),
            [
            'response_headers' => [
                'Content-Type' => 'application/json'
            ]
        ]
        );

        $httpClient = new MockHttpClient($mockResponse);

        $service = new CallApiHubeau(
            $httpClient,
            'https://test-api.fr/'
        );

        $result = $service->fetchLast6MonthsResults('64446');

        $this->assertIsArray($result, "Ce n'est pas un tableau");
        $this->assertArrayHasKey('data', $result, 'clé non trouvée');
        $this->assertCount(1, $result['data']);
        $this->assertSame(0.23, $result['data'][0]['value']);

}

public function testEmptyCityNameFetchOneCityByName(): void
{
    $httpClient = new MockHttpClient();
    $service = new CallApiHubeau(
            $httpClient,
            'https://test-api.fr/'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Le nom de la ville ne peut être vide");
        $service->fetchOneCityByName('');

}
        
    
}
