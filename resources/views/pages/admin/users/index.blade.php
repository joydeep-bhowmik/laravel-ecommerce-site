<?php
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use function Laravel\Folio\name;

name('admin.users.index');

new class extends Component {
    use WithPagination;

    function with()
    {
        $data = User::paginate();

        $headers = [['key' => 'name', 'label' => 'Name', 'class' => 'w-72'], ['key' => 'email', 'label' => 'Email'], ['key' => 'role', 'label' => 'Role'], ['key' => 'created_at', 'label' => 'Registered At'], ['key' => 'action', 'label' => 'Action']];

        return compact('data', 'headers');
    }
};
?>

<x-admin-layout title="Users">
    <x-header title="Users" />

    @volt('admin.users.index')
        <div>
            <x-card>
                @if ($data->count())
                    <x-table :headers="$headers" :rows="$data" with-pagination>
                        @scope('cell_created_at', $user)
                            {{ $user->created_at->format('F d, Y - h:i A') }}
                        @endscope

                        {{-- @scope('cell_action', $user)
                    <x-button class="btn-primary">View</x-button>
                @endscope --}}
                    </x-table>
                @else
                    <center>No data</center>
                @endif
            </x-card>
        </div>
    @endvolt
</x-admin-layout>
