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

    public $statusFilter;

    public $sortBy;

    public $sortDirection;

    public $deleteConfirmId;

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

    public function updatingStatusFilter()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('updatingStatusFilter'))->execute(...$arguments);
    }

    public function sortByColumn($column)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('sortByColumn'))->execute(...$arguments);
    }

    public function confirmDelete($reportId)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('confirmDelete'))->execute(...$arguments);
    }

    public function cancelDelete()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('cancelDelete'))->execute(...$arguments);
    }

    public function deleteReport($reportId)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('deleteReport'))->execute(...$arguments);
    }

};