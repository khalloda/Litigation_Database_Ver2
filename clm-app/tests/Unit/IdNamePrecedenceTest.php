<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PreflightEngine;
use App\Services\MappingEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IdNamePrecedenceTest extends TestCase
{
    use RefreshDatabase;

    protected PreflightEngine $preflightEngine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->preflightEngine = new PreflightEngine(new MappingEngine());
    }

    /** @test */
    public function it_applies_id_precedence_when_both_id_and_name_provided()
    {
        $data = [
            'client_id' => '123',
            'client_name' => 'Test Client',
            'court_id' => '456',
            'court_name' => 'Test Court',
            'opponent_id' => '789',
            'opponent_name' => 'Test Opponent'
        ];

        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->preflightEngine);
        $method = $reflection->getMethod('applyIdNamePrecedence');
        $method->setAccessible(true);

        $result = $method->invoke($this->preflightEngine, $data);

        // ID fields should remain, name fields should be cleared
        $this->assertEquals('123', $result['client_id']);
        $this->assertNull($result['client_name']);

        $this->assertEquals('456', $result['court_id']);
        $this->assertNull($result['court_name']);

        $this->assertEquals('789', $result['opponent_id']);
        $this->assertNull($result['opponent_name']);
    }

    /** @test */
    public function it_preserves_name_fields_when_no_id_provided()
    {
        $data = [
            'client_name' => 'Test Client',
            'court_name' => 'Test Court',
            'opponent_name' => 'Test Opponent'
        ];

        $reflection = new \ReflectionClass($this->preflightEngine);
        $method = $reflection->getMethod('applyIdNamePrecedence');
        $method->setAccessible(true);

        $result = $method->invoke($this->preflightEngine, $data);

        // Name fields should be preserved when no ID provided
        $this->assertEquals('Test Client', $result['client_name']);
        $this->assertEquals('Test Court', $result['court_name']);
        $this->assertEquals('Test Opponent', $result['opponent_name']);
    }

    /** @test */
    public function it_preserves_id_fields_when_no_name_provided()
    {
        $data = [
            'client_id' => '123',
            'court_id' => '456',
            'opponent_id' => '789'
        ];

        $reflection = new \ReflectionClass($this->preflightEngine);
        $method = $reflection->getMethod('applyIdNamePrecedence');
        $method->setAccessible(true);

        $result = $method->invoke($this->preflightEngine, $data);

        // ID fields should be preserved when no name provided
        $this->assertEquals('123', $result['client_id']);
        $this->assertEquals('456', $result['court_id']);
        $this->assertEquals('789', $result['opponent_id']);
    }

    /** @test */
    public function it_handles_empty_and_null_values_correctly()
    {
        $data = [
            'client_id' => '',
            'client_name' => 'Test Client',
            'court_id' => null,
            'court_name' => 'Test Court',
            'opponent_id' => '0',
            'opponent_name' => 'Test Opponent'
        ];

        $reflection = new \ReflectionClass($this->preflightEngine);
        $method = $reflection->getMethod('applyIdNamePrecedence');
        $method->setAccessible(true);

        $result = $method->invoke($this->preflightEngine, $data);

        // Empty/null IDs should not trigger precedence
        $this->assertEquals('', $result['client_id']);
        $this->assertEquals('Test Client', $result['client_name']);

        $this->assertNull($result['court_id']);
        $this->assertEquals('Test Court', $result['court_name']);

        // '0' is not considered a valid ID (not numeric in this context)
        $this->assertEquals('0', $result['opponent_id']);
        $this->assertEquals('Test Opponent', $result['opponent_name']);
    }

    /** @test */
    public function it_handles_non_numeric_id_values()
    {
        $data = [
            'client_id' => 'not-a-number',
            'client_name' => 'Test Client',
            'court_id' => '123',
            'court_name' => 'Test Court'
        ];

        $reflection = new \ReflectionClass($this->preflightEngine);
        $method = $reflection->getMethod('applyIdNamePrecedence');
        $method->setAccessible(true);

        $result = $method->invoke($this->preflightEngine, $data);

        // Non-numeric ID should not trigger precedence
        $this->assertEquals('not-a-number', $result['client_id']);
        $this->assertEquals('Test Client', $result['client_name']);

        // Numeric ID should trigger precedence
        $this->assertEquals('123', $result['court_id']);
        $this->assertNull($result['court_name']);
    }

    /** @test */
    public function it_logs_conflicts_when_precedence_is_applied()
    {
        $data = [
            'client_id' => '123',
            'client_name' => 'Test Client'
        ];

        // Mock the Log facade
        \Log::shouldReceive('warning')
            ->once()
            ->with('ID vs Name precedence conflicts detected', \Mockery::type('array'));

        $reflection = new \ReflectionClass($this->preflightEngine);
        $method = $reflection->getMethod('applyIdNamePrecedence');
        $method->setAccessible(true);

        $method->invoke($this->preflightEngine, $data);
    }

    /** @test */
    public function it_handles_all_supported_id_name_pairs()
    {
        $data = [
            'client_id' => '1',
            'client_name' => 'Client',
            'court_id' => '2',
            'court_name' => 'Court',
            'opponent_id' => '3',
            'opponent_name' => 'Opponent',
            'matter_partner_id' => '4',
            'matter_partner_name' => 'Partner',
            'matter_destination_id' => '5',
            'matter_destination' => 'Destination'
        ];

        $reflection = new \ReflectionClass($this->preflightEngine);
        $method = $reflection->getMethod('applyIdNamePrecedence');
        $method->setAccessible(true);

        $result = $method->invoke($this->preflightEngine, $data);

        // All ID fields should be preserved
        $this->assertEquals('1', $result['client_id']);
        $this->assertEquals('2', $result['court_id']);
        $this->assertEquals('3', $result['opponent_id']);
        $this->assertEquals('4', $result['matter_partner_id']);
        $this->assertEquals('5', $result['matter_destination_id']);

        // All name fields should be cleared
        $this->assertNull($result['client_name']);
        $this->assertNull($result['court_name']);
        $this->assertNull($result['opponent_name']);
        $this->assertNull($result['matter_partner_name']);
        $this->assertNull($result['matter_destination']);
    }
}
