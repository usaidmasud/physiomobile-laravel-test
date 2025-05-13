<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Http\Requests\GetPatientRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * @OA\Info(
 *     title="Physiomobile API",
 *     version="1.0.0",
 * )
 * @OA\Server(
 *     url="https://physiomobile.syaripmasud.my.id",
 *     description="Production server"
 * )
 * @OA\Server(
 *     url="http://localhost:9008",
 *     description="Local server"
 * )
 */
class PatientController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/patient",
     *     summary="Get all patients",
     *     security={{"accessKey": {}}},
     *     @OA\Parameter(
     *         name="accessKey",
     *         in="header",
     *         required=true,
     *         example="123456",
     *         description="Access key for authentication use (123456)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response="200", description="Success")
     * )
     */
    public function index(GetPatientRequest $request)
    {
        $per_page = $request->page_size ?? 10;
        $current_page = $request->page ?? 1;
        $search = $request->search ?? "";
        $patients = Patient::whereHas("user", function ($query) use ($search) {
            $query->where("name", "like", "%{$search}%");
        })->paginate($per_page, ['*'], 'page', $current_page);
        return apiResponse($patients, "Success", 200);
    }

    /**
     * @OA\Post(
     *     path="/api/patient",
     *     summary="Store a new patient",
     *     security={{"accessKey": {}}},
     *     @OA\Parameter(
     *         name="accessKey",
     *         in="header",
     *         required=true,
     *         example="123456",
     *         description="Access key for authentication use (123456)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="name", type="string", example="John Doe"),
 *             @OA\Property(property="id_type", type="string", example="KTP"),
 *             @OA\Property(property="id_no", type="string", example="123456789"),
 *             @OA\Property(property="gender", type="string", example="male"),
 *             @OA\Property(property="dob", type="string", format="date", example="1990-01-01"),
 *             @OA\Property(property="address", type="string", example="123 Street"),
 *             @OA\Property(property="medium_acquisition", type="string", example="Instagram")
 *         )
 *     ),
     *     @OA\Response(response="200", description="Success")
     * )
     */
    public function store(StorePatientRequest $request)
    {
        $validated = $request->validated();
        try {
            DB::beginTransaction();
            // create user
            $user = User::create([
                "name" => $validated["name"],
                "email" => Str::random(25)."@mail.com",
                "password" => bcrypt('password'),
                "id_type" => $validated["id_type"],
                "id_no" => $validated["id_no"],
                "gender" => $validated["gender"],
                "dob" => $validated["dob"],
                "address" => $validated["address"],
            ]);
                // create patient
                $patient = Patient::create([
                    "user_id" => $user->id,
                    "medium_acquisition" => $validated["medium_acquisition"],
                ]);
            DB::commit();

            return apiResponse($patient->fresh("user"), "Patient created successfully", 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse(null, "Failed to create patient", 500);
        }   
    }

    /**
     * @OA\Get(
     *     path="/api/patient/{id}",
     *     summary="Get a patient by ID",
     *     security={{"accessKey": {}}},
     *     @OA\Parameter(
     *         name="accessKey",
     *         in="header",
     *         required=true,
     *         example="123456",
     *         description="Access key for authentication use (123456)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success")
     * )
     */
    public function show($id)
    {
        $patient = Patient::with("user")->where("id", $id)->first();
        if (!$patient) {
            return apiResponse(null, "Patient not found", 404);
        }
        return apiResponse($patient, "Success", 200);
    }

    /**
     * @OA\Put(
     *     path="/api/patient/{id}",
     *     summary="Update a patient by ID",
     *     security={{"accessKey": {}}},
     *     @OA\Parameter(
     *         name="accessKey",
     *         in="header",
     *         required=true,
     *         example="123456",
     *         description="Access key for authentication use (123456)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="id_type", type="string", example="KTP"),
     *             @OA\Property(property="id_no", type="string", example="123456789"),
     *             @OA\Property(property="gender", type="string", example="male"),
     *             @OA\Property(property="dob", type="string", format="date", example="1990-01-01"),
     *             @OA\Property(property="address", type="string", example="123 Street"),
     *             @OA\Property(property="medium_acquisition", type="string", example="Instagram")
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success")
     * )
     */
    public function update(UpdatePatientRequest $request, $id)
    {
        $patient = Patient::where("id", $id)->with("user")->first();
        if (!$patient) {
            return apiResponse(null, "Patient not found", 404);
        }
        $validated = $request->validated();
        try {
            DB::beginTransaction();
            $patient->user->update([
                "name" => $validated["name"],
                "id_type" => $validated["id_type"],
                "id_no" => $validated["id_no"],
                "gender" => $validated["gender"],
                "dob" => $validated["dob"],
                "address" => $validated["address"],
            ]);
            $patient->update([
                "medium_acquisition" => $validated["medium_acquisition"],
            ]);
            DB::commit();
            return apiResponse($patient->fresh("user"), "Patient updated successfully", 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return apiResponse(null, "Failed to update patient", 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/patient/{id}",
     *     summary="Delete a patient by ID",
     *     security={{"accessKey": {}}},
     *     @OA\Parameter(
     *         name="accessKey",
     *         in="header",
     *         required=true,
     *         example="123456",
     *         description="Access key for authentication use (123456)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response="200", description="Success")
     * )
     */
    public function destroy($id)
    {
        $patient = Patient::find($id);
        if (!$patient) {
            return apiResponse(null, "Patient not found", 404);
        }
        $patient->delete();
        return apiResponse(null, "Patient deleted successfully", 200);
    }
}
