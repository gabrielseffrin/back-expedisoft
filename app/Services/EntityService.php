<?php

namespace App\Services;

use App\DTOs\Entity\CarrierDTO;
use App\DTOs\Entity\CustomerDTO;
use App\DTOs\Entity\DestinationDTO;
use App\DTOs\Entity\DockDTO;
use App\DTOs\Entity\DriverDTO;
use App\DTOs\Entity\ProductDTO;
use App\DTOs\Entity\UserDTO;
use App\DTOs\Entity\VehicleDTO;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Destination;
use App\Models\Dock;
use App\Models\Driver;
use App\Models\Product;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Hash;

class EntityService
{
    public function findOrCreateCustomer(CustomerDTO $dto): Customer
    {
        if (!empty($dto->externalId)) {
            $customer = Customer::query()->where('external_id', $dto->externalId)->first();

            if ($customer) {
                $customer->update([
                    'name'  => $dto->name,
                    'email' => $dto->email,
                    'phone' => $dto->phone,
                ]);
                return $customer;
            }
        }

        if (!empty($dto->document)) {
            $customer = Customer::query()->where('document', $dto->document)->first();

            if ($customer) {
                $customer->update([
                    'external_id'   => $dto->externalId ?? null,
                    'source_system' => $dto->sourceSystem ?? null,
                ]);
                return $customer;
            }
        }

        return Customer::query()->create([
            'external_id'   => $dto->externalId ?? null,
            'source_system' => $dto->sourceSystem ?? null,
            'name'          => $dto->name,
            'document'      => $dto->document ?? null,
            'email'         => $dto->email ?? null,
            'phone'         => $dto->phone ?? null,
        ]);
    }

    public function findOrCreateDestination(DestinationDTO $dto): Destination
    {
        if (!empty($dto->externalId)) {
            $destination = Destination::query()->where('external_id', $dto->externalId)->first();

            if ($destination) {
                $destination->update([
                    'name'    => $dto->name,
                    'address' => $dto->address ?? null,
                    'city'    => $dto->city ?? null,
                    'state'   => $dto->state ?? null,
                ]);
                return $destination;
            }
        }

        if (!empty($dto->postalCode)) {
            $destination = Destination::query()
                ->where('postal_code', $dto->postalCode)
                ->where('name', $dto->name)
                ->first();

            if ($destination) {
                $destination->update([
                    'external_id'   => $dto->externalId ?? null,
                    'source_system' => $dto->sourceSystem ?? null,
                ]);
                return $destination;
            }
        }

        return Destination::query()->create([
            'external_id'   => $dto->externalId ?? null,
            'source_system' => $dto->sourceSystem ?? null,
            'name'          => $dto->name,
            'address'       => $dto->address ?? null,
            'city'          => $dto->city ?? null,
            'state'         => $dto->state ?? null,
            'postal_code'   => $dto->postalCode ?? null,
        ]);
    }

    public function findOrCreateCarrier(CarrierDTO $dto): Carrier
    {
        if (!empty($dto->externalId)) {
            $carrier = Carrier::query()->where('external_id', $dto->externalId)->first();

            if ($carrier) {
                $carrier->update([
                    'name'          => $dto->name,
                    'document'      => $dto->document ?? null,
                    'contact_phone' => $dto->contactPhone ?? null,
                ]);
                return $carrier;
            }
        }

        if (!empty($dto->document)) {
            $carrier = Carrier::query()->where('document', $dto->document)->first();

            if ($carrier) {
                $carrier->update([
                    'external_id'   => $dto->externalId ?? null,
                    'source_system' => $dto->sourceSystem ?? null,
                ]);
                return $carrier;
            }
        }

        return Carrier::query()->create([
            'external_id'   => $dto->externalId ?? null,
            'source_system' => $dto->sourceSystem ?? null,
            'name'          => $dto->name,
            'document'      => $dto->document ?? null,
            'contact_phone' => $dto->contactPhone ?? null,
        ]);
    }

