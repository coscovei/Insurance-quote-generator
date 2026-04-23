<?php

namespace App\Http\Controllers;

use App\Models\InsuranceRequest;
use App\Models\Vehicle;
use App\Models\Individual;
use App\Models\Company;
use App\Models\Offer;
use App\Services\LifeIsHardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceController extends Controller
{
    protected $lihService;

    public function __construct(LifeIsHardService $lihService)
    {
        $this->lihService = $lihService;
    }

    public function createOffer(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->all();

            $taxId = $request->input('product.policyholder.taxId');
            if ($taxId) {
                $taxId = preg_replace('/\D+/', '', $taxId);

                data_set($data, 'product.policyholder.taxId', $taxId);
                data_set($data, 'product.vehicle.owner', $data['product']['policyholder']);
            }

            $vehicle = Vehicle::updateOrCreate(
                ['vin' => data_get($data, 'product.vehicle.vin')],
                [
                    'license_plate' => data_get($data, 'product.vehicle.licensePlate') ?? 'B000ABC',
                    'registration_type' => data_get($data, 'product.vehicle.registrationType') ?? 'registered',
                    'vehicle_type' => data_get($data, 'product.vehicle.vehicleType') ?? 'M1',
                    'brand' => data_get($data, 'product.vehicle.brand') ?? 'Dacia',
                    'model' => data_get($data, 'product.vehicle.model') ?? 'Logan',
                    'year_of_construction' => data_get($data, 'product.vehicle.yearOfConstruction') ?? 2022,
                    'engine_displacement' => data_get($data, 'product.vehicle.engineDisplacement') ?? 999,
                    'engine_power' => data_get($data, 'product.vehicle.enginePower') ?? 74,
                    'total_weight' => data_get($data, 'product.vehicle.totalWeight') ?? 1500,
                    'seats' => data_get($data, 'product.vehicle.seats') ?? 5,
                    'fuel_type' => data_get($data, 'product.vehicle.fuelType') ?? 'gasoline',
                    'first_registration' => data_get($data, 'product.vehicle.firstRegistration') ?? '2022-01-01',
                    'usage_type' => data_get($data, 'product.vehicle.usageType') ?? 'personal',
                    'civ_number' => data_get($data, 'product.vehicle.identification.idNumber') ?? 'NECUNOSCUT',
                ]
            );

            $ph = data_get($data, 'product.policyholder');
            $policyholderEntity = null;

            if (!empty($ph['businessName'])) {
                $policyholderEntity = Company::updateOrCreate(
                    ['tax_id' => $ph['taxId']],
                    [
                        'business_name' => $ph['businessName'],
                        'registry_number' => $ph['companyRegistryNumber'] ?? null,
                        'caen_code' => $ph['caenCode'] ?? null,
                    ]
                );
            } else {
                $policyholderEntity = Individual::updateOrCreate(
                    ['tax_id' => $ph['taxId']],
                    [
                        'first_name' => $ph['firstName'] ?? '',
                        'last_name' => $ph['lastName'] ?? '',
                        'birthdate' => $ph['birthdate'] ?? '1980-01-01',
                        'gender' => $ph['gender'] ?? 'm',
                        'id_type' => $ph['identification']['idType'] ?? 'CI',
                        'id_number' => $ph['identification']['idNumber'] ?? '',
                        'is_retired' => $ph['isRetired'] ?? false,
                        'has_disability' => $ph['hasDisability'] ?? false,
                    ]
                );
            }

            $person = $policyholderEntity->person()->updateOrCreate(
                ['email' => $ph['email']],
                ['phone' => $ph['mobileNumber']]
            );

            $addr = $ph['address'];
            $person->address()->updateOrCreate(
                ['person_id' => $person->id],
                [
                    'country' => $addr['country'] ?? 'RO',
                    'county' => $addr['county'],
                    'city' => $addr['city'],
                    'city_code' => $addr['cityCode'],
                    'street' => $addr['street'],
                    'house_number' => $addr['houseNumber'],
                    'building' => $addr['building'] ?? null,
                    'staircase' => $addr['staircase'] ?? null,
                    'apartment' => $addr['apartment'] ?? null,
                    'floor' => $addr['floor'] ?? null,
                    'postcode' => $addr['postcode'] ?? null,
                ]
            );

            $insuranceRequest = InsuranceRequest::create([
                'policyholder_id' => $person->id,
                'owner_id' => $person->id,
                'vehicle_id' => $vehicle->id,
                'target_provider' => data_get($data, 'provider.organization.businessName'),
                'start_date' => data_get($data, 'product.motor.startDate'),
                'term_time' => data_get($data, 'product.motor.termTime', 12),
            ]);

            $token = $this->lihService->getAuthToken();

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'offers' => [],
                    'message' => 'Autentificarea la serviciul extern a eșuat.'
                ], 500);
            }

            $targetProvider = $request->input('provider.organization.businessName');
            $providers = ['asirom', 'grawe', 'axeria', 'hellas_autonom', 'eazy_insure'];

            $isLegalEntity = !empty(data_get($data, 'product.policyholder.businessName'));

            if ($isLegalEntity) {
                $providers = array_values(array_filter($providers, fn ($p) => $p !== 'eazy_insure'));
            }

            if ($targetProvider !== 'all') {
                $providers = [$targetProvider];
            }

            $finalOffers = [];

            foreach ($providers as $name) {
                $currentPayload = $data;
                $currentPayload['provider']['organization']['businessName'] = $name;

                $resData = $this->lihService->requestOffer($currentPayload, $token);

                if (
                    is_array($resData) &&
                    isset($resData['data']['offers'][0])
                ) {
                    $lihOffer = $resData['data']['offers'][0];

                    $localOffer = Offer::create([
                        'insurance_request_id' => $insuranceRequest->id,
                        'insurer_name' => $name,
                        'price' => $lihOffer['premiumAmount'],
                        'external_id' => $lihOffer['offerId'],
                        'raw_data' => $resData,
                        'status' => 'pending'
                    ]);

                    $finalOffers[] = [
                        'id' => $localOffer->id,
                        'insurer' => $name,
                        'price' => $localOffer->price,
                        'details' => $lihOffer,
                        'success' => true
                    ];
                } else {
                    $errorMessage = 'Server indisponibil sau timeout (QA)';

                    if (is_array($resData)) {
                        $errorMessage =
                            $resData['message'] ??
                            $resData['error'] ??
                            'Nu s-au găsit oferte în răspuns.';
                    }

                    $finalOffers[] = [
                        'insurer' => $name,
                        'success' => false,
                        'error' => $errorMessage
                    ];
                }
            }

            usort($finalOffers, function ($a, $b) {
                if ($a['success'] && !$b['success']) return -1;
                if (!$a['success'] && $b['success']) return 1;
                if ($a['success'] && $b['success']) {
                    return $a['price'] <=> $b['price'];
                }
                return 0;
            });

            return response()->json([
                'success' => count(array_filter($finalOffers, fn($o) => $o['success'])) > 0,
                'offers' => $finalOffers
            ]);
        });
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'offer_id' => 'required',
        ]);

        $offer = Offer::find($request->offer_id);

        if (!$offer) {
            return response()->json([
                'success' => false,
                'message' => 'Oferta nu a fost găsită în baza de date.'
            ], 404);
        }

        $offer->update(['status' => 'paid']);

        return response()->json([
            'success' => true,
            'message' => 'Comanda a fost plasată cu succes!',
            'offer' => $offer
        ]);
    }
}
