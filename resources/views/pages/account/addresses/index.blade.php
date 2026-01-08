<?php

use App\Traits\Toast;

use App\Models\Address;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;
use function Laravel\Folio\name;
name('addresses');

new class extends Component {
    use Toast; // Use the trait

    public $addresses, $full_name, $phone, $email, $address_line_1, $address_line_2, $city, $state, $postal_code, $is_default;
    public $addressId; // To track the current address being updated
    public bool $showModal = false;

    public function mount()
    {
        $this->loadAddresses();
    }

    public function loadAddresses()
    {
        $this->addresses = Address::where('user_id', Auth::id())->get();
    }

    public function saveAddress()
    {
        // Check max address limit
        if (Address::where('user_id', Auth::id())->count() >= 3) {
            $this->error(
                'Address Limit Reached', // Title
                'You can only have up to 3 addresses.', // Subtitle
            );
            return;
        }

        $this->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'nullable|email',
            'address_line_1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
        ]);

        // Handle is_default (Only one default allowed)
        if ($this->is_default) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        Address::create([
            'user_id' => Auth::id(),
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'is_default' => $this->is_default ? true : false,
        ]);

        $this->success(
            'Address Added', // Title
            'Your address has been added successfully.', // Subtitle
        );
        $this->resetFields();
        $this->loadAddresses();
        $this->showModal = false;
    }

    public function editAddress($id)
    {
        $address = Address::find($id);
        $this->addressId = $address->id;
        $this->full_name = $address->full_name;
        $this->phone = $address->phone;
        $this->email = $address->email;
        $this->address_line_1 = $address->address_line_1;
        $this->address_line_2 = $address->address_line_2;
        $this->city = $address->city;
        $this->state = $address->state;
        $this->postal_code = $address->postal_code;
        $this->is_default = $address->is_default ? true : false;

        $this->showModal = true;
    }

    public function updateAddress()
    {
        $this->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'nullable|email',
            'address_line_1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'postal_code' => 'required|string|max:10',
        ]);

        $address = Address::find($this->addressId);

        if (!$address) {
            $this->addressId = null;
            return;
        }

        // Handle is_default (Only one default allowed)
        if ($this->is_default) {
            Address::where('user_id', Auth::id())->update(['is_default' => false]);
        }

        $address->update([
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'is_default' => $this->is_default ? true : false,
        ]);

        $this->success(
            'Address Updated', // Title
            'Your address has been updated successfully.', // Subtitle
        );
        $this->resetFields();
        $this->loadAddresses();
        $this->showModal = false;
    }

    public function deleteAddress($id)
    {
        Address::where('id', $id)->where('user_id', Auth::id())->delete();
        $this->success(
            'Address Deleted', // Title
            'Your address has been deleted successfully.', // Subtitle
        );
        $this->loadAddresses();
        $this->resetErrorBag();
    }

    public function resetFields()
    {
        $this->addressId = null;
        $this->full_name = '';
        $this->phone = '';
        $this->email = '';
        $this->address_line_1 = '';
        $this->address_line_2 = '';
        $this->city = '';
        $this->state = '';
        $this->postal_code = '';
        $this->is_default = false;
    }

    function create()
    {
        $this->resetFields();
        $this->showModal = true;
    }
};

?>

