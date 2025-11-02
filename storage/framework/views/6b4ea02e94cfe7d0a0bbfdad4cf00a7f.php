<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    use Livewire\WithPagination;

    public $search;

    public $priorityFilter;

    public $categoryFilter;

    public function mount()
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function updatingSearch()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('updatingSearch'))->execute(...$arguments);
    }

    public function updatingPriorityFilter()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('updatingPriorityFilter'))->execute(...$arguments);
    }

    public function updatingCategoryFilter()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('updatingCategoryFilter'))->execute(...$arguments);
    }

};