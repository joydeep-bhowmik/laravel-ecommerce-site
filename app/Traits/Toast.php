<?php
namespace App\Traits;

trait Toast
{

    function toast($title, $subtitle = '', $variant = 'defautl')
    {
        return $this->dispatch('toast', title: $title, subtitle: $subtitle, variant: $variant);
    }

    function success($title, $subtitle = '')
    {
        return $this->toast($title, $subtitle, 'success');
    }

    function info($title, $subtitle = '')
    {
        return $this->toast($title, $subtitle, 'info');
    }

    function warning($title, $subtitle = '')
    {
        return $this->toast($title, $subtitle, 'warning');
    }

    function error($title, $subtitle = '')
    {
        return $this->toast($title, $subtitle, 'error');
    }
}
