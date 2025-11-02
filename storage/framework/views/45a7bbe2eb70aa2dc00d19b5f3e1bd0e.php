<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $isConnected;

    public $connectedEmail;

    public function mount()
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function checkConnection()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('checkConnection'))->execute(...$arguments);
    }

    public function connect()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('connect'))->execute(...$arguments);
    }

    public function disconnect()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('disconnect'))->execute(...$arguments);
    }

    public function syncAnalyticsProperties()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('syncAnalyticsProperties'))->execute(...$arguments);
    }

};