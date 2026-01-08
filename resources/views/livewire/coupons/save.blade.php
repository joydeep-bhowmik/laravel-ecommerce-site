<?php
use App\Traits\Toast;
use App\Models\Coupon;
use Livewire\Volt\Component;
use Illuminate\Validation\Rule;

new class extends Component {
    use Toast;

    public string|null $id = null;
    public string $code = '';
    public string $description = '';
    public string $discount_type = 'percentage';
    public float $discount_value = 0;
    public float $minimum_order_amount = 0;
    public string $start_date = '';
    public string $end_date = '';
    public int $usage_limit = 0;
    public bool $is_active = true;

    function mount(string|null $id = null)
    {
        $this->id = $id;
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addMonth()->format('Y-m-d');

        if ($this->id) {
            $coupon = Coupon::find($this->id);
            if (!$coupon) {
                abort(404);
            }
            $this->code = $coupon->code;
            $this->description = $coupon->description;
            $this->discount_type = $coupon->discount_type;
            $this->discount_value = $coupon->discount_value;
            $this->minimum_order_amount = $coupon->minimum_order_amount;
            $this->start_date = $coupon->start_date->format('Y-m-d');
            $this->end_date = $coupon->end_date->format('Y-m-d');
            $this->usage_limit = $coupon->usage_limit;
            $this->is_active = $coupon->is_active;
        }
    }

    function save()
    {
        $this->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('coupons', 'code')->ignore($this->id)],
            'description' => 'required|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'minimum_order_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'usage_limit' => 'required|integer|min:0',
        ]);

        // Additional validation for percentage discount
        if ($this->discount_type === 'percentage' && $this->discount_value > 100) {
            $this->addError('discount_value', 'Percentage discount cannot be greater than 100%');
            return;
        }

        $coupon = $this->id ? Coupon::find($this->id) : new Coupon();

        $coupon
            ->fill([
                'code' => $this->code,
                'description' => $this->description,
                'discount_type' => $this->discount_type,
                'discount_value' => $this->discount_value,
                'minimum_order_amount' => $this->minimum_order_amount,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'usage_limit' => $this->usage_limit,
                'is_active' => $this->is_active,
            ])
            ->save();

        $this->success('Saved', 'Coupon saved successfully');

        if (!$this->id) {
            $this->redirect(route('admin.coupons.edit', ['id' => $coupon->id]), navigate: true);
        }
    }

    function delete()
    {
        $coupon = Coupon::find($this->id);
        if ($coupon) {
            $coupon->delete();
            $this->success('Deleted');
        }
        $this->redirect(route('admin.coupons.index'), navigate: true);
    }
};
?>

<div>
    <x-header :title="$id ? 'Coupons / Edit' : 'Coupons / Create'">
        <x-slot:actions>
            <x-button spinner class="btn-primary" wire:click='save'>Save</x-button>
            @if ($id)
                <x-button spinner class="btn-error" wire:click='delete' wire:confirm='Are you sure?'>Delete</x-button>
            @endif
        </x-slot:actions>
    </x-header>

    <x-form>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="space-y-5">
                <x-card title="Coupon Details">
                    <div class="space-y-3">
                        <x-input label="Coupon Code" wire:model='code' placeholder="e.g. SUMMER20" />

                        <x-textarea label="Description" wire:model='description' placeholder="Describe the coupon..." />

                        <x-toggle label="Active Coupon" wire:model="is_active" />
                    </div>
                </x-card>

                <x-card title="Discount Settings">
                    <div class="space-y-3">
                        <x-radio label="Discount Type" wire:model="discount_type" :options="[
                            [
                                'id' => 'percentage',
                                'name' => 'Percentage',
                            ],
                            [
                                'id' => 'fixed',
                                'name' => 'Fixed Amount',
                            ],
                        ]" />

                        <x-input type="number" label="Discount Value" wire:model='discount_value' :suffix="$discount_type === 'percentage' ? '%' : config('app.currency_symbol')"
                            min="0.01" step="0.01" />

                        <x-input type="number" label="Minimum Order Amount" wire:model='minimum_order_amount'
                            :prefix="config('app.currency_symbol')" hint="Minimum cart total required to apply this coupon (0 for no minimum)"
                            min="0" step="0.01" />
                    </div>
                </x-card>
            </div>

            <div class="space-y-5">
                <x-card title="Usage Limits">
                    <div class="space-y-3">
                        <x-input type="number" label="Usage Limit" wire:model='usage_limit'
                            hint="0 means unlimited uses" min="0" />
                    </div>
                </x-card>

                <x-card title="Validity Period">
                    <div class="space-y-3">
                        <x-datetime label="Start Date" wire:model="start_date" without-time :min="now()?->format('Y-m-d')" />

                        <x-datetime label="End Date" wire:model="end_date" without-time :min="$start_date" />
                    </div>
                </x-card>
            </div>
        </div>
    </x-form>
</div>
