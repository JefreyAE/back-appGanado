<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Animals;
use App\Parents;
use App\Purchases;
use App\Sales;
use App\Injectables;
use App\Incidents;
use App\Images_Animals;
use Carbon\Carbon;
use Image;

class AnimalController extends Controller {

    public function pruebas(Request $request) {
        return "Accion de pruebas de UserController";
    }

    public function index(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        $user_token = $jwtAuth->checkToken($token, true);
        try {
            $listActive = Animals::where('user_id', $user_token->id)
                    ->where('animal_state', "Activo")
                    ->orderByDesc('birth_date')
                    ->get();
            $data = array(
                'code' => 200,
                'status' => 'success',
                'listActive' => $listActive
            );
        }catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        

        return response()->json($data, $data['code']);
    }

    public function dead(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        $user_token = $jwtAuth->checkToken($token, true);
        try {
            $listDead = Animals::where('user_id', $user_token->id)->where('animal_state', 'Muerto')->get();
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'listDead' => $listDead
        );

        return response()->json($data, $data['code']);
    }

    public function indexAll(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        //Usuario identificado
        $user_token = $jwtAuth->checkToken($token, true);
        try {
            $listAll = Animals::where('user_id', $user_token->id)->get();
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'listAll' => $listAll
        );

        return response()->json($data, $data['code']);
    }

