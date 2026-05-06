<?php

namespace App\Services;

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
    public function findOrCreateCustomer(array $data)
    {
        if (!empty($data['external_id'])) {
            $customer = Customer::query()->where('external_id', $data['external_id'])
                ->first();

            if ($customer) {
                $customer->update(
                    [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'phone' => $data['phone'],
                    ]
                );
                return $customer;
            }
        }

        if (!empty($data['document'])) {
            $customer = Customer::query()->where('document', $data['document'])->first();

            if ($customer) {
                $customer->update([
                    'external_id' => $data['external_id'] ?? null,
                    'source_system' => $data['source_system'] ?? null,
                ]);
                return $customer;
            }
        }

        return Customer::query()->create(
            [
                'external_id' => $data['external_id'] ?? null,
                'source_system' => $data['source_system'] ?? null,
                'name' => $data['name'],
                'document' => $data['document'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]
        );
    }

    public function findOrCreateDestination(array $data): Destination
    {
        if (!empty($data['external_id'])) {
            $destination = Destination::query()->where('external_id', $data['external_id'])
                ->first();

            if ($destination) {
                $destination->update([
                    'name' => $data['name'],
                    'address' => $data['address'] ?? null,
                    'city' => $data['city'] ?? null,
                    'state' => $data['state'] ?? null,
                ]);
                return $destination;
            }
        }

        if (!empty($data['postal_code'])) {
            $destination = Destination::query()->where('postal_code', $data['postal_code'])
                ->where('name', $data['name'])
                ->first();

            if ($destination) {
                $destination->update([
                    'external_id' => $data['external_id'] ?? null,
                    'source_system' => $data['source_system'] ?? null,
                ]);
                return $destination;
            }
        }

        return Destination::query()->create(
            [
                'external_id' => $data['external_id'] ?? null,
                'source_system' => $data['source_system'] ?? null,
                'name' => $data['name'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
            ]
        );
    }

    public function findOrCreateCarrier(array $data): Carrier
    {
        if (!empty($data['external_id'])) {
            $carrier = Carrier::query()->where('external_id', $data['external_id'])
                ->first();

            if ($carrier) {
                $carrier->update(
                    [
                        'name' => $data['name'],
                        'document' => $data['document'] ?? null,
                        'contact_phone' => $data['contact_phone'] ?? null,
                    ]
                );
                return $carrier;
            }
        }

        if (!empty($data['document'])) {
            $carrier = Carrier::query()->where('document', $data['document'])->first();

            if ($carrier) {
                $carrier->update([
                    'external_id' => $data['external_id'] ?? null,
                    'source_system' => $data['source_system'] ?? null,
                ]);
                return $carrier;
            }
        }

        return Carrier::query()->create([
            'external_id' => $data['external_id'] ?? null,
            'source_system' => $data['source_system'] ?? null,
            'name' => $data['name'],
            'document' => $data['document'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
        ]);
    }

    public function findOrCreateVehicle(array $data): Vehicle
    {
        if (!empty($data['external_id'])) {
            $vehicle = Vehicle::query()->where('external_id', $data['external_id'])
                ->first();

            if ($vehicle) {
                $vehicle->update(
                    [
                        'vehiclePlate' => $data['vehiclePlate'],
                        'model' => $data['model'] ?? null,
                        'capacity' => $data['capacity'] ?? null,
                    ]
                );
                return $vehicle;
            }
        }

        if (!empty($data['vehiclePlate'])) {
            $vehicle = Vehicle::query()->where('vehiclePlate', $data['vehiclePlate'])->first();

            if ($vehicle) {
                $vehicle->update([
                    'external_id' => $data['external_id'] ?? null,
                    'source_system' => $data['source_system'] ?? null,
                ]);
                return $vehicle;
            }
        }


        return Vehicle::query()->create([
            'external_id' => $data['external_id'],
            'source_system' => $data['source_system'],
            'vehiclePlate' => $data['vehiclePlate'],
            'model' => $data['model'] ?? null,
            'carrier_id' => $data['carrier_id'],
        ]);
    }

    public function findOrCreateDriver(array $data): Driver
    {
        if (!empty($data['external_id'])) {
            $driver = Driver::query()->where('external_id', $data['external_id'])
                ->first();

            if ($driver) {
                $driver->update(
                    [
                        'name' => $data['name'],
                        'document' => $data['document'] ?? null,
                        'phone' => $data['phone'] ?? null,
                    ]
                );
                return $driver;
            }
        }

        if (!empty($data['document'])) {
            $driver = Driver::query()->where('document', $data['document'])->first();

            if ($driver) {
                $driver->update([
                    'external_id' => $data['external_id'] ?? null,
                    'source_system' => $data['source_system'] ?? null,
                ]);
                return $driver;
            }
        }

        //dd($data);

        return Driver::query()->create([
            'external_id' => $data['external_id'] ?? null,
            'source_system' => $data['source_system'] ?? null,
            'name' => $data['name'],
            'document' => $data['document'],
            'phone' => $data['phone'] ?? null,
            'carrier_id' => $data['carrier_id'],
        ]);
    }

    public function findOrCreateProduct(array $data): Product
    {
        $product = Product::query()->where('sku', $data['product_sku'])->first();

        if ($product) {
            $product->update([
                'description' => $data['description'],
                'weight' => $data['weight'] ?? null,
                'unit' => $data['unit'] ?? 'un',
                'barcode' => $data['barcode'] ?? null,
            ]);
            return $product;
        }

        return Product::query()->create([
            'sku' => $data['product_sku'],
            'description' => $data['description'],
            'weight' => $data['weight'] ?? null,
            'unit' => $data['unit'] ?? 'un',
            'barcode' => $data['barcode'] ?? null,
        ]);
    }

    public function findOrCreateUser(array $data): User
    {
        $user = User::query()->where('email', $data['email'])->first();

        if ($user) {
            $user->update([
                'name' => $data['name'],
                'external_id' => $data['external_id'] ?? null,
                'source_system' => $data['source_system'] ?? null,
            ]);
            return $user;
        }

        $password = Hash::make('Expedisoft' . $data['email']);

        //dd($password);
        return User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'external_id' => $data['external_id'] ?? null,
            'source_system' => $data['source_system'] ?? null,
            'password' => $password,
        ]);
    }

    public function findOrCreateDock(array $data): Dock
    {
        $dock = Dock::query()->where('external_id', $data['external_id'])
            ->first();

        if ($dock) {
            $dock->update([
                'dock_code' => $data['dock_code'],
                'description' => $data['description'] ?? null,
                'location' => $data['location'] ?? null,
                'external_id' => $data['external_id'] ?? null,
                'source_system' => $data['source_system'] ?? null,
            ]);
            return $dock;
        }

        return Dock::query()->create([
            'external_id' => $data['external_id'] ?? null,
            'source_system' => $data['source_system'] ?? null,
            'dock_code' => $data['dock_code'],
            'description' => $data['description'] ?? null,
            'location' => $data['location'] ?? null,
        ]);
    }
}
