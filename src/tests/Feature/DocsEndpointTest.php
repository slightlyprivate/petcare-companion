<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocsEndpointTest extends TestCase
{
    /**
     * Test that the Scribe documentation UI is accessible.
     */
    public function test_docs_ui_is_accessible(): void
    {
        $response = $this->get('/docs');

        $response->assertStatus(200)
            ->assertSee('API Documentation', false);
    }

    /**
     * Test that the Postman collection endpoint is accessible.
     */
    public function test_docs_postman_collection_is_accessible(): void
    {
        $response = $this->get('/docs.postman');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * Test that the OpenAPI spec endpoint is accessible.
     */
    public function test_docs_openapi_spec_is_accessible(): void
    {
        $response = $this->get('/docs.openapi');

        $response->assertStatus(200);
    }
}
