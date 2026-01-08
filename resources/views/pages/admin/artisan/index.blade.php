<?php

namespace App\Http\Livewire;

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Artisan;
use function Laravel\Folio\name;
name('admin.artisan');

new class extends Component {
    public $command = '';
    public $output = '';
    public $secretCode = '';
    private $requiredSecret = '8974'; // Change this to a secure value

    public function runCommand()
    {
        $this->output = ''; // Clear previous output

        if ($this->secretCode !== $this->requiredSecret) {
            $this->output = 'Error: Unauthorized access';
            return;
        }

        try {
            Artisan::call($this->command);
            $this->output = nl2br(e(Artisan::output()));
        } catch (\Exception $e) {
            $this->output = 'Error: ' . e($e->getMessage());
        }
    }
};
?>
<x-admin-layout title="Artisan">
    @volt('artisan')
        <div>
            <!-- resources/views/livewire/artisan-terminal.blade.php -->
            <div class="p-4 bg-gray-900 text-white rounded-lg ">
                <input type="password" wire:model="secretCode" class="w-full p-2 bg-gray-800 text-white rounded"
                    placeholder="Enter Secret Code...">
                <input type="text" wire:model="command" class="w-full mt-2 p-2 bg-gray-800 text-white rounded"
                    placeholder="Enter Artisan command...">
                <button wire:click="runCommand" class="mt-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 rounded">Run</button>

                <div class="mt-4 p-3 bg-black text-green-400 rounded font-mono text-sm whitespace-pre-wrap min-h-screen">
                    {!! $output !!}
                </div>
            </div>


        </div>
    @endvolt
</x-admin-layout>
