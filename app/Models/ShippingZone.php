<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'country', 'state', 'city', 'postal_code_range', 'price_per_kg', 'tax_rate'];

    /**
     * Get the shipping zone based on address details.
     */
    public static function getZoneForAddress($country, $state, $postalCode)
    {
        // Try to find a match using postal code range
        $zone = self::where('country', $country)
            ->whereRaw('? BETWEEN SUBSTRING_INDEX(postal_code_range, "-", 1)
                AND SUBSTRING_INDEX(postal_code_range, "-", -1)', [$postalCode])
            ->first();

        // If no zone found, fallback to matching by state
        if (! $zone) {
            $zone = self::where('country', $country)
                ->where(function ($query) use ($state) {
                    $query->whereNull('state')->orWhere('state', $state);
                })
                ->first();
        }

        return $zone;
    }

    /**
     * Calculate shipping cost based on weight for this zone.
     */
    public function calculateShippingCost($weight)
    {
        if (! $this->price_per_kg || $weight <= 0) {
            return 0; // Return 0 if no pricing is set or weight is invalid
        }

        return $this->price_per_kg * $weight;
    }

    /**
     * The function `getShippingCost` calculates the shipping cost, tax amount, and total cost for a
     * given address based on the weight of the shipment and the corresponding shipping zone.
     *
     * @param country The `getShippingCost` function you provided calculates the shipping cost, tax
     * amount, and total cost for a given address based on the country, state, postal code, and weight
     * provided.
     * @param state The `state` parameter in the `getShippingCost` function represents the state or
     * region of the address for which you want to calculate the shipping cost. It is used along with
     * the `country` and `postalCode` parameters to determine the shipping zone and applicable taxes
     * for the given address. The
     * @param postalCode The `postalCode` parameter in the `getShippingCost` function represents the
     * postal code of the address for which you want to calculate the shipping cost. This parameter is
     * used along with the `country` and `state` parameters to determine the shipping zone for the
     * address. The shipping cost, tax
     * @param weight Weight is a numerical value representing the weight of the item being shipped. It
     * is used in the `getShippingCost` function to calculate the shipping cost based on the weight of
     * the item. The weight is typically measured in pounds or kilograms, depending on the unit of
     * measurement used by the shipping provider.
     *
     * @return The function `getShippingCost` returns an array with the following keys and values:
     * - 'zone': The name of the shipping zone for the provided address
     * - 'shipping_cost': The calculated shipping cost based on the weight and the shipping zone
     * - 'tax': The calculated tax amount on the shipping cost
     * - 'total_cost': The total cost including the shipping cost and tax amount
     */
    public static function getShippingCost($country, $state, $postalCode, $weight)
    {
        $zone = self::getZoneForAddress($country, $state, $postalCode);

        if (! $zone) {
            return [
                'message'       => 'We don\'t ship to the mentioned address yet',
                'shipping_cost' => null,
                'tax'           => null,
                'total_cost'    => null,
            ];
        }

        // Get tax rate (use zone tax rate if available, otherwise fallback to standard tax rate)
        $taxRate = $zone->tax_rate ?? (Setting::get('standard_tax_rate') ?? 0);

        // Calculate shipping cost
        $shippingCost = $zone->calculateShippingCost($weight);

        // Calculate tax amount on shipping cost
        $taxAmount = ($taxRate / 100) * $shippingCost;

        // Total cost including tax
        $totalCost = $shippingCost + $taxAmount;

        return [
            'zone'          => $zone->name,
            'shipping_cost' => $shippingCost,
            'tax'           => $taxAmount,
            'total_cost'    => $totalCost,
        ];
    }

}
