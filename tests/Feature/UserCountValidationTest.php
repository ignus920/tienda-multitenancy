<?php

namespace Tests\Feature;

use Tests\TestCase;

class UserCountValidationTest extends TestCase
{
    /**
     * Test that the old canCreateOrUpdateUsers method has been removed
     */
    public function test_old_method_removed(): void
    {
        $reflection = new \ReflectionClass(\App\Livewire\Tenant\Users\UserRapForm::class);
        $methods = $reflection->getMethods();
        
        $methodNames = array_map(fn($method) => $method->getName(), $methods);
        
        $this->assertNotContains(
            'canCreateOrUpdateUsers',
            $methodNames,
            'Old canCreateOrUpdateUsers method should be removed'
        );
    }

    /**
     * Test that new validation methods exist
     */
    public function test_new_validation_methods_exist(): void
    {
        $reflection = new \ReflectionClass(\App\Livewire\Tenant\Users\UserRapForm::class);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PRIVATE);
        
        $methodNames = array_map(fn($method) => $method->getName(), $methods);
        
        $this->assertContains('canCreateUser', $methodNames, 'canCreateUser method should exist');
        $this->assertContains('canActivateUser', $methodNames, 'canActivateUser method should exist');
        $this->assertContains('canDeactivateUser', $methodNames, 'canDeactivateUser method should exist');
        $this->assertContains('getActiveUserCount', $methodNames, 'getActiveUserCount method should exist');
        $this->assertContains('getUserLimit', $methodNames, 'getUserLimit method should exist');
        $this->assertContains('hasAvailableUserSlots', $methodNames, 'hasAvailableUserSlots method should exist');
    }
}