    public function create(Request $request) {
        //Comprobar usuario identificado        
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        //Recibir datos
        $json = $request->input('json', null);
        if (is_array($json)) {
            $params = $json;
            $params_array = $json;
            $es = array(
                'array' => 'si'
            );
        } else {
            $params = json_decode($json);
            $params_array = json_decode($json, true);
            $es = array(
                'array' => 'no'
            );
        }

        //Validar lo datos
        if (!isset($params_array['birth_date'])) {
            $params_array['birth_date'] = null;
        }
        if ($params_array['birth_date'] == '') {
            $params_array['birth_date'] = null;
        }

        if (!isset($params_array['nickname'])) {
            $params_array['nickname'] = '';
        }
        if (!isset($params_array['certification_name'])) {
            $params_array['certification_name'] = '';
        }
        if (!isset($params_array['registration_number'])) {
            $params_array['registration_number'] = '';
        }
        if (!isset($params_array['birth_weight'])) {
            $params_array['birth_weight'] = 0;
        }
        $validate = \Validator::make($params_array, [
                    'nickname'            => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                    'certification_name'  => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                    'registration_number' => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                    'birth_weight'        => 'nullable|numeric',
                    'code'                => 'required|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                    'birth_date'          => 'nullable|date',
                    'sex'                 => 'required|alpha',
                    'race'                => 'nullable|regex:/^[a-zA-Z0-9\s\-\/À-ÿ\u00f1\u00d1]+$/u'
        ]);
        //Limpiar blancos
        $params_array = array_map('trim', $params_array);

        if ($validate->fails()) {
            $data = array(
                'code' => 200,
                'status' => 'error',
                'validationErrors' => $validate->errors(),
                'message' => 'Ocurrio un error durante la validación'
            );
        } else {

            try {
                $user_token = $jwtAuth->checkToken($token, true);
                $animal = new Animals();
                $parents = new Parents();
                $animal->user_id = $user_token->id;
                $animal->nickname = $params_array['nickname'];
                $animal->certification_name = $params_array['certification_name'];
                $animal->registration_number = $params_array['registration_number'];
                $animal->birth_weight = $params_array['birth_weight'];
                $animal->code = $params_array['code'];
                $animal->entry_date = Carbon::now();
                if ($params_array['birth_date'] == '') {
                    $animal->birth_date = null;
                } else {
                    $animal->birth_date = $params_array['birth_date'];
                }
                $animal->sex = $params_array['sex'];
                $animal->race = $params_array['race'];
                $animal->animal_state = "Activo";

                $animal->save();

                $animal_id = $animal->id;
                if ($params_array['mother_id'] == "unknown") {
                    $params_array['mother_id'] = 0;
                }
                if ($params_array['father_id'] == "unknown") {
                    $params_array['father_id'] = 0;
                }
                $parents->mother_id = $params_array['mother_id'];
                $parents->father_id = $params_array['father_id'];
                $parents->animal_id = $animal_id;
                $parents->save();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Animal registrado correctamente.',
                    'id' => $animal_id
                );
            } catch (\Exception $e) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => $e->getMessage()
                );
            }
        }
        return response()->json($data, $data['code']);
    }

    public function getAnimal(Request $request) {

        $id = $request->route('id');
        try {
            $validate = \Validator::make(['id' => $id], [
                    'id' => 'required|numeric'
            ]);
        } catch (\Exception $e) {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }
        try {
            /* Datos generales */
            $animal = Animals::where('id', $id)->first();
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'animal' => $animal
        );

        return response()->json($data, $data['code']);
    }

    public function detail(Request $request) {

        $id = $request->route('id');
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        try {
            $validate = \Validator::make(['id' => $id], [
                        'id' => 'required|numeric'
            ]);
        } catch (\Exception $e) {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }

        $user_token = $jwtAuth->checkToken($token, true);
        $animal = Animals::where('id', $id)->where('user_id', $user_token->id)->first();

        if(!$animal){
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => 'No fue posible encontrar el registro'
            );
            return response()->json($data, $data['code']);
        }

        try {
            /* Datos generales */
            $detail = Animals::where('id', $id)->get();
            $parents = Parents::where('animal_id', $id)->get();
            $detail[0]['father_id'] = $parents[0]['father_id'];
            $detail[0]['mother_id'] = $parents[0]['mother_id'];

            /* Incidentes */
            $incidents = Incidents::where('animal_id', $id)->get();

            /* Injectables */
            $injectables = Injectables::where('animal_id', $id)->get();

            /* OffSprings */
            $animal = $detail;
            //return response()->json($sex, 200);
            //$detail = Incidents::where('animal_id',$id)->get();
            if ($animal[0]['sex'] == "Macho") {
                $listOffSprings = Animals::join('parents', 'parents.animal_id', '=', 'animals.id')
                        ->where('father_id', $id)
                        ->select('animals.*', 'parents.father_id', 'parents.mother_id')
                        ->get();
                $firstDaughter = Animals::where('sex', '=', 'Hembra')->join('parents', 'parents.animal_id', '=', 'animals.id')
                ->where('father_id', $id)
                ->select('animals.*', 'parents.father_id', 'parents.mother_id')
                ->orderBy('birth_date', 'ASC')
                ->first();
            }
            if ($animal[0]['sex'] == "Hembra") {
                $listOffSprings = Animals::join('parents', 'parents.animal_id', '=', 'animals.id')
                        ->where('mother_id', $id)
                        ->select('animals.*', 'parents.father_id', 'parents.mother_id')
                        ->get();
                $firstDaughter = Animals::where('sex', '=', 'Hembra')->join('parents', 'parents.animal_id', '=', 'animals.id')
                ->where('mother_id', $id)
                ->select('animals.*', 'parents.father_id', 'parents.mother_id')
                ->orderBy('birth_date', 'ASC')
                ->first();
            }
          
            /*Statistics*/
            
            //Conteo de machos y hembras
            $numberFemale = 0;
            $numberMale = 0;
            foreach($listOffSprings as $animalOffSpring){
                if($animalOffSpring['sex']=="Hembra"){
                    $numberFemale++;
                }
                if($animalOffSpring['sex']=="Macho"){
                    $numberMale++; 
                }
            }

            //Edad
            $birth_date = explode(' ', $detail[0]['birth_date'])[0];
            //Correccion para produccion
            if(!$firstDaughter){
                $firstDaughter = array('birth_date' => '');
            }
            $birth_date_daughter = explode(' ', $firstDaughter['birth_date'])[0];

            $dateHelper = new \App\Helpers\DateHelper();
            $age = $dateHelper->getAge($birth_date);
            $daughterAge = $dateHelper->getAge($birth_date_daughter);

            $statistics = array (
                'offSpringsTotal'  => count($listOffSprings),
                'offSpringsFemale' => $numberFemale,
                'offSpringsMale'   => $numberMale,
                'age' => $age,
                'daughter' => $firstDaughter,
                'daughterAge' => $daughterAge
            );

            $i = 0;
            foreach ($listOffSprings as $animal) {
                $father = Animals::where('id', $listOffSprings[$i]['father_id'])->first();
                $mother = Animals::where('id', $listOffSprings[$i]['mother_id'])->first();
                //Correccion para produccion
                if(!$mother){
                    $mother = array('id'=> '0');
                }
                if(!$father){
                    $mother = array('id'=> '0');
                }
                $listOffSprings[$i]['father'] = $father;
                $listOffSprings[$i]['mother'] = $mother;
                $i++;
            }
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'detail' => $detail,
            'incidents' => $incidents,
            'injectables' => $injectables,
            'offsprings' => $listOffSprings,
            'statistics'  => $statistics
        );

        
        return response()->json($data, $data['code']);
    }

    public function injectables(Request $request) {

        $id = $request->route('id');
        try {
            $validate = \Validator::make(['id' => $id], [
                        'id' => 'required|numeric'
            ]);
        } catch (\Exception $e) {
            $data = array(
                'code' => 403,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }

        try {
            $detail = Injectables::where('animal_id', $id)->get();
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'detail' => $detail
        );

        return response()->json($data, $data['code']);
    }

    public function incidents(Request $request) {

        $id = $request->route('id');

        try {
            $validate = \Validator::make(['id' => $id], [
                        'id' => 'required|numeric'
            ]);
        } catch (\Exception $e) {
            $data = array(
                'code' => 403,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }

        try {
            $detail = Incidents::where('animal_id', $id)->get();
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'detail' => $detail
        );

        return response()->json($data, $data['code']);
    }

    public function offSprings(Request $request) {

        $id = $request->route('id');
        try {
            $validate = \Validator::make(['id' => $id], [
                        'id' => 'required|numeric'
            ]);
        } catch (\Exception $e) {
            $data = array(
                'code' => 403,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }

        try {
            $animal = Animals::where('id', $id)->get();
            //return response()->json($sex, 200);
            //$detail = Incidents::where('animal_id',$id)->get();
            if ($animal[0]['sex'] == "Macho") {
                $listOffSprings = Animals::join('parents', 'parents.animal_id', '=', 'animals.id')
                        ->where('father_id', $id)
                        ->select('animals.*', 'parents.father_id', 'parents.mother_id')
                        ->get();
            }
            if ($animal[0]['sex'] == "Hembra") {
                $listOffSprings = Animals::join('parents', 'parents.animal_id', '=', 'animals.id')
                        ->where('mother_id', $id)
                        ->select('animals.*', 'parents.father_id', 'parents.mother_id')
                        ->get();
            }
            $i = 0;
            foreach ($listOffSprings as $animal) {
                $father = Animals::where('id', $listOffSprings[$i]['father_id'])->first();
                $mother = Animals::where('id', $listOffSprings[$i]['mother_id'])->first();
                $listOffSprings[$i]['father'] = $father;
                $listOffSprings[$i]['mother'] = $mother;
                $i++;
            }
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'detail' => $listOffSprings
        );

        return response()->json($data, $data['code']);
    }

    public function find(Request $request) {
        //Comprobar usuario identificado        
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        //Recibir datos
        $json = $request->input('json', null);
        if (is_array($json)) {
            $params = $json;
            $params_array = $json;
            $es = array(
                'array' => 'si'
            );
        } else {
            $params = json_decode($json);
            $params_array = json_decode($json, true);
            $es = array(
                'array' => 'no'
            );
        }
        //Validar lo datos   
        $validate = \Validator::make($params_array, [
                    'search_type' => 'required|regex:/^[a-z_A-Z0-9\s]+$/u',
                    'find_string' => 'required|regex:/^[a-zA-Z0-9\s\-\/À-ÿ\u00f1\u00d1]+$/u',
        ]);
        //Limpiar blancos
        $params_array = array_map('trim', $params_array);

        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 400,
                'validationErrors' => $validate->errors(),
                'message' => 'Ocurrio un error durante la validación'
            );
        } else {
            try {
                $user_token = $jwtAuth->checkToken($token, true);

                $search_type = $params_array['search_type'];
                $find_string = $params_array['find_string'];

                $listFind = Animals::where('user_id', $user_token->id)
                        ->where($search_type, 'like', '%' . $find_string . '%')
                        ->get();

                if (!is_object($listFind)) {
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'Hubo un error en la consulta.'
                    );
                    return response()->json($data, $data['code']);
                }
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'listFind' => $listFind,
                    'message' => 'Busqueda realizada correctamente.'
                );
            } catch (\Exception $e) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => $e->getMessage()
                );
                return response()->json($data, $data['code']);
            }
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request){

        $image = $request->file('file0');
        $animal_id = $request->input('animal_id');
        $title = $request->input('title');
        $description = $request->input('description');

        $validate = \Validator::make($request->all(),[
            "file0"       => 'required|image|mimes:jpg,jpeg,png,gif|max:500000',
            'animal_id'   => 'required',
            'title'       => 'nullable|regex:/^[a-zA-Z0-9\s\-\/À-ÿ\u00f1\u00d1]+$/u',
            'description' => 'nullable|regex:/^[a-zA-Z0-9\s\-\/À-ÿ\u00f1\u00d1]+$/u'
        ]);

        if(!$image || $validate->fails() || !$animal_id){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Ocurrio un error al registrar los datos asociados a la imagen.' 
            );
        }else{

            $count = Images_Animals::where('animal_id', $animal_id)->count();

            if($count <= 5){
                $image_name = time().$image->getClientOriginalName();
                $image_reg = new Images_Animals();

                $image_reg->image_name = $image_name;
                $image_reg->animal_id = $animal_id;
                $image_reg->title = $title;
                $image_reg->description = $description;
                $image_reg->save();

                \Storage::disk('animals')->put($image_name, \File::get($image));

                try {
                    $image_resized = Image::make(\Storage::disk('animals')->get($image_name));

                    $image_resized->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                      });
    
                      \Storage::disk('animals')->put($image_name, (string) $image_resized->encode('jpg', 70));
                } catch (\Exception $e) {
                    $data = array(
                        'code' => 500,
                        'status' => 'error',
                        'message' => $e->getMessage()
                    );
                    return response()->json($data, $data['code']);
                }

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'count' => $count,
                    'message' => 'Imagen subida correctamente.'
                );
            }else{
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Solo se admiten 5 imagenes por animal.'
                );
            }
        }

        return response()->json($data, $data['code']);
    }

    public function getImagesNames(Request $request){

        $id = $request->route('id');
        try {
            $validate = \Validator::make(['id' => $id], [
                    'id' => 'required|numeric'
            ]);
        } catch (\Exception $e) {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }
        try {
            /* Imagenes */
            $images_list = Images_Animals::where('animal_id', $id)->get();

        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'images_list' => $images_list
        );

        return response()->json($data, $data['code']);
    }

    public function getImage($filename){

        $isset = \Storage::disk('animals')->exists($filename);

        if($isset){
            $file = \Storage::disk('animals')->get($filename);
        
            return new Response($file, 200);
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'No existe la imagen.'
            );
        }
        return response()->json($data, $data['code']);
    }

    public function deleteImage(Request $request){

        //Comprobar usuario identificado        
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        $animal_id = $request->route('animal_id');
        $image_name = $request->route('image_name');
       
        $validate = \Validator::make(['image_name' => $image_name, 'animal_id' => $animal_id],[
            'image_name' => 'nullable|regex:/^[a-zA-Z0-9\s\-\/À-ÿ\u00f1\u00d1.\-\_]+$/u',
            'animal_id'  => 'required|numeric'
        ]);

        if(!$image_name || $validate->fails()){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al borrar la imagen.',
                'data' => $image_name
            );
        }else{

            $user_token = $jwtAuth->checkToken($token, true);
            $animal = Animals::where('id', $animal_id)->where('user_id', $user_token->id)->first();
            
            if(!$animal){
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'No fue posible borrar la imagen'
                );
                return response()->json($data, $data['code']);
            }

            $image_animal = Images_Animals::where('image_name', $image_name)->delete();

            if(\Storage::disk('animals')->exists($image_name)){
                \Storage::disk('animals')->delete($image_name);
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Imagen borrada correctamente.'
                );
            }else{
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'El archivo no existe.'
                );
            }
        }

        return response()->json($data, $data['code']);
    }

    public function update(Request $request) {
        //Comprobar usuario identificado        
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        //Recibir datos
        $json = $request->input('json', null);
        if (is_array($json)) {
            $params_array = $json;
            $es = array(
                'array' => 'si'
            );
        } else {
            $params_array = json_decode($json, true);
            $es = array(
                'array' => 'no'
            );
        }

        //Validar lo datos
        if (!isset($params_array['birth_date'])) {
            $params_array['birth_date'] = null;
        }
        if ($params_array['birth_date'] == '') {
            $params_array['birth_date'] = null;
        }

        if (!isset($params_array['nickname'])) {
            $params_array['nickname'] = '';
        }
        if (!isset($params_array['certification_name'])) {
            $params_array['certification_name'] = '';
        }
        if (!isset($params_array['registration_number'])) {
            $params_array['registration_number'] = '';
        }
        if (!isset($params_array['birth_weight'])) {
            $params_array['birth_weight'] = 0;
        }
        $validate = \Validator::make($params_array, [
                    'nickname'            => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                    'certification_name'  => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                    'registration_number' => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                    'birth_weight'        => 'nullable|numeric',
                    'animal_id'           => 'numeric',
                    'code'                => 'required|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                    'birth_date'          => 'nullable|date',
                    'sex'                 => 'required|alpha',
                    'race'                => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u'
        ]);
        //Limpiar blancos
        $params_array = array_map('trim', $params_array);

        if ($validate->fails()) {
            $data = array(
                'code' => 200,
                'status' => 'error',
                'validationErrors' => $validate->errors(),
                'message' => 'Ocurrio un error durante la validación'
            );
        } else {
    
            try {
                $user_token = $jwtAuth->checkToken($token, true);
                $animal = Animals::where('id', $params_array['animal_id'])->where('user_id', $user_token->id)->first();
                
                if(!$animal){
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'No fue posible actualizar los datos'
                    );
                    return response()->json($data, $data['code']);
                }
               
                $parents = Parents::where('animal_id', $params_array['animal_id'])->first();
                $animal->user_id = $user_token->id;
                $animal->nickname = $params_array['nickname'];
                $animal->certification_name = $params_array['certification_name'];
                $animal->registration_number = $params_array['registration_number'];
                $animal->birth_weight = $params_array['birth_weight'];
                $animal->code = $params_array['code'];
                if ($params_array['birth_date'] == '') {
                    $animal->birth_date = null;
                } else {
                    $animal->birth_date = $params_array['birth_date'];
                }
                $animal->sex = $params_array['sex'];
                $animal->race = $params_array['race'];
                
                $animal->save();

                if ($params_array['mother_id'] == "unknown") {
                    $params_array['mother_id'] = 0;
                }
                if ($params_array['father_id'] == "unknown") {
                    $params_array['father_id'] = 0;
                }
                $parents->mother_id = $params_array['mother_id'];
                $parents->father_id = $params_array['father_id'];
                $parents->save();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Animal actualizado correctamente.',
                    'id' => $params_array['animal_id']
                );
            } catch (\Exception $e) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => $e->getMessage()
                );
                return response()->json($data, $data['code']);
            }
        }

        return response()->json($data, $data['code']);
    }

    public function deleteAnimal(Request $request){
        //Comprobar usuario identificado        
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        //Recibir datos
        $id = $request->route('id');

        $array_id = array('id'=> $id);

        $validate = \Validator::make( $array_id, [
                    'id'  =>  'numeric|required',
        ]);
       
        if ($validate->fails()) {
            $data = array(
                'code' => 200,
                'status' => 'error',
                'validationErrors' => $validate->errors(),
                'message' => 'Ocurrio un error durante la validación'
            );
        } else {

            try {
                $user_token = $jwtAuth->checkToken($token, true);
                $animal = Animals::where('id', $id)->where('user_id', $user_token->id)->first();
                
                if(!$animal){
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'No fue posible actualizar los datos'
                    );
                    return response()->json($data, $data['code']);
                }

                if($animal['sex'] =='Macho'){
                    $is_parent = Parents::where('father_id', $id)->first();
                }else{
                    $is_parent = Parents::where('mother_id', $id)->first();
                }

                if($is_parent){
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'No es posible borrar un registro con descendencia asociada'
                    );
                    return response()->json($data, $data['code']);
                }

                $injectables = Injectables::where('animal_id', $id);
                $injectables->delete();
                $incidents = Incidents::where('animal_id', $id);
                $incidents->delete();
                $purchases = Purchases::where('animal_id', $id);
                $purchases->delete();
                $sales = Sales::where('animal_id', $id);
                $sales->delete();
                $parents = Parents::where('animal_id', $id);
                $parents->delete();

                $images_animal = Images_Animals::where('animal_id', $id)->get();

                foreach($images_animal as $image){
                   
                    if(\Storage::disk('animals')->exists($image['image_name'])){
                        \Storage::disk('animals')->delete($image['image_name']); 
                    }
                }
                $image_animal = Images_Animals::where('animal_id', $id)->delete();

                $animal->delete();
            
            } catch (\Exception $e) {
                $data = array(
                    'code' => 500,
                    'status' => 'error',
                    'message' => $e->getMessage()
                );
                return response()->json($data, $data['code']);
            }
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Registro borrado correctamente'
            );
        }

        return response()->json($data, $data['code']);
 
    }
}
