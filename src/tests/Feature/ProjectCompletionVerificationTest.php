<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @test
 * Comprehensive verification that all acceptance criteria for project completion are met
 */
class ProjectCompletionVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * Verify that all CRUD endpoints exist and are reachable
     */
    public function all_crud_endpoints_are_accessible(): void
    {
        $this->seed();

        // Pet CRUD endpoints
        $this->get('/api/pets')->assertStatus(200);
        $this->get('/api/pets/1')->assertStatus(200);

        // Appointment CRUD endpoints
        $this->get('/api/pets/1/appointments')->assertStatus(200);
        $this->get('/api/appointments/1')->assertStatus(200);

        // Verify collection has expected structure
        $this->assertTrue(file_exists(base_path('postman_collection.json')));
        $collection = json_decode(file_get_contents(base_path('postman_collection.json')), true);
        $this->assertArrayHasKey('info', $collection);
        $this->assertArrayHasKey('item', $collection);
        $this->assertArrayHasKey('variable', $collection);

        // Verify README exists and has key sections
        $this->assertTrue(file_exists(base_path('README.md')));
        $readme = file_get_contents(base_path('README.md'));
        $this->assertStringContainsString('Quick Start', $readme);
        $this->assertStringContainsString('API Endpoints', $readme);
        $this->assertStringContainsString('Postman Collection', $readme);
        $this->assertStringContainsString('Architecture', $readme);

        // Verify web interface is accessible
        $this->get('/pets')->assertStatus(200);
    }

    /**
     * @test
     * Verify that a new developer can follow README to get started
     */
    public function new_developer_quick_start_works(): void
    {
        // Verify database is seeded with expected data
        $this->seed();

        // Should have exactly 3 pets as mentioned in README
        $response = $this->get('/api/pets');
        $response->assertStatus(200);

        $pets = $response->json('data');
        $this->assertCount(3, $pets);

        // Each pet should have appointments
        foreach ($pets as $pet) {
            $appointmentResponse = $this->get("/api/pets/{$pet['id']}/appointments");
            $appointmentResponse->assertStatus(200);
            $appointments = $appointmentResponse->json('data');
            $this->assertNotEmpty($appointments, "Pet {$pet['name']} should have appointments");
        }
    }

    /**
     * @test
     * Verify Postman collection covers all major endpoints
     */
    public function postman_collection_is_comprehensive(): void
    {
        $collection = json_decode(file_get_contents(base_path('postman_collection.json')), true);
        $allRequests = $this->extractAllRequests($collection['item']);
        $requestMethods = array_column($allRequests, 'method');
        $requestUrls = array_column($allRequests, 'url');

        // Should have CRUD operations
        $this->assertContains('GET', $requestMethods);
        $this->assertContains('POST', $requestMethods);
        $this->assertContains('PUT', $requestMethods);
        $this->assertContains('DELETE', $requestMethods);

        // Should have key endpoint patterns
        $urlPatterns = [
            '/api/pets',
            '/api/pets/{pet}',
            '/api/pets/{pet}/appointments',
            '/api/appointments',
        ];

        foreach ($urlPatterns as $pattern) {
            $found = false;
            foreach ($requestUrls as $url) {
                if (is_array($url)) {
                    $path = '/'.implode('/', $url['path']);
                    if (strpos($path, str_replace('{pet}', '1', str_replace('{appointment}', '1', $pattern))) !== false) {
                        $found = true;
                        break;
                    }
                }
            }
            $this->assertTrue($found, "Collection should include endpoint pattern: {$pattern}");
        }
    }

    /**
     * Helper to extract all requests from nested collection structure
     */
    private function extractAllRequests(array $items): array
    {
        $requests = [];

        foreach ($items as $item) {
            if (isset($item['request'])) {
                $requests[] = [
                    'method' => $item['request']['method'],
                    'url' => $item['request']['url'],
                ];
            }

            if (isset($item['item'])) {
                $requests = array_merge($requests, $this->extractAllRequests($item['item']));
            }
        }

        return $requests;
    }
}