    public function findOrCreateVehicle(VehicleDTO $dto): Vehicle
    {
        if (!empty($dto->externalId)) {
            $vehicle = Vehicle::query()->where('external_id', $dto->externalId)->first();

            if ($vehicle) {
                $vehicle->update([
                    'vehiclePlate' => $dto->vehiclePlate,
                    'model'        => $dto->model ?? null,
                ]);
                return $vehicle;
            }
        }

        if (!empty($dto->vehiclePlate)) {
            $vehicle = Vehicle::query()->where('vehiclePlate', $dto->vehiclePlate)->first();

            if ($vehicle) {
                $vehicle->update([
                    'external_id'   => $dto->externalId ?? null,
                    'source_system' => $dto->sourceSystem ?? null,
                ]);
                return $vehicle;
            }
        }

        return Vehicle::query()->create([
            'external_id'   => $dto->externalId,
            'source_system' => $dto->sourceSystem,
            'vehiclePlate'  => $dto->vehiclePlate,
            'model'         => $dto->model ?? null,
            'carrier_id'    => $dto->carrierId,
        ]);
    }

    public function findOrCreateDriver(DriverDTO $dto): Driver
    {
        if (!empty($dto->externalId)) {
            $driver = Driver::query()->where('external_id', $dto->externalId)->first();

            if ($driver) {
                $driver->update([
                    'name'     => $dto->name,
                    'document' => $dto->document ?? null,
                    'phone'    => $dto->phone ?? null,
                ]);
                return $driver;
            }
        }

        if (!empty($dto->document)) {
            $driver = Driver::query()->where('document', $dto->document)->first();

            if ($driver) {
                $driver->update([
                    'external_id'   => $dto->externalId ?? null,
                    'source_system' => $dto->sourceSystem ?? null,
                ]);
                return $driver;
            }
        }

        return Driver::query()->create([
            'external_id'   => $dto->externalId ?? null,
            'source_system' => $dto->sourceSystem ?? null,
            'name'          => $dto->name,
            'document'      => $dto->document,
            'phone'         => $dto->phone ?? null,
            'carrier_id'    => $dto->carrierId,
        ]);
    }

    public function findOrCreateProduct(ProductDTO $dto): Product
    {
        $product = Product::query()->where('sku', $dto->sku)->first();

        if ($product) {
            $product->update([
                'description' => $dto->description,
                'weight'      => $dto->weight ?? null,
                'unit'        => $dto->unit ?? 'un',
                'barcode'     => $dto->barcode ?? null,
            ]);
            return $product;
        }

        return Product::query()->create([
            'sku'         => $dto->sku,
            'description' => $dto->description,
            'weight'      => $dto->weight ?? null,
            'unit'        => $dto->unit ?? 'un',
            'barcode'     => $dto->barcode ?? null,
        ]);
    }

    public function findOrCreateUser(UserDTO $dto): User
    {
        $user = User::query()->where('email', $dto->email)->first();

        if ($user) {
            $user->update([
                'name'          => $dto->name,
                'external_id'   => $dto->externalId ?? null,
                'source_system' => $dto->sourceSystem ?? null,
            ]);
            return $user;
        }

        $password = Hash::make('Expedisoft' . $dto->email);

        return User::query()->create([
            'name'          => $dto->name,
            'email'         => $dto->email,
            'external_id'   => $dto->externalId ?? null,
            'source_system' => $dto->sourceSystem ?? null,
            'password'      => $password,
        ]);
    }

    public function findOrCreateDock(DockDTO $dto): Dock
    {
        $dock = Dock::query()->where('external_id', $dto->externalId)->first();

        if ($dock) {
            $dock->update([
                'dock_code'     => $dto->dockCode,
                'description'   => $dto->description ?? null,
                'location'      => $dto->location ?? null,
                'external_id'   => $dto->externalId ?? null,
                'source_system' => $dto->sourceSystem ?? null,
            ]);
            return $dock;
        }

        return Dock::query()->create([
            'external_id'   => $dto->externalId ?? null,
            'source_system' => $dto->sourceSystem ?? null,
            'dock_code'     => $dto->dockCode,
            'description'   => $dto->description ?? null,
            'location'      => $dto->location ?? null,
        ]);
    }
}
