<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FreightRate> $freightRates
 * @property-read int|null $freight_rates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceTrip> $invoiceTrips
 * @property-read int|null $invoice_trips_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CarrierType whereUpdatedAt($value)
 */
	class CarrierType extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $contact
 * @property string|null $phone
 * @property string|null $address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Client whereUpdatedAt($value)
 */
	class Client extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceTrip> $invoiceTrips
 * @property-read int|null $invoice_trips_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Driver whereUpdatedAt($value)
 */
	class Driver extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $origin_id
 * @property int $destination_id
 * @property int $carrier_type_id
 * @property numeric $base_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CarrierType $carrierType
 * @property-read \App\Models\Location $destination
 * @property-read \App\Models\Location $origin
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreightRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreightRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreightRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreightRate whereBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreightRate whereCarrierTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreightRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreightRate whereDestinationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreightRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreightRate whereOriginId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FreightRate whereUpdatedAt($value)
 */
	class FreightRate extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $client_id
 * @property int $year
 * @property int $month
 * @property string|null $invoice_number
 * @property string|null $issuer_name
 * @property string|null $issuer_address
 * @property string|null $issuer_phone
 * @property numeric $total_amount
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $confirmed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceTrip> $invoiceTrips
 * @property-read int|null $invoice_trips_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIssuerAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIssuerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereIssuerPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Invoice whereYear($value)
 */
	class Invoice extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $invoice_id
 * @property \Illuminate\Support\Carbon $date
 * @property int $origin_id
 * @property int $driver_id
 * @property int $carrier_type_id
 * @property numeric $freight_fee
 * @property string|null $weight
 * @property int $sequence
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CarrierType $carrierType
 * @property-read \App\Models\Driver $driver
 * @property-read \App\Models\Invoice $invoice
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceTripStop> $invoiceTripStops
 * @property-read int|null $invoice_trip_stops_count
 * @property-read \App\Models\Location $origin
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereCarrierTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereDriverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereFreightFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereOriginId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTrip whereWeight($value)
 */
	class InvoiceTrip extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $invoice_trip_id
 * @property int $location_id
 * @property int $sequence
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\InvoiceTrip $invoiceTrip
 * @property-read \App\Models\Location $location
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTripStop newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTripStop newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTripStop query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTripStop whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTripStop whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTripStop whereInvoiceTripId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTripStop whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTripStop whereSequence($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceTripStop whereUpdatedAt($value)
 */
	class InvoiceTripStop extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FreightRate> $destinationRates
 * @property-read int|null $destination_rates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FreightRate> $originRates
 * @property-read int|null $origin_rates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceTrip> $originTrips
 * @property-read int|null $origin_trips_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceTripStop> $tripStops
 * @property-read int|null $trip_stops_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Location whereUpdatedAt($value)
 */
	class Location extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Setting whereValue($value)
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser {}
}

