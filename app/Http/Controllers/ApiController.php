<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Center;
use App\Models\Contact;
use App\Models\File;
use App\Models\Image;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    // ================ HELPER METHODS ================

    protected function success($data, $message = null, $code = 200) {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function error($message = null, $code = 400) {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => null
        ], $code);
    }

    protected function notFound($message = 'Resource not found') {
        return $this->error($message, 404);
    }

    protected function unauthorized($message = 'Unauthorized') {
        return $this->error($message, 401);
    }

    // ================ USER AUTH ================

    function tokenDelete(Request $request) {
        $success = false;
        if($request->user()->currentAccessToken()->delete()){
            $success = true;
        }
        return ["success" => $success];
    }

    // generate api token
    function token(Request $request) {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $token = $request->user()->createToken($request->user()->name);
            return ['success'=>true, 'token' => "Bearer ".$token->plainTextToken, "user"=>$request->user()];
        }

        return ["message" => "The provided credentials do not match our records.",
            "success"=>false,
            "errors" => [
                "email"=> [
                    'The provided credentials do not match our records.'
                ]
            ]
        ];
    }


    // ================ MAPS METHODS ================



    // ================ USER METHODS ================

    /**
     * Get all users
     */
    public function getUsers()
    {
        $users = User::with(['contacts', 'alerts', 'incidentReports'])->get();
        return $this->success($users);
    }

    /**
     * Get a single user
     */
    public function getUser($id)
    {
        $user = User::with(['contacts', 'alerts', 'incidentReports'])->find($id);
        if (!$user) return $this->notFound('User not found');
        return $this->success($user);
    }

    /**
     * Create a new user
     */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'sex' => 'sometimes|string',
            'address' => 'sometimes|string',
            'town' => 'sometimes|string',
            'country' => 'sometimes|string',
            'status' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $user = User::create($validator->validated());
        return $this->token($request);
        // return $this->success($user, 'User created successfully', 200);
    }

    /**
     * Update a user
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) return $this->notFound('User not found');

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8',
            'role' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'sex' => 'sometimes|string',
            'address' => 'sometimes|string',
            'town' => 'sometimes|string',
            'country' => 'sometimes|string',
            'status' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $user->update($validator->validated());
        return $this->success($user, 'User updated successfully');
    }

    /**
     * Delete a user
     */
    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) return $this->notFound('User not found');

        $user->delete();
        return $this->success(null, 'User deleted successfully');
    }

    // ================ ALERT METHODS ================

    public function getAlerts()
    {
        $alerts = Alert::with('user')->get();
        return $this->success($alerts);
    }

    public function getAlert($id)
    {
        $alert = Alert::with('user')->find($id);
        if (!$alert) return $this->notFound('Alert not found');
        return $this->success($alert);
    }

    public function createAlert(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'message' => 'nullable|string',
            'initiated_at' => 'nullable|date',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric'
        ]);



        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->user()->id;
        // return $data;
        $alert = Alert::create($data);
        return $this->success($alert, 'Alert created successfully', 200);
    }

    public function updateAlert(Request $request, $id)
    {
        $alert = Alert::find($id);
        if (!$alert) return $this->notFound('Alert not found');

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'status' => 'sometimes|string',
            'message' => 'sometimes|string',
            'initiated_at' => 'sometimes|date',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric',
            'accuracy' => 'sometimes|numeric'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $alert->update($validator->validated());
        return $this->success($alert, 'Alert updated successfully');
    }

    public function deleteAlert($id)
    {
        $alert = Alert::find($id);
        if (!$alert) return $this->notFound('Alert not found');

        $alert->delete();
        return $this->success(null, 'Alert deleted successfully');
    }

    // ================ CENTER METHODS ================

    public function getCenters()
    {
        $this->run();
        $centers = Center::all();
        return $this->success($centers);
    }

    public function getCenter($id)
    {
        $center = Center::with(['user', 'images', 'files'])->find($id);
        if (!$center) return $this->notFound('Center not found');
        return $this->success($center);
    }

    public function createCenter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string',
            'type' => 'sometimes|string',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric',
            'status' => 'sometimes|string',
            'address' => 'sometimes|string',
            'description' => 'sometimes|string',
            'is_verified' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->user()->id;
        $data['type'] = "center";

        $center = Center::create($data);
        return $this->success($center, 'Center created successfully', 200);
    }

    public function run()
    {
        return;
        for ($i = 1; $i <= 1000; $i++) {
            // Zambia's latitude ranges from about -8.2 (north) to -18.1 (south)
            // Longitude ranges from about 22.0 (west) to 33.7 (east)
            $lat = $this->randomFloat(-18.1, -8.2);
            $lng = $this->randomFloat(21.9, 33.7);  // Slightly extended to cover borders

            DB::table('centers')->insert([
                'user_id' => 1,
                'name' => 'Health Center ' . $i,
                'email' => 'center' . $i . '@example.com',
                'phone' => '097' . rand(1000000, 9999999),
                'type' => 'center',
                'lat' => $lat,
                'lng' => $lng,
                'status' => 'active',
                'address' => 'Plot ' . rand(1, 5000) . ', ' . $this->getRandomZambianTown(),
                'description' => 'This is health center number ' . $i,
                'is_verified' => (bool)rand(0, 1),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function randomFloat($min, $max)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    private function getRandomZambianTown()
    {
        $towns = [
            'Lusaka', 'Ndola', 'Kitwe', 'Kabwe', 'Chingola', 'Mufulira', 'Livingstone',
            'Luanshya', 'Kasama', 'Chipata', 'Solwezi', 'Mazabuka', 'Chililabombwe',
            'Mongu', 'Kafue', 'Choma', 'Mansa', 'Kapiri Mposhi', 'Monze', 'Nchelenge',
            'Senanga', 'Sesheke', 'Nakonde', 'Samfya', 'Petauke', 'Mkushi', 'Kalulushi'
        ];
        return $towns[array_rand($towns)] . ', Zambia';
    }

    public function updateCenter(Request $request, $id)
    {
        $center = Center::find($id);
        if (!$center) return $this->notFound('Center not found');

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string',
            'type' => 'sometimes|string',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric',
            'status' => 'sometimes|string',
            'address' => 'sometimes|string',
            'description' => 'sometimes|string',
            'is_verified' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $center->update($validator->validated());
        return $this->success($center, 'Center updated successfully');
    }

    public function searchCenter($filter){
        $centers = Center::where('namae', "LIKE", "%".$filter."%")
        ->orWhere('email', "LIKE", "%".$filter."%")
        ->orWhere('phone', "LIKE", "%".$filter."%")
        ->orWhere('address', "LIKE", "%".$filter."%")
        ->orWhere('description', "LIKE", "%".$filter."%")
        ->get();

        return $this->success($centers);
    }

    public function deleteCenter($id)
    {
        $center = Center::find($id);
        if (!$center) return $this->notFound('Center not found');

        $center->delete();
        return $this->success(null, 'Center deleted successfully');
    }
    public function deleteCenterByname($id)
    {
        $center = Center::where("name",$id);
        if (!$center) return $this->notFound('Center not found');

        $center->delete();
        return $this->success(null, 'Center deleted successfully');
    }

    // ================ CONTACT METHODS ================

    public function getContacts()
    {
        $contacts = Contact::with('user')->get();
        return $this->success($contacts);
    }

    public function getContact($id)
    {
        $contact = Contact::with('user')->find($id);
        if (!$contact) return $this->notFound('Contact not found');
        return $this->success($contact);
    }

    public function createContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'phone' => 'required|string',
            'email' => 'sometimes|email',
            'relationship' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }
        $data = $validator->validated();
        $data['user_id'] = auth()->user()->id;

        $contact = Contact::create($data);

        return $this->success($contact, 'Contact created successfully', 200);
    }

    public function updateContact(Request $request, $id)
    {
        $contact = Contact::find($id);
        if (!$contact) return $this->notFound('Contact not found');

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'email' => 'sometimes|email',
            'relationship' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $contact->update($validator->validated());
        return $this->success($contact, 'Contact updated successfully');
    }

    public function deleteContact($id)
    {
        $contact = Contact::find($id);
        if (!$contact) return $this->notFound('Contact not found');

        $contact->delete();
        return $this->success(null, 'Contact deleted successfully');
    }

    // ================ FILE METHODS ================

    public function getFiles()
    {
        $files = File::all();
        return $this->success($files);
    }

    public function getFile($id)
    {
        $file = File::find($id);
        if (!$file) return $this->notFound('File not found');
        return $this->success($file);
    }

    public function createFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'ref_id' => 'required|integer',
            'file_path' => 'required|string',
            'type' => 'required|string',
            'status' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $file = File::create($validator->validated());
        return $this->success($file, 'File created successfully', 200);
    }

    public function updateFile(Request $request, $id)
    {
        $file = File::find($id);
        if (!$file) return $this->notFound('File not found');

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'ref_id' => 'sometimes|integer',
            'file_path' => 'sometimes|string',
            'type' => 'sometimes|string',
            'status' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $file->update($validator->validated());
        return $this->success($file, 'File updated successfully');
    }

    public function deleteFile($id)
    {
        $file = File::find($id);
        if (!$file) return $this->notFound('File not found');

        $file->delete();
        return $this->success(null, 'File deleted successfully');
    }

    // ================ IMAGE METHODS ================

    public function getImages()
    {
        $images = Image::all();
        return $this->success($images);
    }

    public function getImage($id)
    {
        $image = Image::find($id);
        if (!$image) return $this->notFound('Image not found');
        return $this->success($image);
    }

    public function createImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'ref_id' => 'required|integer',
            'image_path' => 'required|string',
            'type' => 'required|string',
            'status' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $image = Image::create($validator->validated());
        return $this->success($image, 'Image created successfully', 200);
    }

    public function updateImage(Request $request, $id)
    {
        $image = Image::find($id);
        if (!$image) return $this->notFound('Image not found');

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string',
            'ref_id' => 'sometimes|integer',
            'image_path' => 'sometimes|string',
            'type' => 'sometimes|string',
            'status' => 'sometimes|string'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $image->update($validator->validated());
        return $this->success($image, 'Image updated successfully');
    }

    public function deleteImage($id)
    {
        $image = Image::find($id);
        if (!$image) return $this->notFound('Image not found');

        $image->delete();
        return $this->success(null, 'Image deleted successfully');
    }

    // ================ INCIDENT METHODS ================

    public function getIncidents()
    {
        $incidents = Incident::with('user')->get();
        return $this->success($incidents);
    }

    public function getIncident($id)
    {
        $incident = Incident::with('user')->find($id);
        if (!$incident) return $this->notFound('Incident not found');
        return $this->success($incident);
    }

    public function createIncident(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string',
            'type' => 'sometimes|string',
            'area' => 'sometimes|string',
            'details' => 'sometimes|string',
            'status' => 'sometimes|string',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $incident = Incident::create($validator->validated());
        return $this->success($incident, 'Incident created successfully', 200);
    }

    public function updateIncident(Request $request, $id)
    {
        $incident = Incident::find($id);
        if (!$incident) return $this->notFound('Incident not found');

        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,id',
            'name' => 'sometimes|string',
            'type' => 'sometimes|string',
            'area' => 'sometimes|string',
            'details' => 'sometimes|string',
            'status' => 'sometimes|string',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $incident->update($validator->validated());
        return $this->success($incident, 'Incident updated successfully');
    }

    public function deleteIncident($id)
    {
        $incident = Incident::find($id);
        if (!$incident) return $this->notFound('Incident not found');

        $incident->delete();
        return $this->success(null, 'Incident deleted successfully');
    }
}
