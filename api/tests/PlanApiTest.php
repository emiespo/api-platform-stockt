<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Plan;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class PlanApiTest extends ApiTestCase
{
    // This trait provided by HautelookAliceBundle will take care of refreshing the database content to put it in a known state between every tests
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        $response = static::createClient()->request('GET', '/plans');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/Plan',
            '@id' => '/plans',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 5,
        ]);

        // Checks that the returned JSON is validated by the JSON Schema generated for this API Resource by API Platform
        // This JSON Schema is also used in the generated OpenAPI spec
        $this->assertMatchesResourceCollectionJsonSchema(Plan::class);
    }

    public function testCreatePlan(): void
    {
        $response = static::createClient()->request('POST', '/plans', ['json' => [
            'code'          => 'it',
            'name'          => 'Italy',
            'currency'      => 'EUR',
            'monthlyCost'  => '10',
            'annualCost'   => '35',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/Plan',
            'code'          => 'it',
            'name'          => 'Italy',
            'currency'      => 'EUR',
            'monthlyCost'  => '10.00', // Format changes!
            'annualCost'   => '35.00',
        ]);
        $this->assertMatchesResourceItemJsonSchema(Plan::class);
    }

    public function testCreateInvalidPlan(): void
    {
        static::createClient()->request('POST', '/plans', ['json' => [
            'code'          => '',
            'name'          => 'Portugal',
            'currency'      => 'EUR',
            'monthlyCost'  => '12',
            'annualCost'   => '36',
        ]]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'code: This value should not be blank.',
        ]);
    }

    public function testUpdatePlan(): void
    {
        $client = static::createClient();
        $iri = static::findIriBy(Plan::class, ['code' => 'gb']);

        $client->request('PUT', $iri, ['json' => [
            'name' => 'United Kingdom',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'name' => 'United Kingdom',
        ]);
    }

    public function testDeletePlan(): void
    {
        $client = static::createClient();
        // We will try to delete a plan that is not referenced
        $iri = static::findIriBy(Plan::class, ['name' => 'Germany']);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
        // Through the container, you can access all your services from the tests, including the ORM, the mailer, remote API clients...
            static::$container->get('doctrine')->getRepository(Plan::class)->findOneBy(['name' => 'Germany'])
        );
    }

}
