<?php

            namespace App\Http\Requests\Integration;

            use Illuminate\Foundation\Http\FormRequest;

            class OrderIntegrationRequest extends FormRequest
            {
                public function authorize(): bool
                {
                    return true;
                }

                public function rules(): array
                {
                    return [
                        // Identificação da origem
                        'source_system' => ['required', 'string', 'max:50'],

                        // Loading Order principal
                        'loadingOrder' => ['required', 'array'],
                        'loadingOrder.external_id' => ['required', 'string', 'max:100'],
                        'loadingOrder.issue_date' => ['required', 'date'],
                        'loadingOrder.delivery_date' => ['nullable', 'date'],
                        'loadingOrder.status' => ['required', 'string', 'in:pending,in_progress,completed,cancelled'],
                        //'loadingOrder.total_value' => ['nullable', 'numeric', 'min:0'],
                        'loadingOrder.notes' => ['nullable', 'string'],

                        // Customer
                        'loadingOrder.customer' => ['required', 'array'],
                        'loadingOrder.customer.external_id' => ['required', 'string', 'max:100'],
                        'loadingOrder.customer.name' => ['required', 'string', 'max:255'],
                        'loadingOrder.customer.email' => ['nullable', 'email', 'max:255'],
                        'loadingOrder.customer.phone' => ['nullable', 'string', 'max:20'],
                        'loadingOrder.customer.address' => ['nullable', 'string'],

                        // Destination
                        'loadingOrder.destination' => ['required', 'array'],
                        'loadingOrder.destination.external_id' => ['required', 'string', 'max:100'],
                        'loadingOrder.destination.name' => ['required', 'string', 'max:100'],
                        'loadingOrder.destination.address' => ['nullable', 'string'],
                        'loadingOrder.destination.city' => ['nullable', 'string', 'max:100'],
                        'loadingOrder.destination.state' => ['nullable', 'string', 'max:2'],
                        'loadingOrder.destination.postal_code' => ['nullable', 'string', 'max:10'],

                        // Carrier
                        'loadingOrder.carrier' => ['required', 'array'],
                        'loadingOrder.carrier.external_id' => ['required', 'string', 'max:100'],
                        'loadingOrder.carrier.name' => ['required', 'string', 'max:255'],
                        'loadingOrder.carrier.document' => ['nullable', 'string', 'max:20'],
                        'loadingOrder.carrier.email' => ['nullable', 'email', 'max:255'],
                        'loadingOrder.carrier.phone' => ['nullable', 'string', 'max:20'],

                        // Vehicle
                        'loadingOrder.vehicle' => ['required', 'array'],
                        'loadingOrder.vehicle.external_id' => ['required', 'string', 'max:100'],
                        'loadingOrder.vehicle.vehiclePlate' => ['required', 'string', 'max:10'],
                        'loadingOrder.vehicle.model' => ['nullable', 'string', 'max:100'],
                        //'loadingOrder.vehicle.type' => ['nullable', 'string', 'max:50'],

                        // Driver
                        'loadingOrder.driver' => ['required', 'array'],
                        'loadingOrder.driver.external_id' => ['required', 'string', 'max:100'],
                        'loadingOrder.driver.name' => ['required', 'string', 'max:255'],
                        'loadingOrder.driver.document' => ['nullable', 'string', 'max:20'],
                        'loadingOrder.driver.phone' => ['nullable', 'string', 'max:20'],

                        // Items
                        'loadingOrder.items' => ['required', 'array', 'min:1'],
                        'loadingOrder.items.*.product_sku' => ['required', 'string'],
                        'loadingOrder.items.*.product_description' => ['required', 'string'],
                        'loadingOrder.items.*.quantity' => ['required', 'integer', 'min:1'],
                        'loadingOrder.items.*.unit' => ['nullable', 'string'],

                        // Packages
                        'loadingOrder.items.*.packages' => ['nullable', 'array'],
                        'loadingOrder.items.*.packages.*.unique_package_code' => ['required_with:loadingOrder.items.*.packages', 'string'],
                        'loadingOrder.items.*.packages.*.quantity_in_package' => ['required_with:loadingOrder.items.*.packages', 'integer', 'min:1'],
                    ];
                }

                public function messages(): array
                {
                    return [
                        'source_system.required' => 'O identificador do sistema de origem é obrigatório.',

                        'loadingOrder.required' => 'O payload da ordem de carregamento é obrigatório.',
                        'loadingOrder.external_id.required' => 'O ID externo da ordem é obrigatório.',
                        'loadingOrder.status.in' => 'Status inválido. Use: pending, in_progress, completed ou cancelled.',

                        'loadingOrder.customer.external_id.required' => 'O ID externo do cliente é obrigatório.',
                        'loadingOrder.customer.tax_id.required' => 'O CPF/CNPJ do cliente é obrigatório.',

                        'loadingOrder.destination.external_id.required' => 'O ID externo do destino é obrigatório.',

                        'loadingOrder.vehicle.plate.required' => 'A placa do veículo é obrigatória.',

                        'loadingOrder.driver.external_id.required' => 'O ID externo do motorista é obrigatório.',

                        'loadingOrder.items.required' => 'É necessário informar ao menos um item.',
                        'loadingOrder.items.min' => 'É necessário informar ao menos um item.',
                        'loadingOrder.items.*.quantity.min' => 'A quantidade deve ser maior que zero.',
                    ];
                }
            }
