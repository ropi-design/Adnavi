<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $recommendation;

    public $loading;

    public $question;

    public $answer;

    public $asking;

    public $error;

    public function mount($id)
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function loadRecommendation($id)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('loadRecommendation'))->execute(...$arguments);
    }

    public function updateStatus($status)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('updateStatus'))->execute(...$arguments);
    }

    public function askQuestion(\App\Services\AI\GeminiService $geminiService)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('askQuestion'))->execute(...$arguments);
    }

};