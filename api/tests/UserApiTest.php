<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

class UserApiTest extends ApiTestCase
{
    // This trait provided by HautelookAliceBundle will take care of refreshing the database content to put it in a known state between every tests
    use RefreshDatabaseTrait;

    public function testGetCollection(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        $response = static::createClient()->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/User',
            '@id' => '/users',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 10,
        ]);


        // Checks that the returned JSON is validated by the JSON Schema generated for this API Resource by API Platform
        // This JSON Schema is also used in the generated OpenAPI spec
        $this->assertMatchesResourceCollectionJsonSchema(User::class);
    }

    public function testCreateUser(): void
    {
        $response = static::createClient()->request('POST', '/users', ['json' => [
            'name' => 'Emilian The First',
        ]]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/contexts/User',
            'name' => 'Emilian The First',
            'subscriptions' => [],
        ]);
        $this->assertMatchesResourceItemJsonSchema(User::class);
    }

    public function testCreateInvalidUser(): void
    {
        static::createClient()->request('POST', '/users', ['json' => [
            'name' => ''
        ]]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'name: This value should not be blank.',
        ]);
    }

    public function testUpdateUser(): void
    {
        $client = static::createClient();
        $iri = static::findIriBy(User::class, ['name' => 'Emilian The First']);

        $client->request('PUT', $iri, ['json' => [
            'name' => 'Emilian The Second',
        ]]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'name' => 'Emilian The Second',
        ]);
    }

    public function testDeleteUser(): void
    {
        $client = static::createClient();
        $iri = static::findIriBy(User::class, ['name' => 'Emilian The First']);

        $client->request('DELETE', $iri);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
        // Through the container, you can access all your services from the tests, including the ORM, the mailer, remote API clients...
            static::$container->get('doctrine')->getRepository(User::class)->findOneBy(['name' => 'Emilian The First'])
        );
    }

    public function testGetSubscriptionsCost(): void
    {
        $client = static::createClient();
        /**
         * @var $user User
         */
        $user = static::$container->get('doctrine')->getRepository(User::class)->findOneBy(['name' => 'Emilian The First']);
        $client->request('GET', static::$container->get('api_platform.router')->generate('get_subscriptions_cost', ['id' => $user->getId()]), [
            'json' => ["monthlyCost" => 25,"annualCost" => 125],
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertEquals(
            1,
            static::$container->get('messenger.receiver_locator')->get('doctrine')->getMessageCount(),
            'No message has been sent.'
        );
    }
}
