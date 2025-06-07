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
use Illuminate\Support\Facades\Mail;
use App\Mail\alertMessage;

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


    public function getProfile()
    {
        $user = User::with(['contacts', 'alerts', 'incidentReports'])->find(auth()->id());
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

    public function sendAlert(Request $request) {
        $data = $this->createAlert($request);
        if ($data['success']){
            $alert = $data['data'];
        }else{
            return $this->error($data, 402);
        }

        $contacts = Contact::all();
        foreach($contacts as $contact){
            if (!Mail::to($contact->email)->send(new alertMessage($alert))){
                return $this->error("We are so sory!, But something went wrong and we could not send your message!");
            }
            if($alert->status != 'sent'){
                $alert->status = "sent";
                $alert->save();
            }
        }

        return $this->success(null, "Emergence message sent! Successfully");
    }

    public function getAlerts() {
        $alerts = Alert::with('user')->get();
        return $this->success($alerts);
    }

    public function getAlert($id) {
        $alert = Alert::with('user')->find($id);
        if (!$alert) return $this->notFound('Alert not found');
        return $this->success($alert);
    }

    public function createAlert(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'message' => 'nullable|string',
            'initiated_at' => 'nullable|date',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric'
        ]);



        if ($validator->fails()) {
            return ['success'=>false, 'data'=>$validator->errors()];
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->user()->id;
        $alert = Alert::create($data);
        return ['success'=>true, 'data'=>$alert];
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
        $centers = Center::all();
        if (!count($centers)){
            $this->run();
            $centers = Center::all();
        }
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
        $i = 0;
        foreach($this->dummyCenters as $center) {
            $i++;
            DB::table('centers')->insert([
                'user_id' => 1,
                'name' => 'Health Center ' . $i,
                'email' => 'center' . $i . '@example.com',
                'phone' => $center['phone'],  //'097' . rand(1000000, 9999999),
                'type' => 'center',
                'lat' => $center['lat'],
                'lng' => $center['lng'],
                'status' => 'active',
                'address' => $center['address'],
                'description' => 'This is health center number ' . $i,
                'is_verified' => $center['is_verified'],
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
        // return $filter;
        $centers = Center::where('name', "LIKE", "%".$filter."%")
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
            'name' => 'required|string',
            'type' => 'sometimes|string',
            'area' => 'sometimes|string',
            'details' => 'sometimes|string',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric'
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 422);
        }

        $data = $validator->validated();
        $data['user_id'] = auth()->user()->id;
        $data['type'] = "Incident";

        $incident = Incident::create($data);
        return $this->success($incident, 'Incident created successfully', 200);
    }

    public function updateIncident(Request $request, $id)
    {
        $incident = Incident::find($id);
        if (!$incident) return $this->notFound('Incident not found');

        $validator = Validator::make($request->all(), [
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

    public $dummyCenters = [
        [
            "id"=> 3678,
            "user_id"=> 1,
            "name"=> "Health Center 678",
            "email"=> "center678@example.com",
            "phone"=> "0976699646",
            "type"=> "center",
            "lat"=> -14.972017146774,
            "lng"=> 24.498926316015,
            "status"=> "active",
            "address"=> "Plot 2460, Sesheke, Zambia",
            "description"=> "This is health center number 678",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3679,
            "user_id"=> 1,
            "name"=> "Health Center 679",
            "email"=> "center679@example.com",
            "phone"=> "0979503043",
            "type"=> "center",
            "lat"=> -11.454347636762,
            "lng"=> 33.278300806963,
            "status"=> "active",
            "address"=> "Plot 3021, Ndola, Zambia",
            "description"=> "This is health center number 679",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3681,
            "user_id"=> 1,
            "name"=> "Health Center 681",
            "email"=> "center681@example.com",
            "phone"=> "0974706407",
            "type"=> "center",
            "lat"=> -14.30513308598,
            "lng"=> 32.298580809961,
            "status"=> "active",
            "address"=> "Plot 3147, Kasama, Zambia",
            "description"=> "This is health center number 681",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3683,
            "user_id"=> 1,
            "name"=> "Health Center 683",
            "email"=> "center683@example.com",
            "phone"=> "0975732994",
            "type"=> "center",
            "lat"=> -16.160152550116,
            "lng"=> 22.549059230764,
            "status"=> "active",
            "address"=> "Plot 4534, Mazabuka, Zambia",
            "description"=> "This is health center number 683",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3685,
            "user_id"=> 1,
            "name"=> "Health Center 685",
            "email"=> "center685@example.com",
            "phone"=> "0976842562",
            "type"=> "center",
            "lat"=> -11.660054459637,
            "lng"=> 28.376281515824,
            "status"=> "active",
            "address"=> "Plot 4656, Ndola, Zambia",
            "description"=> "This is health center number 685",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3686,
            "user_id"=> 1,
            "name"=> "Health Center 686",
            "email"=> "center686@example.com",
            "phone"=> "0979988543",
            "type"=> "center",
            "lat"=> -16.910381005244,
            "lng"=> 25.014597061795,
            "status"=> "active",
            "address"=> "Plot 2264, Kabwe, Zambia",
            "description"=> "This is health center number 686",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3689,
            "user_id"=> 1,
            "name"=> "Health Center 689",
            "email"=> "center689@example.com",
            "phone"=> "0979214752",
            "type"=> "center",
            "lat"=> -13.028275493592,
            "lng"=> 23.631743179789,
            "status"=> "active",
            "address"=> "Plot 1701, Chililabombwe, Zambia",
            "description"=> "This is health center number 689",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3690,
            "user_id"=> 1,
            "name"=> "Health Center 690",
            "email"=> "center690@example.com",
            "phone"=> "0977516055",
            "type"=> "center",
            "lat"=> -13.798000214434,
            "lng"=> 28.423177704924,
            "status"=> "active",
            "address"=> "Plot 2180, Kalulushi, Zambia",
            "description"=> "This is health center number 690",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3692,
            "user_id"=> 1,
            "name"=> "Health Center 692",
            "email"=> "center692@example.com",
            "phone"=> "0979879822",
            "type"=> "center",
            "lat"=> -9.3616222730659,
            "lng"=> 30.10342302872,
            "status"=> "active",
            "address"=> "Plot 1383, Monze, Zambia",
            "description"=> "This is health center number 692",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3697,
            "user_id"=> 1,
            "name"=> "Health Center 697",
            "email"=> "center697@example.com",
            "phone"=> "0979708936",
            "type"=> "center",
            "lat"=> -13.464232806938,
            "lng"=> 26.377901780269,
            "status"=> "active",
            "address"=> "Plot 1457, Kabwe, Zambia",
            "description"=> "This is health center number 697",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3698,
            "user_id"=> 1,
            "name"=> "Health Center 698",
            "email"=> "center698@example.com",
            "phone"=> "0977797344",
            "type"=> "center",
            "lat"=> -10.974201568577,
            "lng"=> 30.484732104924,
            "status"=> "active",
            "address"=> "Plot 1999, Solwezi, Zambia",
            "description"=> "This is health center number 698",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3699,
            "user_id"=> 1,
            "name"=> "Health Center 699",
            "email"=> "center699@example.com",
            "phone"=> "0973787411",
            "type"=> "center",
            "lat"=> -16.724403393746,
            "lng"=> 23.60309304516,
            "status"=> "active",
            "address"=> "Plot 1741, Petauke, Zambia",
            "description"=> "This is health center number 699",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3700,
            "user_id"=> 1,
            "name"=> "Health Center 700",
            "email"=> "center700@example.com",
            "phone"=> "0971506739",
            "type"=> "center",
            "lat"=> -17.397147828945,
            "lng"=> 26.573342033696,
            "status"=> "active",
            "address"=> "Plot 4333, Kafue, Zambia",
            "description"=> "This is health center number 700",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3701,
            "user_id"=> 1,
            "name"=> "Health Center 701",
            "email"=> "center701@example.com",
            "phone"=> "0975381694",
            "type"=> "center",
            "lat"=> -15.327891028453,
            "lng"=> 29.761261051456,
            "status"=> "active",
            "address"=> "Plot 900, Petauke, Zambia",
            "description"=> "This is health center number 701",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3702,
            "user_id"=> 1,
            "name"=> "Health Center 702",
            "email"=> "center702@example.com",
            "phone"=> "0978194801",
            "type"=> "center",
            "lat"=> -13.349876748002,
            "lng"=> 27.805865633723,
            "status"=> "active",
            "address"=> "Plot 3226, Mansa, Zambia",
            "description"=> "This is health center number 702",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3703,
            "user_id"=> 1,
            "name"=> "Health Center 703",
            "email"=> "center703@example.com",
            "phone"=> "0973385401",
            "type"=> "center",
            "lat"=> -17.020750572356,
            "lng"=> 25.087192830065,
            "status"=> "active",
            "address"=> "Plot 1353, Sesheke, Zambia",
            "description"=> "This is health center number 703",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3704,
            "user_id"=> 1,
            "name"=> "Health Center 704",
            "email"=> "center704@example.com",
            "phone"=> "0973141784",
            "type"=> "center",
            "lat"=> -12.519878856102,
            "lng"=> 24.581385898907,
            "status"=> "active",
            "address"=> "Plot 1420, Mansa, Zambia",
            "description"=> "This is health center number 704",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3705,
            "user_id"=> 1,
            "name"=> "Health Center 705",
            "email"=> "center705@example.com",
            "phone"=> "0974698825",
            "type"=> "center",
            "lat"=> -14.508372976029,
            "lng"=> 22.009449633076,
            "status"=> "active",
            "address"=> "Plot 2727, Kapiri Mposhi, Zambia",
            "description"=> "This is health center number 705",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3706,
            "user_id"=> 1,
            "name"=> "Health Center 706",
            "email"=> "center706@example.com",
            "phone"=> "0971348987",
            "type"=> "center",
            "lat"=> -14.861749678367,
            "lng"=> 22.905860539622,
            "status"=> "active",
            "address"=> "Plot 1507, Kabwe, Zambia",
            "description"=> "This is health center number 706",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3707,
            "user_id"=> 1,
            "name"=> "Health Center 707",
            "email"=> "center707@example.com",
            "phone"=> "0974271456",
            "type"=> "center",
            "lat"=> -14.380895866584,
            "lng"=> 28.343086217364,
            "status"=> "active",
            "address"=> "Plot 1670, Chililabombwe, Zambia",
            "description"=> "This is health center number 707",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3708,
            "user_id"=> 1,
            "name"=> "Health Center 708",
            "email"=> "center708@example.com",
            "phone"=> "0973447632",
            "type"=> "center",
            "lat"=> -15.124250746623,
            "lng"=> 29.78161750598,
            "status"=> "active",
            "address"=> "Plot 3366, Nakonde, Zambia",
            "description"=> "This is health center number 708",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3711,
            "user_id"=> 1,
            "name"=> "Health Center 711",
            "email"=> "center711@example.com",
            "phone"=> "0973925970",
            "type"=> "center",
            "lat"=> -12.332523444543,
            "lng"=> 32.617228298968,
            "status"=> "active",
            "address"=> "Plot 3781, Mufulira, Zambia",
            "description"=> "This is health center number 711",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3713,
            "user_id"=> 1,
            "name"=> "Health Center 713",
            "email"=> "center713@example.com",
            "phone"=> "0975593035",
            "type"=> "center",
            "lat"=> -14.311396281659,
            "lng"=> 26.154114452588,
            "status"=> "active",
            "address"=> "Plot 3216, Sesheke, Zambia",
            "description"=> "This is health center number 713",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3718,
            "user_id"=> 1,
            "name"=> "Health Center 718",
            "email"=> "center718@example.com",
            "phone"=> "0975532877",
            "type"=> "center",
            "lat"=> -15.886203200643,
            "lng"=> 22.475304690923,
            "status"=> "active",
            "address"=> "Plot 4757, Samfya, Zambia",
            "description"=> "This is health center number 718",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3719,
            "user_id"=> 1,
            "name"=> "Health Center 719",
            "email"=> "center719@example.com",
            "phone"=> "0971207312",
            "type"=> "center",
            "lat"=> -14.020576479947,
            "lng"=> 26.746987414009,
            "status"=> "active",
            "address"=> "Plot 1685, Senanga, Zambia",
            "description"=> "This is health center number 719",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3720,
            "user_id"=> 1,
            "name"=> "Health Center 720",
            "email"=> "center720@example.com",
            "phone"=> "0979446355",
            "type"=> "center",
            "lat"=> -12.332823420285,
            "lng"=> 27.40258665844,
            "status"=> "active",
            "address"=> "Plot 3859, Chingola, Zambia",
            "description"=> "This is health center number 720",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3723,
            "user_id"=> 1,
            "name"=> "Health Center 723",
            "email"=> "center723@example.com",
            "phone"=> "0976131399",
            "type"=> "center",
            "lat"=> -17.138157311286,
            "lng"=> 25.131257607523,
            "status"=> "active",
            "address"=> "Plot 492, Ndola, Zambia",
            "description"=> "This is health center number 723",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3725,
            "user_id"=> 1,
            "name"=> "Health Center 725",
            "email"=> "center725@example.com",
            "phone"=> "0976312990",
            "type"=> "center",
            "lat"=> -16.6118914872,
            "lng"=> 22.617305527123,
            "status"=> "active",
            "address"=> "Plot 591, Kapiri Mposhi, Zambia",
            "description"=> "This is health center number 725",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3727,
            "user_id"=> 1,
            "name"=> "Health Center 727",
            "email"=> "center727@example.com",
            "phone"=> "0977244030",
            "type"=> "center",
            "lat"=> -11.282454889027,
            "lng"=> 29.444379938368,
            "status"=> "active",
            "address"=> "Plot 4578, Ndola, Zambia",
            "description"=> "This is health center number 727",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3733,
            "user_id"=> 1,
            "name"=> "Health Center 733",
            "email"=> "center733@example.com",
            "phone"=> "0978608818",
            "type"=> "center",
            "lat"=> -15.735628858039,
            "lng"=> 28.783405199406,
            "status"=> "active",
            "address"=> "Plot 1938, Monze, Zambia",
            "description"=> "This is health center number 733",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3734,
            "user_id"=> 1,
            "name"=> "Health Center 734",
            "email"=> "center734@example.com",
            "phone"=> "0972191924",
            "type"=> "center",
            "lat"=> -13.821233734405,
            "lng"=> 29.719041681671,
            "status"=> "active",
            "address"=> "Plot 2876, Livingstone, Zambia",
            "description"=> "This is health center number 734",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3735,
            "user_id"=> 1,
            "name"=> "Health Center 735",
            "email"=> "center735@example.com",
            "phone"=> "0976930268",
            "type"=> "center",
            "lat"=> -16.49799365769,
            "lng"=> 27.145447930435,
            "status"=> "active",
            "address"=> "Plot 3529, Lusaka, Zambia",
            "description"=> "This is health center number 735",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3736,
            "user_id"=> 1,
            "name"=> "Health Center 736",
            "email"=> "center736@example.com",
            "phone"=> "0971093820",
            "type"=> "center",
            "lat"=> -13.214294715372,
            "lng"=> 23.413120022003,
            "status"=> "active",
            "address"=> "Plot 2574, Kabwe, Zambia",
            "description"=> "This is health center number 736",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3737,
            "user_id"=> 1,
            "name"=> "Health Center 737",
            "email"=> "center737@example.com",
            "phone"=> "0971753961",
            "type"=> "center",
            "lat"=> -13.616531270145,
            "lng"=> 25.95527682279,
            "status"=> "active",
            "address"=> "Plot 4856, Kapiri Mposhi, Zambia",
            "description"=> "This is health center number 737",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3738,
            "user_id"=> 1,
            "name"=> "Health Center 738",
            "email"=> "center738@example.com",
            "phone"=> "0972492702",
            "type"=> "center",
            "lat"=> -12.279479066506,
            "lng"=> 33.395501250073,
            "status"=> "active",
            "address"=> "Plot 3179, Solwezi, Zambia",
            "description"=> "This is health center number 738",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3739,
            "user_id"=> 1,
            "name"=> "Health Center 739",
            "email"=> "center739@example.com",
            "phone"=> "0975582077",
            "type"=> "center",
            "lat"=> -17.158787500979,
            "lng"=> 26.665915818403,
            "status"=> "active",
            "address"=> "Plot 214, Mansa, Zambia",
            "description"=> "This is health center number 739",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3740,
            "user_id"=> 1,
            "name"=> "Health Center 740",
            "email"=> "center740@example.com",
            "phone"=> "0972914795",
            "type"=> "center",
            "lat"=> -16.668332063625,
            "lng"=> 26.080250060642,
            "status"=> "active",
            "address"=> "Plot 3946, Mazabuka, Zambia",
            "description"=> "This is health center number 740",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3741,
            "user_id"=> 1,
            "name"=> "Health Center 741",
            "email"=> "center741@example.com",
            "phone"=> "0979461183",
            "type"=> "center",
            "lat"=> -15.331071439772,
            "lng"=> 25.561231408204,
            "status"=> "active",
            "address"=> "Plot 4885, Kafue, Zambia",
            "description"=> "This is health center number 741",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3742,
            "user_id"=> 1,
            "name"=> "Health Center 742",
            "email"=> "center742@example.com",
            "phone"=> "0972375998",
            "type"=> "center",
            "lat"=> -13.830764070214,
            "lng"=> 26.057906652315,
            "status"=> "active",
            "address"=> "Plot 1146, Sesheke, Zambia",
            "description"=> "This is health center number 742",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3743,
            "user_id"=> 1,
            "name"=> "Health Center 743",
            "email"=> "center743@example.com",
            "phone"=> "0971175743",
            "type"=> "center",
            "lat"=> -17.209156996202,
            "lng"=> 23.764053551417,
            "status"=> "active",
            "address"=> "Plot 1061, Monze, Zambia",
            "description"=> "This is health center number 743",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3746,
            "user_id"=> 1,
            "name"=> "Health Center 746",
            "email"=> "center746@example.com",
            "phone"=> "0972584573",
            "type"=> "center",
            "lat"=> -9.5292619668549,
            "lng"=> 29.939769574366,
            "status"=> "active",
            "address"=> "Plot 1288, Chingola, Zambia",
            "description"=> "This is health center number 746",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3747,
            "user_id"=> 1,
            "name"=> "Health Center 747",
            "email"=> "center747@example.com",
            "phone"=> "0974450194",
            "type"=> "center",
            "lat"=> -12.452459070716,
            "lng"=> 29.968744732099,
            "status"=> "active",
            "address"=> "Plot 3836, Livingstone, Zambia",
            "description"=> "This is health center number 747",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3748,
            "user_id"=> 1,
            "name"=> "Health Center 748",
            "email"=> "center748@example.com",
            "phone"=> "0972727299",
            "type"=> "center",
            "lat"=> -17.085427416808,
            "lng"=> 24.338058287296,
            "status"=> "active",
            "address"=> "Plot 1924, Kabwe, Zambia",
            "description"=> "This is health center number 748",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3750,
            "user_id"=> 1,
            "name"=> "Health Center 750",
            "email"=> "center750@example.com",
            "phone"=> "0976289088",
            "type"=> "center",
            "lat"=> -13.785133260482,
            "lng"=> 27.839548436617,
            "status"=> "active",
            "address"=> "Plot 2927, Nakonde, Zambia",
            "description"=> "This is health center number 750",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3751,
            "user_id"=> 1,
            "name"=> "Health Center 751",
            "email"=> "center751@example.com",
            "phone"=> "0972166898",
            "type"=> "center",
            "lat"=> -15.010931192157,
            "lng"=> 23.494948850942,
            "status"=> "active",
            "address"=> "Plot 1958, Chipata, Zambia",
            "description"=> "This is health center number 751",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3752,
            "user_id"=> 1,
            "name"=> "Health Center 752",
            "email"=> "center752@example.com",
            "phone"=> "0978223540",
            "type"=> "center",
            "lat"=> -10.019162741825,
            "lng"=> 31.053882066791,
            "status"=> "active",
            "address"=> "Plot 1958, Monze, Zambia",
            "description"=> "This is health center number 752",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3755,
            "user_id"=> 1,
            "name"=> "Health Center 755",
            "email"=> "center755@example.com",
            "phone"=> "0973731801",
            "type"=> "center",
            "lat"=> -13.683564295659,
            "lng"=> 23.50763187325,
            "status"=> "active",
            "address"=> "Plot 1426, Solwezi, Zambia",
            "description"=> "This is health center number 755",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3756,
            "user_id"=> 1,
            "name"=> "Health Center 756",
            "email"=> "center756@example.com",
            "phone"=> "0975287496",
            "type"=> "center",
            "lat"=> -16.281470323625,
            "lng"=> 22.446653457334,
            "status"=> "active",
            "address"=> "Plot 4819, Lusaka, Zambia",
            "description"=> "This is health center number 756",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3757,
            "user_id"=> 1,
            "name"=> "Health Center 757",
            "email"=> "center757@example.com",
            "phone"=> "0978253225",
            "type"=> "center",
            "lat"=> -14.464516158385,
            "lng"=> 25.646554223144,
            "status"=> "active",
            "address"=> "Plot 948, Mufulira, Zambia",
            "description"=> "This is health center number 757",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3758,
            "user_id"=> 1,
            "name"=> "Health Center 758",
            "email"=> "center758@example.com",
            "phone"=> "0976397648",
            "type"=> "center",
            "lat"=> -9.6783291745364,
            "lng"=> 31.207338215926,
            "status"=> "active",
            "address"=> "Plot 2351, Senanga, Zambia",
            "description"=> "This is health center number 758",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3759,
            "user_id"=> 1,
            "name"=> "Health Center 759",
            "email"=> "center759@example.com",
            "phone"=> "0977568333",
            "type"=> "center",
            "lat"=> -10.646135433552,
            "lng"=> 30.812051223084,
            "status"=> "active",
            "address"=> "Plot 4077, Chililabombwe, Zambia",
            "description"=> "This is health center number 759",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3760,
            "user_id"=> 1,
            "name"=> "Health Center 760",
            "email"=> "center760@example.com",
            "phone"=> "0971553763",
            "type"=> "center",
            "lat"=> -9.2918087946679,
            "lng"=> 30.828927098927,
            "status"=> "active",
            "address"=> "Plot 2915, Mkushi, Zambia",
            "description"=> "This is health center number 760",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3763,
            "user_id"=> 1,
            "name"=> "Health Center 763",
            "email"=> "center763@example.com",
            "phone"=> "0974731356",
            "type"=> "center",
            "lat"=> -13.484883469848,
            "lng"=> 23.594061237152,
            "status"=> "active",
            "address"=> "Plot 3146, Lusaka, Zambia",
            "description"=> "This is health center number 763",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:27.000000Z",
            "updated_at"=> "2025-06-01T15:33:27.000000Z"
        ],
        [
            "id"=> 3764,
            "user_id"=> 1,
            "name"=> "Health Center 764",
            "email"=> "center764@example.com",
            "phone"=> "0973787498",
            "type"=> "center",
            "lat"=> -13.21089419737,
            "lng"=> 32.279136272976,
            "status"=> "active",
            "address"=> "Plot 971, Livingstone, Zambia",
            "description"=> "This is health center number764",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3765,
            "user_id"=> 1,
            "name"=> "Health Center 765",
            "email"=> "center765@example.com",
            "phone"=> "0976547853",
            "type"=> "center",
            "lat"=> -10.911160857375,
            "lng"=> 29.934744010882,
            "status"=> "active",
            "address"=> "Plot 2311, Kasama, Zambia",
            "description"=> "This is health center number 765",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3767,
            "user_id"=> 1,
            "name"=> "Health Center 767",
            "email"=> "center767@example.com",
            "phone"=> "0971605112",
            "type"=> "center",
            "lat"=> -15.943291751409,
            "lng"=> 24.978787473346,
            "status"=> "active",
            "address"=> "Plot 2536, Mazabuka, Zambia",
            "description"=> "This is health center number 767",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3774,
            "user_id"=> 1,
            "name"=> "Health Center 774",
            "email"=> "center774@example.com",
            "phone"=> "0971531144",
            "type"=> "center",
            "lat"=> -14.711672160501,
            "lng"=> 25.366986774498,
            "status"=> "active",
            "address"=> "Plot 3380, Nakonde, Zambia",
            "description"=> "This is health center number 774",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3778,
            "user_id"=> 1,
            "name"=> "Health Center 778",
            "email"=> "center778@example.com",
            "phone"=> "0976600055",
            "type"=> "center",
            "lat"=> -11.586171623825,
            "lng"=> 28.852826004453,
            "status"=> "active",
            "address"=> "Plot 2396, Kalulushi, Zambia",
            "description"=> "This is health center number 778",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3780,
            "user_id"=> 1,
            "name"=> "Health Center 780",
            "email"=> "center780@example.com",
            "phone"=> "0972320951",
            "type"=> "center",
            "lat"=> -12.430760866884,
            "lng"=> 29.447695161937,
            "status"=> "active",
            "address"=> "Plot 2931, Kalulushi, Zambia",
            "description"=> "This is health center number 780",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3781,
            "user_id"=> 1,
            "name"=> "Health Center 781",
            "email"=> "center781@example.com",
            "phone"=> "0978910255",
            "type"=> "center",
            "lat"=> -13.648369041294,
            "lng"=> 27.586941102467,
            "status"=> "active",
            "address"=> "Plot 4293, Choma, Zambia",
            "description"=> "This is health center number 781",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3782,
            "user_id"=> 1,
            "name"=> "Health Center 782",
            "email"=> "center782@example.com",
            "phone"=> "0975444209",
            "type"=> "center",
            "lat"=> -14.099705631938,
            "lng"=> 30.466471879355,
            "status"=> "active",
            "address"=> "Plot 2654, Kalulushi, Zambia",
            "description"=> "This is health center number 782",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3783,
            "user_id"=> 1,
            "name"=> "Health Center 783",
            "email"=> "center783@example.com",
            "phone"=> "0979497569",
            "type"=> "center",
            "lat"=> -9.952263172554,
            "lng"=> 31.211681901809,
            "status"=> "active",
            "address"=> "Plot 590, Petauke, Zambia",
            "description"=> "This is health center number 783",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3784,
            "user_id"=> 1,
            "name"=> "Health Center 784",
            "email"=> "center784@example.com",
            "phone"=> "0974482371",
            "type"=> "center",
            "lat"=> -11.92916456006,
            "lng"=> 32.510152794519,
            "status"=> "active",
            "address"=> "Plot 1741, Kabwe, Zambia",
            "description"=> "This is health center number 784",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3785,
            "user_id"=> 1,
            "name"=> "Health Center 785",
            "email"=> "center785@example.com",
            "phone"=> "0978713863",
            "type"=> "center",
            "lat"=> -13.734632065443,
            "lng"=> 22.892255368453,
            "status"=> "active",
            "address"=> "Plot 4232, Chililabombwe, Zambia",
            "description"=> "This is health center number 785",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3786,
            "user_id"=> 1,
            "name"=> "Health Center 786",
            "email"=> "center786@example.com",
            "phone"=> "0972470124",
            "type"=> "center",
            "lat"=> -14.804445907569,
            "lng"=> 26.684561136265,
            "status"=> "active",
            "address"=> "Plot 243, Samfya, Zambia",
            "description"=> "This is health center number 786",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3787,
            "user_id"=> 1,
            "name"=> "Health Center 787",
            "email"=> "center787@example.com",
            "phone"=> "0978201247",
            "type"=> "center",
            "lat"=> -15.34824255926,
            "lng"=> 25.114005409095,
            "status"=> "active",
            "address"=> "Plot 3932, Samfya, Zambia",
            "description"=> "This is health center number 787",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3788,
            "user_id"=> 1,
            "name"=> "Health Center 788",
            "email"=> "center788@example.com",
            "phone"=> "0972542237",
            "type"=> "center",
            "lat"=> -10.301627248713,
            "lng"=> 33.237226740987,
            "status"=> "active",
            "address"=> "Plot 2690, Solwezi, Zambia",
            "description"=> "This is health center number 788",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3790,
            "user_id"=> 1,
            "name"=> "Health Center 790",
            "email"=> "center790@example.com",
            "phone"=> "0977363269",
            "type"=> "center",
            "lat"=> -11.241632689974,
            "lng"=> 32.533576525111,
            "status"=> "active",
            "address"=> "Plot 3506, Lusaka, Zambia",
            "description"=> "This is health center number 790",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3792,
            "user_id"=> 1,
            "name"=> "Health Center 792",
            "email"=> "center792@example.com",
            "phone"=> "0974547098",
            "type"=> "center",
            "lat"=> -14.187986326026,
            "lng"=> 33.395407135084,
            "status"=> "active",
            "address"=> "Plot 4969, Choma, Zambia",
            "description"=> "This is health center number 792",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3794,
            "user_id"=> 1,
            "name"=> "Health Center 794",
            "email"=> "center794@example.com",
            "phone"=> "0979390974",
            "type"=> "center",
            "lat"=> -13.712138642376,
            "lng"=> 25.698346743453,
            "status"=> "active",
            "address"=> "Plot 1683, Ndola, Zambia",
            "description"=> "This is health center number 794",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3796,
            "user_id"=> 1,
            "name"=> "Health Center 796",
            "email"=> "center796@example.com",
            "phone"=> "0978174018",
            "type"=> "center",
            "lat"=> -9.2073361190536,
            "lng"=> 29.623721352557,
            "status"=> "active",
            "address"=> "Plot 3353, Choma, Zambia",
            "description"=> "This is health center number 796",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3797,
            "user_id"=> 1,
            "name"=> "Health Center 797",
            "email"=> "center797@example.com",
            "phone"=> "0978714521",
            "type"=> "center",
            "lat"=> -10.071930752169,
            "lng"=> 29.532210455105,
            "status"=> "active",
            "address"=> "Plot 1363, Lusaka, Zambia",
            "description"=> "This is health center number 797",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3798,
            "user_id"=> 1,
            "name"=> "Health Center 798",
            "email"=> "center798@example.com",
            "phone"=> "0976271731",
            "type"=> "center",
            "lat"=> -10.425849425432,
            "lng"=> 33.656568740381,
            "status"=> "active",
            "address"=> "Plot 926, Kapiri Mposhi, Zambia",
            "description"=> "This is health center number 798",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3799,
            "user_id"=> 1,
            "name"=> "Health Center 799",
            "email"=> "center799@example.com",
            "phone"=> "0976910289",
            "type"=> "center",
            "lat"=> -11.192602712984,
            "lng"=> 30.523206398181,
            "status"=> "active",
            "address"=> "Plot 1912, Chingola, Zambia",
            "description"=> "This is health center number 799",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3802,
            "user_id"=> 1,
            "name"=> "Health Center 802",
            "email"=> "center802@example.com",
            "phone"=> "0975758216",
            "type"=> "center",
            "lat"=> -10.661089848942,
            "lng"=> 32.489308808087,
            "status"=> "active",
            "address"=> "Plot 1235, Lusaka, Zambia",
            "description"=> "This is health center number 802",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3803,
            "user_id"=> 1,
            "name"=> "Health Center 803",
            "email"=> "center803@example.com",
            "phone"=> "0971489537",
            "type"=> "center",
            "lat"=> -17.142865584298,
            "lng"=> 24.542148707175,
            "status"=> "active",
            "address"=> "Plot 4789, Kabwe, Zambia",
            "description"=> "This is health center number 803",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3804,
            "user_id"=> 1,
            "name"=> "Health Center 804",
            "email"=> "center804@example.com",
            "phone"=> "0978549759",
            "type"=> "center",
            "lat"=> -14.61070602844,
            "lng"=> 21.919401655541,
            "status"=> "active",
            "address"=> "Plot 3290, Petauke, Zambia",
            "description"=> "This is health center number 804",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3806,
            "user_id"=> 1,
            "name"=> "Health Center 806",
            "email"=> "center806@example.com",
            "phone"=> "0971637274",
            "type"=> "center",
            "lat"=> -14.847254829317,
            "lng"=> 30.26783977168,
            "status"=> "active",
            "address"=> "Plot 2308, Kapiri Mposhi, Zambia",
            "description"=> "This is health center number 806",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3809,
            "user_id"=> 1,
            "name"=> "Health Center 809",
            "email"=> "center809@example.com",
            "phone"=> "0971085969",
            "type"=> "center",
            "lat"=> -15.640759927705,
            "lng"=> 26.578503379262,
            "status"=> "active",
            "address"=> "Plot 307, Chingola, Zambia",
            "description"=> "This is health center number 809",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3810,
            "user_id"=> 1,
            "name"=> "Health Center 810",
            "email"=> "center810@example.com",
            "phone"=> "0972378865",
            "type"=> "center",
            "lat"=> -15.128874061084,
            "lng"=> 26.870820364808,
            "status"=> "active",
            "address"=> "Plot 2572, Senanga, Zambia",
            "description"=> "This is health center number 810",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3811,
            "user_id"=> 1,
            "name"=> "Health Center 811",
            "email"=> "center811@example.com",
            "phone"=> "0979833651",
            "type"=> "center",
            "lat"=> -15.209398683444,
            "lng"=> 29.476189381525,
            "status"=> "active",
            "address"=> "Plot 4414, Monze, Zambia",
            "description"=> "This is health center number 811",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3812,
            "user_id"=> 1,
            "name"=> "Health Center 812",
            "email"=> "center812@example.com",
            "phone"=> "0972654864",
            "type"=> "center",
            "lat"=> -11.364447036788,
            "lng"=> 30.898275196738,
            "status"=> "active",
            "address"=> "Plot 2466, Ndola, Zambia",
            "description"=> "This is health center number 812",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3816,
            "user_id"=> 1,
            "name"=> "Health Center 816",
            "email"=> "center816@example.com",
            "phone"=> "0973045329",
            "type"=> "center",
            "lat"=> -11.410146065992,
            "lng"=> 24.420905896705,
            "status"=> "active",
            "address"=> "Plot 4062, Mansa, Zambia",
            "description"=> "This is health center number 816",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3819,
            "user_id"=> 1,
            "name"=> "Health Center 819",
            "email"=> "center819@example.com",
            "phone"=> "0974107625",
            "type"=> "center",
            "lat"=> -10.45146917014,
            "lng"=> 31.825891728013,
            "status"=> "active",
            "address"=> "Plot 506, Mkushi, Zambia",
            "description"=> "This is health center number 819",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3820,
            "user_id"=> 1,
            "name"=> "Health Center 820",
            "email"=> "center820@example.com",
            "phone"=> "0974242520",
            "type"=> "center",
            "lat"=> -16.717556916511,
            "lng"=> 24.313210287231,
            "status"=> "active",
            "address"=> "Plot 3473, Nakonde, Zambia",
            "description"=> "This is health center number 820",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3822,
            "user_id"=> 1,
            "name"=> "Health Center 822",
            "email"=> "center822@example.com",
            "phone"=> "0973101455",
            "type"=> "center",
            "lat"=> -13.396985617092,
            "lng"=> 23.825875071122,
            "status"=> "active",
            "address"=> "Plot 3143, Mansa, Zambia",
            "description"=> "This is health center number 822",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3823,
            "user_id"=> 1,
            "name"=> "Health Center 823",
            "email"=> "center823@example.com",
            "phone"=> "0974715388",
            "type"=> "center",
            "lat"=> -13.436239561223,
            "lng"=> 30.526856161853,
            "status"=> "active",
            "address"=> "Plot 693, Petauke, Zambia",
            "description"=> "This is health center number 823",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3824,
            "user_id"=> 1,
            "name"=> "Health Center 824",
            "email"=> "center824@example.com",
            "phone"=> "0973746187",
            "type"=> "center",
            "lat"=> -13.717701654936,
            "lng"=> 28.58733002147,
            "status"=> "active",
            "address"=> "Plot 990, Mkushi, Zambia",
            "description"=> "This is health center number 824",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3825,
            "user_id"=> 1,
            "name"=> "Health Center 825",
            "email"=> "center825@example.com",
            "phone"=> "0974698703",
            "type"=> "center",
            "lat"=> -9.536715227662,
            "lng"=> 30.976832084673,
            "status"=> "active",
            "address"=> "Plot 257, Chingola, Zambia",
            "description"=> "This is health center number 825",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3829,
            "user_id"=> 1,
            "name"=> "Health Center 829",
            "email"=> "center829@example.com",
            "phone"=> "0971176234",
            "type"=> "center",
            "lat"=> -12.795975002924,
            "lng"=> 28.449182701739,
            "status"=> "active",
            "address"=> "Plot 3056, Lusaka, Zambia",
            "description"=> "This is health center number 829",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3830,
            "user_id"=> 1,
            "name"=> "Health Center 830",
            "email"=> "center830@example.com",
            "phone"=> "0978878678",
            "type"=> "center",
            "lat"=> -13.049737769063,
            "lng"=> 24.019342278093,
            "status"=> "active",
            "address"=> "Plot 1039, Kapiri Mposhi, Zambia",
            "description"=> "This is health center number 830",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3834,
            "user_id"=> 1,
            "name"=> "Health Center 834",
            "email"=> "center834@example.com",
            "phone"=> "0974501641",
            "type"=> "center",
            "lat"=> -14.822164631459,
            "lng"=> 22.149174411897,
            "status"=> "active",
            "address"=> "Plot 2017, Ndola, Zambia",
            "description"=> "This is health center number 834",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3835,
            "user_id"=> 1,
            "name"=> "Health Center 835",
            "email"=> "center835@example.com",
            "phone"=> "0973313593",
            "type"=> "center",
            "lat"=> -11.836351295671,
            "lng"=> 25.919825891135,
            "status"=> "active",
            "address"=> "Plot 3621, Kalulushi, Zambia",
            "description"=> "This is health center number 835",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3836,
            "user_id"=> 1,
            "name"=> "Health Center 836",
            "email"=> "center836@example.com",
            "phone"=> "0971628746",
            "type"=> "center",
            "lat"=> -11.126509039815,
            "lng"=> 29.177244163247,
            "status"=> "active",
            "address"=> "Plot 24, Choma, Zambia",
            "description"=> "This is health center number 836",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3840,
            "user_id"=> 1,
            "name"=> "Health Center 840",
            "email"=> "center840@example.com",
            "phone"=> "0972830209",
            "type"=> "center",
            "lat"=> -12.61481529135,
            "lng"=> 26.973842666146,
            "status"=> "active",
            "address"=> "Plot 1901, Chipata, Zambia",
            "description"=> "This is health center number 840",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3842,
            "user_id"=> 1,
            "name"=> "Health Center 842",
            "email"=> "center842@example.com",
            "phone"=> "0976428092",
            "type"=> "center",
            "lat"=> -17.744342394054,
            "lng"=> 24.057514412123,
            "status"=> "active",
            "address"=> "Plot 3645, Ndola, Zambia",
            "description"=> "This is health center number 842",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3843,
            "user_id"=> 1,
            "name"=> "Health Center 843",
            "email"=> "center843@example.com",
            "phone"=> "0977958193",
            "type"=> "center",
            "lat"=> -14.7388896506,
            "lng"=> 22.131536939848,
            "status"=> "active",
            "address"=> "Plot 2641, Chililabombwe, Zambia",
            "description"=> "This is health center number 843",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3844,
            "user_id"=> 1,
            "name"=> "Health Center 844",
            "email"=> "center844@example.com",
            "phone"=> "0971500387",
            "type"=> "center",
            "lat"=> -11.844662937962,
            "lng"=> 29.426853516291,
            "status"=> "active",
            "address"=> "Plot 4188, Solwezi, Zambia",
            "description"=> "This is health center number 844",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3845,
            "user_id"=> 1,
            "name"=> "Health Center 845",
            "email"=> "center845@example.com",
            "phone"=> "0973152103",
            "type"=> "center",
            "lat"=> -9.787263090111,
            "lng"=> 32.712648051052,
            "status"=> "active",
            "address"=> "Plot 428, Monze, Zambia",
            "description"=> "This is health center number 845",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3846,
            "user_id"=> 1,
            "name"=> "Health Center 846",
            "email"=> "center846@example.com",
            "phone"=> "0971157705",
            "type"=> "center",
            "lat"=> -13.68915802035,
            "lng"=> 26.777302677313,
            "status"=> "active",
            "address"=> "Plot 936, Nakonde, Zambia",
            "description"=> "This is health center number 846",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3848,
            "user_id"=> 1,
            "name"=> "Health Center 848",
            "email"=> "center848@example.com",
            "phone"=> "0972130624",
            "type"=> "center",
            "lat"=> -12.041646966916,
            "lng"=> 24.328311698804,
            "status"=> "active",
            "address"=> "Plot 2754, Nakonde, Zambia",
            "description"=> "This is health center number 848",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 27.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 27.000000Z"
        ],
        [
            "id"=> 3851,
            "user_id"=> 1,
            "name"=> "Health Center 851",
            "email"=> "center851@example.com",
            "phone"=> "0979022368",
            "type"=> "center",
            "lat"=> -13.477081085591,
            "lng"=> 33.411481049895,
            "status"=> "active",
            "address"=> "Plot 2684, Mazabuka, Zambia",
            "description"=> "This is health center number 851",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3852,
            "user_id"=> 1,
            "name"=> "Health Center 852",
            "email"=> "center852@example.com",
            "phone"=> "0976134383",
            "type"=> "center",
            "lat"=> -10.057098447698,
            "lng"=> 31.873400917357,
            "status"=> "active",
            "address"=> "Plot 3583, Kasama, Zambia",
            "description"=> "This is health center number 852",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3853,
            "user_id"=> 1,
            "name"=> "Health Center 853",
            "email"=> "center853@example.com",
            "phone"=> "0972739697",
            "type"=> "center",
            "lat"=> -12.048647046298,
            "lng"=> 30.794932608816,
            "status"=> "active",
            "address"=> "Plot 4495, Nakonde, Zambia",
            "description"=> "This is health center number 853",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3854,
            "user_id"=> 1,
            "name"=> "Health Center 854",
            "email"=> "center854@example.com",
            "phone"=> "0978403273",
            "type"=> "center",
            "lat"=> -14.202668181622,
            "lng"=> 23.645686143984,
            "status"=> "active",
            "address"=> "Plot 4388, Mkushi, Zambia",
            "description"=> "This is health center number 854",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3858,
            "user_id"=> 1,
            "name"=> "Health Center 858",
            "email"=> "center858@example.com",
            "phone"=> "0979413745",
            "type"=> "center",
            "lat"=> -11.908641125219,
            "lng"=> 28.581793100611,
            "status"=> "active",
            "address"=> "Plot 4567, Livingstone, Zambia",
            "description"=> "This is health center number 858",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3859,
            "user_id"=> 1,
            "name"=> "Health Center 859",
            "email"=> "center859@example.com",
            "phone"=> "0976008616",
            "type"=> "center",
            "lat"=> -15.543611407626,
            "lng"=> 22.90887702173,
            "status"=> "active",
            "address"=> "Plot 3787, Samfya, Zambia",
            "description"=> "This is health center number 859",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3860,
            "user_id"=> 1,
            "name"=> "Health Center 860",
            "email"=> "center860@example.com",
            "phone"=> "0974003247",
            "type"=> "center",
            "lat"=> -12.969966515931,
            "lng"=> 31.629548057508,
            "status"=> "active",
            "address"=> "Plot 4711, Chipata, Zambia",
            "description"=> "This is health center number 860",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3862,
            "user_id"=> 1,
            "name"=> "Health Center 862",
            "email"=> "center862@example.com",
            "phone"=> "0978082699",
            "type"=> "center",
            "lat"=> -11.26007485467,
            "lng"=> 28.230781490044,
            "status"=> "active",
            "address"=> "Plot 99, Chipata, Zambia",
            "description"=> "This is health center number 862",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3864,
            "user_id"=> 1,
            "name"=> "Health Center 864",
            "email"=> "center864@example.com",
            "phone"=> "0979413994",
            "type"=> "center",
            "lat"=> -11.050649724831,
            "lng"=> 30.920927737197,
            "status"=> "active",
            "address"=> "Plot 4354, Senanga, Zambia",
            "description"=> "This is health center number 864",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3865,
            "user_id"=> 1,
            "name"=> "Health Center 865",
            "email"=> "center865@example.com",
            "phone"=> "0973330526",
            "type"=> "center",
            "lat"=> -10.870127578112,
            "lng"=> 33.643054876357,
            "status"=> "active",
            "address"=> "Plot 1020, Petauke, Zambia",
            "description"=> "This is health center number 865",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3866,
            "user_id"=> 1,
            "name"=> "Health Center 866",
            "email"=> "center866@example.com",
            "phone"=> "0974998581",
            "type"=> "center",
            "lat"=> -14.735434535814,
            "lng"=> 22.567298537617,
            "status"=> "active",
            "address"=> "Plot 4275, Mufulira, Zambia",
            "description"=> "This is health center number 866",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3867,
            "user_id"=> 1,
            "name"=> "Health Center 867",
            "email"=> "center867@example.com",
            "phone"=> "0972327978",
            "type"=> "center",
            "lat"=> -13.084661869698,
            "lng"=> 25.12152697594,
            "status"=> "active",
            "address"=> "Plot 3262, Kabwe, Zambia",
            "description"=> "This is health center number 867",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3868,
            "user_id"=> 1,
            "name"=> "Health Center 868",
            "email"=> "center868@example.com",
            "phone"=> "0979392548",
            "type"=> "center",
            "lat"=> -10.234818787796,
            "lng"=> 28.783517002167,
            "status"=> "active",
            "address"=> "Plot 1347, Kabwe, Zambia",
            "description"=> "This is health center number 868",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3869,
            "user_id"=> 1,
            "name"=> "Health Center 869",
            "email"=> "center869@example.com",
            "phone"=> "0977069383",
            "type"=> "center",
            "lat"=> -16.903948122963,
            "lng"=> 26.205347156853,
            "status"=> "active",
            "address"=> "Plot 1742, Mufulira, Zambia",
            "description"=> "This is health center number 869",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3872,
            "user_id"=> 1,
            "name"=> "Health Center 872",
            "email"=> "center872@example.com",
            "phone"=> "0979344450",
            "type"=> "center",
            "lat"=> -13.103717198271,
            "lng"=> 23.985073426964,
            "status"=> "active",
            "address"=> "Plot 4547, Senanga, Zambia",
            "description"=> "This is health center number 872",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3875,
            "user_id"=> 1,
            "name"=> "Health Center 875",
            "email"=> "center875@example.com",
            "phone"=> "0978637011",
            "type"=> "center",
            "lat"=> -14.665604239966,
            "lng"=> 25.394688797321,
            "status"=> "active",
            "address"=> "Plot 2353, Kafue, Zambia",
            "description"=> "This is health center number 875",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3877,
            "user_id"=> 1,
            "name"=> "Health Center 877",
            "email"=> "center877@example.com",
            "phone"=> "0975969801",
            "type"=> "center",
            "lat"=> -9.8338379682665,
            "lng"=> 28.985568377509,
            "status"=> "active",
            "address"=> "Plot 383, Nakonde, Zambia",
            "description"=> "This is health center number 877",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3880,
            "user_id"=> 1,
            "name"=> "Health Center 880",
            "email"=> "center880@example.com",
            "phone"=> "0977271663",
            "type"=> "center",
            "lat"=> -13.735300674352,
            "lng"=> 25.926176454977,
            "status"=> "active",
            "address"=> "Plot 1884, Kapiri Mposhi, Zambia",
            "description"=> "This is health center number 880",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3881,
            "user_id"=> 1,
            "name"=> "Health Center 881",
            "email"=> "center881@example.com",
            "phone"=> "0973112948",
            "type"=> "center",
            "lat"=> -13.569532055626,
            "lng"=> 24.278283345503,
            "status"=> "active",
            "address"=> "Plot 528, Chingola, Zambia",
            "description"=> "This is health center number 881",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3882,
            "user_id"=> 1,
            "name"=> "Health Center 882",
            "email"=> "center882@example.com",
            "phone"=> "0973174799",
            "type"=> "center",
            "lat"=> -12.320183787737,
            "lng"=> 32.795104897532,
            "status"=> "active",
            "address"=> "Plot 1421, Ndola, Zambia",
            "description"=> "This is health center number 882",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3883,
            "user_id"=> 1,
            "name"=> "Health Center 883",
            "email"=> "center883@example.com",
            "phone"=> "0975635503",
            "type"=> "center",
            "lat"=> -16.754239998923,
            "lng"=> 27.198873912868,
            "status"=> "active",
            "address"=> "Plot 3663, Chingola, Zambia",
            "description"=> "This is health center number 883",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3887,
            "user_id"=> 1,
            "name"=> "Health Center 887",
            "email"=> "center887@example.com",
            "phone"=> "0974981777",
            "type"=> "center",
            "lat"=> -14.991724709371,
            "lng"=> 23.502172626929,
            "status"=> "active",
            "address"=> "Plot 1411, Sesheke, Zambia",
            "description"=> "This is health center number 887",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3888,
            "user_id"=> 1,
            "name"=> "Health Center 888",
            "email"=> "center888@example.com",
            "phone"=> "0979963480",
            "type"=> "center",
            "lat"=> -10.918058824222,
            "lng"=> 30.474509073037,
            "status"=> "active",
            "address"=> "Plot 3188, Monze, Zambia",
            "description"=> "This is health center number 888",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3889,
            "user_id"=> 1,
            "name"=> "Health Center 889",
            "email"=> "center889@example.com",
            "phone"=> "0974603485",
            "type"=> "center",
            "lat"=> -12.390062512127,
            "lng"=> 29.865348839092,
            "status"=> "active",
            "address"=> "Plot 1604, Mkushi, Zambia",
            "description"=> "This is health center number 889",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3890,
            "user_id"=> 1,
            "name"=> "Health Center 890",
            "email"=> "center890@example.com",
            "phone"=> "0976921121",
            "type"=> "center",
            "lat"=> -8.6388062254241,
            "lng"=> 30.114509722783,
            "status"=> "active",
            "address"=> "Plot 1632, Nakonde, Zambia",
            "description"=> "This is health center number 890",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3891,
            "user_id"=> 1,
            "name"=> "Health Center 891",
            "email"=> "center891@example.com",
            "phone"=> "0978889948",
            "type"=> "center",
            "lat"=> -16.544944807861,
            "lng"=> 28.325415584177,
            "status"=> "active",
            "address"=> "Plot 2283, Luanshya, Zambia",
            "description"=> "This is health center number 891",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3892,
            "user_id"=> 1,
            "name"=> "Health Center 892",
            "email"=> "center892@example.com",
            "phone"=> "0972686202",
            "type"=> "center",
            "lat"=> -13.269733427218,
            "lng"=> 32.50056301672,
            "status"=> "active",
            "address"=> "Plot 1573, Luanshya, Zambia",
            "description"=> "This is health center number 892",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3893,
            "user_id"=> 1,
            "name"=> "Health Center 893",
            "email"=> "center893@example.com",
            "phone"=> "0978867941",
            "type"=> "center",
            "lat"=> -9.4158285580649,
            "lng"=> 31.725745406666,
            "status"=> "active",
            "address"=> "Plot 3803, Kabwe, Zambia",
            "description"=> "This is health center number 893",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:28.000000Z",
            "updated_at"=> "2025-06-01T15:33:28.000000Z"
        ],
        [
            "id"=> 3894,
            "user_id"=> 1,
            "name"=> "Health Center 894",
            "email"=> "center894@example.com",
            "phone"=> "0974203051",
            "type"=> "center",
            "lat"=> -12.315876312189,
            "lng"=> 31.155053157944,
            "status"=> "active",
            "address"=> "Plot 3349, Senanga, Zambia",
            "description"=> "This is health center number 894",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:28.000000Z",
            "updated_at"=> "2025-06-01T15:33:28.000000Z"
        ],
        [
            "id"=> 3895,
            "user_id"=> 1,
            "name"=> "Health Center 895",
            "email"=> "center895@example.com",
            "phone"=> "0972937450",
            "type"=> "center",
            "lat"=> -15.402522969201,
            "lng"=> 28.604020310335,
            "status"=> "active",
            "address"=> "Plot 573, Mkushi, Zambia",
            "description"=> "This is health center number 895",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:28.000000Z",
            "updated_at"=> "2025-06-01T15:33:28.000000Z"
        ],
        [
            "id"=> 3899,
            "user_id"=> 1,
            "name"=> "Health Center 899",
            "email"=> "center899@example.com",
            "phone"=> "0975108605",
            "type"=> "center",
            "lat"=> -13.369772561858,
            "lng"=> 30.045752200925,
            "status"=> "active",
            "address"=> "Plot 4530, Monze, Zambia",
            "description"=> "This is health center number 899",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:28.000000Z",
            "updated_at"=> "2025-06-01T15:33:28.000000Z"
        ],
        [
            "id"=> 3900,
            "user_id"=> 1,
            "name"=> "Health Center 900",
            "email"=> "center900@example.com",
            "phone"=> "0978229519",
            "type"=> "center",
            "lat"=> -14.81849022136,
            "lng"=> 27.266334067735,
            "status"=> "active",
            "address"=> "Plot 450, Mansa, Zambia",
            "description"=> "This is health center number 900",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:28.000000Z",
            "updated_at"=> "2025-06-01T15:33:28.000000Z"
        ],
        [
            "id"=> 3901,
            "user_id"=> 1,
            "name"=> "Health Center 901",
            "email"=> "center901@example.com",
            "phone"=> "0979770618",
            "type"=> "center",
            "lat"=> -13.94275233999,
            "lng"=> 33.322807674307,
            "status"=> "active",
            "address"=> "Plot 4398, Ndola, Zambia",
            "description"=> "This is health center number 901",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:28.000000Z",
            "updated_at"=> "2025-06-01T15:33:28.000000Z"
        ],
        [
            "id"=> 3903,
            "user_id"=> 1,
            "name"=> "Health Center 903",
            "email"=> "center903@example.com",
            "phone"=> "0971979309",
            "type"=> "center",
            "lat"=> -15.606125112114,
            "lng"=> 26.533857867044,
            "status"=> "active",
            "address"=> "Plot 4897, Ndola, Zambia",
            "description"=> "This is health center number 903",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:28.000000Z",
            "updated_at"=> "2025-06-01T15:33:28.000000Z"
        ],
        [
            "id"=> 3904,
            "user_id"=> 1,
            "name"=> "Health Center 904",
            "email"=> "center904@example.com",
            "phone"=> "0975896039",
            "type"=> "center",
            "lat"=> -11.822120851242,
            "lng"=> 27.235738805745,
            "status"=> "active",
            "address"=> "Plot 4960, Nchelenge, Zambia",
            "description"=> "This is health center number 904",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15:33:28.000000Z",
            "updated_at"=> "2025-06-01T15:33:28.000000Z"
        ],
        [
            "id"=> 3906,
            "user_id"=> 1,
            "name"=> "Health Center 906",
            "email"=> "center906@example.com",
            "phone"=> "0975894333",
            "type"=> "center",
            "lat"=> -11.359534358913,
            "lng"=> 32.874366422544,
            "status"=> "active",
            "address"=> "Plot 2393, Kasama, Zambia",
            "description"=> "This is health center number 906",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:28.000000Z",
            "updated_at"=> "2025-06-01T15:33:28.000000Z"
        ],
        [
            "id"=> 3907,
            "user_id"=> 1,
            "name"=> "Health Center 907",
            "email"=> "center907@example.com",
            "phone"=> "0973121093",
            "type"=> "center",
            "lat"=> -17.134869956102,
            "lng"=> 28.059479714725,
            "status"=> "active",
            "address"=> "Plot 3664, Kalulushi, Zambia",
            "description"=> "This is health center number 907",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15:33:28.000000Z",
            "updated_at"=> "2025-06-01T15:33:28.000000Z"
        ],
        [
            "id"=> 3909,
            "user_id"=> 1,
            "name"=> "Health Center 909",
            "email"=> "center909@example.com",
            "phone"=> "0978103983",
            "type"=> "center",
            "lat"=> -14.249272416555,
            "lng"=> 26.922008500724,
            "status"=> "active",
            "address"=> "Plot 3852, Choma, Zambia",
            "description"=> "This is health center number 909",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3911,
            "user_id"=> 1,
            "name"=> "Health Center 911",
            "email"=> "center911@example.com",
            "phone"=> "0976699646",
            "type"=> "center",
            "lat"=> -14.972017146774,
            "lng"=> 24.498926316015,
            "status"=> "active",
            "address"=> "Plot 2460, Sesheke, Zambia",
            "description"=> "This is health center number 911",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3912,
            "user_id"=> 1,
            "name"=> "Health Center 912",
            "email"=> "center912@example.com",
            "phone"=> "0977737421",
            "type"=> "center",
            "lat"=> -10.07790113612,
            "lng"=> 32.056114673594,
            "status"=> "active",
            "address"=> "Plot 4135, Chingola, Zambia",
            "description"=> "This is health center number 912",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3917,
            "user_id"=> 1,
            "name"=> "Health Center 917",
            "email"=> "center917@example.com",
            "phone"=> "0979184872",
            "type"=> "center",
            "lat"=> -16.556673462902,
            "lng"=> 25.257825838382,
            "status"=> "active",
            "address"=> "Plot 2412, Samfya, Zambia",
            "description"=> "This is health center number 917",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3918,
            "user_id"=> 1,
            "name"=> "Health Center 918",
            "email"=> "center918@example.com",
            "phone"=> "0972967466",
            "type"=> "center",
            "lat"=> -14.548109716525,
            "lng"=> 29.23908907433,
            "status"=> "active",
            "address"=> "Plot 1329, Sesheke, Zambia",
            "description"=> "This is health center number 918",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3919,
            "user_id"=> 1,
            "name"=> "Health Center 919",
            "email"=> "center919@example.com",
            "phone"=> "0979063876",
            "type"=> "center",
            "lat"=> -13.025714792416,
            "lng"=> 23.679709459832,
            "status"=> "active",
            "address"=> "Plot 3663, Mkushi, Zambia",
            "description"=> "This is health center number 919",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3921,
            "user_id"=> 1,
            "name"=> "Health Center 921",
            "email"=> "center921@example.com",
            "phone"=> "0972758438",
            "type"=> "center",
            "lat"=> -11.584606655866,
            "lng"=> 28.597282807947,
            "status"=> "active",
            "address"=> "Plot 1981, Monze, Zambia",
            "description"=> "This is health center number 921",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3922,
            "user_id"=> 1,
            "name"=> "Health Center 922",
            "email"=> "center922@example.com",
            "phone"=> "0973972390",
            "type"=> "center",
            "lat"=> -12.860377443563,
            "lng"=> 26.036359226674,
            "status"=> "active",
            "address"=> "Plot 1418, Chingola, Zambia",
            "description"=> "This is health center number 922",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3923,
            "user_id"=> 1,
            "name"=> "Health Center 923",
            "email"=> "center923@example.com",
            "phone"=> "0978578479",
            "type"=> "center",
            "lat"=> -11.849718979071,
            "lng"=> 32.561179641476,
            "status"=> "active",
            "address"=> "Plot 4433, Chingola, Zambia",
            "description"=> "This is health center number 923",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3924,
            "user_id"=> 1,
            "name"=> "Health Center 924",
            "email"=> "center924@example.com",
            "phone"=> "0978884095",
            "type"=> "center",
            "lat"=> -13.853341589753,
            "lng"=> 31.566163030018,
            "status"=> "active",
            "address"=> "Plot 726, Choma, Zambia",
            "description"=> "This is health center number 924",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3926,
            "user_id"=> 1,
            "name"=> "Health Center 926",
            "email"=> "center926@example.com",
            "phone"=> "0973594593",
            "type"=> "center",
            "lat"=> -11.89400921026,
            "lng"=> 31.715908945173,
            "status"=> "active",
            "address"=> "Plot 1438, Mkushi, Zambia",
            "description"=> "This is health center number 926",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3928,
            "user_id"=> 1,
            "name"=> "Health Center 928",
            "email"=> "center928@example.com",
            "phone"=> "0978458454",
            "type"=> "center",
            "lat"=> -14.849420159054,
            "lng"=> 31.008101108441,
            "status"=> "active",
            "address"=> "Plot 3119, Kalulushi, Zambia",
            "description"=> "This is health center number 928",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3929,
            "user_id"=> 1,
            "name"=> "Health Center 929",
            "email"=> "center929@example.com",
            "phone"=> "0971493348",
            "type"=> "center",
            "lat"=> -12.321724677422,
            "lng"=> 30.649307647696,
            "status"=> "active",
            "address"=> "Plot 4750, Sesheke, Zambia",
            "description"=> "This is health center number 929",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3930,
            "user_id"=> 1,
            "name"=> "Health Center 930",
            "email"=> "center930@example.com",
            "phone"=> "0978623577",
            "type"=> "center",
            "lat"=> -12.31698389436,
            "lng"=> 26.285365519573,
            "status"=> "active",
            "address"=> "Plot 4114, Petauke, Zambia",
            "description"=> "This is health center number 930",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3933,
            "user_id"=> 1,
            "name"=> "Health Center 933",
            "email"=> "center933@example.com",
            "phone"=> "0973264660",
            "type"=> "center",
            "lat"=> -11.663289352489,
            "lng"=> 33.662591555697,
            "status"=> "active",
            "address"=> "Plot 1073, Mansa, Zambia",
            "description"=> "This is health center number 933",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3934,
            "user_id"=> 1,
            "name"=> "Health Center 934",
            "email"=> "center934@example.com",
            "phone"=> "0977354255",
            "type"=> "center",
            "lat"=> -13.765815080873,
            "lng"=> 22.042704467728,
            "status"=> "active",
            "address"=> "Plot 3699, Choma, Zambia",
            "description"=> "This is health center number 934",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3935,
            "user_id"=> 1,
            "name"=> "Health Center 935",
            "email"=> "center935@example.com",
            "phone"=> "0974016314",
            "type"=> "center",
            "lat"=> -14.452040348459,
            "lng"=> 22.770944669689,
            "status"=> "active",
            "address"=> "Plot 2131, Kapiri Mposhi, Zambia",
            "description"=> "This is health center number 935",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3937,
            "user_id"=> 1,
            "name"=> "Health Center 937",
            "email"=> "center937@example.com",
            "phone"=> "0975112307",
            "type"=> "center",
            "lat"=> -10.800807065843,
            "lng"=> 32.782671976687,
            "status"=> "active",
            "address"=> "Plot 3440, Petauke, Zambia",
            "description"=> "This is health center number 937",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3938,
            "user_id"=> 1,
            "name"=> "Health Center 938",
            "email"=> "center938@example.com",
            "phone"=> "0976541121",
            "type"=> "center",
            "lat"=> -16.578081851536,
            "lng"=> 26.500068794657,
            "status"=> "active",
            "address"=> "Plot 4448, Senanga, Zambia",
            "description"=> "This is health center number 938",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3939,
            "user_id"=> 1,
            "name"=> "Health Center 939",
            "email"=> "center939@example.com",
            "phone"=> "0977046650",
            "type"=> "center",
            "lat"=> -9.6495232421204,
            "lng"=> 32.817141701336,
            "status"=> "active",
            "address"=> "Plot 1043, Mufulira, Zambia",
            "description"=> "This is health center number 939",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3940,
            "user_id"=> 1,
            "name"=> "Health Center 940",
            "email"=> "center940@example.com",
            "phone"=> "0979543078",
            "type"=> "center",
            "lat"=> -10.438030768623,
            "lng"=> 33.273424735094,
            "status"=> "active",
            "address"=> "Plot 1648, Livingstone, Zambia",
            "description"=> "This is health center number 940",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3943,
            "user_id"=> 1,
            "name"=> "Health Center 943",
            "email"=> "center943@example.com",
            "phone"=> "0976155429",
            "type"=> "center",
            "lat"=> -15.763932622673,
            "lng"=> 23.871726593735,
            "status"=> "active",
            "address"=> "Plot 1920, Mufulira, Zambia",
            "description"=> "This is health center number 943",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3944,
            "user_id"=> 1,
            "name"=> "Health Center 944",
            "email"=> "center944@example.com",
            "phone"=> "0971553367",
            "type"=> "center",
            "lat"=> -12.574857655854,
            "lng"=> 24.151315653441,
            "status"=> "active",
            "address"=> "Plot 4859, Kafue, Zambia",
            "description"=> "This is health center number 944",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3945,
            "user_id"=> 1,
            "name"=> "Health Center 945",
            "email"=> "center945@example.com",
            "phone"=> "0978235983",
            "type"=> "center",
            "lat"=> -8.9523602606041,
            "lng"=> 31.017349411695,
            "status"=> "active",
            "address"=> "Plot 3874, Monze, Zambia",
            "description"=> "This is health center number 945",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3946,
            "user_id"=> 1,
            "name"=> "Health Center 946",
            "email"=> "center946@example.com",
            "phone"=> "0973327326",
            "type"=> "center",
            "lat"=> -14.568353215497,
            "lng"=> 29.533216674455,
            "status"=> "active",
            "address"=> "Plot 2261, Nakonde, Zambia",
            "description"=> "This is health center number 946",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3947,
            "user_id"=> 1,
            "name"=> "Health Center 947",
            "email"=> "center947@example.com",
            "phone"=> "0975801775",
            "type"=> "center",
            "lat"=> -13.267696372032,
            "lng"=> 26.538325768448,
            "status"=> "active",
            "address"=> "Plot 2337, Chingola, Zambia",
            "description"=> "This is health center number 947",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3948,
            "user_id"=> 1,
            "name"=> "Health Center 948",
            "email"=> "center948@example.com",
            "phone"=> "0973568173",
            "type"=> "center",
            "lat"=> -15.609859563461,
            "lng"=> 28.946207058172,
            "status"=> "active",
            "address"=> "Plot 3969, Nakonde, Zambia",
            "description"=> "This is health center number 948",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3949,
            "user_id"=> 1,
            "name"=> "Health Center 949",
            "email"=> "center949@example.com",
            "phone"=> "0976615448",
            "type"=> "center",
            "lat"=> -16.626643125073,
            "lng"=> 25.648255897755,
            "status"=> "active",
            "address"=> "Plot 4506, Kapiri Mposhi, Zambia",
            "description"=> "This is health center number 949",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3950,
            "user_id"=> 1,
            "name"=> "Health Center 950",
            "email"=> "center950@example.com",
            "phone"=> "0977892754",
            "type"=> "center",
            "lat"=> -9.944699565668,
            "lng"=> 32.401051801211,
            "status"=> "active",
            "address"=> "Plot 2477, Livingstone, Zambia",
            "description"=> "This is health center number 950",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3951,
            "user_id"=> 1,
            "name"=> "Health Center 951",
            "email"=> "center951@example.com",
            "phone"=> "0972711732",
            "type"=> "center",
            "lat"=> -15.877167347668,
            "lng"=> 26.529438929832,
            "status"=> "active",
            "address"=> "Plot 464, Mansa, Zambia",
            "description"=> "This is health center number 951",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3952,
            "user_id"=> 1,
            "name"=> "Health Center 952",
            "email"=> "center952@example.com",
            "phone"=> "0972131644",
            "type"=> "center",
            "lat"=> -17.187852195319,
            "lng"=> 25.180951931738,
            "status"=> "active",
            "address"=> "Plot 1980, Sesheke, Zambia",
            "description"=> "This is health center number 952",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3953,
            "user_id"=> 1,
            "name"=> "Health Center 953",
            "email"=> "center953@example.com",
            "phone"=> "0976831793",
            "type"=> "center",
            "lat"=> -14.264064055804,
            "lng"=> 26.876367418084,
            "status"=> "active",
            "address"=> "Plot 1335, Mansa, Zambia",
            "description"=> "This is health center number 953",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3955,
            "user_id"=> 1,
            "name"=> "Health Center 955",
            "email"=> "center955@example.com",
            "phone"=> "0975875735",
            "type"=> "center",
            "lat"=> -15.444303292709,
            "lng"=> 22.9709963268,
            "status"=> "active",
            "address"=> "Plot 874, Kabwe, Zambia",
            "description"=> "This is health center number 955",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3956,
            "user_id"=> 1,
            "name"=> "Health Center 956",
            "email"=> "center956@example.com",
            "phone"=> "0977580116",
            "type"=> "center",
            "lat"=> -14.597413438092,
            "lng"=> 24.572013506513,
            "status"=> "active",
            "address"=> "Plot 4199, Monze, Zambia",
            "description"=> "This is health center number 956",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3958,
            "user_id"=> 1,
            "name"=> "Health Center 958",
            "email"=> "center958@example.com",
            "phone"=> "0972477357",
            "type"=> "center",
            "lat"=> -12.892124871627,
            "lng"=> 30.469143188917,
            "status"=> "active",
            "address"=> "Plot 2882, Nakonde, Zambia",
            "description"=> "This is health center number 958",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3960,
            "user_id"=> 1,
            "name"=> "Health Center 960",
            "email"=> "center960@example.com",
            "phone"=> "0978316002",
            "type"=> "center",
            "lat"=> -17.276812189015,
            "lng"=> 24.577392743005,
            "status"=> "active",
            "address"=> "Plot 2824, Mongu, Zambia",
            "description"=> "This is health center number 960",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3962,
            "user_id"=> 1,
            "name"=> "Health Center 962",
            "email"=> "center962@example.com",
            "phone"=> "0977572170",
            "type"=> "center",
            "lat"=> -14.4618973153,
            "lng"=> 31.916901895039,
            "status"=> "active",
            "address"=> "Plot 215, Nakonde, Zambia",
            "description"=> "This is health center number 962",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3963,
            "user_id"=> 1,
            "name"=> "Health Center 963",
            "email"=> "center963@example.com",
            "phone"=> "0979159286",
            "type"=> "center",
            "lat"=> -17.479189253496,
            "lng"=> 26.00144167007,
            "status"=> "active",
            "address"=> "Plot 941, Kabwe, Zambia",
            "description"=> "This is health center number 963",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3967,
            "user_id"=> 1,
            "name"=> "Health Center 967",
            "email"=> "center967@example.com",
            "phone"=> "0979341169",
            "type"=> "center",
            "lat"=> -10.092167870836,
            "lng"=> 30.91870999067,
            "status"=> "active",
            "address"=> "Plot 1683, Nchelenge, Zambia",
            "description"=> "This is health center number 967",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3970,
            "user_id"=> 1,
            "name"=> "Health Center 970",
            "email"=> "center970@example.com",
            "phone"=> "0979456892",
            "type"=> "center",
            "lat"=> -12.667596467988,
            "lng"=> 26.319291505972,
            "status"=> "active",
            "address"=> "Plot 2533, Samfya, Zambia",
            "description"=> "This is health center number 970",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3971,
            "user_id"=> 1,
            "name"=> "Health Center 971",
            "email"=> "center971@example.com",
            "phone"=> "0976772619",
            "type"=> "center",
            "lat"=> -11.181888895194,
            "lng"=> 32.981987495014,
            "status"=> "active",
            "address"=> "Plot 4976, Kabwe, Zambia",
            "description"=> "This is health center number 971",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3973,
            "user_id"=> 1,
            "name"=> "Health Center 973",
            "email"=> "center973@example.com",
            "phone"=> "0972847274",
            "type"=> "center",
            "lat"=> -11.972865078679,
            "lng"=> 28.22328320468,
            "status"=> "active",
            "address"=> "Plot 2052, Senanga, Zambia",
            "description"=> "This is health center number 973",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3976,
            "user_id"=> 1,
            "name"=> "Health Center 976",
            "email"=> "center976@example.com",
            "phone"=> "0974969114",
            "type"=> "center",
            "lat"=> -11.60261858334,
            "lng"=> 30.992429336297,
            "status"=> "active",
            "address"=> "Plot 3638, Kitwe, Zambia",
            "description"=> "This is health center number 976",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3980,
            "user_id"=> 1,
            "name"=> "Health Center 980",
            "email"=> "center980@example.com",
            "phone"=> "0974462864",
            "type"=> "center",
            "lat"=> -8.9512387284316,
            "lng"=> 29.543485730814,
            "status"=> "active",
            "address"=> "Plot 4754, Choma, Zambia",
            "description"=> "This is health center number 980",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3981,
            "user_id"=> 1,
            "name"=> "Health Center 981",
            "email"=> "center981@example.com",
            "phone"=> "0972589359",
            "type"=> "center",
            "lat"=> -10.295335290951,
            "lng"=> 29.25478273777,
            "status"=> "active",
            "address"=> "Plot 2398, Mufulira, Zambia",
            "description"=> "This is health center number 981",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3983,
            "user_id"=> 1,
            "name"=> "Health Center 983",
            "email"=> "center983@example.com",
            "phone"=> "0978622397",
            "type"=> "center",
            "lat"=> -14.955389104623,
            "lng"=> 26.209097887533,
            "status"=> "active",
            "address"=> "Plot 4895, Mongu, Zambia",
            "description"=> "This is health center number 983",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3984,
            "user_id"=> 1,
            "name"=> "Health Center 984",
            "email"=> "center984@example.com",
            "phone"=> "0973487029",
            "type"=> "center",
            "lat"=> -14.053405332776,
            "lng"=> 32.681733244276,
            "status"=> "active",
            "address"=> "Plot 1488, Kabwe, Zambia",
            "description"=> "This is health center number 984",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3986,
            "user_id"=> 1,
            "name"=> "Health Center 986",
            "email"=> "center986@example.com",
            "phone"=> "0976144604",
            "type"=> "center",
            "lat"=> -15.920514741317,
            "lng"=> 26.123167825408,
            "status"=> "active",
            "address"=> "Plot 96, Lusaka, Zambia",
            "description"=> "This is health center number 986",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3987,
            "user_id"=> 1,
            "name"=> "Health Center 987",
            "email"=> "center987@example.com",
            "phone"=> "0972777130",
            "type"=> "center",
            "lat"=> -10.258047753041,
            "lng"=> 28.857525357957,
            "status"=> "active",
            "address"=> "Plot 4885, Ndola, Zambia",
            "description"=> "This is health center number 987",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3989,
            "user_id"=> 1,
            "name"=> "Health Center 989",
            "email"=> "center989@example.com",
            "phone"=> "0977055579",
            "type"=> "center",
            "lat"=> -13.425496280624,
            "lng"=> 23.618286868333,
            "status"=> "active",
            "address"=> "Plot 4393, Nakonde, Zambia",
            "description"=> "This is health center number 989",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3993,
            "user_id"=> 1,
            "name"=> "Health Center 993",
            "email"=> "center993@example.com",
            "phone"=> "0976266986",
            "type"=> "center",
            "lat"=> -14.917742540882,
            "lng"=> 30.878719829572,
            "status"=> "active",
            "address"=> "Plot 635, Livingstone, Zambia",
            "description"=> "This is health center number 993",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3994,
            "user_id"=> 1,
            "name"=> "Health Center 994",
            "email"=> "center994@example.com",
            "phone"=> "0971288973",
            "type"=> "center",
            "lat"=> -11.656170416882,
            "lng"=> 30.747910429373,
            "status"=> "active",
            "address"=> "Plot 1338, Mansa, Zambia",
            "description"=> "This is health center number 994",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3996,
            "user_id"=> 1,
            "name"=> "Health Center 996",
            "email"=> "center996@example.com",
            "phone"=> "0976671319",
            "type"=> "center",
            "lat"=> -13.143241941157,
            "lng"=> 25.655766562911,
            "status"=> "active",
            "address"=> "Plot 646, Monze, Zambia",
            "description"=> "This is health center number 996",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3997,
            "user_id"=> 1,
            "name"=> "Health Center 997",
            "email"=> "center997@example.com",
            "phone"=> "0972520465",
            "type"=> "center",
            "lat"=> -16.69243288552,
            "lng"=> 26.101267946605,
            "status"=> "active",
            "address"=> "Plot 2630, Kalulushi, Zambia",
            "description"=> "This is health center number 997",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 3999,
            "user_id"=> 1,
            "name"=> "Health Center 999",
            "email"=> "center999@example.com",
            "phone"=> "0971629592",
            "type"=> "center",
            "lat"=> -9.7509200355275,
            "lng"=> 33.271111615268,
            "status"=> "active",
            "address"=> "Plot 2754, Mkushi, Zambia",
            "description"=> "This is health center number 999",
            "is_verified"=> true,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ],
        [
            "id"=> 4000,
            "user_id"=> 1,
            "name"=> "Health Center 1000",
            "email"=> "center1000@example.com",
            "phone"=> "0972086009",
            "type"=> "center",
            "lat"=> -13.618724204841,
            "lng"=> 29.877600678698,
            "status"=> "active",
            "address"=> "Plot 1542, Kasama, Zambia",
            "description"=> "This is health center number 1000",
            "is_verified"=> false,
            "created_at"=> "2025-06-01T15: 33: 28.000000Z",
            "updated_at"=> "2025-06-01T15: 33: 28.000000Z"
        ]
    ];
}