<x-app-layout title="Profile Addresses">
    @volt('addresses')
        <div class="">


            <x-profile.layout isactive="addresses">
                <section>

                    <x-header title="Manage Address">
                        <x-slot:actions>
                            @if (Address::where('user_id', Auth::id())->count() <= 3)
                                <x-button wire:click='create' spinner>Create</x-button>
                            @endif
                        </x-slot:actions>
                    </x-header>



                    <!-- Address List -->
                    <div class=" space-y-4 mt-5">
                        @forelse ($addresses as $address)
                            <x-card class="p-6" class=" max-w-xl">
                                <div
                                    class="flex flex-col justify-between space-y-4 sm:flex-row sm:items-center sm:space-y-0">
                                    <div>
                                        <p class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $address['full_name'] }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $address['address_line_1'] }},
                                            {{ $address['city'] }}, {{ $address['state'] }} - {{ $address['postal_code'] }}
                                        </p>
                                        @if ($address['is_default'])
                                            <x-badge value="Default"
                                                class=" bg-green-100 text-xs font-medium text-green-800 dark:bg-green-200" />
                                        @endif
                                    </div>
                                    <div class="flex space-x-2">
                                        <x-button wire:click="editAddress({{ $address['id'] }})" type="button"
                                            class="bg-primary-100 text-primary-700 hover:bg-primary-200" spinner>
                                            Edit
                                        </x-button>
                                        <x-button wire:click="deleteAddress({{ $address['id'] }})" type="button"
                                            class="bg-red-100 text-red-700 hover:bg-red-200" spinner>
                                            Delete
                                        </x-button>
                                    </div>
                                </div>
                            </x-card>
                        @empty
                            <center class="p-5">Nothing here</center>
                        @endforelse
                    </div>

                    <x-modal wire:model='showModal'>
                        <x-form wire:submit.prevent="{{ $addressId ? 'updateAddress' : 'saveAddress' }}">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <x-input wire:model="full_name" label="Full Name" placeholder="Enter your full name" />
                                <x-input wire:model="phone" label="Phone" placeholder="Enter your phone number" />
                            </div>
                            <x-input wire:model="email" label="Email (optional)" placeholder="Enter your email"
                                type="email" />
                            <x-input wire:model="address_line_1" label="Address Line 1"
                                placeholder="Enter address line 1" />
                            <x-input wire:model="address_line_2" label="Address Line 2 (optional)"
                                placeholder="Enter address line 2" />
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <x-input wire:model="city" label="City" placeholder="Enter your city" />
                                @php
                                    $states = [
                                        ['id' => 'Andhra Pradesh', 'name' => 'Andhra Pradesh'],
                                        ['id' => 'Arunachal Pradesh', 'name' => 'Arunachal Pradesh'],
                                        ['id' => 'Assam', 'name' => 'Assam'],
                                        ['id' => 'Bihar', 'name' => 'Bihar'],
                                        ['id' => 'Chhattisgarh', 'name' => 'Chhattisgarh'],
                                        ['id' => 'Goa', 'name' => 'Goa'],
                                        ['id' => 'Gujarat', 'name' => 'Gujarat'],
                                        ['id' => 'Haryana', 'name' => 'Haryana'],
                                        ['id' => 'Himachal Pradesh', 'name' => 'Himachal Pradesh'],
                                        ['id' => 'Jharkhand', 'name' => 'Jharkhand'],
                                        ['id' => 'Karnataka', 'name' => 'Karnataka'],
                                        ['id' => 'Kerala', 'name' => 'Kerala'],
                                        ['id' => 'Madhya Pradesh', 'name' => 'Madhya Pradesh'],
                                        ['id' => 'Maharashtra', 'name' => 'Maharashtra'],
                                        ['id' => 'Manipur', 'name' => 'Manipur'],
                                        ['id' => 'Meghalaya', 'name' => 'Meghalaya'],
                                        ['id' => 'Mizoram', 'name' => 'Mizoram'],
                                        ['id' => 'Nagaland', 'name' => 'Nagaland'],
                                        ['id' => 'Odisha', 'name' => 'Odisha'],
                                        ['id' => 'Punjab', 'name' => 'Punjab'],
                                        ['id' => 'Rajasthan', 'name' => 'Rajasthan'],
                                        ['id' => 'Sikkim', 'name' => 'Sikkim'],
                                        ['id' => 'Tamil Nadu', 'name' => 'Tamil Nadu'],
                                        ['id' => 'Telangana', 'name' => 'Telangana'],
                                        ['id' => 'Tripura', 'name' => 'Tripura'],
                                        ['id' => 'Uttar Pradesh', 'name' => 'Uttar Pradesh'],
                                        ['id' => 'Uttarakhand', 'name' => 'Uttarakhand'],
                                        ['id' => 'West Bengal', 'name' => 'West Bengal'],
                                    ];
                                @endphp

                                <x-select label="Select State" :options="$states" wire:model="state" placeholder="Select"
                                    placeholder-value="0" />

                                <x-input wire:model="postal_code" label="Postal Code" placeholder="Enter postal code" />
                            </div>
                            <x-checkbox wire:model="is_default" label="Set as default address" />
                            <x-button type="submit" class="mt-4 btn-primary" spinner>
                                Save
                            </x-button>
                        </x-form>
                    </x-modal>
                </section>
            </x-profile.layout>
        </div>
    @endvolt
</x-app-layout>
