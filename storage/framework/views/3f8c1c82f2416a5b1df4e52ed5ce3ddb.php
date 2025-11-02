<?php

use Livewire\Volt\Actions;
use Livewire\Volt\CompileContext;
use Livewire\Volt\Contracts\Compiled;
use Livewire\Volt\Component;

new class extends Component implements Livewire\Volt\Contracts\FunctionalComponent
{
    public static CompileContext $__context;

    use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public $selectedPeriod;

    public $metrics;

    public $loading;

    public $customStartDate;

    public $customEndDate;

    public $showCustomDatePicker;

    public $dateRange;

    public $showGraphModal;

    public $selectedMetric;

    public $dailyData;

    public function mount()
    {
        (new Actions\InitializeState)->execute(static::$__context, $this, get_defined_vars());

        (new Actions\CallHook('mount'))->execute(static::$__context, $this, get_defined_vars());
    }

    public function calculateCustomDays()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('calculateCustomDays'))->execute(...$arguments);
    }

    public function calculateDateRange()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('calculateDateRange'))->execute(...$arguments);
    }

    public function loadMetrics()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('loadMetrics'))->execute(...$arguments);
    }

    public function changePeriod($period)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('changePeriod'))->execute(...$arguments);
    }

    public function setCustomDate()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('setCustomDate'))->execute(...$arguments);
    }

    public function refresh()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('refresh'))->execute(...$arguments);
    }

    public function generateDailyData($metricKey)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('generateDailyData'))->execute(...$arguments);
    }

    public function showMetricGraph($metricKey)
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('showMetricGraph'))->execute(...$arguments);
    }

    public function closeGraphModal()
    {
        $arguments = [static::$__context, $this, func_get_args()];

        return (new Actions\CallMethod('closeGraphModal'))->execute(...$arguments);
    }

};