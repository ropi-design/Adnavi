<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $colorMode;

    public $slackTheme;

    public $accessibilityTheme;

    public function mount()
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function saveTheme()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('saveTheme'))->execute(...$arguments);
    }

    public function updateColorMode()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('updateColorMode'))->execute(...$arguments);
    }

    public function updateSlackTheme()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('updateSlackTheme'))->execute(...$arguments);
    }

    public function updateAccessibilityTheme()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('updateAccessibilityTheme'))->execute(...$arguments);
    }

};